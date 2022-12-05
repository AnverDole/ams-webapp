<?php

date_default_timezone_set("Asia/Colombo");

require_once __DIR__ . "/flow.php";
checkFlowAccess();

require_once __DIR__ . "/configs.php";

if ($config->debug == true) {
    ini_set('display_errors', '1');
    ini_set('display_startup_errors', '1');
    error_reporting(E_ALL);
}else{
    ini_set('display_errors', '0');
    ini_set('display_startup_errors', '0');
    error_reporting(0);
}
