<?php

$url_parts = explode('?', $_SERVER['REQUEST_URI']);
$url_path = $url_parts[0];

session_start();

if (!isset($_SESSION['logged_in'])) {
  header('Location: /auth/signin.php');
  die();
}

$dbconn = pg_connect("user=postgres.wjucgknzgympnnywamjy password=" . getenv("PGPASSWORD") . " host=aws-0-eu-west-2.pooler.supabase.com port=6543 dbname=postgres") or die('Could not connect: ' . pg_last_error());

?>

<!DOCTYPE html>
<html>
<?php include "./head.html" ?>
<body>
  <?php include "./nav.php"; ?>
  <main class="container my-5">
    <h1>Home</h1>
    <p>
      Welcome to the app.
    </p>
  </main>
</body>
</html>