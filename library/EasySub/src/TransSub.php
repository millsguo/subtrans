<?php

namespace EasySub;

use EasySub\SubTitle\Srt;
use EasySub\Tools\Log;
use EasySub\Translated\TransApi;
use EasyTranslation\Translation;
use Exception;
use JsonException;
use Zend_Validate_Digits;

class TransSub
{
    protected static Translation $translator;

    /**
     * @var bool 翻译接口出错
     */
    protected static bool $translatorError = false;

    protected static Srt $srtObj;

    /**
     * 初始化翻译接口
     *
     * @return void
     */
    public static function initTranslation(): void
    {
        try {
            self::$translator = new Translation();
            self::$srtObj = new Srt();
        } catch (Exception $e) {
            Log::debug($e->getMessage());
        }
    }

    /**
     * 检查翻译接口是否初始化
     *
     * @return bool
     */
    public static function checkTranslation(): bool
    {
        return self::$translator instanceof Translation;
    }

    /**
     * 翻译字幕文件
     *
     * @param string $sourceSubFile
     * @param string $targetSubFile
     * @param string $sourceLanguage
     * @param string $targetLanguage
     * @return bool
     */
    public static function transSubFile(string $sourceSubFile, string $targetSubFile, string $sourceLanguage, string $targetLanguage): bool
    {
        if (!self::checkTranslation()) {
            Log::translateLog('翻译接口没有初始化');
            return false;
        }
        return self::translateByMultiLine($sourceSubFile, $targetSubFile, $sourceLanguage, $targetLanguage);
    }

    /**
     * 按行翻译字幕文件
     *
     * @param $sourceSubFile
     * @param $targetSubFile
     * @param $sourceLanguage
     * @param $targetLanguage
     * @return bool
     */
    protected static function translateByMultiLine($sourceSubFile, $targetSubFile, $sourceLanguage, $targetLanguage): bool
    {
        Log::translateLog('使用批量翻译');
        try {
            $subFileContentArray = self::$srtObj->readToArray($sourceSubFile);
            if (count($subFileContentArray) < 2) {
                Log::translateLog($sourceSubFile . '字幕文件内容为空');
                return false;
            }
            $outFileArray = [];

            //上一行是否为空行，空行后的数字行为序号
            $numberLineValidator = new Zend_Validate_Digits();

            //多行数组
            $multiSubData = [];
            $isNewSubItem = true;
            $subItemData = [];

            //行号
            $lineNumber = 1;
            $multiLineCount = 1;

            //翻译字数
            $translatedCount = 0;

            foreach ($subFileContentArray as $line) {
                $line = trim($line);
                if (empty($line)) {
                    //空行，则下面是新段
                    $isNewSubItem = true;
                    if (count($subItemData) > 0) {
                        $multiSubData[$lineNumber] = $subItemData;
                        $outFileArray[$lineNumber] = $subItemData;
                        $multiLineCount++;
                    }
                    $lineNumber++;
                    $subItemData = [];

                    if ($multiLineCount >= 49) {
                        //即将达到最大行数
                        $sourceJsonText = self::convertSubData($multiSubData);
                        //将翻译结果写入输出数组
                        self::convertTranslated($sourceLanguage, $targetLanguage, $sourceJsonText, $outFileArray);

                        $multiSubData = [];
                        $multiLineCount = 1;
                    }
                    continue;
                }
                if ($isNewSubItem && $numberLineValidator->isValid($line)) {
                    //序号行
                    $subItemData['lineNumber'] = $line;
                    $isNewSubItem = false;
                    continue;
                }
                if (!$isNewSubItem && str_contains($line, '-->')) {
                    //时间行
                    $subItemData['lineDate'] = $line;
                    $isNewSubItem = false;
                    continue;
                }
                $line = str_replace("...", "... ", $line);    //替换掉部分符号
                //字幕行，可能有多行
                if (isset($subItemData['sourceLine'])) {
                    if (mb_strlen($subItemData['sourceLine']) > 50) {
                        $lineSplitChar = "\r\n";
                    } else {
                        $lineSplitChar = " ";
                    }
                    $subItemData['sourceLine'] .= $lineSplitChar . $line;
                } else {
                    $subItemData['sourceLine'] = $line;
                }
                $translatedCount += mb_strlen($subItemData['sourceLine']);
            }
            //最后不满 50 行的数据
            $sourceJsonText = self::convertSubData($multiSubData);

            //将翻译结果写入输出数组
            self::convertTranslated($sourceLanguage, $targetLanguage, $sourceJsonText, $outFileArray);

            $writeSrtArray = [];
            foreach ($outFileArray as $srtSerial => $itemData) {
                $writeSrtArray[] = $srtSerial . "\r\n";
                if (!isset($itemData['lineDate'])) {
                    continue;
                }
                $writeSrtArray[] = $itemData['lineDate'] . "\r\n";
                if (isset($itemData['translatedLine'])) {
                    $writeSrtArray[] = $itemData['translatedLine'] . "\r\n";
                }
                $writeSrtArray[] = $itemData['sourceLine'] . "\r\n";
                $writeSrtArray[] = "\r\n";
            }
            Log::translateLog('翻译完成，准备写入文件');
            TransApi::updateApiCountByAccessKey(self::$translator->getConfig('access_key'), $translatedCount);
            self::$srtObj->writeSrt($targetSubFile, $writeSrtArray);
            Log::translateLog('字幕翻译成功，保存为：' . $targetSubFile);
            Log::log('字幕翻译成功，保存为：' . $targetSubFile);
            return true;
        } catch (Exception $e) {
            Log::translateLog($e->getMessage());
            Log::translateLog($e->getTraceAsString());
            return false;
        }
    }

