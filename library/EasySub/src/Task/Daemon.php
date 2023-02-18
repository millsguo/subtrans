<?php

namespace EasySub\Task;

use EasySub\Tools\Config;
use EasySub\Tools\Db;
use EasySub\Tools\Log;

class Daemon
{

    /**
     * @var bool 是否正在运行
     */
    private static bool $isRunning = false;

    /**
     * @var int 父进程ID
     */
    private static int $pid;

    /**
     * 开始任务
     * @return bool|void
     */
    public static function start()
    {
        if (self::$isRunning) {
            Log::info('任务已启动，不能重复启动');
            return false;
        }
        do {
            //创建子进程
            $pid = pcntl_fork();

            if ($pid === -1) {
                Log::err('子进程失败');
            } elseif ($pid === 0) {
                //子进程
                $baseAppPath = dirname(__DIR__) . '/../../..';
                require_once $baseAppPath . '/cli/bootstrap.php';

                //获取版本号
                $currentVersion = Config::getVersion();

                Log::info('SubTrans Version ' . $currentVersion);

                //配置文件路径
                $configPath = BASE_APP_PATH . '/config/config.ini';

                //设置默认字符编码
                mb_internal_encoding('UTF-8');

                //初始化Sqlite
                Log::debug('Sqlite 初始化');
                $db = new Db(['dbname' => BASE_APP_PATH . '/database/subtrans'], 'sqlite');

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

                    Command::runScan();
                } catch (\Exception $e) {
                    echo $e->getMessage();
                    echo $e->getTraceAsString();
                }
                exit('子进程退出');
            } else {
                //父进程
                Log::info('子进程ID：' . $pid);
                self::$pid = $pid;
                self::$isRunning = true;
                pcntl_wait($status);
                Log::info('休眠10秒');
                sleep(10);
            }
        } while ($pid !== -1);
    }

    /**
     * 停止进程
     * @return bool
     */
    public static function stop(): bool
    {
        if (!self::$isRunning) {
            return false;
        }
        $result = posix_kill(self::$pid,SIGTERM);
        if ($result) {
            self::$isRunning = false;
            return true;
        }
        return false;
    }
}