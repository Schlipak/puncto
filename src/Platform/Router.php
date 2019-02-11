<?php

namespace Puncto\Platform;

use Puncto\Platform\Env;
use Puncto\Platform\Request;
use Puncto\Platform\StaticHandler;
use Puncto\PunctoObject;
use Puncto\Utils\Logger;
use Puncto\Utils\StringHelper;
use Puncto\View\Renderer;
use \ErrorException;
use \Throwable;

class Router extends PunctoObject
{
    const SUPPORTED_HTTP_METHODS = [
        'GET',
        'POST',
        'PUT',
        'PATCH',
        'DELETE',
    ];

    private $request;
    private $env;
    private $renderer;

    private $errorHandler;

    public function __construct($request, $env, $skipTestModeCode = false)
    {
        $this->skipTestModeCode = $skipTestModeCode;

        $this->request = $request;
        $this->env = $env;
        $this->renderer = new Renderer(['request' => $this->request, 'env' => $this->env]);

        $this->errorHandler = function (...$args) {
            return $this->defaultErrorHandler(...$args);
        };

        if ($this->env->PUNCTO_ENV === 'development') {
            if (!$this->skipTestModeCode) {
                // @codeCoverageIgnoreStart
                set_error_handler(function ($severity, $message, $file, $line) {
                    throw new ErrorException($message, $severity, $severity, $file, $line);
                });
                // @codeCoverageIgnoreEnd
            }

            $this->get('/', 'PUNCTO_DEV__WELCOME', function ($request, $env, $params, $renderer) {
                $renderer->appendContext([
                    'routes' => $this->getAllRoutes(false),
                ]);

                return $renderer->render(__PLATFORM_ROOT__ . '/templates/welcome', false);
            });

            $this->serveStatic('/PUNCTO_DEV/assets/*', 'PUNCTO_DEV__ASSETS', true);
            $this->serveStatic('/PUNCTO_DEV/styles/*', 'PUNCTO_DEV__STYLES', true);
            $this->serveStatic('/PUNCTO_DEV/scripts/*', 'PUNCTO_DEV__SCRIPTS', true);
        }
    }

    public function getRequest()
    {
        return $this->request;
    }

    public function getEnv()
    {
        return $this->env;
    }

    public function getRenderer()
    {
        return $this->renderer;
    }

    public function __call($name, $args)
    {
        $httpMethod = strtoupper($name);

        $route = '';
        $handler = '(anonymous)';
        $callback = null;

        $count = count($args);
        if ($count === 2) {
            list($route, $callback) = $args;
        } elseif ($count === 3) {
            list($route, $handler, $callback) = $args;
        } else {
            $malformedCallInfo = print_r($args, true);

            Logger::error("Register {$httpMethod} route: Invalid argument count (got $count, expected 2..3)");
            Logger::error("$name -> $malformedCallInfo");
            return false;
        }

        if (!in_array($httpMethod, self::SUPPORTED_HTTP_METHODS)) {
            if ($this->env->PUNCTO_ENV === 'development') {
                $this->skipTestModeCode = true;
                echo $this->invalidMethodHandler($route, $httpMethod);
                return false;
            }
        }

        $routeData = $this->formatRouteParams($route, $callback, $handler, $httpMethod);
        $this->{strtolower($name)}[$routeData['route']] = $routeData;

        $routeName = $routeData['route'];
        $handlerName = $routeData['handler'];
        Logger::debug("Registered route $httpMethod $routeName -> $handlerName");

        return true;
    }

    public function registerResource($name, $pluralize = false)
    {
        $controllerName = StringHelper::toClassCase($name);
        $controllerNamePlural = $controllerName;

        if (!$pluralize) {
            $controllerName = StringHelper::toSingular($controllerName);
        }

        $controllerClass = __APPNAMESPACE__ . '\\' . $controllerName . 'Controller';
        $baseRoute = '/' . StringHelper::toURL($controllerNamePlural);
        $resourceRoute = "{$baseRoute}/:id";

        $handlers = [
            ['url' => $baseRoute, 'methods' => ['GET'], 'action' => 'index'],
            ['url' => $resourceRoute, 'methods' => ['GET'], 'action' => 'show'],
            ['url' => $baseRoute, 'methods' => ['POST'], 'action' => 'create'],
            ['url' => $resourceRoute, 'methods' => ['PUT', 'PATCH'], 'action' => 'update'],
            ['url' => $resourceRoute, 'methods' => ['DELETE'], 'action' => 'delete'],
        ];

        foreach ($handlers as $handlerData) {
            $action = $handlerData['action'];
            $handler = "{$controllerName}#{$action}";

            foreach ($handlerData['methods'] as $method) {
                $this->$method(
                    $handlerData['url'],
                    $handler,
                    $this->createControllerHandler($handler, $controllerName, $controllerClass, $action)
                );
            }
        }
    }

