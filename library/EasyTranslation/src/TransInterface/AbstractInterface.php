<?php

namespace EasyTranslation\TransInterface;

/**
 * 翻译接口
 */
interface AbstractInterface
{
    /**
     * 配置接口参数
     *
     * @param array $config
     * @return void
     */
    public function init(array $config): void;

    /**
     * 翻译
     *
     * @param string $sourceLanguage
     * @param string $targetLanguage
     * @param string $text
     * @return string
     */
    public function translate(string $sourceLanguage, string $targetLanguage, string $text): string;

    /**
     * 批量翻译
     *
     * @param string $sourceLanguage
     * @param string $targetLanguage
     * @param string $muleLineJson
     * @return string|array
     */
    public function batchTranslate(string $sourceLanguage, string $targetLanguage, string $muleLineJson);
}