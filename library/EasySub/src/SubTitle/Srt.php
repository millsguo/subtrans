<?php

namespace EasySub\SubTitle;

use EasySub\Tools\Log;
use Exception;

/**
 * 字幕处理
 */
class Srt
{
    /**
     * 读取文件内容
     *
     * @param string $filePath
     * @return array
     * @throws Exception
     */
    public function readToArray(string $filePath): array
    {
        if (!is_file($filePath)) {
            throw new Exception('文件不存在');
        }

        if (!is_readable($filePath)) {
            throw new Exception('文件读取失败');
        }

        $filePathInfo = pathinfo($filePath);

        if (!isset($filePathInfo['extension'])) {
            throw new Exception('文件没有扩展名');
        }

        if ($filePathInfo['extension'] != 'srt') {
            throw new Exception('仅支持打开srt字幕文件');
        }
        if (filesize($filePath) <= 2) {
            unlink($filePath);
            throw new Exception('字幕文件为空，删除字幕');
        }
        return @file($filePath);
    }

    /**
     * 写入文件
     *
     * @param string $filePath
     * @param $content
     * @return void
     * @throws Exception
     */
    public function writeSrt(string $filePath, $content)
    {
        if (is_array($content) && count($content) <= 2) {
            Log::info("数组内容为空，保存失败");
            return ;
        } elseif (is_string($content) && empty(trim($content))) {
            Log::info("内容为空，保存失败");
            return ;
        }
        Log::info('开始保存字幕文件：' . $filePath);
        $saveResult = @file_put_contents($filePath, $content);
        if ($saveResult === false) {
            Log::info('保存失败');
        } else {
            Log::info('保存成功');
        }
    }

    /**
     * 导出字幕
     *
     * @param string $videoFilePath
     * @param string $exportLanguage
     * @return bool|string
     * @throws Exception
     */
    public function exportInsideSubTitle(string $videoFilePath, string $exportLanguage)
    {
        $subTitleInfoArray = $this->getVideoInsideSubTitleInfo($videoFilePath);

        //检查是否有中文字幕
        $haveChineseSub = false;
        foreach ($subTitleInfoArray as $subTitle) {
            if (!isset($subTitle['tags']['language'])) {
                continue;
            }
            $subLanguage = $subTitle['tags']['language'];
            Log::info('发现' . $subLanguage . '内置字幕');
            if ($subLanguage == 'chi') {
                $haveChineseSub = true;
            }
        }

        //有中文字幕时，停止处理
        if ($haveChineseSub) {
            Log::info('发现内置中文字幕，不需要导出英文字幕');
            return true;
        }

        //导出字幕结果
        $subTitleFilePath = false;

        //字幕数量
        $subTitleCount = count($subTitleInfoArray);
        switch ($subTitleCount) {
            case 0:
                //没有字幕
                return false;
            case 1:
                //只有默认字幕，导出默认字幕
                if (!isset($subTitleInfoArray[0]['tags']['language'])) {
                    $subTitleLanguage = 'eng';
                } else {
                    $subTitleLanguage = $subTitleInfoArray[0]['tags']['language'];
                }
                Log::info('发现一个内置字幕，字幕编码为：' . $subTitleInfoArray[0]['codec_name']);
                if ($subTitleInfoArray[0]['codec_name'] == 'dvb_subtitle') {
                    Log::info('不支持此格式字幕导出，跳过');
                    return false;
                }
                $subTitleFilePath = $this->exportInsideSubTitleToSrt($videoFilePath, 0, $subTitleLanguage);
                break;
            default:
                Log::info('发现 ' . $subTitleCount . ' 个内置字幕');
                //多个字幕，只导出英文字幕
                $subIndex = 0;
//                $engDefaultIndex = -1;

                //最长字幕流索引
                $maxLineSubIndex = 0;
                //最长字幕流长度
                $maxLineSubCount = 0;

                foreach ($subTitleInfoArray as $subTitleInfo) {
                    if (!isset($subTitleInfo['tags']['language'])) {
                        $subIndex++;
                        continue;
                    }
                    //只处理 subrip 字幕, 跳过dvb_subtitle等字幕
                    if ($subTitleInfo['codec_name'] != 'subrip') {
                        $subIndex++;
                        Log::info('发现 ' . $subTitleInfo['codec_name'] . ' 编码字幕，跳过');
                        continue;
                    }
//                    if ($subTitleInfo['tags']['language'] == 'eng' && $engDefaultIndex == -1) {
//                        $engDefaultIndex = $subIndex;
//                    }
                    if ($subTitleInfo['tags']['language'] == $exportLanguage) {
                        $numberOfBytes = $subTitleInfo['tags']['NUMBER_OF_BYTES'] ?? 0;
                        Log::debug('索引序号：' . $subIndex . ' 字幕长度：' . $numberOfBytes / 1024 . "KB");
                        if ($numberOfBytes > $maxLineSubCount) {
                            $maxLineSubCount = $numberOfBytes;
                            $maxLineSubIndex = $subIndex;
                        }
                        $subIndex++;
                    }
                }
                Log::debug('导出最长字幕流：' . $maxLineSubIndex);
                $subTitleFilePath = $this->exportInsideSubTitleToSrt($videoFilePath, $maxLineSubIndex, $exportLanguage);
                break;
        }
        if (!$subTitleFilePath) {
            //导出字幕失败
            return false;
        }
        Log::info('导出字幕成功');
        return $subTitleFilePath;
    }

