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
class TvController extends Default_Model_ControllerHelper
{
    public function action_init(): void
    {
        \EasySub\CheckSub::initLibrary();
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
    }

    public function scanAction()
    {
        if (!isset($this->params['target'])) {
            $this->quickRedirect('未指定扫描目标','/tv/list/','warning');
        }
        if (strtolower($this->params['target']) === 'all') {
            $taskObj = new \EasySub\Task\Queue();
            $tvLibraryArray = \EasySub\Video\Store::getTvLibrary();
            if (!$tvLibraryArray) {
                $this->quickRedirect('没有添加剧集库','/tv/list/','warning');
            }
            $message = '';
            foreach ($tvLibraryArray as $tvPath) {
                $result = $taskObj->addTask('tv',$tvPath);
                if ($result) {
                    $message .= '剧集库[' . $tvPath . ']添加成功';
                } else {
                    $message .= '剧集库[' . $tvPath . ']添加失败：' . $taskObj->getMessage();
                }
            }
            \EasySub\Task\Command::runScan();
            $this->quickRedirect($message,'/tv/list/','warning');
        }
        $this->quickRedirect('暂不支持单独扫描', '/tv/list/','warning');
    }
}
