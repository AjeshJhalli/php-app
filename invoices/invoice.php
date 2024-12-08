<?php

include "../functions/format_currency.php";

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

$query = "
  SELECT status, sale.customer_id AS customer_id, sale.project_id AS project_id, customer.name AS customer_name, project.name as project_name
  FROM sale
  LEFT JOIN customer
  ON customer.id = sale.customer_id
  LEFT JOIN project
  ON project.id = sale.project_id
  WHERE sale.id = $1 AND sale.user_id = $2";
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
        <li class="breadcrumb-item active" aria-current="page"><?php echo "#" . htmlspecialchars($invoice_id) ?></li>
      </ol>
    </nav>
    <h2 class="py-4"><?php echo htmlspecialchars($line['status']) ?></h2>
    <div class="pb-4">
      <div>Customer: <a href="/customers/customer.php?id=<?php echo htmlspecialchars($line["customer_id"]) ?>"><?php echo htmlspecialchars($line["customer_name"]) ?></a></div>
      <div>Project: <a href="/projects/project.php?id=<?php echo htmlspecialchars($line["project_id"]) ?>"><?php echo htmlspecialchars($line["project_name"]) ?></a></div>
    </div>
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

        while ($line_item = pg_fetch_assoc($result)) { ?>
          <tr>
            <td><?php echo htmlspecialchars($line_item["name"]); ?></td>
            <td><?php echo htmlspecialchars($line_item["quantity"]); ?></td>
            <td><?php echo format_currency(htmlspecialchars($line_item["unit_amount"])); ?></td>
            <td><?php echo format_currency(htmlspecialchars($line_item["quantity"] * $line_item["unit_amount"])); ?></td>
          </tr>
        <?php } ?>
      </tbody>
    </table>
  </main>
</body>

</html>

<?php pg_close($dbconn);
