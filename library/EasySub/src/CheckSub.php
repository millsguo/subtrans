<?php

namespace EasySub;

use EasySub\SubTitle\Srt;
use EasySub\Tools\Config;
use EasySub\Tools\Log;
use EasySub\Tools\Misc;
use EasySub\Translated\TransApi;
use EasySub\Video\Movie;
use EasySub\Video\Store;
use EasySub\Video\Tv;
use Exception;
use ZipArchive;

class CheckSub
{
    /**
     * @var string 翻译接口提供商
     */
    public static string $apiName = 'aliyun';

    /**
     * @var bool 机器翻译开关
     */
    public static bool $enableTrans = false;

    /**
     * 初始化
     * @return void
     */
    public static function initCli(): void
    {
        try {
            $configArray = Config::getConfig(BASE_APP_PATH . '/config/config.ini');

            $translationArray = $configArray->translation;
            if (isset($translationArray->api_name)) {
                self::$apiName = $translationArray->api_name;
                Log::info('找到翻译API配置:' . self::$apiName);
            }
            if (isset($translationArray->enable_trans) && ($translationArray->enable_trans === 1 || $translationArray->enable_trans === "1")) {
                self::$enableTrans = true;
                Log::info('找到翻译开关:打开');
            }
            if ($translationArray) {
                Log::info('使用配置文件');
                if (isset($translationArray->aliyun1)) {
                    $aliyunArray = $translationArray->aliyun1->toArray();
                    if ($aliyunArray['use_pro'] === 1) {
                        $usePro = true;
                    } else {
                        $usePro = false;
                    }
                    TransApi::addApiConfig($aliyunArray['access_key'],$aliyunArray['access_secret'],$usePro);
                }
                if (isset($translationArray->aliyun2)) {
                    $aliyunArray = $translationArray->aliyun2->toArray();
                    if ($aliyunArray['use_pro'] === 1) {
                        $usePro = true;
                    } else {
                        $usePro = false;
                    }
                    TransApi::addApiConfig($aliyunArray['access_key'],$aliyunArray['access_secret'],$usePro);
                }
            }

            TransSub::initTranslation();
        } catch (Exception $e) {
            echo $e->getMessage();
            echo $e->getTraceAsString();
        }
    }

    /**
     * 初始化库
     * @return void
     */
    public static function initLibrary(): void
    {
        $configArray = Config::getConfig(BASE_APP_PATH . '/config/config.ini');
        for ($i = 1;$i <= 3; $i++) {
            $moviesName = 'movies-' . $i;
            $tvName = 'tv-' . $i;
            if (isset($configArray->volume->{$moviesName})) {
                Log::info('增加电影库：' . $configArray->volume->{$moviesName});
                Store::addMovieLibrary($configArray->volume->{$moviesName});
            }
            if (isset($configArray->volume->{$tvName})) {
                Log::info('增加剧集库：' . $configArray->volume->{$tvName});
                Store::addTvLibrary($configArray->volume->{$tvName});
            }
        }
    }

