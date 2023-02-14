<?php
/*
 * Copyright (c) 2022. millsguo
 * 项目名称：subtrans
 * 文件名称：IndexController.php
 * 修改时间：2022/10/28 上午1:06
 * 作者：millsguo
 */

use EasySub\Tools\Log;

/**
 * 后台管理首页
 * @author MillsGuo
 *
 */
class SystemController extends Default_Model_ControllerHelper
{
    public function action_init(): void
    {
    }
    
    /**
     * 转配置页
     */
    public function indexAction(): void
    {
        $this->redirect('/system/config/');
    }

    /**
     * 配置页
     * @return void
     */
    public function configAction(): void
    {
        $configFilePath = BASE_APP_PATH . '/config/config.ini';
        if (!is_readable($configFilePath)) {
            $initResult = \EasySub\Tools\Config::initConfigFile();
            if (!$initResult) {
                $this->quickRedirect(BASE_APP_PATH . '/config/config.ini配置文件不可写','/error/error/','danger');
            }
        }
        $configIni = \EasySub\Tools\Config::getConfig($configFilePath,'',true);
        if ($this->isPost()) {
            if (isset($this->params['enable_trans']) && $this->params['enable_trans'] === 'true') {
                $enableTrans = true;
            } else {
                $enableTrans = false;
            }
            $configIni->translation->enable_trans = $enableTrans;
            if (isset($this->params['aliyun_1_access_key'],$this->params['aliyun_1_access_secret'],$this->params['aliyun_1_use_pro'])) {
                if ($this->params['aliyun_1_use_pro'] === 'true') {
                    $usePro1 = true;
                } else {
                    $usePro1 = false;
                }
                $configIni->translation->aliyun1->access_key = $this->params['aliyun_1_access_key'];
                $configIni->translation->aliyun1->access_secret = $this->params['aliyun_1_access_secret'];
                $configIni->translation->aliyun1->use_pro = $usePro1;
            }
            if (isset($this->params['aliyun_2_access_key'],$this->params['aliyun_2_access_secret'],$this->params['aliyun_2_use_pro'])) {
                if ($this->params['aliyun_2_use_pro'] === 'true') {
                    $usePro2 = true;
                } else {
                    $usePro2 = false;
                }
                $configIni->translation->aliyun2->access_key = $this->params['aliyun_2_access_key'];
                $configIni->translation->aliyun2->access_secret = $this->params['aliyun_2_access_secret'];
                $configIni->translation->aliyun2->use_pro = $usePro2;
            }
            $writeObj = new \Zend_Config_Writer_Ini();
            $writeObj->setConfig($configIni);
            $writeObj->setFilename(BASE_APP_PATH . '/config/config.ini');
            try {
                $writeObj->write();
                $this->quickRedirect('设置保存成功','/system/config/');
            } catch (Zend_Config_Exception $e) {
                Log::info($e->getMessage());
                $this->quickRedirect($e->getMessage(),'/system/config/','warning');
            }
        }
        $this->view->config = $configIni;
    }
}
