<?php

session_start();

if (isset($_SESSION['logged_in'])) {
  header('Location: /home.php');
  die();
}

$dbconn = pg_connect("user=postgres.wjucgknzgympnnywamjy password=" . getenv("PGPASSWORD") . " host=aws-0-eu-west-2.pooler.supabase.com port=6543 dbname=postgres") or die('Could not connect: ' . pg_last_error());

?>

<!DOCTYPE html>
<html>
<?php include "../head.html" ?>

<body>
  <?php include "../nav.php"; ?>
  <main class="container my-5">
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

            header("Location: /home.php");
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
    Don't have an account yet? <a href="/auth/signup.php">Sign Up</a>

  </main>
</body>

</html>