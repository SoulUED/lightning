<?php
/**
 * @name BidManager.php
 * @author wxweven wxweven@163.com
 * Date 2013-11-27 
 * Encoding UTF-8
 */
class BidManager extends CApplicationComponent{	
	/**
	 * 根据标段的id,返回标段的详细信息
	 * Enter description here ...
	 * @param $bidId
	 * @return $bidDetail 标段的详细信息
	 */
	public function getBidInfo($bidId,$condition='',$params=array()) {
		return BidInfo::model()->with('user')->findByPk( $bidId ,$condition,$params); // 通过标段id来获取标段信息
	}
	
	public function getBidMetaInfo($metaId,$condition='',$params=array()){
		return BidMeta::model()->with(array('user','bid'))->findByPk($metaId,$condition,$params);
	}
	
	/**
	 * 标段列表
	 * @param string $condition
	 * @param array $params
	 */
	public function getBidList($condition, $params = array()){
		return BidInfo::model()->with('user')->findAll($condition,$params);
	}
	
	/**
	 * 投资列表
	 * @param string $condition
	 * @param array $params
	 */
	public function getBidMetaList($condition, $params = array()){
		return BidMeta::model()->with(array('user','bid'))->findAll($condition,$params);
	}
	
	/**
	 * 发标
	 * @param integer $user
	 * @param string $title
	 * @param string $description
	 * @param double $sum
	 * @param double $rate
	 * @param integer $start
	 * @param integer $end
	 * @param integer $deadline
	 * @return boolean
	 */
	public function raiseBid($user,$title,$description,$sum,$rate,$start,$end,$deadline){
		$bid = new BidInfo();
		$bid->attribute = array(
			'user_id' => $user,
			'title' => $title,
			'description' => $description,
			'sum' => $sum * 100,
			'month_rate' => $rate * 100,
			'start' => $start,
			'end' => $end,
			'deadline' => $deadline,
			'progress' => 0,
			'verify_progress' => 0
		);
		return $bid->save();
	}
	
	/**
	 * 投标 - 同时锁定标段进度。
	 * @param integer $user_id
	 * @param integer $bid_id
	 * @param integer $sum
	 * @return boolean
	 */
	public function purchaseBid($user_id,$bid_id,$sum){
		//修改标段进度
		$bid = BidInfo::model()->findByPk($bid_id);
		
		$transaction = Yii::app()->db->beginTransaction();
		try{
			$bid->updateCounters(array(
				'progress' => ($sum * 100) / $bid->getAttribute('sum')
			));
			
			$meta = new BidMeta();
			$meta->attributes = array(
				'user_id' => $user_id,
				'bid_id' => $bid_id,
				'sum' => $sum * 100,
				'buy_time' => time(),
				'status' => 0
			);
			$meta->save();
			$transaction->commit();
			return $meta->getPrimaryKey();
		}catch(Exception $e){
			$transaction->rollback();
			return 0;
		}
	}
	
	/**
	 * 
	 * @param integer $meta_no
	 * @return boolean
	 */
	public function payPurchaseBid($meta_no){
		$meta = BidMeta::model()->with(array('user','bid'))->findByPk($meta_no);
		$user = $meta->getRelated('user');
		
		$transaction = Yii::app()->db->beginTransaction();
		try{
			$user->updateCounters(array(
				'balance' => - $meta->getAttribute('sum')
			));
			
			$meta->attributes = array(
				'finish_time' => time(),
				'status' => 1
			);
			$meta->save();
			$transaction->commit();
			return true;
		}catch (Exception $e){
			$transaction->rollback();
			return false;
		}
	}
	
	/**
	 * 
	 * @param integer $meta_no
	 * @return boolean
	 */
	public function revokePurchaseBid($meta_no){
		$meta = BidMeta::model()->with(array('user','bid'))->findByPk($meta_no);
		$user = $meta->getRelated('user');
		$bid = $meta->getRelated('bid');
		
		$transaction = Yii::app()->db->beginTransaction();
		try{
			$bid->updateCounters(array(
				'progress' => - $meta->getAttribute('sum') / $bid->getAttribute('sum')
			));
		
			$meta->attributes = array(
				'finish_time' => time(),
				'status' => 2
			);
			$meta->save();
			$transaction->commit();
			return true;
		}catch (Exception $e){
			$transaction->rollback();
			return false;
		}
	}
	
	/**
	 * 后台审核Bid
	 * @param integer $bid
	 * @param string $message
	 */
	public function handleBid($bid,$message = null){
		if(empty($message)){
			return BidInfo::model()->updateByPk($bid,array('verify_progress' => 1));
		} else {
			return BidInfo::model()->updateByPk($bid,array(
				'verify_progress' => 2,
				'failed_description' => $message
			));
		}
	}
}