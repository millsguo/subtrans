<?php
/*
 * Copyright (c) 2022. millsguo
 * 项目名称：subtrans
 * 文件名称：IndexController.php
 * 修改时间：2022/10/28 上午1:06
 * 作者：millsguo
 */

use EasySub\Video\Store;

/**
 * 任务管理
 * @author MillsGuo
 *
 */
class TaskController extends Default_Model_ControllerHelper
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
        $this->redirect('/');
    }

    /**
     * 添加扫描任务
     * @return void
     */
    public function scanAction(): void
    {
        if (!$this->isAjax()) {
            $this->quickRedirect('访问方法错误','/','danger');
        }

        if (!isset($this->params['target'])) {
            $this->json(['code' => 400,'msg' => '参数错误']);
        }
        $tv = new \EasySub\Video\Tv();

        $target = strtolower($this->params['target']);
        $taskObj = new \EasySub\Task\Queue();
        switch ($target) {
            case 'all':
                if (!isset($this->params['type'])) {
                    $this->json(['code' => 406,'msg' => '缺少电影库类型参数']);
                }
                $targetType = strtoupper($this->params['type']);
                Store::initLibraryFromConfig();
                if ($targetType === 'TV') {
                    $libraryArray = Store::getTvLibrary();
                    $typeStr = '剧集';
                } else {
                    $typeStr = '电影';
                    $libraryArray = Store::getMovieLibrary();
                }
                if (empty($libraryArray)) {
                    $this->json(['code' => 401,'msg' => '没有添加' . $typeStr . '库']);
                }

                $message = '';
                $haveError = false;
                foreach ($libraryArray as $moviePath) {
                    $result = $taskObj->addTask($targetType,$moviePath);
                    if ($result) {
                        $message .= $typeStr . '库[' . $moviePath . ']添加成功';
                    } else {
                        $haveError = true;
                        $message .= $typeStr . '库[' . $moviePath . ']添加失败：' . $taskObj->getMessage();
                    }
                }
                if ($haveError) {
                    $this->json(['code' => 402,'msg' => $message]);
                }
                $this->json(['code' => 0]);
                break;
            case 'movie':
                if (!isset($this->params['id'])) {
                    $this->json(['code' => 403,'msg' => '缺少电影ID']);
                }
                $movieObj = new \EasySub\Video\Movie();
                $movieRow = $movieObj->getMovie((int)$this->params['id']);
                if (!$movieRow) {
                    $this->json(['code' => 404,'msg' => '电影不存在，ID：' . $this->params['id']]);
                }
                $result = $taskObj->addTask('movie',$movieRow->file_path);
                if ($result) {
                    $this->json(['code' => 0]);
                }
                $this->json(['code' => 405,'msg' => $taskObj->getMessage()]);
                break;
            case 'tv':
                if (!isset($this->params['id'])) {
                    $this->json(['code' => 403,'msg' => '缺少剧集季ID']);
                }

                $tvRow = $tv->getTv((int)$this->params['id']);
                if (!$tvRow) {
                    $this->json(['code' => 404,'msg' => '剧集不存在，剧ID：' . $this->params['id']]);
                }
                $result = $taskObj->addTask('tv',$tvRow->tv_path);
                if ($result) {
                    $this->json(['code' => 0]);
                }
                $this->json(['code' => 405,'msg' => $taskObj->getMessage()]);
                break;
            case 'season':
                if (!isset($this->params['id'])) {
                    $this->json(['code' => 403,'msg' => '缺少剧集季ID']);
                }

                $seasonRow = $tv->getSeason((int)$this->params['id']);
                if (!$seasonRow) {
                    $this->json(['code' => 404,'msg' => '剧集不存在，季ID：' . $this->params['id']]);
                }
                $result = $taskObj->addTask('tv',$seasonRow->season_path);
                if ($result) {
                    $this->json(['code' => 0]);
                }
                $this->json(['code' => 405,'msg' => $taskObj->getMessage()]);
                break;
            case 'episode':
                if (!isset($this->params['id'])) {
                    $this->json(['code' => 403,'msg' => '缺少剧集集ID']);
                }
                $episodeRow = $tv->getEpisode((int)$this->params['id']);
                if (!$episodeRow) {
                    $this->json(['code' => 404,'msg' => '剧集视频不存在，集ID：' . $this->params['id']]);
                }
                $result = $taskObj->addTask('tv',$episodeRow->file_path);
                if ($result) {
                    $this->json(['code' => 0]);
                }
                $this->json(['code' => 405,'msg' => $taskObj->getMessage()]);
                break;
            default:
                $this->json(['code' => 500,'msg' => '[' . $target . ']类型不支持']);
        }
        $this->closeView();
    }
}
