<?php

namespace Puncto\Traits;

use Puncto\StringHelper;

trait RenderHelper
{
    public function escape($input)
    {
        return htmlspecialchars($input);
    }

    public function camelCase($input)
    {
        return StringHelper::toCamelCase($input);
    }

    public function classCase($input)
    {
        return StringHelper::toClassCase($input);
    }
}
