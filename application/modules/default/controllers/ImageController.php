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
        if (!isset($this->params['dir'],$this->params['filename'])) {
            $this->quickRedirect('图片访问参数错误','/error/error/','warning');
        }
        $filePath = urldecode($this->params['dir']) . urldecode($this->params['filename']);
        if (!str_starts_with($filePath,'/data/')){
            $this->quickRedirect('仅允许访问挂载目录','/error/error/','danger');
        }
        if (!is_readable($filePath)) {
            $this->quickRedirect('图片不存在','/error/error/','warning');
        }
        header("Content-type: image/jpg");
        echo file_get_contents($filePath);

//        $imageObj = imagecreatefromjpeg($filePath);
//        echo imagejpeg($imageObj);
    }
}
