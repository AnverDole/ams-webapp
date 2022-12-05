# Application/Form Management System
This is a simple CRUD application for a foreign employment agency. it facilitates a way of managing their clients (employees') details.

## Deploy to the developer machine
First, you need to install git.
Create a new folder for the project root directory.

Open the cmd(console) and type cd, and then past the previously created project directory path.

$ cd ./project-root-directory

Type git clone, and then paste the URL of the repository.
$ git clone https://github.com/AnverDole/ams-webapp

Then you need to install all dependencies.
$ composer install

You need to change the environment variables in the app\configs.php file.
    
    $config = (object)[
        "appname" => "AMS",
        "environment" => "production", //change the environment 
        "catch_version_suffix" => "v1",
        "debug" => true,
        "database" => [
            "host" => "localhost",
            "username" => "root",
            "password" => "",
            "db" => "agency_app_db",
        ]
    ];
    
    $filesystem = (object)[
        "applications" => "/../data/applications"
    ];
    
    $urlConfig = (object)[
        "domain" => "http://localhost",
        "root" => "/application-management-system/agency-app", //change this app path
    ];
    
    $mailConfig = (object)[
        "username" => "something@gmail.com",
        "password" => "password",
        "host" => "smtp.gmail.com",
        "port" => 465,
        "email" => "something@gmail.com"
    ];

Finally, you can deploy this source code into the hosting environment or do the development on your local machine.
