<?php

require_once __DIR__ . "./../app/flow.php";
setFlowStarted();

require_once __DIR__ . "./../app/access.php";
require_once __DIR__ . "./../helpers/layout_hooks.php";
require_once __DIR__ . "./../layouts/left-menu.php";
require_once __DIR__ . "./../helpers/url.php";
require_once __DIR__ . "./../app/database.php";
require_once __DIR__ . "./../app/auth.php";
require_once __DIR__ . "./../app/configs.php";
require_once __DIR__ . "./../data-access/application.php";
require_once __DIR__ . "/../helpers/layout-snippets.php";


checkAuthed();

setCurrentPageId("edit-application");

$isSubmited = isset($_SESSION["edit-form-old-data"]);

if (count($_POST) > 0) {
    $application_id = mysqli_escape_string($db, trim($_POST["application-id"] ?? null));

    if (!isApplicationIDExists($application_id)) {
        header('Location: ./search.php');
        die();
    }

    try {

        $data = validateApplication($_POST, $_FILES);

        if (count($data->errors) > 0) {
            $_SESSION["edit-form-old-data"] = $_POST;
            $_SESSION["edit-form-error-message"] = "There were errors on your form.";
            $_SESSION["edit-form-errors"] = $data->errors;
            header('Location: ' . $_SERVER['PHP_SELF'] . "?application-id={$application_id}");
            die();
        }
        $sanitized = (object)$data->data;

        /* disable autocommit */
        mysqli_autocommit($db, FALSE);

        updateApplicationToDB($sanitized->application_id, $sanitized);

        deleteKnownLanguagesFromDB($application_id);
        insertKnownLanguagesToDB($application_id, $sanitized->known_languages);


        if (!is_null($sanitized->passport_copies)) {
            softDeletePassportCopies($application_id);
            addPassportCopies($application_id, $sanitized->passport_copies);
        }

        if (!is_null($sanitized->full_photo)) {
            softdeleteFullPhoto($application_id);
            addFullPhoto($application_id, $sanitized->full_photo);
        }


        if (!is_null($sanitized->passport_photo)) {
            sloftDeletePassportSizePhoto($application_id);
            addPassportSizePhoto($application_id, $sanitized->passport_photo);
        }


        /* commit insert */
        mysqli_commit($db);

        $_SESSION["edit-form-success-message"] = "Application #{$application_id} is successfully updated.";
        header('Location: ' . $_SERVER['PHP_SELF'] . "?application-id={$application_id}");

        die();
    } catch (Exception $e) {
        /* Rollback */
        mysqli_rollback($db);

        $_SESSION["edit-form-old-data"] = $_POST;
        $_SESSION["edit-form-error-message"] = "Something is wrong! please try again. " . $e->getMessage();
        header('Location: ' . $_SERVER['PHP_SELF'] . "?application-id={$application_id}");
        die();
    } catch (Throwable $e) {
        /* Rollback */
        mysqli_rollback($db);

        $_SESSION["edit-form-old-data"] = $_POST;
        $_SESSION["edit-form-error-message"] = "Something is wrong! please try again. " . $e->getMessage();
        header('Location: ' . $_SERVER['PHP_SELF'] . "?application-id={$application_id}");
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

    $application_id = mysqli_escape_string($db, trim($data["application-id"] ?? null));
    $passport_no = mysqli_escape_string($db, trim($data["passport_no"] ?? null));
    $first_name = mysqli_escape_string($db, trim($data["first_name"] ?? null));
    $last_name = mysqli_escape_string($db, trim($data["last_name"] ?? null));
    $birth_date = mysqli_escape_string($db, trim($data["birth_date"] ?? null));
    $gender = mysqli_escape_string($db, trim($data["gender"] ?? null));
    $nationality = mysqli_escape_string($db, trim($data["nationality"] ?? null));
    $arrival_date_oman = mysqli_escape_string($db, trim($data["arrival_date_oman"] ?? null));
    $commission = mysqli_escape_string($db, trim($data["commission"] ?? null));
    $guaranty_period_start_date = mysqli_escape_string($db, trim($data["guaranty_period_start_date"] ?? null));
    $return_date = mysqli_escape_string($db, trim($data["return_date"] ?? null));
    $sale_amount = mysqli_escape_string($db, trim($data["sale_amount"] ?? null));
    $agent_name = mysqli_escape_string($db, trim($data["agent_name"] ?? null));
    $sponsors_name = mysqli_escape_string($db, trim($data["sponsors_name"] ?? null));
    $sponsors_mobile_number = mysqli_escape_string($db, trim($data["sponsors_mobile_number"] ?? null));
    $sponsor_id = mysqli_escape_string($db, trim($data["sponsor_id"] ?? null));
    $full_payment = mysqli_escape_string($db, trim($data["full_payment"] ?? null));
    $bill_no = mysqli_escape_string($db, trim($data["bill_no"] ?? null));
    $visa_status = mysqli_escape_string($db, trim($data["visa_status"] ?? null));

    $known_languages = ((array)($data["known_languages"] ?? [])) ?? [];

    $passport_copies = $files["passport_copies"] ?? null;
    $full_photo =  $files["full_photo"] ?? null;
    $passport_photo =  $files["passport_photo"] ?? null;


    $errors = [];

    $application = getApplicationByID($application_id);

    // Validate passport no
    if (!(strlen((string)$passport_no) > 0)) {
        $errors["passport_no"] = "The passport no field is missing or invalid";
    } else {


        if (($application->passport_no != $passport_no) && isPassportNoExists($passport_no)) {
            $errors["passport_no"] = "The given passport no is already exists";
        }
    }

    // Validate first name
    if (!(strlen((string)$first_name) > 0)) {
        $errors["first_name"] = "The first name field is missing or invalid";
    }

    // Validate last name
    if (!(strlen((string)$last_name) > 0)) {
        $errors["last_name"] = "The last name field is missing or invalid";
    }

    // Validate birth date
    if (!(strlen((string)$birth_date) > 0)) {
        $errors["birth_date"] = "The birth date field is missing or invalid";
    } else {
        $birth_date_ = DateTime::createFromFormat("Y-m-d", $birth_date);
        if (!$birth_date_) {
            $errors["birth_date"] = "The birth date field is invalid";
        } else {
            $birth_date = $birth_date_;
        }
    }

    // Validate nationality
    if (!(strlen((string)$nationality) > 0)) {
        $errors["nationality"] = "The nationality field is missing or invalid";
    }

    // Validate oman arrival date 
    if (!(strlen((string)$arrival_date_oman) > 0)) {
        // $errors["arrival_date_oman"] = "The arrival date oman field is missing or invalid";
        $arrival_date_oman = null;
    } else {
        $arrival_date_oman_ = DateTime::createFromFormat("Y-m-d", $arrival_date_oman);
        if (!$arrival_date_oman_) {
            $errors["arrival_date_oman"] = "The arrival date oman field is invalid";
        } else {
            $arrival_date_oman = $arrival_date_oman_;
        }
    }

    // Validate commission
    if (!(strlen((string)$commission) > 0)) {
        $errors["commission"] = "The commission field is missing or invalid";
    } else {
        if (!is_numeric($commission) || $commission < 0) {
            $errors["commission"] = "The commission field is invalid";
        }
    }


    // Validate guaranty period start date
    if (!(strlen((string)$guaranty_period_start_date) > 0)) {
        // $errors["guaranty_period_start_date"] = "The guaranty period start date field is missing or invalid";
        $guaranty_period_start_date = null;
    } else {
        $guaranty_period_start_date_ = DateTime::createFromFormat("Y-m-d", $guaranty_period_start_date);
        if (!$guaranty_period_start_date_) {
            $errors["guaranty_period_start_date"] = "The guaranty period start date field is invalid";
        } else {
            $guaranty_period_start_date = $guaranty_period_start_date_;
        }
    }

    // Validate return date
    if (!(strlen((string)$return_date) > 0)) {
        // $errors["return_date"] = "The return date field is missing or invalid";
        $return_date = null;
    } else {
        $return_date_ = DateTime::createFromFormat("Y-m-d", $return_date);
        if (!$return_date_) {
            $errors["return_date"] = "The return date field is invalid";
        } else {
            $return_date = $return_date_;
        }
    }

    // Validate sale amount
    if (!(strlen((string)$sale_amount) > 0)) {
        $errors["sale_amount"] = "The sale amount field is missing or invalid";
    } else {
        if (!is_numeric($sale_amount) || $sale_amount < 0) {
            $errors["sale_amount"] = "The sale amount field is invalid";
        }
    }

    // Validate sponser name
    // if (!(strlen((string)$sponsors_name) > 0)) {
    //     $errors["sponsors_name"] = "The sponsors name field is missing or invalid";
    // }

    // Validate agent name
    if (!(strlen((string)$agent_name) > 0)) {
        $errors["agent_name"] = "The agent name field is missing or invalid";
    }

    // Validate sponser mobile
    if (!(strlen((string)$sponsors_mobile_number) > 0)) {
        // $errors["sponsors_mobile_number"] = "The sponsors mobile number field is missing or invalid";
    } else {
        if (!is_numeric($sponsors_mobile_number)) {
            $errors["sponsors_mobile_number"] = "The sponsors mobile number field is invalid";
        }
    }

    // Validate sponser id
    // if (!(strlen((string)$sponsor_id) > 0)) {
    //     $errors["sponsor_id"] = "The sponsor id field is missing or invalid";
    // }

    // Validate full payment
    if (!(strlen((string)$full_payment) > 0)) {
        $errors["full_payment"] = "The full payment field is missing or invalid";
    } else {
        if (!is_numeric($full_payment) || $full_payment < 0) {
            $errors["full_payment"] = "The full payment field is invalid";
        }
    }

    // Validate bill no
    // if (!(strlen((string)$bill_no) > 0)) {
    // $errors["bill_no"] = "The bill no field is missing or invalid";
    // }


    // Validate gender
    if (!(strlen((string)$gender) > 0)) {
        $errors["gender"] = "The gender field is missing or invalid";
    } else {
        if (!isValidGender([$gender])) {
            $errors["gender"] = "The gender field is invalid";
        }
    }

    // Validate known languages
    if (!(count($known_languages) > 0)) {
        $errors["known_languages"] = "Please select at least one language";
    } else {
        if (!isValidKnownKanguages($known_languages)) {
            $errors["known_languages"] = "The known languages selection is invalid";
        }
    }

    // Validate visa status
    if (!(strlen((string)$visa_status) > 0)) {
        $errors["visa_status"] = "The visa status field is missing or invalid";
    } else {
        if (!isValidVisaStatus([$visa_status])) {
            $errors["visa_status"] = "The visa status field is invalid";
        }
    }

    if (strlen($full_photo["tmp_name"] ?? "") > 0) {
        $fullPhotoValidation = validateUploadedFile("full photo", $full_photo, 3000, ['image/jpeg', 'image/png']);
        if ($fullPhotoValidation->errors !== null) {
            $errors["full_photo"] = $fullPhotoValidation->errors;
        }
    }


    if (strlen($passport_photo["tmp_name"] ?? "") > 0) {
        $passportPhotoValidation = validateUploadedFile("passport photo", $passport_photo, 500, ['image/jpeg', 'image/png']);
        if ($passportPhotoValidation->errors !== null) {
            $errors["passport_photo"] = $passportPhotoValidation->errors;
        }
    }

    if (strlen($passport_copies["tmp_name"] ?? "") > 0) {
        $PassportCopiesFileValidation = validateUploadedFile("passport copies", $passport_copies, 5000, ['application/pdf']);
        if ($PassportCopiesFileValidation->errors !== null) {
            $errors["passport_copies"] = $PassportCopiesFileValidation->errors;
        }
    }

    return (object)[
        "errors" => $errors,
        "data" => [
            "application_id" => $application_id,
            "passport_no" => $passport_no,
            "first_name" => $first_name,
            "last_name" => $last_name,
            "birth_date" => $birth_date,
            "gender" => $gender,
            "nationality" => $nationality,
            "arrival_date_oman" => $arrival_date_oman,
            "return_date" => $return_date,
            "commission" => $commission,
            "guaranty_period_start_date" => $guaranty_period_start_date,
            "sale_amount" => $sale_amount,
            "agent_name" => $agent_name,
            "sponsors_mobile_number" => $sponsors_mobile_number,
            "sponsors_name" => $sponsors_name,
            "sponsor_id" => $sponsor_id,
            "full_payment" => $full_payment,
            "bill_no" => $bill_no,
            "known_languages" => $known_languages,
            "visa_status" => $visa_status,
            "passport_copies" => $PassportCopiesFileValidation->validated ?? null,
            "full_photo" => is_null($full_photo) ? null : $fullPhotoValidation->validated,
            "passport_photo" => is_null($passport_photo) ? null : $passportPhotoValidation->validated
        ]
    ];
}

/**
 * Validate a file uploded trough http
 * @param string $attributeLabel name of the field. this will use for error messages.
 * @param object $file the uploded file extracted from $_FILES
 * @param int $maxSize maximum size of the file(KB)
 * @param array allowed mime types 
 * @return object
 */
function validateUploadedFile($attributeLabel, $file, $maxSize = null, $allow = [])
{
    // Check whether the file is uploaded trough http for security reasons.
    if (!is_uploaded_file($file["tmp_name"])) {
        return (object)[
            "errors" => "The {$attributeLabel} field is required",
            "file" => null
        ];
    }

    $filepath = $file['tmp_name'];
    $fileSize = filesize($filepath);

    $filetype = finfo_file(finfo_open(FILEINFO_MIME_TYPE), $filepath);
    $extension =  pathinfo($file["name"])["extension"] ?? null;

    // Check whether the file type is allowed
    if (is_array($allow) && count($allow) > 0) {
        if (!in_array($filetype, $allow)) {
            return (object)[
                "errors" => "The file type is not allowed",
                "file" => null
            ];
        }
    }

    // Check whether the file is valid in size
    if ($fileSize === 0) {
        return (object)[
            "errors" => "The file is invalid",
            "file" => null
        ];
    }

    // Check whether the file is over sized
    if (($maxSize !== null) && $maxSize > 0) {
        if ($fileSize > 1024 * $maxSize) { //check file size is grater than maxSize (Kb)
            return (object)[
                "errors" => "The file size is too large",
                "file" => null
            ];
        }
    }

    return (object)[
        "errors" => null,
        "validated" => [
            "file" => $file,
            "filesize" => $maxSize,
            "type" => $maxSize,
            "extension" => $extension,
        ]
    ];
}

$application_id = mysqli_escape_string($db, trim($_REQUEST["application-id"] ?? null));

if (strlen($application_id) < 1) {
    header('Location: ./search.php');
    die();
}

$application = getApplicationByID($application_id);

if (!$application) {
    header('Location: ./search.php');
    die();
}

$genders = getAllGenders();
$knownKanguages = getAllKnownKanguages();
$visaStatuses = getAllVisaStatuses();

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $config->appname ?> | Edit Application #<?= $application->id ?></title>

    <script src="https://code.jquery.com/jquery-3.6.0.slim.min.js" integrity="sha256-u7e5khyithlIdTpu22PHhENmPcRdFiHRjhAuHcs05RI=" crossorigin="anonymous"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/5.2.0/css/bootstrap.min.css" integrity="sha512-XWTTruHZEYJsxV3W/lSXG1n3Q39YIWOstqvmFsdNEEQfHoZ6vm6E9GK2OrF6DSJSpIbRbi+Nn0WDPID9O7xB2Q==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="stylesheet" href="<?= assetUrl('styles/fonts.css', true) ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css" integrity="sha512-SfTiTlX6kk+qitfevl/7LibUOeJWlt9rbyDn92a1DqWOw9vWG2MFoays0sgObmWazO5BQPiFucnnEAjpAB+/Sw==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="stylesheet" href="<?= assetUrl('styles/components.css', true) ?>">
    <?= renderHead() ?>
    <link rel="stylesheet" href="<?= assetUrl('styles/register.css', true) ?>">
    <?= renderFavicon() ?>
</head>

<body>

    <?php setPageContent("Edit Application #{$application->id}", "<i class='fa fa-plus-circle' aria-hidden='true'></i>", [
        ["title" => "My Account", "url" => "./"],
        ["title" => "Edit Application", "url" => null]
    ], function () use ($genders, $knownKanguages, $visaStatuses, $application, $isSubmited) {
    ?>
        <div class="register-block c-card">
            <form id="edit-form" onsubmit="return handleSubmitEvent()" action="./edit.php" method="post" enctype="multipart/form-data">
                <input type="hidden" name="application-id" value="<?= $application->id ?>">
                <div class="container">
                    <div class="row">
                        <div class="col-12">
                            <?php if (isset($_SESSION["edit-form-error-message"])) { ?>
                                <p class="text-danger"><i class="fa fa-exclamation-circle" aria-hidden="true"></i> <?= $_SESSION["edit-form-error-message"] ?></p>
                            <?php } else if (isset($_SESSION["edit-form-success-message"])) { ?>
                                <p class="text-success"><i class="fa fa-check-circle" aria-hidden="true"></i> <?= $_SESSION["edit-form-success-message"] ?></p>
                            <?php } ?>
                        </div>
                    </div>
                    <div class="row mb-4 d-flex flex-row justify-content-between">
                        <div class="col-8">
                            <div id="passport-photo-container" class="passport-photo border <?= isset($_SESSION["edit-form-errors"]["passport_photo"]) ? "border-danger" : "" ?>">
                                <span>Passport photo</span>
                                <div id="photo-layer" class="photo-layer" style="background-image: url('./files.php?type=passport-photo&application-id=<?= $application->id ?>');"></div>
                                <div id="new-photo-layer" class="photo-layer d-none"></div>
                                <input type="file" id="passport_photo_input" name="passport_photo">

                                <button type="button" id="new_passport_photo_remove" class="btn btn-primary file-picker-triggerer d-none">
                                    <i class="fa fa-minus" aria-hidden="true"></i>
                                </button>
                                <button type="button" id="passport_photo_triggerer" class="btn btn-primary file-picker-triggerer">
                                    <i class="fa fa-cloud-upload" aria-hidden="true"></i>
                                </button>
                            </div>
                            <div id="passport_photo_error_txt" class="d-block invalid-feedback">
                                <?= $_SESSION["edit-form-errors"]["passport_photo"] ?? null ?>
                            </div>
                        </div>
                        <div class="col-4 d-flex justify-content-end align-items-start">
                            <a href="./search.php?passport-no=<?= $application->passport_no ?>" class="btn btn-light"><i class="fa fa-chevron-circle-left" aria-hidden="true"></i> Back</a>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="passport_no" class="mb-1">Passport No</label>

                                <input type="text" class="form-control <?= isset($_SESSION["edit-form-errors"]["passport_no"]) ? "is-invalid" : "" ?>" id="passport_no" name="passport_no" placeholder="Enter passport no..." value="<?= $isSubmited ? $_SESSION["edit-form-old-data"]["passport_no"] : $application->passport_no ?>" required>

                                <?php if (isset($_SESSION["edit-form-errors"]["passport_no"])) { ?>
                                    <div class="invalid-feedback">
                                        <?= $_SESSION["edit-form-errors"]["passport_no"] ?>
                                    </div>
                                <?php } ?>
                            </div>
                        </div>
                    </div>
                    <div class="row mt-3">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="first_name" class="mb-1">First Name</label>
                                <input type="text" class="form-control <?= isset($_SESSION["edit-form-errors"]["first_name"]) ? "is-invalid" : "" ?>" id="first_name" name="first_name" placeholder="Enter first name..." value="<?= $isSubmited ? $_SESSION["edit-form-old-data"]["first_name"] : $application->first_name ?>" required>
                                <?php if (isset($_SESSION["edit-form-errors"]["first_name"])) { ?>
                                    <div class=" invalid-feedback">
                                        <?= $_SESSION["edit-form-errors"]["first_name"] ?>
                                    </div>
                                <?php } ?>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="last_name" class="mb-1">Last Name</label>
                                <input type="text" class="form-control <?= isset($_SESSION["edit-form-errors"]["last_name"]) ? "is-invalid" : "" ?>" id="last_name" name="last_name" placeholder="Enter last name..." value="<?= $isSubmited ? $_SESSION["edit-form-old-data"]["last_name"] : $application->last_name ?>" required>
                                <?php if (isset($_SESSION["edit-form-errors"]["last_name"])) { ?>
                                    <div class=" invalid-feedback">
                                        <?= $_SESSION["edit-form-errors"]["last_name"] ?>
                                    </div>
                                <?php } ?>
                            </div>
                        </div>
                    </div>
                    <div class="row mt-3">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="birth_date" class="mb-1">Date Of Birth</label>
                                <input type="date" class="form-control <?= isset($_SESSION["edit-form-errors"]["birth_date"]) ? "is-invalid" : "" ?>" id="birth_date" name="birth_date" placeholder="Enter date of birth..." value="<?= $isSubmited ? $_SESSION["edit-form-old-data"]["birth_date"] : $application->birth_date ?>" required>
                                <?php if (isset($_SESSION["edit-form-errors"]["birth_date"])) { ?>
                                    <div class=" invalid-feedback">
                                        <?= $_SESSION["edit-form-errors"]["birth_date"] ?>
                                    </div>
                                <?php } ?>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="gender" class="mb-1">Gender</label>
                                <select class="form-control <?= isset($_SESSION["edit-form-errors"]["gender"]) ? "is-invalid" : "" ?>" name="gender" id="gender" required>
                                    <option selected disabled value="">Select one</option>
                                    <?php foreach ($genders as $gender) { ?>
                                        <option <?= (($isSubmited ? $_SESSION["edit-form-old-data"]["gender"] : $application->gender->id) == $gender["id"]) ? "selected" : "" ?> value="<?= $gender["id"] ?>"><?= $gender["gender"] ?></option>
                                    <?php } ?>
                                </select>
                                <?php if (isset($_SESSION["edit-form-errors"]["gender"])) { ?>
                                    <div class="invalid-feedback">
                                        <?= $_SESSION["edit-form-errors"]["gender"] ?>
                                    </div>
                                <?php } ?>
                            </div>
                        </div>
                    </div>
                    <div class="row mt-3">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="nationality" class="mb-1">Nationality</label>
                                <input type="text" class="form-control <?= isset($_SESSION["edit-form-errors"]["nationality"]) ? "is-invalid" : "" ?>" id="nationality" name="nationality" placeholder="Enter nationality..." value="<?= $isSubmited ? $_SESSION["edit-form-old-data"]["nationality"] : $application->nationality ?>" required>
                                <?php if (isset($_SESSION["edit-form-errors"]["nationality"])) { ?>
                                    <div class=" invalid-feedback">
                                        <?= $_SESSION["edit-form-errors"]["nationality"] ?>
                                    </div>
                                <?php } ?>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="known_languages_arabic" class="mb-1">Known Languages</label>
                                <div class="known-language-checkboxes">
                                    <?php $applicationKnownLanguagesIds = array_map(function ($lan) {
                                        return $lan["id"];
                                    }, $application->known_languages); ?>
                                    <?php foreach ($knownKanguages as $knownLanguage) { ?>
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input <?= isset($_SESSION["edit-form-errors"]["known_languages"]) ? "is-invalid" : "" ?>" type="checkbox" id="known_languages_<?= $knownLanguage["id"] ?>" name="known_languages[]" value="<?= $knownLanguage["id"] ?>" <?= (array_search($knownLanguage["id"],  $isSubmited ? $_SESSION["edit-form-old-data"]["known_languages"] ?? [] : $applicationKnownLanguagesIds) !== false) ? "checked" : "" ?> required>
                                            <label class="form-check-label" for="known_languages_<?= $knownLanguage["id"] ?>"><?= $knownLanguage["language"] ?></label>
                                        </div>
                                    <?php } ?>
                                </div>
                                <?php if (isset($_SESSION["edit-form-errors"]["known_languages"])) { ?>
                                    <div class="d-block invalid-feedback">
                                        <?= $_SESSION["edit-form-errors"]["known_languages"] ?>
                                    </div>
                                <?php } ?>
                            </div>
                        </div>
                    </div>
                    <div class="row mt-3">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="visa_status" class="mb-1">Visa status</label>
                                <select class="form-control <?= isset($_SESSION["edit-form-errors"]["visa_status"]) ? "is-invalid" : "" ?>" name="visa_status" id="visa_status" required>
                                    <option selected disabled value="">Select one</option>
                                    <?php foreach ($visaStatuses as $visaStatus) { ?>
                                        <option <?= (($isSubmited ? $_SESSION["edit-form-old-data"]["visa_status"] : $application->visa_status->id) ===  $visaStatus["id"]) ? "selected" : "" ?> value="<?= $visaStatus["id"] ?>"><?= $visaStatus["status"] ?></option>
                                    <?php } ?>
                                </select>
                                <?php if (isset($_SESSION["edit-form-errors"]["visa_status"])) { ?>
                                    <div class="invalid-feedback">
                                        <?= $_SESSION["edit-form-errors"]["visa_status"] ?>
                                    </div>
                                <?php } ?>
                            </div>
                        </div>
                    </div>
                    <div class="row mt-3">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="arrival_date_oman" class="mb-1">Arrival Date Oman</label>
                                <input type="date" class="form-control <?= isset($_SESSION["edit-form-errors"]["arrival_date_oman"]) ? "is-invalid" : "" ?>" id="arrival_date_oman" name="arrival_date_oman" value="<?= $isSubmited ? $_SESSION["edit-form-old-data"]["arrival_date_oman"] : $application->arrival_date_oman ?>">
                                <?php if (isset($_SESSION["edit-form-errors"]["arrival_date_oman"])) { ?>
                                    <div class=" invalid-feedback">
                                        <?= $_SESSION["edit-form-errors"]["arrival_date_oman"] ?>
                                    </div>
                                <?php } ?>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="commission" class="mb-1">Commission</label>
                                <div class="input-group mb-2 mr-sm-2">
                                    <input type="number" min="0.00" step="0.01" class="form-control  <?= isset($_SESSION["edit-form-errors"]["commission"]) ? "is-invalid" : "" ?>" id="commission" name="commission" placeholder="0.00" value="<?= $isSubmited ? $_SESSION["edit-form-old-data"]["commission"] : $application->commission ?>" required>
                                    <div class=" input-group-prepend">
                                        <div class="input-group-text">OMR</div>
                                    </div>
                                </div>
                                <?php if (isset($_SESSION["edit-form-errors"]["commission"])) { ?>
                                    <div class="d-block invalid-feedback">
                                        <?= $_SESSION["edit-form-errors"]["commission"] ?>
                                    </div>
                                <?php } ?>
                            </div>
                        </div>
                    </div>
                    <div class="row mt-3">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="guaranty_period_start_date" class="mb-1">Guaranty Period Start Date</label>
                                <input type="date" class="form-control <?= isset($_SESSION["edit-form-errors"]["guaranty_period_start_date"]) ? "is-invalid" : "" ?>" id="guaranty_period_start_date" name="guaranty_period_start_date" value="<?= $isSubmited ? $_SESSION["edit-form-old-data"]["guaranty_period_start_date"] : $application->guaranty_period_start_date ?>">
                                <?php if (isset($_SESSION["edit-form-errors"]["guaranty_period_start_date"])) { ?>
                                    <div class=" d-block invalid-feedback">
                                        <?= $_SESSION["edit-form-errors"]["guaranty_period_start_date"] ?>
                                    </div>
                                <?php } ?>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="return_date" class="mb-1">Return Date</label>
                                <input type="date" class="form-control <?= isset($_SESSION["edit-form-errors"]["return_date"]) ? "is-invalid" : "" ?>" id="return_date" name="return_date" placeholder="Enter return date..." value="<?= $isSubmited ? $_SESSION["edit-form-old-data"]["return_date"] : $application->return_date ?>">
                                <?php if (isset($_SESSION["edit-form-errors"]["return_date"])) { ?>
                                    <div class=" invalid-feedback">
                                        <?= $_SESSION["edit-form-errors"]["return_date"] ?>
                                    </div>
                                <?php } ?>
                            </div>
                        </div>
                    </div>
                    <div class="row mt-3">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="sale_amount" class="mb-1">Sale Amount</label>

                                <div class="input-group mb-2 mr-sm-2">
                                    <input type="number" min="0.00" step="0.01" class="form-control  <?= isset($_SESSION["edit-form-errors"]["sale_amount"]) ? "is-invalid" : "" ?>" id="sale_amount" name="sale_amount" placeholder="0.00" value="<?= $isSubmited ? $_SESSION["edit-form-old-data"]["sale_amount"] : $application->sale_amount ?>" required>
                                    <div class="input-group-prepend">
                                        <div class="input-group-text">OMR</div>
                                    </div>
                                </div>
                                <?php if (isset($_SESSION["edit-form-errors"]["sale_amount"])) { ?>
                                    <div class="d-block invalid-feedback">
                                        <?= $_SESSION["edit-form-errors"]["sale_amount"] ?>
                                    </div>
                                <?php } ?>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="agent_name" class="mb-1">Agent Name</label>
                                <input type="text" class="form-control <?= isset($_SESSION["edit-form-errors"]["agent_name"]) ? "is-invalid" : "" ?>" id="agent_name" name="agent_name" placeholder="Enter agent name..." value="<?= $isSubmited ? $_SESSION["edit-form-old-data"]["agent_name"] : $application->agent_name ?>" required>
                                <?php if (isset($_SESSION["edit-form-errors"]["agent_name"])) { ?>
                                    <div class=" invalid-feedback">
                                        <?= $_SESSION["edit-form-errors"]["agent_name"] ?>
                                    </div>
                                <?php } ?>
                            </div>
                        </div>
                    </div>
                    <div class="row mt-3">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="sponsors_name" class="mb-1">Sponsor’s Name</label>
                                <input type="text" class="form-control <?= isset($_SESSION["edit-form-errors"]["sponsors_name"]) ? "is-invalid" : "" ?>" name="sponsors_name" id="sponsors_name" placeholder="Enter sponsor’s name..." value="<?= $isSubmited ? $_SESSION["edit-form-old-data"]["sponsors_name"] : $application->sponsors_name ?>">
                                <?php if (isset($_SESSION["edit-form-errors"]["sponsors_name"])) { ?>
                                    <div class="d-block invalid-feedback">
                                        <?= $_SESSION["edit-form-errors"]["sponsors_name"] ?>
                                    </div>
                                <?php } ?>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="sponsor_id" class="mb-1">Sponsor’s ID</label>
                                <input type="text" class="form-control  <?= isset($_SESSION["edit-form-errors"]["sponsor_id"]) ? "is-invalid" : "" ?>" name="sponsor_id" id="sponsor_id" placeholder="Enter sponsor's ID..." value="<?= $isSubmited ? $_SESSION["edit-form-old-data"]["sponsor_id"] : $application->sponsor_id ?>">
                                <?php if (isset($_SESSION["edit-form-errors"]["sponsor_id"])) { ?>
                                    <div class="d-block invalid-feedback">
                                        <?= $_SESSION["edit-form-errors"]["sponsor_id"] ?>
                                    </div>
                                <?php } ?>
                            </div>
                        </div>
                    </div>
                    <div class="row mt-3">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="sponsors_mobile_number" class="mb-1">Sponsor’s Mobile Number</label>
                                <input type="text" class="form-control <?= isset($_SESSION["edit-form-errors"]["sponsors_mobile_number"]) ? "is-invalid" : "" ?>" name="sponsors_mobile_number" id="sponsors_mobile_number" placeholder="Enter sponsor’s mobile number..." value="<?= $isSubmited ? $_SESSION["edit-form-old-data"]["sponsors_mobile_number"] : $application->sponsors_mobile_number ?>">
                                <?php if (isset($_SESSION["edit-form-errors"]["sponsors_mobile_number"])) { ?>
                                    <div class="d-block invalid-feedback">
                                        <?= $_SESSION["edit-form-errors"]["sponsors_mobile_number"] ?>
                                    </div>
                                <?php } ?>
                            </div>
                        </div>
                    </div>
                    <div class="row mt-3">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="full_payment" class="mb-1">Full payment</label>

                                <div class="input-group mb-2 mr-sm-2">
                                    <input type="number" min="0.00" step="0.01" class="form-control <?= isset($_SESSION["edit-form-errors"]["full_payment"]) ? "is-invalid" : "" ?>" id="full_payment" name="full_payment" placeholder="0.00" value="<?= $isSubmited ? $_SESSION["edit-form-old-data"]["full_payment"] : $application->full_payment ?>" required>
                                    <div class="input-group-prepend">
                                        <div class="input-group-text">OMR</div>
                                    </div>
                                </div>
                                <?php if (isset($_SESSION["edit-form-errors"]["full_payment"])) { ?>
                                    <div class="d-block invalid-feedback">
                                        <?= $_SESSION["edit-form-errors"]["full_payment"] ?>
                                    </div>
                                <?php } ?>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="bill_no" class="mb-1">Bill No</label>
                                <input type="text" class="form-control <?= isset($_SESSION["edit-form-errors"]["bill_no"]) ? "is-invalid" : "" ?>" id="bill_no" name="bill_no" placeholder="Enter bill no..." value="<?= $isSubmited ? $_SESSION["edit-form-old-data"]["bill_no"] : $application->bill_no ?>">
                                <?php if (isset($_SESSION["edit-form-errors"]["bill_no"])) { ?>
                                    <div class="d-block invalid-feedback">
                                        <?= $_SESSION["edit-form-errors"]["bill_no"] ?>
                                    </div>
                                <?php } ?>
                            </div>
                        </div>
                    </div>
                    <div class="row mt-3">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="passport_copies" class="mb-1">Passport Copies (pdf)</label>
                                <!-- <input type="file" class="form-control <?= isset($_SESSION["edit-form-errors"]["passport_copies"]) ? "is-invalid" : "" ?>" id="passport_copies" name="passport_copies" required> -->

                                <div class="border rounded bg-light px-3 py-2 file-input">
                                    <div class="current d-flex flex-row w-100">
                                        <div class="d-flex flex-grow-1 flex-column align-items-start me-3">
                                            <div class="d-flex position-relative w-100" style="height: 1.5rem;">
                                                <p class="m-0 position-absolute fixed-top text-truncate"><?= $application->passport_copies ?></p>
                                            </div>
                                            <?php $extensions = explode(".", $application->passport_copies); ?>
                                            <?php $extension = strtoupper(end($extensions)) ?>
                                            <span class="fs-8 fw-lighter"><?= $extension ?></span>
                                        </div>
                                        <div class="d-flex align-items-center">
                                            <button class="btn btn-light me-2 trigger-upload" type="button"><i class="fa fa-upload" aria-hidden="true"></i></button>
                                            <a class="btn btn-primary" href="./files.php?type=passport-copies&application-id=<?= $application->id ?>" target="_blank"><i class="fa fa-cloud-download" aria-hidden="true"></i></a>
                                        </div>
                                    </div>
                                    <div class="new d-flex d-none flex-row w-100">
                                        <div class="d-flex flex-grow-1 flex-column align-items-start me-3">
                                            <div class="d-flex position-relative w-100" style="height: 1.5rem;">
                                                <p class="m-0 position-absolute fixed-top text-truncate file-name">-</p>
                                            </div>
                                            <span class="fs-8 fw-lighter file-extension text-truncate">-</span>
                                        </div>
                                        <div class="d-flex align-items-center">
                                            <button class="btn btn-danger remove-new-file" type="button"><i class="fa fa-minus" aria-hidden="true"></i></button>
                                        </div>
                                    </div>
                                    <input type="file" name="passport_copies" hidden accept=".pdf">
                                </div>
                            </div>
                            <?php if (isset($_SESSION["edit-form-errors"]["passport_copies"])) { ?>
                                <div class="d-block invalid-feedback">
                                    <?= $_SESSION["edit-form-errors"]["passport_copies"] ?>
                                </div>
                            <?php } ?>

                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="full_photo" class="mb-1">Full Photo (allowed file types are jpeg, png)</label>
                                <!-- <input type="file" class="form-control <?= isset($_SESSION["edit-form-errors"]["full_photo"]) ? "is-invalid" : "" ?>" id="full_photo" name="full_photo" required> -->
                                <div class="border rounded bg-light px-3 py-2 file-input">
                                    <div class="current d-flex flex-row w-100">
                                        <div class="d-flex flex-grow-1 flex-column align-items-start me-3">
                                            <div class="d-flex position-relative w-100" style="height: 1.5rem;">
                                                <p class="m-0 position-absolute fixed-top text-truncate"><?= $application->full_photo ?></p>
                                            </div>
                                            <?php $extensions = explode(".", $application->full_photo); ?>
                                            <?php $extension = strtoupper(end($extensions)) ?>
                                            <span class="fs-8 fw-lighter text-truncate"><?= $extension ?></span>
                                        </div>
                                        <div class="d-flex align-items-center">
                                            <button class="btn btn-light me-2 trigger-upload" type="button"><i class="fa fa-upload" aria-hidden="true"></i></button>
                                            <a class="btn btn-primary" href="./files.php?type=full-photo&application-id=<?= $application->id ?>" target="_blank"><i class="fa fa-cloud-download" aria-hidden="true"></i></a>
                                        </div>
                                    </div>
                                    <div class="new d-flex d-none flex-row w-100">
                                        <div class="d-flex flex-grow-1 flex-column align-items-start me-3">
                                            <div class="d-flex position-relative w-100" style="height: 1.5rem;">
                                                <p class="m-0 position-absolute fixed-top text-truncate file-name">-</p>
                                            </div>
                                            <span class="fs-8 fw-lighter file-extension text-truncate">-</span>
                                        </div>
                                        <div class="d-flex align-items-center">
                                            <button class="btn btn-danger remove-new-file" type="button"><i class="fa fa-minus" aria-hidden="true"></i></button>
                                        </div>
                                    </div>
                                    <input type="file" name="full_photo" hidden accept=".jpeg,.jpg,.png">
                                </div>
                                <?php if (isset($_SESSION["edit-form-errors"]["full_photo"])) { ?>
                                    <div class="d-block invalid-feedback">
                                        <?= $_SESSION["edit-form-errors"]["full_photo"] ?>
                                    </div>
                                <?php } ?>

                            </div>
                        </div>
                    </div>
                    <div class="row mt-5">
                        <div class="col-12 d-flex justify-content-end">
                            <a href="./edit.php?application-id=<?= $application->id ?>" id="reset-form-btn" class="btn btn-light mx-3" type="button"><i class="fa fa-eraser" aria-hidden="true"></i> Reset</a>
                            <button class="btn btn-primary" type="submit"><i class="fa fa-floppy-o" aria-hidden="true"></i> Save</button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
        <div id="save-changes-model" class="modal" tabindex="-1" role="dialog">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Save changes</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <p>Are you sure that you want to save all changes?</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-primary trigger-save"><i class="fa fa-floppy-o" aria-hidden="true"></i> Save changes</button>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
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
        $("#reset-form-btn").on("click", function() {
            $(this).closest('form').trigger('reset');
        });

        $("#passport_photo_triggerer").on("click", function() {
            $("#passport_photo_input").trigger("click");
        });

        $("#new_passport_photo_remove").on("click", function() {
            clearPassportPhoto();
        });

        function loadPassportPhoto() {
            let file = $("#passport_photo_input").get(0).files[0];
            let reader = new FileReader();
            reader.readAsDataURL(file);
            reader.onload = function() {
                $("#new-photo-layer").css({
                    "background-image": `url('${reader.result}')`
                });

                $("#new-photo-layer").removeClass("d-none");
                $("#photo-layer").addClass("d-none");
                $("#new_passport_photo_remove").removeClass("d-none");
                $("#passport_photo_triggerer").addClass("d-none");
            };
            reader.onerror = function(error) {
                clearPassportPhoto();
            };
        }

        function clearPassportPhoto() {
            $("#new-photo-layer").css({
                "background-image": "none"
            });
            $("#passport_photo_input").val('');

            $("#new-photo-layer").addClass("d-none");
            $("#photo-layer").removeClass("d-none");
            $("#new_passport_photo_remove").addClass("d-none");
            $("#passport_photo_triggerer").removeClass("d-none");
        }

        $("#passport_photo_input").on("change", function() {
            let fileExtension = ['jpeg', 'jpg', 'png'];

            // Check file type
            if ($.inArray($(this).val().split('.').pop().toLowerCase(), fileExtension) == -1) {
                $("#passport_photo_error_txt").text("Only '.jpeg','.jpg' formats are allowed.");
                $("#passport-photo-container").addClass("border-danger");
                clearPassportPhoto();
                return;
            } else {
                $("#passport_photo_error_txt").text("");
                $("#passport-photo-container").removeClass("border-danger");
            }


            let fileSizeInKb = this.files[0].size / 1024;

            // Check file size
            if (fileSizeInKb > 2048) {
                $("#passport_photo_error_txt").text("The maximum file size allowed is 2MB");
                $("#passport-photo-container").addClass("border border-danger");
                clearPassportPhoto();
                return;
            } else {
                $("#passport_photo_error_txt").text("");
                $("#passport-photo-container").removeClass("border border-danger");
            }

            loadPassportPhoto();

        });


        let knownLanguageRequiredCheckboxes = $('.known-language-checkboxes :checkbox[required]');
        knownLanguageRequiredCheckboxes.change(function() {
            if (knownLanguageRequiredCheckboxes.is(':checked')) {
                knownLanguageRequiredCheckboxes.removeAttr('required');
            } else {
                knownLanguageRequiredCheckboxes.attr('required', 'required');
            }
        });


        $(".file-input").on("click", ".trigger-upload", function() {
            $(this).parents(".file-input").find("input[type=file]").trigger("click");
        });
        $(".file-input").on("change", "input[type=file]", function() {
            let filename = $(this).get(0).files[0].name;
            let extention = filename.split(".");
            extention = extention[extention.length - 1];
            extention = String(extention).toUpperCase();

            let currentContainer = $(this).parents(".file-input").find(".current");
            let newContainer = $(this).parents(".file-input").find(".new");

            newContainer.find(".file-name").text(filename);
            newContainer.find(".file-extension").text(extention);

            currentContainer.addClass("d-none");
            newContainer.removeClass("d-none");
        });
        $(".file-input").on("click", ".remove-new-file", function() {
            $(this).val("");

            let currentContainer = $(this).parents(".file-input").find(".current");
            let newContainer = $(this).parents(".file-input").find(".new");

            newContainer.find(".file-name").text("-");
            newContainer.find(".file-extension").text("-");

            currentContainer.removeClass("d-none");
            newContainer.addClass("d-none");
        });





        let saveChangesModel = new bootstrap.Modal(document.getElementById('save-changes-model'));

        let handleSubmitEvent = submitConfirmation;

        function submitConfirmation() {

            new Promise(resolve => {
                $(".trigger-save").one("click", function() {
                    resolve(true);
                });

                saveChangesModel.show();
            }).then(function() {

                handleSubmitEvent = () => true;

                $("#edit-form").submit();

                handleSubmitEvent = f1;
            });

            return false;
        }
    </script>
</body>

</html>
<?php unset($_SESSION["edit-form-old-data"]); ?>
<?php unset($_SESSION["edit-form-success-message"]); ?>
<?php unset($_SESSION["edit-form-error-message"]); ?>
<?php unset($_SESSION["edit-form-errors"]); ?>