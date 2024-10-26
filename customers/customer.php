<?php
session_start();
if (!isset($_SESSION['logged_in'])) {
  header('Location: /auth/signin.php');
  exit;
}
?>
<!DOCTYPE html>
<html>

<head>
  <title>Software Developer Costing Xero Sync</title>
  <script src="https://unpkg.com/htmx.org@2.0.3"></script>
</head>

<body>
  <?php include_once '../components/navbar.php'; ?>
  <?php
  $customer_id = $_GET['id'];
  ?>
  <h1>
    <a href="/customers">Customers</a>
    >
    <?php

    $customer_id = $_GET['id'];

    $dbconn = pg_connect("user=postgres.wjucgknzgympnnywamjy password=" . getenv("PGPASSWORD") . " host=aws-0-eu-west-2.pooler.supabase.com port=6543 dbname=postgres") or die('Could not connect: ' . pg_last_error());

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
      $name = $_POST['name'];
      pg_update($dbconn, 'customer', array('name' => $name), array('id' => $customer_id));

      header('Location: ?id=' . $customer_id);
      die();
    } else if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
      
      pg_delete($dbconn, 'customer', array('id'=> $customer_id));

      header('HX-Location: /customers');
      die();
    }

    $query = 'SELECT name FROM customer WHERE id = ' . $customer_id;
    $result = pg_query($dbconn, $query) or die('Query failed: ' . pg_last_error());

    if ($line = pg_fetch_row($result, null, PGSQL_ASSOC)) {
      echo "<a href='/customers/customer.php?id={$customer_id}'>{$line["name"]}</a>";
    } else {
      echo "Error - Could not find customer";
      http_response_code(404);
      die();
    }

    pg_free_result($result);
    pg_close($dbconn);

    ?>
  </h1>
  <a href="?id=<?php echo $customer_id ?>&mode=edit">Edit</a>
  <button hx-delete="" hx-confirm="Are you sure you want to delete this customer?">
    Delete
  </button>
  <h2>Profile</h2>
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
    <h2>Projects</h2>
    <ul>
      <li>Project 1</li>
      <li>Project 1</li>
      <li>Project 1</li>
    </ul>
    <h2>Invoices</h2>
    <table>
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
</body>

</html>