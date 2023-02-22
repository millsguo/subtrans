<?php
/*
 * Copyright (c) 2022. millsguo
 * 项目名称：subtrans
 * 文件名称：IndexController.php
 * 修改时间：2022/10/28 上午1:06
 * 作者：millsguo
 */

/**
 * 图片显示
 * @author MillsGuo
 *
 */
class ImageController extends Default_Model_ControllerHelper
{
    public function action_init(): void
    {
        $this->closeLayout();
        $this->closeView();
    }
    
    /**
     * 转配置页
     */
    public function indexAction(): void
    {
        $this->quickRedirect('图片打开错误','/error/error/','warning');
    }

    /**
     * 显示图片
     * @return void
     */
    public function showAction(): void
    {
        if (isset($this->params['code'])) {
            $filePath = base64_decode($this->params['code']);
        } elseif (isset($this->params['dir'],$this->params['filename'])) {
            $filePath = urldecode($this->params['dir']) . urldecode($this->params['filename']);
        } else {
            die('图片访问参数错误');
        }
        if (!str_starts_with($filePath,'/data/')){
            die('仅允许访问挂载目录');
        }
        if (isset($this->params['trans']) && $this->params['trans'] === 'open') {
            $filePath = str_replace(array(' ', '(', ')'), array('\ ', '\(', '\)'), $filePath);
        }
        if (!is_readable($filePath)) {
            $dirArray = scandir('/data/movies-1');
            echo print_r($dirArray,true);
            $fileArray = scandir(dirname($filePath));
            echo print_r($fileArray,true);
            die('[' . $filePath . ']图片不存在');
        }
        header("Content-type: image/jpg");
        echo file_get_contents($filePath);
    }
}
