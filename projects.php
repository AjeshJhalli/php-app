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
  $result = pg_insert($dbconn, "project", ["name" => $_POST["name"], "user_id" => $_SESSION["id"], "customer_id" => $_POST["customer_id"], "hourly_rate" => $_POST["hourly_rate"]]);
  pg_close($dbconn);
  header("Location: /projects.php");
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

    <h1 class="mb-4">Projects</h1>

    <?php if ($new) { ?>

      <form method="POST">
        <div>
          <label class="form-label">
            Customer:
            <select class="form-select" name="customer_id">
              <option selected disabled>Customer</option>
              <?php

              $query = "SELECT id, name FROM customer WHERE user_id = '" . $_SESSION["id"] . "' ";
              $result = pg_query($dbconn, $query) or die("Query failed: " . pg_last_error());

              while (($line = pg_fetch_row($result, null, PGSQL_ASSOC))) {
                echo "<option value='" . $line["id"] . "'>" . $line["name"] . "</option>";
              }

              ?>
            </select>
          </label>
        </div>
        <div>
          <label class="form-label">
            Project Name:
            <input class="form-control" type="text" name="name">
          </label>
        </div>
        <div>
          <label class="form-label">
            Hourly Rate:
            <input class="form-control" type="number" step=".01" name="hourly_rate">
          </label>
        </div>
        <div>
          <a class="btn btn-primary" href="/projects.php">Cancel</a>
          <button class="btn btn-primary">Save</button>
        </div>
      </form>
    <?php } else { ?>
      <div class="d-flex justify-content-between align-items-center mb-3">
        <form class="form-inline my-2 my-lg-0 d-flex" hx-get="/components/project-list.php" hx-target="#project-tbody" hx-trigger="load, input changed delay:500ms, search">
          <input class="form-control" name="search" type="search" placeholder="Search" aria-label="Search">
        </form>
        <a class="btn btn-primary" href="?mode=new">New</a>
      </div>
      <table class="table border mt-2 table-hover">
        <thead>
          <tr>
            <th scope="col">Project Name</th>
            <th scope="col">Customer</th>
          </tr>
        </thead>
        <tbody id="project-tbody">
        </tbody>
      </table>
    <?php } ?>
  </main>
</body>

</html>