<?php

namespace EasySub\Translated;

use EasySub\Tools\Config;
use EasySub\Tools\Log;
use EasySub\Tools\Table;
use Exception;
use Zend_Db_Table_Row_Abstract;

class TransApi
{
    /**
     * @var array API配置
     */
    private static array $apiConfigArray = [];

    protected static Table $apiTable;

    public function __construct()
    {
        self::$apiTable = new Table('translation_api','id');
    }

    /**
     * 初始化数据库表
     * @return void
     */
    private static function initTable(): void
    {
        if (!isset(self::$apiTable)) {
            self::$apiTable = new Table('translation_api','id');
        }
    }

    /**
     * @param string $accessKey
     * @param string $accessSecret
     * @param bool $usePro
     * @return bool
     */
    public static function addApiConfig(string $accessKey,string $accessSecret,bool $usePro): bool
    {
        if (!isset(self::$apiConfigArray[$accessKey])) {
            self::$apiConfigArray[$accessKey] = [
                'access_key' => $accessKey,
                'access_secret' => $accessSecret,
                'use_pro'   => $usePro
            ];
            return true;
        }
        return false;
    }

    /**
     * 初始化翻译接口
     * @return array
     */
    public static function initApi(): array
    {
        self::initTable();
        if (count(self::$apiConfigArray) > 0) {
            foreach (self::$apiConfigArray as $key => $config) {
                $apiRow = self::getApiByAccessKey($key);
                if (!$apiRow) {
                    $regionId = $config['region_id'] ?? 'cn-hangzhou';
                    if ($config['use_pro'] === '1' || $config['use_pro'] === 1) {
                        $usePro = true;
                    } else {
                        $usePro = false;
                    }
                    if (isset($config['enable_pay']) && ($config['enable_pay'] === '1' || $config['enable_pay'] === 1)) {
                        $enablePay = true;
                    } else {
                        $enablePay = false;
                    }
                    self::addApi(
                        '阿里云翻译接口',
                        'aliyun',
                        $config['access_key'],
                        $config['access_secret'],
                        $usePro,
                        $regionId,
                        1000000,
                        60,
                        $enablePay
                    );
                }
            }
        } else {
            $config = Config::getConfig(BASE_APP_PATH . '/config/config.ini','translation');
            if (isset($config['aliyun1'])) {
                $config = $config['aliyun1'];
                if ($config['use_pro'] === 1 || $config['use_pro'] === '1') {
                    $usePro = true;
                } else {
                    $usePro = false;
                }
                if (isset($config['enable_pay']) && ($config['enable_pay'] === '1' || $config['enable_pay'] === 1)) {
                    $enablePay = true;
                } else {
                    $enablePay = false;
                }
                $regionId = $config['region_id'] ?? 'cn-hangzhou';
                self::addApiConfig($config['access_key'],$config['access_secret'],$usePro);
                self::addApi('阿里云翻译接口','aliyun',$config['access_key'],$config['access_secret'],$usePro,$regionId,1000000,60,$enablePay);
            }
            if (isset($config['aliyun2'])) {
                $config = $config['aliyun2'];
                if ($config['use_pro'] === 1 || $config['use_pro'] === '1') {
                    $usePro = true;
                } else {
                    $usePro = false;
                }
                if (isset($config['enable_pay']) && ($config['enable_pay'] === '1' || $config['enable_pay'] === 1)) {
                    $enablePay = true;
                } else {
                    $enablePay = false;
                }
                $regionId = $config['region_id'] ?? 'cn-hangzhou';
                self::addApiConfig($config['access_key'],$config['access_secret'],$usePro);
                self::addApi('阿里云翻译接口','aliyun',$config['access_key'],$config['access_secret'],$usePro,$regionId,1000000,60,$enablePay);
            }
        }

        $usedApiRow = self::getSmartApi();
        if (!$usedApiRow) {
            self::initApiByEnv();
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
     * 通过环境变更初始化接口
     *
     * @return array
     */
    public static function initApiByEnv(): array
    {
        self::initTable();
        if (isset($_ENV['ACCESS_KEY'])) {
            Log::info('使用环境变量');
            $accessKey = trim($_ENV['ACCESS_KEY']);
            $apiRow = self::getApiByAccessKey($accessKey);
            if (!$apiRow) {
                //不存在此接口
                Log::info('[' . $accessKey . ']接口数据不在数据库中');
                $accessSecret = $_ENV['ACCESS_SECRET'] ?? '';
                if (isset($_ENV['USE_PRO']) && strtolower($_ENV['USE_PRO']) === 'true') {
                    $usePro = true;
                } else {
                    $usePro = false;
                }
                if (isset($_ENV['ENABLE_PAY']) && strtolower($_ENV['ENABLE_PAY']) === 'true') {
                    $enablePay = true;
                } else {
                    $enablePay = false;
                }
                $regionId = $_ENV['REGION_ID'] ?? 'cn-hangzhou';
                try {
                    $apiId = self::addApi(
                        '阿里云翻译接口',
                        'aliyun',
                        $accessKey,
                        $accessSecret,
                        $usePro,
                        $regionId,
                        1000000,
                        60,
                        $enablePay
                    );
                } catch (Exception $e) {
                }
            }
        }

        for ($i = 1; $i <= 5; $i++) {
            $accessKeyName = 'ACCESS_KEY_' . $i;
            if (isset($_ENV[$accessKeyName])) {
                $accessKey = trim($_ENV[$accessKeyName]);
                $apiRow = self::getApiByAccessKey($accessKey);
                if (!$apiRow) {
                    //不存在此接口
                    Log::info('[' . $accessKey . ']接口数据不在数据库中');
                    $accessSecret = $_ENV['ACCESS_SECRET_' . $i] ?? '';
                    if (isset($_ENV['USE_PRO_' . $i]) && strtolower($_ENV['USE_PRO_' . $i]) === 'true') {
                        $usePro = true;
                    } else {
                        $usePro = false;
                    }
                    if (isset($_ENV['ENABLE_PAY_' . $i]) && strtolower($_ENV['ENABLE_PAY_' . $i]) === 'true') {
                        $enablePay = true;
                    } else {
                        $enablePay = false;
                    }
                    $regionId = $_ENV['REGION_ID_' . $i] ?? 'cn-hangzhou';
                    try {
                        $apiId = self::addApi(
                            '阿里云翻译接口',
                            'aliyun',
                            $accessKey,
                            $accessSecret,
                            $usePro,
                            $regionId,
                            1000000,
                            60,
                            $enablePay
                        );
                        if ($apiId) {
                            Log::info('[' . $accessKey . ']保存至数据库成功');
                        } else {
                            Log::info('[' . $accessKey . ']保存至数据库失败');
                        }
                    } catch (Exception $e) {
                        Log::info($e->getMessage());
                    }
                }
            }
        }
        $usedApiRow = self::getSmartApi();
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
     * @param bool $usePro
     * @param string $regionId
     * @param int $freeCountLimit
     * @param int $feeCount
     * @param bool $enablePay
     * @return bool|int
     */
    public static function addApi(
        string $apiName,
        string $apiType,
        string $accessKey,
        string $accessSecret,
        bool $usePro,
        string $regionId,
        int $freeCountLimit,
        int $feeCount,
        bool $enablePay = false): bool|int
    {
        self::initTable();
        $apiRow = self::getApiByAccessKey($accessKey);
        if ($apiRow) {
            throw new \RuntimeException('AccessKey已存在');
        }
        if ($enablePay) {
            $enablePayValue = 1;
        } else {
            $enablePayValue = 0;
        }
        if ($usePro) {
            $useProValue = 1;
        } else {
            $useProValue = 0;
        }
        $data = [
            'name'  => $apiName,
            'api_type'  => $apiType,
            'api_access_key'    => $accessKey,
            'api_access_secret' => $accessSecret,
            'api_region_id'     => $regionId,
            'api_use_pro'       => $useProValue,
            'free_count_limit'  => $freeCountLimit,
            'fee_count' => $feeCount,
            'enable_pay'    => $enablePayValue
        ];
        $apiId = self::$apiTable->insert($data);
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
    public static function getApi(int $id): Zend_Db_Table_Row_Abstract|bool
    {
        self::initTable();
        $where = [
            'id = ?'=> $id
        ];
        $row = self::$apiTable->fetchRow($where);
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
    public static function getApiByAccessKey(string $accessKey): bool|Zend_Db_Table_Row_Abstract
    {
        self::initTable();
        $where = [
            'api_access_key = ?'    => $accessKey
        ];
        $row = self::$apiTable->fetchRow($where);
        if (isset($row->id)) {
            return $row;
        }

        return false;
    }

    /**
     * 获取可用接口信息
     *
     * @return false|Zend_Db_Table_Row_Abstract
     */
    public static function getSmartApi(): bool|Zend_Db_Table_Row_Abstract
    {
        self::initTable();
        $where = [
            'id > ?'    => 0
        ];
        $apiRows = self::$apiTable->fetchAll($where, 'id ASC');
        if (is_countable($apiRows)) {
            foreach ($apiRows as $apiRow) {
                if ((int)$apiRow->current_month_limit === 0) {
                    return $apiRow;
                }
                if ((int)$apiRow->enable_pay === 1) {
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
    public static function updateApiCount(int $apiId, int $translatedCount): bool
    {
        /**
         *
        id                             INTEGER 翻译接口ID
        name                           TEXT, 翻译接口名称
        api_type                       TEXT, 翻译接口代码
        translated_count               INTEGER default 0, 总翻译字数
        translated_free_count          INTEGER default 0, 翻译免费字数
        translated_fee_count           INTEGER default 0, 翻译收费字数
        free_count_limit               INTEGER default 0, 免费翻译额度
        fee_count                      INTEGER default 0, 付费翻译金额
        current_month_translated_count INTEGER default 0, 当月付费翻译字数
        current_month_free_count       INTEGER default 0, 当月免费翻译字数
        current_month_limit            INTEGER default 0, 当月是否超过免费额度
        current_month_str              INTEGER, 月份
        enable_pay                     INTEGER default 0, 是否允许付费翻译
        api_access_key                 TEXT,
        api_access_secret              TEXT,
        api_region_id                  TEXT, 接口区域
        api_use_pro                    INTEGER default 0 是否使用专业翻译接口
         */
        self::initTable();
        $apiRow = self::getApi($apiId);
        if (!$apiRow) {
            return false;
        }
        if ($apiRow->current_month_str !== date('Ym')) {
            //不是本月数据，清零
            $data = [
                'current_month_translated_count'    => 0,
                'current_month_free_count'          => 0,
                'current_month_limit'               => 0,
                'current_month_str'                 => date('Ym')
            ];
            $updateResult = self::$apiTable->update($data, ['id = ?' => $apiId]);
            if (!$updateResult) {
                throw new \RuntimeException('更新月数据失败');
            }
            $apiRow = self::getApi($apiId);
        }
        $data = [
            'translated_count'                  => (int)$apiRow->translated_count + $translatedCount,
        ];
        if ($apiRow->current_month_free_count > $apiRow->free_count_limit) {
            //付费翻译
            $data['translated_fee_count'] = (int)$apiRow->translated_fee_count + $translatedCount;
            $data['current_month_translated_count'] = (int)$apiRow->current_month_translated_count + $translatedCount;
        } else {
            //免费翻译
            $monthFreeCount = (int)$apiRow->current_month_free_count + $translatedCount;
            $data['translated_free_count'] = (int)$apiRow->translated_free_count + $translatedCount;
            $data['current_month_free_count'] = $monthFreeCount;
            if ($monthFreeCount > $apiRow->free_count_limit) {
                //超过免费额度
                $data['current_month_limit'] = 1;
            }
        }
        $updateResult = self::$apiTable->update($data, ['id = ?' => $apiId]);
        if (!$updateResult) {
            throw new \RuntimeException('更新免费翻译字数失败');
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
    public static function updateApiCountByAccessKey(string $accessKey, int $translatedCount): bool
    {
        $apiRow = self::getApiByAccessKey($accessKey);
        if (!$apiRow) {
            throw new \RuntimeException('接口不存在');
        }
        return self::updateApiCount($apiRow->id, $translatedCount);
    }

    /**
     * 获取接口列表
     * @return \Zend_Db_Table_Rowset_Abstract
     */
    public static function fetchApi(): \Zend_Db_Table_Rowset_Abstract
    {
        self::initTable();
        $where = [
            'id > ?'    => 0
        ];
        return self::$apiTable->fetchAll($where,'id ASC');
    }

    /**
     * 设置接口当月状态
     * @param int $apiId
     * @param bool $limitState true 禁用，false 启用
     * @return bool
     */
    public static function limitApi(int $apiId,bool $limitState = true): bool
    {
        self::initTable();
        $where = [
            'id = ?'    => $apiId
        ];
        if ($limitState) {
            $limitValue = 1;
        } else {
            $limitValue = 0;
        }
        $data = [
            'current_month_limit'   => $limitValue
        ];
        $result = self::$apiTable->update($data, $where);
        if ($result) {
            return true;
        }
        return false;
    }
}