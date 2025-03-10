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
$email = $_POST["email_address"];

$stmt = $db->prepare("UPDATE email_address SET email_address = ? WHERE user_id = ? AND id = ?");
$stmt->execute([$email, $_SESSION["id"], $email_id]);

