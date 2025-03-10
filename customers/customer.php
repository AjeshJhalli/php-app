<?php

include "../core-signed-in.php";

$user_id = $_SESSION["id"];
$customer_id = $_GET["id"];

if ($_SERVER["REQUEST_METHOD"] === "POST") {

  $name = $_POST['name'];
  $statement_update_customer = $db->prepare("UPDATE customer SET name = ? WHERE id = ? AND user_id = ?");
  $statement_update_customer->execute([$name, $customer_id, $user_id]);
  header('Location: ?id=' . $customer_id);
  die();

} elseif ($_SERVER["REQUEST_METHOD"] === "DELETE") {

  $statement_delete_customer = $db->prepare("DELETE FROM customer WHERE id = ? AND user_id = ?");
  $statement_delete_customer->execute([$customer_id, $user_id]);
  header('HX-Location: /customers.php');
  die();

}

$stmt = $db->prepare("SELECT name FROM customer WHERE id = ? AND user_id = ?");
$stmt->execute([$customer_id, $user_id]);

if (!($line = $stmt->fetch())) {
  http_response_code(404);
  die();
}

?>

<!DOCTYPE html>
<html>

<?php include "../head.html" ?>

<body>
  <?php include "../nav.php"; ?>
  <main class="container my-5">
    <div class="d-flex align-items-center justify-content-between">
      <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
          <li class="breadcrumb-item"><a href="/customers.php">Customers</a></li>
          <li class="breadcrumb-item active" aria-current="page"><?php echo htmlspecialchars($line["name"]); ?></li>
        </ol>
      </nav>
      <?php
      $url_edit = "?id=$customer_id&mode=edit";
      $url_delete = "";
      $delete_confirmation = "Are you sure you want to delete this customer?";
      include '../templates/template_view_actions.php';
      ?>
    </div>

    <h2 class="py-4"><?php echo htmlspecialchars($line['name']) ?></h2>
    <?php if (isset($_GET['mode']) && $_GET['mode'] === 'edit') { ?>
      <form method="POST">
        <div>
          <label class="form-label">
            Name:
            <input class="form-control" type="text" name="name" value="<?php echo htmlspecialchars($line['name']) ?>" required>
          </label>
        </div>
        <a class="btn btn-primary" href="?id=<?php echo htmlspecialchars($customer_id); ?>">Cancel</a>
        <button class="btn btn-primary">Save</button>
      </form>
    <?php } else { ?>
      <div id="customer-tabs" hx-get="/components/customer-tabs.php?tab=email-addresses&customer_id=<?php echo htmlspecialchars($customer_id); ?>" hx-trigger="load"></div>
    <?php } ?>
  </main>
</body>

</html>