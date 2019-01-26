<?php
  $title = 'Puncto | No such controller';
?>

<!DOCTYPE html>
<html lang="en">
<?php include 'partials/_head.html.php'?>
<body>
  <?php include 'partials/_header.html.php'?>

  <main>
    <h2 class="error">
      <p>No such controller: <?= $controller ?></p>
    </h2>

    <?php include 'partials/_dump.html.php'?>
  </main>
</body>
</html>
