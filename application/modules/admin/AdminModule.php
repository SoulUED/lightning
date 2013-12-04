<?php

class AdminModule extends CWebModule
{
	public function init()
	{
		
		// this method is called when the module is being created
		// you may place code here to customize the module or the application
		$this->defaultController = "index";
		// import the module-level models and components
		$this->setImport(array(
			'admin.components.*',
			'admin.models.*',
			'admin.models.forms.*',
		));
		
		Yii::app()->setComponents(array(
			'errorHandler' => array(
				'errorAction' => 'admin/index/error',
			),
		));
		
	}

	public function beforeControllerAction($controller, $action)
	{
		if(parent::beforeControllerAction($controller, $action))
		{
			// this method is called before any module controller action is performed
			// you may place customized code here
			return true;
		}
		else
			return false;
	}
}