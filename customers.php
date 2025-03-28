<?php

include "./core-signed-in.php";

$new = isset($_GET['mode']) && $_GET['mode'] === 'new';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $stmt = $db->prepare("INSERT INTO customer (name, user_id) VALUES (?, ?)");
  $stmt->execute([$_POST["name"], $_SESSION["id"]]);
  header("Location: /customers.php");
  die();
}

?>

<!DOCTYPE html>
<html>
<?php include "./head.html" ?>
<body>
  <?php include "./nav.php"; ?>
  <main class="container my-5">
    <?php if ($new) { ?>
      <h1 class="mb-4">New Customer</h1>
      <form method="POST">
        <div>
          <label class="form-label">
            Name:
            <input class="form-control" type="text" name="name" value="" required>
          </label>
        </div>
        <div>
          <a class="btn btn-primary" href="/customers.php">Cancel</a>
          <button class="btn btn-primary">Save</button>
        </div>
      </form>
    <?php } else { ?>
      <h1 class="mb-4">Customers</h1>
      <div class="d-flex justify-content-between align-items-center mb-3">
        <form class="form-inline my-2 my-lg-0 d-flex" hx-get="/components/customer-list.php" hx-target="#customer-list" hx-trigger="load, input changed delay:500ms, search">
          <input class="form-control" name="search" type="search" placeholder="Search" aria-label="Search">
        </form>
        <a class="btn btn-primary" href="?mode=new">New</a>
      </div>
      <div id="customer-list" class="list-group"></div>
    <?php } ?>
  </main>
</body>

</html>