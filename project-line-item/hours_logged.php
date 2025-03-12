<?php

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
  http_response_code(405);
  die();
}

include "../functions/format_currency.php";

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
$item_id = $_POST["item_id"];
$hours_logged = $_POST["hours_logged"];
$hourly_rate = $_POST["hourly_rate"];

if (!$hours_logged) {
  $hours_logged = 0;
}

$stmt = $db->prepare("UPDATE project_line_item SET hours_logged = ? WHERE user_id = ? AND id = ?");
$stmt->execute([$hours_logged, $user_id, $item_id]);

echo format_currency($hours_logged * $hourly_rate);
