<?php

namespace EasySub\Video;

use EasySub\Tools\NfoTrait;
use EasySub\Tools\Table;
use Zend_Db_Table_Row_Abstract;

/**
 * 电影类
 */
class Movie
{
    use NfoTrait;

    private Table $infoTable;

    public function __construct()
    {
        $this->infoTable = new Table('movies_info');
    }

    /**
     * 增加电影
     * @param string $filePath
     * @param bool $haveChineseSubTitle
     * @return bool
     */
    public function addMovie(string $filePath,bool $haveChineseSubTitle = false): bool
    {
        if (!is_readable($filePath)) {
            $this->message = '视频文件不存在';
            return false;
        }
        $pathInfo = pathinfo($filePath);
        if (!isset($pathInfo['dirname'])) {
            $this->message = '获取文件信息失败';
            return false;
        }
        $pathHash = md5($filePath);
        $dirPath = $pathInfo['dirname'];
        $fileName = $pathInfo['basename'];

        $baseData = [
            'file_path' => $dirPath,
            'file_name' => $fileName,
            'file_path_hash'    => $pathHash,
            'scan_time' => time()
        ];
        if ($haveChineseSubTitle) {
            $baseData['have_zh_sub'] = 1;
        } else {
            $baseData['have_zh_sub'] = 0;
        }
        $embyData = $this->getNfo($dirPath,$fileName,'movieInfo');

        $data = array_merge($baseData,$embyData);

        $existsRow = $this->getMovieByHash($pathHash);
        if ($existsRow) {
            $result = $this->infoTable->update($data,['id = ?' => $existsRow->id]);
        } else {
            $result = $this->infoTable->insert($data);
        }
        if ($result) {
            return true;
        }

        return false;
    }

    /**
     * 通过文件路径HASH查询信息
     * @param string $pathHash
     * @return bool|Zend_Db_Table_Row_Abstract
     */
    public function getMovieByHash(string $pathHash): bool|Zend_Db_Table_Row_Abstract
    {
        $where = [
            'file_path_hash = ?'    => $pathHash
        ];
        $row = $this->infoTable->fetchRow($where);
        if (isset($row->id)) {
            return $row;
        }
        return false;
    }

    /**
     * 获取信息
     * @param int $id
     * @return bool|Zend_Db_Table_Row_Abstract
     */
    public function getMovie(int $id): bool|Zend_Db_Table_Row_Abstract
    {
        $where = [
            'id = ?'    => $id
        ];
        $row = $this->infoTable->fetchRow($where);
        if (isset($row->id)) {
            return $row;
        }
        return false;
    }

    /**
     * 获取电影NFO数据
     * @param int $id
     * @return bool|array
     */
    public function getMovieNfo(int $id): bool|array
    {
        $movieRow = $this->getMovie($id);
        if (!$movieRow) {
            return false;
        }
        return $this->getNfo($movieRow->file_path,$movieRow->file_name,'movieInfo',true);
    }

    /**
     * 删除电影
     * @param int $id
     * @return bool
     */
    public function deleteMovie(int $id): bool
    {
        $where = [
            'id = ?'    => $id
        ];
        $result = $this->infoTable->delete($where);
        if ($result) {
            return true;
        }
        return false;
    }

    /**
     * 获取电影列表
     * @param $where
     * @param $order
     * @param $count
     * @param $page
     * @param bool $isPage
     * @return bool|\Zend_Db_Table_Rowset_Abstract|\Zend_Paginator
     */
    public function autoFetch($where,$order,$count,$page,bool $isPage = false): bool|\Zend_Paginator|\Zend_Db_Table_Rowset_Abstract
    {
        return $this->infoTable->autoFetch($where,$order,$count,$page,$isPage);
    }
}