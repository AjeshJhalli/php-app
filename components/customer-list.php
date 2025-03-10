<?php

session_start();

if (!isset($_SESSION['logged_in'])) {
  http_response_code(401);
  die();
}

try {
  $db = new \PDO("sqlite:../database/codecost.sqlite");
} catch (\PDOException $e) {
  die();
}

$user_id = $_SESSION['id'];

if (isset($_GET['search'])) {
  $search = $_GET['search'];
  $search = "%$search%";
  $stmt = $db->prepare("SELECT id, name FROM customer WHERE user_id = ? AND name LIKE ?");
  $stmt->execute([$user_id, $search]);
} else {
  $stmt = $db->prepare("SELECT id, name FROM customer WHERE user_id = ?");
  $stmt->execute([$user_id]);
}

foreach ($stmt as $line) { ?>
  <a class="list-group-item list-group-item-action" href="/customers/customer.php?id=<?php echo htmlspecialchars($line["id"]); ?>">
    <?php echo htmlspecialchars($line['name']); ?>
  </a>
<?php }
