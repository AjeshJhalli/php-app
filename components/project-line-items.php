<?php

$url_parts = explode('?', $_SERVER['REQUEST_URI']);
$url_path = $url_parts[0];

session_start();

if (!isset($_SESSION['logged_in']) && $url_path != "/auth/signin.php") {
  header('Location: /auth/signin.php');
  die();
}

$dbconn = pg_connect("user=postgres.wjucgknzgympnnywamjy password=" . getenv("PGPASSWORD") . " host=aws-0-eu-west-2.pooler.supabase.com port=6543 dbname=postgres") or die('Could not connect: ' . pg_last_error());

$query = "INSERT INTO project_line_item (name, user_id, customer_id, project_id) 
  VALUES ($1, $2, $3, $4) 
  RETURNING id, name";

$params = [
  "",
  $_SESSION["id"],
  (int)$_POST["customer_id"],
  (int)$_POST["project_id"]
];

$result = pg_query_params($dbconn, $query, $params);

if ($result) {
  $row = pg_fetch_assoc($result);
  $line_item_id = $row['id'];
} else {
  pg_close($dbconn);
  die();
}

pg_close($dbconn);

?>

<tr>
<td><input type="hidden" name="item_id" value="<?php echo htmlspecialchars($row["id"]) ?>"><input class="form-control" name="item_name" value="<?php echo htmlspecialchars($row["name"]) ?>" hx-post="/project-line-item/name.php" hx-trigger="keyup changed delay:500ms" hx-include="previous input"></td>
  <td>
    <select class="form-select">
      <option selected>
        To Do
      </option>
      <option>
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
  <td>0</td>
  <td>£0.00</td>
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