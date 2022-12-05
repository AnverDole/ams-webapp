<?php

require_once __DIR__ . "/../app/flow.php";
checkFlowAccess();

require_once __DIR__ . "/../app/database.php";
require_once __DIR__ . "/../app/configs.php";


/**
 * Get all genders
 */
function getAllGenders()
{
    global $db;
    $sql = "SELECT * FROM gender";

    $results = mysqli_query($db, $sql);

    if (!$results) {
        throw new Exception(mysqli_error($db));
    }

    $genders = [];
    while ($result  = mysqli_fetch_assoc($results)) {
        $genders[] = [
            "id" => $result["id"],
            "gender" => $result["gender"]
        ];
    }

    return $genders;
}
/**
 * Get gender by id
 */
function getGenderById(int $id)
{
    global $db;
    $sql = "SELECT * FROM gender where id='{$id}'";

    $results = mysqli_query($db, $sql);

    if (!$results) {
        throw new Exception(mysqli_error($db));
    }

    $result = mysqli_fetch_assoc($results);

    return (object)[
        "id" => $result["id"],
        "gender" => $result["gender"]
    ];
}

/**
 * Check whether the gender id is exist in the database
 */
function isValidGender($ids)
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
 * Get all visa statuses
 */
function getAllVisaStatuses()
{
    global $db;
    $sql = "SELECT * FROM visa_status";

    $results = mysqli_query($db, $sql);

    if (!$results) {
        throw new Exception(mysqli_error($db));
    }

    $statuses = [];
    while ($result  = mysqli_fetch_assoc($results)) {
        $statuses[] = [
            "id" => $result["id"],
            "status" => $result["status"]
        ];
    }

    return $statuses;
}
/**
 * Get visa status
 */
function getVisaStatus(int $id)
{
    global $db;
    $sql = "SELECT * FROM visa_status where id = '{$id}' ";

    $results = mysqli_query($db, $sql);

    if (!$results) {
        throw new Exception(mysqli_error($db));
    }


    $result  = mysqli_fetch_assoc($results);


    return (object)[
        "id" => $result["id"],
        "status" => $result["status"]
    ];
}

/**
 * Check whether the visa status id is exist in the database
 */
function isValidVisaStatus($ids)
{
    global $db;

    $ids = array_map(function ($id) use ($db) {
        $id = mysqli_escape_string($db, trim(($id)));
        return "(id = '{$id}')";
    }, $ids);

    $id = join(" and ", $ids);
    $sql = "SELECT * FROM visa_status where {$id}";

    $results = mysqli_query($db, $sql);

    if (!$results) {
        throw new Exception(mysqli_error($db));
    }

    return mysqli_num_rows($results) > 0;
}

/**
 * Get all known languages
 */
function getAllKnownKanguages()
{
    global $db;
    $sql = "SELECT * FROM known_languages";

    $results = mysqli_query($db, $sql);

    if (!$results) {
        throw new Exception(mysqli_error($db));
    }

    $languages = [];
    while ($result  = mysqli_fetch_assoc($results)) {
        $languages[] = [
            "id" => $result["id"],
            "language" => $result["language"]
        ];
    }

    return $languages;
}

/**
 * Check whether the known language ids are exist in the database
 */
function isValidKnownKanguages($ids)
{
    global $db;
    $isAllExists = true;
    foreach ($ids as $id) {
        $id = mysqli_escape_string($db, trim(($id)));

        $sql = "SELECT * FROM known_languages where id='{$id}'";

        $results = mysqli_query($db, $sql);

        if (!$results) {
            throw new Exception(mysqli_error($db));
        }

        $isAllExists &= mysqli_num_rows($results) > 0;
    }

    return $isAllExists;
}


/**
 * Insert application into the database
 */
function insertKnownLanguagesToDB(int $application_id, array $language_ids)
{
    global $db;

    if (count($language_ids) < 1) return;

    $records = array_map(function ($language_id) use ($application_id) {
        return "('{$application_id}', '{$language_id}')";
    }, $language_ids);

    $sql = "INSERT INTO application_known_languages(application_id, known_language_id) Values";
    $sql .= join(", ", $records);

    $results = mysqli_query($db, $sql);

    if (!$results) {
        throw new Exception(mysqli_error($db));
    }
}
/**
 * Insert application into the database
 */
