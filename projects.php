<?php

include "./core-signed-in.php";

$new = isset($_GET['mode']) && $_GET['mode'] === 'new';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

  $stmt = $db->prepare("INSERT INTO project (name, user_id, customer_id, hourly_rate) VALUES (?, ?, ?, ?)");
  $stmt->execute([$_POST["name"], $_SESSION["id"], $_POST["customer_id"], $_POST["hourly_rate"]]);
  header("Location: /projects.php");
  die();
  
}

?>

<!DOCTYPE html>
<html>

<?php include "./head.html" ?>

<body>
  <?php include "./nav.php"; ?>
  <main class="container my-5">

    <h1 class="mb-4">Projects</h1>

    <?php if ($new) { ?>

      <form method="POST">
        <div>
          <label class="form-label">
            Customer:
            <select class="form-select" name="customer_id">
              <option selected disabled>Customer</option>
              <?php

              $stmt = $db->prepare("SELECT id, name FROM customer WHERE user_id = ?");
              $stmt->execute([$user_id]);

              foreach ($stmt as $line) {
                echo "<option value='" . $line["id"] . "'>" . $line["name"] . "</option>";
              }

              ?>
            </select>
          </label>
        </div>
        <div>
          <label class="form-label">
            Project Name:
            <input class="form-control" type="text" name="name">
          </label>
        </div>
        <div>
          <label class="form-label">
            Hourly Rate:
            <input class="form-control" type="number" step=".01" name="hourly_rate">
          </label>
        </div>
        <div>
          <a class="btn btn-primary" href="/projects.php">Cancel</a>
          <button class="btn btn-primary">Save</button>
        </div>
      </form>
    <?php } else { ?>
      <div class="d-flex justify-content-between align-items-center mb-3">
        <form class="form-inline my-2 my-lg-0 d-flex" hx-get="/components/project-list.php" hx-target="#project-tbody" hx-trigger="load, input changed delay:500ms, search">
          <input class="form-control" name="search" type="search" placeholder="Search" aria-label="Search">
        </form>
        <a class="btn btn-primary" href="?mode=new">New</a>
      </div>
      <table class="table border mt-2 table-hover">
        <thead>
          <tr>
            <th scope="col">Project Name</th>
            <th scope="col">Customer</th>
          </tr>
        </thead>
        <tbody id="project-tbody">
        </tbody>
      </table>
    <?php } ?>
  </main>
</body>

</html>