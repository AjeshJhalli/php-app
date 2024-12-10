<?php

include "../functions/format_currency.php";

$url_parts = explode('?', $_SERVER['REQUEST_URI']);
$url_path = $url_parts[0];

session_start();

if (!isset($_SESSION['logged_in']) && $url_path != "/auth/signin.php") {
  header('Location: /auth/signin.php');
  die();
}

$dbconn = pg_connect("user=postgres.wjucgknzgympnnywamjy password=" . getenv("PGPASSWORD") . " host=aws-0-eu-west-2.pooler.supabase.com port=6543 dbname=postgres") or die('Could not connect: ' . pg_last_error());

$project_id = $_GET['id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $name = $_POST["name"];
  $hourly_rate = $_POST["hourly_rate"];
  pg_update($dbconn, "project", array("name" => $name, "hourly_rate" => $hourly_rate), array('id' => $project_id));
  header('Location: ?id=' . $project_id);
  die();
} elseif ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
  pg_delete($dbconn, "project", array('id' => $project_id));
  header('HX-Location: /projects.php');
  die();
}

$query = 'SELECT project.name AS project_name, customer.name AS customer_name, customer_id, hourly_rate FROM project INNER JOIN customer ON customer_id = customer.id WHERE project.id = ' . $project_id;
$result = pg_query($dbconn, $query) or die('Query failed: ' . pg_last_error());

if (!($line = pg_fetch_row($result, null, PGSQL_ASSOC))) {
  http_response_code(404);
  die();
}

$customer_id = $line["customer_id"];

$hourly_rate = number_format((float)htmlspecialchars($line["hourly_rate"]), 2, '.', '');

pg_free_result($result);

?>

<!DOCTYPE html>
<html>
<?php include "../head.html" ?>

