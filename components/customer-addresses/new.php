<?php

session_start();

if (!isset($_SESSION['logged_in'])) {
  die();
}

$query = "INSERT INTO address (line1, line2, city, county, country, postcode, user_id, customer_id) 
  VALUES (?, ?, ?, ?, ?, ?, ?, ?) 
  RETURNING id";

$params = [
  "",
  "",
  "",
  "",
  "",
  "",
  $_SESSION["id"],
  (int)$_POST["customer_id"]
];

$result = pg_query_params($dbconn, $query, $params);

if ($result) {
  $row = pg_fetch_assoc($result);
  $address_id = $row["id"];
} else {
  pg_close($dbconn);
  die();
}

pg_close($dbconn);

?>

<tr>
  <td>
    <input type="hidden" name="address_id" value="<?php echo htmlspecialchars($row["id"]) ?>">
    <input class="form-control" name="line1" value="" hx-post="/components/customer-addresses/line1.php" hx-trigger="keyup changed delay:500ms" hx-include="previous input">
  </td>
  <td>
    <input type="hidden" name="address_id" value="<?php echo htmlspecialchars($row["id"]) ?>">
    <input class="form-control" name="line2" value="" hx-post="/components/customer-addresses/line2.php" hx-trigger="keyup changed delay:500ms" hx-include="previous input">
  </td>
  <td>
    <input type="hidden" name="address_id" value="<?php echo htmlspecialchars($row["id"]) ?>">
    <input class="form-control" name="city" value="" hx-post="/components/customer-addresses/city.php" hx-trigger="keyup changed delay:500ms" hx-include="previous input">
  </td>
  <td>
    <input type="hidden" name="address_id" value="<?php echo htmlspecialchars($row["id"]) ?>">
    <input class="form-control" name="county" value="" hx-post="/components/customer-addresses/county.php" hx-trigger="keyup changed delay:500ms" hx-include="previous input">
  </td>
  <td>
    <input type="hidden" name="address_id" value="<?php echo htmlspecialchars($row["id"]) ?>">
    <input class="form-control" name="country" value="" hx-post="/components/customer-addresses/country.php" hx-trigger="keyup changed delay:500ms" hx-include="previous input">
  </td>
  <td>
    <input type="hidden" name="address_id" value="<?php echo htmlspecialchars($row["id"]) ?>">
    <input class="form-control" name="postcode" value="" hx-post="/components/customer-addresses/postcode.php" hx-trigger="keyup changed delay:500ms" hx-include="previous input">
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