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
  SELECT status, sale.customer_id AS customer_id, sale.project_id AS project_id, sale.id AS sale_id, customer.name AS customer_name, project.name as project_name
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
    <a class="btn btn-primary" href="/invoices/preview.php?id=<?php echo htmlspecialchars($invoice_id) ?>">Preview</a>
    <h2 class="py-4">Invoice #<?php echo htmlspecialchars($line['sale_id']) ?></h2>
    <div class="row justify-content-start container pb-4 w-50">
      <div class="row align-items-center">
        <div class="col-sm">Customer:</div>
        <a class="col-sm" href="/customers/customer.php?id=<?php echo htmlspecialchars($line["customer_id"]) ?>">
          <?php echo htmlspecialchars($line["customer_name"]) ?>
        </a>
      </div>
      <div class="row align-items-center">
        <div class="col-sm">Project:</div>
        <a class="col-sm" href="/projects/project.php?id=<?php echo htmlspecialchars($line["project_id"]) ?>">
          <?php echo htmlspecialchars($line["project_name"]) ?>
        </a>
      </div>
      <div class="row align-items-center">
        <div class="col-sm">Status:</div>
        <select name="sale_status" hx-post="/invoices/status.php" hx-swap="none" hx-include="next input" class="col-sm form-select form-select-sm">
          <option value="DRAFT" <?php if (htmlspecialchars($line["status"]) === "DRAFT") echo "selected" ?>>DRAFT</option>
          <option value="APPROVED" <?php if (htmlspecialchars($line["status"]) === "APPROVED") echo "selected" ?>>APPROVED</option>
          <option value="AWAITING PAYMENT" <?php if (htmlspecialchars($line["status"]) === "AWAITING PAYMENT") echo "selected" ?>>AWAITING PAYMENT</option>
          <option value="PART PAID" <?php if (htmlspecialchars($line["status"]) === "PART PAID") echo "selected" ?>>PART PAID</option>
          <option value="PAID" <?php if (htmlspecialchars($line["status"]) === "PAID") echo "selected" ?>>PAID</option>
        </select>
        <input type="hidden" name="sale_id" value="<?php echo htmlspecialchars($invoice_id); ?>">
      </div>
      <div class="row align-items-center">
        <div class="col-sm">Customer Address:</div>
        <select name="address_id" class="col-sm form-select form-select-sm" hx-post="/invoices/customer-address.php" hx-swap="none" hx-include="next input">
          <?php

          $query = "
          SELECT
              address.id as address_id,
              line1, 
              line2, 
              city, 
              county, 
              country, 
              postcode,
              CASE 
                  WHEN sale_with_address.id IS NOT NULL THEN true
                  ELSE false
              END AS has_address_set
          FROM address
          INNER JOIN sale 
              ON sale.customer_id = address.customer_id
          LEFT JOIN sale AS sale_with_address
              ON sale_with_address.customer_address_id = address.id
          WHERE sale.user_id = $1 AND sale.id = $2;
        ";

          $params = [$user_id, $invoice_id];
          $result = pg_query_params($dbconn, $query, $params) or die('Query failed: ' . pg_last_error());

          while ($row = pg_fetch_assoc($result)) { ?>
            <option value="<?php echo $row["address_id"] ?>" <?php if ($row["has_address_set"] === "t") echo "selected"; ?>>
              <?php echo $row["line1"]; ?>,
              <?php echo $row["city"]; ?>,
              <?php echo $row["country"]; ?>,
              <?php echo $row["postcode"]; ?>
            </option>
          <?php } ?>
        </select>
        <input type="hidden" name="sale_id" value="<?php echo htmlspecialchars($invoice_id); ?>">
      </div>
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
