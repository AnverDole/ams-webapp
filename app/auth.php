<?php

session_start();

require_once __DIR__ . "/database.php";
require_once __DIR__ . "/../helpers/url.php";
require_once __DIR__ . "/../data-access/admin.php";


/**
 * Check wether the user is logged in or redirect to given url
 */
function checkSuperAdminAuthed($redirectTo = null)
{
    global $db;

    if (!$redirectTo) {
        $redirectTo = urlPath("/");
    }

    if (isset($_SESSION["authed_admin_id"])) {
        $admin_id = mysqli_escape_string($db, trim($_SESSION["authed_admin_id"] ?? null));
        $admin = getAdminAccountByID($admin_id);
        if (!$admin) {
            header("location: {$redirectTo}");
            die();
        }


        if ($admin["type"]->id != 1) {
            header("location: {$redirectTo}");
            die();
        }
    } else {
        header("location: {$redirectTo}");
        die();
    }
}
/**
 * Check wether the user is logged in or redirect to given url
 */
function checkAuthed($redirectTo = null)
{
    global $db;

    if (!$redirectTo) {
        $redirectTo = urlPath("/login.php");
    }

    if (!is_null(getAuthAdmin())) {
        return;
    } else {
        header("location: {$redirectTo}");
        die();
    }
}


/**
 * Check wether the user is a guest or redirect to given url
 */
function checkGuest($redirectTo = null)
{
    if (!$redirectTo) {
        $redirectTo = urlPath("/");
    }
    
    if (!is_null(getAuthAdmin())) {
        header("location: {$redirectTo}");
        die();
    }
}

function getAuthAdmin()
{
    global $db;

    if (isset($_SESSION["authed_admin_id"])) {
        $admin_id = mysqli_escape_string($db, trim($_SESSION["authed_admin_id"] ?? null));
        $results = getAdminAccountByID($admin_id);

        if (!$results) {
            unset($_SESSION["authed_admin_id"]);
        }

        return $results;
    } else {
        return null;
    }
}
