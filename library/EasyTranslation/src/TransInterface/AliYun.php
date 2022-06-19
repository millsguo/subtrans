<?php

namespace EasyTranslation\TransInterface;

use AlibabaCloud\Client\AlibabaCloud;
use AlibabaCloud\Client\Exception\ClientException;
use AlibabaCloud\Client\Exception\ServerException;
use AlibabaCloud\Client\Result\Result;
use EasySub\Tools\Log;
use Exception;

class AliYun implements AbstractInterface
{
    /**
     * @var bool 客户端是否初始化
     */
    protected bool $clientIsInit = false;

    /**
     * @var bool 是否使用专业版翻译接口
     */
    protected bool $useProApi = false;

    /**
     * 专业版翻译接口支持的语言代码
     * @var array|string[]
     */
    protected array $proTargetLanguageArray = [
        'zh'    => '中文简体',
        'zh-tw' => '中文繁体',
        'en'    => '英语',
        'ko'    => '韩语',
        'ja'    => '日语'
    ];

    /**
     * 翻译接口重试次数
     *
     * @var int
     */
    protected int $tryTimesLimit = 10;

    /**
     * @param array $config
     * @return void
     * @throws Exception
     */
    public function init(array $config): void
    {
        $accessKey = $config['accessKey'] ?? '';
        $accessSecret = $config['accessSecret'] ?? '';
        $regionId = $config['regionId'] ?? 'cn-hangzhou';
        try {
            AlibabaCloud::accessKeyClient($accessKey, $accessSecret)->regionId($regionId)->asDefaultClient();
            $this->clientIsInit = true;
            if (isset($config['usePro']) && $config['usePro']) {
                $this->useProApi = true;
            } else {
                $this->useProApi = false;
            }
        } catch (ClientException $e) {
            $this->clientIsInit = false;
            throw new Exception($e->getMessage());
        }
    }

    /**
     * 翻译，不能超过 5000 字符
     *
     * @param string $sourceLanguage
     * @param string $targetLanguage
     * @param string $text
     * @return string
     * @throws ClientException
     * @throws Exception
     */
    public function translate(string $sourceLanguage, string $targetLanguage, string $text): string
    {
        $this->checkClientInit();

        $text = trim($text);

        if (empty($text)) {
            return '';
        }
        $sourceLanguage = $this->preParseSourceLanguage($sourceLanguage);

        if (empty($sourceLanguage)) {
            $sourceLanguage = $this->detectLanguage($text);
        }

        $targetLanguage = $this->preParseTargetLanguage($targetLanguage);
        if (empty($targetLanguage)) {
            $targetLanguage = 'zh';
        }


        if (!isset($this->proTargetLanguageArray[$targetLanguage])) {
            //专业版翻译接口不支持语种
            $this->useProApi = false;
        }
        $result = $this->requestTranslate($sourceLanguage, $targetLanguage, $text);

        if ($result !== false) {
            return $result;
        }
        return false;
    }

    /**
     * 统一处理源语言代码
     *
     * @param string $sourceLanguage
     * @return string
     */
    private function preParseSourceLanguage(string $sourceLanguage): string
    {
        switch (strtolower($sourceLanguage)) {
            case 'eng':
            case 'english':
            case 'en':
                $sourceLanguage = 'en';
                break;
            case '':
                $sourceLanguage = '';
                break;
        }
        return $sourceLanguage;
    }

    /**
     * 统一处理目标语言代码
     *
     * @param string $targetLanguage
     * @return string
     */
    private function preParseTargetLanguage(string $targetLanguage): string
    {
        switch (strtolower($targetLanguage)) {
            case 'zh':
            case 'zh-cn':
            case 'cn':
            case 'chi':
            case 'chinese':
            case 'china':
                $targetLanguage = 'zh';
                break;
        }
        return $targetLanguage;
    }

    /**
     * 调用翻译接口
     *
     * @param $sourceLanguage
     * @param $targetLanguage
     * @param $text
     * @return string|false
     */
    protected function requestTranslate($sourceLanguage, $targetLanguage, $text)
    {
        $tryTimes = 1;
        do {
            $needTry = true;
            try {
                if ($this->useProApi) {
                    //专业版翻译
                    $result = AlibabaCloud::alimt()
                        ->v20181012()
                        ->translate()
                        ->method('POST')
                        ->withScene('social')
                        ->withSourceLanguage($sourceLanguage)
                        ->withSourceText($text)
                        ->withFormatType('text')
                        ->withTargetLanguage($targetLanguage)
                        ->request();
                } else {
                    //通用版翻译
                    $result = AlibabaCloud::alimt()
                        ->v20181012()
                        ->translateGeneral()
                        ->method('POST')
                        ->withSourceLanguage($sourceLanguage)
                        ->withSourceText($text)
                        ->withFormatType('text')
                        ->withTargetLanguage($targetLanguage)
                        ->request();
                }
                $returnArray = $result->toArray();

                if (isset($returnArray['Code']) && $returnArray['Code'] == 200 && isset($returnArray['Data']['Translated'])) {
                    return $returnArray['Data']['Translated'];
                } elseif (isset($returnArray['Message'])) {
                    Log::debug('CODE:[' . $returnArray['Code'] . '] MSG:[' . $returnArray['Message'] . ']');
                } else {
                    Log::debug('接口返回数据格式错误[' . print_r($returnArray, true) . ']');
                }
            } catch (ClientException|ServerException $e) {
                Log::err($e->getMessage());
                Log::err($e->getTraceAsString());
            }
            //尝试3次，还是失败后，放弃
            $tryTimes++;
            if ($tryTimes > $this->tryTimesLimit) {
                $needTry = false;
            }
        } while ($needTry);
        Log::debug('尝试' . $this->tryTimesLimit . '次都失败，放弃');
        return false;
    }

