<?php

/*!
 * demo - 乐众支付sdk
 * @author xiewulong <xiewl140320@gxwsxx.com>
 * @create 2014/7/9
 * @since 1.0.0
 */

header('content-type:text/html;charset=utf-8;');

$action = isset($_GET['action']) ? $_GET['action'] : 'create';	//action默认为创建支付单



/**
 * 处理异步通知
 * @desc 日志信息将记录在当前文件夹的notify.log, 运行demo时请确保当前目录的可写权限
 * @since 1.0.0
 */
if($action == 'notify'){
	$filepath = dirname(__FILE__) . '/notify.log';
	$file = fopen($filepath, 'a+');
	fwrite($file, date('Y-m-d H:i:s', time()) . ': ' . json_encode($_POST) . "\n");
	fclose($file);
	unset($file);
	echo 'success';
	chmod($filepath, 0777);
	exit;
}



/**
 * 处理同步通知
 * @desc 此接口数据仅供视图处理, 切勿作为入库数据
 * @since 1.0.0
 */
if($action == 'callback' && isset($_GET['jumpType'])){
	switch($_GET['jumpType']){
		case '0000':
			$str = '<h1>用户付款成功</h1>';
			$str .= '<p><a href="/60001/index.php?action=settle">支付单结算</a></p>';
			break;
		case '0001':
			$str = '<h1>用户中断付款</h1>';
			break;
		case '0002':
			$str = '<h1>用户希望再次购买该支付单商品</h1>';
			break;
		case '0003':
			$str = '<h1>乐众支付系统出错</h1>';
			break;
	}
	$str .= '<p>billId: ' . $_GET['billId'] . '</p>';
	$str .= '<p>orderId: ' . $_GET['orderId'] . '</p>';
	$str .= '<br /><p><a href="/60001/index.php?action=query">查询支付单状态</a></p>';
	$str .= '<p><a href="/60001/index.php?action=close">关闭支付单</a></p>';
	$str .= '<p><a href="/60001/index.php">生成新支付单</a></p>';
	echo $str;
	exit;
}



define('IN_LEZPAY', 1);	//定义app入口

require_once 'config.php';	//配置文件
require_once 'lezpay.class.php';	//sdk



/**
 * 实例化sdk
 * @desc 必须传入待验证的开发者编号和应用编号
 * @since 1.0.0
 */
$lezpay = new LezPay(LEZPAY_DEVID, LEZPAY_APPID);



/**
 * 处理表单提交请求并调取sdk相应接口获取数据
 * @since 1.0.0
 */