    /**
     * 将翻译后的写入输出文件数组
     *
     * @param $sourceLanguage
     * @param $targetLanguage
     * @param $sourceJsonText
     * @param $outFileArray
     * @return void
     * @throws Exception
     */
    protected static function convertTranslated($sourceLanguage, $targetLanguage, $sourceJsonText, &$outFileArray): void
    {
        $transJsonText = self::$translator->batchTranslate($sourceLanguage, $targetLanguage, $sourceJsonText);
        if ($transJsonText !== false) {
            if (is_array($transJsonText)) {
                $transArray = $transJsonText;
            } else {
                $transArray = json_decode($transJsonText, 320, 512, JSON_THROW_ON_ERROR);
            }
            if (is_countable($transArray) && count($transArray) > 0) {
                foreach ($transArray as $transItem) {
                    if (isset($transItem['code'], $outFileArray[$transItem['index']]) && $transItem['code'] === '200') {
                        $outFileArray[$transItem['index']]['translatedLine'] = $transItem['translated'];
                    }
                }
            } else {
                Log::debug('批量翻译返回错误:' . print_r($transArray, true));
            }
        } else {
            Log::log('翻译出错');
            self::$translatorError = true;
        }
    }

    /**
     * 转换成json
     *
     * @param array $subItemData
     * @return false|string
     */
    protected static function convertSubData(array $subItemData): bool|string
    {
        $jsonArray = [];
        foreach ($subItemData as $lineNumber => $itemData) {
            $jsonArray[(string)$lineNumber] = $itemData['sourceLine'];
        }
        if (count($jsonArray) > 0) {
            try {
                return json_encode($jsonArray, JSON_THROW_ON_ERROR | 320);
            } catch (JsonException $e) {
                Log::err($e->getMessage());
                return false;
            }
        }

        return false;
    }

    /**
     * 按行翻译字幕文件
     *
     * @param $sourceSubFile
     * @param $targetSubFile
     * @param $sourceLanguage
     * @param $targetLanguage
     * @return bool
     */
    protected static function translateByLine($sourceSubFile, $targetSubFile, $sourceLanguage, $targetLanguage): bool
    {
        try {
            $subFileContentArray = self::$srtObj->readToArray($sourceSubFile);
            if (count($subFileContentArray) < 2) {
                Log::info($sourceSubFile . '字幕文件内容为空');
                return false;
            }
            $outFileArray = [];

            //上一行是否为空行，空行后的数字行为序号
            $numberLineValidator = new Zend_Validate_Digits();

            foreach ($subFileContentArray as $line) {
                $line = trim($line);
                if (!empty($line) && !$numberLineValidator->isValid($line) && !str_contains($line, '-->')) {
                    //不是空行，不是数字行，不是时间行
                    $line = str_replace(['.', ','], ' ', $line);    //替换掉部分符号
                    $transLine = self::$translator->translate($sourceLanguage, $targetLanguage, $line);
                    if ($transLine) {
                        $outFileArray[] = $transLine . "\r\n";
                    }
                }
                $outFileArray[] = $line . "\r\n";
            }
            self::$srtObj->writeSrt($targetSubFile, $outFileArray);
            return true;
        } catch (Exception $e) {
            Log::debug($e->getMessage());
            return false;
        }
    }
}