<?php

if ($_SERVER["REQUEST_METHOD"] != "POST") {
  http_response_code(405);
  die();
}

session_start();

if (!isset($_SESSION["logged_in"])) {
  header('Location: /auth/signin.php');
  die();
}

$db_path = "sqlite:../../database/codecost.sqlite";

try {
  $db = new \PDO($db_path);
} catch (\PDOException $e) {
  echo $e;
  http_response_code(500);
  die();
}

$email_id = $_POST["email_id"];
$label = $_POST["email_label"];

$stmt = $db->prepare("UPDATE email_address SET label = ? WHERE user_id = ? AND id = ?");
$stmt->execute([$label, $_SESSION["id"], $email_id]);

