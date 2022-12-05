<?php
require_once __DIR__ . "/app/flow.php";
setFlowStarted();

require_once __DIR__ . "/app/access.php";
require_once __DIR__ . "/app/database.php";
require_once __DIR__ . "/app/auth.php";
require_once __DIR__ . "/helpers/url.php";
require_once __DIR__ . "/app/configs.php";
require_once __DIR__ . "/data-access/admin.php";
require_once __DIR__ . "/helpers/layout-snippets.php";



checkGuest();


if (count($_POST) > 0) {
    try {
        $data = validateApplication($_POST);

        if (count($data->errors) > 0) {
            $_SESSION["login-form-old-data"] = $_POST;
            $_SESSION["login-form-error-message"] = "There were errors on your form.";
            $_SESSION["login-form-errors"] = $data->errors;
            header('Location: ' . $_SERVER['PHP_SELF']);
            die();
        }
        $sanitized = (object)$data->data;

        $admin = getAdminAccount($sanitized->email);
        if (count($admin) < 1) {
            $_SESSION["login-form-old-data"] = $_POST;
            $_SESSION["login-form-error-message"] = "There were errors on your form.";
            $data->errors["email"] = "Email address or the password is wrong.";
            $_SESSION["login-form-errors"] = $data->errors;
            header('Location: ' . $_SERVER['PHP_SELF']);
            die();
        }

        $admin = (object)$admin;

        if (!password_verify($sanitized->password, $admin->password)) {
            $_SESSION["login-form-old-data"] = $_POST;
            $_SESSION["login-form-error-message"] = "There were errors on your form.";

            $_SESSION["login-form-errors"] = [
                "email" => "Email address or the password is wrong"
            ];
            header('Location: ' . $_SERVER['PHP_SELF']);
            die();
        }

        $_SESSION["authed_admin_id"] = $admin->id;

        header('Location: ./');
        die();
    } catch (Exception $e) {
        echo $e->getMessage();
        $_SESSION["login-form-old-data"] = $_POST;
        $_SESSION["login-form-error-message"] = "Something is wrong! please try again. " . $e->getMessage();
        header('Location: ' . $_SERVER['PHP_SELF']);
        die();
    } catch (Throwable $e) {
        echo $e->getMessage();
        $_SESSION["login-form-old-data"] = $_POST;
        $_SESSION["login-form-error-message"] = "Something is wrong! please try again. " . $e->getMessage();
        header('Location: ' . $_SERVER['PHP_SELF']);
        die();
    }
}


/**
 * Validate form fields
 * @param array $data input fields
 * @return object
 */
function validateApplication(array $data)
{
    global $db;

    $email = mysqli_escape_string($db, trim($data["email"] ?? null));
    $password = mysqli_escape_string($db, trim($data["password"] ?? null));


    $errors = [];


    // Validate email
    if (!(strlen((string)$email) > 0)) {
        $errors["email"] = "The email address field is missing or invalid";
    } else {
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors["email"] = "The email address field is invalid";
        }
    }

    // Validate password
    if (!(strlen((string)$password) > 0)) {
        $errors["password"] = "The password field is missing or invalid";
    }


    return (object)[
        "errors" => $errors,
        "data" => [
            "email" => $email,
            "password" => $password
        ]
    ];
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $config->appname ?> | Login</title>
    <script src="https://code.jquery.com/jquery-3.6.0.slim.min.js" integrity="sha256-u7e5khyithlIdTpu22PHhENmPcRdFiHRjhAuHcs05RI=" crossorigin="anonymous"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/5.2.0/css/bootstrap.min.css" integrity="sha512-XWTTruHZEYJsxV3W/lSXG1n3Q39YIWOstqvmFsdNEEQfHoZ6vm6E9GK2OrF6DSJSpIbRbi+Nn0WDPID9O7xB2Q==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="stylesheet" href="<?= assetUrl('/styles/fonts.css', true) ?>">
   
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css" integrity="sha512-SfTiTlX6kk+qitfevl/7LibUOeJWlt9rbyDn92a1DqWOw9vWG2MFoays0sgObmWazO5BQPiFucnnEAjpAB+/Sw==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="stylesheet" href="<?= assetUrl('/styles/components.css', true) ?>">
    <link rel="stylesheet" href="<?= assetUrl('/styles/login.css', true) ?>">


    <?= renderFavicon(); ?>
</head>

<body class="d-flex flex-column">

    <div class="mb-4 text-center">
        <h4>Application Management System</i></h4>
    </div>

    <div class="c-card login-block py-4 px-4 mb-5">
        <form action="./login.php" method="post">
            <div class="container">
                <div class="row">
                    <h5 class="p-0 mb-0">Welcome</h5>
                    <p class="p-0 text-muted ">Login to your account...</p>
                </div>
                <?php if (isset($_SESSION["login-form-error-message"])) { ?>
                    <div class="row">
                        <p class="text-danger p-0"><?= $_SESSION["login-form-error-message"] ?></p>
                    </div>
                <?php } ?>
                <div class="row">
                    <div class="p-0 form-group">
                        <label for="email" class="mb-1">Email</label>
                        <input type="text" class="form-control <?= isset($_SESSION["login-form-errors"]["email"]) ? 'is-invalid' : '' ?>" id="email" name="email" placeholder="Enter email address..." required>
                        <?php if (isset($_SESSION["login-form-errors"]["email"])) { ?>
                            <div class="invalid-feedback">
                                <?= $_SESSION["login-form-errors"]["email"] ?>
                            </div>
                        <?php } ?>
                    </div>
                </div>
                <div class="row mt-2">
                    <div class="p-0 form-group">
                        <label for="password" class="mb-1">Password</label>
                        <input type="password" class="form-control" id="password" name="password" placeholder="Enter password..." required>
                    </div>
                </div>
                <div class="row mt-4">
                    <div class="d-flex flex-column p-0 align-items-center">
                        <button type="submit" class="btn btn-primary w-100">Login</button>
                        <a href="<?= urlPath("/password-reset/forgot-password.php") ?>" class="link-secondary mt-2 mt-4 p-0">Forgot Password</a>
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

<?php unset($_SESSION["login-form-old-data"]) ?>
<?php unset($_SESSION["login-form-error-message"]) ?>
<?php unset($_SESSION["login-form-success-message"]) ?>
<?php unset($_SESSION["login-form-errors"]) ?>