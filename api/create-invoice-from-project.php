<?php

session_start();

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
  http_response_code(405);
  die();
}

if (!isset($_SESSION['logged_in'])) {
  die();
}

try {
  $db = new \PDO("sqlite:../database/codecost.sqlite");
} catch (\PDOException $e) {
  die();
}

// We need the project ID and line item IDs
$user_id = $_SESSION["id"];
$project_id = $_POST["project_id"];
$item_ids = explode(',', $_POST["item_ids"]);

// Get customer ID
$stmt = $db->prepare("SELECT customer_id, hourly_rate FROM project WHERE id = ? AND user_id = ?");
$stmt->execute([$project_id, $user_id]);
$row = $stmt->fetch();
$customer_id = $row[0];
$hourly_rate = $row[1];

$stmt = $db->prepare("SELECT id FROM address WHERE customer_id = ? AND user_id = ? LIMIT 1");
$stmt->execute([$customer_id, $user_id]);
$row = $stmt->fetch();

// if (!$row) {
//   http_response_code(400);
//   die("Customer does not have an address");
// }

$address_id = $row[0];

// Create invoice
$stmt = $db->prepare("INSERT INTO sale (project_id, customer_id, user_id, status, customer_address_id) VALUES (?, ?, ?, ?, ?) RETURNING id");
$stmt->execute([$project_id, $customer_id, $_SESSION["id"], "DRAFT", $address_id]);
$row = $stmt->fetch();
$invoice_id = $row["id"];

// Get project line items
$id_list = implode(', ', array_map('intval', $item_ids));
$stmt = $db->prepare("SELECT name, hours_logged FROM project_line_item WHERE id IN (?)");
$stmt->execute([$id_list]);

// Create the invoice line items
foreach ($stmt as $row) {
  $stmt = $db->prepare("INSERT INTO sale_line_item (name, quantity, unit_amount, user_id, sale_id) VALUES (?, ?, ?, ?, ?)");
  $stmt->execute([$row["name"], $row["hours_logged"], $hourly_rate, $user_id, $invoice_id]);
}
