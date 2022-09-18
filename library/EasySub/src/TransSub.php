<?php

namespace EasySub;

use EasySub\SubTitle\Srt;
use EasySub\Tools\Config;
use EasySub\Tools\Log;
use EasySub\Translated\TransApi;
use EasyTranslation\Translation;
use Exception;
use Zend_Validate_Digits;

class TransSub
{
    protected static Translation $translator;

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
        if (self::$translator instanceof Translation) {
            return true;
        } else {
            return false;
        }
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
            Log::info('翻译接口没有初始化');
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
        Log::info('使用批量翻译');
        try {
            $subFileContentArray = self::$srtObj->readToArray($sourceSubFile);
            if (count($subFileContentArray) < 2) {
                Log::info($sourceSubFile . '字幕文件内容为空');
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
                if (!$isNewSubItem && strpos($line, '-->') !== false) {
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
            $transApi = new TransApi();
            $transApi->updateApiCountByAccessKey(self::$translator->getConfig('access_key'), $translatedCount);
            self::$srtObj->writeSrt($targetSubFile, $writeSrtArray);
            return true;
        } catch (Exception $e) {
            Log::debug($e->getMessage());
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
    protected static function convertTranslated($sourceLanguage, $targetLanguage, $sourceJsonText, &$outFileArray)
    {
        $transJsonText = self::$translator->batchTranslate($sourceLanguage, $targetLanguage, $sourceJsonText);
        if ($transJsonText !== false) {
            if (is_array($transJsonText)) {
                $transArray = $transJsonText;
            } else {
                $transArray = json_decode($transJsonText, 320);
            }
            if (is_countable($transArray) && count($transArray) > 0) {
                foreach ($transArray as $transItem) {
                    if (isset($transItem['code']) && $transItem['code'] == '200') {
                        if (isset($outFileArray[$transItem['index']])) {
                            $outFileArray[$transItem['index']]['translatedLine'] = $transItem['translated'];
                        }
                    }
                }
            } else {
                Log::debug('批量翻译返回错误:' . print_r($transArray, true));
            }
        }
    }

    /**
     * 转换成json
     *
     * @param array $subItemData
     * @return false|string
     */
    protected static function convertSubData(array $subItemData)
    {
        $jsonArray = [];
        foreach ($subItemData as $lineNumber => $itemData) {
            $jsonArray[strval($lineNumber)] = $itemData['sourceLine'];
        }
        if (count($jsonArray) > 0) {
            return json_encode($jsonArray, 320);
        } else {
            return false;
        }
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
                if (!empty($line) && !$numberLineValidator->isValid($line) && strpos($line, '-->') === false) {
                    //不是空行，不是数字行，不是时间行
                    $line = str_replace(['.', ','], ' ', $line);    //替换掉部分符号
                    $transLine = self::$translator->translate($sourceLanguage, $targetLanguage, $line);
                    if ($transLine !== false) {
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