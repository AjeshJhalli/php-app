<?php

if ($_SERVER["REQUEST_METHOD"] !== "DELETE") {
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

$user_id = (int)$_SESSION["id"];
$item_id = (int)$_GET["id"];

$stmt = $db->prepare("DELETE FROM project_line_item WHERE user_id = ? AND id = ?");
$stmt->execute([$user_id, $item_id]);

header("HX-Refresh: true");
