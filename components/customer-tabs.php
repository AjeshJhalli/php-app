<?php

include "../functions/format_currency.php";

session_start();

if (!isset($_SESSION['logged_in'])) {
  die();
}

$db_path = "sqlite:../database/codecost.sqlite";

try {
  $db = new \PDO($db_path);
} catch (\PDOException $e) {
  echo $e;
  die();
}

$user_id = $_SESSION["id"];
$tab = $_GET["tab"];
$customer_id = $_GET['customer_id'];

?>
<ul class="nav nav-tabs">
  <li class="nav-item">
    <a class="nav-link <?php if ($tab === "email-addresses") echo "active" ?>" id="customer-email-addresses-tab" hx-get="/components/customer-tabs.php?tab=email-addresses&customer_id=<?php echo htmlspecialchars($customer_id); ?>" hx-target="#customer-tabs">Email Addresses</a>
  </li>
  <li class="nav-item">
    <a class="nav-link disabled" href="#">Phone Numbers</a>
  </li>
  <li class="nav-item">
    <a class="nav-link <?php if ($tab === "addresses") echo "active" ?>" id="customer-addresses-tab" hx-get="/components/customer-tabs.php?tab=addresses&customer_id=<?php echo htmlspecialchars($customer_id); ?>" hx-target="#customer-tabs">Addresses</a>
  </li>
  <li class="nav-item">
    <a class="nav-link <?php if ($tab == "projects") echo "active" ?>" id="customer-projects-tab" hx-get="/components/customer-tabs.php?tab=projects&customer_id=<?php echo htmlspecialchars($customer_id); ?>" hx-target="#customer-tabs">Projects</a>
  </li>
  <li class="nav-item">
    <a class="nav-link <?php if ($tab == "invoices") echo "active" ?>" id="customer-invoices-tab" hx-get="/components/customer-tabs.php?tab=invoices&customer_id=<?php echo htmlspecialchars($customer_id); ?>" hx-target="#customer-tabs">Invoices</a>
  </li>
  <li class="nav-item">
