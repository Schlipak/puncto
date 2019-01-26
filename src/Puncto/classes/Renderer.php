<?php

namespace Puncto;

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
        foreach ($this->context as $key => $value) {
            ${$key} = $value;
        }

        try {
            $file = $template;

            if ($expandPath) {
                $file = __ROOT__ . "/app/templates/$template.html.php";
            }

            ob_start();
            include $file;
            ob_end_flush();
        } catch (Throwable $err) {
            ob_end_clean();

            throw $err;
        }
    }
}
