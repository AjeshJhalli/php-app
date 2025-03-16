<?php

include "../functions/format_currency.php";

session_start();

if (!isset($_SESSION['logged_in'])) {
  die();
}

try {
  $db = new \PDO("sqlite:../database/codecost.sqlite");
} catch (\PDOException $e) {
  echo $e;
  die();
}

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

foreach ($stmt as $line) { ?>
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
      <?php echo format_currency(htmlspecialchars($line['amount'])); ?>
    </td>
  </tr>
<?php }
