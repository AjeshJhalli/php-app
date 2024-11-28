<?php

$url_parts = explode('?', $_SERVER['REQUEST_URI']);
$url_path = $url_parts[0];

session_start();

if ($url_path == "/auth/signout") {
  session_destroy();
  header('Location: /auth/signin');
  die();
}

if (isset($_SESSION['logged_in']) && $url_path == "/auth/signup") {
  header('Location: /');
  die();
} elseif (!isset($_SESSION['logged_in']) && $url_path == "/auth/signup") {
} elseif (!isset($_SESSION['logged_in']) && $url_path != "/auth/signin") {
  header('Location: /auth/signin');
  die();
} elseif (isset($_SESSION['logged_in']) && $url_path == "/auth/signin") {
  header('Location: /');
  die();
}

$dbconn = pg_connect("user=postgres.wjucgknzgympnnywamjy password=" . getenv("PGPASSWORD") . " host=aws-0-eu-west-2.pooler.supabase.com port=6543 dbname=postgres") or die('Could not connect: ' . pg_last_error());

if ($url_path == '/customers/customer') {

  $customer_id = $_GET['id'];

  if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    pg_update($dbconn, 'customer', array('name' => $name), array('id' => $customer_id));
    header('Location: ?id=' . $customer_id);
    die();
  } elseif ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
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
} elseif ($url_path == '/projects/project') {

  $project_id = $_GET['id'];

  if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST["name"];
    $hourly_rate = $_POST["hourly_rate"];
    pg_update($dbconn, "project", array("name" => $name, "hourly_rate" => $hourly_rate), array('id' => $project_id));
    header('Location: ?id=' . $project_id);
    die();
  } elseif ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
    pg_delete($dbconn, "project", array('id' => $project_id));
    header('HX-Location: /projects');
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
} else if ($url_path == '/customers') {

  $new = isset($_GET['mode']) && $_GET['mode'] === 'new';

  if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    pg_insert($dbconn, "customer", ["name" => $_POST["name"], "user_id" => $_SESSION["id"]]);
    pg_close($dbconn);
    header("Location: /customers");
    die();
  }
} else if ($url_path == '/auth/signin') {
} elseif ($url_path == '/auth/signup') {
} elseif ($url_path == "/components/customer-invoices") {
  echo 'invoices <a class="nav-link active" id="customer-invoices-tab" hx-get="/components/customer-invoices" hx-target="#tab-content" hx-swap-oob="true">Invoices</a>';
  die();
} elseif ($url_path == "/components/customer-projects") {
  die();
} elseif ($url_path == "/components/customer-tabs") {

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
      <a class="nav-link <?php if ($tab == "projects") echo 'active' ?>" id="customer-projects-tab" hx-get="/components/customer-tabs?tab=projects&customer_id=<?php echo $customer_id; ?>" hx-target="#customer-tabs">Projects</a>
    </li>
    <li class="nav-item">
      <a class="nav-link <?php if ($tab == "invoices") echo 'active' ?>" id="customer-invoices-tab" hx-get="/components/customer-tabs?tab=invoices&customer_id=<?php echo $customer_id; ?>" hx-target="#customer-tabs">Invoices</a>
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
          echo "<a class='list-group-item list-group-item-action' href='/projects/project?id={$line['id']}'>{$line['name']}</a>";
        }
        ?>
      </div>
    <?php
    } else if ($tab == "invoices") {
      echo "the invoices";
    }
    ?>
  </div>
  <?php

  die();
} elseif ($url_path == "/projects") {

  $new = isset($_GET['mode']) && $_GET['mode'] === 'new';

  if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $result = pg_insert($dbconn, "project", ["name" => $_POST["name"], "user_id" => $_SESSION["id"], "customer_id" => $_POST["customer_id"], "hourly_rate" => $_POST["hourly_rate"]]);
    pg_close($dbconn);
    header("Location: /projects");
    die();
  }
} elseif ($url_path == "/components/customer-list") {

  if (isset($_GET['search'])) {
    $search = '%' . pg_escape_string($dbconn, $_GET["search"]) . '%';
    $query = "SELECT id, name FROM customer WHERE user_id = $1 AND name ILIKE $2";
    $params = [$_SESSION['id'], $search];
    $stmt = pg_prepare($dbconn, "", $query);
    $result = pg_execute($dbconn, "", $params);
  } else {
    $query = "SELECT id, name FROM customer WHERE user_id = " . $_SESSION['id'];
    $result = pg_query($dbconn, $query) or die('Query failed: ' . pg_last_error());
  }

  if (!$result) {
    die('Query failed: ' . pg_last_error());
  }

  while ($line = pg_fetch_array($result, null, PGSQL_ASSOC)) {
    echo "<a class='list-group-item list-group-item-action' href='/customers/customer?id={$line['id']}'>{$line['name']}</a>";
  }

  pg_free_result($result);
  pg_close($dbconn);
  die();
} elseif ($url_path == "/components/project-list") {

  if (isset($_GET['search'])) {
    $search = '%' . pg_escape_string($dbconn, $_GET["search"]) . '%';
    $query = "SELECT project.id AS project_id, project.name AS project_name, customer_id, customer.name AS customer_name FROM project INNER JOIN customer ON customer_id = customer.id WHERE project.user_id = $1 AND (project.name ILIKE $2 OR customer.name ILIKE $2)";
    $params = [$_SESSION['id'], $search];
    $stmt = pg_prepare($dbconn, "", $query);
    $result = pg_execute($dbconn, "", $params);
  } else {
    $query = "SELECT project.id AS project_id, project.name AS project_name, customer_id, customer.name AS customer_name FROM project INNER JOIN customer ON customer_id = customer.id WHERE project.user_id = $1";
    $params = [$_SESSION['id']];
    $stmt = pg_prepare($dbconn, "", $query);
    $result = pg_execute($dbconn, "", $params);
  }

  if (!$result) {
    die('Query failed: ' . pg_last_error());
  }

  while ($line = pg_fetch_array($result, null, PGSQL_ASSOC)) { ?>
    <tr onclick="window.location.href = '/projects/project?id=<?php echo $line["project_id"]; ?>'">
      <td>
        <?php echo $line['project_name']; ?>
      </td>
      <td>
        <?php echo $line['customer_name']; ?>
      </td>
    </tr>
<?php }

  pg_free_result($result);
  pg_close($dbconn);
  die();
} elseif ($url_path == "/components/project-line-items") {
  include "./new-line-item.php";
  die();
} elseif ($url_path == "/project-line-item/name") {
  include "./project-line-item-name.php";
  die();
} elseif ($_SERVER['PHP_SELF'] !== '/index.php') {
  http_response_code(404);
  die();
}

