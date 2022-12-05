<?php

require_once __DIR__ . "/../app/flow.php";
setFlowStarted();

require_once __DIR__ . "/../app/access.php";
require_once __DIR__ . "/../app/database.php";
require_once __DIR__ . "/../app/auth.php";
require_once __DIR__ . "./../app/configs.php";
require_once __DIR__ . "/../helpers/url.php";
require_once __DIR__ . "/../data-access/admin.php";
require_once __DIR__ . "/../helpers/layout-snippets.php";
require_once __DIR__ . "/../vendor/autoload.php";

checkGuest();

/**
 * Validate form fields
 * @param array $data input fields
 * @return object
 */
function validateApplication(array $data)
{
    global $db;

    $token = mysqli_escape_string($db, trim($data["token"] ?? ""));
    $email = mysqli_escape_string($db, trim($data["email"] ?? ""));
    $password = mysqli_escape_string($db, trim($data["password"] ?? ""));
    $confirm_password = mysqli_escape_string($db, trim($data["confirm_password"] ?? ""));


    $errors = [];

    if (!(strlen($token) > 0 && isValidPasswordResetToken($token))) {
        $errors["token"] = "Invalid token.";
        return (object)[
            "errors" => $errors,
            "data" => []
        ];
    }

    $admin = getAdminFromToken($token);


    // Validate email
    if (!(strlen((string)$email) > 0)) {
        $errors["email"] = "The email address field is missing or invalid";
    } else {
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors["email"] = "The email address field is invalid";
        } else if ($admin["email"] != $email) {
            $errors["email"] = "The email address field is invalid";
        }
    }

    if (strlen($password) < 1) {
        $errors["password"] = "The password field is missing or invalid";
    } else if (strlen($password) < 8) {
        $errors["password"] = "The password field should contain at least 8 characters";
    } else  if ($password !== $confirm_password) {
        $errors["password"] = "The password confirmation is not matching";
    }


    return (object)[
        "errors" => $errors,
        "data" => [
            "token" => $token,
            "email" => $email,
            "password" => $password
        ]
    ];
}

