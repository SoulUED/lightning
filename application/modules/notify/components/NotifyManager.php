<?php
/**
 * @name NotifyManager.php
 * @author lancelot <lancelot1215@gmail.com>
 * Date 2013-11-26 
 * Encoding UTF-8
 */
class NotifyManager extends CApplicationComponent{
	public $smsAPI;//第三方短信接口地址
	public $cacheID;
	public $mobileCodePrefix = 'MOBILE_CODE_';
	
	public function init(){
		$this->attachBehavior('curl','CurlBehavior');
		$this->attachBehavior('curlMulti','CurlMultiBehavior');
	}
	
	public function getSettingProvider($config){
		return new CActiveDataProvider('NotificationSettings',$config);
	}
	
	public function getSettingProviderByEventName($name,$config=array(),$enablePagination=false,$enableSort=true){
		Utils::resovleProviderConfigCriteria($config,$enablePagination,$enableSort);
		$criteria = $config['criteria'];
		$criteria->condition = 'event=:eventName';
		$criteria->params = array(
				':eventName' => $name
		);
		
		return new CActiveDataProvider('NotificationSettings',$config);
	}
	
	public function getSettingProviderByType($type,$config){
		Utils::resovleProviderConfigCriteria($config);
		$criteria = $config['criteria'];
		$countCriteria = $config['countCriteria'];
		
		$criteria->addCondition('notify_type=:type');
		$criteria->params[':type'] = $type;
		$countCriteria->addCondition('notify_type=:type');
		$countCriteria->params[':type'] = $criteria->params[':type'];
		
		return new CActiveDataProvider('NotificationSettings',$config);
	}
	
	public function update($id,$attributes){
		return NotificationSettings::model()->updateByPk($id, $attributes);
	}
	
	public function save($attributes){
		$model = new NotificationSettings();
		$model->attributes = $attributes;
		if ( $model->validate() ){
			return $model->save(false);
		}else {
			return $model;
		}
	}
	
	public function getInstance($id){
		return NotificationSettings::model()->findByPk($id);
	}
	
	/**
	 * 发送短信
	 * @param string $target
	 * @param string $content
	 * @return boolean
	 */
	public function sendSms($target,$content){
		$curl = $this->curl;
		$curl->setMethod('GET');
		$content = iconv('UTF-8','GB2312',$content);
		$url = $this->smsAPI.'&mobile='.$target.'&content='.$content;
		$curl->curlSend($url);
		if ( $curl->getHasError() ){
			$error = $curl->getError();
			$curl->reset(true);
			return $error;
		}else {
			$curl->reset(true);
			return true;
		}
	}
	
	/**
	 * 
	 * @param int $length
	 * @return string
	 */
	public function generateRandomCode($length=4){
		$letters = 'bcdfghjklmnpqrstvwxyz';
		$vowels = 'aeiou';
		$code = '';
		for($i = 0; $i < $length; ++$i)
		{
			if($i % 2 && mt_rand(0,10) > 2 || !($i % 2) && mt_rand(0,10) > 9)
				$code .= $vowels[mt_rand(0,4)];
			else
				$code .= $letters[mt_rand(0,20)];
		}
		
		return $code;
	}
	
	/**
	 * 验证手机号码格式
	 * @param string $mobile
	 * @return boolean
	 */
	public function checkMobileFormat($mobile){
		return 0 !== preg_match('#^13\d{9}|14[57]\d{8}|15[012356789]\d{8}|18[01256789]\d{8}$#', $mobile);
	}
	
	/**
	 * 生成并保存手机验证码
	 * @param string $mobile
	 * @param int $duration 有效时间，单位为秒，默认为半小时
	 * @param int $length 验证码长度
	 */
	public function setMobileVerifyCode($mobile,$duration=1800,$length=4){
		$cache = Yii::app()->getComponent($this->cacheID);
		if ( $cache === null ){
			return false;
		}
		
		$code = $this->generateRandomCode($length);
		$cache->set($this->mobileCodePrefix.$mobile,$code,$duration);
		
		return $code;
	}
	
	/**
	 * 验证手机验证码是否正确
	 * @param string $mobile
	 * @param int $code
	 * @return boolean
	 */
	public function applyMobileCodeVerify($mobile,$code){
		$cache = Yii::app()->getComponent($this->cacheID);
		if ( $cache === null ){
			return false;
		}
		
		$cachedCode = $cache->get($this->mobileCodePrefix.$mobile);
		
		return $cachedCode !== false && $cachedCode === $code;
	}
}