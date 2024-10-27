<?php
session_start();
if (!isset($_SESSION['logged_in'])) {
  header('Location: /auth/signin.php');
  die();
}

$customer_id = $_GET['id'];

$dbconn = pg_connect("user=postgres.wjucgknzgympnnywamjy password=" . getenv("PGPASSWORD") . " host=aws-0-eu-west-2.pooler.supabase.com port=6543 dbname=postgres") or die('Could not connect: ' . pg_last_error());

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
  echo "Error - Could not find customer";
  die();
}

pg_free_result($result);
pg_close($dbconn);

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
      <li class="breadcrumb-item active" aria-current="page"><?php echo $line["name"]; ?></li>
    </ol>
  </nav>
  <div class="btn-group" role="group">
  <a class="btn btn-outline-primary" href="?id=<?php echo $customer_id ?>&mode=edit">Edit</a>
  <button class="btn btn-outline-primary" hx-delete="" hx-confirm="Are you sure you want to delete this customer?">
    Delete
  </button>
  </div>

  <h2><?php echo $line['name'] ?></h2>
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
    <h3>Projects</h3>
    <ul>
      <li>Project 1</li>
      <li>Project 1</li>
      <li>Project 1</li>
    </ul>
    <h3>Invoices</h3>
    <table class="table">
      <thead>
        <tr>
          <th>Number</th>
          <th>Date</th>
          <th>Status</th>
          <th>Amount</th>
          <th>Balance</th>
        </tr>
      </thead>
      <tbody>
        <tr>
          <td>
            INV-001
          </td>
          <td>
            12/11/2024
          </td>
          <td>
            PAID
          </td>
          <td>
            £100.00
          </td>
          <td>
            £100.00
          </td>
        </tr>
      </tbody>
    </table>
  <?php } ?>
  </main>
</body>

</html>