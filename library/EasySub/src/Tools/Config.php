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
     * @param string $configFile
     * @param string $section
     * @param bool $allowModifications
     * @return Zend_Config_Ini|bool
     */
    public static function getConfig(string $configFile,string $section = '',bool $allowModifications = false): Zend_Config_Ini|bool
    {
        try {
            if (!file_exists($configFile)) {
                Log::info('配置文件不存在：' . $configFile);
                return false;
            }
            return new Zend_Config_Ini($configFile,$section,['allowModifications' => $allowModifications]);
        } catch (Zend_Config_Exception $e) {
            Log::debug($e->getMessage());
            return false;
        }
    }

    /**
     * 写配置文件
     * @param string $configFile
     * @param string $section
     * @param array|Zend_Config_Ini $data
     * @return bool
     */
    public static function writeConfig(string $configFile, string $section, array|Zend_Config_Ini $data): bool
    {
        try {
            if ($data instanceof Zend_Config_Ini) {
                $configObj = $data;
            } elseif (is_array($data)) {
                $configObj = new \Zend_Config([],true);
                $configObj->{$section} = [];
                $configObj->setExtend($section);
                foreach ($data as $key => $value) {
                    $configObj->{$section}->{$key} = $value;
                }
            } else {
                Log::info('写入数据格式错误');
                return false;
            }

            $writeObj = new \Zend_Config_Writer_Ini();
            $writeObj->setConfig($configObj);
            $writeObj->setFilename($configFile);
            $writeObj->write();
            return true;
        } catch (Zend_Config_Exception $e) {
            Log::info($e->getMessage());
            return false;
        }
    }

    /**
     * 返回当前版本号，同时版本号+1
     * @return string
     */
    public static function updateVersion(): string
    {
        //版本文件路径
        $versionPath = APPLICATION_PATH . '/config/version.ini';
        if (!is_file($versionPath)) {
            Log::info('版本文件不存在,' . $versionPath);
            $versionData = [
                'main_version' => 0,
                'sub_version'   => 3,
                'major_version' => 6,
                'full_version'  => '0.3.6'
            ];
            self::writeConfig($versionPath,'version',$versionData);
        }
        $versionConfig = self::getConfig($versionPath,'version',true);
        $outVersion = $versionConfig->full_version;
        Log::info(print_r($versionConfig,true));
        if ($versionConfig->major_version < 10) {
            $versionConfig->major_version++;
        } elseif ($versionConfig->sub_version < 10) {
            $versionConfig->major_version = 1;
            $versionConfig->sub_version++;
        } else {
            $versionConfig->main_verion++;
        }
        $versionConfig->full_version = $versionConfig->main_verion . '.' . $versionConfig->sub_version . '.' . $versionConfig->major_version;
        self::writeConfig($versionPath,'version',$versionConfig);
        return $outVersion;
    }

    /**
     * 获取版本号
     * @return mixed
     */
    public static function getVersion(): mixed
    {
        //版本文件路径
        $versionPath = APPLICATION_PATH . '/config/version.ini';
        $versionConfig = self::getConfig($versionPath,'version',false);
        if (!$versionConfig) {
            return '0.0.0';
        }
        return $versionConfig->full_version;
    }
}