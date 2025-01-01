<?php

$url_parts = explode('?', $_SERVER['REQUEST_URI']);
$url_path = $url_parts[0];

session_start();

if (!isset($_SESSION['logged_in']) && $url_path != "/auth/signin.php") {
  header('Location: /auth/signin.php');
  die();
}

$dbconn = pg_connect("user=postgres.wjucgknzgympnnywamjy password=" . getenv("PGPASSWORD") . " host=aws-0-eu-west-2.pooler.supabase.com port=6543 dbname=postgres") or die('Could not connect: ' . pg_last_error());

$query = "INSERT INTO project_line_item (name, user_id, customer_id, project_id) 
  VALUES ($1, $2, $3, $4) 
  RETURNING id, name, hours_logged, status";

$params = [
  "",
  $_SESSION["id"],
  (int)$_POST["customer_id"],
  (int)$_POST["project_id"]
];

$result = pg_query_params($dbconn, $query, $params);

if ($result) {
  $row = pg_fetch_assoc($result);
  $line_item_id = $row['id'];
} else {
  pg_close($dbconn);
  die();
}

pg_close($dbconn);

// The hourly rate can be 0 on new items because the initial time logged will be 0.
$hourly_rate = 0;

include "../functions/format_currency.php";
include "../projects/project-line-item-row.php";

?>
