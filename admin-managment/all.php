<?php

require_once __DIR__ . "./../app/flow.php";
setFlowStarted();

require_once __DIR__ . "/../app/access.php";
require_once __DIR__ . "/../helpers/layout_hooks.php";
require_once __DIR__ . "/../layouts/left-menu.php";
require_once __DIR__ . "/../helpers/url.php";
require_once __DIR__ . "/../app/database.php";
require_once __DIR__ . "/../app/auth.php";
require_once __DIR__ . "/../app/configs.php";
require_once __DIR__ . "/../data-access/admin.php";
require_once __DIR__ . "/../helpers/layout-snippets.php";

setCurrentPageId("admin-managment");

checkSuperAdminAuthed();
$authedAdmin = getAuthAdmin();

if (isset($_POST["deactivate-admin"])) {
    try {
        $admin_id = mysqli_escape_string($db, trim($_POST["admin_id"]));

        if ($authedAdmin["id"] == $admin_id) {
            $_SESSION["page-form-error-message"] = "Managing own account is not allowed.";
            header('Location: ' . $_SERVER['PHP_SELF']);
            die();
        }

        if (!isAccountWithIDExists($admin_id)) {
            $_SESSION["page-form-error-message"] = "Failed to execute requested action! please try again.";
            header('Location: ' . $_SERVER['PHP_SELF']);
            die();
        }

        deactivateAdminAccount($admin_id);


        $_SESSION["page-form-success-message"] = "The admin account #{$admin_id} is deactivated.";
        header('Location: ' . $_SERVER['PHP_SELF']);
        die();
    } catch (Throwable $e) {
        $_SESSION["page-form-error-message"] = "Failed to execute requested action! please try again.";
        header('Location: ' . $_SERVER['PHP_SELF']);
        die();
    } catch (Exception $e) {
        $_SESSION["page-form-error-message"] = "Failed to execute requested action! please try again.";
        header('Location: ' . $_SERVER['PHP_SELF']);
        die();
    }
}
if (isset($_POST["activate-admin"])) {
    try {
        $admin_id = mysqli_escape_string($db, trim($_POST["admin_id"]));

        if ($authedAdmin["id"] == $admin_id) {
            $_SESSION["page-form-error-message"] = "Managing own account is not allowed.";
            header('Location: ' . $_SERVER['PHP_SELF']);
            die();
        }

        if (!isAccountWithIDExists($admin_id)) {
            $_SESSION["page-form-error-message"] = "Failed to execute requested action! please try again.";
            header('Location: ' . $_SERVER['PHP_SELF']);
            die();
        }

        activateAdminAccount($admin_id);


        $_SESSION["page-form-success-message"] = "The admin account #{$admin_id} is deactivated.";
        header('Location: ' . $_SERVER['PHP_SELF']);
        die();
    } catch (Throwable $e) {
        $_SESSION["page-form-error-message"] = "Failed to execute requested action! please try again.";
        header('Location: ' . $_SERVER['PHP_SELF']);
        die();
    } catch (Exception $e) {
        $_SESSION["page-form-error-message"] = "Failed to execute requested action! please try again.";
        header('Location: ' . $_SERVER['PHP_SELF']);
        die();
    }
}


$allAdminAccounts = getAllAdminAccounts();
$authedAdmin = getAuthAdmin();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $config->appname ?> | Admin Managment</title>

    <script src="https://code.jquery.com/jquery-3.6.0.slim.min.js" integrity="sha256-u7e5khyithlIdTpu22PHhENmPcRdFiHRjhAuHcs05RI=" crossorigin="anonymous"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/5.2.0/css/bootstrap.min.css" integrity="sha512-XWTTruHZEYJsxV3W/lSXG1n3Q39YIWOstqvmFsdNEEQfHoZ6vm6E9GK2OrF6DSJSpIbRbi+Nn0WDPID9O7xB2Q==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="stylesheet" href="<?= assetUrl('styles/fonts.css', true) ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css" integrity="sha512-SfTiTlX6kk+qitfevl/7LibUOeJWlt9rbyDn92a1DqWOw9vWG2MFoays0sgObmWazO5BQPiFucnnEAjpAB+/Sw==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="stylesheet" href="<?= assetUrl('styles/components.css', true) ?>">
    <?= renderHead() ?>
    <link rel="stylesheet" href="<?= assetUrl('styles/all-admin.css', true) ?>">
    <?= renderFavicon() ?>
</head>

