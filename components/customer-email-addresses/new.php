<?php

session_start();

if (!isset($_SESSION['logged_in'])) {
  die();
}

$db_path = "sqlite:../../database/codecost.sqlite";

try {
  $db = new \PDO($db_path);
} catch (\PDOException $e) {
  echo $e;
  die();
}

$stmt = $db->prepare("INSERT INTO email_address (label, email_address, default_flag, user_id, customer_id) 
  VALUES ($1, $2, $3, $4, $5) 
  RETURNING id");

$stmt->execute([
  "",
  "",
  "FALSE",
  $_SESSION["id"],
  (int)$_POST["customer_id"]
]);

$row = $stmt->fetch();

if ($row) {
  $email_id = $row["id"];
} else {
  die();
}

?>

<tr>
  <td><input type="hidden" name="email_id" value="<?php echo htmlspecialchars($row["id"]) ?>"><input class="form-control" name="email_label" value="" hx-post="/components/customer-email-addresses/label.php" hx-trigger="keyup changed delay:500ms" hx-include="previous input"></td>
  <td><input type="hidden" name="email_id" value="<?php echo htmlspecialchars($row["id"]) ?>"><input class="form-control" name="email_address" value="" hx-post="/components/customer-email-addresses/email-address.php" hx-trigger="keyup changed delay:500ms" hx-include="previous input"></td>
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