    public function load($file)
    {
        $json = json_decode(file_get_contents($file));

        foreach ($json as $httpMethod => $routes) {
            foreach ($routes as $path => $handler) {
                list($controllerName, $action) = explode('#', $handler);
                $controllerClass = __APPNAMESPACE__ . '\\' . $controllerName . 'Controller';

                $this->$httpMethod(
                    $path,
                    $handler,
                    $this->createControllerHandler($handler, $controllerName, $controllerClass, $action)
                );
            }
        }
    }

    private function createControllerHandler($handler, $controllerName, $controllerClass, $action)
    {
        return function (
            $request,
            $env,
            $params,
            $renderer
        ) use (
            $handler,
            $controllerName,
            $controllerClass,
            $action
        ) {
            $start = round(microtime(true) * 1000);

            try {
                $controller = new $controllerClass($request, $env, $params, $renderer);
            } catch (Throwable $err) {
                return $this->renderError(
                    501,
                    'Not Implemented',
                    'no_controller',
                    [
                        'controller' => $controllerClass,
                        'action' => $action,
                        'handler' => $handler,
                        'exception' => $err,
                    ]
                );
            }

            if (!method_exists($controller, $action)) {
                $allRoutes = $this->getAllRoutes(false, $controllerName);

                return $this->renderError(
                    501,
                    'Not Implemented',
                    'no_action',
                    [
                        'controller' => $controllerClass,
                        'action' => $action,
                        'handler' => $handler,
                        'routes' => $allRoutes,
                    ]
                );
            }

            try {
                $output = $controller->$action();
            } catch (Throwable $err) {
                return $this->renderError(
                    500,
                    'Internal Server Error',
                    'internal_error',
                    [
                        'controller' => $controllerClass,
                        'action' => $action,
                        'handler' => $handler,
                        'exception' => $err,
                    ]
                );
            }

            $end = round(microtime(true) * 1000);
            $dt = $end - $start;
            Logger::log("  Processed in ${dt}ms", 'green');

            return $output;
        };
    }

    public function serveStatic($route, $name = 'BuiltinStaticHandler', $serveBuiltin = false)
    {
        $routeData = $this->formatRouteParams($route, function ($request, $env, $params) use ($route, $serveBuiltin) {
            $handler = new StaticHandler($route, $serveBuiltin);

            try {
                return $handler->render($request, $env, $params);
            } catch (Throwable $err) {
                return $this->renderError(404, 'Not Found', 'not_found');
            }
        }, $name, 'GET');

        $this->get[$routeData['route']] = $routeData;

        $routeName = $routeData['route'];
        Logger::debug("Registered static route $routeName");
    }

    /**
     * Removes trailing forward slashes from the right of the route.
     * @param route (string)
     */
    private function cleanRoute($route)
    {
        $result = ltrim(rtrim($route, '/'), '/');

        return '/' . $result;
    }

    /**
     * Handles route params
     * @param route (string)
     * @param method (object(Closure))
     */
    private function formatRouteParams($route, $method = null, $handler = null, $httpMethod = null)
    {
        $result = $this->cleanRoute($route);

        $matches = [];
        preg_match_all("/:([\w_]+)|(\*)$/", $result, $matches, PREG_PATTERN_ORDER | PREG_UNMATCHED_AS_NULL);
        $params = array_values(array_filter(array_merge($matches[1], $matches[2])));

        $reg = preg_replace("/\//", "\/", $result);
        $reg = preg_replace("/:[\w_]+/", "([^\/\?&]+)", $reg);
        $reg = preg_replace("/\*$/", "(.*)", $reg);

        $reg = "/^$reg(?:\?.*)?$/";

        return [
            'httpMethod' => $httpMethod,
            'route' => $result,
            'reg' => $reg,
            'params' => $params,
            'method' => $method,
            'handler' => $handler,
        ];
    }

    private function invalidMethodHandler($route, $method)
    {
        $this->renderer->appendContext([
            'route' => $route,
            'method' => $method,
        ]);

        return $this->renderError(405, 'Method Not Allowed', 'method_not_allowed');
    }

    private function defaultErrorHandler($request, $env, $params, $renderer)
    {
        list('errorCode' => $code, 'errorMessage' => $message) = $renderer->getContext();

        return "$code $message";
    }

