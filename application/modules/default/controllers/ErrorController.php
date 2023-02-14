<?php
/*
 * Copyright (c) 2022. 长沙用车无忧网络科技有限公司
 * 项目名称：yundianyi2-php74
 * 文件名称：ErrorController.php
 * 修改时间：2022/10/28 上午1:06
 * 作者：millsguo
 */

/**
 * 错误处理模块
 * @author MillsGuo
 *
 */
class ErrorController extends Zend_Controller_Action
{
    public function errorAction()
    {
        $errors = $this->getParam('error_handler');
        switch ($errors->type) {
            case Zend_Controller_Plugin_ErrorHandler::EXCEPTION_NO_ROUTE:
            case Zend_Controller_Plugin_ErrorHandler::EXCEPTION_NO_CONTROLLER:
            case Zend_Controller_Plugin_ErrorHandler::EXCEPTION_NO_ACTION:
                // 404 error -- controller or action not found
                $this->getResponse()->setHttpResponseCode(404);
                $level = 2;
                $this->view->message = '您访问的页面不存在';
                $errorPage = '404';
                break;
            default:
                // application error
                $this->getResponse()->setHttpResponseCode(500);
                $level = 1;
                $this->view->message = '内部错误';
                $errorPage = '500';
                break;
        }
        // conditionally display exceptions
        if ($this->getInvokeArg('displayExceptions')) {
            $this->view->showExceptions = true;
            $this->view->exception = $errors->exception;
            $this->view->request   = $errors->request;
        }
        $this->view->messageType = 'error';
        $params = $errors->request->getParams();

        $this->render($errorPage);
        $this->_helper->layout->setLayout('subtrans');
        if ($errorPage === '500') {
            $traceStr = $errors->exception->getTraceAsString();
            $traceStr .= print_r($params, true);
            \EasySub\Tools\Log::log($errors->exception->getMessage());
            \EasySub\Tools\Log::log($traceStr);
        }
    }
}
