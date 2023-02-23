<?php


namespace EasySub\Tools;

use Exception;
use Zend_Db;
use Zend_Db_Adapter_Abstract;
use Zend_Db_Exception;
use Zend_Db_Table;

class Db
{
    protected Zend_Db_Adapter_Abstract $db;

    /**
     * 构造函数
     *
     * @param array $config
     * @param string $dbType
     */
    public function __construct(array $config, string $dbType)
    {
        $this->init($config, $dbType);
        $this->autoUpgradeDbFile();
    }

    /**
     * 自动升级数据库
     * @return bool
     */
    protected function autoUpgradeDbFile(): bool
    {
        $masterTable = new Table('sqlite_master','tbl_name');
        $where = [
            'type = ?'      => 'table',
            'tbl_name = ?'  => 'tv_info'
        ];
        $row = $masterTable->fetchRow($where);
        if (isset($row->name)) {
            Log::info('数据库不需要升级');
            return true;
        }
        $upgradeResult = copy(BASE_APP_PATH . '/insideConfig/subtrans-init', BASE_APP_PATH . '/config/subtrans');
        if ($upgradeResult) {
            Log::info('数据库升级成功');
        } else {
            Log::err('数据库升级失败');
        }
        return $upgradeResult;
    }

    /**
     * 数据库连接初始化
     *
     * @param array $config
     * @param string $dbType
     * @return void
     */
    public function init(array $config, string $dbType)
    {
        switch (strtolower($dbType)) {
            case 'sqlite':
            case 'pdo_sqlite':
            case 'sqlite3':
                $adapterName = 'Pdo_Sqlite';
                break;
            case 'pdo_mysql':
            case 'mysql':
            default:
                $adapterName = 'Pdo_Mysql';
                break;
        }
        try {
            $this->db = Zend_Db::factory($adapterName, $config);
            Zend_Db_Table::setDefaultAdapter($this->db);
        } catch (Zend_Db_Exception $e) {
            Log::debug($e->getMessage());
            Log::debug($e->getTraceAsString());
        }
    }

    /**
     * 获得数据库连接对象
     *
     * @return Zend_Db_Adapter_Abstract
     * @throws Exception
     */
    public function getDb(): Zend_Db_Adapter_Abstract
    {
        if (!isset($this->db)) {
            throw new \RuntimeException('数据库连接未初始化');
        }
        return $this->db;
    }
}