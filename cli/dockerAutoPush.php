<?php
// Define path to application directory
defined('BASE_APP_PATH')
|| define('BASE_APP_PATH', dirname(__DIR__) . '/');

require_once BASE_APP_PATH . '/cli/bootstrap.php';

// Define path to application directory
defined('CLI_PATH')
|| define('CLI_PATH', realpath(__DIR__));

$runParams = getopt('',['test','stable','tag']);

$isTest = false;
$isStable = false;
$isTag = false;
if (isset($runParams['test'])) {
    $isTest = true;
}
if (isset($runParams['stable'])) {
    $isStable = true;
}
if (isset($runParams['tag'])) {
    $isTag = true;
}
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

if ($isStable) {
    echo "latest稳定版开始上传\r\n";
    exec('docker push millsguo/subtrans', $output, $returnVar);
    if ($returnVar !== 0) {
        echo "上传出错\r\n";
        exit();
    }
    echo "latest稳定版上传完成\r\n";
}
if ($isTag) {
    echo "生成" . $currentVersion . " 版本标签\r\n";
    exec('docker tag millsguo/subtrans millsguo/subtrans:' . $currentVersion, $output, $returnVar);

    echo "开始上传 " . $currentVersion . " 打包版本\r\n";
    exec('docker push millsguo/subtrans:' . $currentVersion, $output, $returnVar);
    if ($returnVar !== 0) {
        echo "上传出错\r\n";
        exit();
    }
    echo "上传完成 " . $currentVersion . " 打包版本\r\n";
}
if ($isTest) {
    echo "生成" . $currentVersion . " 测试版本标签\r\n";
    exec('docker tag millsguo/subtrans millsguo/subtrans:test', $output, $returnVar);

    echo "开始上传 " . $currentVersion . " 测试版\r\n";
    exec('docker push millsguo/subtrans:test', $output, $returnVar);
    if ($returnVar !== 0) {
        echo "上传出错\r\n";
        exit();
    }
    echo "上传完成 " . $currentVersion . " 测试版\r\n";
}
\EasySub\Tools\Config::updateVersion();
