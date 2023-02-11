<?php
// Define path to application directory
defined('APPLICATION_PATH')
|| define('APPLICATION_PATH', realpath(__DIR__ . '/../'));

require_once APPLICATION_PATH . '/cli/bootstrap.php';

use EasySub\Tools\Config;
use EasySub\Tools\Log;

$currentVersion = Config::updateVersion();

//获取配置文件
$configPath = APPLICATION_PATH . '/config/config.ini';
if (is_readable($configPath)) {
    Config::setConfig($configPath);
}

Log::info('SubTrans Version ' . $currentVersion);


try {

} catch (Exception $e) {
    echo $e->getMessage();
    echo $e->getTraceAsString();
}
