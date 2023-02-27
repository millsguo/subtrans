<?php
/*
 * Copyright (c) 2022. millsguo
 * 项目名称：subtrans
 * 文件名称：IndexController.php
 * 修改时间：2022/10/28 上午1:06
 * 作者：millsguo
 */

use Intervention\Image\ImageManager;
use Intervention\Image\ImageManagerStatic;

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
        $this->quickRedirect('图片打开错误', '/error/error/', 'warning');
    }

    /**
     * 显示图片
     * @return void
     */
    public function showAction(): void
    {
        if (isset($this->params['code'])) {
            $filePath = base64_decode($this->params['code']);
        } else {
            die('图片访问参数错误');
        }
        if (!str_starts_with($filePath, '/data/')) {
            die('仅允许访问挂载目录');
        }

        if (!is_readable($filePath)) {
            $message = '[' . $filePath . ']图片不存在';
            $width = $this->params['w'] ?? '200';
            $height = $this->params['h'] ?? '120';
            echo $this->showPlaceHolder($message, $width, $height);
        } else {
            $manager = $this->initImage();
            $image = $manager->make($filePath);
            if (isset($this->params['w'], $this->params['h'])) {
                $width = (int)$this->params['w'];
                $height = (int)$this->params['h'];
                $image->fit($width, $height,function($constraint) {
                    $constraint->upsize();
                });
            }
            echo $image->response('jpg', 90);
        }
    }

    /**
     * 生成占位图片
     * @param string $message
     * @param int $width
     * @param int $height
     * @return mixed
     */
    protected function showPlaceHolder(string $message, int $width, int $height): mixed
    {
        $manager = $this->initImage();
        $image = $manager->canvas($width, $height, '#CCCCCC');
        $image->text($message, (int)($width / 2), (int)($height / 2), function ($font) {
            $font->align('center');
            $font->valign('middle');
            $font->color('#fdf6e3');
        });
        return $image->response('jpg');
    }

    /**
     * 图片初始化
     * @return ImageManager
     */
    protected function initImage(): ImageManager
    {
        if (extension_loaded('imagick')) {
            $manager = ImageManagerStatic::configure(['driver' => 'imagick']);
        } elseif(extension_loaded('gd')) {
            $manager = ImageManagerStatic::configure(['driver' => 'gd']);
        } else {
            throw new \runtimeException('没有GD或imagick库');
        }
        return $manager;
    }
}
