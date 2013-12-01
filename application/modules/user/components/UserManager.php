<?php
/**
 * @name UserManager.php
 * @author lancelot <lancelot1215@gmail.com>
 * Date 2013-11-23 
 * Encoding UTF-8
 */
class UserManager extends CApplicationComponent{
	public $cookieTimeout = 0;
	/**
	 * 
	 * @param array $regInfo
	 * @return boolean|RegisterForm
	 */
	public function register($regInfo,$scenario='reg'){
		$form = new RegisterForm();

		if ( $regInfo === null ){
			return $form;
		}
		$form->setScenario($scenario);
		
		$form->attributes = $regInfo;
		if ( $form->validate() ){
			$result = $form->save();
			if ( $result === true ){
				return true;
			}else {
				foreach ( $result->getErrors() as $attribute => $errors ){
					foreach ( $errors as $error ){
						$form->addError($attribute, $error);
					}
				}
			}
		}
		
		return $form;
	}
	

	public function login($info){
		$login = new LoginForm();

		if ( $info !== null ){
			$login->attributes = $info;
			if ( $login->rememberMe !== 'on' ){
				$this->cookieTimeout = 0;
			}
			if($login->validate() && $login->run($this->cookieTimeout)){
				return true;
			}
		}
		
		return $login;
	}

	
	public function getUserInfo($uid,$condition='',$params=array()){
		return FrontUser::model()->with('baseUser')->findByPk($uid,$condition,$params);
	}
}