<?php
// Define path to application directory
defined('APPLICATION_PATH')
|| define('APPLICATION_PATH', realpath(__DIR__ . '/../'));

require_once APPLICATION_PATH . '/cli/bootstrap.php';


try {
    \EasySub\Task\Command::run('ps -ef');
} catch (Exception $e) {
    echo $e->getMessage();
    echo $e->getTraceAsString();
}
