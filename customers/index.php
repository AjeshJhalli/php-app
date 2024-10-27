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

  <main class="container">
    <nav aria-label="breadcrumb">
      <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="/customers">Customers</a></li>
        <?php if ($new) { ?><li class="breadcrumb-item active" aria-current="page"> <a href="">New</a></li><?php } ?>
      </ol>
    </nav>

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
      <a class="btn btn-primary" href="?mode=new">New</a>
    <?php

      $query = 'SELECT id, name FROM customer';
      $result = pg_query($dbconn, $query) or die('Query failed: ' . pg_last_error());

      echo "<div class='list-group'>";
      while ($line = pg_fetch_array($result, null, PGSQL_ASSOC)) {
        
        echo "<a class='list-group-item list-group-item-action' href='/customers/customer.php?id={$line['id']}'>{$line['name']}</a>";
        
      }
      echo "</div>";

      pg_free_result($result);
      pg_close($dbconn);
    }
    ?>
  </main>


</body>

</html>