$token = null;
if (count($_GET) > 0 && isset($_GET["token"])) {
    try {
        $token = mysqli_escape_string($db, trim($_GET["token"] ?? ""));

        if (!(strlen($token) > 0 && isValidPasswordResetToken($token))) {
            $_SESSION["forgot-password-form-error-message"] = "Invalid password reset link.";
            header('Location: ./forgot-password.php');
            die();
        }

        $admin = getAdminFromToken($token);
        if (!$admin) {
            $_SESSION["forgot-password-form-error-message"] = "Invalid password reset link.";
            header('Location: ./forgot-password.php');
            die();
        }
    } catch (Exception $e) {
        $_SESSION["forgot-password-form-error-message"] = "Something is wrong! please try again. " . $e->getMessage();
        header('Location: ./forgot-password.php');
        die();
    } catch (Throwable $e) {
        $_SESSION["forgot-password-form-error-message"] = "Something is wrong! please try again. " . $e->getMessage();
        header('Location: ./forgot-password.php');
        die();
    }
} else if (count($_POST) > 0 && isset($_POST["token"])) {
    try {


        $data = validateApplication($_POST);

        if (count($data->errors) > 0) {
            $_SESSION["reset-password-form-old-data"] = $_POST;
            $_SESSION["reset-password-form-error-message"] = "There were errors on your form.";
            $_SESSION["reset-password-form-errors"] = $data->errors;
            header('Location: ' . $_SERVER['PHP_SELF'] . "?token={$data->data['token']}");
            die();
        }

        $sanitized = (object)$data->data;
        $admin = getAdminFromToken($sanitized->token);

        if (!$admin) {
            $_SESSION["forgot-password-form-error-message"] = "Invalid password reset link.";
            header('Location: ./forgot-password.php');
            die();
        }

        updateAdminPassword($admin["id"], $sanitized->password);
        clearPasswordRecoveryTokens($admin["id"]);

        $_SESSION["forgot-password-form-success-message"] = "Your account password is successfully changed.";
        header('Location: ./forgot-password.php');
        die();
    } catch (Exception $e) {
        $_SESSION["forgot-password-form-error-message"] = "Something is wrong! please try again. " . $e->getMessage();
        header('Location: ./forgot-password.php');
        die();
    } catch (Throwable $e) {
        $_SESSION["forgot-password-form-error-message"] = "Something is wrong! please try again. " . $e->getMessage();
        header('Location: ./forgot-password.php');
        die();
    }
} else {
    $_SESSION["forgot-password-form-error-message"] = "Invalid password reset link.";
    header('Location: ./forgot-password.php');
    die();
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $config->appname ?> | Reset Password</title>

    <script src="https://code.jquery.com/jquery-3.6.0.slim.min.js" integrity="sha256-u7e5khyithlIdTpu22PHhENmPcRdFiHRjhAuHcs05RI=" crossorigin="anonymous"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/5.2.0/css/bootstrap.min.css" integrity="sha512-XWTTruHZEYJsxV3W/lSXG1n3Q39YIWOstqvmFsdNEEQfHoZ6vm6E9GK2OrF6DSJSpIbRbi+Nn0WDPID9O7xB2Q==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="stylesheet" href="<?= assetUrl('styles/fonts.css', true) ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css" integrity="sha512-SfTiTlX6kk+qitfevl/7LibUOeJWlt9rbyDn92a1DqWOw9vWG2MFoays0sgObmWazO5BQPiFucnnEAjpAB+/Sw==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="stylesheet" href="<?= assetUrl('styles/components.css', true) ?>">
    <link rel="stylesheet" href="<?= assetUrl('styles/login.css', true) ?>">

    <?= renderFavicon() ?>
</head>

<body class="d-flex flex-column">

    <div class="mb-4 text-center">
        <h4>MEALIM GHIL AL-SHBOUL<br> <i class="text-primary">TRAD</i></h4>
    </div>

    <div class="c-card login-block py-4 px-4 mb-5">
        <form action="./reset-password.php" method="post">
            <input type="hidden" name="token" value="<?= $token ?>">
            <div class="container">
                <div class="row">
                    <h5 class="p-0 mb-0">Change Account Password</h5>
                    <p class="p-0 text-muted ">Choose a strong and secure password...</p>
                </div>
                <?php if (isset($_SESSION["reset-password-form-error-message"])) { ?>
                    <div class="row">
                        <p class="text-danger p-0"><?= $_SESSION["reset-password-form-error-message"] ?></p>
                    </div>
                <?php } ?>
                <div class="row">
                    <div class="p-0 form-group">
                        <label for="email" class="mb-1">Email</label>
                        <input type="email" class="form-control <?= isset($_SESSION["reset-password-form-errors"]["email"]) ? 'is-invalid' : '' ?>" id="email" name="email" placeholder="Enter email address..." required value="<?= $_SESSION["reset-password-form-old-data"]["email"] ?? "" ?>">
                        <?php if (isset($_SESSION["reset-password-form-errors"]["email"])) { ?>
                            <div class="invalid-feedback">
                                <?= $_SESSION["reset-password-form-errors"]["email"] ?>
                            </div>
                        <?php } ?>
                    </div>
                </div>
                <div class="row">
                    <div class="p-0 form-group mt-2">
                        <label for="password" class="mb-1">New Password</label>
                        <input type="password" class="form-control <?= isset($_SESSION["reset-password-form-errors"]["password"]) ? 'is-invalid' : '' ?>" id="password" name="password" placeholder="Type your new password..." required>
                        <?php if (isset($_SESSION["reset-password-form-errors"]["password"])) { ?>
                            <div class="invalid-feedback">
                                <?= $_SESSION["reset-password-form-errors"]["password"] ?>
                            </div>
                        <?php } ?>
                    </div>
                </div>
                <div class="row">
                    <div class="p-0 form-group mt-2">
                        <label for="confirm_password" class="mb-1">Confirm New Password</label>
                        <input type="password" class="form-control <?= isset($_SESSION["reset-password-form-errors"]["password"]) ? 'is-invalid' : '' ?>" id="confirm_password" name="confirm_password" placeholder="Retype your new password..." required>
                        <?php if (isset($_SESSION["reset-password-form-errors"]["confirm_password"])) { ?>
                            <div class="invalid-feedback">
                                <?= $_SESSION["reset-password-form-errors"]["confirm_password"] ?>
                            </div>
                        <?php } ?>
                    </div>
                </div>

                <div class="row mt-4">
                    <div class="d-flex flex-column p-0 align-items-center">
                        <button type="submit" class="btn btn-primary w-100">Save</button>
                    </div>
                </div>
            </div>
        </form>
    </div>

    <footer class="d-flex flex-column align-items-center">
        <span class="text-muted mb-2">Copyright © 2022 all rights reserved</span>

    </footer>

</body>

</html>

<?php unset($_SESSION["reset-password-form-old-data"]) ?>
<?php unset($_SESSION["reset-password-form-error-message"]) ?>
<?php unset($_SESSION["reset-password-form-errors"]) ?>