<?php

$url_parts = explode('?', $_SERVER['REQUEST_URI']);
$url_path = $url_parts[0];

session_start();

if (!isset($_SESSION['logged_in']) && $url_path != "/auth/signin.php") {
  header('Location: /auth/signin.php');
  die();
}

$dbconn = pg_connect("user=postgres.wjucgknzgympnnywamjy password=" . getenv("PGPASSWORD") . " host=aws-0-eu-west-2.pooler.supabase.com port=6543 dbname=postgres") or die('Could not connect: ' . pg_last_error());

$invoice_id = $_GET["id"];
$user_id = $_SESSION["id"];

$query = "SELECT status FROM sale WHERE id = $1 AND user_id = $2";
$params = [$invoice_id, $user_id];
$result = pg_query_params($dbconn, $query, $params) or die('Query failed: ' . pg_last_error());

if (!($line = pg_fetch_row($result, null, PGSQL_ASSOC))) {
  http_response_code(404);
  die();
}

pg_free_result($result);

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
    <nav aria-label="breadcrumb">
      <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="/invoices.php">Invoices</a></li>
        <li class="breadcrumb-item active" aria-current="page"><?php echo "#" . $invoice_id ?></li>
      </ol>
    </nav>
    <h2 class="py-4"><?php echo $line['status'] ?></h2>
    <table class="table">
      <thead>
        <th>
          Line Item
        </th>
        <th>
          Quantity (hours)
        </th>
        <th>
          Unit Amount
        </th>
        <th>
          Total Amount
        </th>
      </thead>
      <tbody>
        <?php

        $result = pg_query_params($dbconn, "SELECT name, quantity, unit_amount FROM sale_line_item WHERE sale_id = $1 AND user_id = $2", [$invoice_id, $user_id]);

        while ($line_item = pg_fetch_assoc($result)) {
        ?>
          <tr>
            <td><?php echo $line_item["name"]; ?></td>
            <td><?php echo $line_item["quantity"]; ?></td>
            <td>£<?php echo $line_item["unit_amount"]; ?></td>
            <td>£<?php echo $line_item["quantity"] * $line_item["unit_amount"]; ?></td>
          </tr>
        <?php
        }

        ?>
      </tbody>
    </table>
  </main>
</body>

</html>

<?php pg_close($dbconn);
