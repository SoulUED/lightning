<?php
/**
 * @name FrontUserController.php
 * @author lancelot <lancelot1215@gmail.com>
 * Date 2013-12-10 
 * Encoding UTF-8
 */
class FrontUserController extends Admin{
	public function actionView(){
		$criteria = new CDbCriteria();
		$this->app->getModule('user');//加载模块model
		
		$selector = Selector::load('FrontUserSelector',$this->getQuery('FrontUserSelector'),$criteria);
		
		$dataProvider = new CActiveDataProvider('FrontUser',array(
				'criteria' => $criteria,
				'countCriteria' => array(
						'condition' => $criteria->condition,
						'params' => $criteria->params
				),
				'pagination' => array(
						'pageSize' => 15
				),
		));
		
		$this->tabTitle = '用户列表';
		$this->addNotifications('搜索','information',true);
		$this->render('view',array('dataProvider'=>$dataProvider,'selector'=>$selector));
	}
}