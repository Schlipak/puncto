<?php

namespace Puncto;

use Puncto\Autoloader;
use Puncto\Bootstrapable;
use Puncto\IRequest;
use Puncto\Renderer;
use \ErrorException;
use \Throwable;

class Router
{
    const SUPPORTED_HTTP_METHODS = [
        "GET",
        "POST",
    ];

    private $request;
    private $env;
    private $renderer;

    private $errorHandler;

    public function __construct(IRequest $request, Bootstrapable $env)
    {
        $this->request = $request;
        $this->env = $env;
        $this->renderer = new Renderer(['request' => $request, 'env' => $env]);

        $this->errorHandler = function (...$args) {
            return $this->defaultErrorHandler(...$args);
        };

        if ($this->env->PUNCTO_ENV === 'development') {
            set_error_handler(function ($severity, $message, $file, $line) {
                throw new ErrorException($message, $severity, $severity, $file, $line);
            });

            $this->get(['/', 'PUNCTO_DEV__WELCOME'], function ($request, $env, $params, $renderer) {
                $renderer->appendContext([
                    'routes' => $this->getAllRoutes(false),
                ]);

                return $renderer->render(__DIR__ . '/../templates/welcome.html.php', false);
            });

            $this->serveStatic('/PUNCTO_DEV/assets/*', 'PUNCTO_DEV__ASSETS', true);
            $this->serveStatic('/PUNCTO_DEV/styles/*', 'PUNCTO_DEV__STYLES', true);
            $this->serveStatic('/PUNCTO_DEV/scripts/*', 'PUNCTO_DEV__SCRIPTS', true);
        } else {
            error_reporting(0);
        }
    }

    public function register($base, $app = 'app') {
        Autoloader::register($base, $app);
    }

    public function __call($name, $args)
    {
        $httpMethod = strtoupper($name);
        $handler = '(anonymous)';
        list($route, $method) = $args;

        if (is_array($route)) {
            list($route, $handler) = $route;
        }

        if (!in_array($httpMethod, self::SUPPORTED_HTTP_METHODS)) {
            $this->invalidMethodHandler($route, $httpMethod);
        }

        $routeData = $this->formatRouteParams($route, $method, $handler, $httpMethod);
        $this->{strtolower($name)}[$routeData['route']] = $routeData;
    }

    public function load($file)
    {
        $json = json_decode(file_get_contents($file));

        foreach ($json as $httpMethod => $routes) {
            foreach ($routes as $path => $handler) {
                list($controllerName, $action) = explode('#', $handler);
                $controllerClass = Autoloader::APP_NAMESPACE . '\\' . $controllerName . 'Controller';

                $this->$httpMethod(
                    [$path, $handler],
                    function ($request, $env, $params, $renderer) use ($handler, $controllerName, $controllerClass, $action) {
                        $start = round(microtime(true) * 1000);

                        try {
                            $controller = new $controllerClass($request, $env, $params, $renderer);
                        } catch (Throwable $err) {
                            die($this->renderError(
                                501, 'Not Implemented', 'no_controller',
                                [
                                    'controller' => $controllerClass,
                                    'action' => $action,
                                    'handler' => $handler,
                                    'exception' => $err,
                                ]
                            ));
                        }

                        if (!method_exists($controller, $action)) {
                            $allRoutes = $this->getAllRoutes(false, $controllerName);

                            die($this->renderError(
                                501, 'Not Implemented', 'no_action',
                                [
                                    'controller' => $controllerClass,
                                    'action' => $action,
                                    'handler' => $handler,
                                    'routes' => $allRoutes,
                                ]
                            ));
                        }

                        try {
                            $output = $controller->$action();
                        } catch (Throwable $err) {
                            die($this->renderError(
                                500, 'Internal Server Error', 'internal_error',
                                [
                                    'controller' => $controllerClass,
                                    'action' => $action,
                                    'handler' => $handler,
                                    'exception' => $err,
                                ]
                            ));
                        }

                        $end = round(microtime(true) * 1000);
                        $dt = $end - $start;
                        error_log(Kolor::color("  Processed in ${dt}ms", 'green'));

                        return $output;
                    }
                );
            }
        }
    }

