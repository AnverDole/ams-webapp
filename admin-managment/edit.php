<?php

require_once __DIR__ . "/../app/flow.php";
setFlowStarted();

require_once __DIR__ . "/../app/access.php";
require_once __DIR__ . "/../helpers/layout_hooks.php";
require_once __DIR__ . "/../layouts/left-menu.php";
require_once __DIR__ . "/../helpers/url.php";
require_once __DIR__ . "/../app/database.php";
require_once __DIR__ . "/../app/auth.php";
require_once __DIR__ . "./../app/configs.php";
require_once __DIR__ . "/../data-access/admin.php";
require_once __DIR__ . "/../helpers/layout-snippets.php";

checkAuthed();

setCurrentPageId("admin-managment");

$admin_id = mysqli_escape_string($db, trim($_REQUEST["admin-id"] ?? $_REQUEST["admin_id"] ?? null));
$admin = getAdminAccountByID($admin_id);
$authedAdmin = getAuthAdmin();

if (!$admin) {
    header('Location: ./all.php');
    die();
}

if (count($_POST) > 0) {

    try {
        if ($authedAdmin["id"] == $admin_id) {
            $_SESSION["page-form-error-message"] = "Managing own account is not allowed.";
            header('Location: ' . $_SERVER['PHP_SELF']);
            die();
        }

        $data = validateApplication($_POST, $_FILES);

        if (count($data->errors) > 0) {
            $_SESSION["edit-form-old-data"] = $_POST;
            $_SESSION["edit-form-error-message"] = "There were errors on your form.";
            $_SESSION["edit-form-errors"] = $data->errors;
            header("Location: " . $_SERVER["PHP_SELF"] . "?admin-id={$admin_id}");
            die();
        }
        $sanitized = (object)$data->data;

        $sanitized->password = password_hash($sanitized->password, PASSWORD_BCRYPT);

        updateAdmin($admin_id, $sanitized);

        /* disable autocommit */
        mysqli_autocommit($db, FALSE);

        /* commit insert */
        mysqli_commit($db);

        $_SESSION["page-form-success-message"] = "Record successfully updated.";
        header("Location: ./all.php");

        die();
    } catch (Exception $e) {
        /* Rollback */
        mysqli_rollback($db);

        $_SESSION["edit-form-old-data"] = $_POST;
        $_SESSION["edit-form-error-message"] = "Something is wrong! please try again. " . $e->getMessage();
        header("Location: " . $_SERVER["PHP_SELF"] . "?admin-id={$admin_id}");
        die();
    } catch (Throwable $e) {
        /* Rollback */
        mysqli_rollback($db);

        $_SESSION["edit-form-old-data"] = $_POST;
        $_SESSION["edit-form-error-message"] = "Something is wrong! please try again. " . $e->getMessage();
        header("Location: " . $_SERVER["PHP_SELF"] . "?admin-id={$admin_id}");
        die();
    }
}

/**
 * Validate form fields
 * @param array $data input fields
 * @return object
 */
function validateApplication(array $data, array $files)
{
    global $db;
    global $admin;

    $first_name = mysqli_escape_string($db, trim($data["first_name"] ?? null));
    $last_name = mysqli_escape_string($db, trim($data["last_name"] ?? null));
    $email = mysqli_escape_string($db, trim($data["email"] ?? null));
    $account_type = mysqli_escape_string($db, trim($data["account_type"] ?? null));

    $errors = [];

    // Validate first name
    if (!(strlen((string)$first_name) > 0)) {
        $errors["first_name"] = "The first name field is missing or invalid";
    }

    // Validate last name
    if (!(strlen((string)$last_name) > 0)) {
        $errors["last_name"] = "The last name field is missing or invalid";
    }

    // Validate email
    if (!(strlen((string)$email) > 0)) {
        $errors["email"] = "The email field is missing or invalid";
    } else {
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors["email"] = "The email field is invalid";
        } else {
            if (($admin["email"] != $email) && isAccountWithEmailExists($email)) {
                $errors["email"] = "The given email address is already taken";
            }
        }
    }

    // Validate email
    if (!(strlen((string)$account_type) > 0)) {
        $errors["account_type"] = "The account type field is missing or invalid";
    } else {
        $type = getAccountTypeByID($account_type);
        if (!$type) {
            $errors["account_type"] = "The account type field is invalid";
        }
        $account_type = $type;
    }

    return (object)[
        "errors" => $errors,
        "data" => [
            "first_name" => $first_name,
            "last_name" => $last_name,
            "email" => $email,
            "account_type" => $account_type
        ]
    ];
}


