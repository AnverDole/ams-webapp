<?php

require_once __DIR__ . "/app/flow.php";
setFlowStarted();

require_once __DIR__ . "/helpers/url.php";
require_once __DIR__ . "/app/access.php";
require_once __DIR__ . "/app/auth.php";

checkAuthed();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . urlPath("/"));
    die();
}

unset($_SESSION["authed_admin_id"]);

setcookie(session_id(), "", time() - 3600);
session_destroy();
session_write_close();

header('Location: ' . urlPath("/login.php"));
die();
