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
        $subPid = pcntl_fork();
        if ($subPid === -1) {
            Log::log('子进程失败');
            return false;
        }

        if ($subPid) {
            pcntl_wait($status);
            self::$scanTaskRunning = true;
            return true;
        }

        exec($command, $outArray,$returnState);
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