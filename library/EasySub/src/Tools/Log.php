<?php

namespace EasySub\Tools;

use Zend_Log;
use Zend_Log_Exception;
use Zend_Log_Writer_Stream;

class Log
{
    protected static Zend_Log $logObj;

    /**
     * 是否初始化
     * @var bool
     */
    private static bool $isInit = false;

    /**
     * 检查日志对象是否初始化
     *
     * @return void
     * @throws Zend_Log_Exception
     */
    protected static function checkLog(): void
    {
        if (!isset(self::$logObj)) {
            self::$logObj = new Zend_Log();
            if (PHP_SAPI === 'cli') {
                self::$logObj->addWriter(new Zend_Log_Writer_Stream('php://output'));
            } else {
                self::$logObj->addWriter(new Zend_Log_Writer_Stream(BASE_APP_PATH . '/config/logs/web-' . date('Ymd') . '.log'));
            }
        }
    }

    /**
     * 日志初始化，并指定日志文件
     *
     * @param string $configFile
     * @return bool
     */
    public static function init(string $configFile): bool
    {
        if (self::$isInit) {
            return true;
        }
        try {
            self::checkLog();
            $writer = new Zend_Log_Writer_Stream($configFile);
            self::$logObj->addWriter($writer);
            self::$isInit = true;
            return true;
        } catch (Zend_Log_Exception $e) {
            echo $e->getMessage();
            echo $e->getTraceAsString();
            return false;
        }
    }

    public static function info(mixed $message): void
    {
        self::log($message, 6);
    }

    public static function warn(mixed $message): void
    {
        self::log($message, 4);
    }

    public static function err(mixed $message): void
    {
        self::log($message, 3);
    }

    public static function debug(mixed $message): void
    {
        self::log($message, 7);
    }

    public static function critical(mixed $message): void
    {
        self::log($message, 2);
    }

    public static function alert(mixed $message): void
    {
        self::log($message, 1);
    }

    public static function emerge(mixed $message): void
    {
        self::log($message, 0);
    }

    public static function notice(mixed $message): void
    {
        self::log($message, 5);
    }

    /**
     * @param string $message
     * @param int $priority
     * @return void
     */
    public static function log(mixed $message, int $priority = 6): void
    {
        try {
            self::checkLog();
            if (is_string($message) || is_int($message) || is_float($message)) {
                $message = trim($message);
            } else {
                $message = print_r($message, true);
            }
            self::$logObj->log($message, $priority);
        } catch (Zend_Log_Exception $e) {
            self::$logObj->err($e->getMessage());
        }
    }
}