function deleteKnownLanguagesFromDB(int $application_id)
{
    global $db;

    $sql = "delete from application_known_languages where application_id = '{$application_id}' ";

    $results = mysqli_query($db, $sql);

    if (!$results) {
        throw new Exception(mysqli_error($db));
    }
}

/**
 * Insert application into the database
 */
function insertApplicationToDB(object $data)
{
    global $db;


    $birth_date = is_null($data->birth_date) > 0 ? null : $data->birth_date->format("Y-m-d");

    $arrival_date_oman = is_null($data->arrival_date_oman) > 0 ? null : $data->arrival_date_oman->format("Y-m-d");
    $return_date = is_null($data->return_date) > 0 ? null : $data->return_date->format("Y-m-d");
    $guaranty_period_start_date = is_null($data->guaranty_period_start_date) > 0 ? null : $data->guaranty_period_start_date->format("Y-m-d");

    $sql = "INSERT INTO application set ";
    $sql .= "passport_no = '{$data->passport_no}', ";
    $sql .= "first_name = '{$data->first_name}', ";
    $sql .= "last_name = '{$data->last_name}', ";
    $sql .= "gender_id = '{$data->gender}', ";
    $sql .= "nationality = '{$data->nationality}', ";
    $sql .= "commission = '{$data->commission}', ";
    $sql .= "sale_amount = '{$data->sale_amount}', ";
    $sql .= "agent_name = '{$data->agent_name}', ";
    $sql .= "sponsors_mobile_number = '{$data->sponsors_mobile_number}', ";
    $sql .= "sponsors_name = '{$data->sponsors_name}', ";
    $sql .= "sponsor_id = '{$data->sponsor_id}', ";
    $sql .= "full_payment = '{$data->full_payment}', ";
    $sql .= "bill_no = '{$data->bill_no}', ";
    $sql .= "visa_status_id = '{$data->visa_status}' ";

    if (!is_null($birth_date)) {
        $sql .= ",birth_date = '{$birth_date}' ";
    }else{
        $sql .= ",birth_date = NULL ";
    }

    if (!is_null($arrival_date_oman)) {
        $sql .= ",arrival_date_oman = '{$arrival_date_oman}' ";
    }else{
        $sql .= ",arrival_date_oman = NULL ";
    }

    if (!is_null($return_date)) {
        $sql .= ",return_date = '{$return_date}' ";
    }else{
        $sql .= ",return_date = NULL ";
    }

    if (!is_null($guaranty_period_start_date)) {
        $sql .= ",guaranty_period_start_date = '{$guaranty_period_start_date}' ";
    }else{
        $sql .= ",guaranty_period_start_date = NULL ";
    }
    
    $sql .= ";";

    $results = mysqli_query($db, $sql);
    if (!$results) {
        throw new Exception(mysqli_error($db));
    }

    return mysqli_insert_id($db);
}
/**
 * Update application into the database
 */
function updateApplicationToDB(int $application_id, object $data)
{
    global $db;

    $birth_date = is_null($data->birth_date) > 0 ? null : $data->birth_date->format("Y-m-d");

    $arrival_date_oman = is_null($data->arrival_date_oman) > 0 ? null : $data->arrival_date_oman->format("Y-m-d");
    $return_date = is_null($data->return_date) > 0 ? null : $data->return_date->format("Y-m-d");
    $guaranty_period_start_date = is_null($data->guaranty_period_start_date) > 0 ? null : $data->guaranty_period_start_date->format("Y-m-d");


    $sql = "Update application set ";
    $sql .= "passport_no = '{$data->passport_no}', ";
    $sql .= "first_name = '{$data->first_name}', ";
    $sql .= "last_name = '{$data->last_name}', ";
    $sql .= "gender_id = '{$data->gender}', ";
    $sql .= "nationality = '{$data->nationality}', ";
    $sql .= "commission = '{$data->commission}', ";
    $sql .= "sale_amount = '{$data->sale_amount}', ";
    $sql .= "agent_name = '{$data->agent_name}', ";
    $sql .= "sponsors_mobile_number = '{$data->sponsors_mobile_number}', ";
    $sql .= "sponsors_name = '{$data->sponsors_name}', ";
    $sql .= "sponsor_id = '{$data->sponsor_id}', ";
    $sql .= "full_payment = '{$data->full_payment}', ";
    $sql .= "bill_no = '{$data->bill_no}', ";
    $sql .= "visa_status_id = '{$data->visa_status}' ";

    if (!is_null($birth_date)) {
        $sql .= ",birth_date = '{$birth_date}' ";
    } else {
        $sql .= ",birth_date = NULL ";
    }

    if (!is_null($arrival_date_oman)) {
        $sql .= ",arrival_date_oman = '{$arrival_date_oman}' ";
    } else {
        $sql .= ",arrival_date_oman = NULL ";
    }

    if (!is_null($return_date)) {
        $sql .= ",return_date = '{$return_date}' ";
    } else {
        $sql .= ",return_date = NULL ";
    }

    if (!is_null($guaranty_period_start_date)) {
        $sql .= ",guaranty_period_start_date = '{$guaranty_period_start_date}' ";
    } else {
        $sql .= ",guaranty_period_start_date = NULL ";
    }

    $sql .= "where id ='{$application_id}';";

    $results = mysqli_query($db, $sql);
    if (!$results) {
        throw new Exception(mysqli_error($db));
    }
}



