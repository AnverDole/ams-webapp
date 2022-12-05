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
require_once __DIR__ . "/../data-access/application.php";
require_once __DIR__ . "/../helpers/layout-snippets.php";

checkAuthed();

setCurrentPageId("application-managment");

$passportNo = null;
$application = null;
if (isset($_GET["passport-no"])) {
    $data = validateSearch($_GET);

    if (count($data->errors) > 0) {
        $_SESSION["search-form-old-data"] = $_GET;
        $_SESSION["search-form-errors"] = $data->errors;
        header('Location: ' . $_SERVER['PHP_SELF']);
        die();
    }

    $sanitized = (object)($data)->data;
    $passportNo = $sanitized->passport_no;
    $application = getApplicationByPassportNo($sanitized->passport_no);

    if (!$application) {
        $_SESSION["search-form-old-data"] = $_GET;
        $_SESSION["search-form-errors"] = [
            "passport_no" => "Passport no is not found"
        ];
        header('Location: ' . $_SERVER['PHP_SELF']);
        die();
    }
}


function validateSearch($data)
{
    global $db;
    $passport_no = mysqli_escape_string($db, trim((string)($data["passport-no"]) ?? null));

    $errors = [];

    // Validate passport no
    if (!(strlen($passport_no) > 0)) {
        $errors["passport_no"] = "The passport no field is missing or invalid";
    }

    return (object)[
        "errors" => $errors,
        "data" => [
            "passport_no" => $passport_no
        ]
    ];
}

