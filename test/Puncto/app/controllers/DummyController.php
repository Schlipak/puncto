<?php

namespace PunctoUnit;

use Puncto\Controller;

class DummyController extends Controller
{
    public function home()
    {
        return 'Index';
    }

    public function controllerError()
    {
        return $this->undefinedMethod();
    }
}
