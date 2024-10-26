<?php
  session_start();
  if (isset($_SESSION['logged_in'])) {
    header('Location: /');
    die();
  }
?>
<!DOCTYPE html>
<html>

<head>
  <title>Software Developer Costing Xero Sync</title>
</head>

<body>
  <h1>Software Developer Costing Xero Sync</h1>
  <h2>Sign Up</h2>
  <form method="POST">
    <div>
      <label>
        Username: <input type="text" name="username">
      </label>
    </div>
    <div>
      <label>
        Password: <input type="password" name="password">
      </label>
    </div>
    <div>
      <label>
        Confirm Password: <input type="password" name="confirm-password">
      </label>
    </div>

    <ul>
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
        $query = "SELECT username FROM app_user WHERE username = '" . $username . "'";
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
            "password_hash"=> $hash,
          );

          $result = pg_insert($dbconn, "app_user", $user);

          if ($result) {
            echo "Sign up successful";
          } else {
            echo "Could not sign up";
          }          
        }

        pg_close($dbconn);
      }
      ?>
    </ul>
    <button>Sign Up</button>
  </form>
  Already have an account? <a href="signin.php">Sign In</a>
</body>

</html>