function calculateAge($dateStr)
{
    try {
        $date = DateTime::createFromFormat("Y-m-d", $dateStr);

        if (!$date) {
            return null;
        }

        $intervel =  $date->diff(new DateTime());

        return $intervel->y;
    } catch (Exception $e) {
        return null;
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $config->appname ?> | Search</title>

    <script src="https://code.jquery.com/jquery-3.6.0.slim.min.js" integrity="sha256-u7e5khyithlIdTpu22PHhENmPcRdFiHRjhAuHcs05RI=" crossorigin="anonymous"></script>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/5.2.0/css/bootstrap.min.css" integrity="sha512-XWTTruHZEYJsxV3W/lSXG1n3Q39YIWOstqvmFsdNEEQfHoZ6vm6E9GK2OrF6DSJSpIbRbi+Nn0WDPID9O7xB2Q==" crossorigin="anonymous" referrerpolicy="no-referrer" />

    <link rel="stylesheet" href="<?= assetUrl('styles/fonts.css', true) ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css" integrity="sha512-SfTiTlX6kk+qitfevl/7LibUOeJWlt9rbyDn92a1DqWOw9vWG2MFoays0sgObmWazO5BQPiFucnnEAjpAB+/Sw==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="stylesheet" href="<?= assetUrl('styles/components.css', true) ?>">
    <?= renderHead() ?>
    <link rel="stylesheet" href="<?= assetUrl('styles/search.css', true) ?>">
    <?= renderFavicon() ?>
</head>

<body>

    <?php setPageContent("Search", "<i class='fa fa-search' aria-hidden='true'></i>", [
        ["title" => "My Account", "url" => "./"],
        ["title" => "Search", "url" => null]
    ], function () use ($passportNo, $application) {
    ?>
        <div class="search-block c-card">
            <form action="./search.php" method="get">
                <div class="container">
                    <div class="row">
                        <div class="col-12">
                            <div class="form-group">
                                <label for="passport_no" class="mb-1">Passport No</label>
                                <input type="text" class="form-control <?= isset($_SESSION["search-form-errors"]["passport_no"]) ? "is-invalid" : "" ?>" id="passport_no" name="passport-no" placeholder="Enter passport no..." value="<?= $passportNo ?? $_SESSION["search-form-old-data"]["passport-no"] ?? "" ?>">

                                <?php if (isset($_SESSION["search-form-errors"]["passport_no"])) { ?>
                                    <div class="invalid-feedback">
                                        <?= $_SESSION["search-form-errors"]["passport_no"] ?>
                                    </div>
                                <?php } ?>
                            </div>
                        </div>
                    </div>
                    <div class="row mt-3">
                        <div class="col-12 d-flex justify-content-end">
                            <a href="./search.php" id="reset-form-btn" class="btn btn-light mx-3" type="button"><i class="fa fa-eraser" aria-hidden="true"></i> Reset</a>
                            <button class="btn btn-primary" type="submit"><i class="fa fa-search" aria-hidden="true"></i> Search</button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
        <?php if ($application != null) { ?>
            <div class="search-result-block c-card mt-3">
                <div class="container">
                    <div class="row">
                        <div class="col-3">
                            <div class="container">
                                <div class="row">
                                    <div class="col-12">
                                        <span class="fw-light">Passport Photo</span>
                                        <div class="passport-photo" style="background-image: url('./files.php?type=passport-photo&application-id=<?= $application->id ?>');"></div>
                                    </div>

                                    <div class="col-12 mt-4">
                                        <span class="fw-light">Full Photo</span>
                                        <div class="full-photo" style="background-image: url('./files.php?type=full-photo&application-id=<?= $application->id ?>');"></div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-9">
                            <div class="container">
                                <div class="row">
                                    <div class="col-md-4 mb-4">
                                        <span class="fw-light">First Name</span>
                                        <p class="mb-0"><?= $application->first_name ?></p>
                                    </div>
                                    <div class="col-md-4 mb-4">
                                        <span class="fw-light">Last Name</span>
                                        <p class="mb-0"><?= $application->last_name ?></p>
                                    </div>
                                    <div class="col-md-4 mb-4">
                                        <span class="fw-light">Passport No</span>
                                        <p class="mb-0"><?= $application->passport_no ?></p>
                                    </div>
                                </div>
                                <div class="row mb-4">
                                    <div class="col-md-4 mb-4">
                                        <span class="fw-light">Date Of Birth</span>
                                        <p class="mb-0"><?= strlen($application->birth_date) > 0 ? $application->birth_date : "-" ?></p>
                                    </div>
                                    <div class="col-md-4 mb-4">
                                        <span class="fw-light">Age</span>
                                        <p class="mb-0"><?= calculateAge($application->birth_date) ?? "-" ?></p>
                                    </div>
                                    <div class="col-md-4 mb-4">
                                        <span class="fw-light">Nationality</span>
                                        <p class="mb-0"><?= $application->nationality ?></p>
                                    </div>
                                </div>
                                <div class="row mb-4">
                                    <div class="col-md-4 mb-4">
                                        <span class="fw-light">Passport Copies</span>
                                        <a href="./files.php?type=passport-copies&application-id=<?= $application->id ?>" class="btn btn-primary btn-sm mt-2" target="_blank"><i class="fa fa-external-link-square" aria-hidden="true"></i> View</a>
                                    </div>
                                </div>
                                <div class="row mb-4">
                                    <div class="col-md-4 mb-4">
                                        <span class="fw-light">Gender</span>
                                        <p class="mb-0"><?= $application->gender->gender ?></p>
                                    </div>
                                    <div class="col-md-4 mb-4">
                                        <span class="fw-light">Visa Status</span>
                                        <p class="mb-0"><?= $application->visa_status->status ?></p>
                                    </div>
                                    <div class="col-md-4 mb-4">
                                        <span class="fw-light">Known Languages</span>
                                        <?php
                                        $known_languages = array_map(function ($known_language) {
                                            return $known_language["language"];
                                        }, $application->known_languages);
                                        ?>
                                        <p class="mb-0"><?= join(", ", $known_languages) ?></p>
                                    </div>
                                </div>
                                <div class="row mb-4">
                                    <div class="col-md-4 mb-4">
                                        <span class="fw-light">Arrival Date Oman</span>
                                        <p class="mb-0"><?= strlen($application->arrival_date_oman) > 0 ? $application->arrival_date_oman : "-" ?></p>
                                    </div>
                                    <div class="col-md-4 mb-4">
                                        <span class="fw-light">Commission</span>
                                        <p class="mb-0"><?= $application->commission ?></p>
                                    </div>
                                </div>
                                <div class="row mb-4">
                                    <div class="col-md-4 mb-4">
                                        <span class="fw-light">Guaranty Period Start Date</span>
                                        <p class="mb-0"><?= strlen($application->guaranty_period_start_date) > 0 ? $application->guaranty_period_start_date : "-" ?></p>
                                    </div>
                                    <div class="col-md-4 mb-4">
                                        <span class="fw-light">Return Date</span>
                                        <p class="mb-0"><?= strlen($application->return_date) > 0 ? $application->return_date : "-" ?></p>
                                    </div>
                                    <div class="col-md-4 mb-4">
                                        <span class="fw-light">Days Left</span>
                                        <?php if (strlen($application->return_date) > 0) { ?>
                                            <?php $return = DateTime::createFromFormat("Y-m-d", $application->return_date); ?>
                                            <p class="mb-0"><?= (new DateTime())->diff($return)->days  ?></p>
                                        <?php } else { ?>
                                            <p class="mb-0">-</p>
                                        <?php } ?>
                                    </div>
                                </div>
                                <div class="row mb-4">
                                    <div class="col-md-4 mb-4">
                                        <span class="fw-light">Agent name</span>
                                        <p class="mb-0"><?= $application->agent_name ?></p>
                                    </div>
                                    <div class="col-md-4 mb-4">
                                        <span class="fw-light">Sale Amount</span>
                                        <p class="mb-0"><?= number_format($application->sale_amount, 2, ".", "") . " OMR" ?></p>
                                    </div>
                                </div>
                                <div class="row mb-4">
                                    <div class="col-md-4 mb-4">
                                        <span class="fw-light">Sponsor’s ID</span>
                                        <p class="mb-0"><?= $application->sponsor_id ?></p>
                                    </div>
                                    <div class="col-md-4 mb-4">
                                        <span class="fw-light">Sponsor’s Name</span>
                                        <p class="mb-0"><?= strlen($application->sponsors_name) > 0 ? $application->sponsors_name : "-" ?></p>
                                    </div>
                                    <div class="col-md-4 mb-4">
                                        <span class="fw-light">Sponsor’s Mobile No</span>
                                        <p class="mb-0"><?= strlen($application->sponsors_mobile_number) ? $application->sponsors_mobile_number  : "-" ?></p>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-4 mb-4">
                                        <span class="fw-light">Full payment</span>
                                        <p class="mb-0"><?= number_format($application->full_payment, 2, ".", "") . " OMR" ?></p>
                                    </div>
                                    <div class="col-md-4 mb-4">
                                        <span class="fw-light">Bill No</span>
                                        <p class="mb-0"><?= strlen($application->bill_no) > 0 ?  $application->bill_no : "-" ?></p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="print-result-block c-card mt-3 p-3 d-flex justify-content-end">
                <a href="./edit.php?application-id=<?= $application->id ?>" class="btn btn-light me-2"><i class="fa fa-pencil-square-o" aria-hidden="true"></i> Edit</a>
                <!-- <button class="btn btn-light" onclick="printT()"><i class="fa fa-print" aria-hidden="true"></i> Print</button> -->
            </div>
        <?php } ?>
    <?php
    }) ?>


    <?= renderBaseLayout() ?>


    <?= renderFoot() ?>

</body>

</html>

<?php unset($_SESSION["search-form-old-data"]); ?>
<?php unset($_SESSION["search-form-errors"]); ?>