<?php

namespace EasySub;

use EasySub\SubTitle\Srt;
use EasySub\Tools\Log;
use EasySub\Tools\Misc;
use Exception;
use ZipArchive;

class CheckSub
{
    /**
     * @param ZipArchive $fullSubZip
     * @return array
     */
    public static function getSubFileNameByZip(ZipArchive $fullSubZip): array
    {
        $fullSubFileArray = [];
        for ($i = 0; $i < $fullSubZip->count(); $i++) {
            $zipFileName = $fullSubZip->getNameIndex($i);
            $zipFileType = mb_substr($zipFileName, -3);
            switch (strtolower($zipFileType)) {
                case 'srt':
                case 'ass':
                case 'ssa':
                    $fullSubFileArray[] = $zipFileName;
                    Log::info('找到压缩包中的字幕文件：' . $zipFileName);
                    break;
            }
        }
        return $fullSubFileArray;
    }

    /**
     *扫描目录
     *
     * @param string $dirPath
     * @param bool $isSeason
     * @return void
     */
    public static function scanDir(string $dirPath, bool $isSeason = false): void
    {
        if (empty($dirPath)) {
            Log::info('空目录');
            return;
        }
        if (!is_dir($dirPath)) {
            Log::info('不是正确的目录' . $dirPath);
            return;
        }
        $dirPath = '/' . trim($dirPath, '/') . '/';
        try {
            $dirArray = Misc::scanDir($dirPath);
            if (!$dirArray) {
                return;
            }
            foreach ($dirArray as $fileName) {
                $fullPath = $dirPath . $fileName;
                if (is_dir($fullPath)) {
                    //目录
                    if (str_starts_with($fileName, 'Season')) {
                        Log::info('Season目录');
                        $checkSeason = self::checkFullSeasonSubZip($fullPath);
                        if ($checkSeason) {
                            continue;
                        }
                    }
                    self::scanDir($fullPath);
                } elseif (is_readable($fullPath)) {
                    //可读文件
                    $fileInfo = pathinfo($fullPath);
                    if (!isset($fileInfo['extension'])) {
                        //Log::debug($fullPath . '获取扩展名失败');
                        continue;
                    }
                    $fileExt = $fileInfo['extension'];
                    switch ($fileExt) {
                        case 'mp4':
                        case 'mkv':
                            Log::info('找到视频文件:' . $fileName);
                            //视频文件
                            $subFileName = self::checkSubTitleFile($fullPath, 'zh');
                            if ($subFileName) {
                                Log::info('已有中文字幕文件，跳过');
                                continue 2;
                            }
                            //中文字幕文件名
                            $chineseSubFileName = $fileInfo['dirname'] . '/' . $fileInfo['filename'] . '.zh.srt';
                            //检查是否有已下载的字幕文件
                            $checkChineseSubZip = self::checkTvDownloadedSubZip($fullPath, $isSeason);
                            if ($checkChineseSubZip) {
                                Log::info('找到中文字幕包，并处理完成');
                                continue 2;
                            }
                            //没有中文字幕
                            $engSubFile = self::checkSubTitleFile($fullPath, 'eng');
                            if (!$engSubFile) {
                                //没有英文字幕
                                Log::info('没有英文外挂字幕文件');
                                $srtObj = new Srt();
                                $exportSubResult = $srtObj->exportInsideSubTitle($fullPath, 'eng');
                                if ($exportSubResult === true) {
                                    //有内置中文字幕
                                    Log::info('内置中文字幕处理完成');
                                    continue 2;
                                }
                                if ($exportSubResult === false) {
                                    //导出失败
                                    Log::info('导出内置英文字幕失败');
                                    continue 2;
                                }
                                $engSubFile = $exportSubResult;
                                Log::info('英文字幕导出成功：' . $engSubFile);
                            } else {
                                //有英文字幕文件
                                Log::info('有英文字幕文件:' . $engSubFile);
                            }
                            $enableTrans = $_ENV['ENABLE_TRANS'] ?? 'false';

                            if (strtolower($enableTrans) === 'true') {
                                Log::info('开始翻译英文字幕文件:' . $fullPath . '/' . $engSubFile);
                                TransSub::transSubFile($fullPath . '/' . $engSubFile, $chineseSubFileName, 'eng', 'zh');
                            } else {
                                Log::info('翻译功能已关闭');
                            }
                            break;
                    }
                } else {
                    Log::info($fullPath . '权限错误');
                }
            }
        } catch (Exception $e) {
            Log::debug($e->getMessage());
            Log::debug($e->getTraceAsString());
        }
    }

