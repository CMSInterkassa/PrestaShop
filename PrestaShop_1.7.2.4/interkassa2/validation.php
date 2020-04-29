<?php
/**
 * @name Интеркасса 2.0
 * @description Модуль предназначен для CMS Prestashop 1.7.x
 * @author interkassa
 * @last_update 29.04.2020
 * @version 1.3
 */

include(dirname(__FILE__) . '/../../config/config.inc.php');
include(dirname(__FILE__) . '/interkassa2.php');
$interkassa = new Interkassa2();
//ini_set('display_errors', 1);
$request = $_POST;

if (!empty($request) && checkIP() && isset($request['ik_sign'])) {
//    wrlog('ip ok');
//    wrlog($request);
    $cart = new Cart((int)$request['ik_pm_no']);
    $customer = new Customer((int)$cart->id_customer);

    $currency = new Currency((int)$cart->id_currency);
    $ik_cur = $currency->iso_code;
    $ik_co_id = Configuration::get('INTERKASSA2_CO_ID');

    if ($request['ik_inv_st'] == 'success' && $ik_co_id == $request['ik_co_id'] && $ik_cur == $request['ik_cur']) {
//        wrlog('rest params ok');

        $request_sign = $request['ik_sign'];

        if (isset($request['ik_pw_via']) && $request['ik_pw_via'] == 'test_interkassa_test_xts') {
            $sign = $interkassa::IkSignFormation($request, 0);
        } else {
            $sign = $interkassa::IkSignFormation($request);
        }
        if ($request_sign == $sign) {
            if ($request['ik_inv_st'] == 'success') {

                $orderId = Order::getOrderByCartId((int)($cart->id));
                $order = new Order($orderId);
                if (isset($order->current_state)) {
                    file_put_contents(__DIR__ . '/temp.log', 'current_state -> ' . $order->current_state . "\n\n", FILE_APPEND);
                    $history = new OrderHistory();
                    $history->id_order = (int)$order->id;
                    $history->changeIdOrderState(Configuration::get('INTERKASSA_PAID'), (int)($order->id));
                    echo 'OK';
                    header("HTTP/1.1 200 OK");
                    exit;
                } else {
                    // $kernel - fix bug exception
                    global $kernel;
                    if(!$kernel){
                        require_once _PS_ROOT_DIR_.'/app/AppKernel.php';
                        $kernel = new \AppKernel('prod', false);
                        $kernel->boot();
                    }

                    $interkassa->validateOrder(
                        $cart->id,
                        Configuration::get('INTERKASSA_PAID'),
                        $request['ik_am'],
                        $interkassa->displayName,
                        NULL,
                        [],
                        (int)$currency->id,
                        false,
                        $customer->secure_key
                    );

                    $orderId = Order::getOrderByCartId((int)($cart->id));
                    $order = new Order($orderId);
                    $history = new OrderHistory();
                    $history->id_order = (int)$order->id;
                    $history->changeIdOrderState(Configuration::get('INTERKASSA_PAID'), (int)($order->id));


                    echo 'OK';
                    header("HTTP/1.1 200 OK");
                }

                exit;
            }
        } else {
            Tools::redirectLink(__PS_BASE_URI__ . 'order.php');
        }
    }

}

function checkIP()
{
    $ip_stack = array(
        '151.80.190.97',
        '35.233.69.55'//'151.80.190.104'
    );

    $ip = !empty($_SERVER['HTTP_CF_CONNECTING_IP'])? $_SERVER['HTTP_CF_CONNECTING_IP'] : $_SERVER['REMOTE_ADDR'];
    $ip_callback = ip2long($ip) ? ip2long($ip) : !ip2long($ip);

    if ($ip_callback == ip2long($ip_stack[0]) || $ip_callback == ip2long($ip_stack[1])) {
        return true;
    } else {
        return false;
    }
}

//Функция для ведения лога
function wrlog($content)
{
    $file = 'log.txt';
    $doc = fopen($file, 'a');
    if ($doc) {
        file_put_contents($file, PHP_EOL . '====================' . date("H:i:s") . '=====================', FILE_APPEND);
        if (is_array($content)) {
            foreach ($content as $k => $v) {
                if (is_array($v)) {
                    wrlog($v);
                } else {
                    file_put_contents($file, PHP_EOL . $k . '=>' . $v, FILE_APPEND);
                }
            }
        } elseif (is_object($content)) {
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