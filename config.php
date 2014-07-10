<?php

/*!
 * 配置文件 - 乐众支付sdk
 * @author xiewulong <xiewl140320@gxwsxx.com>
 * @create 2014/7/9
 * @since 1.0.0
 */

if(!defined('IN_LEZPAY')){
	die('Access denied');
}

define('LEZPAY_DEVID', '123456789012');	//开发者编号
define('LEZPAY_APPID', '123456789012');	//应用编号
define('LEZPAY_NOTIFYURL', 'http://yourdomain/notify');	//异步通知URL, 请填写绝对地址
define('LEZPAY_CALLBACK', 'http://yourdomain/callback');	//同步通知URL, 请填写绝对地址