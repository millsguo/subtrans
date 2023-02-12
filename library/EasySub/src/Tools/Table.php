<?php
/*
 * Copyright (c) 2022. 长沙用车无忧网络科技有限公司
 * 项目名称：yundianyi2-php74
 * 文件名称：Table.php
 * 修改时间：2022/10/28 上午1:15
 * 作者：millsguo
 */

namespace EasySub\Tools;

use Exception;
use Zend_Db;
use Zend_Db_Expr;
use Zend_Db_Select;
use Zend_Db_Table_Abstract;
use Zend_Db_Table_Row_Abstract;
use Zend_Db_Table_Rowset_Abstract;
use Zend_Db_Table_Select;
use Zend_Json_Encoder;
use Zend_Paginator;
use Zend_Paginator_Adapter_DbTableSelect;

/**
 * 数据库表对象
 *
 * @author  Mills
 * @version 2.0
 */
class Table extends Zend_Db_Table_Abstract
{
    //表名称
    protected $_name = '';
    //主键
    protected $_primary = 'id';

    //表字段名数组
    protected $_fieldKeyArray = null;

    //主键是否自增字段
    protected $_primaryAutoIncrement = true;

    //是否启用事务处理
    protected $_useTransfer = false;

    private $_message = [];

    /**
     * 构造函数
     *
     * @param string|null $tableName
     * @param string|null $primary
     * @param ?array $config
     */
    public function __construct(?string $tableName = null,?string $primary = null,?array $config = null)
    {
        if ($tableName !== null) {
            $this->setTableName($tableName);
        }
        if ($primary !== null) {
            $this->setPrimary($primary);
        }
        if (is_array($config)) {
            $config['name'] = $this->_name;
        } else {
            $config = ['name' => $this->_name];
        }

        parent::__construct($config);
    }

    /**
     * 设置表名称
     *
     * @param string $name
     *
     * @return $this
     */
    public function setTableName(string $name): self
    {
        $this->_name = $name;

        return $this;
    }

    /**
     * 设置表主键
     *
     * @param string $primary
     * @param bool   $isAutoIncrement
     *
     * @return $this
     */
    public function setPrimary(string $primary,bool $isAutoIncrement = true): self
    {
        $this->_primary = $primary;
        $this->_primaryAutoIncrement = $isAutoIncrement;

        return $this;
    }

    /**
     * 取得错误消息
     *
     * @param bool $returnString
     *
     * @return string | array
     */
    public function getMessage(bool $returnString = true): array|string
    {
        if ($returnString) {
            if (is_array($this->_message)) {
                $message = implode('', $this->_message);
            } else {
                $message = $this->_message;
            }

            return $message;
        }

        return $this->_message;
    }

    /**
     * 取得页数据
     * 页数据通过fetchAll()方法调用，并通过fetchAll()方法实现缓存
     * 注意：如果WHERE是SELECT对象，则ORDER无效
     *
     * @param array|Zend_Db_Select $where
     * @param string|null $order
     * @param int|null $count
     * @param int $page
     * @param string|null $group
     *
     * @return Zend_Paginator|bool
     */
    public function getPage(array|Zend_Db_Select $where,?string $order = null,?int $count = 20,int $page = 1,?string $group = null): bool|Zend_Paginator
    {
        if ($where instanceof Zend_Db_Select) {
            $select = $where;
            $selectTableObj = new \Zend_Paginator_Adapter_DbSelect($select);
        } else {
            $select = $this->select();
            if (is_array($where)) {
                foreach ($where as $key => $value) {
                    $select->where($key, $value);
                }
            }
            if ($group !== null) {
                $select->group($group);
            }
            $selectTableObj = new Zend_Paginator_Adapter_DbTableSelect($select);
        }

        try {
            if (!$select->getPart('order')) {
                $select->order($order);
            }
            $pageObj = new Zend_Paginator($selectTableObj);
            $pageObj->setCurrentPageNumber($page);
            $pageObj->setItemCountPerPage($count);
            return $pageObj;
        } catch (Exception $e) {
            $this->_message = $e->getMessage();
            Log::log($e->getMessage());
            Log::log($e->getTraceAsString());
            return false;
        }
    }

