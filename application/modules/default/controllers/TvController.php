<?php
/*
 * Copyright (c) 2022. millsguo
 * 项目名称：subtrans
 * 文件名称：IndexController.php
 * 修改时间：2022/10/28 上午1:06
 * 作者：millsguo
 */

use EasySub\Video\Tv;

/**
 * 后台管理首页
 * @author MillsGuo
 *
 */
class TvController extends Default_Model_ControllerHelper
{
    private Tv $tv;

    public function action_init(): void
    {
        $this->tv = new Tv();
    }
    
    /**
     * 转列表页
     */
    public function indexAction(): void
    {
        $this->redirect('/tv/list/');
    }

    /**
     * 列表页
     * @return void
     */
    public function listAction(): void
    {
        $where = [
            'id > ?'    => 0
        ];
        $this->view->rows = $this->tv->autoFetchTv($where,'date_added DESC',30,$this->page,true);
    }

    /**
     * 剧集信息
     * @return void
     */
    public function seasonAction(): void
    {
        if (!isset($this->params['id'])) {
            $this->quickRedirect('参数错误', '/tv/list/','warning');
        }
        $tvId = (int)$this->params['id'];

        $tvRow = $this->tv->getTv($tvId);
        if (!$tvRow) {
            $this->quickRedirect($this->tv->getMessage(), '/tv/list/','warning');
        }
        $this->view->tvRow = $tvRow;
        $this->view->nfoData = $this->tv->getTvNfo($tvId);
        $this->view->seasonRows = $this->tv->fetchSeasonByTv($tvId);
        $this->view->episodeRows = $this->tv->fetchEpisodeByTv($tvId);
    }

    /**
     * 剧集单集详情页
     * @return void
     */
    public function showAction(): void
    {
        if (!isset($this->params['id'])) {
            $this->quickRedirect('缺少集ID','/tv/','warning');
        }
        $id = (int)$this->params['id'];
        $episodeRow = $this->tv->getEpisode($id);
        if (!$episodeRow) {
            $this->quickRedirect($this->tv->getMessage(),'/tv/','warning');
        }
        $seasonRow = $this->tv->getSeason((int)$episodeRow->season_id);
        if (!$seasonRow) {
            $this->quickRedirect($this->tv->getMessage(),'/tv/','warning');
        }
        $episodeRows = $this->tv->fetchEpisodeBySeason($seasonRow->id);

        $this->view->tvRow = $this->tv->getTv($episodeRow->tv_id);
        $this->view->episodeRow = $episodeRow;
        $nfoData = $this->tv->getEpisodeNfo($id);
        $this->view->nfoData = $nfoData;
        $this->view->seasonRow = $seasonRow;
        $this->view->episodeArray = $episodeRows;
    }

    /**
     * 删除处理
     * @return void
     */
    public function deleteAction()
    {
        if (!isset($this->params['id'],$this->params['type'],$this->params['target'])) {
            $this->quickRedirect('缺少剧集ID','/tv/','warning');
        }
        $id = (int)$this->params['id'];

        if ($this->params['type'] === 'record') {
            $deleteDirector = false;
            $successMsg = '删除成功，稍后程序将自动扫描添加';
        } else {
            $deleteDirector = true;
            $successMsg = '和文件删除成功';
        }
        switch ($this->params['target']) {
            case 'tv':
                $targetName = '剧集全部';
                $result = $this->tv->deleteTv($id, $deleteDirector);
                break;
            case 'season':
                $targetName = '剧集全季';
                $result = $this->tv->deleteSeason($id,$deleteDirector);
                break;
            case 'episode':
                $targetName = '剧集';
                $result = $this->tv->deleteEpisode($id, $deleteDirector);
                break;
            default:
                $targetName = '';
                $result = false;
                $this->quickRedirect('目标不支持','/tv/','warning');
        }
        if ($result) {
            $this->quickRedirect($targetName . $successMsg, '/tv/');
        } else {
            $this->quickRedirect($this->tv->getMessage(), '/tv/','error');
        }
    }
}