    private function getAllRoutes($includeMethod = false, $filterForController = null)
    {
        $allRoutesPerMethod = array_map(function ($method, $index) {
            $method = strtolower($method);

            if (!isset($this->$method)) {
                return [];
            }

            return array_map(function ($route) use ($index) {
                $route['sortIndex'] = $index;
                return $route;
            }, array_values($this->$method));
        }, self::SUPPORTED_HTTP_METHODS, array_keys(self::SUPPORTED_HTTP_METHODS));

        $allRoutes = array_filter(array_merge(...$allRoutesPerMethod), function ($route) {
            return !preg_match("/^PUNCTO_.+$/", $route['handler']);
        });

        if (!$includeMethod) {
            $allRoutes = array_map(function ($route) {
                unset($route['method']);
                return $route;
            }, $allRoutes);
        }

        if ($filterForController) {
            $allRoutes = array_filter($allRoutes, function ($route) use ($filterForController) {
                return preg_match("/^${filterForController}#.+$/", $route['handler']);
            });
        }

        return $allRoutes;
    }

    private function renderError($code, $message, $template, $additionalContext = [])
    {
        Logger::error("  Completed $code $message");
        header("{$this->request->serverProtocol} $code $message", true);

        if ($this->request->accepts('application/json')) {
            return json_encode([
                'status' => 'error',
                'message' => $message,
                'code' => $code,
            ]);
        }

        $this->renderer->appendContext([
            'errorCode' => $code,
            'errorMessage' => $message,
        ]);

        if ($this->env->PUNCTO_ENV === 'development') {
            $this->renderer->appendContext($additionalContext);

            if (!$this->renderer->hasContext('routes')) {
                $this->renderer->appendContext([
                    'routes' => $this->getAllRoutes(),
                ]);
            }

            return $this->renderer->render(__PLATFORM_ROOT__ . "/templates/$template", false);
        }

        return call_user_func_array($this->errorHandler, [$this->request, $this->env, [], $this->renderer]);
    }

    private function renderNotFound()
    {
        return $this->renderError(404, 'Not Found', 'no_such_route');
    }

    public function onError($callback)
    {
        $this->errorHandler = $callback;
        Logger::debug("Registered onError callback");
    }

    private function extractGetParams($url)
    {
        $matches = [];

        preg_match_all("/[\?&]([^\?&=]+)=([^\?&=]+)/", $url, $matches, PREG_PATTERN_ORDER | PREG_UNMATCHED_AS_NULL);
        $params = array_combine($matches[1], $matches[2]);

        return $params;
    }

    /**
     * Resolves a route
     */
    public function resolve()
    {
        $remoteAddr = $this->request->remoteAddr;
        $originMethod = $this->request->requestMethod;
        $httpMethod = $originMethod;
        $cleanRoute = $this->cleanRoute($this->request->requestUri);

        Logger::log("$remoteAddr -> Started $httpMethod $cleanRoute", 'white', 'bold');

        if (strtoupper($httpMethod) === 'HEAD') {
            $httpMethod = 'GET';
        } elseif (!in_array(strtoupper($httpMethod), self::SUPPORTED_HTTP_METHODS)) {
            echo $this->invalidMethodHandler($cleanRoute, $httpMethod);
            return;
        }

        $routesDict = [];
        if (isset($this->{strtolower($httpMethod)})) {
            $routesDict = $this->{strtolower($httpMethod)};
        }

        foreach ($routesDict as $route) {
            $matches = [];

            if (preg_match($route['reg'], $cleanRoute, $matches)) {
                $method = $route['method'];
                $paramNames = $route['params'];

                array_shift($matches);
                $params = array_combine($paramNames, $matches);
                $params = array_merge($params, $this->extractGetParams($cleanRoute));

                $this->renderer->appendContext(['params' => $params]);

                $handler = $route['handler'];
                Logger::log("  Matched route handler $handler");

                header('X-Powered-By: Puncto');
                header("X-Version: {$this->env->PUNCTO_VERSION}");
                header("{$this->request->serverProtocol} 200 OK");

                $output = call_user_func_array($method, [$this->request, $this->env, $params, $this->renderer]);

                if ($httpMethod !== $originMethod) {
                    return Logger::log("  Completed 200 OK", 'blue');
                }

                echo $output;
                return;
            }
        }

        echo $this->renderNotFound();
    }

    /** @codeCoverageIgnore */
    public function __destruct()
    {
        if (!$this->skipTestModeCode) {
            $this->resolve();
        }
    }
}
