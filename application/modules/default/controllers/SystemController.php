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
            if (isset($this->params['debug']) && $this->params['debug'] === 'true') {
                $debug = true;
            } else {
                $debug = false;
            }
            $configIni->translation->debug = $debug;
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
                if ($this->params['aliyun_1_enable_pay'] === 'true') {
                    $enablePay1 = true;
                } else {
                    $enablePay1 = false;
                }
                $configIni->translation->aliyun1->access_key = $this->params['aliyun_1_access_key'];
                $configIni->translation->aliyun1->access_secret = $this->params['aliyun_1_access_secret'];
                $configIni->translation->aliyun1->use_pro = $usePro1;
                $configIni->translation->aliyun1->enable_pay = $enablePay1;
            }
            if (isset($this->params['aliyun_2_access_key'],$this->params['aliyun_2_access_secret'],$this->params['aliyun_2_use_pro'])) {
                if ($this->params['aliyun_2_use_pro'] === 'true') {
                    $usePro2 = true;
                } else {
                    $usePro2 = false;
                }
                if ($this->params['aliyun_2_enable_pay'] === 'true') {
                    $enablePay2 = true;
                } else {
                    $enablePay2 = false;
                }
                $configIni->translation->aliyun2->access_key = $this->params['aliyun_2_access_key'];
                $configIni->translation->aliyun2->access_secret = $this->params['aliyun_2_access_secret'];
                $configIni->translation->aliyun2->use_pro = $usePro2;
                $configIni->translation->aliyun2->enable_pay = $enablePay2;
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

    /**
     * 设置翻译接口状态
     * @return void
     */
    public function setapiAction(): void
    {
        if (!isset($this->params['id'],$this->params['state'])) {
            $this->quickRedirect('参数错误', '/','warning');
        }
        if ($this->params['state'] === '1') {
            $apiState = true;
        } else {
            $apiState = false;
        }
        $result = \EasySub\Translated\TransApi::limitApi($this->params['id'],$apiState);
        if ($result) {
            $this->quickRedirect('接口设置成功', '/');
        } else {
            $this->quickRedirect('接口设置失败','/','warning');
        }
    }
}
