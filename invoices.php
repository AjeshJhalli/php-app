<?php

include "./core-signed-in.php";
include "./functions/format_currency.php";

$user_id = $_SESSION["id"];

if (isset($_GET["search"])) {
  $search = $_GET["search"];
  $search = "%$search%";
  $query = "
    SELECT sale.id AS id, sale.status AS status, customer.name AS customer_name, project.name AS project_name, SUM(sale_line_item.unit_amount * sale_line_item.quantity) AS amount
    FROM sale
    LEFT JOIN project
    ON project.id = sale.project_id
    LEFT JOIN customer
    ON customer.id = sale.customer_id
    LEFT JOIN sale_line_item
    ON sale_line_item.sale_id = sale.id
    WHERE sale.user_id = :user_id AND
    (project.name LIKE :search OR customer.name LIKE :search)
    GROUP BY sale.id, customer_name, project_name
  ";
  $params = [":user_id" => $user_id, ":search" => $search];
} else {
  $query = "
    SELECT sale.id AS id, sale.status AS status, customer.name AS customer_name, project.name AS project_name, SUM(sale_line_item.unit_amount * sale_line_item.quantity) AS amount
    FROM sale
    LEFT JOIN project
    ON project.id = sale.project_id
    LEFT JOIN customer
    ON customer.id = sale.customer_id
    LEFT JOIN sale_line_item
    ON sale_line_item.sale_id = sale.id
    WHERE sale.user_id = ?
    GROUP BY sale.id, customer_name, project_name
  ";
  $params = [$user_id];
}

$stmt = $db->prepare($query);
$stmt->execute($params);

?>

<!DOCTYPE html>
<html>
<?php include "./head.html" ?>

<body>
  <?php include "./nav.php"; ?>
  <main class="container my-5">
    <h1 class="mb-4">Invoices</h1>
    <table id="table-invoices" class="display">
      <thead>
        <tr>
          <th scope="col" style="text-align: right; padding-right: 40px; width: 200px;">Invoice Number</th>
          <th scope="col">Customer</th>
          <th scope="col">Project</th>
          <th scope="col">Status</th>
          <th scope="col" style="text-align: right; padding-right: 40px; width: 200px;">Amount</th>
        </tr>
      </thead>
      <tbody id="project-tbody" hx-get="/components/invoice-list.php" hx-trigger="load">
        <?php foreach ($stmt as $line) { ?>
          <tr onclick="window.location.href = '/invoices/invoice.php?id=<?php echo htmlspecialchars($line["id"]); ?>'">
            <td style="text-align: right; padding-right: 40px;">
              <?php echo htmlspecialchars($line['id']); ?>
            </td>
            <td>
              <?php echo htmlspecialchars($line['customer_name']); ?>
            </td>
            <td>
              <?php echo htmlspecialchars($line['project_name']); ?>
            </td>
            <td>
              <?php echo htmlspecialchars($line['status']); ?>
            </td>
            <td style="text-align: right; padding-right: 40px;">
              <?php
              if ($line["amount"]) {
                $amount = $line["amount"];
              } else {
                $amount = 0;
              }
              echo format_currency($amount);
              ?>
            </td>
          </tr>
        <?php } ?>
      </tbody>
    </table>
  </main>
  <script defer>
    new DataTable('#table-invoices');
  </script>
</body>

</html>