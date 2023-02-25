<?php

namespace EasyTranslation;

use EasySub\Tools\Log;
use EasySub\Translated\TransApi;
use EasyTranslation\TransInterface\AliYun;
use Exception;

class Translation
{
    private AliYun $translateObj;
    private array $config = [];

    /**
     * 参数配置
     *
     * @param array $config
     * @return void
     * @throws Exception
     */
    public function setConfig(array $config): void
    {
        if (!isset($config['translate_api'])) {
            throw new \RuntimeException('请先指定翻译接口');
        }
        $this->config = $config;

        $translateApi = strtolower($config['translate_api']);
        switch ($translateApi) {
            case 'aliyun':
                if (!isset($config['access_key'])) {
                    throw new \RuntimeException('请指定阿里云AccessKey');
                }
                if (!isset($config['access_secret'])) {
                    throw new \RuntimeException('请指定阿里云AccessSecret');
                }
                $regionId = $config['region_id'] ?? '';
                if (isset($config['use_pro']) && (int)$config['use_pro'] === 1) {
                    $usePro = true;
                } else {
                    $usePro = false;
                }
                $aliYunConfig = [
                    'accessKey' => $config['access_key'],
                    'accessSecret'  => $config['access_secret'],
                    'regionId'  => $regionId,
                    'usePro'    => $usePro
                ];
                $this->translateObj = new AliYun();
                $this->translateObj->init($aliYunConfig);
                break;
            case 'tencent':
                //腾讯云翻译接口
                throw new \RuntimeException('暂不支持腾讯云翻译接口');
            case 'huawei':
                //华为云翻译接口
                throw new \RuntimeException('暂不支持华为云翻译接口');
            default:
                throw new \RuntimeException($translateApi . '翻译接口不支持');
        }
    }

    /**
     * 获取配置参数
     *
     * @param string $key
     * @return false|mixed
     */
    public function getConfig(string $key)
    {
        return $this->config[$key] ?? false;
    }

    /**
     * 翻译
     *
     * @param string $sourceLanguage
     * @param string $targetLanguage
     * @param string $text
     * @return string|bool
     */
    public function translate(string $sourceLanguage, string $targetLanguage, string $text): string|bool
    {
        try {
            $apiConfig = TransApi::initApi();
            $this->setConfig($apiConfig);
            if (!is_object($this->translateObj)) {
                throw new \RuntimeException('未配置接口，请先配置接口');
            }
            return $this->translateObj->translate($sourceLanguage, $targetLanguage, $text);
        } catch (Exception $e) {
            Log::err($e->getMessage());
            return false;
        }
    }

    /**
     * 批量翻译
     *
     * @param string $sourceLanguage
     * @param string $targetLanguage
     * @param string $multiLineJson
     * @return array|false
     * @throws Exception
     */
    public function batchTranslate(string $sourceLanguage, string $targetLanguage, string $multiLineJson): bool|array
    {
        $apiConfig = TransApi::initApi();
        Log::info('使用' . $apiConfig['id'] . '号接口');
        $this->setConfig($apiConfig);

        if (!is_object($this->translateObj)) {
            throw new \RuntimeException('未配置接口，请先配置接口');
        }
        return $this->translateObj->batchTranslate($sourceLanguage, $targetLanguage, $multiLineJson);
    }
}