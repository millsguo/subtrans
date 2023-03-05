<?php

namespace EasySub\Task;

use EasySub\CheckSub;
use EasySub\Tools\Log;
use EasySub\Translated\TransApi;
use EasySub\TransSub;

class Command
{
    /**
     * @var true
     */
    private static bool $scanTaskRunning = false;

    /**
     * @var bool
     */
    private static bool $checkTaskRunning = false;

    /**
     * 执行命令
     * @return bool
     */
    public static function runScan(): bool
    {
        if (self::$scanTaskRunning) {
            Log::log('扫描子任务正在执行');
            return false;
        }

        TransApi::initApi();
        TransSub::initTranslation();

        $queueObj = new \EasySub\Task\Queue();
        $count = 10;
        $page = 1;
        while ($rows = $queueObj->fetchTask($count,$page)) {
            if (is_countable($rows) && count($rows) > 0) {
                foreach ($rows as $row) {
                    if (strtolower($row->task_type) === 'tv') {
                        Log::info('扫描剧集：' . $row->target_path);
                        checkSub::scanTvDir($row->target_path);
                    } else {
                        Log::info('扫描电影：' . $row->target_path);
                        checkSub::scanDir($row->target_path);
                    }
                    $queueObj->deleteTask($row->id);
                }
            } else {
                Log::log('任务队列为空');
                break;
            }
        }
        self::stopScan();
        Log::log('扫描任务完成');
        return true;
    }

    /**
     * 更新视频HASH
     * @return bool
     */
    public static function checkAndSetHash(): bool
    {
        if (self::$checkTaskRunning) {
            Log::log('检测视频及设置视频hash子任务正在执行');
            return false;
        }

        CheckSub::checkAll();
        self::stopCheck();
        Log::log('更新视频HASH任务完成');
        return true;
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

    /**
     * 检查子任务完成
     * @return void
     */
    public static function stopCheck(): void
    {
        if (self::$checkTaskRunning) {
            self::$checkTaskRunning = false;
        }
    }
}