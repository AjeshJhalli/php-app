<?php

$url_parts = explode('?', $_SERVER['REQUEST_URI']);
$url_path = $url_parts[0];

session_start();

if (!isset($_SESSION['logged_in']) && $url_path != "/auth/signin.php") {
  header('Location: /auth/signin.php');
  die();
}

$dbconn = pg_connect("user=postgres.wjucgknzgympnnywamjy password=" . getenv("PGPASSWORD") . " host=aws-0-eu-west-2.pooler.supabase.com port=6543 dbname=postgres") or die('Could not connect: ' . pg_last_error());

if ($_SERVER["REQUEST_METHOD"] != "POST") {
  http_response_code(405);
  die();
}

$item_id = $_POST["item_id"];
$hours_logged = $_POST["hours_logged"];
$hourly_rate = $_POST["hourly_rate"];

if (!$hours_logged) {
  $hours_logged = 0;
}

$query = "UPDATE project_line_item SET hours_logged = $1 WHERE user_id = $2 AND id = $3";
$params = [$hours_logged, $_SESSION["id"], $item_id];
$result = pg_query_params($dbconn, $query, $params);

pg_close($dbconn);

echo "£" . number_format((float)($hours_logged * $hourly_rate), 2, '.', '');
