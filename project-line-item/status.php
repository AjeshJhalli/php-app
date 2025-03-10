<?php

if ($_SERVER["REQUEST_METHOD"] != "POST") {
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
$item_id = $_POST["item_id"];
$status = $_POST["item_status"];

$stmt = $db->prepare("UPDATE project_line_item SET status = ? WHERE user_id = ? AND id = ?");
$stmt->execute([$status, $user_id, $item_id]);
