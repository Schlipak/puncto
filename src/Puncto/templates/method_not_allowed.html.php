<?php
  $title = 'Puncto | Method not allowed';
?>

<!DOCTYPE html>
<html lang="en">
<?php include 'partials/_head.html.php'?>
<body>
  <?php include 'partials/_header.html.php'?>

  <main>
    <h2 class="error">
      <p>Method not allowed</p>

      <?php if (isset($exception)): ?>
        <div class="sub">
          <p><?= $exception->getMessage() ?></p>
          <p>In <?= preg_replace("/\//", "<wbr>/", $exception->getFile()) ?> line <?= $exception->getLine() ?></p>
        </div>
      <?php else: ?>
        <div class="sub">
          <p>In route <?= $route ?></p>
          <p>Method <?= $method ?> is not supported</p>
        </div>
      <?php endif ?>
    </h2>

    <?php if (isset($exception)): ?>
      <div class="collapse">
        <input type="checkbox" id="collapse-trace" checked><label for="collapse-trace">Show stack trace</label>
        <pre class="collapse-target"><?= $exception->getTraceAsString() ?></pre>
      </div>
    <?php endif ?>

    <?php include 'partials/_dump.html.php'?>
  </main>
</body>
</html>
