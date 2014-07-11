<?php

/*!
 * sdk - 乐众支付
 * @author xiewulong <xiewl140320@gxwsxx.com>
 * @create 2014/7/9
 * @since 1.0.0
 */

if(!defined('IN_LEZPAY')){
	die('Access denied');
}

/**
 * 乐众支付功能接口类
 * @class LezPay
 * @since 1.0.0
 */
class LezPay {

	private $devId;	//开发者编号
	
	private $appId;	//应用编号

	private $baseUrl = 'http://10.1.15.50:20005/web-lezpay/ipay/';	//支付接口基础链接
	
	public function __construct($devId, $appId){
		$this->devId = $devId;
		$this->appId = $appId;
	}

	/**
	 * 支付单创建接口
	 * 如创建成功则直接跳转至乐众支付页, 否则打印出错误信息
	 * @method createPayBill
	 * @since 1.0.0
	 * @param {array} $data 以数组格式传入接口参数
	 * @return {none}
	 */
	public function createPayBill($data){
		$result = $this->getData('createPayBill', $data);
		if($result['flag']){
			header('location: ' . $result['lezpayUrl']);
		}else{
			echo $result['retCode'] . ': ' . $result['retMsg'];
		}
	}

	/**
	 * 支付单结算接口
	 * @method settlePayBill
	 * @since 1.0.0
	 * @param {array} $data 以数组格式传入接口参数
	 * @return {array} 以数组格式返回结果数据
	 */
	public function settlePayBill($data){
		return $this->getData('settlePayBill', $data);
	}

	/**
	 * 支付单查询接口
	 * @method queryPayBill
	 * @since 1.0.0
	 * @param {array} $data 以数组格式传入接口参数
	 * @return {array} 以数组格式返回结果数据
	 */
	public function queryPayBill($data){
		$result = $this->getData('queryPayBill', $data);
		if(!(isset($data['retType']) && $data['retType'] == 'JSON')){
			$this->fixedXmlData(&$result['queryPayBillsInfoOutList'], 'QueryPayBillsInfoOut');
		}
		return $result;
	}

	/**
	 * 支付单关闭接口
	 * @method closePayBill
	 * @since 1.0.0
	 * @param {array} $data 以数组格式传入接口参数
	 * @return {array} 以数组格式返回结果数据
	 */
	public function closePayBill($data){
		return $this->getData('closePayBill', $data);
	}

	/**
	 * 统一接口请求方法
	 * @method getData
	 * @since 1.0.
	 * @param {string} $action 接口类型
	 * @param {array} $data 接口参数
	 * @return {array} 返回数组格式的结果数据
	 */
	private function getData($action, $data){
		$data['devId'] = $this->devId;
		$data['appId'] = $this->appId;
		$result = $this->curl($this->baseUrl . $action, http_build_query($data), 'webkit');
		return $this->fixedBool($this->obj2Arr(isset($data['retType']) && $data['retType'] == 'JSON' ? json_decode($result) : simplexml_load_string($result, 'SimpleXMLElement', LIBXML_NOCDATA), 1));
	}

	/**
	 * xml数据数组格式化兼容方法
	 * @method fixedXmlData
	 * @since 1.0.0
	 * @param {array} $data 数据数组指针
	 * @param {string} $name 需要兼容的数组属性名
	 * @return {none} 
	 */
	public function fixedXmlData($data, $name){
		$data = isset($data[$name][0]) ? $data[$name] : array($data[$name]);
	}

	/**
	 * 修复字符串化的布尔值
	 * @method fixedBool
	 * @since 1.0.0
	 * @param {array} $data 数据数组
	 * @return {array} 返回修复后的数据
	 */
	public function fixedBool($data){
		if(isset($data['flag'])){
			switch($data['flag']){
				case 'true':
					$data['flag'] = 1;
					break;
				case 'false':
					$data['flag'] = 0;
					break;
			}
		}
		return $data;
	}

	/**
	 * 将对象转化为数组
	 * @method obj2Arr
	 * @since 1.0.0
	 * @param {object} $obj 对象数据
	 * @param {bool} [$deep=0] 深度转化
	 * @return {array} 返回转化后的数组
	 */
	public function obj2Arr($obj, $deep = 0){
		$_arr = is_object($obj) ? get_object_vars($obj) : $obj;
		$arr = array();
		foreach($_arr as $k => $v){
			$arr[$k] = $deep && (is_object($v) || is_array($v)) ? $this->obj2Arr($v, $deep) : $v;
		}
		return $arr;
	}

	/**
	 * curl远程获取数据方法
	 * @method curl
	 * @since 1.0.0
	 * @param {string} $url 请求地址
	 * @param {array|string} [$data=null] post数据
	 * @param {string} [$useragent=null] 模拟浏览器用户代理信息
	 * @return {string} 返回获取的数据
	 */
	private function curl($url, $data = null, $useragent = null){
		$curl = curl_init();
		curl_setopt($curl, CURLOPT_URL, $url);
		curl_setopt($curl, CURLOPT_HEADER, 0);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
		if(isset($data)){
			curl_setopt($curl, CURLOPT_POST, 1);
			curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
		}
		if(isset($useragent)){
			curl_setopt($curl, CURLOPT_USERAGENT, $useragent);
		}
		$data = curl_exec($curl);
		curl_close($curl);
		return $data;
	}

}