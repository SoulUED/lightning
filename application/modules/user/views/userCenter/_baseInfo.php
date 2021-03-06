<?php
/**
 * @name _baseInfo.php
 * @author lancelot <lancelot1215@gmail.com>
 * Date 2013-12-19 
 * Encoding UTF-8
 */
$userData = $this->userData;
$BidSum = $this->userBidMoney;
$MetaSum = $this->userMetaBidMoney;
?>
<div class="aud-detail">
                <div class="det-per-inf">
                    <img src="<?php echo $this->user->getState('avatar')?>" class="det-img" />
                    <p>
                        <span class="aud-time">
                        <?php  
                        //var_dump((int)date('H'));die();
                            if(6<=date('H') && date('H')<=13)
                                echo '早上好';
                            elseif(13<date('H') && date('H')<=18)
                                echo '下午好';
                            elseif(18<date('H')||0<=date('H') && date('H')<6)
                                echo '晚上好';
                        ?>，
                        </span>
                        <span class="aud-det-name"><?php echo Yii::app()->user->name;?> </span>
                    </p>
                    <p class="aud-st-serve">
                        <?php if(!empty($userData->nickname)){?>
                        <img src="<?php echo $this->imageUrl.'det-person.png'?>"  title="已绑定昵称"/>
                        <?php }
                            if(!empty($userData->realname)){
                        ?>
                        <img src="<?php echo $this->imageUrl.'det-pro.png'?>" title="已填写实名"/>
                        <?php }
                            if(!empty($userData->email)){
                        ?>
                        <img src="<?php echo $this->imageUrl.'det-email.png'?>" title="已填写邮箱"/>
                        <?php }
                            if(!empty($userData->mobile)){
                        ?>
                        <img src="<?php echo $this->imageUrl.'det-cal.png'?>" title="已绑定手机"/>
                        <?php }
                            if(!empty($userData->pay_password)){
                        ?>
                        <img src="<?php echo $this->imageUrl.'det-bank.png'?>" title="已绑定资金密码"/>
                        <?php }
                        ?>
                        <span>安全等级 :  <span class="det-rank">高</span></span>
                        <span>上次登录 :  <span class="det-ip"> <?php echo $this->request->getIpLocation($userData->last_login_ip).' '.date('Y-m-d H:i:s',$userData['last_login_time'])?></span></span>
                    </p>
                </div>
                <div class="aud-money">
                    <div class="mon-show">
                        <p>
                            <span>账户余额</span>
                            <span>我的投资</span>
                            <span>我的借款</span>
                        </p>
                        <p class="det-mon">
                            <span>
                            <?php
                            $balance = $userData['balance']/100;
                            if($balance >= 100000){
                                $highBalance = $balance/10000;
                                echo $highBalance."万元";
                            }else 
                                echo $balance."元";
                            ?>
                            </span>

                         
                            <span><?php echo $BidSum.'元';?></span>
                            
                            <span><?php echo $MetaSum.'元'?></span>
                        </p>
                        <div>
                            <a href="<?php echo $this->createUrl('userCenter/userFund');?>" id="int">充值</a>
                            <a href="<?php echo $this->createUrl('userCenter/userFund');?>" id="out">提现</a>
                        </div>
                    </div>
                </div>
            </div>
