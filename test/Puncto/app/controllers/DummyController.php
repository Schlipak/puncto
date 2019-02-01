<?php

namespace PunctoUnit;

use Puncto\Controller;

class DummyController extends Controller
{
    public function index()
    {
        return 'Index';
    }

    public function controllerError()
    {
        return $this->undefinedMethod();
    }
}
