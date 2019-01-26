<?php
  $title = 'Puncto | No such action';
?>

<!DOCTYPE html>
<html lang="en">
<?php include 'partials/_head.html.php'?>
<body>
  <?php include 'partials/_header.html.php'?>

  <main>
    <h2 class="error">
      <span>No such action: <?= $handler ?></span>
    </h2>

    <?php
      $ref = new ReflectionClass($controller);
      $actions = array_map(function($action) {
        return $action->name;
      }, $ref->getMethods(ReflectionMethod::IS_PUBLIC));

      $routes = array_filter($routes, function($route) use ($actions) {
        $actionName = explode("#", $route['handler'])[1];

        return in_array($actionName, $actions);
      });
    ?>

    <?php $routeTableCaption = "Available routes in $controller" ?>
    <?php include 'partials/_routes.html.php'?>
    <?php include 'partials/_dump.html.php'?>
  </main>
</body>
</html>
