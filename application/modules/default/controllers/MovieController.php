<?php
/*
 * Copyright (c) 2022. millsguo
 * 项目名称：subtrans
 * 文件名称：IndexController.php
 * 修改时间：2022/10/28 上午1:06
 * 作者：millsguo
 */

use EasySub\Video\Movie;

/**
 * 后台管理首页
 * @author MillsGuo
 *
 */
class MovieController extends Default_Model_ControllerHelper
{
    private Movie $movie;

    public function action_init(): void
    {
        $this->movie = new Movie();
    }
    
    /**
     * 转列表页
     */
    public function indexAction(): void
    {
        $this->redirect('/movie/list/');
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
        $this->view->rows = $this->movie->autoFetch($where,'date_added DESC',30,$this->page,true);
    }

    /**
     * 电影详情页
     * @return void
     */
    public function showAction(): void
    {
        if (!isset($this->params['id'])) {
            $this->quickRedirect('缺少电影ID','/movie/','warning');
        }
        $id = (int)$this->params['id'];
        $movieRow = $this->movie->getMovie($id);
        if (!$movieRow) {
            $this->quickRedirect($this->movie->getMessage(),'/movie/','warning');
        }
        $this->view->movieRow = $movieRow;
        $nfoData = $this->movie->getMovieNfo($id);
        $this->view->nfoData = $nfoData;
    }

    /**
     * 删除电影
     * @return void
     */
    public function deleteAction(): void
    {
        if (!isset($this->params['id'],$this->params['type'])) {
            $this->quickRedirect('缺少电影ID','/movie/','warning');
        }
        $id = (int)$this->params['id'];
        if ($this->params['type'] === 'record') {
            $deleteDirector = false;
            $successMsg = '电影记录删除成功，稍后程序将自动扫描添加';
        } else {
            $deleteDirector = true;
            $successMsg = '电影记录和文件删除成功';
        }
        $result = $this->movie->deleteMovie($id, $deleteDirector);
        if ($result) {
            $this->quickRedirect($successMsg, '/movie/');
        } else {
            $this->quickRedirect($this->movie->getMessage(), '/movie/show/id/' . $this->params['id'], 'danger');
        }
    }
}