if(isset($_POST['orderId'])){
	switch($action){

		//生成支付单
		case 'create':
			$lezpay->createPayBill($_POST);
			break;

		//支付单结算
		case 'settle':
			$result = $lezpay->settlePayBill($_POST);
			if($result['flag']){
				$str = '<h1>支付单结算成功</h1>';
				$str .= '<p>结算额: ' . $result['settleAmount'] . '元</p>';
				$str .= '<p>退款额: ' . $result['refundAmount'] . '元</p>';
				$str .= '<p>扣除手续费: ' . $result['chargeAmount'] . '元</p>';
			}else{
				$str = '<h1>支付单结算失败: ' . $result['retMsg'] . '</h1>';
				$str .= '<p><a href="/60001/index.php?action=settle">重新结算</a></p>';
				$str .= '<p><a href="/60001/index.php?action=close">关闭支付单</a></p>';
			}
			$str .= '<br /><p><a href="/60001/index.php?action=query">查询支付单状态</a></p>';
			$str .= '<p><a href="/60001/index.php">生成新支付单</a></p>';
			echo $str;
			break;

		//查询支付单
		case 'query':
			$result = $lezpay->queryPayBill($_POST);
			if($result['flag']){
				$str = '<h1>支付单查询成功</h1>';
				$str .= '<p>orderId: ' . $result['orderId'] . '</p>';
				$str .= '<p>billId: ' . $result['billId'] . '</p>';
				$str .= '<table width="100%" border="1px" cellspacing="0">';
				$str .= '<tr>';
				$str .= '<th>billId</th>';
				$str .= '<th>lezpayUrl</th>';
				$str .= '<th>name</th>';
				$str .= '<th>amount</th>';
				$str .= '<th>userId</th>';
				$str .= '<th>scoreApply</th>';
				//$str .= '<th>urlNotice</th>';
				//$str .= '<th>urlJump</th>';
				$str .= '<th>balance</th>';
				$str .= '<th>score</th>';
				$str .= '<th>status</th>';
				$str .= '<th>timeCreate</th>';
				$str .= '<th>timeEnd</th>';
				$str .= '<th>timePay</th>';
				$str .= '</tr>';

				//循环输出该订单下所有支付单
				$querys = $result['queryPayBillsInfoOutList'];
				foreach($querys as $query){
					$str .= '<tr>';
					$str .= '<td>' . $query['billId'] . '</td>';
					$str .= '<td>' . $query['lezpayUrl'] . '</td>';
					$str .= '<td>' . $query['name'] . '</td>';
					$str .= '<td>' . $query['amount'] . '</td>';
					$str .= '<td>' . $query['userId'] . '</td>';
					$str .= '<td>' . $query['scoreApply'] . '</td>';
					//$str .= '<td>' . $query['urlNotice'] . '</td>';
					//$str .= '<td>' . $query['urlJump'] . '</td>';
					$str .= '<td>' . $query['balance'] . '</td>';
					$str .= '<td>' . $query['score'] . '</td>';
					$str .= '<td>' . $query['status'] . '</td>';
					$str .= '<td>' . $query['timeCreate'] . '</td>';
					$str .= '<td>' . $query['timeEnd'] . '</td>';
					$str .= '<td>' . $query['timePay'] . '</td>';
					$str .= '</tr>';
				}

				$str .= '</table>';
				$str .= '<p><a href="/60001/index.php?action=settle">支付单结算</a></p>';
				$str .= '<p><a href="/60001/index.php?action=close">关闭支付单</a></p>';
			}else{
				$str = '<h1>支付单查询失败</h1>';
			}
			$str .= '<br /><p><a href="/60001/index.php?action=query">重新查询</a></p>';
			$str .= '<p><a href="/60001/index.php">生成新支付单</a></p>';
			echo $str;
			break;
		
		//关闭支付单
		case 'close':
			$result = $lezpay->closePayBill($_POST);
			if($result['flag']){
				$str = '<h1>支付单关闭成功</h1>';
			}else{
				$str = '<h1>支付单关闭失败: ' . $result['retMsg'] . '</h1>';
				$str .= '<p><a href="/60001/index.php?action=close">重新关闭支付单</a></p>';
			}
			$str .= '<br /><p><a href="/60001/index.php?action=query">查询支付单状态</a></p>';
			$str .= '<p><a href="/60001/index.php">生成新支付单</a></p>';
			echo $str;
			break;
			break;
	}
	exit;
}



/**
 * 根据action定义标题话术
 * @since 1.0.0
 */
switch($action){
	case 'create':
		$title = '生成支付单';
		break;
	case 'settle':
		$title = '订单结算';
		break;
	case 'query':
		$title = '查询支付单状态';
		break;
	case 'close':
		$title = '关闭支付单';
		break;
}

?>

<!DOCTYPE html>

<!-- begin html -->
<html>

<!-- begin head -->
<head>
<title><?=$title?> - 乐众支付</title>
<meta http-equiv="content-type" content="text/html;charset=utf-8" />
<meta http-equiv="X-UA-Compatible" content="IE=edge" />
<meta name="author" content="xiewulong" />

</head>
<!-- end head -->

<!-- begin body -->
<body style="background:#fff;">

<h1>乐众支付 - <?=$title?></h1>

