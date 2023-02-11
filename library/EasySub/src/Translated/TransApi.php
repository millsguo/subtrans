<?php

namespace EasySub\Translated;

use EasySub\Tools\Log;
use Exception;
use Zend_Db_Table;
use Zend_Db_Table_Row_Abstract;

class TransApi
{
    protected Zend_Db_Table $apiTable;

    public function __construct()
    {
        $this->apiTable = new Zend_Db_Table('translation_api');
    }

    /**
     * 通过环境变更初始化接口
     *
     * @return array
     * @throws Exception
     */
    public function initApiByEnv(): array
    {
        if (isset($_ENV['ACCESS_KEY'])) {
            Log::info('使用环境变量');
            $accessKey = trim($_ENV['ACCESS_KEY']);
            $apiRow = $this->getApiByAccessKey($accessKey);
            if (!$apiRow) {
                //不存在此接口
                $accessSecret = $_ENV['ACCESS_SECRET'] ?? '';
                if (isset($_ENV['USE_PRO']) && strtolower($_ENV['USE_PRO']) === 'true') {
                    $usePro = 1;
                } else {
                    $usePro = 0;
                }
                $regionId = $_ENV['REGION_ID'] ?? 'cn-hangzhou';
                try {
                    $apiId = $this->addApi(
                        'ALIYUN API',
                        'aliyun',
                        $accessKey,
                        $accessSecret,
                        $usePro,
                        $regionId,
                        1000000,
                        60,
                        false
                    );
                } catch (Exception $e) {
                }
            }
        }

        for ($i = 1; $i <= 5; $i++) {
            if (isset($_ENV['ACCESS_KEY_' . $i])) {
                $accessKey = trim($_ENV['ACCESS_KEY_' . $i]);
                $apiRow = $this->getApiByAccessKey($accessKey);
                if (!$apiRow) {
                    //不存在此接口
                    $accessSecret = $_ENV['ACCESS_SECRET_' . $i] ?? '';
                    if (isset($_ENV['USE_PRO']) && strtolower($_ENV['USE_PRO']) === 'true') {
                        $usePro = 1;
                    } else {
                        $usePro = 0;
                    }
                    $regionId = $_ENV['REGION_ID_' . $i] ?? 'cn-hangzhou';
                    try {
                        $apiId = $this->addApi(
                            'ALIYUN API',
                            'aliyun',
                            $accessKey,
                            $accessSecret,
                            $usePro,
                            $regionId,
                            1000000,
                            60,
                            false
                        );
                    } catch (Exception $e) {
                    }
                }
            }
        }
        $usedApiRow = $this->getSmartApi();
        if (!$usedApiRow) {
            throw new \RuntimeException('没有满足条件的可用接口');
        }
        return [
            'translate_api' => $usedApiRow->api_type,
            'access_key'    => $usedApiRow->api_access_key,
            'access_secret' => $usedApiRow->api_access_secret,
            'region_id'     => $usedApiRow->api_region_id,
            'use_pro'       => $usedApiRow->api_use_pro,
            'id'            => $usedApiRow->id
        ];
    }

    /**
     * 增加API接口
     *
     * @param string $apiName
     * @param string $apiType
     * @param string $accessKey
     * @param string $accessSecret
     * @param int $usePro
     * @param string $regionId
     * @param int $freeCountLimit
     * @param int $feeCount
     * @param bool $enablePay
     * @return bool|int
     * @throws Exception
     */
    public function addApi(
        string $apiName,
        string $apiType,
        string $accessKey,
        string $accessSecret,
        int $usePro,
        string $regionId,
        int $freeCountLimit,
        int $feeCount,
        bool $enablePay = false): bool|int
    {
        $apiRow = $this->getApiByAccessKey($accessKey);
        if ($apiRow) {
            throw new \RuntimeException('AccessKey已存在');
        }
        if ($enablePay) {
            $enablePay = 1;
        } else {
            $enablePay = 0;
        }
        $data = [
            'name'  => $apiName,
            'api_type'  => $apiType,
            'api_access_key'    => $accessKey,
            'api_access_secret' => $accessSecret,
            'api_region_id'     => $regionId,
            'api_use_pro'       => $usePro,
            'free_count_limit'  => $freeCountLimit,
            'fee_count' => $feeCount,
            'enable_pay'    => $enablePay
        ];
        $apiId = $this->apiTable->insert($data);
        if ($apiId) {
            return (int)$apiId;
        }

        return false;
    }

