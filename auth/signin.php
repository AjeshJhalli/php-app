<?php include "../core-signed-out.php" ?>

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

        $stmt = $db->prepare("SELECT id, password_hash FROM app_user WHERE username = ?");
        $stmt->execute([$username]);
        $line = $stmt->fetch();

        if ($line) {
          if (password_verify($password, $line['password_hash'])) {

            session_regenerate_id();
            $_SESSION['logged_in'] = true;
            $_SESSION['username'] = $username;
            $_SESSION['id'] = $line['id'];
            $_SESSION['last_ping'] = time();
            $_SESSION['expiry'] = $_SESSION['last_ping'] + 30 * 86400;

            header("Location: /home.php");
            die();
          }
        }

        echo '<div class="mb-2">Incorrect username or password</div>';

      }
      ?>
      <button class="btn btn-primary my-2">Sign In</button>
    </form>
    Don't have an account yet? <a href="/auth/signup.php">Sign Up</a>

  </main>
</body>

</html>