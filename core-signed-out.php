<?php

session_start();

$url_parts = explode('?', $_SERVER['REQUEST_URI']);
$url_path = $url_parts[0];

if (isset($_SESSION['logged_in'])) {
  header('Location: /home.php');
  die();
}

if ($url_path !== "/auth/signin.php" && $url_path !== "/auth/signup.php") {
  header('Location: /');
  die();
}

$db_path = "sqlite:" .  __DIR__ . "/database/codecost.sqlite";

try {
  $db = new \PDO($db_path);
} catch (\PDOException $e) {
  die();
}

?>