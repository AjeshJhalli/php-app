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
    <nav class="d-print-none" aria-label="breadcrumb">
      <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="/invoices.php">Invoices</a></li>
        <li class="breadcrumb-item"><a href="/invoices/invoice.php?id=<?php echo htmlspecialchars($invoice_id) ?>"><?php echo "#" . htmlspecialchars($invoice_id) ?></a></li>
        <li class="breadcrumb-item active" aria-current="page">Preview</li>
      </ol>
    </nav>
    <button class="btn btn-primary d-print-none mb-3" onclick="print()">
      Print
    </button>
    <div class="row align-items-start container mb-3 w-50">
      <div class="row">
        <span class="col fw-bold">
          Invoice No:
        </span>
        <span class="col">
          <?php echo htmlspecialchars($line['sale_id']) ?>
        </span>
      </div>
      <div class="row">
        <span class="col fw-bold">Invoice Date:</span>
        <span class="col"><?php echo date("d M Y"); ?></span>
      </div>
      <div class="row">
        <span class="col fw-bold">Due Date:</span>
        <span class="col"><?php echo date("d M Y", time() + 604800); ?></span>
      </div>
    </div>
    <hr>
    <div class="container">
      <div class="row">
        <div class="col">
          <div class="col fw-bold">From:</div>
          <div class="col">Code Cost</div>
          <div class="col">120 Example Lane</div>
          <div class="col">Twiddleham</div>
          <div class="col">Skibidishire</div>
          <div class="col">United Kingdom</div>
        </div>
        <div class="col">
          <?php
          $query = "
          SELECT line1, line2, city, county, country, postcode, customer.name as customer_name
          FROM address
          INNER JOIN sale
          ON address.id = sale.customer_address_id
          INNER JOIN customer
          ON sale.customer_id = customer.id
          WHERE sale.id = $1 AND address.user_id = $2;
        ";
          $result = pg_query_params($dbconn, $query, [$invoice_id, $user_id]);
          $address = pg_fetch_assoc($result);
          ?>
          <div class="col fw-bold">Bill To:</div>
          <div class="col"><?php echo $address["customer_name"]; ?></div>
          <div class="col"><?php echo $address["line1"]; ?></div>
          <div class="col"><?php echo $address["line2"]; ?></div>
          <div class="col"><?php echo $address["city"]; ?></div>
          <div class="col"><?php echo $address["county"]; ?></div>
          <div class="col"><?php echo $address["country"]; ?></div>
          <div class="col"><?php echo $address["postcode"]; ?></div>
        </div>
      </div>
    </div>
    <hr>
    <table class="table">
      <thead>
        <th>
          Description
        </th>
        <th style="text-align: right;">
          Quantity (hours)
        </th>
        <th style="text-align: right;">
          Unit Amount
        </th>
        <th style="text-align: right;">
          Total Amount
        </th>
      </thead>
      <tbody>
        <?php

        $result = pg_query_params($dbconn, "SELECT name, quantity, unit_amount FROM sale_line_item WHERE sale_id = $1 AND user_id = $2", [$invoice_id, $user_id]);

        while ($line_item = pg_fetch_assoc($result)) { ?>
          <tr>
            <td><?php echo htmlspecialchars($line_item["name"]); ?></td>
            <td style="text-align: right;"><?php echo htmlspecialchars($line_item["quantity"]); ?></td>
            <td style="text-align: right;"><?php echo format_currency(htmlspecialchars($line_item["unit_amount"])); ?></td>
            <td style="text-align: right;"><?php echo format_currency(htmlspecialchars($line_item["quantity"] * $line_item["unit_amount"])); ?></td>
          </tr>
        <?php } ?>
      </tbody>
      <tfoot>
        <tr>
          <th></th>
          <th></th>
          <th style="text-align: right;">Grand Total:</th>
          <th style="text-align: right;">
            <?php
            $result = pg_query_params($dbconn, "SELECT SUM(sale_line_item.unit_amount * sale_line_item.quantity) AS amount
            FROM sale
            LEFT JOIN sale_line_item
            ON sale_line_item.sale_id = sale.id
            WHERE sale.user_id = $2 AND sale.id = $1
            GROUP BY sale.id", [$invoice_id, $user_id]);
            $row = pg_fetch_assoc($result);
            echo format_currency($row["amount"]);
            ?>
          </th>
        </tr>
      </tfoot>
    </table>
  </main>
</body>

</html>

<?php pg_close($dbconn);
