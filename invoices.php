<?php

include "./core-signed-in.php";

$new = isset($_GET['mode']) && $_GET['mode'] === 'new';

?>

<!DOCTYPE html>
<html>
<?php include "./head.html" ?>

<body>
  <?php include "./nav.php"; ?>
  <main class="container my-5">
    <h1 class="mb-4">Invoices</h1>
    <div class="d-flex justify-content-between align-items-center mb-3">
        <form class="form-inline my-2 my-lg-0 d-flex" hx-get="/components/invoice-list.php" hx-target="#project-tbody" hx-trigger="load, input changed delay:500ms, search">
          <input class="form-control" name="search" type="search" placeholder="Search" aria-label="Search">
        </form>
      </div>
      <table class="table border mt-2 table-hover">
        <thead>
          <tr>
            <th scope="col" style="text-align: right; padding-right: 40px; width: 200px;">Invoice Number</th>
            <th scope="col">Customer</th>
            <th scope="col">Project</th>
            <th scope="col">Status</th>
            <th scope="col" style="text-align: right; padding-right: 40px; width: 200px;">Amount</th>
          </tr>
        </thead>
        <tbody id="project-tbody">
        </tbody>
      </table>
  </main>
</body>

</html>