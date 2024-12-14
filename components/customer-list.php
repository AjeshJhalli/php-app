<?php

include "../config.php";

session_start();

$url_parts = explode('?', $_SERVER['REQUEST_URI']);
$url_path = $url_parts[0];
$user_id = $_SESSION['id'];

if (!isset($_SESSION['logged_in']) && $url_path != "/auth/signin.php") {
  header('Location: /auth/signin.php');
  die();
}

$dbconn = pg_connect($db_connection_string) or die('Could not connect: ' . pg_last_error());

if (isset($_GET['search'])) {
  $search = '%' . pg_escape_string($dbconn, $_GET["search"]) . '%';
  $query = "SELECT id, name FROM customer WHERE user_id = $1 AND name ILIKE $2";
  $params = [$user_id, $search];
  $stmt = pg_prepare($dbconn, "", $query);
  $result = pg_execute($dbconn, "", $params);
} else {
  $query = "SELECT id, name FROM customer WHERE user_id = $1";
  $result = pg_query_params($dbconn, $query, [$user_id]) or die('Query failed: ' . pg_last_error());
}

if (!$result) {
  die('Query failed: ' . pg_last_error());
}

while ($line = pg_fetch_array($result, null, PGSQL_ASSOC)) { ?>
  <a class="list-group-item list-group-item-action" href="/customers/customer.php?id=<?php echo htmlspecialchars($line["id"]); ?>">
    <?php echo htmlspecialchars($line['name']); ?>
  </a>
  <?php
}

pg_free_result($result);
pg_close($dbconn);
die();
