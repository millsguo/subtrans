<?php

namespace EasySub\Task;

use EasySub\Tools\Log;

class Command
{
    /**
     * @var true
     */
    private static bool $scanTaskRunning = false;

    /**
     * 执行命令
     * @return bool|array
     */
    public static function runScan(): bool|array
    {
        if (self::$scanTaskRunning) {
            Log::log('扫描子任务正在执行');
            return false;
        }
        $command = 'php ' . BASE_APP_PATH . '/cli/scanTask.php';
        Log::info('主任务进程启动');
        $subPid = pcntl_fork();
        if ($subPid === -1) {
            Log::log('子进程失败');
            return false;
        }

        if ($subPid) {
            Log::info('子进程ID：' . $subPid);
            pcntl_wait($status);
            self::$scanTaskRunning = true;
            return true;
        }

        Log::info('子进程启动');
        exec($command, $outArray,$returnState);
        Log::info('子进程信息');
        Log::info($outArray);
        exit();
    }

    /**
     * 扫描子任务完成
     * @return void
     */
    public static function stopScan(): void
    {
        if (self::$scanTaskRunning) {
            self::$scanTaskRunning = false;
        }
    }
}