?>

<!DOCTYPE html>
<html>

<head>
  <title>Cost Cost</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
  <script src="https://unpkg.com/htmx.org@2.0.3"></script>
  <script src="https://code.jquery.com/jquery-3.7.1.min.js" integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>
  <link href="https://gitcdn.github.io/bootstrap-toggle/2.2.2/css/bootstrap-toggle.min.css" rel="stylesheet">
  <script src="https://gitcdn.github.io/bootstrap-toggle/2.2.2/js/bootstrap-toggle.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>

</head>

<body>
  <?php
  $uri_path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
  $uri_segments = explode('/', $uri_path);
  ?>
  <nav class="navbar navbar-expand-lg bg-light border-bottom shadow-sm">
    <div class="container-fluid">
      <a class="navbar-brand fw-bold text-primary" href="/">
        <i class="bi bi-house-door-fill"></i>Code Cost
      </a>

      <button
        class="navbar-toggler"
        type="button"
        data-bs-toggle="collapse"
        data-bs-target="#navbarSupportedContent"
        aria-controls="navbarSupportedContent"
        aria-expanded="false"
        aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
      </button>
      <?php if (isset($_SESSION['logged_in'])) { ?>
        <div class="collapse navbar-collapse" id="navbarSupportedContent">

          <ul class="navbar-nav me-auto mb-2 mb-lg-0">
            <li class="nav-item">
              <a class="nav-link d-flex align-items-center <?php if ($uri_segments[1] === "") {
                                                              echo "active";
                                                            } ?>" href="/">
                <i class="bi bi-house"></i> <span class="ms-1">Home</span>
              </a>
            </li>
            <li class="nav-item">
              <a class="nav-link d-flex align-items-center <?php if ($uri_segments[1] === "customers") {
                                                              echo "active";
                                                            } ?>" aria-current="page" href="/customers">
                <i class="bi bi-people"></i><span class="ms-1">Customers</span>
              </a>
            </li>
            <li class="nav-item">
              <a class="nav-link d-flex align-items-center <?php if ($uri_segments[1] === "projects") {
                                                              echo "active";
                                                            } ?>" aria-current="page" href="/projects">
                <i class="bi bi-people"></i><span class="ms-1">Projects</span>
              </a>
            </li>
          </ul>


          <ul class="navbar-nav ms-auto mb-2 mb-lg-0">
            <li class="nav-item d-flex align-items-center me-3">
              <span class="text-secondary">
                <i class="bi bi-person-circle"></i> Logged in as:
                <strong><?= htmlspecialchars($_SESSION['username'], ENT_QUOTES) ?></strong>
              </span>
            </li>
            <li class="nav-item">
              <form action="/auth/signout" class="d-inline">
                <button type="submit" class="btn btn-outline-secondary">
                  <i class="bi bi-box-arrow-right"></i> Sign out
                </button>
              </form>
            </li>
          </ul>

        </div>
      <?php } ?>
    </div>
  </nav>
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
      <h2 class="py-4"><?php echo $line['name'] ?></h2>
      <?php if (isset($_GET['mode']) && $_GET['mode'] === 'edit') { ?>
        <form method="POST">
          <div>
            <label class="form-label">
              Name:
              <input class="form-control" type="text" name="name" value="<?php echo $line['name'] ?>">
            </label>
          </div>
          <a class="btn btn-primary" href="?id=<?php echo $customer_id ?>">Cancel</a>
          <button class="btn btn-primary">Save</button>
        </form>
      <?php } else { ?>
        <div id="customer-tabs" hx-get="/components/customer-tabs?tab=projects&customer_id=<?php echo $customer_id; ?>" hx-trigger="load"></div>
      <?php }
    } elseif ($url_path == '/customers') {
      if ($new) { ?>
        <h1 class="mb-4">New Customer</h1>
        <form method="POST">
          <div>
            <label class="form-label">
              Name:
              <input class="form-control" type="text" name="name" value="">
            </label>
          </div>
          <div>
            <a class="btn btn-primary" href="/customers">Cancel</a>
            <button class="btn btn-primary">Save</button>
          </div>
        </form>
      <?php } else { ?>
        <h1 class="mb-4">Customers</h1>

        <div class="d-flex justify-content-between align-items-center mb-3">
          <form class="form-inline my-2 my-lg-0 d-flex" hx-get="/components/customer-list" hx-target="#customer-list" hx-trigger="load, input changed delay:500ms, search">
            <input class="form-control" name="search" type="search" placeholder="Search" aria-label="Search">
          </form>
          <a class="btn btn-primary" href="?mode=new">New</a>
        </div>

        <div id="customer-list" class="list-group"></div>

      <?php
      }
    } elseif ($uri_path == "/auth/signin") {
      ?>
      <h1>Sign In</h1>
      <form class="needs-validation" method="POST">
        <div>
          <label class="form-label">
            Username: <input class="form-control" type="text" name="username">
          </label>
        </div>
        <div>
          <label class="form-label">
            Password: <input class="form-control" type="password" name="password">
          </label>
        </div>
        <?php
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {

          $username = strtolower($_POST["username"]);
          $password = $_POST["password"];

          $dbconn = pg_connect("user=postgres.wjucgknzgympnnywamjy password=" . getenv("PGPASSWORD") . " host=aws-0-eu-west-2.pooler.supabase.com port=6543 dbname=postgres") or die('Could not connect: ' . pg_last_error());
          $query = "SELECT id, password_hash FROM app_user WHERE username = '" . $username . "'";
          $result = pg_query($dbconn, $query);

          if ($line = pg_fetch_row($result, null, PGSQL_ASSOC)) {
            if (password_verify($password, $line['password_hash'])) {

              session_regenerate_id();
              $_SESSION['logged_in'] = true;
              $_SESSION['username'] = $username;
              $_SESSION['id'] = $line['id'];
              $_SESSION['last_ping'] = time();
              $_SESSION['expiry'] = $_SESSION['last_ping'] + 30 * 86400;

              header("Location: /");
              pg_free_result($result);
              pg_close($dbconn);
              die();
            }
          }

          echo '<div class="mb-2">Incorrect username or password</div>';

          pg_free_result($result);
          pg_close($dbconn);
        }
        ?>
        <button class="btn btn-primary my-2">Sign In</button>
      </form>
      Don't have an account yet? <a href="/auth/signup">Sign Up</a>
    <?php } elseif ($url_path == "/auth/signup") { ?>
      <h1>Sign Up</h1>
      <form class="needs-validation" method="POST">
        <div>
          <label class="form-label">
            Username: <input class="form-control" type="text" name="username">
          </label>
        </div>
        <div>
          <label class="form-label">
            Password: <input class="form-control" type="password" name="password">
          </label>
        </div>
        <div>
          <label class="form-label">
            Confirm Password: <input class="form-control" type="password" name="confirm-password">
          </label>
        </div>

        <ul class="m-0 p-0">
          <?php
          if ($_SERVER['REQUEST_METHOD'] === 'POST') {

            $username = strtolower($_POST["username"]);
            $password = $_POST["password"];
            $confirm_password = $_POST["confirm-password"];

            if ($username === "") {
              echo "<li>Please enter a username</li>";
            } else if (!ctype_alnum($username)) {
              echo "<li>Usernames can only contain letters (A-Z) and digits</li>";
            }
            if ($password === "") {
              echo "<li>Please enter a password</li>";
            }
            if ($confirm_password === "") {
              echo "<li>Please confirm your password</li>";
            }

            if ($password !== $confirm_password) {
              echo "<li>Password and confirmation password do not match</li>";
            }

            // Now check if username already exists
            $dbconn = pg_connect("user=postgres.wjucgknzgympnnywamjy password=" . getenv("PGPASSWORD") . " host=aws-0-eu-west-2.pooler.supabase.com port=6543 dbname=postgres") or die('Could not connect: ' . pg_last_error());
            $query = "SELECT id, username FROM app_user WHERE username = '" . $username . "'";
            $result = pg_query($dbconn, $query);

            $username_exists = false;

            if ($line = pg_fetch_row($result, null, PGSQL_ASSOC)) {
              $username_exists = true;
              echo "<li>Username already exists</li>";
            }

            pg_free_result($result);

            if ($username && $password && $confirm_password && ctype_alnum($username) && $password === $confirm_password && !$username_exists) {

              $hash = password_hash($password, null);

              $user = array(
                "username" => $username,
                "password_hash" => $hash,
              );

              $result = pg_insert($dbconn, "app_user", $user);

              if ($result) {

                session_regenerate_id();
                $_SESSION['logged_in'] = true;
                $_SESSION['username'] = $username;
                $_SESSION['id'] = $line['id'];
                $_SESSION['last_ping'] = time();
                $_SESSION['expiry'] = $_SESSION['last_ping'] + 30 * 86400;

                header('Location: /');
                die();
              } else {
                echo "Could not sign up";
              }
            }

            pg_close($dbconn);
          }
          ?>
        </ul>
        <button class="btn btn-primary my-2">Sign Up</button>
      </form>
      Already have an account? <a href="/auth/signin">Sign In</a>
    <?php } elseif ($url_path == "/projects") { ?>
      <h1 class="mb-4">Projects</h1>

      <?php if ($new) { ?>

        <form method="POST">
          <div>
            <label class="form-label">
              Customer:
              <select class="form-select" name="customer_id">
                <option selected disabled>Customer</option>
                <?php

                $query = "SELECT id, name FROM customer WHERE user_id = '" . $_SESSION["id"] . "' ";
                $result = pg_query($dbconn, $query) or die("Query failed: " . pg_last_error());

                while (($line = pg_fetch_row($result, null, PGSQL_ASSOC))) {
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
            <a class="btn btn-primary" href="/projects">Cancel</a>
            <button class="btn btn-primary">Save</button>
          </div>
        </form>

      <?php } else {
      ?>
        <div class="d-flex justify-content-between align-items-center mb-3">
          <form class="form-inline my-2 my-lg-0 d-flex" hx-get="/components/project-list" hx-target="#project-tbody" hx-trigger="load, input changed delay:500ms, search">
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


      <?php }
    } elseif ($url_path == "/projects/project") { ?>
      <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
          <li class="breadcrumb-item"><a href="/projects">Projects</a></li>
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
          <a href="/customers/customer?id=<?php echo $line["customer_id"]; ?>"><?php echo $line["customer_name"] ?></a>
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
                <td><input type="hidden" name="item_id" value="<?php echo $row["id"] ?>"><input class="form-control" name="item_name" value="<?php echo $row["name"] ?>" hx-post="/project-line-item/name" hx-trigger="keyup changed delay:500ms" hx-include="previous input"></td>
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
          <form hx-post="/components/project-line-items" hx-target="#project-line-items-tbody" hx-swap="afterend">
            <input type="hidden" name="customer_id" value="<?php echo $customer_id; ?>">
            <input type="hidden" name="project_id" value="<?php echo $project_id; ?>">
            <button class="btn btn-primary">New</button>
          </form>
        </div>
      <?php }
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