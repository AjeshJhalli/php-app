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
  $query = "SELECT project.id AS project_id, project.name AS project_name, customer_id, customer.name AS customer_name FROM project INNER JOIN customer ON customer_id = customer.id WHERE project.user_id = $1 AND (project.name ILIKE $2 OR customer.name ILIKE $2)";
  $params = [$_SESSION['id'], $search];
  $stmt = pg_prepare($dbconn, "", $query);
  $result = pg_execute($dbconn, "", $params);
} else {
  $query = "SELECT project.id AS project_id, project.name AS project_name, customer_id, customer.name AS customer_name FROM project INNER JOIN customer ON customer_id = customer.id WHERE project.user_id = $1";
  $params = [$_SESSION['id']];
  $stmt = pg_prepare($dbconn, "", $query);
  $result = pg_execute($dbconn, "", $params);
}

if (!$result) {
  die('Query failed: ' . pg_last_error());
}

while ($line = pg_fetch_array($result, null, PGSQL_ASSOC)) { ?>
  <tr onclick="window.location.href = '/projects/project.php?id=<?php echo htmlspecialchars($line["project_id"]); ?>'">
    <td>
      <?php echo htmlspecialchars($line['project_name']); ?>
    </td>
    <td>
      <?php echo htmlspecialchars($line['customer_name']); ?>
    </td>
  </tr>
<?php }

pg_free_result($result);
pg_close($dbconn);
