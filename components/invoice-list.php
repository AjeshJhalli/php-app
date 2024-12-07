<?php

include "../config.php";

$url_parts = explode('?', $_SERVER['REQUEST_URI']);
$url_path = $url_parts[0];

session_start();

if (!isset($_SESSION['logged_in']) && $url_path != "/auth/signin.php") {
  header('HX-Location: /auth/signin.php');
  die();
}

$dbconn = pg_connect($db_connection_string) or die('Could not connect: ' . pg_last_error());

$query = "
  SELECT sale.id AS id, sale.status AS status, customer.name AS customer_name, project.name AS project_name
  FROM sale
  LEFT JOIN project
  ON project.id = sale.project_id
  LEFT JOIN customer
  ON customer.id = sale.customer_id
  WHERE sale.user_id = $1
";

$params = [$_SESSION['id']];
$stmt = pg_prepare($dbconn, "", $query);
$result = pg_execute($dbconn, "", $params);

if (!$result) {
  die('Query failed: ' . pg_last_error());
}

while ($line = pg_fetch_array($result, null, PGSQL_ASSOC)) { ?>
  <tr onclick="window.location.href = '/invoices/invoice.php?id=<?php echo $line["id"]; ?>'">
    <td>
      <?php echo $line['id']; ?>
    </td>
    <td>
      <?php echo $line['customer_name']; ?>
    </td>
    <td>
      <?php echo $line['project_name']; ?>
    </td>
    <td>
      <?php echo $line['status']; ?>
    </td>
    <td>
      100
    </td>
  </tr>
<?php }

pg_free_result($result);
pg_close($dbconn);
die();
