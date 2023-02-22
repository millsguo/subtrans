<?php

namespace EasySub\Video;

use EasySub\Tools\Table;

class Tv
{

    private Table $infoTable;
    private string $message = '';
    private Table $seasonTable;

    public function __construct()
    {

        $this->seasonTable = new Table('tv_season');
        $this->infoTable = new Table('tv_info');
    }

    /**
     * 增加电影
     * @param string $filePath
     * @param bool $haveChineseSubTitle
     * @return bool
     */
    public function addTv(string $filePath,bool $haveChineseSubTitle = false): bool
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
        $fileName = $pathInfo['filename'];

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
        $embyData = $this->getNfo($dirPath,$fileName);
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
     * @return bool|\Zend_Db_Table_Row_Abstract
     */
    public function getMovieByHash(string $pathHash): bool|\Zend_Db_Table_Row_Abstract
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
     * @return bool|\Zend_Db_Table_Row_Abstract
     */
    public function getMovie(int $id): bool|\Zend_Db_Table_Row_Abstract
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

    /**
     * 获取NFO信息
     * @param string $dirPath
     * @param string $fileName
     * @return array
     */
    private function getNfo(string $dirPath,string $fileName): array
    {
        if (is_readable($dirPath . '/' . $fileName . '.nfo')) {
            //EMBY 刮削信息
            $fileInfo = simplexml_load_string(file_get_contents($dirPath . '/' . $fileName . '.nfo'));
            return $this->getDataFromEmbyNfo($fileInfo);
        }

        return [];
    }

    /**
     * 获取NFO数据到数组
     * @param \SimpleXMLElement $simpleXml
     * @return array
     */
    private function getDataFromEmbyNfo(\SimpleXMLElement $simpleXml): array
    {
        $returnField = [
            'title',
            'originaltitle',
            'dateadded',
            'rating',
            'year',
            'imdbid',
            'tmdbid',
            'runtime'
        ];
        $data = [];
        foreach ($returnField as $key) {
            switch ($key) {
                case 'originaltitle':
                    if (isset($simpleXml->{$key})) {
                        $data['original_title'] = $simpleXml->{$key};
                    }
                    break;
                case 'dateadded':
                    if (isset($simpleXml->{$key})) {
                        $data['date_added'] = $simpleXml->{$key};
                    }
                    break;
                case 'imdbid':
                    if (isset($simpleXml->{$key})) {
                        $data['imdb_id'] = $simpleXml->{$key};
                    }
                    break;
                case 'tmdbid':
                    if (isset($simpleXml->{$key})) {
                        $data['tmdb_id'] = $simpleXml->{$key};
                    }
                    break;
                default:
                    if (isset($simpleXml->{$key})) {
                        $data[$key] = $simpleXml->{$key};
                    }
                    break;
            }
        }
        return $data;
    }

    public function getMessage(): string
    {
        return $this->message;
    }
}