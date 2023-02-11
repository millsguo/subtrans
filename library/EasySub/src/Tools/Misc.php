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
}