<?php


require_once __DIR__ . "/../app/flow.php";
setFlowStarted();

require_once __DIR__ . "/../app/database.php";


function buildApplicationTables()
{
    global $db;

    // Create application related tables
    (function () use ($db) {
        // Create tables
        mysqli_query($db, "
            CREATE TABLE visa_status (
                id bigint primary key not null AUTO_INCREMENT,
                status varchar(100)
            )
        ") or die("Error: " . mysqli_error($db));

        mysqli_query($db, "
            CREATE TABLE gender (
                id bigint primary key not null AUTO_INCREMENT,
                gender varchar(100)
            )
        ") or die("Error: " . mysqli_error($db));

        mysqli_query($db, "
            CREATE TABLE known_languages (
                id bigint primary key not null AUTO_INCREMENT,
                language varchar(100)
            );
        ") or die("Error: " . mysqli_error($db));


        mysqli_query($db, "
            CREATE TABLE application (
                id bigint not null AUTO_INCREMENT,
                passport_no varchar(255) not null,
                first_name varchar(255),
                last_name varchar(255),
                birth_date date,
                gender_id bigint,
                nationality varchar(100),
                arrival_date_oman date,
                return_date date,
                commission double,
                guaranty_period_start_date date,
                agent_name varchar(255),
                sale_amount double,
                sponsors_mobile_number varchar(20),
                sponsors_name varchar(255),
                sponsor_id varchar(255),
                full_payment double,
                bill_no varchar(255),
                visa_status_id bigint not null,
                PRIMARY KEY (id),
                FOREIGN KEY (visa_status_id) references visa_status(id),
                FOREIGN KEY (gender_id) references gender(id)
            );

        ") or die("Error: " . mysqli_error($db));

        mysqli_query($db, "
            CREATE TABLE application_known_languages (
                id bigint primary key AUTO_INCREMENT,
                application_id bigint,
                known_language_id bigint,
                FOREIGN KEY (application_id) references application(id),
                FOREIGN KEY (known_language_id) references known_languages(id)
            );
        ") or die("Error: " . mysqli_error($db));

        mysqli_query($db, "
            CREATE TABLE application_files (
                id bigint primary key AUTO_INCREMENT,
                application_id bigint,
                type varchar(100),
                file_name varchar(255),
                is_deleted boolean default 0,
                FOREIGN KEY (application_id) references application(id)
            );
        ") or die("Error: " . mysqli_error($db));

        //   Insert default data
        mysqli_query($db, "
            INSERT INTO known_languages(language) VALUES
                ('Arabic'),
                ('Hindi'),
                ('Other')
        ") or die("Error: " . mysqli_error($db));

        mysqli_query($db, "
            INSERT INTO gender(gender) VALUES
                ('Female'),
                ('Male')
        ") or die("Error: " . mysqli_error($db));

        mysqli_query($db, "
            INSERT INTO visa_status(status) VALUES
                ('Visit'),
                ('Employment')
        ") or die("Error: " . mysqli_error($db));
    })();
}
function buildAdminTables()
{
    global $db;

    // Create application related tables
    (function () use ($db) {
        // Create tables

        mysqli_query($db, "
        CREATE TABLE admin_types (
            id bigint not null AUTO_INCREMENT,
            name varchar(100),
            PRIMARY KEY (id)
        );
    ") or die("Error: " . mysqli_error($db));

        mysqli_query($db, "
            CREATE TABLE admins (
                id bigint not null AUTO_INCREMENT,
                first_name varchar(100),
                last_name varchar(100),
                email varchar(100) UNIQUE,
                type_id bigint,
                password varchar(500) not null,
                is_deactivated boolean default 0,
                created_at datetime,
                last_updated_at datetime,
                PRIMARY KEY (id),
                FOREIGN KEY (type_id) references admin_types(id)
            );

        ") or die("Error: " . mysqli_error($db));

        mysqli_query($db, "
            CREATE TABLE admin_reset_password_requests (
                id bigint not null AUTO_INCREMENT,
                admin_id bigint,
                reset_password_token varchar(255) NOT NULL,
                created_at datetime,
                expires_at datetime,
                PRIMARY KEY (id),
                FOREIGN KEY (admin_id) references admins(id)
            );

        ") or die("Error: " . mysqli_error($db));



        //   Insert default data
        mysqli_query($db, "
            INSERT INTO admin_types (name) VALUES
                ('Super Admin'),
                ('Admin');
        ") or die("Error: " . mysqli_error($db));

        mysqli_query($db, "
            INSERT INTO admins set
                first_name = 'System',
                last_name = 'Admin',
                email = 'system@admin.com',
                type_id = 1,
                password = 's',
                created_at = NOW(),
                last_updated_at = NOW()
        ") or die("Error: " . mysqli_error($db));
    })();
}

buildApplicationTables();
buildAdminTables();
