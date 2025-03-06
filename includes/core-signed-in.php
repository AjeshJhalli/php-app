<?php

session_start();

$url_parts = explode('?', $_SERVER['REQUEST_URI']);
$url_path = $url_parts[0];

if (!isset($_SESSION['logged_in'])) {
  header('Location: /signin.php');
  die();
}

if ($url_path === "/auth/signin.php" || $url_path === "/auth/signup.php") {
  header('Location: /home.php');
  die();
}

try {
  $db = new \PDO("sqlite:./database/codecost.sqlite");
} catch (\PDOException $e) {
  die();
}

?>