    /**
     * 检查对应视频文件相对应的集数是否已下载相关字幕ZIP包，ZIP包以S01E01命名，找到并解压成功返回TRUE
     *
     * @param string $videoFile
     * @param bool $isSeason
     * @return bool
     */
    public static function checkTvDownloadedSubZip(string $videoFile, bool $isSeason = false): bool
    {
        $videoFileInfo = pathinfo($videoFile);

        $currentDirPath = $videoFileInfo['dirname'];

        if ($isSeason) {
            $seasonEpisode = self::getSeasonEpisode($videoFileInfo['filename']);
        } else {
            $seasonEpisode = 's00e00';
        }

        $currentFileArray = Misc::scanDir($videoFileInfo['dirname']);
        if (!$currentFileArray) {
            Log::info($currentFileArray . '目录为空或不可读');
            return false;
        }

        foreach ($currentFileArray as $currentFile) {
            $currentFileInfo = pathinfo($currentFile);
            $currentFileName = strtolower($currentFileInfo['filename']);

            if (strtolower($currentFileName) !== strtolower($seasonEpisode)) {
                //如果不是S01E01.zip这种文件，则直接跳过
                continue;
            }
            $fileExtension = strtolower($currentFileInfo['extension']);
            switch ($fileExtension) {
                case 'zip':
                    $zipFile = new ZipArchive();
                    if ($zipFile->open($currentDirPath . '/' . $currentFile) === true) {
                        Log::info('找到字幕压缩包' . $currentFile);
                        $outFileArray = self::getSubFileNameByZip($zipFile);
                        if (count($outFileArray) > 0) {
                            //有字幕文件，先解压
                            $zipFile->extractTo($videoFileInfo['dirname'], $outFileArray);
                            //再将解压的字幕文件改名
                            foreach ($outFileArray as $subFile) {
                                $subFileInfo = pathinfo($videoFileInfo['dirname'] . '/' . $subFile);

                                self::renameSubFilename($videoFileInfo, $subFileInfo);
                            }
                            $zipFile->close();
                            return true;
                        }

                        Log::info($currentFile . '压缩文件中没有字幕文件');
                        $zipFile->close();
                        return false;
                    }

                    Log::info($currentFile . '压缩文件打开失败');
                    continue 2;
                case 'rar':
                    return false;
                case 'srt':
                case 'ssa':
                case 'ass':
                    $subFileInfo = pathinfo($videoFileInfo['dirname'] . '/' . $currentFile);
                    self::renameSubFilename($videoFileInfo, $subFileInfo);
                    break;
            }
        }
        return false;
    }

    /**
     * 将字幕文件匹配视频文件
     *
     * @param array $videoFileInfo
     * @param array $subFileInfo
     * @return void
     */
    protected static function renameSubFilename(array $videoFileInfo, array $subFileInfo): void
    {
        $seasonStr = self::getSeasonEpisode($subFileInfo['basename']);
        $seasonPos = mb_stripos($subFileInfo['filename'], $seasonStr);
        $subRangeStr = trim(mb_substr($subFileInfo['filename'], ($seasonPos + mb_strlen($seasonStr))), '.');
        $newSubFile = $videoFileInfo['dirname'] . '/' . $videoFileInfo['filename'] . '.zh.' . $subRangeStr . '.' . $subFileInfo['extension'];
        @rename($videoFileInfo['dirname'] . '/' . $subFileInfo['basename'], $newSubFile);
        Log::info($subFileInfo['basename'] . '文件已解压并改名为：[' . $newSubFile . ']');
    }

