<?php

$url_parts = explode('?', $_SERVER['REQUEST_URI']);
$url_path = $url_parts[0];

session_start();

if (!isset($_SESSION['logged_in']) && $url_path != "/auth/signin.php") {
  header('Location: /auth/signin.php');
  die();
}

$dbconn = pg_connect("user=postgres.wjucgknzgympnnywamjy password=" . getenv("PGPASSWORD") . " host=aws-0-eu-west-2.pooler.supabase.com port=6543 dbname=postgres") or die('Could not connect: ' . pg_last_error());

$tab = $_GET["tab"];
$customer_id = $_GET['customer_id'];

?>
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
    <a class="nav-link <?php if ($tab == "projects") echo 'active' ?>" id="customer-projects-tab" hx-get="/components/customer-tabs.php?tab=projects&customer_id=<?php echo $customer_id; ?>" hx-target="#customer-tabs">Projects</a>
  </li>
  <li class="nav-item">
    <a class="nav-link <?php if ($tab == "invoices") echo 'active' ?>" id="customer-invoices-tab" hx-get="/components/customer-tabs.php?tab=invoices&customer_id=<?php echo $customer_id; ?>" hx-target="#customer-tabs">Invoices</a>
  </li>
  <li class="nav-item">
</ul>
<div id="tab-content">
  <?php
  if ($tab == "projects") {

    $query = "SELECT id, name FROM project WHERE customer_id = " . $_GET["customer_id"] . " AND user_id = '" . $_SESSION['id'] . "'";
    $result = pg_query($dbconn, $query) or die('Query failed: ' . pg_last_error());

  ?>
    <div class="list-group mt-2">
      <?php
      while ($line = pg_fetch_array($result, null, PGSQL_ASSOC)) {
        echo "<a class='list-group-item list-group-item-action' href='/projects/project.php?id={$line['id']}'>{$line['name']}</a>";
      }
      ?>
    </div>
  <?php } else if ($tab == "invoices") {
    echo "the invoices";
  } ?>
</div>