<div>
	<form id="payForm" action="/60001/index.php?action=<?=$action?>" method="post">
		<p>
			<label>
				<span>数据构造方式(可选)：</span>
				<select name="retType">
					<option value="XML" selected="selected">XML</option>
					<option value="JSON">JSON</option>
				</select>
			</label>
		</p>
		<p>
			<label>
				<span>数据编码格式(可选)：</span>
				<select name="codeType">
					<option value="UTF-8" selected="selected">UTF-8</option>
				</select>
			</label>
		</p>
		<p>
			<label>
				<span>订单号：</span>
				<input type="text" name="orderId" value="test0010" />
			</label>
		</p>
		<?php if($action != 'create'){ ?>
		<p>
			<label>
				<span>支付单号：</span>
				<input type="text" name="billId" value="000013A" />
			</label>
		</p>
		<?}?>
		<?php if($action == 'create'){ ?>
		<p>
			<label>
				<span>商品或服务名称：</span>
				<input type="text" name="name" value="测试商品" />
			</label>
		</p>
		<p>
			<label>
				<span>商品或服务描述信息(可选)：</span>
				<textarea name="describe" cols="30" rows="3">测试商品的描述</textarea>
			</label>
		</p>
		<p>
			<label>
				<span>用户的机顶盒号：</span>
				<input type="text" name="stbId" id="stbId" readonly="readonly" />
			</label>
		</p>
		<p>
			<label>
				<span>支付有效期：</span>
				<select name="validTime">
					<option value="24" selected="selected">24</option>
					<option value="48">48</option>
					<option value="72">72</option>
					<option value="240">240</option>
				</select>
				<span>小时</span>
			</label>
		</p>
		<p>
			<label>
				<span>支付单金额(格式0.00)：</span>
				<input type="text" name="amount" value="8888.88" />
				<span>元</span>
			</label>
		</p>
		<p>
			<label>
				<span>是否可以使用乐众支付积分抵扣付款：</span>
				<select name="scoreApply">
					<option value="0001" selected="selected">启用</option>
					<option value="0000">禁用</option>
				</select>
			</label>
		</p>
		<p>
			<label>
				<span>异步通知URL：</span>
				<input type="text" name="urlNotice" size="50" value="<?=LEZPAY_NOTIFYURL?>" readonly="readonly" />
			</label>
		</p>
		<p>
			<label>
				<span>跳出返回URL：</span>
				<input type="text" name="urlJump" size="50" value="<?=LEZPAY_CALLBACK?>" readonly="readonly" />
			</label>
		</p>
		<?}?>
		<?php if($action == 'settle'){ ?>
		<p>
			<label>
				<span>结算类型：</span>
				<select name="type">
					<option value="SETTLE" selected="selected">结算</option>
					<option value="REFUND">退款</option>
				</select>
			</label>
		</p>
		<p>
			<label>
				<span>结算金额：</span>
				<input type="text" name="settleAmount" value="8800.00" />
				<span>元</span>
			</label>
		</p>
		<p>
			<label>
				<span>退款金额：</span>
				<input type="text" name="refundAmount" value="88.88" />
				<span>元</span>
			</label>
		</p>
		<?}?>
		<p>
			<button type="submit">提交</button>
		</p>
	</form>
</div>

<?php if($action == 'create'){ ?>
<!-- begin javascript -->
<script type="text/javascript">
/**
 * 获取机顶盒号和机顶盒类型
 * @desc pc端将机顶盒号置为demo数据'54521541511', 机顶盒类型为'0000'
 * @desc 如机顶盒类型测试为'0001', 不兼容post提交, 则需要把method设置为'get'
 * @since 1.0.0
 */
(function(window, undefined){
	var win		= window,
		doc		= document,
		_stbId	= win.guangxi ? guangxi.getStbNum() || guangxi.System.newwork.macAddress.replace(/:/g, '').replace(/No Card/g, '') || document.all.ip.value : '54521541511',
		_bType	= win.iPanel ? /(Safari)|(Chrome)|(Firefox)/.test(navigator.userAgent) ? _stbId.length == 11 && _stbId.substring(2, 4) == '19' ? '0002' : win.iPanel.getGlobalVar('RESOLUTION_1280_720') ? '0003' : '0002' : '0001' : '0000';
	doc.getElementById('stbId').value = _stbId;
	_bType == '0001' && (doc.getElementById('payForm').method = 'get');
})(window);
</script>
<!-- end javascript -->
<?}?>

</body>
<!-- end body -->

</html>
<!-- end html -->