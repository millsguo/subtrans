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