<?php
/*
**用户个人中心
design By HJtianling_LXY,<2507073658@qq.com>
2013.11.16
*/

class UserCenterController extends Controller{
	public $defaultAction = 'userInfo';
	public $userData;
	
	public function filters(){
		$filters = parent::filters();
		$filters[] = 'fetchUserData + userInfo,myLend,myBorrow,userSecurity,userFund';
		return $filters;
	}
	
	public function filterFetchUserData($filterChain){
		$uid = $this->app->user->id;
		$this->userData = $this->getModule()->getComponent('userManager')->getUserInfo($uid);
		$filterChain->run();
	}
	
	/*
	*个人信息获取
	*/
	public function actionUserInfo(){
		$this->pageTitle = '个人中心';
		$uid = $this->app->user->id;
		$userData = $this->userData;

		$role = $userData['role'];
		$creditData= $this->getUserCredit($role);
		$IconUrl = null;

		if(isset($_POST['FrontUser'])){
			$attributes = $_POST['FrontUser'];

			$userData->gender = $attributes['gender'];
			$userData->address = $attributes['address'];
			$userData->role = $attributes['role'];
			$userData->age = $attributes['age'];
			$userData->identity_id = $attributes['identity_id'];


			if($userData->save()){
				Yii::app()->user->setFlash('success','信息修改成功');
				$this->redirect(Yii::app()->createUrl('user/userCenter/userInfo'));
			}else{
				Yii::app()->user->setFlash('error','信息修改失败');
				$this->redirect(Yii::app()->createUrl('user/userCenter/userInfo'));
			}
		}
		
		$IconUrl = $this->user->getState('avatar');
		$this->render('userInfo',array('userData'=>$userData,'creditData'=>$creditData,'model'=>new FrontCredit(),'IconUrl'=>$IconUrl));
		
	}

	/*
	**查询用户角色
	*/
	public function getUserRole($id){
		if(is_numeric($id)){
			$userData = $this->app->getModule('user')->getComponent('userManager')->getUserInfo($id);
			$role = $userData['role'];
			return $role;
		}
	}

	/*
	**获取用户信用信息
	*/
	public function getUserCredit($role){
		if(!empty($role)){
			$userCredit = CreditRole::model()->with('verification')->findAll('role =:role',array(':role'=>$role));
			foreach($userCredit as $value){
				$creditSetting[] = array(
						$value->getRelated('verification')
					);
			}
			
			return $creditSetting;
		}
	}

	/*
	**添加信用项
	*/
	public function actionVerificationAdd($type){
		if(!empty($type)){
			$uid = $this->app->user->id;
			$role = $this->user->getState('role');
			//$role = $this->getUserRole($uid);
			$criteria = new CDbCriteria;
			$criteria->condition = 'verification_id = '.$type.'';
			$criteria->compare('role',$role);
			$verificationData = CreditRole::model()->with('verification')->findAll($criteria);

			if(empty($verificationData)){
				echo "用户角色与信用项目不符合";
				die();
			}
			$post = $this->getPost();

			if(!empty($post)){

				$model = new FrontCredit;
				$file=CUploadedFile::getInstance($model,'filename'); 

				$fileName = $file->getName();
				//$filenameUTF8 = iconv("gb2312","UTF-8",$fileName);
				$fileSize = $file->getSize();
				$fileType = pathinfo($fileName, PATHINFO_EXTENSION);
				//对上传文件类型进行审核
				$TypeVerify = $this->TypeVerify($fileType);

				if($TypeVerify == 400){
					echo "文件不合法";
					die();
				}else{
					$uploadDir = dirname(Yii::app()->basePath).DS.$this->app->getPath('creditFile').
					                    $this->app->partition($uid,'creditFile');

					if(!is_dir($uploadDir)){ //若目标目录不存在，则生成该目录
						mkdir($uploadDir,0077,true);
					}

					$randName = Tool::getRandName();//获取一个随机名
					$newName = "Credit".$randName.".".$fileName;//对文件进行重命名

					$saveUrl = $uploadDir.$newName;
					$isUp = $file->saveAs($saveUrl);//保存上传文件

					if($isUp){
						$model->user_id = Yii::app()->user->id;
						$model->verification_id = $type;
						$model->file_type = $TypeVerify;
						$model->content = $newName;
						$model->submit_time = time();
						$model->status = 0;

						if($model->save()){
							Yii::app()->user->setFlash('success','上传成功');
							$this->redirect(Yii::app()->createUrl('user/userCenter/userInfo'));

						}
						else
							var_dump($model->getErrors());
					}
				}
				


			}
		}
	}


