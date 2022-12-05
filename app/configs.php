<?php

require_once __DIR__ . "/flow.php";
checkFlowAccess();

$config = (object)[
    "appname" => "MGAST",
    "environment" => "production",
    "catch_version_suffix" => "v1",
    "debug" => true,
    "database" => [
        "host" => "localhost",
        "username" => "root",
        "password" => "",
        "db" => "agency_app_db",
    ]
];

$filesystem = (object)[
    "applications" => "/../data/applications"
];

$urlConfig = (object)[
    "domain" => "http://localhost",
    "root" => "/application-management-system/agency-app",
];

$mailConfig = (object)[
    "username" => "something@gmail.com",
    "password" => "password",
    "host" => "smtp.gmail.com",
    "port" => 465,
    "email" => "something@gmail.com"
];
