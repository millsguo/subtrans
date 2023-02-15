<?php
/*
 * Copyright (c) 2022. millsguo
 * 项目名称：subtrans
 * 文件名称：IndexController.php
 * 修改时间：2022/10/28 上午1:06
 * 作者：millsguo
 */

/**
 * 后台管理首页
 * @author MillsGuo
 *
 */
class MovieController extends Default_Model_ControllerHelper
{
    public function action_init(): void
    {
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
    }

    public function scanAction()
    {
        if (!isset($this->params['target'])) {
            $this->quickRedirect('未指定扫描目标','/movie/list/','warning');
        }
        if (strtolower($this->params['target']) === 'all') {
            $taskObj = new \EasySub\Task\Queue();

            $taskObj->addTask('movie','/data/movies-1');
        }
    }
}
