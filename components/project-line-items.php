<?php

session_start();

if (!isset($_SESSION['logged_in'])) {
  http_response_code(401);
  die();
}

try {
  $db = new \PDO("sqlite:../database/codecost.sqlite");
} catch (\PDOException $e) {
  die();
}

$stmt = $db->prepare("INSERT INTO project_line_item (name, user_id, customer_id, project_id, description, status) VALUES (?, ?, ?, ?, ?, ?) RETURNING id, name, hours_logged, status, description");

$stmt->execute([
  "",
  $_SESSION["id"],
  (int)$_POST["customer_id"],
  (int)$_POST["project_id"],
  "",
  "To Do"
]);

$row = $stmt->fetch();

if ($row) {
  $line_item_id = $row['id'];
} else {
  die();
}

// The hourly rate can be 0 on new items because the initial time logged will be 0.
$hourly_rate = 0;

include "../functions/format_currency.php";
include "../projects/project-line-item-row.php";

?>
