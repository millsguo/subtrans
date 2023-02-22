<?php

namespace EasySub\Video;

use EasySub\Tools\NfoTrait;
use EasySub\Tools\Table;
use Zend_Db_Table_Row_Abstract;
use Zend_Db_Table_Rowset_Abstract;
use Zend_Paginator;

class Tv
{
    use NfoTrait;

    private Table $seasonTable;
    private Table $tvTable;
    private Table $episodeTable;

    public function __construct()
    {
        $this->tvTable = new Table('tv_info');
        $this->seasonTable = new Table('season_info');
        $this->episodeTable = new Table('episode_info');
    }

    /**
     * 增加剧集
     * @param string $tvPath
     * @return bool
     */
    public function addTv(string $tvPath): bool
    {
        if (!is_dir($tvPath)) {
            $this->message = '剧集目录不存在';
            return false;
        }

        $pathHash = $this->getTvHash($tvPath);

        $baseData = [
            'tv_path'   => $tvPath,
            'tv_path_hash'  => $pathHash,
            'scan_time' => time()
        ];
        $embyData = $this->getNfo($tvPath,'tvshow.nfo','tvInfo');
        $data = array_merge($baseData,$embyData);

        $existsRow = $this->getTvByHash($pathHash);
        if ($existsRow) {
            $result = $this->tvTable->update($data,['id = ?' => $existsRow->id]);
            if ($result) {
                return $existsRow->id;
            }
        } else {
            $result = $this->tvTable->insert($data);
            if ($result) {
                return $result;
            }
        }
        $this->message = $this->tvTable->getMessage();
        if (empty($this->message)) {
            $this->message = '剧集信息更新失败';
        }
        return false;
    }

    /**
     * 通过文件路径HASH查询信息
     * @param string $pathHash
     * @return bool|Zend_Db_Table_Row_Abstract
     */
    public function getTvByHash(string $pathHash): bool|Zend_Db_Table_Row_Abstract
    {
        $where = [
            'file_path_hash = ?'    => $pathHash
        ];
        $row = $this->tvTable->fetchRow($where);
        if (isset($row->id)) {
            return $row;
        }
        return false;
    }

    /**
     * 获取剧集信息
     * @param int $id
     * @return bool|Zend_Db_Table_Row_Abstract
     */
    public function getTv(int $id): bool|Zend_Db_Table_Row_Abstract
    {
        $where = [
            'id = ?'    => $id
        ];
        $row = $this->tvTable->fetchRow($where);
        if (isset($row->id)) {
            return $row;
        }
        return false;
    }

