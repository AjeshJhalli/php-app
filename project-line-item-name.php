<?php

if ($_SERVER["REQUEST_METHOD"] != "POST") {
  http_response_code(405);
  die();
}

$item_id = $_POST["item_id"];
$name = $_POST["item_name"];

$query = "UPDATE project_line_item SET name = $1 WHERE user_id = $2 AND id = $3";
$params = [$name, $_SESSION["id"], $item_id];
$result = pg_query_params($dbconn, $query, $params);

print($name);
print($item_id);

pg_close($dbconn);

?>