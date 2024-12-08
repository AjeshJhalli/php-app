<?php

$url_parts = explode('?', $_SERVER['REQUEST_URI']);
$url_path = $url_parts[0];

session_start();

if (!isset($_SESSION['logged_in']) && $url_path != "/auth/signin.php") {
  header('Location: /auth/signin.php');
  die();
}

$dbconn = pg_connect("user=postgres.wjucgknzgympnnywamjy password=" . getenv("PGPASSWORD") . " host=aws-0-eu-west-2.pooler.supabase.com port=6543 dbname=postgres") or die('Could not connect: ' . pg_last_error());

$new = isset($_GET['mode']) && $_GET['mode'] === 'new';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  pg_insert($dbconn, "customer", ["name" => $_POST["name"], "user_id" => $_SESSION["id"]]);
  pg_close($dbconn);
  header("Location: /customers.php");
  die();
}

?>

<!DOCTYPE html>
<html>
<?php include "./head.html" ?>

<body>
  <?php
  $uri_path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
  $uri_segments = explode('/', $uri_path);
  include "./nav.php";
  ?>
  <main class="container my-5">
    <h1 class="mb-4">Invoices</h1>
    <div class="d-flex justify-content-between align-items-center mb-3">
        <form class="form-inline my-2 my-lg-0 d-flex" hx-get="/components/invoice-list.php" hx-target="#project-tbody" hx-trigger="load, input changed delay:500ms, search">
          <input class="form-control" name="search" type="search" placeholder="Search" aria-label="Search">
        </form>
      </div>
      <table class="table border mt-2 table-hover">
        <thead>
          <tr>
            <th scope="col" style="text-align: right; padding-right: 40px; width: 200px;">Invoice Number</th>
            <th scope="col">Customer</th>
            <th scope="col">Project</th>
            <th scope="col">Status</th>
            <th scope="col" style="text-align: right; padding-right: 40px; width: 200px;">Amount</th>
          </tr>
        </thead>
        <tbody id="project-tbody">
        </tbody>
      </table>
  </main>
</body>

</html>