	/*
	**验证附件类型
	*/
	public function TypeVerify($fileType){

		if(isset($fileType) && !empty($fileType)){
			$map = array(
					'file'=>array('pdf','zip','rar'),
					'image'=>array('jpg','jpeg','png','gif')
				);

			foreach($map as $type=>$typeData){
				foreach($typeData as $key=> $value){
					if(preg_match('/'.$value.'/',$fileType)){
						return $type; 
					}
				}
				
			}
			return 400;
		}
	}


	/*
	**安全中心
	*/
	public function actionUserSecurity(){
		$this->pageTitle = '闪电贷';
		$uid = Yii::app()->user->id;

		$userData = $this->userData;

		if(!empty($userData)){
			$IconUrl = Yii::app()->getModule('user')->userManager->getUserIcon($uid);
			$this->render('userSecurity',array('userData'=>$userData,'IconUrl'=>$IconUrl));
		}
	}

	
	/*
	**投资列表
	*/
	public function actionMyLend(){
		$this->pageTitle = '闪电贷';
		$uid = Yii::app()->user->id;
		$MyLend = array();
		$waitingForBuy = array();

		$criteria = new CDbCriteria;
		$criteria->alias = "meta";
		$criteria->condition = 'meta.user_id=:uid';
		$criteria->params = array(
				':uid' => $uid
		);

		$LendData = BidMeta::model()->with('bid','user')->findAll($criteria);

		if(!empty($LendData)){
			foreach($LendData as $value){
				$userData = $value->getRelated('user');
				$bidData = $value->getRelated('bid');
				if( $value->finish_time != 0){
					if($bidData->progress == 100){

					}else{
						$waitingForBuy[] = array(
							'nickname'=>$userData->nickname,
							'bidTitle'=>$bidData->title,
							'rate'=>$bidData->month_rate,
							'sum'=>$value->sum/100,
							'deadline'=>$bidData->deadline,
							'buyTime'=>date('Y:m:d H:i:s',$value->finish_time),

						);
					}

				}

			}
			
		}

		$IconUrl = Yii::app()->getModule('user')->userManager->getUserIcon($uid);
		$this->render('myLend',array('waitingForBuy'=>$waitingForBuy,'IconUrl'=>$IconUrl));
		
	}

	/*
	**我的借款
	*/
	public function actionMyBorrow(){
		$this->pageTitle = '闪电贷';
		$uid = Yii::app()->user->id;
		$waitingForPay = array();
		$waitingForBuy = array();
		$borrowSum = 0;

		$myBorrowData = $this->app->getModule('tender')->getComponent('bidManager')->getBidList('user_id =:uid',array(
			'uid'=>$uid));
		foreach($myBorrowData as $value){
			if($value->attributes['verify_progress'] == 1){
				if($value->attributes['progress'] == 100){
					$waitingForPay[] = array(
							$value->attributes
						);
					$borrowSum += $value->attributes['sum']/100;
				}else{
				$waitingForBuy[] = array(
							$value->attributes
						);
				}
			}
		}
		$IconUrl = Yii::app()->getModule('user')->userManager->getUserIcon($uid);
		$this->render('myBorrow',array(
									'waitingForPay'=>$waitingForPay,
									'waitingForBuy'=>$waitingForBuy,
									'borrowSum'=>$borrowSum,
									'IconUrl'=>$IconUrl
									));
	}

	/*
	**用户头像上传
	*/
	public function actionIconUpload(){
		if(isset($_FILES['Filedata'])){
			$uid = $this->app->user->id;
			$fileInfo = CUploadedFile::getInstanceByName('Filedata');
			$fileName = $fileInfo->name;
			$fileType =  $fileInfo->getExtensionName();

			$TypeVerify = $this->TypeVerify($fileType);
			if($TypeVerify !== 'image'){
				echo "上传文件不合法!";
				die();
			}
			$uploadDir = dirname(Yii::app()->basePath).DS.$this->app->getPath('avatar').
					                    $this->app->partition($uid,'avatar');

			if(!is_dir($uploadDir)){ //若目标目录不存在，则生成该目录
				mkdir($uploadDir,0077,true);
			}

			$randName = Tool::getRandName();//获取一个随机名
			$newName = md5('userIcon'.$randName.$fileName).'.'.$fileType;//对文件进行重命名
			$saveUrl = $uploadDir.$newName;
			$isUp = $fileInfo->saveAs($saveUrl);//保存上传文件

			if($isUp){
				$thumbUrl = Tool::getThumb($saveUrl,300,300,$saveUrl);//制作缩略图并放回缩略图存储路径

				$Icon = new FrontUserIcon();
				$Icon->user_id = $uid;
				$Icon->file_name = $newName;
				$Icon->size = '300*300';
				$Icon->file_size = $fileInfo->size;
				$Icon->in_using = 1;

				FrontUserIcon::model()->updateAll(array('in_using'=>0),'user_id=:uid',array(':uid'=>$uid));
				if($Icon->save()){
					Yii::app()->user->setFlash('success','上传成功');
					$this->user->setState('avatar',$this->app->getPartedUrl('avatar',$uid).$newName);
					$this->redirect(Yii::app()->createUrl('user/userCenter/userInfo'));
				}
			}
		}
	}


