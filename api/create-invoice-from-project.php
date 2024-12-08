<?php

$url_parts = explode('?', $_SERVER['REQUEST_URI']);
$url_path = $url_parts[0];

session_start();

if (!isset($_SESSION['logged_in']) && $url_path != "/auth/signin.php") {
  die();
}

$dbconn = pg_connect("user=postgres.wjucgknzgympnnywamjy password=" . getenv("PGPASSWORD") . " host=aws-0-eu-west-2.pooler.supabase.com port=6543 dbname=postgres") or die('Could not connect: ' . pg_last_error());

if ($_SERVER["REQUEST_METHOD"] != "POST") {
  http_response_code(405);
  die();
}

// We need the project ID and line item IDs
$user_id = $_SESSION["id"];
$project_id = $_POST["project_id"];
$item_ids = explode(',', $_POST["item_ids"]);

// Get customer ID
$result = pg_prepare($dbconn, "", "SELECT customer_id, hourly_rate FROM project WHERE id = $1 AND user_id = $2");
$result = pg_execute($dbconn, "", array($project_id, $user_id));
$row = pg_fetch_row($result);
$customer_id = $row[0];
$hourly_rate = $row[1];
pg_free_result($result);

// Create invoice
$query3 = "INSERT INTO sale (project_id, customer_id, user_id, status) VALUES ($1, $2, $3, $4) RETURNING id";
$params = [$project_id, $customer_id, $_SESSION["id"], "DRAFT"];
$result = pg_query_params($dbconn, $query3, $params);
$row = pg_fetch_assoc($result);
$invoice_id = $row["id"];
pg_free_result($result);

// Get project line items
$id_list = implode(', ', array_map('intval', $item_ids));
$query1 = "SELECT name, hours_logged FROM project_line_item WHERE id IN ($id_list)";
$result = pg_query($dbconn, $query1);

// Create the invoice line items
if ($result) {
  while ($row = pg_fetch_assoc($result)) {
    $item = ["name" => $row["name"], "quantity" => $row["hours_logged"], "unit_amount" => $hourly_rate, "user_id" => $user_id, "sale_id" => $invoice_id];
    $result2 = pg_insert($dbconn, "sale_line_item", $item);
  }
} else {
  die();
}

pg_close($dbconn);

echo $invoice_id;

die();

// $query = "UPDATE project_line_item SET status = $1 WHERE user_id = $2 AND id = $3";
// $params = [$status, $_SESSION["id"], $item_id];
// $result = pg_query_params($dbconn, $query, $params);

// pg_close($dbconn);