/**
 * attach passport copies to the application & upload 
 * the file to appropriate location.
 */
function addPassportCopies(int $application_id, $file)
{
    global $filesystem;
    global $db;

    $extension = $file["extension"];
    $filename = "PC-" . time() . "." . $extension;

    $dir = __DIR__ . "{$filesystem->applications}/{$application_id}";
    $file_path = "{$dir}/{$filename}";

    if (!file_exists($dir)) {
        mkdir($dir, 0755, true);
    }

    if (!move_uploaded_file($file["file"]["tmp_name"], $file_path)) {
        throw new Exception("Failed to upload passport copies");
    }

    $file_path = mysqli_escape_string($db, $file_path);

    $sql = "INSERT INTO application_files SET 
            application_id = '{$application_id}',
            type = 'passport-copies',
            file_name = '{$filename}'
    ";
    if (!mysqli_query($db, $sql)) {
        throw new Exception("Failed to attach passport copy file");
    }
}
/**
 * Soft delete given application's passport copy file.
 */
function softDeletePassportCopies(int $application_id)
{
    global $db;

    $sql = "Update application_files SET 
                is_deleted = '1'
                where application_id = '{$application_id}' and type = 'passport-copies' ";

    if (!mysqli_query($db, $sql)) {
        throw new Exception("Failed to soft delete passport copy file");
    }
}
/**
 * attach full photo to the application & upload 
 * the file to appropriate location.
 */
function addFullPhoto(int $application_id, $file)
{
    global $filesystem;
    global $db;

    $extension = $file["extension"];
    $filename = "FP-" . time() . "." . $extension;

    $dir = __DIR__ . "{$filesystem->applications}/{$application_id}";
    $file_path = "{$dir}/{$filename}";

    if (!file_exists($dir)) {
        mkdir($dir, 0755, true);
    }

    if (!move_uploaded_file($file["file"]["tmp_name"], $file_path)) {
        throw new Exception("Failed to upload full photo");
    }

    $file_path = mysqli_escape_string($db, $file_path);

    $sql = "INSERT INTO application_files SET 
            application_id = '{$application_id}',
            type = 'full-photo',
            file_name = '{$filename}'
    ";
    if (!mysqli_query($db, $sql)) {
        throw new Exception("Failed to attach full photo file");
    }
}
/**
 * Soft delete given application's full photo file.
 */
function softdeleteFullPhoto(int $application_id)
{
    global $db;

    $sql = "Update application_files SET 
                is_deleted = '1'
                where application_id = '{$application_id}' and type = 'full-photo' ";

    if (!mysqli_query($db, $sql)) {
        throw new Exception("Failed to soft delete full photo file");
    }
}
/**
 * attach passport size photo to the application & upload 
 * the file to appropriate location.
 */
function addPassportSizePhoto(int $application_id, $file)
{
    global $filesystem;
    global $db;

    $extension = $file["extension"];
    $filename = "PP-" . time() . "." . $extension;

    $dir = __DIR__ . "{$filesystem->applications}/{$application_id}";
    $file_path = "{$dir}/{$filename}";

    if (!file_exists($dir)) {
        mkdir($dir, 0755, true);
    }

    if (!move_uploaded_file($file["file"]["tmp_name"], $file_path)) {
        throw new Exception("Failed to upload passport photo");
    }

    $file_path = mysqli_escape_string($db, $file_path);

    $sql = "INSERT INTO application_files SET 
            application_id = '{$application_id}',
            type = 'passport-photo',
            file_name = '{$filename}'
    ";
    if (!mysqli_query($db, $sql)) {
        throw new Exception("Failed to attach passport photo file");
    }
}

