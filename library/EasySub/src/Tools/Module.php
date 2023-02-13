<?php
/*
 * Copyright (c) 2022. 长沙用车无忧网络科技有限公司
 * 项目名称：yundianyi2-php74
 * 文件名称：Config.php
 * 修改时间：2022/10/28 上午1:15
 * 作者：millsguo
 */

namespace EasySub\Tools;

class Module extends \Zend_Controller_Plugin_Abstract
{
    /**@var string The Init Name aka initModule */
    private $_moduleInitName = '';

    public function routeShutdown(\Zend_Controller_Request_Abstract $request)
    {
        $activeModuleName = $request->getModuleName();
        $this->_moduleInitName = $activeModuleName."Init";

        $appBootstrap = $this->_getMainBootstrap();
        $activeModuleBootstrap = $this->_getActiveBootstrap($appBootstrap, $activeModuleName);
        $this->_processApplicationBootstrap($appBootstrap);
        if ($activeModuleBootstrap instanceof \Zend_Application_Module_Bootstrap) {
            $this->_processActiveModuleBootstrap($activeModuleBootstrap);
        }
    }

    /*****************************************************************
     * Gets the Main Boostrap Object
     *
     * @return \Zend_Application_Bootstrap_Bootstrap Main Bootstrap
     *****************************************************************/
    private function _getMainBootstrap()
    {
        $frontController = \Zend_Controller_Front::getInstance();
        $bootstrap =  $frontController->getParam('bootstrap');
        return $bootstrap;
    }

    /*******************************************************************************
     * Gets the Current Active Module's Boostrap Object
     *
     * @param \Zend_Application_Bootstrap_Bootstrap $appBootstrap The Main Bootstrap
     * @param String $activeModuleName The name to find.
     * @return \Zend_Application_Module_Bootstrap Active Module Bootstrap
     ******************************************************************************/
    private function _getActiveBootstrap($appBootstrap, $activeModuleName)
    {
        if (empty($activeModuleName))
        {
            return $appBootstrap;
        }
        $moduleList = $appBootstrap->modules;
        if (isset($moduleList[$activeModuleName])) {
            $activeModule = $moduleList[$activeModuleName];
        } else {
            $activeModule = $appBootstrap;
        }
        return $activeModule;
    }

    /*********************************************************
     * Process the methods from within the main bootstrap
     * @param \Zend_Application_Bootstrap_BootstrapAbstract $appBootstrap The Application Bootstrap;
     **********************************************************/
    private function _processApplicationBootstrap($appBootstrap)
    {
        $moduleInitNameLength = strlen($this->_moduleInitName);
        $bootstrapMethodNames = get_class_methods($appBootstrap);
        foreach ($bootstrapMethodNames as $key=>$method) {
// 			$runMethod = false;
// 			$methodNameLength = strlen($method);
            if ($this->_isModuleNameInitMethod($method)) {
                $resource = call_user_func(array($appBootstrap, $method));
                $resourceName = substr($method, $moduleInitNameLength);
                if (!is_null($resource)) {
                    $this->storeResource($resource, $resourceName, $appBootstrap);
                }
            }
            unset($key);
        }
    }

    /*********************************************************
     * Process the methods from within the main bootstrap
     * @param \Zend_Application_Module_Bootstrap $activeModuleBootstrap The "Active"  Modules's Bootstrap;
     **********************************************************/
    private function _processActiveModuleBootstrap($activeModuleBootstrap)
    {
        $moduleInitNameLength = strlen($this->_moduleInitName);
        $methodNames = get_class_methods($activeModuleBootstrap);
        foreach ($methodNames as $key=>$method) {
            $runMethod = false;
            if ($this->_isActiveInitMethod($method)) {
                $resourceName = substr($method, 10);
                $runMethod = true;
            } elseif ($this->_isModuleNameInitMethod($method)) {
                $resourceName = substr($method, $moduleInitNameLength);
                $runMethod = true;
            }
            if ($runMethod) {
                $resource = call_user_func(array($activeModuleBootstrap, $method));
                if (!is_null($resource)) {
                    $this->storeResource($resource, $resourceName, $activeModuleBootstrap);
                }
            }
            unset($key);
        }
    }

    /*******************************************************
     * Check to see if the method is in style of ModulenameInit
     * @param string $method The method name to check
     ********************************************************/
    private function _isModuleNameInitMethod($method)
    {
        $methodNameLength = strlen($method);
        $moduleInitNameLength = strlen($this->_moduleInitName);
        $methodNameLonger = ($moduleInitNameLength < $methodNameLength);
        $methodNameBeginMatch = $this->_moduleInitName == substr($method, 0, $moduleInitNameLength);
        return $methodNameLonger && $methodNameBeginMatch;
    }

    /*******************************************************
     * Check to see if the method is in style of activeInit
     * @param string $method The method name to check
     ********************************************************/
    private function _isActiveInitMethod($method)
    {
        $methodNameLength = strlen($method);
        $methodNameLonger = ($methodNameLength > 10);
        $methodNameBeginMatch = 'activeInit' === substr($method, 0, 10);
        return $methodNameLonger && $methodNameBeginMatch;
    }

    /***********************************
     * Store the resource returned by the function so that it can be "bootstrapped"
     * @param misc $resource The Resource to be stored
     * @param string $name the name of the resource
     * @param \Zend_Application_Bootstrap_BootstrapAbstract $bootstrap The Bootstrap against which to store the resource
     ********************/
    private function storeResource($resource, $name, $bootstrap)
    {
        // Store the resource.. not sure how to do this yet.. if you do let me know! <img src="http://binarykitten.com/wp-includes/images/smilies/icon_biggrin.gif" alt=":D" class="wp-smiley">
    }
}