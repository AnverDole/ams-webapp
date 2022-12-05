<?php 

require_once __DIR__ ."/app/flow.php";
setFlowStarted();

require_once __DIR__ . "/app/auth.php";
require_once __DIR__ . "/helpers/url.php";


checkAuthed();


header("location: " . urlPath("/application-managment/search.php"));