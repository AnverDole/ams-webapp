<?php

require_once __DIR__ . "/../app/flow.php";
checkFlowAccess();

require_once __DIR__ . "/../app/database.php";
require_once __DIR__ . "/../app/configs.php";



/**
 * Get account type 
 */
function getAccountTypeByID(int $id)
{
    global $db;
    $sql = "SELECT * FROM admin_types where id='{$id}'";

    $results = mysqli_query($db, $sql);

    if (!$results) {
        throw new Exception(mysqli_error($db));
    }

    if (mysqli_num_rows($results) < 1) {
        return null;
    }

    $result = mysqli_fetch_assoc($results);


    return (object)[
        "id" => $result["id"],
        "name" => $result["name"]
    ];
}

/**
 * Check whether the account type id is exist in the database
 */
function isValidAccountType($id)
{
    global $db;

    $ids = array_map(function ($id) use ($db) {
        $id = mysqli_escape_string($db, trim(($id)));
        return "(id = '{$id}')";
    }, $ids);

    $id = join(" and ", $ids);
    $sql = "SELECT * FROM gender where {$id}";

    $results = mysqli_query($db, $sql);

    if (!$results) {
        throw new Exception(mysqli_error($db));
    }

    return mysqli_num_rows($results) > 0;
}
/**
 * Check whether the email address exist in the database
 */
function isAccountWithEmailExists($email)
{
    global $db;

    $sql = "SELECT * FROM admins where email='{$email}' ";

    $results = mysqli_query($db, $sql);

    if (!$results) {
        throw new Exception(mysqli_error($db));
    }

    return mysqli_num_rows($results) > 0;
}
/**
 * Check whether the email address exist in the database
 */
function isAccountWithIDExists($id)
{
    global $db;

    $sql = "SELECT * FROM admins where id='{$id}' ";

    $results = mysqli_query($db, $sql);

    if (!$results) {
        throw new Exception(mysqli_error($db));
    }

    return mysqli_num_rows($results) > 0;
}

/**
 * Get all account types
 */
function getAllAccountTypes()
{
    global $db;
    $sql = "SELECT * FROM admin_types";

    $results = mysqli_query($db, $sql);

    if (!$results) {
        throw new Exception(mysqli_error($db));
    }

    $types = [];
    while ($result  = mysqli_fetch_assoc($results)) {
        $types[] = [
            "id" => $result["id"],
            "name" => $result["name"]
        ];
    }

    return $types;
}
/**
 * Get all admin account
 */
function getAllAdminAccounts()
{
    global $db;
    $sql = "SELECT * FROM admins";

    $results = mysqli_query($db, $sql);

    if (!$results) {
        throw new Exception(mysqli_error($db));
    }

    $types = [];
    while ($result  = mysqli_fetch_assoc($results)) {
        $types[] = [
            "id" => $result["id"],
            "first_name" => $result["first_name"],
            "last_name" => $result["last_name"],
            "type" => getAccountTypeByID($result["type_id"]),
            "email" => $result["email"],
            "is_deactivated" => $result["is_deactivated"],
            "created_at" => $result["created_at"],
            "last_updated_at" => $result["last_updated_at"]
        ];
    }

    return $types;
}
/**
 * Add admin account
 */
function createAdmin(object $data)
{
    global $db;

    $now = date("Y-m-d H:i:s");

    $sql  = "INSERT INTO admins SET ";
    $sql .= "first_name = '{$data->first_name}', ";
    $sql .= "last_name = '{$data->last_name}', ";
    $sql .= "email = '{$data->email}', ";
    $sql .= "type_id = '{$data->account_type->id}', ";
    $sql .= "password = '{$data->password}', ";
    $sql .= "is_deactivated = '0', ";
    $sql .= "created_at = '{$now}', ";
    $sql .= "last_updated_at = '{$now}' ";

    print_r($sql);
    $results = mysqli_query($db, $sql);

    if (!$results) {
        throw new Exception(mysqli_error($db));
    }

    $types = [];
    while ($result  = mysqli_fetch_assoc($results)) {
        $types[] = [
            "id" => $result["id"],
            "name" => $result["name"]
        ];
    }

    return $types;
}
/**
 * Add admin account
 */
function updateAdmin(int $id, object $data)
{
    global $db;

    $now = date("Y-m-d H:i:s");

    $sql  = "UPDATE admins SET ";
    $sql .= "first_name = '{$data->first_name}', ";
    $sql .= "last_name = '{$data->last_name}', ";
    $sql .= "email = '{$data->email}', ";
    $sql .= "type_id = '{$data->account_type->id}', ";
    $sql .= "last_updated_at = '{$now}' ";
    $sql .= "where id='{$id}' ";

    print_r($sql);
    $results = mysqli_query($db, $sql);

    if (!$results) {
        throw new Exception(mysqli_error($db));
    }

    $types = [];
    while ($result  = mysqli_fetch_assoc($results)) {
        $types[] = [
            "id" => $result["id"],
            "name" => $result["name"]
        ];
    }

    return $types;
}

