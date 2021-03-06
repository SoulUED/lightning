<?php

/**
 * ContactForm class.
 * ContactForm is the data structure for keeping
 * contact form data. It is used by the 'contact' action of 'SiteController'.
 */
class RegisterForm extends CFormModel
{
	public $nickname;
	public $email;
	public $mobile;
	public $password;
	public $confirm;
	public $code;
	public $protocal;

	/**
	 * Declares the validation rules.
	 */
	public function rules()
	{
		$rules = array(
			// name, email, subject and body are required
			array('nickname, email, mobile, password', 'required','message'=>'请填写{attribute}'),
			array('confirm','required','message'=>'请重复输入一次密码'),
			// email has to be a valid email address
			array('email', 'email','message'=>'{attribute}格式不正确'),
			array('mobile', 'checkmobile'),
			array('password','confirmPassword'),
			// verifyCode needs to be entered correctly
			//'captcha' => array('code', 'captcha' ,'allowEmpty'=>!CCaptcha::checkRequirements(),'message'=>'验证码错误'),
			'captcha' => array('code', 'verifyMobileCode'),
			'protocal' => array('protocal','confirm','message'=>'请同意网站服务协议'),
		);
		return $rules;
	}
	
	public function attributeLabels(){
		return array(
				'nickname' => '昵称',
				'email' => '邮箱地址',
				'mobile' => '手机号码',
				'password' => '密码',
				'code' => '验证码'
		);
	}
	
	public function save(){
		$db = new FrontUser();
		$db->attributes = array(
			'nickname' => $this->nickname,
			'email' => $this->email,
			'mobile' => $this->mobile,
			'password' => $this->password,
			'user_type' => 'frontuser'
		);
		if ( $db->save() === true ){
			return true;
		}else {
			return $db;
		}
	}
	
	public function confirm($attribute,$params){
		if ( $this->protocal !== 'on' ){
			$this->addError('protocal',$params['message']);
		}
	}
	
	public function confirmPassword($attribute,$params){
		if(!$this->hasErrors()){
			if($this->password != $this->confirm){
				$this->addError('confirm', '两次输入的密码不一致');
			}
		}
	}
	
	public function checkmobile($attribute,$params){
		if(!$this->hasErrors()){
			if(!preg_match('#^13\d{9}|14[57]\d{8}|15[012356789]\d{8}|18[01256789]\d{8}$#', $this->mobile)){
				$this->addError('mobile', '手机号码格式不正确');
			}
		}
	}
	
	public function verifyMobileCode($attribute,$params){
		$notify = Yii::app()->getModule('notify')->getComponent('notifyManager');
		if ( $notify->applyMobileCodeVerify($this->mobile,$this->code) === false ){
			$this->addError('code','验证码错误');
		}
	}
}