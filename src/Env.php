<?php

namespace Puncto;

class Env extends Bootstrapable
{
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

        $this->PUNCTO_VERSION = self::getVersion();
    }

    public static function getVersion()
    {
        $cd = 'cd ' . __DIR__ . ' &&';

        $lastTag = exec("$cd git describe --tags `git rev-list --tags --max-count=1`");
        $commitsSinceLastTag = exec("$cd git rev-list `git rev-list --tags --no-walk --max-count=1`..HEAD --count");
        $currentCommitHash = exec("$cd git rev-parse --short HEAD");

        // Ignore from coverage: dependant on git repo state
        // @codeCoverageIgnoreStart
        if ($commitsSinceLastTag > 0) {
            return "$lastTag+$commitsSinceLastTag ($currentCommitHash)";
        }

        return $lastTag;
        // @codeCoverageIgnoreEnd
    }

    /** @codeCoverageIgnore */
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
