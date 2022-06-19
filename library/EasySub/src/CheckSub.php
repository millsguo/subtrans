<?php

namespace EasySub;

use EasySub\SubTitle\Srt;
use EasySub\Tools\Log;
use EasySub\Tools\Misc;
use Exception;

class CheckSub
{
    /**
     *扫描目录
     *
     * @param string $dirPath
     * @param bool $isSeason
     * @return void
     */
    public static function scanDir(string $dirPath, bool $isSeason = false)
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

            foreach ($dirArray as $fileName) {
                $fullPath = $dirPath . $fileName;
                if (is_dir($fullPath)) {
                    //目录
                    self::scanDir($fullPath);
                } elseif (is_readable($fullPath)) {
                    //可读文件
                    $fileInfo = pathinfo($fullPath);
                    if (!isset($fileInfo['extension'])) {
                        Log::debug($fullPath . '获取扩展名失败');
                        continue;
                    }
                    $fileExt = $fileInfo['extension'];
                    switch ($fileExt) {
                        case 'mp4':
                        case 'mkv':
                            Log::info('找到视频文件:' . $fullPath);
                            //视频文件
                            $subFileName = self::checkSubTitleFile($fullPath, 'zh');
                            if ($subFileName) {
                                Log::info('已有中文字幕文件，跳过');
                                continue 2;
                            }
                            //中文字幕文件名
                            $chineseSubFileName = $fileInfo['dirname'] . '/' . $fileInfo['filename'] . '.zh.srt';
                            //检查是否有已下载的字幕文件
                            $checkChineseSubZip = self::checkTvDownloadedSubZip($fullPath);
                            if ($checkChineseSubZip) {
                                Log::info('找到中文字幕包，并处理完成');
                                continue 2;
                            }
                            //没有中文字幕
                            $engSubFile = self::checkSubTitleFile($fullPath, 'eng');
                            if (!$engSubFile) {
                                //没有英文字幕
                                Log::info('没有英文字幕文件');
                                $srtObj = new Srt();
                                $exportSubResult = $srtObj->exportInsideSubTitle($fullPath, 'eng');
                                if ($exportSubResult === true) {
                                    //有内置中文字幕
                                    Log::info('字幕导出成功');
                                    continue 2;
                                }
                                if ($exportSubResult === false) {
                                    //导出失败
                                    Log::info('字幕文件从视频中导出失败');
                                    continue 2;
                                }
                                $engSubFile = $exportSubResult;
                                Log::info('英文字幕导出成功：' . $engSubFile);
                            } else {
                                //有英文字幕文件
                                Log::info('有英文字幕文件:' . $engSubFile);
                            }
                            $enableTrans = $_ENV['ENABLE_TRANS'] ?? 'false';

                            if (strtolower($enableTrans) == 'true') {
                                Log::info('开始翻译英文字幕文件');
                                TransSub::transSubFile($engSubFile, $chineseSubFileName, 'eng', 'zh');
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
     * @return bool
     */
    public static function checkTvDownloadedSubZip(string $videoFile): bool
    {
        $videoFileInfo = pathinfo($videoFile);

        $seasonEpisode = self::getSeasonEpisode($videoFileInfo['filename']);

        $currentFileArray = Misc::scanDir($videoFileInfo['dirname']);
        if (!$currentFileArray) {
            Log::info($currentFileArray . '目录为空或不可读');
            return false;
        }

        foreach ($currentFileArray as $currentFile) {
            $currentFileInfo = pathinfo($currentFile);
            $currentFileName = strtolower($currentFileInfo['filename']);

            if ($currentFileName != strtolower($seasonEpisode)) {
                //如果不是S01E01.zip这种文件，则直接跳过
                continue;
            }
            $fileExtension = strtolower($currentFileInfo['extension']);
            switch ($fileExtension) {
                case 'zip':
                    $zipFile = new \ZipArchive();
                    if ($zipFile->open($currentFile) === true) {
                        $outFileArray = [];
                        for ($i = 0; $i < $zipFile->count(); $i++) {
                            $zipFileName = $zipFile->getNameIndex($i);
                            $zipFileType = mb_substr($zipFileName,-3);
                            switch (strtolower($zipFileType)) {
                                case 'srt':
                                case 'ass':
                                case 'ssa':
                                    $outFileArray[] = $zipFileName;
                                    Log::info('找到压缩包中的字幕文件：' . $zipFileName);
                                    break;
                            }
                        }
                        if (count($outFileArray) > 0) {
                            //有字幕文件，先解压
                            $zipFile->extractTo($videoFileInfo['dirname'], $outFileArray);
                            //再将解压的字幕文件改名
                            foreach ($outFileArray as $subFile) {
                                $subFileInfo = pathinfo($videoFileInfo['dirname'] . '/' . $subFile);
                                $newSubFile = $videoFileInfo['dirname'] . '/' . $videoFileInfo['filename'] . '.zh.' . $subFileInfo['extension'];
                                @rename($videoFileInfo['dirname'] . '/' . $subFile, $newSubFile);
                                Log::info($subFile . '文件已解压并改名为：[' . $newSubFile . ']');
                                unlink($videoFileInfo['dirname'] . '/' . $subFile);
                            }
                            $zipFile->close();
                            return true;
                        } else {
                            Log::info($currentFile . '压缩文件中没有字幕文件');
                            $zipFile->close();
                            return false;
                        }
                    } else {
                        Log::info($currentFile . '压缩文件打开失败');
                    }
                    continue 2;
                case 'rar':
                    return false;
            }
        }
        return false;
    }

    /**
     * 获取文件名中的集数
     * @param string $videoFile
     * @return false|mixed
     */
    protected static function getSeasonEpisode(string $videoFile)
    {
        $matchRegex = '/s\d+e\d+/i';

        if (preg_match($matchRegex, $videoFile, $matchResult)) {
            return $matchResult[0];
        } else {
            return false;
        }
    }

    /**
     * 检查是否存在相关语言字幕
     *
     * @param string $videoFile
     * @param string $subLanguage
     * @return false|string
     */
    public static function checkSubTitleFile(string $videoFile, string $subLanguage)
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
                Log::info('找到' . $currentFileName . '字幕文件');
                $extensionName = strtolower($checkFileInfo['extension']);
                $subRangeStr = mb_substr($checkFileInfo['filename'], $videoFileNameLen);
                switch ($extensionName) {
                    case 'srt':
                    case 'ass':
                    case 'ssa':
                        if (stripos($subRangeStr, $subLanguage) !== false) {
                            $checkResult = self::checkSubTitleFileSize($currentFullFilePath);
                            if ($checkResult) {
                                return $currentFileName;
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
            if (is_writeable($subFileName)) {
                unlink($subFileName);
            } else {
                Log::info('没有写权限，文件删除失败:' . $subFileName);
            }

            return false;
        } else {

            return true;
        }
    }

    /**
     * 获取视频文件对应的字幕文件名及路径
     * @param string $videoFilePath
     * @param string $subLanguage
     * @param string $subType
     * @return false|string
     */
    public static function getSubTitleFileName(string $videoFilePath, string $subLanguage, string $subType = 'srt')
    {
        if (!is_readable($videoFilePath)) {
            Log::info($videoFilePath . '视频文件不存在');
            return false;
        }
        $fileInfo = pathinfo($videoFilePath);

        return $fileInfo['dirname'] . '/' . $fileInfo['filename'] . '.' . $subLanguage . '.' . $subType;
    }
}