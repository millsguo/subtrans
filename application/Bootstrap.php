<?php
/*
 * Copyright (c) 2022. 长沙用车无忧网络科技有限公司
 * 项目名称：yundianyi2-php74
 * 文件名称：Bootstrap.php
 * 修改时间：2022/10/28 上午1:06
 * 作者：millsguo
 */

use EasySub\Tools\Log;

/**
 * 应用引导文件
 */
class Bootstrap extends Zend_Application_Bootstrap_Bootstrap
{
    protected array $moduleNameArray;

    /**
     * 设置时区和编码
     */
    public function _initSetTimeZone(): void
    {
        date_default_timezone_set('Asia/Shanghai');
        if (extension_loaded('mbstring')) {
            mb_internal_encoding("UTF-8");
        }

        if (extension_loaded('bcmath')) {
            bcscale(2);
        }
    }

    /**
     * 日志初始化
     * @return void
     */
    public function _initLog(): void
    {
        Log::init(BASE_APP_PATH . '/logs/web.log');
    }

    /**
     * 设置自动加载
     */
    public function _initAutoload(): void
    {
        //默认模块
        $this->setAutoloader('default');
    }

    /**
     * 处理自动加载类
     *
     * @param $moduleName
     */
    protected function setAutoloader($moduleName): void
    {
        $moduleName = strtolower($moduleName);
        $nameSpace = ucfirst($moduleName);
        $autoLoader = new Zend_Application_Module_Autoloader([
            'namespace' => $nameSpace,
            'basePath' => dirname(__FILE__ . '/modules/' . $moduleName)
        ]);
    }

    /**
     * 数据库连接初始化
     *
     * @param bool $enableDbDebug
     * @return mixed
     */
    public function _initDb(bool $enableDbDebug = false): mixed
    {
        if (Zend_Registry::isRegistered('db')) {
            try {
                return Zend_Registry::get('db');
            } catch (Zend_Exception $e) {
                Log::info($e->getMessage());
            }
        }
        //初始化Sqlite
        $db = new EasySub\Tools\Db(['dbname' => APPLICATION_PATH . '/database/subtrans'], 'sqlite');

        Zend_Registry::set('db', $db);

        return $db;
    }

    /**
     * SESSION初始化
     */
    public function _initSession(): false|Zend_Session_Namespace
    {
        $currentDomain = $this->_getCurrentDomain();

        $options = $this->getOptions();
        if (isset($options['resources']['session'])) {
            $options = $options['resources']['session'];

            $options['cookie_domain'] = $currentDomain;
        }
        try {
            Zend_Session::setOptions($options);
            Zend_Session::start();
            $session = new Zend_Session_Namespace('subtrans');

            if (!isset($session->initialized)) {
                Zend_Session::regenerateId();
            }
            Zend_Registry::set('session', $session);

            return $session;
        } catch (Zend_Session_Exception $e) {
            Log::info($e->getMessage());
            return false;
        }
    }

    /**
     * 文档头初始化
     *
     * @throws Zend_Application_Bootstrap_Exception
     */
    protected function _initDoctype(): void
    {
        $this->bootstrap('view');
        $view = $this->getResource('view');
        $view->doctype('HTML5');
    }

    /**
     * 获取当前域名
     *
     * @return bool|string
     */
    private function _getCurrentDomain(): bool|string
    {
        try {
            if (Zend_Registry::isRegistered('domain')) {
                $currentDomain = Zend_Registry::get('domain');
                Log::log($currentDomain);
                return $currentDomain;
            }
            $currentDomain = $_SERVER['SERVER_NAME'];
            //保存当前注册使用的域名 xxx.com 格式
            Zend_Registry::set('domain', $currentDomain);
            return $currentDomain;
        } catch (Zend_Application_Bootstrap_Exception|Zend_Config_Exception|Zend_Exception $e) {
            return false;
        }
    }

    /**
     * 路由初始化
     */
    protected function _initRoute(): void
    {
    }

    /**
     * 设置模块支持
     *
     * @param object $routeObj 路由对象
     * @param string $domain 匹配域名
     * @param string $moduleName 模块名称
     */
    protected function addModuleSupport(mixed $routeObj,string $domain,string $moduleName): void
    {
        $hostNameRouteObj = new Zend_Controller_Router_Route_Hostname(
            $domain,
            [
                'module' => $moduleName
            ]
        );
        $pathObj = new Zend_Controller_Router_Route_Module(
            [
                'module' => $moduleName,
                'controller' => 'index',
                'action' => 'index'
            ]
        );
        $i = 1;
        while (isset($this->moduleNameArray[$moduleName])) {
            $moduleName .= $i;
            $i++;
        }
        $this->moduleNameArray[$moduleName] = $i;
        $routeObj->addRoute($moduleName, $hostNameRouteObj->chain($pathObj));
    }

    /**
     * 多模块初始化
     */
    public function _initModule(): void
    {
//        $frontController = Zend_Controller_Front::getInstance();
//        if ($frontController === null) {
//            return;
//        }
//        $frontController->registerPlugin(new \EasySub\Tools\Module());
    }

    /**
     * 邮件初始化
     */
    public function _initMail(): void
    {
        $this->_initLog();
        try {
            $config = \EasySub\Tools\Config::getConfig(BASE_APP_PATH . '/config/config.ini');

            if (isset($config->mail->smtp->username,$config->mail->smtp->password)) {
                $config = Zend_Registry::get('config');
                $configParam = [
                    'ssl' => 'ssl',
                    'auth' => 'login',
                    'username' => $config->mail->smtp->username,
                    'password' => $config->mail->smtp->password
                ];
                $transport = new Zend_Mail_Transport_Smtp($config->mail->smtp->server, $configParam);
                Zend_Mail::setDefaultTransport($transport);
            }
        } catch (Zend_Config_Exception|Zend_Exception $e) {
            Log::info($e->getMessage());
        }
    }
}