<body>
  <script>
    function toggleCheckboxes(mainCheckbox) {
      const checkboxes = document.getElementsByClassName('line-item-checkbox');
      let checkedCount = 0;
      for (let i = 0; i < checkboxes.length; i++) {
        checkboxes[i].checked = mainCheckbox.checked;
        checkedCount++;
      }

      const buttonInvoice = document.getElementById('button-invoice');
      buttonInvoice.hidden = !mainCheckbox.checked;

      if (!buttonInvoice.hidden) {
        buttonInvoice.innerText = `Invoice Selected Items (${checkedCount})`;
      }
    }

    function toggleCheckbox() {
      const checkboxes = document.getElementsByClassName('line-item-checkbox');

      let checkedCount = 0;
      for (let i = 0; i < checkboxes.length; i++) {
        if (checkboxes[i].checked) {
          checkedCount++;
        }
      }

      document.getElementById('line-item-head-checkbox').checked = checkedCount && true;

      const buttonInvoice = document.getElementById('button-invoice');
      buttonInvoice.hidden = !checkedCount;

      if (!buttonInvoice.hidden) {
        buttonInvoice.innerText = `Invoice Selected Items (${checkedCount})`;
      }
    }

    function invoiceSelectedLineItems() {

      const selectedIds = $('.line-item-checkbox:checked')
        .map(function() {
          return $(this).closest('tr').find('input[name="item_id"]').val();
        })
        .get();

      const searchParams = new URLSearchParams(window.location.search);
      const projectId = searchParams.get('id');

      console.log(projectId);
      console.log(selectedIds);

      fetch("/api/create-invoice-from-project.php", {
          method: "POST",
          headers: {
            'Content-Type': 'application/x-www-form-urlencoded'
          },
          body: new URLSearchParams({
            project_id: projectId,
            item_ids: selectedIds.join(',')
          }),
          credentials: 'include',
          redirect: 'follow'
        }).then(data => data.text())
        .then(id => {
          window.location.href = `/invoices/invoice.php?id=${id}`;
        });

    }
  </script>
  <?php
  $uri_path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
  $uri_segments = explode('/', $uri_path);
  include "../nav.php";
  ?>
  <main class="container my-5">
    <nav aria-label="breadcrumb">
      <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="/projects.php">Projects</a></li>
        <li class="breadcrumb-item active" aria-current="page"><?php echo htmlspecialchars($line["project_name"]); ?></li>
      </ol>
    </nav>
    <div class="btn-group" role="group">
      <a class="btn btn-primary" href="?id=<?php echo htmlspecialchars($project_id) ?>&mode=edit">Edit</a>
      <button class="btn btn-primary" hx-delete="" hx-confirm="Are you sure you want to delete this project?">
        Delete
      </button>
    </div>
    <h2 class="py-4"><?php echo htmlspecialchars($line['project_name']) ?></h2>
    <?php if (isset($_GET['mode']) && $_GET['mode'] === 'edit') { ?>
      <form method="POST">
        <div>
          <label class="form-label">
            Project Name:
            <input class="form-control" type="text" name="name" value="<?php echo htmlspecialchars($line['project_name']) ?>">
          </label>
        </div>
        <div>
          <label class="form-label">
            Hourly Rate:
            <input class="form-control" type="number" step=".01" name="hourly_rate" value="<?php echo htmlspecialchars($line["hourly_rate"]) ?>">
          </label>
        </div>
        <a class="btn btn-primary" href="?id=<?php echo htmlspecialchars($project_id) ?>">Cancel</a>
        <button class="btn btn-primary">Save</button>
      </form>
    <?php } else { ?>
      <div class="d-flex justify-content-between pb-4 align-items-start">
        <div>
          <div>
            Customer:
            <a href="/customers/customer.php?id=<?php echo htmlspecialchars($line["customer_id"]); ?>"><?php echo htmlspecialchars($line["customer_name"]) ?></a>
          </div>
          <div>
            Hourly Rate: £<?php echo $hourly_rate ?>
          </div>
        </div>
        <button id="button-invoice" class="btn btn-secondary" hidden onclick="invoiceSelectedLineItems()">Invoice Selected Items</button>
      </div>
      <table class="table">
        <thead>
          <tr>
            <th><input type="checkbox" id="line-item-head-checkbox" onchange="toggleCheckboxes(this)"></th>
            <th>
              Item
            </th>
            <th>
              Status
            </th>
            <th style="text-align: right; width: 300px;">
              Hours Logged
            </th>
            <th style="text-align: right;">
              Amount
            </th>
            <th>
              Invoice
            </th>
            <th>
            </th>
          </tr>
        </thead>
        <tbody id="project-line-items-tbody">
          <?php
          $query = "SELECT id, name, status, hours_logged FROM project_line_item WHERE user_id = $1 AND project_id = $2 ORDER BY created_at ASC";
          $params = [$_SESSION["id"], $project_id];
          $result = pg_query_params($dbconn, $query, $params);

          while ($row = pg_fetch_row($result, null, PGSQL_ASSOC)) { ?>
            <tr>
              <td>
                <input class="line-item-checkbox" type="checkbox" name="line-item-<?php echo htmlspecialchars($row["id"]) ?>-checkbox" onchange="toggleCheckbox()">
              </td>
              <td><input type="hidden" name="item_id" value="<?php echo htmlspecialchars($row["id"]) ?>"><input class="form-control" name="item_name" value="<?php echo htmlspecialchars($row["name"]) ?>" hx-post="/project-line-item/name.php" hx-trigger="keyup changed delay:500ms" hx-include="previous input"></td>
              <td><input type="hidden" name="item_id" value="<?php echo htmlspecialchars($row["id"]) ?>">
                <select class="form-select" name="item_status" hx-post="/project-line-item/status.php" hx-swap="none" hx-include="previous input">
                  <option value="To Do" <?php if ($row["status"] == "To Do") echo "selected" ?>>
                    To Do
                  </option>
                  <option value="In Progress" <?php if ($row["status"] == "In Progress") echo "selected" ?>>
                    In Progress
                  </option>
                  <option value="Testing" <?php if ($row["status"] == "Testing") echo "selected" ?>>
                    Testing
                  </option>
                  <option value="Done" <?php if ($row["status"] == "Done") echo "selected" ?>>
                    Done
                  </option>
                  <option value="Blocked" <?php if ($row["status"] == "Blocked") echo "selected" ?>>
                    Blocked
                  </option>
                </select>
              </td>
              <td class="d-flex justify-content-end" style="width: 300px;"><input type="hidden" name="item_id" value="<?php echo htmlspecialchars($row["id"]) ?>"><input class="form-control" style="width: 100px;" type="number" name="hours_logged" value="<?php echo htmlspecialchars($row["hours_logged"]) ?>" hx-post="/project-line-item/hours_logged.php" hx-trigger="keyup changed delay:200ms, change changed delay:200ms" hx-include="closest td" hx-target="next td"><input type="hidden" name="hourly_rate" value="<?php echo htmlspecialchars($hourly_rate); ?>"></td>
              <td id="line-item-<?php echo htmlspecialchars($row["id"]) ?>" align="right">
                <?php echo format_currency($hourly_rate * $row["hours_logged"]); ?>
              </td>
              <td>
              </td>
              <td>
                <div class="dropdown">
                  <button class="btn btn-secondary dropdown-toggle" type="button" id="dropdownMenuButton1" data-bs-toggle="dropdown" aria-expanded="false">
                    Options
                  </button>
                  <ul class="dropdown-menu">
                    <li><input type="hidden" name="item_id" value="<?php echo htmlspecialchars($row["id"]); ?>"><a class="dropdown-item" hx-delete="/project-line-item/delete.php?id=<?php echo htmlspecialchars($row["id"]); ?>" hx-include="previous input" hx-target="closest tr" hx-swap="outerHTML" hx-confirm="Are you sure you want to delete this line item?">Delete</a></li>
                  </ul>
                </div>
              </td>
            </tr>
          <?php } ?>
        </tbody>
      </table>
      <div class="d-flex justify-content-end">
        <form hx-post="/components/project-line-items.php" hx-target="#project-line-items-tbody" hx-swap="beforeend">
          <input type="hidden" name="customer_id" value="<?php echo htmlspecialchars($customer_id); ?>">
          <input type="hidden" name="project_id" value="<?php echo htmlspecialchars($project_id); ?>">
          <button class="btn btn-primary">New</button>
        </form>
      </div>
    <?php } ?>
  </main>
</body>

</html>