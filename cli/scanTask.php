<?php
defined('BASE_APP_PATH')
|| define('BASE_APP_PATH', dirname(__DIR__));
// Define path to application directory
defined('APPLICATION_PATH')
|| define('APPLICATION_PATH', BASE_APP_PATH . '/application');

require_once BASE_APP_PATH . '/cli/bootstrap.php';

$cliParams = getopt('',['start','stop']);

if (isset($cliParams['start'])) {
    \EasySub\Task\Daemon::start();
}
if (isset($cliParams['stop'])) {
    \EasySub\Task\Daemon::stop();
}