<?php

include "../functions/format_currency.php";
include "../core-signed-in.php";

$user_id = $_SESSION["id"];
$invoice_id = $_GET["id"];

$stmt = $db->prepare("
  SELECT status, sale.customer_id AS customer_id, sale.project_id AS project_id, sale.id AS sale_id, customer.name AS customer_name, project.name as project_name
  FROM sale
  LEFT JOIN customer
  ON customer.id = sale.customer_id
  LEFT JOIN project
  ON project.id = sale.project_id
  WHERE sale.id = ? AND sale.user_id = ?");

$stmt->execute([$invoice_id, $user_id]);
$line = $stmt->fetch();

if (!$line) {
  http_response_code(404);
  die();
}

?>

<!DOCTYPE html>
<html>

<?php include "../head.html" ?>

<body>
  <?php include "../nav.php"; ?>
  <main class="container my-5">
    <div class="d-flex justify-content-between align-items-center">
      <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
          <li class="breadcrumb-item"><a href="/invoices.php">Invoices</a></li>
          <li class="breadcrumb-item active" aria-current="page"><?php echo "#" . htmlspecialchars($invoice_id) ?></li>
        </ol>
      </nav>
    </div>
    <h2 class="py-4">Invoice #<?php echo htmlspecialchars($line['sale_id']) ?></h2>
    <table class="table mb-5">
      <thead>
        <tr>
          <th>Customer</th>
          <th>Project</th>
          <th>Status</th>
          <th>Customer Address</th>
        </tr>
      </thead>
      <tbody>
        <tr>
          <td>
            <a class="" href="/customers/customer.php?id=<?php echo htmlspecialchars($line["customer_id"]) ?>">
              <?php echo htmlspecialchars($line["customer_name"]) ?>
            </a>
          </td>
          <td>
            <a class="col-sm" href="/projects/project.php?id=<?php echo htmlspecialchars($line["project_id"]) ?>">
              <?php echo htmlspecialchars($line["project_name"]) ?>
            </a>
          </td>
          <td>
            <select name="sale_status" hx-post="/invoices/status.php" hx-swap="none" hx-include="next input" class="form-select form-select-sm">
              <option value="DRAFT" <?php if (htmlspecialchars($line["status"]) === "DRAFT") echo "selected" ?>>DRAFT</option>
              <option value="APPROVED" <?php if (htmlspecialchars($line["status"]) === "APPROVED") echo "selected" ?>>APPROVED</option>
              <option value="AWAITING PAYMENT" <?php if (htmlspecialchars($line["status"]) === "AWAITING PAYMENT") echo "selected" ?>>AWAITING PAYMENT</option>
              <option value="PART PAID" <?php if (htmlspecialchars($line["status"]) === "PART PAID") echo "selected" ?>>PART PAID</option>
              <option value="PAID" <?php if (htmlspecialchars($line["status"]) === "PAID") echo "selected" ?>>PAID</option>
            </select>
            <input type="hidden" name="sale_id" value="<?php echo htmlspecialchars($invoice_id); ?>">
          </td>
          <td>
            <select name="address_id" class="form-select form-select-sm" hx-post="/invoices/customer-address.php" hx-swap="none" hx-include="next input">
              <?php

              $stmt = $db->prepare("
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
              ");
              $stmt->execute([$user_id, $invoice_id]);

              foreach ($stmt as $row) { ?>
                <option value="<?php echo $row["address_id"] ?>" <?php if ($row["has_address_set"] === "t") echo "selected"; ?>>
                  <?php echo $row["line1"]; ?>,
                  <?php echo $row["city"]; ?>,
                  <?php echo $row["country"]; ?>,
                  <?php echo $row["postcode"]; ?>
                </option>
              <?php } ?>
            </select>
            <input type="hidden" name="sale_id" value="<?php echo htmlspecialchars($invoice_id); ?>">
          </td>
        </tr>
      </tbody>
    </table>
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

        $stmt = $db->prepare("SELECT name, quantity, unit_amount FROM sale_line_item WHERE sale_id = ? AND user_id = ?");
        $stmt->execute([$invoice_id, $user_id]);

        foreach ($stmt as $line_item) { ?>
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

            $stmt = $db->prepare("SELECT SUM(sale_line_item.unit_amount * sale_line_item.quantity) AS amount
            FROM sale
            LEFT JOIN sale_line_item
            ON sale_line_item.sale_id = sale.id
            WHERE sale.user_id = ? AND sale.id = ?
            GROUP BY sale.id");
            $result = $stmt->execute([$invoice_id, $user_id]);
            $row = $stmt->fetch();

            if ($row) {
              $amount = $row["amount"];
            } else {
              $amount = 0;
            }

            echo format_currency($amount);

            ?>
          </th>
        </tr>
      </tfoot>
    </table>
  </main>
</body>

</html>
