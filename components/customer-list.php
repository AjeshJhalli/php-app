<?php

$url_parts = explode('?', $_SERVER['REQUEST_URI']);
$url_path = $url_parts[0];

session_start();

if (!isset($_SESSION['logged_in']) && $url_path != "/auth/signin.php") {
  header('Location: /auth/signin.php');
  die();
}

$dbconn = pg_connect("user=postgres.wjucgknzgympnnywamjy password=" . getenv("PGPASSWORD") . " host=aws-0-eu-west-2.pooler.supabase.com port=6543 dbname=postgres") or die('Could not connect: ' . pg_last_error());

if (isset($_GET['search'])) {
  $search = '%' . pg_escape_string($dbconn, $_GET["search"]) . '%';
  $query = "SELECT id, name FROM customer WHERE user_id = $1 AND name ILIKE $2";
  $params = [$_SESSION['id'], $search];
  $stmt = pg_prepare($dbconn, "", $query);
  $result = pg_execute($dbconn, "", $params);
} else {
  $query = "SELECT id, name FROM customer WHERE user_id = " . $_SESSION['id'];
  $result = pg_query($dbconn, $query) or die('Query failed: ' . pg_last_error());
}

if (!$result) {
  die('Query failed: ' . pg_last_error());
}

while ($line = pg_fetch_array($result, null, PGSQL_ASSOC)) {
  echo "<a class='list-group-item list-group-item-action' href='/customers/customer.php?id={$line['id']}'>{$line['name']}</a>";
}

pg_free_result($result);
pg_close($dbconn);
die();
