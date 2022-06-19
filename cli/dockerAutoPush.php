<?php
// Define path to application directory
defined('CLI_PATH')
|| define('CLI_PATH', realpath(dirname(__FILE__) ));
require_once CLI_PATH . '/version.php';

echo '进入打包工作目录' . CLI_PATH . '/../' . "\r\n";
exec('cd ' . CLI_PATH . '/../', $output, $returnVar);

echo '开始打包' . "\r\n";
exec('docker build -t millsguo/subtrans .', $output, $returnVar);
echo "打包完成\r\n";

echo "latest开始上传\r\n";
exec('docker push millsguo/subtrans', $output, $returnVar);
echo "latest上传完成\r\n";

echo "生成" . ST_VERSION . " 版本标签\r\n";
exec('docker tag millsguo/subtrans millsguo/subtrans:' . ST_VERSION, $output, $returnVar);

echo "开始上传 " . ST_VERSION . " 打包版本\r\n";
exec('docker push millsguo/subtrans:' . ST_VERSION, $output, $returnVar);
echo "上传完成 " . ST_VERSION . " 打包版本\r\n";