    /**
     * 通过接口ID获取接口信息
     *
     * @param int $id
     * @return bool|Zend_Db_Table_Row_Abstract
     */
    public function getApi(int $id): Zend_Db_Table_Row_Abstract|bool
    {
        $where = [
            'id = ?'=> $id
        ];
        $row = $this->apiTable->fetchRow($where);
        if (isset($row->id)) {
            return $row;
        }

        return false;
    }

    /**
     * 通过AK获取接口信息
     *
     * @param string $accessKey
     * @return false|Zend_Db_Table_Row_Abstract
     */
    public function getApiByAccessKey(string $accessKey)
    {
        $where = [
            'api_access_key = ?'    => $accessKey
        ];
        $row = $this->apiTable->fetchRow($where);
        if (isset($row->id)) {
            return $row;
        }

        return false;
    }

    /**
     * 获取可用接口信息
     *
     * @return false|Zend_Db_Table_Row_Abstract|null
     */
    public function getSmartApi()
    {
        $where = [
            'id > ?'    => 0
        ];
        $apiRows = $this->apiTable->fetchAll($where, 'id ASC');
        if (is_countable($apiRows)) {
            foreach ($apiRows as $apiRow) {
                if ($apiRow->current_month_limit == 0) {
                    return $apiRow;
                }
                if ($apiRow->enable_pay == 1) {
                    return $apiRow;
                }
            }
        }
        return false;
    }

    /**
     * 更新免费翻译字数统计
     *
     * @param int $apiId
     * @param int $translatedCount
     * @return bool
     * @throws Exception
     */
    public function updateApiCount(int $apiId, int $translatedCount)
    {
        $apiRow = $this->getApi($apiId);
        if (!$apiRow) {
            return false;
        }
        if ($apiRow->current_month_str != date('Ym')) {
            //不是本月数据，清零
            $data = [
                'current_month_translated_count'    => 0,
                'current_month_free_count'          => 0,
                'current_month_limit'               => 0,
                'current_month_str'                 => date('Ym')
            ];
            $updateResult = $this->apiTable->update($data, ['id = ?' => $apiId]);
            if (!$updateResult) {
                throw new Exception('更新月数据失败');
            }
            $apiRow = $this->getApi($apiId);
        }
        $data = [
            'current_month_translated_count'    => intval($apiRow->current_month_translated_count) + $translatedCount,
            'translated_count'                  => intval($apiRow->translated_count) + $translatedCount,
        ];
        if ($apiRow->current_month_free_count > $apiRow->free_count_limit) {
            //付费翻译
            $data['translated_fee_count'] = intval($apiRow->translated_fee_count) + $translatedCount;
        } else {
            //免费翻译
            $monthFreeCount = intval($apiRow->current_month_free_count) + $translatedCount;
            $data['translated_free_count'] = intval($apiRow->translated_free_count) + $translatedCount;
            $data['current_month_free_count'] = $monthFreeCount;
            if ($monthFreeCount > $apiRow->free_count_limit) {
                //超过免费额度
                $data['current_month_limit'] = 1;
            }
        }
        $updateResult = $this->apiTable->update($data, ['id = ?' => $apiId]);
        if (!$updateResult) {
            throw new Exception('更新免费翻译字数失败');
        }
        return true;
    }

    /**
     * 通过AK更新接口信息
     *
     * @param string $accessKey
     * @param int $translatedCount
     * @return bool
     * @throws Exception
     */
    public function updateApiCountByAccessKey(string $accessKey, int $translatedCount)
    {
        $apiRow = $this->getApiByAccessKey($accessKey);
        if (!$apiRow) {
            throw new Exception('接口不存在');
        }
        return $this->updateApiCount($apiRow->id, $translatedCount);
    }
}