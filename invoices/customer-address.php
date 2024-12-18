<?php

session_start();

if (!isset($_SESSION['logged_in'])) {
  die();
}

$dbconn = pg_connect("user=postgres.wjucgknzgympnnywamjy password=" . getenv("PGPASSWORD") . " host=aws-0-eu-west-2.pooler.supabase.com port=6543 dbname=postgres") or die('Could not connect: ' . pg_last_error());

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
  http_response_code(405);
  die();
}

$invoice_id = $_POST["sale_id"];
$address_id = $_POST["address_id"];

$query = "UPDATE sale SET customer_address_id = $1 WHERE user_id = $2 AND id = $3";
$params = [$address_id, $_SESSION["id"], $invoice_id];
$result = pg_query_params($dbconn, $query, $params);

pg_close($dbconn);