<body>

    <?php setPageContent("New Admin", "<i class='fa fa-plus-circle' aria-hidden='true'></i>", [
        ["title" => "My Account", "url" => "./../application-managment/search.php"],
        ["title" => "Admin Managment", "url" => "./../admin-managment/all.php"],
        ["title" => "All", "url" => null]
    ], function () use ($allAdminAccounts, $authedAdmin) {
    ?>
        <div class="admin-managment-block c-card">
            <div class="d-flex justify-content-between align-items-center p-4">
                <h5>Admin Accounts</h5>
                <a class="btn btn-primary" href="./new.php"><i class="fa fa-plus-circle" aria-hidden="true"></i> New Account</a>
            </div>
            <div class="p-4 pt-0">
                <?php if (isset($_SESSION["page-form-error-message"])) { ?>
                    <p class="text-danger"><i class="fa fa-exclamation-circle" aria-hidden="true"></i> <?= $_SESSION["page-form-error-message"] ?></p>
                <?php } else if (isset($_SESSION["page-form-success-message"])) { ?>
                    <p class="text-success"><i class="fa fa-check-circle" aria-hidden="true"></i> <?= $_SESSION["page-form-success-message"] ?></p>
                <?php } ?>
                <table class="table table-striped ">
                    <tr>
                        <th class="">#ID</th>
                        <th class="">Name</th>
                        <th class="">Email</th>
                        <th class="text-center">Type</th>
                        <th class="text-center">Status</th>
                        <th></th>
                    </tr>
                    <?php foreach ($allAdminAccounts as $admin) { ?>
                        <tr>
                            <td class="align-middle ">#<?= $admin["id"] ?></td>
                            <td class="align-middle "><?= $admin["first_name"] ?> <?= $admin["last_name"] ?></td>
                            <td class="align-middle "><?= $admin["email"] ?></td>
                            <td class="align-middle text-center">
                                <span class="badge <?= /* super admin */ $admin["type"]->id  == 1 ? "text-success" : "text-info" ?> "><?= $admin["type"]->name ?></span>
                            </td>
                            <td class="align-middle text-center">
                                <span class="badge <?= $admin["is_deactivated"] ? "bg-danger" : "bg-success" ?>"><?= $admin["is_deactivated"] ? "Deactivated" : "Active" ?></span>
                            </td>
                            <td class="align-middle" width="20">
                                <div class="dropdown">
                                    <?php if ($admin["id"] != $authedAdmin["id"]) { ?>
                                        <button class="btn btn-light" type="button" id="item<?= $admin["type"]->id ?>ActionMenu" data-bs-toggle="dropdown" aria-expanded="false">
                                            <i class="fa fa-ellipsis-v" aria-hidden="true"></i>
                                        </button>
                                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="item<?= $admin["type"]->id ?>ActionMenu">
                                            <li><a class="dropdown-item" href="./edit.php?admin-id=<?= $admin["id"] ?>">Edit</a></li>
                                            <?php if ($admin["is_deactivated"]) { ?>
                                                <li><a class="dropdown-item activate-admin" href="javascript:void(0)" admin-id="<?= $admin["id"] ?>">Activate</a></li>
                                            <?php } else { ?>
                                                <li><a class="dropdown-item deactivate-admin" href="javascript:void(0)" admin-id="<?= $admin["id"] ?>">Deactivate</a></li>
                                            <?php } ?>
                                        </ul>
                                    <?php } ?>
                                </div>
                            </td>
                        </tr>
                    <?php } ?>

                    <?php if (count($allAdminAccounts) < 1) { ?>
                        <tr>
                            <td colspan="6" class="text-center text-muted border-bottom-none">
                                <p class="my-5">No admin accounts.</p>
                            </td>
                        </tr>
                    <?php } ?>
                </table>
            </div>
        </div>

        <div id="deactivate-admin-model" class="modal" tabindex="-1" role="dialog">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Deactivate Admin #<i></i></h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <p>Are you sure that you wants to deactivate the #<i></i> admin account?</p>
                    </div>
                    <div class="modal-footer">
                        <form action="./all.php" method="post">
                            <input type="hidden" id="admin-id" name="admin_id">
                            <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
                            <button type="submit" class="btn btn-danger" name="deactivate-admin"><i class="fa fa-ban" aria-hidden="true"></i> Deactivate</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <div id="activate-admin-model" class="modal" tabindex="-1" role="dialog">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Activate Admin #<i></i></h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <p>Are you sure that you wants to activate the #<i></i> admin account?</p>
                    </div>
                    <div class="modal-footer">
                        <form action="./all.php" method="post">
                            <input type="hidden" id="admin-id" name="admin_id">
                            <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
                            <button type="submit" class="btn btn-success" name="activate-admin"><i class="fa fa-check-circle" aria-hidden="true"></i> Activate</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <?
        ?>

    <?php
    }) ?>

    <?= renderBaseLayout() ?>
    <?= renderFoot() ?>


    <script>
        let deactivateAdminMmodel = new bootstrap.Modal(document.getElementById('deactivate-admin-model'));
        let activateAdminMmodel = new bootstrap.Modal(document.getElementById('activate-admin-model'));

        $(".deactivate-admin").on("click", function() {
            deactivateAdminMmodel.show();
            let adminId = $(this).attr("admin-id");

            $("#deactivate-admin-model").find(".modal-title i").text(adminId);
            $("#deactivate-admin-model").find(".modal-body i").text(adminId);
            $("#deactivate-admin-model").find("#admin-id").val(adminId);
        });

        $(".activate-admin").on("click", function() {
            activateAdminMmodel.show();
            let adminId = $(this).attr("admin-id");

            $("#activate-admin-model").find(".modal-title i").text(adminId);
            $("#activate-admin-model").find(".modal-body i").text(adminId);
            $("#activate-admin-model").find("#admin-id").val(adminId);
        });
    </script>
</body>

</html>
<?php unset($_SESSION["page-form-error-message"]); ?>
<?php unset($_SESSION["page-form-success-message"]); ?>