	/*
	**用户登录密码修改
	*/
	public function actionPasswordChange(){
		$post = $this->getPost();
		if(!empty($post)){
			$oldPassword = $post['oldPassword'];
			$newPassword = $post['newPassword'];
			$rePassword = $post['rePassword'];

			if($newPassword != $rePassword){
				Yii::app()->user->setFlash('error','新密码和重复密码不对应');
				$this->redirect(Yii::app()->createUrl('user/userCenter/userSecurity'));
				exit();
			}
			$uid = $this->user->id;
			$userData = FrontUser::model()->findByPk($uid);

			$security = Yii::app()->getSecurityManager();
			$passwordVerify = $security->verifyPassword($oldPassword,$userData->password);

			if($passwordVerify != true){
				Yii::app()->user->setFlash('error','密码验证失败');
				$this->redirect(Yii::app()->createUrl('user/userCenter/userSecurity'));
				exit();
			}else{
				$password = $security->generatePassword($newPassword);
				$userData->password = $password;
				if($userData->save()){
					Yii::app()->user->setFlash('success','密码修改成功');
					$this->redirect(Yii::app()->createUrl('user/userCenter/userSecurity'));
				}else
					var_dump($userData->getErrors());
			}	
		}
	}

	/*
	**支付密码设置
	*/
	public function actionPayPasswordCreate(){
		$post = $this->getPost();

		if(!empty($post)){
			$password = $post['password'];
			$rePassword = $post['rePassword'];

			if($password != $rePassword){
				Yii::app()->setFlash('error','密码和重复密码不对应');
				$this->redirect(Yii::app()->createUrl('user/userCenter/userSecurity'));
				exit();
			}

			$uid = $this->user->id;
			$userData = FrontUser::model()->findByPk($uid);

			if(!empty($userData)){
				$security = Yii::app()->getSecurityManager();
				$payPassword = $security->generatePassword($password);//调用加密组建对密码进行加密
				$userData->pay_password = $payPassword;

				if($userData->save()){
					Yii::app()->user->setFlash('success','支付密码设置成功');
					$this->redirect(Yii::app()->createUrl('user/userCenter/userSecurity'));
				}
			}
		}
	}


	public function actionUserFund(){
		$this->pageTitle = '闪电贷';
		$uid = $this->id;
		$userData = $this->userData;
		$IconUrl = Yii::app()->getModule('user')->userManager->getUserIcon($uid);
		
		$p2p = $this->app->getModule('pay')->fundManager->getP2pList(array(
			'condition' => '(from_user=:uid OR to_user=:uid) AND `time`>=:t',
			'order' => '`time` DESC',
			'params' => array(
				'uid' => $this->user->getId(),
				't' => mktime(0,0,0) - 2592000,
			)
		));
		
		$this->render('userFund',array(
			'userData'=>$userData,
			'IconUrl'=>$IconUrl,
			'p2p' => new CArrayDataProvider($p2p),
		));
	}
	
	public function actionAjaxFund(){
		$type = $this->getPost('type','p2p');
		$date = $this->getPost('date','1');
		
		if($type == 'p2p'){
			$p2p = $this->app->getModule('pay')->fundManager->getP2pList(array(
				'condition' => '(from_user=:uid OR to_user=:uid) AND `time`>=:t',
				'order' => '`time` DESC',
				'params' => array(
					'uid' => $this->user->getId(),
					't' => mktime(0,0,0) - $date * 2592000,
				)
			));
		}elseif($type == 'withdraw'){
			$p2p = $this->app->getModule('pay')->fundManager->getWithdrawList(array(
				'condition' => 'user_id=:uid AND raise_time>=:t',
				'order' => '`raise_time` DESC',
				'params' => array(
					'uid' => $this->user->getId(),
					't' => mktime(0,0,0) - $date * 2592000,
				)
			));
		}elseif($type == "recharge"){
			$p2p = $this->app->getModule('pay')->fundManager->getPayList(array(
				'condition' => 'user_id=:uid AND `raise_time`>=:t',
				'order' => '`raise_time` DESC',
				'params' => array(
					'uid' => $this->user->getId(),
					't' => mktime(0,0,0) - $date * 2592000,
				)
			));
		}

		$this->renderPartial("_p2p",array(
			'p2p' => new CArrayDataProvider($p2p),
			'view' => "_".$type."List"
		));
	}

	public function actionAjaxReport(){
		$type = $this->getPost('type','p2p');
		$date = $this->getPost('date','1');
	}
}
?>