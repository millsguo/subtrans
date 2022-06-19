<?php

namespace EasySub\Tools;

use Zend_Log;
use Zend_Log_Exception;
use Zend_Log_Writer_Stream;

class Log
{
    protected static Zend_Log $logObj;

    /**
     * 检查日志对象是否初始化
     *
     * @return void
     * @throws Zend_Log_Exception
     */
    protected static function checkLog()
    {
        if (!isset(self::$logObj)) {
            self::$logObj = new Zend_Log();
            self::$logObj->addWriter(new Zend_Log_Writer_Stream('php://output'));
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
        try {
            self::checkLog();
            $writer = new Zend_Log_Writer_Stream($configFile);
            self::$logObj->addWriter($writer);
            return true;
        } catch (Zend_Log_Exception $e) {
            echo $e->getMessage();
            echo $e->getTraceAsString();
            return false;
        }
    }

    public static function info(string $message)
    {
        self::log($message, 6);
    }

    public static function warn(string $message)
    {
        self::log($message, 4);
    }

    public static function err(string $message)
    {
        self::log($message, 3);
    }

    public static function debug($message)
    {
        if (is_string($message) || is_int($message) || is_float($message)) {
            $message = trim($message);
        } else {
            $message = print_r($message, true);
        }
        $logDebug = $_ENV['LOG_DEBUG'] ?? 'false';
        if (strtolower($logDebug) == 'true') {
            self::log($message, 7);
        }
    }

    public static function critical(string $message)
    {
        self::log($message, 2);
    }

    public static function alert(string $message)
    {
        self::log($message, 1);
    }

    public static function emerge(string $message)
    {
        self::log($message, 0);
    }

    public static function notice(string $message)
    {
        self::log($message, 5);
    }

    /**
     * @param string $message
     * @param int $priority
     * @return void
     */
    protected static function log(string $message, int $priority)
    {
        try {
            self::checkLog();
            self::$logObj->log($message, $priority);
        } catch (Zend_Log_Exception $e) {
            self::$logObj->err($e->getMessage());
        }
    }
}