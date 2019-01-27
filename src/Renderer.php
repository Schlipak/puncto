<?php

namespace {
    function RendererDefineHelpers()
    {
        if (!function_exists('partial')) {
            function partial($name, $ext = 'html.php')
            {
                $backtrace = debug_backtrace();
                $caller = $backtrace[0]['file'];
                $base = dirname($caller);

                return "$base/partials/_$name.$ext";
            }
        }
    }
}

namespace Puncto {
    use \RendererDefineHelpers;
    use \Throwable;

    class Renderer
    {
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

        public function render($template, $expandPath = true)
        {
            $__templateFile = $template;

            if ($expandPath) {
                $__templateFile = __ROOT__ . "/app/templates/$template.html.php";
            }

            extract($this->context);
            RendererDefineHelpers();

            try {
                ob_start();
                include $__templateFile;
                ob_end_flush();
            } catch (Throwable $err) {
                ob_end_clean();

                throw $err;
            }
        }
    }
}
