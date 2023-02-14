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
    /**
     * @var array 所有传入的参数数组
     */
    protected array $params;
    /**
     * @var int
     */
    protected int $page;
    protected Zend_Controller_Action_Helper_Abstract $flashMessage;

    public function init(): void
    {
        $this->preInitView();
        $this->initSession();
        if ($this->getParam('controller') !== 'system') {
            $this->checkConfig();
        }

        if (method_exists($this,'action_init')) {
            $this->action_init();
        }
    }

    /**
     * 检查配置文件，不存在则转配置页
     * @return void
     */
    private function checkConfig(): void
    {
        if (!is_readable(BASE_APP_PATH . '/config/config.ini')) {
            $this->quickRedirect('请先配置系统', '/system/config/','warning');
        }
    }

    /**
     * 初始化视图
     */
    protected function preInitView(): void
    {
        $this->params = $this->getAllParams();
        $this->view->controller = $this->getParam('controller');
        $this->view->action = $this->getParam('action');
        $this->page = (int)$this->getParam('page');
        if ($this->page < 1) {
            $this->page = 1;
        }
        $this->view->page = $this->page;
        try {
            $this->initView();

            $this->flashMessage = $this->_helper->getHelper('FlashMessenger');
            $this->view->message = $this->flashMessage->getMessages();
        } catch (Zend_Controller_Exception $e) {
            \EasySub\Tools\Log::err($e->getMessage());
            \EasySub\Tools\Log::err($e->getTraceAsString());
        }

        $this->params = $this->getAllParams();
    }

    /**
     * 初始化Session
     */
    protected function initSession(): void
    {
        if (Zend_Registry::isRegistered('session')) {
            try {
                $this->session = Zend_Registry::get('session');
            } catch (Zend_Exception $e) {
                $this->quickRedirect($e->getMessage(), '/error/', 'warning');
            }
            $this->view->session = $this->session;
            if (isset($this->session->messageType)) {
                $this->view->messageType = $this->session->messageType;
            }
            if (isset($this->session->messageTitle)) {
                $this->view->messageTitle = $this->session->messageTitle;
            }
            if (isset($this->session->messageStr)) {
                $this->view->messageStr = $this->session->messageStr;
            }
        } else {
            $this->quickRedirect('SESSION未启用', '/error/', 'warning');
        }
    }

    /**
     * @param string $layoutName
     * @return void
     */
    protected function setLayout(string $layoutName): void
    {
        $this->_helper->layout->setLayout($layoutName);
    }

    /**
     * 关闭主模板
     */
    public function closeLayout(): void
    {
        $this->_helper->layout->disableLayout();
    }

    /**
     * 关闭视图
     */
    public function closeView(): void
    {
        $this->_helper->viewRenderer->setNoRender();
    }

    /**
     * 是否为AJAX访问
     * @return bool
     */
    public function isAjax(): bool
    {
        $request = $this->getRequest();
        if (!$request) {
            return false;
        }
        $isAjax = $request->isXmlHttpRequest();
        if ($isAjax) {
            $this->closeLayout();
            return true;
        }

        return false;
    }

    /**
     * 是否为Post访问
     *
     * @return bool
     */
    public function isPost(): bool
    {
        $request = $this->getRequest();
        if (!$request) {
            return false;
        }
        $isGet = $request->isPost();
        if ($isGet) {
            return true;
        }

        return false;
    }

    /**
     * 快速跳转
     * @param array|string $message
     * @param string $url
     * @param string $type
     *
     * @return void
     */
    public function quickRedirect(array|string $message, string $url, string $type = 'success'): void
    {
        if (isset($this->session)) {
            $this->session->messageType = $type;
            if (is_array($message)) {
                if (isset($message['title'])) {
                    $this->session->messageTitle = $message['title'];
                } else {
                    $this->session->messageTitle = '';
                }
                if (isset($message['message'])) {
                    $this->flashMessage->addMessage($message['message']);
                    $this->session->messageStr = $message['message'];
                }
            } else {
                $this->flashMessage->addMessage($message);
                $this->session->messageStr = $message;
            }
        }
        $this->redirect($url);
    }
}
