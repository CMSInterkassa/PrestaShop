<?php
/**
 * @name Интеркасса 2.0
 * @description Модуль разработан в компании GateOn предназначен для CMS Prestashop 1.6.1.12
 * @author www.gateon.net
 * @email www@smartbyte.pro
 * @version 1.5
 * @update 28.03.2017
 */

include(dirname(__FILE__).'/../../config/config.inc.php');
include(dirname(__FILE__).'/interkassa.php');
$interkassa = new Interkassa();


if (count($_POST) && checkIP() && isset($_POST['ik_sign'])) {

    $cart = new Cart(intval($_POST['ik_pm_no']));
    $currency = new Currency(intval($cart->id_currency));
    $ik_cur = $currency->iso_code;
    $ik_am = number_format(Tools::convertPrice($cart->getOrderTotal(true, 3), $currency), 2, '.', '');
    $ik_co_id = Configuration::get('ik_co_id');


    if ($_POST['ik_inv_st'] == 'success' && $ik_co_id == $_POST['ik_co_id'] && $ik_cur == $_POST['ik_cur'] && $ik_am ==$_POST['ik_am']) {

        wrlog('rest params ok');

        if(isset($_REQUEST['ik_pw_via']) && $_REQUEST['ik_pw_via'] == 'test_interkassa_test_xts'){
            $secret_key = Configuration::get('test_key');
        } else {
            $secret_key = Configuration::get('secret_key');
        }

        $request = $_POST;
        $request_sign = $request['ik_sign'];
        unset($request['ik_sign']);

        //удаляем все поле которые не принимают участия в формировании цифровой подписи
        foreach ($request as $key => $value) {
            if (!preg_match('/ik_/', $key)) continue;
            $request[$key] = $value;
        }

        //формируем цифровую подпись
        ksort($request, SORT_STRING);
        array_push($request, $secret_key);
        $str = implode(':', $request);
        $sign = base64_encode(md5($str, true));

        wrlog($sign . '/' . $request_sign);

        //Если подписи совпадают то осуществляется смена статуса заказа в админке
        if ($request_sign == $sign) {
            $interkassa->validateOrder($cart->id, Configuration::get('INTERKASSA_PAID'), $_POST['ik_am'], $interkassa->displayName, NULL, array('transaction_id'=>$_POST['ik_inv_id']));
            $order = new Order($interkassa->currentOrder);
            Tools::redirectLink(__PS_BASE_URI__.'order-confirmation.php?id_cart='.$cart->id.'&id_module='.$interkassa->id.'&id_order='.$interkassa->currentOrder.'&key='.$order->secure_key);

        } else {
            Tools::redirectLink(__PS_BASE_URI__.'order.php');
        }
    } else {
        Tools::redirectLink(__PS_BASE_URI__.'order.php');
    }
} else {
    Tools::redirectLink(__PS_BASE_URI__.'order.php');
}



//Функция для ведения лога
function wrlog($content){
    $file = 'log.txt';
    $doc = fopen($file, 'a');
    if($doc){
        file_put_contents($file, PHP_EOL . '====================' . date("H:i:s") . '=====================', FILE_APPEND);
        if (is_array($content)) {
            wrlog('Вывод массива:');
            foreach ($content as $k => $v) {
                if (is_array($v)) {
                    wrlog($v);
                } else {
                    file_put_contents($file, PHP_EOL . $k . '=>' . $v, FILE_APPEND);
                }
            }
        }elseif(is_object($content)){
            wrlog('Вывод обьекта:');
            foreach (get_object_vars($content) as $k => $v) {
                if (is_object($v)) {
                    wrlog($v);
                } else {
                    file_put_contents($file, PHP_EOL . $k . '=>' . $v, FILE_APPEND);
                }
            }
        } else {
            file_put_contents($file, PHP_EOL . $content, FILE_APPEND);
        }
        fclose($doc);
    }
}

function checkIP()
{
    $ip_stack = array(
        'ip_begin' => '151.80.190.97',
        'ip_end' => '151.80.190.104'
        );

    if (ip2long($_SERVER['REMOTE_ADDR']) < ip2long($ip_stack['ip_begin']) || ip2long($_SERVER['REMOTE_ADDR']) > ip2long($ip_stack['ip_end'])) {
        wrlog('REQUEST IP' . $_SERVER['REMOTE_ADDR'] . 'doesnt match');
        return false;
    }
    return true;
}


?>