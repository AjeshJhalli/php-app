<?php

$url_parts = explode('?', $_SERVER['REQUEST_URI']);
$url_path = $url_parts[0];

session_start();

if (!isset($_SESSION['logged_in']) && $url_path !== "/auth/signin.php") {
  die();
}

$dbconn = pg_connect("user=postgres.wjucgknzgympnnywamjy password=" . getenv("PGPASSWORD") . " host=aws-0-eu-west-2.pooler.supabase.com port=6543 dbname=postgres") or die('Could not connect: ' . pg_last_error());

$query = "INSERT INTO email_address (label, email_address, default_flag, user_id, customer_id) 
  VALUES ($1, $2, $3, $4, $5) 
  RETURNING id";

$params = [
  "",
  "",
  "FALSE",
  $_SESSION["id"],
  (int)$_POST["customer_id"]
];

$result = pg_query_params($dbconn, $query, $params);

if ($result) {
  $row = pg_fetch_assoc($result);
  $email_id = $row["id"];
} else {
  pg_close($dbconn);
  die();
}

pg_close($dbconn);

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