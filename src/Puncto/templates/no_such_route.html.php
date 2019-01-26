<?php
  $title = 'Puncto | No such route';
?>

<!DOCTYPE html>
<html lang="en">
<?php include 'partials/_head.html.php'?>
<body>
  <?php include 'partials/_header.html.php'?>

  <main>
    <h2 class="error">
      <span>No such route: <?=$request->requestMethod?>&nbsp;<?=$request->requestUri?></span>
    </h2>

    <?php include 'partials/_routes.html.php'?>
    <?php include 'partials/_dump.html.php'?>
  </main>
</body>
</html>