    /**
     * 取得指定字段组成的数组
     *
     * @param string $key
     * @param string $value
     * @param array|null $where
     * @param string|null $order
     *
     * @return array
     */
    public function getColsArray(string $key,string $value,?array $where = null,?string $order = null): array
    {
        $rowSet = $this->fetchAll($where, $order);
        if (is_countable($rowSet) && count($rowSet) > 0) {
            $return = [];
            foreach ($rowSet as $row) {
                if (isset($row->$key, $row->$value)) {
                    $return[$row->$key] = $row->$value;
                } else {
                    return $return;
                }
            }
        } else {
            return [];
        }

        return $return;
    }

    /**
     * 取得去重条数
     *
     * @param array|string $where
     * @param string|null $group
     * @param string|null $order
     * @param string $from
     * @param bool|string $distinct 去重字段
     *
     * @return bool|int
     */
    public function getDistinctCount(array|string $where,?string $group = null,?string $order = null, mixed $from = null,bool|string $distinct = false): bool|int
    {
        $select = $this->getAdapter()->select();
        if ($from === null) {
            if ($distinct !== false) {
                $from = [
                    'count' => new Zend_Db_Expr('COUNT(DISTINCT ' . $distinct . ')')
                ];
            } else {
                $from = ['count' => 'count(*)'];
            }
        }
        $select->from([$this->_name], $from);
        if (is_array($where)) {
            foreach ($where as $key => $value) {
                $select->where($key, $value);
            }
        } else {
            $select->where($where);
        }

        if ($group !== null) {
            $select->group($group);
        }
        if ($order !== null) {
            $select->order($order);
        }
        try {
            $stmt = $this->getAdapter()->query($select);
            $result = $stmt->fetchAll();
            return $result[0]['count'] ?? 0;
        } catch (Exception $e) {
            $this->_message = $e->getMessage();

            return false;
        }
    }

    /**
     * 取得条数
     *
     * @param array|string $where
     * @param string|null $group
     * @param string|null $order
     * @param string|null $from
     * @param bool|string $distinct 去重字段
     *
     * @return bool|int
     */
    public function getCount(array|string $where, ?string $group = null, ?string $order = null,?string $from = null,bool|string $distinct = false): bool|int
    {
        $select = $this->getAdapter()->select();
        if ($from === null) {
            if ($distinct !== false) {
                $from = ['count' => 'count(DISTINCT(' . $distinct . '))'];
            } else {
                $from = ['count' => 'count(*)'];
            }
        }
        $select->from([$this->_name], $from);
        if (is_array($where)) {
            foreach ($where as $key => $value) {
                $select->where($key, $value);
            }
        } else {
            $select->where($where);
        }
        if ($group !== null) {
            $select->group($group);
        }
        if ($order !== null) {
            $select->order($order);
        }
        try {
            $stmt = $this->getAdapter()->query($select);
            $result = $stmt->fetchAll();
            return $result[0]['count'] ?? 0;
        } catch (Exception $e) {
            $this->_message = $e->getMessage();

            return false;
        }
    }

    /**
     * 取得字段总和
     *
     * @param string       $field
     * @param array|string $where
     *
     * @return int|bool
     */
    public function getSum(string $field,string|array $where): bool|int
    {
        $select = $this->getAdapter()->select();
        $select->from([$this->_name], ['total' => 'sum(' . $field . ')']);
        if (is_array($where)) {
            foreach ($where as $key => $value) {
                $select->where($key, $value);
            }
        } else {
            $select->where($where);
        }
        try {
            $stmt = $this->getAdapter()->query($select);
            $result = $stmt->fetchAll();
            return $result[0]['total'] ?? 0;
        } catch (Exception $e) {
            $this->_message = $e->getMessage();

            return false;
        }
    }

