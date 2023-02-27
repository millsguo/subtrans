<?php

namespace EasySub\Video;

use EasySub\Tools\Config;

class Store
{
    /**
     * 电影库路径
     * @var array|string[]
     */
    private static array $movieLibrary = [];

    /**
     * 剧集库路径
     * @var array|string[]
     */
    private static array $tvLibrary = [];

    /**
     * 获取有效电影库路径
     * @return array
     */
    public static function getMovieLibrary(): array
    {
        $returnArray = [];
        foreach (self::$movieLibrary as $moviePath) {
            if (is_readable($moviePath) || is_dir($moviePath)) {
                $returnArray[] = $moviePath;
            }
        }
        return $returnArray;
    }

    /**
     * 增加电影库路径
     * @param string $moviePath
     * @return bool
     */
    public static function addMovieLibrary(string $moviePath): bool
    {
        if (is_dir($moviePath)) {
            self::$movieLibrary[] = $moviePath;
            return true;
        }
        return false;
    }

    /**
     * 获取有效剧集库路径
     * @return array
     */
    public static function getTvLibrary(): array
    {
        $returnArray = [];
        foreach (self::$tvLibrary as $tvPath) {
            if (is_readable($tvPath) || is_dir($tvPath)) {
                $returnArray[] = $tvPath;
            }
        }
        return $returnArray;
    }

    /**
     * 增加剧集库路径
     * @param string $tvPath
     * @return bool
     */
    public static function addTvLibrary(string $tvPath): bool
    {
        if (is_dir($tvPath)) {
            self::$tvLibrary[] = $tvPath;
            return true;
        }
        return false;
    }

    /**
     * 从配置文件中初始化电影库和剧集库
     * @return void
     */
    public static function initLibraryFromConfig(): void
    {
        $config = Config::getConfig(BASE_APP_PATH . '/config/config.ini','volume');
        $configArray = $config->toArray();
        for ($i = 1; $i <= 3; $i++) {
            $movieKey = 'movies-' . $i;
            $tvKey = 'tv-' . $i;
            if (isset($configArray[$movieKey]) && !empty($configArray[$movieKey])) {
                self::addMovieLibrary($configArray[$movieKey]);
            }
            if (isset($configArray[$tvKey]) && !empty($configArray[$tvKey])) {
                self::addTvLibrary($configArray[$tvKey]);
            }
        }
    }
}