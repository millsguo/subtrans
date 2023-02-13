<?php
/*
 * Copyright (c) 2022. MillsGuo
 * 项目名称：subtrans
 * 文件名称：Module.php
 * 修改时间：2022/10/28 上午1:15
 * 作者：millsguo
 */

namespace EasySub\Tools;

use Zend_Application_Bootstrap_Bootstrap;
use Zend_Application_Module_Bootstrap;
use Zend_Controller_Front;
use Zend_Controller_Plugin_Abstract;
use Zend_Controller_Request_Abstract;

class Module extends Zend_Controller_Plugin_Abstract
{
	/**@var string The Init Name aka initModule */
	private string $_moduleInitName = '';

	public function routeShutdown(Zend_Controller_Request_Abstract $request)
	{
		$activeModuleName = $request->getModuleName();
		$this->_moduleInitName = $activeModuleName."Init";

		$appBootstrap = $this->_getMainBootstrap();
		$activeModuleBootstrap = $this->_getActiveBootstrap($appBootstrap, $activeModuleName);
		$this->_processApplicationBootstrap($appBootstrap);
		if ($activeModuleBootstrap instanceof Zend_Application_Module_Bootstrap) {
			$this->_processActiveModuleBootstrap($activeModuleBootstrap);
		}
	}

	/*****************************************************************
	 * Gets the Main Boostrap Object
	*
	* @return Zend_Application_Bootstrap_Bootstrap Main Bootstrap
	*****************************************************************/
	private function _getMainBootstrap(): Zend_Application_Bootstrap_Bootstrap
    {
		$frontController = Zend_Controller_Front::getInstance();
        if ($frontController === null) {
            throw new \RuntimeException('获取前端实例失败');
        }
        return $frontController->getParam('bootstrap');
	}

    /*******************************************************************************
     * Gets the Current Active Module's Boostrap Object
     *
     * @param Zend_Application_Bootstrap_Bootstrap $appBootstrap The Main Bootstrap
     * @param String $activeModuleName The name to find.
     * @return Zend_Application_Module_Bootstrap|Zend_Application_Bootstrap_Bootstrap Active Module Bootstrap
     ****************************************************************************
     */
	private function _getActiveBootstrap(Zend_Application_Bootstrap_Bootstrap $appBootstrap, string $activeModuleName): Zend_Application_Module_Bootstrap|Zend_Application_Bootstrap_Bootstrap
    {
	    if (empty($activeModuleName))
	    {
	        return $appBootstrap;
	    }
		$moduleList = $appBootstrap->modules;
        return $moduleList[$activeModuleName] ?? $appBootstrap;
	}

	/*********************************************************
	 * Process the methods from within the main bootstrap
	* @param Zend_Application_Bootstrap_Bootstrap $appBootstrap The Application Bootstrap;
	**********************************************************/
	private function _processApplicationBootstrap(Zend_Application_Bootstrap_Bootstrap $appBootstrap)
	{
		$moduleInitNameLength = strlen($this->_moduleInitName);
		$bootstrapMethodNames = get_class_methods($appBootstrap);
		foreach ($bootstrapMethodNames as $key=>$method) {
			if ($this->_isModuleNameInitMethod($method)) {
//				$resource = call_user_func(array($appBootstrap, $method));
//				$resourceName = substr($method, $moduleInitNameLength);
				//if (!is_null($resource)) {
					//$this->storeResource($resource, $resourceName, $appBootstrap);
				//}
			}
			unset($key);
		}
	}

	/*********************************************************
	 * Process the methods from within the main bootstrap
	* @param Zend_Application_Module_Bootstrap $activeModuleBootstrap The "Active"  Modules's Bootstrap;
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
	private function _isActiveInitMethod(string $method): bool
    {
		$methodNameLength = strlen($method);
		$methodNameLonger = ($methodNameLength > 10);
		$methodNameBeginMatch = str_starts_with($method, 'activeInit');
		return $methodNameLonger && $methodNameBeginMatch;
	}

	/***********************************
	 * Store the resource returned by the function so that it can be "bootstrapped"
	* @param misc $resource The Resource to be stored
	* @param string $name the name of the resource
	* @param Zend_Application_Bootstrap_Bootstrap $bootstrap The Bootstrap against which to store the resource
	********************/
	private function storeResource($resource, $name, $bootstrap)
	{
	}
}