    /**
     * 字段值增加
     *
     * @param string $cols 需要增加值的字段
     * @param array $where 满足需要增加值的条件
     * @param int|float $value 需要增加的值大小
     *
     * @return bool
     */
    public function increaseCols(string $cols,array $where,int|float $value = 1): bool
    {
        if (is_float($value)) {
            $value = (float)$value;
        } else {
            $value = (int)$value;
        }
        if ($value === 0) {
            return true;
        }

        return $this->colsChange($cols, $value, $where);
    }

    /**
     * 数值字段直接更新
     *
     * @param string $cols   需要更新的数值字段
     * @param int    $count  需要公式处理的值
     * @param array  $where  更新条件
     * @param string $method 更新公式，暂时支持 + - * /
     *
     * @return bool
     */
    public function colsChange(string $cols, int $count, array $where,string $method = '+'): bool
    {
        if ($method === '+' || $method === '-' || $method === '*' || $method === '/') {
            $methodChar = $method;
        } else {
            $this->_message = '不支持此计算公式';

            return false;
        }
        $data = [$cols => new Zend_Db_Expr($cols . ' ' . $methodChar . ' ' . $count)];

        $result = $this->update($data, $where);
        if ($result) {
            return true;
        }
        return false;
    }

    /**
     * 字段值减小
     *
     * @param string $cols 需要减小值的字段
     * @param array|string $where 满足需要减小值的条件
     * @param int|float $value 需要减小的值大小
     *
     * @return bool
     */
    public function reductionCols(string $cols,array|string $where, int|float $value = 1): bool
    {
        if (is_float($value)) {
            $value = (float)$value;
        } else {
            $value = (int)$value;
        }
        if ($value === 0) {
            return true;
        }

        return $this->colsChange($cols, $value, $where, '-');
    }

    /**
     * 批量插入多行数据
     *
     * @param array $data 格式：array(array('key' => 'value','key2' => 'value2'),array('key' => 'value11','key2' =>
     *                    'value22'));
     *
     * @return bool
     */
    public function insertMultiple(array $data):bool
    {
        if (is_array($data)) {
            if (count($data) <= 0) {
                $this->_message = '数据不能为空';

                return false;
            }
            $columns = [];
            $rowSet = [];
            foreach ($data as $row) {
                foreach ($row as $key => $value) {
                    if (!in_array($key, $columns, true)) {
                        $columns[] = $key;
                    }
                    unset($value);
                }
                $rowString = '';
                foreach ($columns as $iValue) {
                    $rowString .= '"' . addslashes($row[$iValue]) . '",';
                }
                $rowString = trim($rowString, ',');
                $rowSet[] = '(' . $rowString . ')';
            }
            try {
                $columnsString = implode(',', $columns);
                $valueString = implode(',', $rowSet);
                $sql = 'INSERT INTO ' . $this->_name . ' (' . $columnsString . ') VALUES ' . $valueString;
                $queryAdapter = $this->getAdapter()->query($sql);
                if ($queryAdapter) {
                    return true;
                }

                $this->_message = '数据库查询出错';

                return false;
            } catch (Exception $e) {
                $this->_message = $e->getMessage();

                return false;
            }
        } else {
            $this->_message = '参数必须为数组';

            return false;
        }
    }

    /**
     * 将参数转换为字符串
     *
     * @return string
     */
    private function convertParams(): string
    {
        $paramsCount = func_num_args();
        $params = func_get_args();
        if ($paramsCount < 1) {
            return '';
        }
        $encodeStr = [];
        foreach ($params as $item) {
            if (is_object($item)) {
                if ($item instanceof Zend_Db_Table_Select) {
                    $encodeStr[] = $item->__toString();
                } else {
                    $encodeStr[] = get_object_vars($item);
                }
            } else {
                $encodeStr[] = $item;
            }
        }
        $encodeParams = Zend_Json_Encoder::encode($encodeStr);

        return md5($encodeParams);
    }

