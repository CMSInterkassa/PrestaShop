<?php
//Модуль разработан в компании GateOn предназначен для CMS Prestashop 1.6
//Сайт разработчикa: www.gateon.net
//E-mail: www@smartbyte.pro
//Версия: 1.1

include(dirname(__FILE__).'/../../config/config.inc.php');
include(dirname(__FILE__).'/interkassa.php');
$interkassa = new Interkassa();

wrlog('log activated');

if(isset($_REQUEST['ik_pw_via']) && $_REQUEST['ik_pw_via'] == 'test_interkassa_test_xts'){
	$secret_key = Configuration::get('test_key');
} else {
	$secret_key = Configuration::get('secret_key');
}
//Снова формируем ключ для проверки с ответом Интеркассы

$cart = new Cart(intval($_REQUEST['ik_pm_no']));
$currency = new Currency(intval($cart->id_currency));
$order_amount = number_format(Tools::convertPrice($cart->getOrderTotal(true, 3), $currency), 2, '.', '');
$request_sign = $_REQUEST['ik_sign'];

unset($_REQUEST['ik_sign']);

$arr = $_REQUEST;
ksort($arr,SORT_STRING);
array_push($arr, $secret_key);
$arr = implode(':', $arr);
$signature = base64_encode(md5($arr, true));
		
if($_REQUEST){
	
	if(compare($order_amount,$_REQUEST['ik_am']) && compare($currency->iso_code, $_REQUEST['ik_cur']) && compare($signature,$request_sign)){
			// wrlog($request_sign.'/'.$signature);
		wrlog('validation has done');
	
		$interkassa->validateOrder($cart->id, _PS_OS_PAYMENT_, $_REQUEST['ik_am'], $interkassa->displayName, NULL, NULL);
		$order = new Order($interkassa->currentOrder);
		Tools::redirectLink(__PS_BASE_URI__.'order-confirmation.php?id_cart='.$cart->id.'&id_module='.$interkassa->id.'&id_order='.$interkassa->currentOrder.'&key='.$order->secure_key);
	}

}else{
	Tools::redirectLink(__PS_BASE_URI__.'order.php');
}

Tools::redirectLink(__PS_BASE_URI__.'order.php');

//Эта функция сделана в целях записи ответа от Итнеркассы и не является обязательной.
function wrlog($content){
	$file = 'log.txt';
	$doc = fopen($file, 'a');
	file_put_contents($file, PHP_EOL . $content, FILE_APPEND);	
	fclose($doc);
}
function compare($first,$second){
	return $first==$second;
}

?>