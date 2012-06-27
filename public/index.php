<?php
// Define Application Path
define('APPLICATION_PATH', realpath("../application"));

// Define Application Configuration file path
define('APPLICATION_ENV', "development");

// Require DTech_Application_Bootstrap class file
require_once '../library/DTech/Application.php';
// Initialize the application
$application = new DTech_Application_Bootstrap(
        APPLICATION_PATH . "/config/application.ini",
        APPLICATION_ENV
);

// Bootstrap and run the application
$application->bootstrap();