    /**
     * 根据表格字段过滤参数
     *
     * @param array $data
     *
     * @return array
     */
    public function filterInsertAndUpdateParams(array $data): array
    {
        $newData = [];
        if ($this->_fieldKeyArray === null) {
            $this->_fieldKeyArray = $this->_db->describeTable($this->_name);
        }
        foreach ($this->_fieldKeyArray as $column) {
            if (isset($column['COLUMN_NAME'], $data[$column['COLUMN_NAME']])) {
                $newData[$column['COLUMN_NAME']] = $data[$column['COLUMN_NAME']];
            }
        }

        return $newData;
    }

    /**
     * 取得数据库对象
     *
     * @return Table
     */
    public function getTable(): Table
    {
        return $this;
    }

    /**
     * 取得数据表名称
     *
     * @return string
     */
    public function getTableName(): string
    {
        return $this->_name;
    }

    /**
     * 智能写入数据
     *
     * @param array $data
     *
     * @return bool|int
     */
    public function autoInsert(array $data): bool|int
    {
        $data = $this->filterInsertAndUpdateParams($data);
        $result = $this->insert($data);
        if ($result) {
            return $result;
        }

        $this->_message = '智能写入失败';

        return false;
    }

    /**
     * 智能更新数据
     *
     * @param array $data
     * @param string|array $where
     *
     * @return bool|int
     */
    public function autoUpdate(array $data, string|array $where): bool|int
    {
        $data = $this->filterInsertAndUpdateParams($data);
        $result = $this->update($data, $where);
        if ($result) {
            return $result;
        }

        $this->_message = '智能更新失败';

        return false;
    }

    /**
     * 智能获取数据
     *
     * @param string|array $where 条件
     * @param null|string|array $order 排序
     * @param null|int $count 数量
     * @param null|int $page 页数/OFFSET
     * @param bool $isPage 是否返回分页数据，默认为FALSE
     *
     * @return Zend_Paginator|Zend_Db_Table_Rowset_Abstract|bool
     */
    public function autoFetch(string|array $where = [], mixed $order = null, mixed $count = null, mixed $page = null, bool $isPage = false): bool|Zend_Paginator|Zend_Db_Table_Rowset_Abstract
    {
        try {
            if ($isPage) {
                return $this->getPage($where, $order, $count, $page);
            }

            return $this->fetchAll($where, $order, $count, $page);
        } catch (Exception $exception) {
            $this->_message = $exception->getMessage();

            return false;
        }
    }

    /**
     * 启用事务模式
     */
    public function beginTransaction(): Table
    {
        if (!$this->_useTransfer) {
            $this->_db->beginTransaction();
            $this->_useTransfer = true;
        }

        return $this;
    }

    /**
     * 提交事务
     */
    public function commit(): Table
    {
        if ($this->_useTransfer) {
            $this->_db->commit();
            $this->_useTransfer = false;
        }

        return $this;
    }

    /**
     * 事务回滚
     */
    public function rollback(): Table
    {
        if ($this->_useTransfer) {
            $this->_db->rollBack();
            $this->_useTransfer = false;
        }

        return $this;
    }

    /**
     * 对象销毁时，如果事务还没关闭，则自动提交事务并关闭
     */
    public function __destruct()
    {
        if ($this->_useTransfer) {
            $this->rollback();
        }
    }

    /**
     * 删除所有数据
     *
     * @return bool
     */
    public function truncate(): bool
    {
        try {
            $this->getAdapter()->query('TRUNCATE TABLE ' . $this->_name);

            return true;
        } catch (Exception $e) {
            $this->_message = $e->getMessage();

            return false;
        }
    }

