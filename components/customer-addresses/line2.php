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

$address_id = $_POST["address_id"];
$line2 = $_POST["line2"];

$query = "UPDATE address SET line2 = $1 WHERE user_id = $2 AND id = $3";
$params = [$line2, $_SESSION["id"], $address_id];
$result = pg_query_params($dbconn, $query, $params);

pg_close($dbconn);
