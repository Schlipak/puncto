<?php
  $title = 'Puncto | Internal server error';
?>

<!DOCTYPE html>
<html lang="en">
<?php include 'partials/_head.html.php'?>
<body>
  <?php include 'partials/_header.html.php'?>

  <main>
    <h2 class="error">
      <p>Internal server error</p>

      <?php if (isset($exception)): ?>
        <div class="sub">
          <p><?= $exception->getMessage() ?></p>
          <p>In <?= preg_replace("/\//", "<wbr>/", $exception->getFile()) ?> line <?= $exception->getLine() ?></p>
        </div>
      <?php else: ?>
        <div class="sub">
          <p><?= $message ?></p>
        </div>
      <?php endif ?>
    </h2>

    <p class="sub">
      PHP notices and warnings are turned into errors in development mode.<br>
      This gives you an opportunity to catch mistakes early during development.<br>
      This behaviour will be turned off in production mode.
    </p>

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