    /**
     * 检查语种
     *
     * @param string $text
     * @return string
     * @throws ClientException
     * @throws ServerException
     * @throws Exception
     */
    private function detectLanguage(string $text): string
    {
        if (!$this->clientIsInit) {
            throw new Exception('翻译客户端未初始化');
        }
        $result = AlibabaCloud::alimt()
            ->v20181012()
            ->getDetectLanguage()
            ->withSourceText($text)
            ->request();

        $resultArray = $result->toArray();

        if (isset($resultArray['GetDetectLanguageResponse']['DetectedLanguage'])) {
            return $resultArray['GetDetectLanguageResponse']['DetectedLanguage'];
        } else {
            throw new Exception('语种检测失败');
        }
    }

    /**
     * 批量翻译，不能超过 50 条，每条不能超过 1000 字符
     *
     * @param string $sourceLanguage
     * @param string $targetLanguage
     * @param string $muleLineJson
     * @return array|string|false
     * @throws Exception
     */
    public function batchTranslate(string $sourceLanguage, string $targetLanguage, string $muleLineJson)
    {
        $this->checkClientInit();

        if (empty($muleLineJson)) {
            return '';
        }
        $sourceLanguage = $this->preParseSourceLanguage($sourceLanguage);

        $targetLanguage = $this->preParseTargetLanguage($targetLanguage);
        if (empty($targetLanguage)) {
            $targetLanguage = 'zh';
        }

        if (!isset($this->proTargetLanguageArray[$targetLanguage])) {
            //专业版翻译接口不支持语种
            $this->useProApi = false;
        }
        $result = $this->requestBatchTranslate($sourceLanguage, $targetLanguage, $muleLineJson);

        if ($result !== false) {
            return $result;
        }
        return false;
    }

    /**
     * 检查客户是否初始化
     *
     * @return void
     * @throws Exception
     */
    private function checkClientInit()
    {
        if (!$this->clientIsInit) {
            throw new Exception('翻译客户端未初始化');
        }
    }

    /**
     * 调用翻译接口
     *
     * @param $sourceLanguage
     * @param $targetLanguage
     * @param $text
     * @return array|string|false
     */
    protected function requestBatchTranslate($sourceLanguage, $targetLanguage, $text)
    {
        $tryTimes = 1;
        do {
            $needTry = true;
            try {
                if ($this->useProApi) {
                    //专业版翻译
                    $result = AlibabaCloud::alimt()
                        ->v20181012()
                        ->getBatchTranslate()
                        ->method('POST')
                        ->withScene('social')
                        ->withApiType('translate_ecommerce')
                        ->withSourceLanguage($sourceLanguage)
                        ->withSourceText($text)
                        ->withFormatType('text')
                        ->withTargetLanguage($targetLanguage)
                        ->request();
                } else {
                    //通用版翻译
                    $result = AlibabaCloud::alimt()
                        ->v20181012()
                        ->getBatchTranslate()
                        ->method('POST')
                        ->withScene('general')
                        ->withApiType('translate_standard')
                        ->withSourceLanguage($sourceLanguage)
                        ->withSourceText($text)
                        ->withFormatType('text')
                        ->withTargetLanguage($targetLanguage)
                        ->request();
                }

                $returnArray = $result->toArray();

                if (isset($returnArray['Code']) && $returnArray['Code'] == 200 && isset($returnArray['TranslatedList'])) {
                    return $returnArray['TranslatedList'];
                } elseif (isset($returnArray['Message'])) {
                    Log::debug('CODE:[' . $returnArray['Code'] . '] MSG:[' . $returnArray['Message'] . ']');
                } else {
                    Log::debug('接口返回数据格式错误[' . print_r($returnArray, true) . ']');
                }
            } catch (ClientException|ServerException $e) {
                Log::err($e->getMessage());
                Log::err($e->getTraceAsString());
            }
            //尝试3次，还是失败后，放弃
            $tryTimes++;
            if ($tryTimes > $this->tryTimesLimit) {
                $needTry = false;
            }
        } while ($needTry);
        Log::debug('尝试' . $this->tryTimesLimit . '次都失败，放弃');
        return false;
    }
}