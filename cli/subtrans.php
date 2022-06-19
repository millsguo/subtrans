<?php
// Define path to application directory
defined('APPLICATION_PATH')
|| define('APPLICATION_PATH', realpath(dirname(__FILE__) . '/../'));

require_once APPLICATION_PATH . '/cli/bootstrap.php';

use EasySub\Tools\Config;
use EasySub\Tools\Log;
use EasySub\Translated\TransApi;
use EasySub\TransSub;
use EasySub\CheckSub;

require_once APPLICATION_PATH . '/cli/version.php';

//获取配件
Config::setConfig(APPLICATION_PATH . '/config/local-config.ini');

Log::info('SubTrans Version ' . ST_VERSION);

//初始化Sqlite
Log::debug('Sqlite 初始化');
$db = new EasySub\Tools\Db(['dbname' => APPLICATION_PATH . '/database/subtrans'], 'sqlite');

$translationArray = [];
$apiName = $_ENV['API_NAME'] ?? 'aliyun';
$enableTrans = $_ENV['ENABLE_TRANS'] ?? false;
$transApi = new TransApi();


try {
    $configArray = Config::getConfig();
    if ($configArray) {
        Log::info('使用配置文件');
        if (isset($configArray['translation']['aliyun1'])) {
            foreach ($configArray['translation']['aliyun1'] as $key => $value) {
                $_ENV[strtoupper($key) . '_1'] = $value;
            }
        }
        if (isset($configArray['translation']['aliyun2'])) {
            foreach ($configArray['translation']['aliyun2'] as $key => $value) {
                $_ENV[strtoupper($key) . '_2'] = $value;
            }
        }
    }

    TransSub::initTranslation($apiName, $translationArray);

    for ($i = 1;$i <= 3; $i++) {
        if (isset($configArray['volume']['movies-' . $i])) {
            $dirPath = $configArray['volume']['movies-' . $i];
            Log::info('扫描配置电影目录：' . $dirPath);
            CheckSub::scanDir($dirPath);
        }
        if (isset($configArray['volume']['tv-' . $i])) {
            $dirPath = $configArray['volume']['tv-' . $i];
            if (!empty($dirPath)) {
                Log::info('扫描配置剧集目录：' . $dirPath);
                CheckSub::scanDir($dirPath,true);
            }
        } else {
            Log::info('配置剧集目录不存在');
        }

        Log::info('开始处理挂载目录');
        $moviesPath = '/data/movies-' . $i;
        $tvPath = '/data/tv-' . $i;
        if (is_dir($moviesPath)) {
            Log::info('扫描挂载电影目录：' . $moviesPath);
            CheckSub::scanDir($moviesPath);
        } else {
            Log::info('目录不存在或未挂载 [' . $moviesPath . ']');
        }

        if (is_dir($tvPath)) {
            Log::info('扫描挂载剧集目录：' . $tvPath);
            CheckSub::scanDir($tvPath, true);
        } else {
            Log::info('目录不存在或未挂载 [' . $tvPath . ']');
        }
    }
} catch (Exception $e) {
    echo $e->getMessage();
    echo $e->getTraceAsString();
}
