<?php

namespace EasySub\Task;

use EasySub\Tools\Table;

/**
 * 任务管理
 */
class Queue
{

    protected Table $queueTable;
    private string $message;

    /**
     * 任务构造函数
     */
    public function __construct()
    {
        $this->queueTable = new Table('task_queue');
    }

    /**
     * 添加任务
     * @param string $taskType
     * @param string $targetPath
     * @return bool
     */
    public function addTask(string $taskType,string $targetPath): bool
    {
        $taskType = strtoupper($taskType);
        if ($taskType !== 'MOVIE' && $taskType !== 'TV') {
            $this->message = '任务类型，只支持MOVIE和TV';
            return false;
        }
        $taskHash = md5(strtoupper($taskType) . trim($targetPath,'/'));
        $hashRow = $this->getTaskByHash($taskHash);
        if ($hashRow) {
            $this->message = '任务已存在，请不要重复添加';
            return false;
        }
        $data = [
            'target_path'   => $targetPath,
            'task_type'   => strtoupper($taskType),
            'task_hash'     => $taskHash,
            'task_time'     => time()
        ];
        $result = $this->queueTable->insert($data);
        if ($result) {
            return true;
        }
        $this->message = '保存任务至数据库失败';
        return false;
    }

    /**
     * 通过HASH获取任务信息
     * @param string $hash
     * @return false|\Zend_Db_Table_Row_Abstract
     */
    private function getTaskByHash(string $hash): bool|\Zend_Db_Table_Row_Abstract
    {
        $where = [
            'task_hash = ?' => $hash
        ];
        $row = $this->queueTable->fetchRow($where);
        if (isset($row->id)) {
            return true;
        }
        return false;
    }

    /**
     * 任务完成后，需要删除任务
     * @param int $id
     * @return bool
     */
    public function deleteTask(int $id): bool
    {
        $where = [
            'id = ?'    => $id
        ];
        $result = $this->queueTable->delete($where);
        if ($result) {
            return true;
        }
        return false;
    }

    /**
     * 获取任务列表
     * @param int $count
     * @param int $page
     * @return false|\Zend_Db_Table_Rowset_Abstract
     */
    public function fetchTask(int $count,int $page): bool|\Zend_Db_Table_Rowset_Abstract
    {
        if ($page < 1) {
            $page = 1;
        }
        $where = [
            'id > ?'    => 0
        ];
        $offset = ($page - 1) * $count;
        $rows = $this->queueTable->fetchAll($where,'task_time ASC',$count,$offset);
        if (is_countable($rows) && count($rows) > 0) {
            return $rows;
        }
        return false;
    }

    /**
     * 获取错误信息
     * @return string
     */
    public function getMessage(): string
    {
        return $this->message;
    }
}