    /**
     * 根据条件检查数据是否存在
     *
     * @param array $where
     * @param int   $disId 排除ID
     *
     * @return bool|Zend_Db_Table_Row_Abstract
     */
    public function checkValueExists(array $where, int $disId = 0): Zend_Db_Table_Row_Abstract|bool
    {
        if ($disId > 0) {
            $where['id != ?'] = $disId;
        }
        $row = $this->fetchRow($where);
        if (isset($row->id)) {
            return $row;
        }

        return false;
    }

    /**
     * 使用JOIN查询多表
     *
     * @param array  $where    [key => value]
     * @param string $joinTable
     * @param string $joinRule 'master.id = join.customid'
     * @param string $order
     * @param int    $count
     * @param int    $offset
     *
     * @return array
     */
    public function fetchJoin(array $where, string $joinTable, string $joinRule, string $order,int $count,int $offset): array
    {
        $select = $this->getAdapter()->select();
        $select->from(['master' => $this->_name]);
        $select->join(['join' => $joinTable], $joinRule);
        if (is_array($where)) {
            foreach ($where as $key => $value) {
                $select->where($key, $value);
            }
        }
        $select->order($order);
        $select->limit($count, $offset);
        return $select->query()->fetchAll(Zend_Db::FETCH_OBJ);
    }

    /**
     * 多表连接取条数
     *
     * @param array  $where
     * @param string $joinTable
     * @param string $joinRule
     *
     * @return int
     */
    public function getJoinCount(array $where, string $joinTable,string $joinRule): int
    {
        $select = $this->getAdapter()->select();
        $from = ['count' => 'count(*)'];
        $select->from(['master' => $this->_name], $from);
        $select->join(['join' => $joinTable], $joinRule);
        if (is_array($where)) {
            foreach ($where as $key => $value) {
                $select->where($key, $value);
            }
        }
        $queryObj = $select->query();
        $returnData = $queryObj->fetchAll();
        return $returnData[0]['count'] ?? 0;
    }

    /**
     * 多表连接取字段总和
     *
     * @param string $sumField
     * @param array  $where
     * @param string $joinTable
     * @param string $joinRule
     *
     * @return int
     */
    public function getJoinSum(string $sumField, array $where, string $joinTable, string $joinRule): int
    {
        $select = $this->getAdapter()->select();
        $select->from(['master' => $this->_name], ['total' => 'sum(' . $sumField . ')']);
        $select->join(['join' => $joinTable], $joinRule);
        if (is_array($where)) {
            foreach ($where as $key => $value) {
                $select->where($key, $value);
            }
        }
        $queryObj = $select->query();
        $returnData = $queryObj->fetchAll();
        return $returnData[0]['total'] ?? 0;
    }

    /**
     * 使用OR条件获取数据列表
     *
     * @param array $orWhere
     * @param array|string $order
     * @param null|int $count
     * @param null|int $offset
     *
     * @return array
     */
    public function fetchAllByOr(array $orWhere, array|string $order = [],?int $count = null, ?int $offset = null): array
    {
        $select = $this->getAdapter()->select()->from($this->_name);
        if (is_array($orWhere)) {
            $firstWhere = false;
            foreach ($orWhere as $key => $value) {
                if (!$firstWhere) {
                    $select->where($key, $value);
                    $firstWhere = true;
                } else {
                    $select->orWhere($key, $value);
                }
            }
        }
        if (is_array($order) && count($order) > 0) {
            $select->order($order);
        } else {
            $select->order([$order]);
        }
        if ($offset === null) {
            $offset = 0;
        }
        if ($count !== null || $offset !== null) {
            $select->limit($count, $offset);
        }
        $queryObj = $select->query();
        $data = $queryObj->fetchAll(Zend_Db::FETCH_OBJ);
        if (count($data) > 0) {
            return $data;
        }

        return [];
    }

    /**
     * 指定新的数据库连接
     *
     * @param $dbAdapter
     *
     * @return $this
     */
    public function setDbAdapter($dbAdapter): self
    {
        $this->_setAdapter($dbAdapter);
        return $this;
    }
}
