<?php
ini_set('max_execution_time',0);
ini_set('memory_limit', '500M');
set_time_limit(0);
date_default_timezone_set('Asia/Shanghai');

defined('BASE_APP_PATH')
|| define('BASE_APP_PATH', dirname(__DIR__) . '/');

// Ensure library/ is on include_path
set_include_path(implode(PATH_SEPARATOR, array(
    realpath(BASE_APP_PATH . '/library'),
    realpath(BASE_APP_PATH . '/vendor'),
    get_include_path(),
)));

require_once BASE_APP_PATH . '/vendor/autoload.php';

$logFile = BASE_APP_PATH . '/config/logs/cli-' . date('Ymd') . '.log';

\EasySub\Tools\Log::init($logFile);
