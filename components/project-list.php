<?php

session_start();

if (!isset($_SESSION["logged_in"])) {
  die();
}

$db_path = "sqlite:../database/codecost.sqlite";

try {
  $db = new \PDO($db_path);
} catch (\PDOException $e) {
  echo $e;
  die();
}

$user_id = $_SESSION["id"];

if (isset($_GET['search'])) {
  
  $search = $_GET["search"];
  $search = "%$search%";
  $stmt = $db->prepare("SELECT project.id AS project_id, project.name AS project_name, customer_id, customer.name AS customer_name FROM project INNER JOIN customer ON customer_id = customer.id WHERE project.user_id = $1 AND (project.name LIKE ? OR customer.name LIKE ?)");
  $stmt->execute([$user_id, $search]);
  
} else {

  $stmt = $db->prepare("SELECT project.id AS project_id, project.name AS project_name, customer_id, customer.name AS customer_name FROM project INNER JOIN customer ON customer_id = customer.id WHERE project.user_id = $1");
  $stmt->execute([$user_id]);

}

foreach ($stmt as $line) { ?>
  <tr onclick="window.location.href = '/projects/project.php?id=<?php echo htmlspecialchars($line["project_id"]); ?>'">
    <td>
      <?php echo htmlspecialchars($line['project_name']); ?>
    </td>
    <td>
      <?php echo htmlspecialchars($line['customer_name']); ?>
    </td>
  </tr>
<?php }