    /**
     * 检查整季字幕包，命名格式s01.zip
     * @param string $seasonDir
     * @return bool
     */
    protected static function checkFullSeasonSubZip(string $seasonDir): bool
    {
        $dirPathInfo = pathinfo($seasonDir);
        $seasonNumber = str_pad(trim(substr($dirPathInfo['basename'], 6)),2,"0",STR_PAD_LEFT);
        $fullSeasonSubFile = $seasonDir . '/s' . $seasonNumber . '.zip';
        if (!file_exists($fullSeasonSubFile)) {
            $fullSeasonSubFile = $seasonDir . '/S' . $seasonNumber . '.zip';
            if (!file_exists($fullSeasonSubFile)) {
                Log::info('没有第' . $seasonNumber . '季整季字幕包');
                return false;
            }
        }
        $fullSubZip = new ZipArchive();
        if ($fullSubZip->open($fullSeasonSubFile) === true) {
            Log::info('找到第' . $seasonNumber . '季字幕压缩包');
            $fullSubFileArray = self::getSubFileNameByZip($fullSubZip);
            if (count($fullSubFileArray) > 0) {
                //有字幕文件，先解压
                $fullSubZip->extractTo($seasonDir, $fullSubFileArray);
                //再将解压的字幕文件改名
                foreach ($fullSubFileArray as $subFile) {
                    $subSeasonName = self::getSeasonEpisode($subFile);

                    $fileArray = Misc::scanDir($seasonDir);
                    foreach ($fileArray as $file) {
                        $filePathInfo = pathinfo($seasonDir . '/' . $file);
                        if (!isset($filePathInfo['extension'])) {
                            //如果是目录，没有扩展名，则直接跳过
                            continue;
                        }
                        if (strtolower($filePathInfo['extension']) !== 'mp4' && strtolower($filePathInfo['extension']) !== 'mkv') {
                            continue;
                        }
                        $fileSeasonName = self::getSeasonEpisode($file);
                        if ((!empty($subSeasonName) && !empty($fileSeasonName)) && strtolower($subSeasonName) === strtolower($fileSeasonName)) {
                            //找到字幕相对应的视频集数
                            $subFileInfo = pathinfo($seasonDir . '/' . $subFile);

                            self::renameSubFilename($filePathInfo, $subFileInfo);
                            Log::info($subSeasonName . '字幕处理完成');
                        }
                    }
                }
                $fullSubZip->close();
                return true;
            }

            Log::info($fullSeasonSubFile . '压缩文件中没有字幕文件');
            $fullSubZip->close();
            return false;
        }

        Log::info($fullSeasonSubFile . '压缩文件打开失败');
        return false;
    }

    /**
     * 获取文件名中的集数
     * @param string $videoFile
     * @return false|string
     */
    protected static function getSeasonEpisode(string $videoFile): bool|string
    {
        $matchRegex = '/s\d+e\d+/i';

        if (preg_match($matchRegex, $videoFile, $matchResult)) {
            return $matchResult[0];
        }

        return false;
    }

    /**
     * 检查是否存在相关语言字幕
     *
     * @param string $videoFile
     * @param string $subLanguage
     * @return false|string
     */
    public static function checkSubTitleFile(string $videoFile, string $subLanguage): bool|string
    {
        if (!is_readable($videoFile)) {
            Log::info($videoFile . '视频文件不存在');
            return false;
        }
        $videoFileInfo = pathinfo($videoFile);

        $videoDirPath = $videoFileInfo['dirname'];
        $videoFileName = $videoFileInfo['filename'];
        $videoFileNameLen = mb_strlen($videoFileName);

        $currentDirFileArray = Misc::scanDir($videoDirPath);

        if (!$currentDirFileArray) {
            Log::info($currentDirFileArray . '目录扫描出错');
            return false;
        }

        foreach ($currentDirFileArray as $currentFileName) {
            $currentFullFilePath = $videoDirPath . '/' . $currentFileName;
            $checkFileInfo = pathinfo($currentFullFilePath);
            if (stripos($checkFileInfo['filename'], $videoFileName) !== false) {
                //当前文件名中包含视频文件名
                $extensionName = strtolower($checkFileInfo['extension']);
                $subRangeStr = mb_substr($checkFileInfo['filename'], $videoFileNameLen);
                switch ($extensionName) {
                    case 'srt':
                    case 'ass':
                    case 'ssa':
                        Log::info('找到' . $currentFileName . '字幕文件');
                        if (stripos($subRangeStr, $subLanguage) !== false) {
                            $checkResult = self::checkSubTitleFileSize($currentFullFilePath);
                            if ($checkResult) {
                                return $currentFullFilePath;
                            }
                        }
                        break;
                }
            }
        }
        return false;
    }

    /**
     * 检查文件是否为空，空文件直接删除
     *
     * @param string $subFileName
     * @return bool
     */
    public static function checkSubTitleFileSize(string $subFileName): bool
    {
        $fileSize = filesize($subFileName);
        if ($fileSize <= 2) {
            if (is_writable($subFileName)) {
                unlink($subFileName);
            } else {
                Log::info('没有写权限，文件删除失败:' . $subFileName);
            }

            return false;
        }

        return true;
    }
}