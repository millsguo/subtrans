<?php
/*
 * Copyright (c) 2022. MillsGuo
 * 文件名称：ControllerHelper.php
 * 修改时间：2022/10/28 上午1:06
 * 作者：millsguo
 */

/**
 *
 * @author MillsGuo
 *
 */
class Default_Model_ControllerHelper extends Zend_Controller_Action
{
    public function module_init(): void
    {
        $this->setLayout('web');
    }

    /**
     * @param string $layoutName
     * @return void
     */
    public function setLayout(string $layoutName): void
    {
        $this->_helper->layout->setLayout($layoutName);
    }
}
