<?php

define('BASE_APP_PATH', dirname(__DIR__));

// Define path to application directory
defined('APPLICATION_PATH')
    || define('APPLICATION_PATH', BASE_APP_PATH . '/application');

// Define application environment
defined('APPLICATION_ENV')
    || define('APPLICATION_ENV', (getenv('APPLICATION_ENV') ?: 'production'));

// Ensure library/ is on include_path
set_include_path(implode(PATH_SEPARATOR, array(
    dirname(BASE_APP_PATH) . '/library',
    dirname(BASE_APP_PATH) . '/vendor',
    get_include_path(),
)));

require_once dirname(__DIR__) . '/vendor/autoload.php';

/** Zend_Application */
#require_once 'Zend/Application.php';
// Create application, bootstrap, and run
try {
    $application = new Zend_Application(
        APPLICATION_ENV,
        APPLICATION_PATH . '/configs/application.ini'
    );
    $application->bootstrap()->run();
} catch (Zend_Application_Exception $e) {
    \EasySub\Tools\Log::info($e->getMessage());
    \EasySub\Tools\Log::info($e->getTraceAsString());
}
