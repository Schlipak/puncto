<?php
  $title = 'Puncto | Resource not found';
?>

<!DOCTYPE html>
<html lang="en">
<?php include 'partials/_head.html.php'?>
<body>
  <?php include 'partials/_header.html.php'?>

  <main>
    <h2 class="error">
      <span>Resource not found: <?=$request->requestUri?></span>
    </h2>

    <?php include 'partials/_dump.html.php'?>
  </main>
</body>
</html>