    /**
     * 获取视频内嵌字幕列表
     *
     * @param string $videoFilePath
     * @return array
     * @throws Exception
     */
    protected function getVideoInsideSubTitleInfo(string $videoFilePath): array
    {
        if (!is_readable($videoFilePath)) {
            throw new Exception('视频文件不存在');
        }

        exec('ffprobe -loglevel quiet -select_streams s -show_entries stream=index:stream_tags=language:stream_tags=title:stream=codec_name:stream_tags=NUMBER_OF_BYTES -print_format json "' . $videoFilePath . '"', $videoInfoArray, $runReturn);

        if ($runReturn === 1) {
            throw new Exception('获取字幕列表命令不存在或执行失败');
        }
        $videoInfoJson = implode("\r\n", $videoInfoArray);

        $jsonArray = json_decode($videoInfoJson, 320);

        if (!isset($jsonArray['streams'])) {
            throw new Exception('视频文件中没有内嵌字幕');
        }
        Log::debug('字幕详细信息');
        Log::debug(print_r($jsonArray, true));
        return $jsonArray['streams'];
    }

    /**
     * 导出指定字幕
     *
     * @param string $videoFilePath 视频文件路径
     * @param int $subTitleIndex 导入字幕序号，从0开始计算
     * @param string $subLanguageCode 导出字幕的语言代码
     * @return bool|string
     */
    protected function exportInsideSubTitleToSrt(string $videoFilePath, int $subTitleIndex, string $subLanguageCode)
    {
        switch (strtoupper($subLanguageCode)) {
            case 'ZH':
            case 'CHI':
            case 'CN':
            case 'ZH-CN':
            case 'CHINESE':
            case 'CHINA':
                $subLanguageCode = 'zh';
                break;
            case 'ENG':
            case 'ENGLISH':
            case 'EN':
                $subLanguageCode = 'eng';
                break;
            default:
                Log::info($subLanguageCode . '暂时不支持');
                $subLanguageCode = 'zh';
                break;
        }
        //生成导出的字幕文件名
        $fileInfo = pathinfo($videoFilePath);

        $subtitleFilename = $fileInfo['dirname'] . '/' . $fileInfo['filename'] . '.' . $subLanguageCode . '.srt';

        //导出字幕命令
        $extractSubtitleCommand = 'ffmpeg -loglevel quiet -i "'. $videoFilePath .'" -map 0:s:' . $subTitleIndex . ' "'.$subtitleFilename.'" -y';
        exec($extractSubtitleCommand, $output, $return);

        if ($return !== 0) {
            if (file_exists($subtitleFilename)) {
                $fileSize = filesize($subtitleFilename);
                if ($fileSize <= 1) {
                    unlink($subtitleFilename);
                }
            }
            return false;
        }
        return $subtitleFilename;
    }
}