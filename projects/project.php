<?php

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

pg_free_result($result);

?>

<!DOCTYPE html>
<html>
<?php include "../head.html" ?>
<body>
  <?php
  $uri_path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
  $uri_segments = explode('/', $uri_path);
  include "../nav.php";
  ?>
  <main class="container my-5">
    <nav aria-label="breadcrumb">
      <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="/projects.php">Projects</a></li>
        <li class="breadcrumb-item active" aria-current="page"><?php echo $line["project_name"]; ?></li>
      </ol>
    </nav>
    <div class="btn-group" role="group">
      <a class="btn btn-outline-primary" href="?id=<?php echo $project_id ?>&mode=edit">Edit</a>
      <button class="btn btn-outline-primary" hx-delete="" hx-confirm="Are you sure you want to delete this project?">
        Delete
      </button>
    </div>
    <h2 class="py-4"><?php echo $line['project_name'] ?></h2>
    <?php if (isset($_GET['mode']) && $_GET['mode'] === 'edit') { ?>
      <form method="POST">
        <div>
          <label class="form-label">
            Project Name:
            <input class="form-control" type="text" name="name" value="<?php echo $line['project_name'] ?>">
          </label>
        </div>
        <div>
          <label class="form-label">
            Hourly Rate:
            <input class="form-control" type="number" step=".01" name="hourly_rate" value="<?php echo $line["hourly_rate"] ?>">
          </label>
        </div>
        <a class="btn btn-primary" href="?id=<?php echo $project_id ?>">Cancel</a>
        <button class="btn btn-primary">Save</button>
      </form>
    <?php } else { ?>
      <div>
        Customer:
        <a href="/customers/customer.php?id=<?php echo $line["customer_id"]; ?>"><?php echo $line["customer_name"] ?></a>
      </div>
      <div>
        Hourly Rate: £<?php echo number_format((float)$line["hourly_rate"], 2, '.', ''); ?>
      </div>
      <table class="table">
        <thead>
          <tr>
            <th>
              Item
            </th>
            <th>
              Status
            </th>
            <th>
              Hours Logged
            </th>
            <th>
              Amount
            </th>
            <th>
            </th>
          </tr>
        </thead>
        <tbody id="project-line-items-tbody">
          <?php
          $query = "SELECT id, name, status FROM project_line_item WHERE user_id = $1 AND project_id = $2 ORDER BY created_at ASC";
          $params = [$_SESSION["id"], $project_id];
          $result = pg_query_params($dbconn, $query, $params);

          while ($row = pg_fetch_row($result, null, PGSQL_ASSOC)) { ?>
            <tr>
              <td><input type="hidden" name="item_id" value="<?php echo $row["id"] ?>"><input class="form-control" name="item_name" value="<?php echo $row["name"] ?>" hx-post="/project-line-item/name.php" hx-trigger="keyup changed delay:500ms" hx-include="previous input"></td>
              <td>
                <select class="form-select" name="status">
                  <option <?php if ($row["status"] == "To Do") echo "selected" ?>>
                    To Do
                  </option>
                  <option <?php if ($row["status"] == "In Progress") echo "selected" ?>>
                    In Progress
                  </option>
                  <option>
                    Testing
                  </option>
                  <option>
                    Done
                  </option>
                  <option>
                    Blocked
                  </option>
                </select>
              </td>
              <td>
                14.5
              </td>
              <td>
                £362.50
              </td>
              <td>
                <div class="dropdown">
                  <button class="btn btn-secondary dropdown-toggle" type="button" id="dropdownMenuButton1" data-bs-toggle="dropdown" aria-expanded="false">
                    Options
                  </button>
                  <ul class="dropdown-menu">
                    <li><a class="dropdown-item" href="#">Add payment</a></li>
                    <li><a class="dropdown-item" href="#">Log time</a></li>
                    <li><a class="dropdown-item" href="#">Delete</a></li>
                  </ul>
                </div>
              </td>
            </tr>
          <?php } ?>
        </tbody>
      </table>
      <div class="d-flex justify-content-end">
        <form hx-post="/components/project-line-items.php" hx-target="#project-line-items-tbody" hx-swap="afterend">
          <input type="hidden" name="customer_id" value="<?php echo $customer_id; ?>">
          <input type="hidden" name="project_id" value="<?php echo $project_id; ?>">
          <button class="btn btn-primary">New</button>
        </form>
      </div>
    <?php } ?>
  </main>
</body>

</html>