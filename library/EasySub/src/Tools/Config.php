<?php

namespace EasySub\Tools;

use Zend_Config_Exception;
use Zend_Config_Ini;

class Config
{
    protected static Zend_Config_Ini $config;

    /**
     * 设置配置文件
     *
     * @param string $configFile
     * @return void
     */
    public static function setConfig(string $configFile): void
    {
        try {
            if (file_exists($configFile)) {
                self::$config = new Zend_Config_Ini($configFile);
            }
        } catch (Zend_Config_Exception $e) {
            Log::debug($e->getMessage());
        }
    }

    /**
     * 获取配置信息
     *
     * @param string $section
     * @return array|false|mixed
     */
    public static function getConfig(string $section = ''): mixed
    {
        if (!isset(self::$config)) {
            return false;
        }
        $return = self::$config->toArray();
        if (empty($section)) {
            return $return;
        }
        return $return[$section] ?? false;
    }
}