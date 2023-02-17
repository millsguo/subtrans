<?php
// Define path to application directory
defined('BASE_APP_PATH')
|| define('BASE_APP_PATH', dirname(__DIR__));

require_once BASE_APP_PATH . '/cli/bootstrap.php';

use EasySub\CheckSub;
use EasySub\Tools\Config;
use EasySub\Tools\Log;

//获取版本号
$currentVersion = Config::getVersion();

Log::info('SubTrans Version ' . $currentVersion);

//配置文件路径
$configPath = BASE_APP_PATH . '/config/config.ini';

//设置默认字符编码
mb_internal_encoding('UTF-8');

//初始化Sqlite
Log::debug('Sqlite 初始化');
$db = new EasySub\Tools\Db(['dbname' => BASE_APP_PATH . '/database/subtrans'], 'sqlite');

CheckSub::init();
CheckSub::scanAll();