<?php

namespace PunctoUnit;

use Puncto\Controller;

class UserController extends Controller
{
    public function show()
    {
        $id = $this->params['id'];

        return "User #$id";
    }
}
