<?php
  session_start();
  if (isset($_SESSION['logged_in'])) {
    header('Location: /');
    die();
  }
?>
<!DOCTYPE html>
<html>
<?php include_once '../components/head.php'; ?>
<body>
  <h1>Software Developer Costing Xero Sync</h1>
  <h2>Sign In</h2>
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

      echo "<div>Incorrect username or password</div>";

      pg_free_result($result);
      pg_close($dbconn);
    }
    ?>
    <button>Sign In</button>
  </form>
  Don't have an account yet? <a href="signup.php">Sign Up</a>
</body>

</html>