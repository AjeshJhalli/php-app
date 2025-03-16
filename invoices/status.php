<?php

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
  http_response_code(405);
  die();
}

session_start();

if (!isset($_SESSION['logged_in'])) {
  die();
}

try {
  $db = new \PDO("sqlite:../database/codecost.sqlite");
} catch (\PDOException $e) {
  die();
}

$user_id = $_SESSION["id"];
$invoice_id = $_POST["sale_id"];
$status = $_POST["sale_status"];

$stmt = $db->prepare("UPDATE sale SET status = $1 WHERE user_id = ? AND id = ?");
$stmt->execute([$status, $user_id, $invoice_id]);
