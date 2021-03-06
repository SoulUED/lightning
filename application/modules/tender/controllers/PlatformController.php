<?php
/**
 * file: PlatformController.php
 * author: Toruneko<toruneko@outlook.com>
 * date: 2013-11-24
 * desc: 支付平台选择
 */
class PlatformController extends Controller{
	private $name = '结算中心';
	
	public function actionOrder(){
		$metaId = Utils::appendDecrypt($this->getQuery('metano'));
		$meta = BidMeta::model()->with('user','bid')->findByPk($metaId);
		
		if(!empty($meta) && $meta->getAttribute('user_id') == $this->user->getId()){
			$user = $meta->getRelated('user');
			$bid = $meta->getRelated('bid');
			
			$this->setPageTitle($bid->getAttribute('title').' - '.$this->name);
			
			$userManager = Yii::app()->getModule('user')->userManager;
			$bider = $userManager->getUserInfo($bid->getAttribute('user_id'));
			
			if(!empty($_POST)){
				$payment = $this->getPost('payment','ips');
				$in_pay = $this->getPost('in-pay');
				
				if($in_pay == 'on'){
					$this->render('check',array(
						'user' => $user,
						'bid' => $bid,
						'bider' => $bider,
						'meta' => $meta
					));
					$this->app->end();
				}else{
					$this->redirect(Yii::app()->getModule('pay')->fundManager->pay($payment,array(
						'metano' => $meta->getAttribute('id'),
						'inpay' => $in_pay,
					)));
				}
			}
			
			$this->render('index',array(
				'user' => $user,
				'bid' => $bid,
				'bider' => $bider,
				'meta' => $meta,
			));
		}else{
			//404
		}
	}
	
	public function actionCheck(){
		$metaId = Utils::appendDecrypt($this->getQuery('metano'));
		$meta = BidMeta::model()->with('user','bid')->findByPk($metaId);

		if( $meta !== null && $meta->getAttribute('user_id') == $this->user->getId()){
			$this->setPageTitle($meta->getRelated('bid')->getAttribute('title').' - '.$this->name);
			
			$data = $this->getPost('Check');
			$password = $this->getPost('pay_pwd');
			$code = $this->getPost('pay_verify');
			$user = $meta->getRelated('user');
			
			if ( $this->app->getSecurityManager()->verifyPassword() === false ){
				$this->render('check',array(
						'user' => $user,
						'bid' => $bid,
						'bider' => $bider,
						'meta' => $meta
				));
				$this->app->end();
			}
			
			if($this->getModule()->bidManager->payPurchasedBid($this->getQuery('metano'))){
				$this->render('success');
			}else{
				//$this->render();//失败 - 账户余额补足  或 重复付款
			}
		}else{
			// 404
		}
	}
}