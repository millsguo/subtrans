<?php
defined('BASE_APP_PATH')
|| define('BASE_APP_PATH', dirname(__DIR__));
// Define path to application directory
defined('APPLICATION_PATH')
|| define('APPLICATION_PATH', BASE_APP_PATH . '/application');

require_once BASE_APP_PATH . '/cli/bootstrap.php';

use EasySub\CheckSub;
use EasySub\Tools\Config;
use EasySub\Tools\Log;
use EasySub\TransSub;

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

try {
    $configArray = Config::getConfig($configPath);

    $translationArray = $configArray->translation;
    if (isset($translationArray->api_name)) {
        $_ENV['API_NAME'] = $translationArray->api_name;
        Log::info('找到翻译API配置:' . $_ENV['API_NAME']);
    }
    if (isset($translationArray->enable_trans) && ($translationArray->enable_trans === 1 || $translationArray->enable_trans === "1")) {
        $_ENV['ENABLE_TRANS'] = true;
        Log::info('启用机器翻译');
    }
    if ($translationArray) {
        Log::info('载入配置文件');
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

    $queueObj = new \EasySub\Task\Queue();
    $count = 10;
    $page = 1;
    while ($rows = $queueObj->fetchTask($count,$page)) {
        foreach ($rows as $row) {
            if (strtolower($row->task_type) === 'tv') {
                $isSeason = true;
                Log::info('扫描剧集：' . $row->target_path);
            } else {
                $isSeason = false;
                Log::info('扫描电影：' . $row->target_path);
            }
            checkSub::scanDir($row->target_path,$isSeason);
            $queueObj->deleteTask($row->id);
        }
    }
    Log::log('扫描任务完成');
} catch (Exception $e) {
    echo $e->getMessage();
    echo $e->getTraceAsString();
}
\EasySub\Task\Command::stopScan();
exit;