/**
 * Deactivate admin account
 */
function deactivateAdminAccount($id)
{
    global $db;

    $sql = "UPDATE admins set is_deactivated='1'  where id='{$id}' ";

    $results = mysqli_query($db, $sql);

    if (!$results) {
        throw new Exception(mysqli_error($db));
    }
}
/**
 * Activate admin account
 */
function activateAdminAccount($id)
{
    global $db;

    $sql = "UPDATE admins set is_deactivated='0'  where id='{$id}' ";

    $results = mysqli_query($db, $sql);

    if (!$results) {
        throw new Exception(mysqli_error($db));
    }
}
/**
 * Get admin account using email address
 */
function getAdminAccount($email)
{
    global $db;

    $sql = "SELECT * from admins where email='{$email}' limit 1 ";

    $results = mysqli_query($db, $sql);

    if (!$results) {
        throw new Exception(mysqli_error($db));
    }

    if (mysqli_num_rows($results) < 1) {
        return null;
    }
    return mysqli_fetch_assoc($results);
}
/**
 * Get admin account using id
 */
function getAdminAccountByID($id)
{
    global $db;

    $sql = "SELECT * from admins where id='{$id}' limit 1 ";

    $results = mysqli_query($db, $sql);

    if (!$results) {
        throw new Exception(mysqli_error($db));
    }

    if (mysqli_num_rows($results) < 1) {
        return null;
    }
    $data = mysqli_fetch_assoc($results);

    return [
        "id" => $data["id"],
        "first_name" => $data["first_name"],
        "last_name" => $data["last_name"],
        "email" => $data["email"],
        "type" => getAccountTypeByID($data["type_id"]),
        "password" => $data["password"],
        "is_deactivated" => $data["is_deactivated"],
        "created_at" => $data["created_at"],
        "last_updated_at" => $data["last_updated_at"]
    ];
}

/**
 * Get admin account using email address
 */
function createNewPasswordRecoveryToken($admin_id, $email)
{
    global $db;

    $valid = 5; //password recovery token valid duration in minitues

    $token = md5($email . $admin_id) . rand(10, 9999);
    $expires_at = new DateTime();

    $now = $expires_at->format("Y-m-d H:i:s");
    
    $expires_at->modify("+{$valid} minutes");
    $expires_at = $expires_at->format("Y-m-d H:i:s");


    $sql  = "INSERT INTO admin_reset_password_requests set ";
    $sql .= "admin_id = '{$admin_id}', ";
    $sql .= "reset_password_token = '{$token}',	";
    $sql .= "created_at = '{$now}', ";
    $sql .= "expires_at = '{$expires_at}'";

    $results = mysqli_query($db, $sql);

    if (!$results) {
        throw new Exception(mysqli_error($db));
    }

    return (object)[
        "token" => $token,
        "valid" => $valid
    ];
}
/**
 * Get admin account using email address
 */
function isValidPasswordResetToken($token)
{
    global $db;
    $now = date("Y-m-d H:i:s");

    $sql  = "SELECT * from admin_reset_password_requests ";
    $sql .= "where reset_password_token = '{$token}' and expires_at > '{$now}' ";

    $results = mysqli_query($db, $sql);

    if (!$results) {
        throw new Exception(mysqli_error($db));
    }
 
    return (mysqli_num_rows($results) ?? 0) > 0;
}
/**
 * Get admin account using email address
 */
function getAdminFromToken($token)
{
    global $db;
    $now = date("Y-m-d H:i:s");

    $sql  = "SELECT * from admin_reset_password_requests ";
    $sql .= "where reset_password_token = '{$token}' and expires_at > '{$now}' ";

    $results = mysqli_query($db, $sql);

    if (!$results) {
        throw new Exception(mysqli_error($db));
    }
 
    $result =  mysqli_fetch_assoc($results);

    return getAdminAccountByID($result["admin_id"]);
}

/**
 * Get admin account using email address
 */
function clearPasswordRecoveryTokens($admin_id)
{
    global $db;

    $sql  = "DELETE FROM admin_reset_password_requests ";
    $sql .= "where admin_id = '{$admin_id}' ";

    $results = mysqli_query($db, $sql);

    if (!$results) {
        throw new Exception(mysqli_error($db));
    }

    return true;
}
/**
 * Get admin account using email address
 */
function updateAdminPassword($admin_id, $password)
{
    global $db;

    $password = password_hash($password, PASSWORD_BCRYPT);

    $sql  = "UPDATE admins ";
    $sql .= "set password = '{$password}' ";
    $sql .= "where id = '{$admin_id}' ";

    $results = mysqli_query($db, $sql);

    if (!$results) {
        throw new Exception(mysqli_error($db));
    }

    return true;
}