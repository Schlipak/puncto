# Puncto

![](https://img.shields.io/circleci/project/github/Schlipak/puncto.svg?label=Build%20status&logo=circleci&style=flat)
![](https://img.shields.io/codeclimate/coverage/Schlipak/puncto.svg?label=Coverage&style=flat)
![](https://img.shields.io/codeclimate/maintainability/Schlipak/puncto.svg?label=Maintainability&style=flat)

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
│   ├── assets
│   ├── controllers
│   │   └── HomeController.php
│   └── templates
│       ├── home.html.php
│       └── partials
│           ├── _header.html.php
│           └── _head.html.php
├── composer.json
├── composer.lock
├── config
│   └── routes.json
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

require __DIR__ . '/vendor/autoload.php';

use Puncto\Application;

// Create a new Puncto application
// The name will default to `app`
// The app name will also be used as an namespace for your controllers and classes
$app = new Application('puncto-demo');

$app->configure(function ($config) {
    // Set up a static asset path
    // Assets will be served from the URL /assets/ASSET_FILENAME
    $config->serveStatic('/assets/*');
    
    // Loads routes from the config file (see Route configuration)
    $config->loadRoutes('config/routes.json');

    // Declare `users` as a resource
    // This will assume the existance of a `APP_NAMESPACE\UserController` class
    // and add the `index`, `show`, `create`, `update`, and `delete` routes
    $config->resource('users');
    
    // You can also declare inline routes by calling a supported HTTP verb on `$config`
    $config->get('/demo', function($request, $env, $params, $renderer) {
      return "Demo content";
    });
});

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

Supported HTTP methods:
* GET _(+ HEAD)_
* POST
* PUT
* PATCH
* DELETE

Each method defines a series of URLs, which map to a controller action, formatted as `ControllerName#actionName`.
A controller named `Home` in the route configuration will automatically map to the `APP_NAMESPACE\HomeController` class.

## Controllers

A typical controller is a class exposing public methods which have access to the request, and return a rendered template.
Puncto has no support for Models (for now), so any database call must be done manually. (sorry)

```php
<?php

// The namespace must correspond to the application name given in Puncto\Router#register
namespace MyApp;

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
