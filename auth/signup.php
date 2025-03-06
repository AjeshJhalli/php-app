<?php include "../includes/core-signed-in.php" ?>

<!DOCTYPE html>
<html>

<?php include "../head.html" ?>

<body>
  <?php include "../nav.php"; ?>
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

          $stmt = $db->prepare("SELECT id, username FROM app_user WHERE username = ?");
          $stmt->execute([$username]);
          $line = $stmt->fetch();

          $username_exists = false;

          if ($line) {
            $username_exists = true;
            echo "<li>Username already exists</li>";
          }

          if ($username && $password && $confirm_password && ctype_alnum($username) && $password === $confirm_password && !$username_exists) {

            $hash = password_hash($password, null);

            $user = array(
              "username" => $username,
              "password_hash" => $hash,
            );

            $stmt = $db->prepare("INSERT INTO app_user (username, password_hash) VALUES ($1, $2) RETURNING id");
            $stmt->execute([$username, $hash]);
            $line = $stmt->fetch();

            if ($line) {

              session_start();

              $_SESSION['logged_in'] = true;
              $_SESSION['username'] = $username;
              $_SESSION['id'] = $line['id'];
              $_SESSION['last_ping'] = time();
              $_SESSION['expiry'] = $_SESSION['last_ping'] + 30 * 86400;

              header('Location: /home.php');
              die();
            } else {
              echo "Could not sign up";
            }
          }
        }
        ?>
      </ul>
      <button class="btn btn-primary my-2">Sign Up</button>
    </form>
    Already have an account? <a href="/auth/signin.php">Sign In</a>
  </main>
</body>

</html>