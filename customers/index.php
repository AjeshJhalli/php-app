<?php
session_start();
if (!isset($_SESSION['logged_in'])) {
  header('Location: /auth/signin.php');
  die();
}

$new = isset($_GET['mode']) && $_GET['mode'] === 'new';

$dbconn = pg_connect("user=postgres.wjucgknzgympnnywamjy password=" . getenv("PGPASSWORD") . " host=aws-0-eu-west-2.pooler.supabase.com port=6543 dbname=postgres") or die('Could not connect: ' . pg_last_error());

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  pg_insert($dbconn, "customer", ["name" => $_POST["name"]]);
  pg_close($dbconn);
  header("Location: /customers");
  die();
}
?>

<!DOCTYPE html>
<html>

<?php include_once '../components/head.php'; ?>

<body>

  <?php include_once '../components/navbar.php'; ?>
  <h1>
    <a href="/customers">Customers</a>
    <?php if ($new) { ?> > <a href="">New</a><?php } ?>
  </h1>

  <?php if ($new) { ?>
    <form method="POST" action="">
      <label>
        Name:
        <input type="text" name="name" value="">
      </label>
      <a href="">Cancel</a>
      <button>Save</button>
    </form>
  <?php } else { ?>
    <a href="?mode=new">New</a>
  <?php

    $query = 'SELECT id, name FROM customer';
    $result = pg_query($dbconn, $query) or die('Query failed: ' . pg_last_error());

    echo "<ul>";
    while ($line = pg_fetch_array($result, null, PGSQL_ASSOC)) {
      echo "<li>";
      echo "<a href='/customers/customer.php?id={$line['id']}'>{$line['name']}</a>";
      echo "</li>";
    }
    echo "</ul>";

    pg_free_result($result);
    pg_close($dbconn);
  }
  ?>
</body>

</html>