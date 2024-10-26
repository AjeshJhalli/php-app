<?php
session_start();
if (!isset($_SESSION['logged_in'])) {
  header('Location: /auth/signin.php');
  die();
}

if ($_SERVER['PHP_SELF'] !== '/index.php') {
  echo "<h1>404 - Page not found</h1><a href='/'>Home</a>";
  http_response_code(404);
  die();
}

?>
<!DOCTYPE html>
<html>

<head>
  <title>Software Developer Costing Xero Sync</title>
</head>

<body>
  <?php include_once './components/navbar.php'; ?>
  <h1>Home</h1>
  <p>
    Welcome to the app.
  </p>
</body>

</html>