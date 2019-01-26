<?php
  $title = 'Puncto | Welcome';
?>

<!DOCTYPE html>
<html lang="en">
<?php include 'partials/_head.html.php'?>
<body>
  <?php include 'partials/_header.html.php'?>

  <main class="centered">
    <div class="card">
      <h2>
        <span>We have liftoff!</span>
      </h2>

      <img src="/PUNCTO_DEV/assets/rocket.svg" alt="Woosh!" class="rocket">

      <div>
        <p>Get started with Puncto by adding a <code>GET /</code> route to replace this page</p>
        <p class="sub">You are seeing this message because your app is in <strong>development mode</strong></p>
        <p class="sub">
          <a href="#" data-trigger data-target="#modal-dev-infos">Show development infos</a>
        </p>
      </div>
    </div>

    <div class="modal-container">
      <div class="modal-inner">
        <div class="modal" id="modal-dev-infos">
          <a href="#" data-close>
            <img src="/PUNCTO_DEV/assets/close.svg" alt="Close modal">
          </a>

          <h2>Development infos</h2>

          <?php include 'partials/_routes.html.php'?>
          <?php include 'partials/_dump.html.php'?>
        </div>
      </div>
    </div>

  </main>

  <script src="/PUNCTO_DEV/scripts/modal.js"></script>
</body>
</html>
