<div class="table-wrapper">
  <table>
    <caption><?= isset($routeTableCaption) ? $routeTableCaption : 'Available routes' ?></caption>
    <thead>
      <tr>
        <th>Method</th>
        <th>Endpoint</th>
        <th>Handler</th>
      </tr>
    </thead>
    <tbody>
      <?php if (is_null($routes) || empty($routes)): ?>
        <tr>
          <td colspan="3">No routes</td>
        </tr>
      <?php endif ?>

      <?php foreach ($routes as $route): ?>
        <tr>
          <td>
            <span class="tag <?= strtolower($route['httpMethod']) ?>">
              <?= strtoupper($route['httpMethod']) ?>
            </span>
            <?php if (strtoupper($route['httpMethod']) === 'GET'): ?>
              <span class="tag head additional">HEAD</span>
            <?php endif ?>
          </td>
          <td><?= $route['route'] ?></td>
          <td><?= $route['handler'] ?></td>
        </tr>
      <?php endforeach?>
    </tbody>
  </table>
</div>
