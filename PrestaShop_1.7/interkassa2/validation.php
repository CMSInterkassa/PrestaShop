<?php
/**
 * @name Интеркасса 2.0
 * @description Модуль разработан в компании GateOn предназначен для CMS Prestashop 1.7.0.x
 * @author www.gateon.net
 * @email www@smartbyte.pro
 * @version 1.1
 */

include(dirname(__FILE__) . '/../../config/config.inc.php');
include(dirname(__FILE__) . '/interkassa2.php');
$interkassa = new Interkassa2();

if (count($_POST) && checkIP() && isset($_POST['ik_sign'])) {
    wrlog('ip ok');
    wrlog($_POST);
    $cart = new Cart((int)$_POST['ik_pm_no']);

    $currency = new Currency((int)$cart->id_currency);
    $ik_cur = $currency->iso_code;
    $ik_co_id = Configuration::get('INTERKASSA2_CO_ID');

    wrlog($ik_cur);
    wrlog($ik_co_id);
    if ($_POST['ik_inv_st'] == 'success' && $ik_co_id == $_POST['ik_co_id'] && $ik_cur == $_POST['ik_cur']) {

        wrlog('rest params ok');

        if (isset($_REQUEST['ik_pw_via']) && $_REQUEST['ik_pw_via'] == 'test_interkassa_test_xts') {
            $secret_key = Configuration::get('INTERKASSA2_T_KEY');
        } else {
            $secret_key = Configuration::get('INTERKASSA2_S_KEY');
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
        if ($request_sign == $sign) {

            $order = new Order($interkassa->currentOrder);
            if(isset($order->current_state)){
                $history = new OrderHistory();
                $history->id_order = (int)$order->id;
                $history->changeIdOrderState(Configuration::get('INTERKASSA_PAID'), (int)($order->id));
            }else{
                $interkassa->validateOrder($cart->id, Configuration::get('INTERKASSA_PAID'), $_POST['ik_am'], $interkassa->displayName, NULL,array('transaction_id'=>$_POST['ik_inv_id']));
                Tools::redirectLink(__PS_BASE_URI__.'order-confirmation.php?id_cart='.$cart->id.'&id_module='.$interkassa->id.'&id_order='.$interkassa->currentOrder.'&key='.$order->secure_key);
            }
            $order = new Order($interkassa->currentOrder);
        } else {
            Tools::redirectLink(__PS_BASE_URI__ . 'order.php');
        }
    }

}
//Функция для ведения лога
    function wrlog($content)
        {
            $file = 'log.txt';
            $doc = fopen($file, 'a');
            if($doc){
                file_put_contents($file, PHP_EOL . '====================' . date("H:i:s") . '=====================', FILE_APPEND);
                if (is_array($content)) {
                    foreach ($content as $k => $v) {
                        if (is_array($v)) {
                            wrlog($v);
                        } else {
                            file_put_contents($file, PHP_EOL . $k . '=>' . $v, FILE_APPEND);
                        }
                    }
                }elseif(is_object($content)){
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

    if (!ip2long($_SERVER['REMOTE_ADDR']) >= ip2long($ip_stack['ip_begin']) && !ip2long($_SERVER['REMOTE_ADDR']) <= ip2long($ip_stack['ip_end'])) {
        wrlog('REQUEST IP' . $_SERVER['REMOTE_ADDR'] . 'doesnt match');
        die('Ты мошенник! Пшел вон отсюда!');
    }
    return true;
}