$allAccountTypes = getAllAccountTypes();



?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $config->appname ?> | Edit Admin</title>

    <script src="https://code.jquery.com/jquery-3.6.0.slim.min.js" integrity="sha256-u7e5khyithlIdTpu22PHhENmPcRdFiHRjhAuHcs05RI=" crossorigin="anonymous"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/5.2.0/css/bootstrap.min.css" integrity="sha512-XWTTruHZEYJsxV3W/lSXG1n3Q39YIWOstqvmFsdNEEQfHoZ6vm6E9GK2OrF6DSJSpIbRbi+Nn0WDPID9O7xB2Q==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="stylesheet" href="<?= assetUrl('styles/fonts.css', true) ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css" integrity="sha512-SfTiTlX6kk+qitfevl/7LibUOeJWlt9rbyDn92a1DqWOw9vWG2MFoays0sgObmWazO5BQPiFucnnEAjpAB+/Sw==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="stylesheet" href="<?= assetUrl('styles/components.css', true) ?>">
    <?= renderHead() ?>
    <link rel="stylesheet" href="<?= assetUrl('styles/new-admin.css', true) ?>">
    <?= renderFavicon() ?>
</head>

<body>

    <?php setPageContent("Edit Admin", "<i class='fa fa-plus-circle' aria-hidden='true'></i>", [
        ["title" => "My Account", "url" => "./"],
        ["title" => "Admin Managment", "url" => "./all.php"],
        ["title" => "Edit", "url" => null]
    ], function () use ($allAccountTypes, $admin) {
    ?>
        <div class="register-block c-card" style="max-width: 560px">

            <form action="./edit.php" method="post">
                <div class="container ">
                    <input type="hidden" name="admin_id" value="<?= $admin['id'] ?>">
                    <div class="row">
                        <div class="d-flex align-items-center justify-content-between">
                            <h5>Edit Admin</h5>
                            <a href="./all.php" class="btn btn-light"><i class="fa fa-chevron-circle-left" aria-hidden="true"></i> Back</a>
                        </div>
                        <p>Please fill all fields.</p>
                    </div>
                    <div class="row">
                        <div class="col-12">
                            <?php if (isset($_SESSION["edit-form-error-message"])) { ?>
                                <p class="text-danger"><i class="fa fa-exclamation-circle" aria-hidden="true"></i> <?= $_SESSION["edit-form-error-message"] ?></p>
                            <?php } else if (isset($_SESSION["edit-form-success-message"])) { ?>
                                <p class="text-success"><i class="fa fa-check-circle" aria-hidden="true"></i> <?= $_SESSION["edit-form-success-message"] ?></p>
                            <?php } ?>
                        </div>
                    </div>
                    <div class="row">
                        <div class="form-group">
                            <label for="first_name" class="mb-1">First Name</label>
                            <input type="text" class="form-control  <?= isset($_SESSION["edit-form-errors"]["first_name"]) ? "is-invalid" : "" ?>" id="first_name" name="first_name" placeholder="Enter first name..." value="<?= $_SESSION["edit-form-old-data"]["first_name"] ?? $admin["first_name"] ?>" required>

                            <?php if (isset($_SESSION["edit-form-errors"]["first_name"])) { ?>
                                <div class="invalid-feedback">
                                    <?= $_SESSION["edit-form-errors"]["first_name"] ?>
                                </div>
                            <?php } ?>
                        </div>
                    </div>
                    <div class="row mt-3">
                        <div class="form-group">
                            <label for="last_name" class="mb-1">Last Name</label>
                            <input type="text" class="form-control  <?= isset($_SESSION["edit-form-errors"]["last_name"]) ? "is-invalid" : "" ?>" id="last_name" name="last_name" placeholder="Enter last name..." value="<?= $_SESSION["edit-form-old-data"]["last_name"] ?? $admin["last_name"]  ?>" required>

                            <?php if (isset($_SESSION["edit-form-errors"]["last_name"])) { ?>
                                <div class="invalid-feedback">
                                    <?= $_SESSION["edit-form-errors"]["last_name"] ?>
                                </div>
                            <?php } ?>
                        </div>
                    </div>
                    <div class="row mt-3">
                        <div class="form-group">
                            <label for="email" class="mb-1">Email Address</label>
                            <input type="email" class="form-control  <?= isset($_SESSION["edit-form-errors"]["email"]) ? "is-invalid" : "" ?>" id="email" name="email" placeholder="Enter email address..." value="<?= $_SESSION["edit-form-old-data"]["email"] ?? $admin["email"]  ?>" required>

                            <?php if (isset($_SESSION["edit-form-errors"]["email"])) { ?>
                                <div class="invalid-feedback">
                                    <?= $_SESSION["edit-form-errors"]["email"] ?>
                                </div>
                            <?php } ?>
                        </div>
                    </div>

                    <div class="row mt-3">
                        <div class="form-group">
                            <label for="account_type" class="mb-1">Account Type</label>

                            <select class="form-control <?= isset($_SESSION["edit-form-error-message"]["account_type"]) ? "is-invalid" : "" ?>" name="account_type" id="account_type" required>
                                <option selected disabled value="">Select one</option>

                                <?php foreach ($allAccountTypes as $allAccountType) { ?>
                                    <option <?= (($_SESSION["edit-form-old-data"]["account_type"] ?? $admin["type"]->id) == $allAccountType["id"]) ? "selected" : "" ?> value="<?= $allAccountType["id"] ?>"><?= $allAccountType["name"] ?></option>
                                <?php } ?>
                            </select>

                            <?php if (isset($_SESSION["edit-form-errors"]["account_type"])) { ?>
                                <div class="invalid-feedback">
                                    <?= $_SESSION["edit-form-errors"]["account_type"] ?>
                                </div>
                            <?php } ?>
                        </div>

                    </div>
                    <div class="row mt-3">
                        <div class="col-12 d-flex justify-content-end">
                            <a href="./edit.php?admin-id=<?= $admin["id"] ?>" id="reset-form-btn" class="btn btn-light mx-3" type="button"><i class="fa fa-eraser" aria-hidden="true"></i> Reset</a>
                            <button class="btn btn-primary" type="submit"><i class="fa fa-floppy-o" aria-hidden="true"></i> Save</button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
        <?
        ?>

    <?php
    }) ?>

    <?= renderBaseLayout() ?>
    <?= renderFoot() ?>

    <script>
        $("#reset-form-btn").on("click", function() {
            $(this).closest('form').trigger('reset');
        });
        $("#toggle-password-visibility").on("change", function() {
            if ($(this).prop("checked")) {
                $("#password").attr("type", "text");
            } else {
                $("#password").attr("type", "password");
            }
        });
    </script>
</body>

</html>
<?php unset($_SESSION["edit-form-old-data"]); ?>
<?php unset($_SESSION["edit-form-success-message"]); ?>
<?php unset($_SESSION["edit-form-error-message"]); ?>
<?php unset($_SESSION["edit-form-errors"]); ?>