    private static function getMimeType($path)
    {
        $mime = mime_content_type($path);
        $ext = pathinfo($path, PATHINFO_EXTENSION);

        switch ($ext) {
            case 'html':
                return 'text/html';
            case 'css':
                return 'text/css';
            case 'js':
                return 'application/javascript';
            default:
                return $mime;
        }
    }

    public function serveStatic($route, $name = 'BuiltinStaticHandler', $serveBuiltin = false)
    {
        $routeData = $this->formatRouteParams($route, function ($request, $env, $params) use ($route, $serveBuiltin) {
            $start = round(microtime(true) * 1000);

            $base = explode('/*', $route)[0];
            $path = __APP__ . $base . DIRECTORY_SEPARATOR . $params['*'];

            if ($serveBuiltin) {
                $base = explode('/PUNCTO_DEV', $base)[1];
                $dir = explode('/src', __DIR__)[0];

                $path = $dir . $base . DIRECTORY_SEPARATOR . $params['*'];
            }

            if (file_exists($path)) {
                $mtime = filemtime($path);
                $gmtMtime = gmdate('D, d M Y H:i:s', $mtime) . ' GMT';
                $etag = sprintf('%08x-%08x', crc32($path), $mtime);

                if (isset($request->httpIfModifiedSince) || isset($request->httpIfNoneMatch)) {
                    $ifMod = $request->httpIfModifiedSince;
                    $ifNone = $request->httpIfNoneMatch;

                    if ($ifMod == $gmtMtime || str_replace('"', '', stripslashes($ifNone)) == $etag) {
                        error_log(Kolor::color("  Completed 304 Not Modified", 'magenta'));

                        header("{$request->serverProtocol} 304 Not Modified");
                        die();
                    }
                }

                $size = filesize($path);
                $mime = self::getMimeType($path);

                session_cache_limiter('none');

                header("Content-Type: $mime");
                header("Content-Length: $size");
                header("ETag: \"$etag\"");
                header("Last-Modified: $gmtMtime");
                header('Cache-Control: max-age=' . (60 * 60 * 24));

                $output = file_get_contents($path);

                $end = round(microtime(true) * 1000);
                $dt = $end - $start;
                error_log(Kolor::color("  Processed in ${dt}ms", 'green'));

                return $output;
            }

            return $this->renderError(404, 'Not Found', 'not_found');
        }, $name, 'GET');

        $this->get[$routeData['route']] = $routeData;
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
        error_log(Kolor::color("  Completed 405 Method Not Allowed", 'yellow'));

        $this->renderer->appendContext([
            'route' => $route,
            'method' => $method,
        ]);

        header("{$this->request->serverProtocol} 405 Method Not Allowed");
        die($this->renderError(405, 'Method Not Allowed', 'method_not_allowed'));
    }

    private function defaultErrorHandler($request, $env, $params, $renderer)
    {
        list('errorCode' => $code, 'errorMessage' => $message) = $renderer->getContext();

        return "$code $message";
    }

    private function getAllRoutes($includeMethod = false, $filterForController = null)
    {
        // TODO: Replace with dynamic HTTPMethods
        $allRoutes = array_filter(array_merge(
            (isset($this->get) ? $this->get : []),
            (isset($this->post) ? $this->post : [])
        ), function ($route) {
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
        error_log(Kolor::color("  Completed $code $message", 'red'));
        header("{$this->request->serverProtocol} $code $message");

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

            return $this->renderer->render(__DIR__ . "/../templates/$template.html.php", false);
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

        error_log(Kolor::color("$remoteAddr -> Started $httpMethod $cleanRoute", 'white', 'bold'));

        if (strtoupper($httpMethod) === 'HEAD') {
            $httpMethod = 'GET';
        } else if (!in_array(strtoupper($httpMethod), self::SUPPORTED_HTTP_METHODS)) {
            $this->invalidMethodHandler($cleanRoute, $httpMethod);
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
                error_log("  Matched route handler $handler");

                header('X-Powered-By: Puncto');

                $output = call_user_func_array($method, [$this->request, $this->env, $params, $this->renderer]);

                if ($httpMethod !== $originMethod) {
                    error_log(Kolor::color("  Completed 200 OK", 'blue'));
                    die();
                }

                die($output);
            }
        }

        die($this->renderNotFound());
    }

    public function __destruct()
    {
        $this->resolve();
    }
}
