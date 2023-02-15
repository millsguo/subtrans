<?php
// Define path to application directory
defined('APPLICATION_PATH')
|| define('APPLICATION_PATH', realpath(__DIR__ . '/../'));

require_once APPLICATION_PATH . '/cli/bootstrap.php';

// Define path to application directory
defined('CLI_PATH')
|| define('CLI_PATH', realpath(__DIR__));

$currentVersion = \EasySub\Tools\Config::getVersion();
echo "开始打包，版本号：" . $currentVersion . "\r\n";

echo '进入打包工作目录' . CLI_PATH . '/../' . "\r\n";
exec('cd ' . CLI_PATH . '/../', $output, $returnVar);

echo '开始打包' . "\r\n";
exec('docker build -t millsguo/subtrans .', $output, $returnVar);
if ($returnVar !== 0) {
    echo "打包出错\r\n";
    exit();
}
echo "打包完成\r\n";

echo "latest开始上传\r\n";
exec('docker push millsguo/subtrans', $output, $returnVar);
if ($returnVar !== 0) {
    echo "上传出错\r\n";
    exit();
}
echo "latest上传完成\r\n";

//echo "生成" . $currentVersion . " 版本标签\r\n";
//exec('docker tag millsguo/subtrans millsguo/subtrans:' . $currentVersion, $output, $returnVar);
//
//echo "开始上传 " . $currentVersion . " 打包版本\r\n";
//exec('docker push millsguo/subtrans:' . $currentVersion, $output, $returnVar);
//echo "上传完成 " . $currentVersion . " 打包版本\r\n";

\EasySub\Tools\Config::updateVersion();
