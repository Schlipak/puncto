<?php

namespace Puncto;

use Puncto\Interfaces\IRenderable;
use Puncto\Logger;
use Puncto\Traits\RenderHelper;
use \Throwable;

class Renderer extends PunctoObject implements IRenderable
{
    use RenderHelper;

    private $context;

    public function __construct($context)
    {
        $this->context = $context;
    }

    public function setContext($context)
    {
        $this->context = $context;
    }

    public function getContext()
    {
        return $this->context;
    }

    public function appendContext($newContext)
    {
        $this->setContext(array_merge($this->context, $newContext));
    }

    public function hasContext($name)
    {
        return array_key_exists($name, $this->context);
    }

    private function findCircularDeps($template, $ext = 'html.php')
    {
        $backtrace = debug_backtrace();
        $results = ['circular' => false, 'stack' => []];

        foreach ($backtrace as $trace) {
            if (isset($trace['file'])) {
                $file = $trace['file'];

                if ($file === $template) {
                    $results['circular'] = true;
                }

                if (preg_match("/$ext$/", $file)) {
                    $results['stack'][] = $file;
                }
            }
        }

        return $results;
    }

    private function renderCircularDepError($results, $currentTemplate)
    {
        if ($results['circular']) {
            $stack = array_merge([$currentTemplate], $results['stack']);
            $list = implode('', array_map(function ($file) {
                return "<li>$file</li>";
            }, $stack));
            $message = "Circular dependency detected<ol start='0'>$list</ol>";

            throw new \Error($message);
        }
    }

    public function expandPath($filename)
    {
        return (
            __ROOT__ .
            DIRECTORY_SEPARATOR .
            __APP__ .
            DIRECTORY_SEPARATOR .
            'templates' .
            DIRECTORY_SEPARATOR .
            $filename
        );
    }

    public function partial($name, $ext = 'html.php')
    {
        $backtrace = debug_backtrace();
        $caller = $backtrace[0]['file'];
        $base = dirname($caller);
        $filename = "_$name.$ext";
        $fullName = $this->expandPath("partials/$filename");

        Logger::debug("    Include partial $filename");

        $this->renderCircularDepError($this->findCircularDeps($fullName), $fullName);
        return $fullName;
    }

    public function render($template, $expandPath = true, $ext = 'html.php')
    {
        $__templateFile = "$template.$ext";
        Logger::log("  Rendering template $__templateFile");

        if ($expandPath) {
            $__templateFile = $this->expandPath($__templateFile);
        }

        $this->renderCircularDepError($this->findCircularDeps($__templateFile), $__templateFile);

        extract($this->context);

        try {
            ob_start();
            include $__templateFile;
            return ob_get_clean();
        } catch (Throwable $err) {
            ob_end_clean();
            throw $err;
        }
    }
}
