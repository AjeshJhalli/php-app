<?php

$url_parts = explode('?', $_SERVER['REQUEST_URI']);
$url_path = $url_parts[0];

session_start();

if (!isset($_SESSION['logged_in']) && $url_path != "/auth/signin.php") {
  header('Location: /auth/signin.php');
  die();
}

$dbconn = pg_connect("user=postgres.wjucgknzgympnnywamjy password=" . getenv("PGPASSWORD") . " host=aws-0-eu-west-2.pooler.supabase.com port=6543 dbname=postgres") or die('Could not connect: ' . pg_last_error());

$customer_id = $_GET['id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $name = $_POST['name'];
  pg_update($dbconn, 'customer', array('name' => $name), array('id' => $customer_id));
  header('Location: ?id=' . $customer_id);
  die();
} elseif ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
  pg_delete($dbconn, 'customer', array('id' => $customer_id));
  header('HX-Location: /customers.php');
  die();
}

$query = "SELECT name FROM customer WHERE id = $1 AND user_id = $2";
$result = pg_query_params($dbconn, $query, [$customer_id, $_SESSION["id"]]) or die('Query failed: ' . pg_last_error());

if (!($line = pg_fetch_row($result, null, PGSQL_ASSOC))) {
  http_response_code(404);
  die();
}

pg_free_result($result);
pg_close($dbconn);

?>

<!DOCTYPE html>
<html>

<?php include "../head.html" ?>

<body>
  <?php
  $uri_path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
  $uri_segments = explode('/', $uri_path);
  include "../nav.php";
  ?>
  <main class="container my-5">
    <div class="d-flex align-items-center justify-content-between">
      <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
          <li class="breadcrumb-item"><a href="/customers.php">Customers</a></li>
          <li class="breadcrumb-item active" aria-current="page"><?php echo htmlspecialchars($line["name"]); ?></li>
        </ol>
      </nav>
      <?php
      $url_edit = "?id=$customer_id&mode=edit";
      $url_delete = "";
      $delete_confirmation = "Are you sure you want to delete this customer?";
      include '../templates/template_view_actions.php';
      ?>
    </div>

    <h2 class="py-4"><?php echo htmlspecialchars($line['name']) ?></h2>
    <?php if (isset($_GET['mode']) && $_GET['mode'] === 'edit') { ?>
      <form method="POST">
        <div>
          <label class="form-label">
            Name:
            <input class="form-control" type="text" name="name" value="<?php echo htmlspecialchars($line['name']) ?>" required>
          </label>
        </div>
        <a class="btn btn-primary" href="?id=<?php echo htmlspecialchars($customer_id); ?>">Cancel</a>
        <button class="btn btn-primary">Save</button>
      </form>
    <?php } else { ?>
      <div id="customer-tabs" hx-get="/components/customer-tabs.php?tab=email-addresses&customer_id=<?php echo htmlspecialchars($customer_id); ?>" hx-trigger="load"></div>
    <?php } ?>
  </main>
</body>

</html>