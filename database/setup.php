<?php

http_response_code(404);
die();

try {
  $db = new \PDO("sqlite:../database/codecost.sqlite");
  echo 'Connected to the SQLite database successfully!';
} catch (\PDOException $e) {
  echo $e->getMessage();
  die();
}

$queries = [
  "DROP TABLE address",
  "CREATE TABLE IF NOT EXISTS app_user (
    id INTEGER PRIMARY KEY,
    creation_timestamp DATETIME DEFAULT CURRENT_TIMESTAMP,
    username TEXT NOT NULL,
    password_hash TEXT NOT NULL,
    session_id TEXT
  )",
  "CREATE TABLE IF NOT EXISTS customer (
    id INTEGER PRIMARY KEY,
    creation_timestamp DATETIME DEFAULT CURRENT_TIMESTAMP,
    user_id INTEGER NOT NULL,
    name TEXT NOT NULL
  )",
  "CREATE TABLE IF NOT EXISTS email_address (
    id INTEGER PRIMARY KEY,
    creation_timestamp DATETIME DEFAULT CURRENT_TIMESTAMP,
    user_id INTEGER NOT NULL,
    email_address TEXT,
    label TEXT,
    customer_id INTEGER,
    default_flag BOOLEAN NOT NULL
  )",
  "CREATE TABLE IF NOT EXISTS phone_number (
    id INTEGER PRIMARY KEY,
    creation_timestamp DATETIME DEFAULT CURRENT_TIMESTAMP,
    user_id INTEGER NOT NULL,
    phone_number TEXT NOT NULL,
    dialcode TEXT NOT NULL,
    label TEXT NOT NULL,
    customer_id INTEGER,
    default_flag BOOLEAN NOT NULL
  )",
  "CREATE TABLE IF NOT EXISTS address (
    id INTEGER PRIMARY KEY,
    creation_timestamp DATETIME DEFAULT CURRENT_TIMESTAMP,
    customer_id INTEGER NOT NULL,
    user_id INTEGER NOT NULL,
    line1 TEXT NOT NULL,
    line2 TEXT NOT NULL,
    city TEXT NOT NULL,
    county TEXT NOT NULL,
    country TEXT NOT NULL,
    postcode TEXT NOT NULL
  )",
  "CREATE TABLE IF NOT EXISTS project (
    id INTEGER PRIMARY KEY,
    creation_timestamp DATETIME DEFAULT CURRENT_TIMESTAMP,
    user_id INTEGER NOT NULL,
    name TEXT NOT NULL,
    hourly_rate FLOAT NOT NULL DEFAULT 0.0,
    customer_id INTEGER NOT NULL
  )",
  "CREATE TABLE IF NOT EXISTS project_line_item (
    id INTEGER PRIMARY KEY,
    creation_timestamp DATETIME DEFAULT CURRENT_TIMESTAMP,
    user_id INTEGER NOT NULL,
    project_id INTEGER NOT NULL,
    customer_id INTEGER NOT NULL,
    name TEXT NOT NULL,
    description TEXT NOT NULL,
    status TEXT NOT NULL,
    hours_logged FLOAT NOT NULL DEFAULT 0.0
  )",
  "CREATE TABLE IF NOT EXISTS sale (
    id INTEGER PRIMARY KEY,
    creation_timestamp DATETIME DEFAULT CURRENT_TIMESTAMP,
    user_id INTEGER NOT NULL,
    project_id INTEGER NOT NULL,
    customer_id INTEGER NOT NULL,
    customer_address_id INTEGER,
    status TEXT NOT NULL
  )",
  "CREATE TABLE IF NOT EXISTS sale_line_item (
    id INTEGER PRIMARY KEY,
    creation_timestamp DATETIME DEFAULT CURRENT_TIMESTAMP,
    user_id INTEGER NOT NULL,
    sale_id INTEGER NOT NULL,
    unit_amount FLOAT NOT NULL,
    quantity INTEGER NOT NULL,
    name TEXT NOT NULL
  )"
];

foreach ($queries as $query) {
  $db->exec($query);
}