/**
 * Soft delete given application's passport size photo file.
 */
function sloftDeletePassportSizePhoto(int $application_id)
{
    global $db;

    $sql = "Update application_files SET 
                is_deleted = '1'
                where application_id = '{$application_id}' and type = 'passport-photo' ";

    if (!mysqli_query($db, $sql)) {
        throw new Exception("Failed to soft delete passport photo file");
    }
}



/**
 * Get application by passport no
 */
function getApplicationByPassportNo(string $passport_no)
{
    global $db;

    $sql = "SELECT * from application where passport_no='{$passport_no}' limit 1";

    $results = mysqli_query($db, $sql);


    if (!$results) {
        throw new Exception("Failed to query applications");
    }
    if (mysqli_num_rows($results) < 1) {
        return null;
    }

    $application = (object)mysqli_fetch_assoc($results);

    $application->gender = getGenderById($application->gender_id);
    $application->visa_status = getVisaStatus($application->visa_status_id);


    $files = getApplicationFiles($application->id);

    $application->known_languages = getKnownLanguagesByApplicationId($application->id);


    foreach ($files as $type => $file) {
        $type = str_replace("-", "_", $type);
        $application->{$type} = $file;
    }

    return $application;
}
/**
 * Get application by application_id
 */
function getApplicationByID(string $id)
{
    global $db;

    global $db;

    $sql = "SELECT * from application where id='{$id}' limit 1";

    $results = mysqli_query($db, $sql);


    if (!$results) {
        throw new Exception("Failed to query applications");
    }
    if (mysqli_num_rows($results) < 1) {
        return null;
    }

    $application = (object)mysqli_fetch_assoc($results);

    $application->gender = getGenderById($application->gender_id);
    $application->visa_status = getVisaStatus($application->visa_status_id);


    $files = getApplicationFiles($application->id);

    $application->known_languages = getKnownLanguagesByApplicationId($application->id);


    foreach ($files as $type => $file) {
        $type = str_replace("-", "_", $type);
        $application->{$type} = $file;
    }

    return $application;
}
function getApplicationFiles($application_id)
{
    global $db;
    $sql = "select * from application_files where application_id = '{$application_id}' and is_deleted = '0'";
    $results = mysqli_query($db, $sql);

    print_r(mysqli_error($db));

    $files = [];
    while ($result = mysqli_fetch_assoc($results)) {
        $filename = $result["file_name"];
        $files[$result["type"]] = $filename;
    }

    return $files;
}


/**
 * Get known languages of given application 
 */
function getKnownLanguagesByApplicationId(int $application_id)
{
    global $db;
    $sql = "SELECT * FROM application_known_languages inner join known_languages on known_languages.id = application_known_languages.known_language_id where application_id='{$application_id}'";

    $results = mysqli_query($db, $sql);

    if (!$results) {
        throw new Exception(mysqli_error($db));
    }

    $languages = [];
    while ($result  = mysqli_fetch_assoc($results)) {
        $languages[] = [
            "id" => $result["id"],
            "language" => $result["language"]
        ];
    }

    return $languages;
}

/**
 * Get all genders
 */
function isPassportNoExists(string $passport_no)
{
    global $db;

    $passport_no = mysqli_escape_string($db, trim($passport_no));

    if (strlen($passport_no) < 1) {
        return false;
    }

    $sql = "SELECT * from application where passport_no='{$passport_no}'";

    $results = mysqli_query($db, $sql);

    if (!$results) {
        throw new Exception(mysqli_error($db));
    }

    return mysqli_num_rows($results) > 0;
}
/**
 * Get all genders
 */
function isApplicationIDExists(string $application_id)
{
    global $db;

    $application_id = mysqli_escape_string($db, trim($application_id));

    if (strlen($application_id) < 1) {
        return false;
    }

    $sql = "SELECT * from application where id='{$application_id}'";

    $results = mysqli_query($db, $sql);

    if (!$results) {
        throw new Exception(mysqli_error($db));
    }

    return mysqli_num_rows($results) > 0;
}
