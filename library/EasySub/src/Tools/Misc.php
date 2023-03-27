<?php

namespace EasySub\Tools;


use EasySub\Exception\MiscException;

class Misc
{
    /**
     * 获取目录列表
     *
     * @param string $basePath
     * @return array|false
     * @throws MiscException
     */
    public static function scanDir(string $basePath): bool|array
    {
        if (!is_dir($basePath)) {
            throw new MiscException($basePath . ' is not directory');
        }
        $dirArray = scandir($basePath);
        if (!$dirArray) {
            return false;
        }
        unset($dirArray[0], $dirArray[1]);

        return $dirArray;
    }

    /**
     * 检查文件是否存在，存在返回true，不存在返回false
     * @param string $filePath
     * @return bool
     */
    public static function checkFileExists(string $filePath): bool
    {
        if (!file_exists($filePath)) {
            clearstatcache(true,$filePath);
            if (!is_readable($filePath)) {
                return false;
            }
        }
        return true;
    }

    /**
     * 删除目录及目录下所有文件
     * @param string $dir
     * @return bool
     */
    public static function deleteDirectory(string $dir): bool
    {
        if (!file_exists($dir)) {
            return true;
        }

        if (!is_dir($dir)) {
            return unlink($dir);
        }

        foreach (scandir($dir) as $item) {
            if ($item === '.' || $item === '..') {
                continue;
            }

            if (!self::deleteDirectory($dir . DIRECTORY_SEPARATOR . $item)) {
                return false;
            }
        }

        return rmdir($dir);
    }

    /**
     * 返回目录和文件的完整路径
     * @param string $dir
     * @param string $file
     * @return string
     */
    public static function linkDirAndFile(string $dir, string $file): string
    {
        if (substr($dir,-1) === DIRECTORY_SEPARATOR) {
            return $dir . $file;
        }
        return $dir . DIRECTORY_SEPARATOR . $file;
    }
}