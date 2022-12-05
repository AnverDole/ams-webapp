<?php

require_once __DIR__ . "/../app/flow.php";
setFlowStarted();

require_once __DIR__ . "/../app/access.php";
require_once __DIR__ . "/../app/database.php";
require_once __DIR__ . "/../app/auth.php";
require_once __DIR__ . "./../app/configs.php";
require_once __DIR__ . "/../data-access/application.php";

checkAuthed();

$type = mysqli_escape_string($db, trim($_GET["type"] ?? null));
$application_id = mysqli_escape_string($db, trim($_GET["application-id"] ?? null));
$should_download = trim($_GET["download"] ?? false) == "1";
$application = getApplicationFiles($application_id);

$filename = $application[$type];
$attachment_location = __DIR__ . "/../data/applications/{$application_id}/{$filename}";
$extension = pathinfo($attachment_location, PATHINFO_EXTENSION);

if (file_exists($attachment_location)) {
    $mime  = mime_content_type($attachment_location);

    header($_SERVER["SERVER_PROTOCOL"] . " 200 OK");
    header("Cache-Control: public"); // needed for internet explorer
    header("Content-Type: {$mime}");
    header("Content-Transfer-Encoding: Binary");
    header("Content-Length:" . filesize($attachment_location));

    if($should_download){
        header("Content-Disposition: attachment; filename={$type}#{$application_id}.{$extension}");
    }else{
        header("Content-Disposition: inline; filename={$type}#{$application_id}.{$extension}");
    }

    readfile($attachment_location);
    
    die();
} else {
    die("Error: File not found.");
}
