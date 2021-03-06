<?php
$this->cs->registerCssFile($this->cssUrl.'common.css');
$this->cs->registerCssFile($this->cssUrl.'detail.css');

$this->cs->registerScript("table","
$('.form-button').on('click',function(){
	$.post('".$this->createUrl('userCenter/ajaxFund')."',{type:$('#search-type').val(),date:$('#search-date').val()},function(res){
		$('.record-table tbody').remove();
		$('.record-table .list-view').remove();
		console.log(res);
		$('.record-table').html($('.record-table').html() + res);
	});
});
$('#export-record').on('click',function(){
	$.post('".$this->createUrl('userCenter/ajaxReport')."',{type:$('#search-type').val(),date:$('#search-date').val()},function(res){
		
	});
});
");
?>
    <div id="container">
        <div class="wd989">
            <h1 class="aud-nav">
                <a href="<?php echo $this->createUrl('userCenter/userFund'); ?>">个人中心 ></a>
                <a href="#">我的闪电贷</a>
            </h1>
            <?php $this->renderPartial('_baseInfo')?>
            <div class="aud-find aud-find-menu">
                <ul id="find-table-button">
                    <a href="<?php echo Yii::app()->createUrl('user/userCenter/userInfo');?>">
                        <li class="find-table-3"><div class="find-table-op">个人信息</div></li>
                    </a>
                    <a href="<?php echo Yii::app()->createUrl('user/userCenter/myBorrow');?>">
                        <li class="find-table-0"><div class="find-table-op">我的借款</div></li>
                    </a>
                    <a href="<?php echo Yii::app()->createUrl('user/userCenter/myLend');?>">
                        <li class="find-table-1"><div class="find-table-op">我的投资</div></li>
                        </a>
                    <a href="<?php echo Yii::app()->createUrl('user/userCenter/userSecurity');?>">
                        <li class="find-table-2"><div class="find-table-op">安全中心</div></li>
                    </a>
                    <a href="<?php echo $this->createUrl('userCenter/userFund')?>">
                        <li class="find-table-4"><div class="find-table-op-hidden">资金管理</div></li>
                    </a>
                </ul>
            </div>
            <div class="aud-find">
                <div class="find-table-box find-table-box-show fund">
                    <div class="tab-border tab-top"></div>
                    <div class="tab-border tab-bottom"></div>
                    <div class="tab-border-l tab-left"></div>
                    <div class="tab-border-l tab-right"></div>
                    <div class="table-content">
                        <div class="tab-content-l">
                            <div class="tab-border-l tab-right"></div>
                            <p>账户余额<span><?php echo number_format($userData->getAttribute('balance') / 100,2); ?><span>元</span></span></p>
                        </div>
                        <div class="tab-content-r">
                            <p>
                            	<?php $total = $this->app->getModule('tender')->bidManager->getLockBalance($this->user->getId()); ?>
                                <span>总资金  <?php echo number_format($total + $userData->getAttribute('balance') / 100,2); ?>元</span>
                                <span>可用资金  <?php echo number_format($userData->getAttribute('balance') / 100,2); ?>元</span>
                            </p>
                            <p>
                                <span>冻结资金  <?php echo number_format($total,2); ?>元</span>
                                <span>可提现资金  <?php echo number_format($userData->getAttribute('balance') / 100,2); ?>元</span>
                            </p>
                        </div>
                    </div>
                </div>
                <ul id="find-table-detail">
                    <li class="find-selected">交易记录</li>
                    <li>充值</li>
                    <li>提现</li>
                </ul>
                <div class="find-table-content fund find-table-content-show">
                    <div id="fund-record-query">
                        <span>查询类型</span>
                        <select name="query_type" id="search-type">
                        	<option value="p2p">即时到帐</option>
                            <option value="recharge">充值记录</option>
                            <option value="withdraw">提现记录</option>
                        </select>
                        <span>查询时间</span>
                        <select name="query_date" id="search-date">
                        	<option value="1">一月以内</option>
                        	<option value="3">三月以内</option>
                        	<option value="12">一年以内</option>
                        </select>
                        <a href="javascript:void(0);" class="form-button" id="query-button">查询</a>
                        <a href="javascript:void(0);" id="export-record">导出查询结果</a>
                    </div>
                    <table class="record-table"> 
                        <thead>
                            <tr>
                                <td>时间</td>
                                <td>对方 | 账号</td>
                                <td>金额</td>
                                <td>手续费</td>
                                <td>状态</td>
                                <td>操作</td>
                            </tr>
                        </thead>
						<?php $this->widget('zii.widgets.CListView',array(
							'dataProvider' => $p2p,
							'itemView' => '_p2pList',
							'template' => '{items}',
							'itemsTagName' => 'tbody',
							'emptyText' => '',
							'ajaxUpdate' => false,
							'cssFile' => false,
							'baseScriptUrl' => null,
						)); ?>
                    </table>
                </div>
                <div class="find-table-content fund">
                    <div class="paymethod-bank clearfix">
                        <h2>选择充值方式</h2>
                        <ul>
                          <li>
                            <input type="radio" name="pay_bank" value="ips" id="b-ips" />
                            <label class="ips" for="b-ips"></label>
                          </li>
                        </ul>
                      </div>
                    <div class="pay-form">
                        <h2>填写充值金额</h2>
                        <form>
                            <ul>
                                <li>
                                    <label>账户余额</label>
                                    <span><?php echo number_format($userData->getAttribute('balance') / 100,2); ?></span>
                                    <span>元</span>
                                </li>
                                <li>
                                    <label for="pay-num">充值金额</label>
                                    <input type="text" id="pay-num"/>
                                    <span>元</span>
                                </li>
                                <li>
                                    <label>充值费用</label>
                                    <span>0.00</span>
                                    <span>元</span>
                                </li>
                                <li>
                                    <label>手机号码</label>
                                    <span><?php echo $userData->getAttribute('mobile'); ?></span>
                                </li>
                                <li>
                                    <label>验证码</label>
                                    <input type="text" class="verifycode"/>
                                    <img src="" id="randImage"/>
                                </li>
                            </ul>
                        </form>
                    </div>
                </div>
                <div class="find-table-content withdraw">
                    <div class="pay-form">
                        <h2>请填写提现数据</h2>
                        <form id="fund-withdraw">
                            <ul>
                                <li>
                                    <label>可用资金 </label>
                                    <span class="number">0.00</span>
                                    <span>元</span>
                                </li>
                                <li>
                                    <label for="withdraw-num">提现金额 </label>
                                    <input type="text" id="withdraw-num"/>
                                    <span>元</span>
                                </li>
                                <li>
                                    <label for="withdraw-num">银行卡号</label>
                                    <input type="text" id="withdraw-num"/>
                                </li>                              
                                <li>
                                    <label>提现费用 </label>
                                    <span class="number" id="userPaySum">0.00</span>
                                    <span>元</span>
                                    <div class="hint">
                                        <img src="<?php echo $this->imageUrl.'fund_hint.png';?>" />
                                        <p>test</p>
                                    </div>
                                </li>
                                <li>
                                    <label>实际扣除金额 </label>
                                    <span class="number" id="getSum">0.00</span>
                                    <span>元</span>
                                </li>
                                <li>
                                    <label>预计到账日期 </label>
                                    <span>2013-11-17</span>
                                </li>
                                <li>
                                    <label>资金密码 </label>
                                    <input type="password" id="withdraw-passwd" />
                                    <a href="#">忘记密码</a>
                                </li>
                                <li>
                                    <input type="submit" value="提现" class="form-button" id="fund-withdraw-submit"/>
                                </li>
                            </ul>
                        </form>
                        <p>温馨提示</p>
                        <p>1.请确保您输入的是提现金额，以及银行账号信息准确无误。</p>
                        <p>2.如果您填写的提现信息不正确可能会导致提现失败，由此产生的提现费用将不予返还</p>
                        <p>3.在双休日和法定节假日期间，用户可以申请提现，闪电贷会在下一个工作日进行处理。由此造成的不便，请多多谅解！</p>
                        <p>4.平台禁止洗钱、信用卡套现、虚假交易等行为，一经发现并确认，将终止该账户的使用。</p>
                    </div>
                </div>
                <div class="find-table-content">
                </div>
            </div>
        </div>
    </div>
<?php
$this->cs->registerScriptFile($this->scriptUrl.'/update.js',CClientScript::POS_END);
$this->cs->registerScriptFile($this->scriptUrl.'tableChange.js',CClientScript::POS_END);
$this->cs->registerScriptFile($this->scriptUrl.'/personal.js',CClientScript::POS_END);
?>
