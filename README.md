# Puncto

![](https://img.shields.io/circleci/project/github/Schlipak/puncto.svg?label=Build%20status&logo=circleci&style=flat)

Puncto PHP web framework

Very early alpha, **DO NOT USE**

# Installation
## Composer

Puncto is not available on Packagist, in order to test it please use the following Composer configuration:

```json
{
  "minimum-stability": "dev",
  "repositories": [
    {
      "type": "vcs",
      "url": "https://github.com/Schlipak/puncto.git"
    }
  ],
  "require": {
    "schlipak/puncto": "*"
  }
}
```

This configuration is bleeding-edge, as the framework is not stable yet. Use this for testing or development purposes only. Puncto is **NOT** production ready yet.

# Usage
## Directory structure

```
.
├── app
│   ├── assets
│   ├── controllers
│   │   └── HomeController.php
│   └── templates
│       ├── home.html.php
│       └── partials
│           ├── _header.html.php
│           └── _head.html.php
├── composer.json
├── composer.lock
├── config
│   └── routes.json
├── index.php
├── rewrite.conf
└── vendor
```

* The `app/` directory contains your app code and assets
* The `config/` directory contains your app configuration files
* `index.php` is the entry file for your app
* `rewrite.conf`: Or equivalent depending on the serveur you use. All URLs should be redirected to `index.php`.
  - You can use the builtin PHP development server for testing purposes: `php -S localhost:8080 index.php`
  
## Entry file

The `index.php` file is the entry file for your application. Here is an example usage:

```php
<?php

// Import the Composer autoloader
require __DIR__ . '/vendor/autoload.php';

use Puncto\Router;

// Create a new Puncto router
$router = new Router();

// Register the root directory, and optionally the app/ directory.
$router->register(__DIR__, 'app');

// Sets up a static assets path.
// This will make all the files inside app/assets/ available as /assets/... in your app.
$router->serveStatic('/assets/*');

// Load the route configuration file.
// This will create routes and link them to their respective controller automatically
$router->load('config/routes.json');

// You can also define routes manually as such
$router->get('/test', function ($request, $env, $params, $renderer) {
  return 'Test page';
});

// The router resolves the current route automatically
```

## Route configuration

The route configuration file should follow the following format:

```json
{
  "get": {
    "/": "Home#index",
    "/demo": "Demo#index"
  },
  "post": {
    "/data": "Data#echo"
  }
}
```

GET and POST are the only HTTP methods supported for now (along HEAD).
Each method defines a series of URLs, which map to a controller action, formatted as `ControllerName#actionName`.
A controller named `Home` in the route configuration will automatically map to the `App\HomeController` class.

## Controllers

A typical controller is a class exposing public methods which have access to the request, and return a rendered template.
Puncto has no support for Models (for now), so any database call must be done manually. (sorry)

```php
<?php

// The namespace must correspond to the app name given in Puncto\Router#register
namespace App;

use Puncto\Controller;

// Controllers must extend the Puncto\Controller class
class HomeController extends Controller
{
    // Any public method can be used as a controller action
    public function index()
    {
        // use #appendContext to make variables visible to the template
        $this->appendContext([
            'title' => 'Puncto | Home',
        ]);

        // Controllers must return a render or a string
        //
        // Calling $this->render('home') will render the template in app/templates/home.html.php
        return $this->render('home');
    }
}
```

## Templates and partials

Templates and partials are PHP files ending in `.html.php`, located in the `app/templates/` directory.

Partial filenames must be prefixed with a `_` and are located in `app/templates/partials/`.

You can put templates and partials in subdirectories.
* A template located at `app/templates/home/index.html.php` should be named as `home/index` when rendered by a controller.
* A partial located at `app/templates/home/partials/_navbar.html.php` should be named as `home/navbar` when included with the `#partial` method.

```php
<!DOCTYPE html>
<html lang="en">
<?php include $this->partial('head') ?>
<body>
  <?php include $this->partial('header') ?>

  <main>
    <h1>Homepage</h1>

    <p>More text</p>
    <p><?= $this->escape($value) ?></p>
  </main>
</body>
</html>
```