    /**
     * 扫描所有库
     * @return void
     */
    public static function scanAll(): void
    {
        //扫描目录
        Log::info('扫描电影库');
        $movieLibrary = Store::getMovieLibrary();
        foreach ($movieLibrary as $dirPath) {
            Log::info('扫描电影目录：' . $dirPath);
            self::scanDir($dirPath);
        }
        Log::info('扫描剧集库');
        $tvLibrary = Store::getTvLibrary();
        foreach ($tvLibrary as $dirPath) {
            Log::info('扫描剧集目录：' . $dirPath);
            self::scanTvDir($dirPath,true);
        }
    }

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
     *扫描电影目录
     *
     * @param string $dirPath
     * @return void
     */
    public static function scanDir(string $dirPath): void
    {
        if (empty($dirPath)) {
            Log::info('空目录');
            return;
        }
        if (!is_dir($dirPath)) {
            Log::info('不是正确的目录' . $dirPath);
            return;
        }
        //电影
        $videoObj = new Movie();
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
                                $addResult = $videoObj->addMovie($fullPath,true);
                                if ($addResult) {
                                    Log::info('更新电影库成功');
                                } else {
                                    Log::info('更新电影库失败');
                                }
                                continue 2;
                            }
                            //中文字幕文件名
                            $chineseSubFileName = $fileInfo['dirname'] . '/' . $fileInfo['filename'] . '.default.zh.srt';
                            //检查是否有已下载的字幕文件
                            $checkChineseSubZip = self::checkTvDownloadedSubZip($fullPath, false);
                            if ($checkChineseSubZip) {
                                Log::info('找到中文字幕包，并处理完成');
                                $addResult = $videoObj->addMovie($fullPath,true);
                                if ($addResult) {
                                    Log::info('更新电影库成功');
                                } else {
                                    Log::info('更新电影库失败');
                                }
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
                                    $addResult = $videoObj->addMovie($fullPath,true);
                                    if ($addResult) {
                                        Log::info('更新电影库成功');
                                    } else {
                                        Log::info('更新电影库失败');
                                    }
                                    continue 2;
                                }
                                if ($exportSubResult === false) {
                                    //导出失败
                                    Log::info('导出内置英文字幕失败');
                                    $addResult = $videoObj->addMovie($fullPath,false);
                                    if ($addResult) {
                                        Log::info('更新电影库成功');
                                    } else {
                                        Log::info('更新电影库失败');
                                    }
                                    continue 2;
                                }
                                $engSubFile = $exportSubResult;
                                Log::info('英文字幕导出成功：' . $engSubFile);
                            } else {
                                //有英文字幕文件
                                Log::info('有英文字幕文件:' . $engSubFile);
                            }
                            $enableTrans = $_ENV['ENABLE_TRANS'] ?? 'false';

                            if ($enableTrans === true || strtolower($enableTrans) === 'true') {
                                Log::info('开始翻译英文字幕文件:' . $engSubFile);
                                TransSub::transSubFile($engSubFile, $chineseSubFileName, 'eng', 'zh');
                                $addResult = $videoObj->addMovie($fullPath,true);
                            } else {
                                Log::info('翻译功能已关闭');
                                $addResult = $videoObj->addMovie($fullPath,false);
                            }

                            if ($addResult) {
                                Log::info('更新电影数据库成功');
                            } else {
                                Log::info('更新电影数据库失败');
                            }
                            break;
                        case 'srt':
                        case 'ass':
                            //字幕文件
                            Log::info('找到字幕文件：' . $fileName);
                            $fileEpisode = self::getSeasonEpisode($fileName);
                            if (!$fileEpisode) {
                                Log::info('字幕文件不是剧集字幕，跳过');
                                continue 2;
                            }
                            $videoFileName = self::getSeasonEpisodeVideoFileByDir($fileInfo['dirname'],$dirArray,$fileEpisode);
                            Log::info('匹配剧集：' . $videoFileName);
                            $videoFileInfo = pathinfo($fileInfo['dirname'] . '/' . $videoFileName);
                            if (!str_contains($fileName,$videoFileInfo['filename'])) {
                                self::renameSubFilename($videoFileInfo,$fileInfo);
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
     * 扫描剧集目录
     * @param string $dirPath
     * @return void
     */
    public static function scanTvDir(string $dirPath): void
    {
        if (empty($dirPath)) {
            Log::info('空目录');
            return;
        }
        if (!is_dir($dirPath)) {
            Log::info('不是正确的目录' . $dirPath);
            return;
        }
        //剧集
        $videoObj = new Tv();

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
                    self::scanTvDir($fullPath);
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
                                Log::info('更新剧集');
                                $videoObj->autoParseEpisode($dirPath,$fileName,true);
                                continue 2;
                            }
                            //中文字幕文件名
                            $chineseSubFileName = $fileInfo['dirname'] . '/' . $fileInfo['filename'] . '.default.zh.srt';
                            //检查是否有已下载的字幕文件
                            $checkChineseSubZip = self::checkTvDownloadedSubZip($fullPath, true);
                            if ($checkChineseSubZip) {
                                Log::info('找到中文字幕包，并处理完成');
                                Log::info('更新剧集');
                                $videoObj->autoParseEpisode($dirPath,$fileName,true);
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
                                    Log::info('更新剧集');
                                    $videoObj->autoParseEpisode($dirPath,$fileName,true);
                                    continue 2;
                                }
                                if ($exportSubResult === false) {
                                    //导出失败
                                    Log::info('导出内置英文字幕失败');
                                    Log::info('更新剧集');
                                    $videoObj->autoParseEpisode($dirPath,$fileName,false);
                                    continue 2;
                                }
                                $engSubFile = $exportSubResult;
                                Log::info('英文字幕导出成功：' . $engSubFile);
                            } else {
                                //有英文字幕文件
                                Log::info('有英文字幕文件:' . $engSubFile);
                            }
                            $enableTrans = $_ENV['ENABLE_TRANS'] ?? 'false';

                            if ($enableTrans === true || strtolower($enableTrans) === 'true') {
                                Log::info('开始翻译英文字幕文件:' . $engSubFile);
                                TransSub::transSubFile($engSubFile, $chineseSubFileName, 'eng', 'zh');
                                Log::info('更新剧集');
                                $addResult = $videoObj->autoParseEpisode($dirPath,$fileName,true);
                            } else {
                                Log::info('翻译功能已关闭');
                                $addResult = $videoObj->autoParseEpisode($dirPath,$fileName,false);
                            }

                            if ($addResult) {
                                Log::info('更新数据库成功');
                            } else {
                                Log::info('更新数据库失败');
                            }
                            break;
                        case 'srt':
                        case 'ass':
                            //字幕文件
                            Log::info('找到字幕文件：' . $fileName);
                            $fileEpisode = self::getSeasonEpisode($fileName);
                            if (!$fileEpisode) {
                                Log::info('字幕文件不是剧集字幕，跳过');
                                continue 2;
                            }
                            $videoFileName = self::getSeasonEpisodeVideoFileByDir($fileInfo['dirname'],$dirArray,$fileEpisode);
                            Log::info('匹配剧集：' . $videoFileName);
                            $videoFileInfo = pathinfo($fileInfo['dirname'] . '/' . $videoFileName);
                            if (!str_contains($fileName,$videoFileInfo['filename'])) {
                                self::renameSubFilename($videoFileInfo,$fileInfo);
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
     * 找到当前目录下的指定剧集的视频文件名
     * @param string $dirPath
     * @param array $dirArray
     * @param string $episode
     * @return bool|string
     */
    protected static function getSeasonEpisodeVideoFileByDir(string $dirPath, array $dirArray, string $episode): bool|string
    {
        foreach ($dirArray as $fileName) {
            $fileInfo = pathinfo($dirPath . $fileName);
            if (!isset($fileInfo['extension'])) {
                //Log::debug($fullPath . '获取扩展名失败');
                continue;
            }
            $fileExt = $fileInfo['extension'];
            switch ($fileExt) {
                case 'mkv':
                case 'mp4':
                    $fileEpisode = self::getSeasonEpisode($fileName);
                    if (!$fileEpisode) {
                        continue 2;
                    }
                    if ($fileEpisode === $episode) {
                        return $fileName;
                    }
                    break;
            }

        }
        return false;
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
                            //有字幕文件，先解压至subs_s01e01目录
                            $zipFile->extractTo($videoFileInfo['dirname'] . '/subs_' . $seasonEpisode, $outFileArray);
                            //再将解压的字幕文件改名
                            foreach ($outFileArray as $subFile) {
                                $subFileInfo = pathinfo($videoFileInfo['dirname'] . '/subs_' . $seasonEpisode . '/' . $subFile);

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
        $subRangeStr = self::getLanguageTagFromSubFilename($subFileInfo['filename']);
        if (empty($subRangeStr)) {
            $subRangeStr = 'zh';
        }
        Log::info('语言标签：' . $subRangeStr);
        switch ($subRangeStr) {
            case 'zh':
            case 'chs':
            case 'eng':
                break;
            default:
                //$subRangeStr = 'zh.' . $subRangeStr;
                break;
        }
        $newSubFile = $videoFileInfo['filename'] . '.' . $subRangeStr . '.' . $subFileInfo['extension'];
        if (!Misc::checkFileExists($videoFileInfo['dirname'] . '/' . $videoFileInfo['basename'])) {
            Log::info('视频文件不存在');
        }
        if (!Misc::checkFileExists($subFileInfo['dirname'] . '/' . $subFileInfo['basename'])) {
            Log::info('字幕文件不存在');
        }
        $renameResult = @rename($videoFileInfo['dirname'] . '/' . $subFileInfo['basename'], $videoFileInfo['dirname'] . '/' . $newSubFile);
        if ($renameResult) {
            Log::info('更名成功');
            Log::info('文件改名为：[' . $videoFileInfo['basename'] . ']新文件名[' . $newSubFile . ']');
        } else {
            Log::info('更名失败');
        }
    }

    /**
     * 获取字幕文件中的语言标签
     * @param string $subFilename
     * @return string
     */
    public static function getLanguageTagFromSubFilename(string $subFilename): string
    {
        $dotCount = mb_substr_count($subFilename, '.');
        if ($dotCount <= 1) {
            return '';
        }
        $firstDotPos = mb_stripos($subFilename, '.');
        $endDotPos = mb_strrpos($subFilename,'.');
        $languageTag = mb_substr($subFilename,($firstDotPos + 1),($endDotPos - $firstDotPos - 1));
        $tagDotCount = mb_substr_count($languageTag,'.');
        if ($tagDotCount > 0) {
            $startTagPos = mb_strrpos($languageTag,'.') + 1;
            $languageTag = mb_substr($languageTag,$startTagPos);
        }
        return $languageTag;
    }

    /**
     * 检查整季字幕包，命名格式s01.zip
     * @param string $seasonDir
     * @return bool
     */
    protected static function checkFullSeasonSubZip(string $seasonDir): bool
    {
        $dirPathInfo = pathinfo($seasonDir);
        $seasonNumber = str_pad(trim(substr($dirPathInfo['basename'], 6)), 2, "0", STR_PAD_LEFT);
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
                        //如果是检查中文字幕，则考虑chi字串，如chinese
                        if ($subLanguage === 'zh' && stripos($subRangeStr, 'chi')) {
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