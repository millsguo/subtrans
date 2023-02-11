<?php
// Define path to application directory
defined('APPLICATION_PATH')
|| define('APPLICATION_PATH', realpath(__DIR__ . '/../'));

require_once APPLICATION_PATH . '/cli/bootstrap.php';

use EasySub\CheckSub;
use EasySub\Tools\Config;
use EasySub\Tools\Log;
use EasySub\TransSub;

//获取版本号
$currentVersion = Config::getVersion();

Log::info('SubTrans Version ' . $currentVersion);

//配置文件路径
$configPath = APPLICATION_PATH . '/config/config.ini';


//初始化Sqlite
Log::debug('Sqlite 初始化');
$db = new EasySub\Tools\Db(['dbname' => APPLICATION_PATH . '/config/database_subtrans'], 'sqlite');

try {
    $configArray = Config::getConfig($configPath);

    $translationArray = $configArray->translation;
    if (isset($translationArray->api_name)) {
        $_ENV['API_NAME'] = $translationArray->api_name;
    }
    if (isset($translationArray->enable_trans)) {
        $_ENV['ENABLE_TRANS'] = $translationArray->enable_trans;
    }
    if ($translationArray) {
        Log::info('使用配置文件');
        if (isset($translationArray->aliyun1)) {
            $aliyunArray = $translationArray->aliyun1->toArray();
            foreach ($aliyunArray as $key => $value) {
                $_ENV[strtoupper($key) . '_1'] = $value;
            }
        }
        if (isset($translationArray->aliyun2)) {
            $aliyunArray = $translationArray->aliyun2->toArray();
            foreach ($aliyunArray as $key => $value) {
                $_ENV[strtoupper($key) . '_2'] = $value;
            }
        }
    }

    TransSub::initTranslation();
    $isUseConfig = false;
    for ($i = 1;$i <= 3; $i++) {
        $moviesName = 'movies-' . $i;
        $tvName = 'tv-' . $i;
        if (isset($configArray->volume->{$moviesName})) {
            $dirPath = $configArray->volume->{$moviesName};
            Log::info('扫描配置电影目录：' . $dirPath);
            CheckSub::scanDir($dirPath);
            $isUseConfig = true;
        }
        if (isset($configArray->volume->{$tvName})) {
            $dirPath = $configArray->volume->{$tvName};
            if (!empty($dirPath)) {
                Log::info('扫描配置剧集目录：' . $dirPath);
                CheckSub::scanDir($dirPath,true);
                $isUseConfig = true;
            }
        } else {
            Log::info('配置剧集目录不存在');
        }

        if (!$isUseConfig) {
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
    }
} catch (Exception $e) {
    echo $e->getMessage();
    echo $e->getTraceAsString();
}
