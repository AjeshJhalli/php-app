<?php
session_start();
if (!isset($_SESSION['logged_in'])) {
  header('Location: /auth/signin.php');
  die();
}

$dbconn = pg_connect("user=postgres.wjucgknzgympnnywamjy password=" . getenv("PGPASSWORD") . " host=aws-0-eu-west-2.pooler.supabase.com port=6543 dbname=postgres") or die('Could not connect: ' . pg_last_error());

$url_parts = explode('?', $_SERVER['REQUEST_URI']);
$url_path = $url_parts[0];

if ($url_path == '/customers/customer') {

  $customer_id = $_GET['id'];

  if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    pg_update($dbconn, 'customer', array('name' => $name), array('id' => $customer_id));
    header('Location: ?id=' . $customer_id);
    die();
  } else if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
    pg_delete($dbconn, 'customer', array('id' => $customer_id));
    header('HX-Location: /customers');
    die();
  }

  $query = 'SELECT name FROM customer WHERE id = ' . $customer_id;
  $result = pg_query($dbconn, $query) or die('Query failed: ' . pg_last_error());

  if (!($line = pg_fetch_row($result, null, PGSQL_ASSOC))) {
    http_response_code(404);
    die();
  }

  pg_free_result($result);
  pg_close($dbconn);
} else if ($url_path == '/customers') {

  $new = isset($_GET['mode']) && $_GET['mode'] === 'new';

  if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    pg_insert($dbconn, "customer", ["name" => $_POST["name"], "user_id" => $_SESSION["id"]]);
    pg_close($dbconn);
    header("Location: /customers");
    die();
  }
} else {

  if ($_SERVER['PHP_SELF'] !== '/index.php') {
    http_response_code(404);
    die();
  }
}
?>

<!DOCTYPE html>
<html>
<?php include_once './components/head.php'; ?>

<body>
  <?php include_once './components/navbar.php'; ?>
  <main class="container my-5">
    <?php
    if ($url_path == '/customers/customer') {
    ?>
      <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
          <li class="breadcrumb-item"><a href="/customers">Customers</a></li>
          <li class="breadcrumb-item active" aria-current="page"><?php echo $line["name"]; ?></li>
        </ol>
      </nav>
      <div class="btn-group" role="group">
        <a class="btn btn-outline-primary" href="?id=<?php echo $customer_id ?>&mode=edit">Edit</a>
        <button class="btn btn-outline-primary" hx-delete="" hx-confirm="Are you sure you want to delete this customer?">
          Delete
        </button>
      </div>
      <h2 class="pt-4"><?php echo $line['name'] ?></h2>
      <?php if (isset($_GET['mode']) && $_GET['mode'] === 'edit') { ?>
        <form method="POST">
          <label>
            Name:
            <input type="text" name="name" value="<?php echo $line['name'] ?>">
          </label>
          <a href="?id=<?php echo $customer_id ?>">Cancel</a>
          <button>Save</button>
        </form>
      <?php } else { ?>
        <p>
          <span>Name: <?php echo $line["name"]; ?></span>
        </p>
        <ul class="nav nav-tabs">
          <li class="nav-item ">
            <a class="nav-link disabled" aria-current="page" href="#">Email Addresses</a>
          </li>
          <li class="nav-item">
            <a class="nav-link disabled" href="#">Phone Numbers</a>
          </li>
          <a class="nav-link disabled" href="#">Addresses</a>
          </li>
          <li class="nav-item">
            <a class="nav-link active" hx-get="/components/customer-projects.php" hx-target="#tab-content">Projects</a>
          </li>
          <li class="nav-item">
            <a class="nav-link" hx-get="/components/customer-invoices.php" hx-target="#tab-content">Invoices</a>
          </li>
          <li class="nav-item">
        </ul>
        <div id="tab-content">
          <?php include_once './components/customer-projects.php'; ?>
        </div>
      <?php }
    } elseif ($url_path == '/customers') {
      ?>

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
        $query = "SELECT id, name FROM customer WHERE user_id = " . $_SESSION['id'];
        $result = pg_query($dbconn, $query) or die('Query failed: ' . pg_last_error());

        echo "<div class='list-group'>";
        while ($line = pg_fetch_array($result, null, PGSQL_ASSOC)) {

          echo "<a class='list-group-item list-group-item-action' href='/customers/customer?id={$line['id']}'>{$line['name']}</a>";
        }
        echo "</div>";

        pg_free_result($result);
        pg_close($dbconn);
      }
      ?>

    <?php
    } else {
    ?>
      <h1>Home</h1>
      <p>
        Welcome to the app.
      </p>
    <?php
    }
    ?>
  </main>
</body>

</html>