</ul>
<div id="tab-content">
  <?php
  if ($tab === "projects") {
    $customer_id = $_GET["customer_id"];
    $stmt = $db->prepare("SELECT id, name FROM project WHERE customer_id = ? AND user_id = ?");
    $stmt->execute([$customer_id, $user_id]);
  ?>
    <div class="list-group mt-2">
      <?php foreach ($stmt as $line) { ?>
        <a class='list-group-item list-group-item-action' href="/projects/project.php?id=<?php echo htmlspecialchars($line["id"]); ?>">
          <?php echo htmlspecialchars($line["name"]); ?>
        </a>
      <?php } ?>
    </div>
  <?php } else if ($tab == "invoices") {
    $customer_id = $_GET["customer_id"];
    $stmt = $db->prepare("SELECT sale.id as id, status, SUM(quantity * unit_amount) as amount FROM sale LEFT JOIN sale_line_item ON sale.id = sale_line_item.sale_id WHERE sale.customer_id = ? AND sale.user_id = ? GROUP BY sale.id");
    $stmt->execute([$customer_id, $user_id]);

  ?>
    <div class="list-group mt-2">
      <?php foreach ($stmt as $line) { ?>
        <a class="list-group-item list-group-item-action" href="/invoices/invoice.php?id=<?php echo htmlspecialchars($line['id']); ?>">
          Invoice #<?php echo htmlspecialchars($line['id']) . " - " . format_currency(htmlspecialchars($line["amount"])) . " - " . htmlspecialchars($line['status']); ?>
        </a>
      <?php } ?>
    </div>
  <?php } else if ($tab === "email-addresses") { ?>
    <table class="table">
      <thead>
        <tr>
          <th>Name</th>
          <th>Email Address</th>
          <th></th>
        </tr>
      </thead>
      <tbody id="customer-email-addresses-body">
        <?php

        $stmt = $db->prepare("SELECT id, email_address, label FROM email_address WHERE customer_id = ? AND user_id = ?");
        $stmt->execute([$customer_id, $user_id]);
        
        foreach ($stmt as $row) { ?>
          <tr>
            <td>
              <input type="hidden" name="email_id" value="<?php echo htmlspecialchars($row["id"]) ?>">
              <input class="form-control" name="email_label" value="<?php echo $row["label"] ?>" hx-post="/components/customer-email-addresses/label.php" hx-trigger="keyup changed delay:500ms" hx-include="previous input">
            </td>
            <td>
              <input type="hidden" name="email_id" value="<?php echo htmlspecialchars($row["id"]) ?>">
              <input class="form-control" name="email_address" value="<?php echo $row["email_address"] ?>" hx-post="/components/customer-email-addresses/email-address.php" hx-trigger="keyup changed delay:500ms" hx-include="previous input">
            </td>
            <td>
              <div class="dropdown">
                <button class="btn btn-secondary dropdown-toggle" type="button" id="dropdownMenuButton1" data-bs-toggle="dropdown" aria-expanded="false">
                  Options
                </button>
                <ul class="dropdown-menu">
                  <li>
                    <form hx-confirm="Are you sure you want to delete this email address?" hx-delete="/components/customer-email-addresses/delete.php" hx-target="closest tr">
                      <input type="hidden" name="email_id" value="<?php echo htmlspecialchars($row["id"]) ?>">
                      <button class="dropdown-item">Delete</button>
                    </form>
                  </li>
                </ul>
              </div>
            </td>
          </tr>
        <?php } ?>
      </tbody>
    </table>
    <div class="d-flex justify-content-end">
      <form hx-post="/components/customer-email-addresses/new.php" hx-target="#customer-email-addresses-body" hx-swap="beforeend">
        <input type="hidden" name="customer_id" value="<?php echo htmlspecialchars($customer_id); ?>">
        <button class="btn btn-primary">New</button>
      </form>
    </div>
  <?php } elseif ($tab === "addresses") { ?>

    <table class="table">
      <thead>
        <tr>
          <th>Line 1</th>
          <th>Line 2</th>
          <th>City</th>
          <th>County/State</th>
          <th>Country</th>
          <th>Postcode</th>
          <th></th>
        </tr>
      </thead>
      <tbody id="customer-addresses-body">
        <?php

        $stmt = $db->prepare("SELECT id, line1, line2, city, county, country, postcode FROM address WHERE customer_id = ? AND user_id = ? ORDER BY creation_timestamp ASC");
        $stmt->execute([$customer_id, $user_id]);
        
        foreach ($stmt as $row) { ?>
          <tr>
            <td>
              <input type="hidden" name="address_id" value="<?php echo htmlspecialchars($row["id"]) ?>">
              <input class="form-control" name="line1" value="<?php echo htmlspecialchars($row["line1"]) ?>" hx-post="/components/customer-addresses/line1.php" hx-trigger="keyup changed delay:500ms" hx-include="previous input">
            </td>
            <td>
              <input type="hidden" name="address_id" value="<?php echo htmlspecialchars($row["id"]) ?>">
              <input class="form-control" name="line2" value="<?php echo htmlspecialchars($row["line2"]) ?>" hx-post="/components/customer-addresses/line2.php" hx-trigger="keyup changed delay:500ms" hx-include="previous input">
            </td>
            <td>
              <input type="hidden" name="address_id" value="<?php echo htmlspecialchars($row["id"]) ?>">
              <input class="form-control" name="city" value="<?php echo htmlspecialchars($row["city"]) ?>" hx-post="/components/customer-addresses/city.php" hx-trigger="keyup changed delay:500ms" hx-include="previous input">
            </td>
            <td>
              <input type="hidden" name="address_id" value="<?php echo htmlspecialchars($row["id"]) ?>">
              <input class="form-control" name="county" value="<?php echo htmlspecialchars($row["county"]) ?>" hx-post="/components/customer-addresses/county.php" hx-trigger="keyup changed delay:500ms" hx-include="previous input">
            </td>
            <td>
              <input type="hidden" name="address_id" value="<?php echo htmlspecialchars($row["id"]) ?>">
              <input class="form-control" name="country" value="<?php echo htmlspecialchars($row["country"]) ?>" hx-post="/components/customer-addresses/country.php" hx-trigger="keyup changed delay:500ms" hx-include="previous input">
            </td>
            <td>
              <input type="hidden" name="address_id" value="<?php echo htmlspecialchars($row["id"]) ?>">
              <input class="form-control" name="postcode" value="<?php echo htmlspecialchars($row["postcode"]) ?>" hx-post="/components/customer-addresses/postcode.php" hx-trigger="keyup changed delay:500ms" hx-include="previous input">
            </td>
            <td>
              <div class="dropdown">
                <button class="btn btn-secondary dropdown-toggle" type="button" id="dropdownMenuButton1" data-bs-toggle="dropdown" aria-expanded="false">
                  Options
                </button>
                <ul class="dropdown-menu">
                  <li>
                    <form hx-confirm="Are you sure you want to delete this address?" hx-delete="/components/customer-addresses/delete.php" hx-target="closest tr">
                      <input type="hidden" name="address_id" value="<?php echo htmlspecialchars($row["id"]) ?>">
                      <button class="dropdown-item">Delete</button>
                    </form>
                  </li>
                </ul>
              </div>
            </td>
          </tr>
        <?php } ?>
      </tbody>
    </table>
    <div class="d-flex justify-content-end">
      <form hx-post="/components/customer-addresses/new.php" hx-target="#customer-addresses-body" hx-swap="beforeend">
        <input type="hidden" name="customer_id" value="<?php echo htmlspecialchars($customer_id); ?>">
        <button class="btn btn-primary">New</button>
      </form>
    </div>

  <?php } ?>
</div>