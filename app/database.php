<?php

require_once __DIR__ . "/flow.php";
checkFlowAccess();

require_once __DIR__ . "/configs.php";

$dbCredentials = (object)$config->database;


$db = mysqli_connect(
    $dbCredentials->host,
    $dbCredentials->username,
    $dbCredentials->password,
    $dbCredentials->db,
);

if ($db == null || mysqli_connect_error()) {
    die("Database connection failed.");
}