    /**
     * 删除剧集
     * @param int $id
     * @return bool
     */
    public function deleteTv(int $id): bool
    {
        $where = [
            'id = ?'    => $id
        ];
        $result = $this->tvTable->delete($where);
        if ($result) {
            $seasonRows = $this->fetchSeasonByTv($id);
            if (is_countable($seasonRows) && count($seasonRows) > 0) {
                foreach ($seasonRows as $seasonRow) {
                    $this->deleteSeason($seasonRow->id);
                }
            }
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
     * @return bool|Zend_Db_Table_Rowset_Abstract|Zend_Paginator
     */
    public function autoFetchTv($where,$order,$count,$page,bool $isPage = false): bool|Zend_Paginator|Zend_Db_Table_Rowset_Abstract
    {
        return $this->tvTable->autoFetch($where,$order,$count,$page,$isPage);
    }

    /**
     * 增加季信息
     * @param int $tvId
     * @param string $seasonPath
     * @return false|mixed|string
     */
    public function addSeason(int $tvId,string $seasonPath): mixed
    {
        $tvRow = $this->getTv($tvId);
        if (!$tvRow) {
            return false;
        }
        if (!is_dir($seasonPath)) {
            $this->message = $seasonPath . '不是目录';
            return false;
        }

        $seasonData = $this->getNfo($seasonPath, 'season.nfo','seasonInfo');

        $seasonHash = $this->getSeasonHash($seasonPath);

        $baseData = [
            'tv_id' => $tvId,
            'tv_title'  => $tvRow->title,
            'season_path'   => $seasonPath,
            'season_path_hash'  => $seasonHash
        ];
        $data = array_merge($seasonData, $baseData);

        $seasonRow = $this->getSeasonByPathHash($seasonHash);
        if (!$seasonRow) {
            $seasonId = $this->seasonTable->insert($data);
            if ($seasonId) {
                return $seasonId;
            }
        } else {
            $where = [
                'id = ?'    => $seasonRow->id
            ];
            $result = $this->seasonTable->update($data, $where);
            if ($result) {
                return $seasonRow->id;
            }
        }
        $this->message = $this->seasonTable->getMessage();
        if (empty($this->message)) {
            $this->message = '保存Season信息失败';
        }
        return false;
    }

    /**
     * 通过路径HASH获取季信息
     * @param string $pathHash
     * @return bool|Zend_Db_Table_Row_Abstract|null
     */
    public function getSeasonByPathHash(string $pathHash): bool|Zend_Db_Table_Row_Abstract|null
    {
        $where = [
            'season_path_hash = ?'  => $pathHash
        ];
        $row = $this->seasonTable->fetchRow($where);
        if (isset($row->id)) {
            return $row;
        }
        return false;
    }

    /**
     * 通过季ID获取季信息
     * @param int $seasonId
     * @return bool|Zend_Db_Table_Row_Abstract|null
     */
    public function getSeason(int $seasonId): bool|Zend_Db_Table_Row_Abstract|null
    {
        $where = [
            'id = ?'    => $seasonId
        ];
        $row = $this->seasonTable->fetchRow($where);
        if (isset($row->id)) {
            return $row;
        }
        return false;
    }

    /**
     * 删除季信息
     * @param int $seasonId
     * @return bool
     */
    public function deleteSeason(int $seasonId): bool
    {
        $where = [
            'id = ?'    => $seasonId
        ];
        $result = $this->seasonTable->delete($where);
        if ($result) {
            //同时删除单集信息
            $episodeWhere = [
                'season_id = ?' => $seasonId
            ];
            $this->episodeTable->delete($episodeWhere);
            return true;
        }
        return false;
    }

    /**
     * 通过剧集ID获取季列表
     * @param int $tvId
     * @return Zend_Db_Table_Rowset_Abstract
     */
    public function fetchSeasonByTv(int $tvId): Zend_Db_Table_Rowset_Abstract
    {
        $where = [
            'tv_id = ?' => $tvId
        ];
        return $this->seasonTable->fetchAll($where, 'season_number ASC');
    }

    /**
     * 增加单集信息
     * @param int $seasonId
     * @param string $episodePath
     * @param string $episodeFilename
     * @param bool $haveChineseSub
     * @return false|mixed|string
     */
    public function addEpisode(int $seasonId,string $episodePath,string $episodeFilename,bool $haveChineseSub = false): mixed
    {
        $seasonRow = $this->getSeason($seasonId);
        if (!$seasonRow) {
            return false;
        }
        if ($haveChineseSub) {
            $haveZhSub = 1;
        } else {
            $haveZhSub = 0;
        }
        $episodeData = $this->getNfo($episodePath, $episodeFilename, 'episodeInfo');

        $episodeHash = $this->getEpisodeHash($episodePath, $episodeFilename);

        $baseData = [
            'file_path' => $episodePath,
            'file_name' => $episodeFilename,
            'file_path_hash'    => $episodeHash,
            'have_zh_sub'   => $haveZhSub,
            'season_id' => $seasonId,
            'tv_id'     => $seasonRow->tv_id,
            'tv_title'  => $seasonRow->tv_title
        ];

        $data = array_merge($baseData, $episodeData);

        $episodeRow = $this->getEpisodeByPathHash($episodeHash);
        if (!$episodeRow) {
            $episodeId = $this->episodeTable->insert($data);
            if ($episodeId) {
                return $episodeId;
            }
        } else {
            $updateWhere = [
                'id = ?'    => $episodeRow->id
            ];
            $result = $this->episodeTable->update($data, $updateWhere);
            if ($result) {
                return $episodeRow->id;
            }
        }
        $this->message = $this->episodeTable->getMessage();
        if (empty($this->message)) {
            $this->message = '保存单集信息失败';
        }
        return false;
    }

    /**
     * 通过HASH获取单集信息
     * @param string $pathHash
     * @return bool|Zend_Db_Table_Row_Abstract|null
     */
    public function getEpisodeByPathHash(string $pathHash): bool|Zend_Db_Table_Row_Abstract|null
    {
        $where = [
            'file_path_hash = ?'    => $pathHash
        ];
        $row = $this->episodeTable->fetchRow($where);
        if (isset($row->id)) {
            return $row;
        }
        $this->message = $this->episodeTable->getMessage();
        if (empty($this->message)) {
            $this->message = '剧集不存在';
        }
        return false;
    }

    /**
     * 获取单集信息
     * @param int $episodeId
     * @return bool|Zend_Db_Table_Row_Abstract|null
     */
    public function getEpisode(int $episodeId): bool|Zend_Db_Table_Row_Abstract|null
    {
        $where = [
            'id = ?'    => $episodeId
        ];
        $row = $this->episodeTable->fetchRow($where);
        if (isset($row->id)) {
            return $row;
        }
        return false;
    }

    /**
     * 删除单集信息
     * @param int $episodeId
     * @return bool
     */
    public function deleteEpisode(int $episodeId): bool
    {
        $where = [
            'id = ?'    => $episodeId
        ];
        $result = $this->episodeTable->delete($where);
        if ($result) {
            return true;
        }
        return false;
    }

    /**
     * 获取整季所有集
     * @param int $seasonId
     * @return Zend_Db_Table_Rowset_Abstract
     */
    public function fetchEpisodeBySeason(int $seasonId): Zend_Db_Table_Rowset_Abstract
    {
        $where = [
            'season_id = ?' => $seasonId
        ];
        return $this->episodeTable->fetchAll($where,'episode ASC');
    }

    /**
     * 获取单集hash
     * @param string $filePath
     * @param string $filename
     * @return string
     */
    private function getEpisodeHash(string $filePath,string $filename): string
    {
        return md5('episode_' . $filePath . $filename);
    }

    /**
     * 获取季HASH
     * @param string $seasonPath
     * @return string
     */
    private function getSeasonHash(string $seasonPath): string
    {
        return md5('season_' . $seasonPath);
    }

    /**
     * 获取剧集HASH
     * @param string $tvPath
     * @return string
     */
    private function getTvHash(string $tvPath): string
    {
        return md5('tv_' . $tvPath);
    }
}