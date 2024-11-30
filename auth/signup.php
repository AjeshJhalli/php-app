<?php

if (isset($_SESSION['logged_in'])) {
  header('Location: /');
  die();
}

$dbconn = pg_connect("user=postgres.wjucgknzgympnnywamjy password=" . getenv("PGPASSWORD") . " host=aws-0-eu-west-2.pooler.supabase.com port=6543 dbname=postgres") or die('Could not connect: ' . pg_last_error());

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

            $query = "INSERT INTO app_user (username, password_hash) VALUES ($1, $2) RETURNING id";
            $params = [$username, $hash];
            $result = pg_query_params($dbconn, $query, $params);

            if ($result) {

              $line = pg_fetch_assoc($result);

              session_start();

              $_SESSION['logged_in'] = true;
              $_SESSION['username'] = $username;
              $_SESSION['id'] = $line['id'];
              $_SESSION['last_ping'] = time();
              $_SESSION['expiry'] = $_SESSION['last_ping'] + 30 * 86400;

              header('Location: /');
              pg_close($dbconn);
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
    Already have an account? <a href="/auth/signin.php">Sign In</a>
  </main>
</body>

</html>