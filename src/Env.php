<?php

namespace Puncto;

class Env extends Bootstrapable
{
    const VERSION = '0.1.0';

    private $env;

    protected function bootstrapSelf()
    {
        $this->env = $_ENV;

        foreach ($this->env as $key => $value) {
            $this->$key = $value;
        }

        if (!isset($this->PUNCTO_ENV)) {
            $this->PUNCTO_ENV = 'development';
        }

        $this->PUNCTO_VERSION = self::VERSION;
    }

    /** @codeCoverageIgnore  */
    public function __toString()
    {
        $body = "";

        foreach ($this as $key => $value) {
            if ($key === 'env') {
                continue;
            }

            $body .= "  $key => $value\n";
        }

        return "<#Env\n$body>";
    }
}
