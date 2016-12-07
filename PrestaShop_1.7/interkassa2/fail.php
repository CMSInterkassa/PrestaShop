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

if($_POST['ik_inv_st'] == 'canceled'){
    $cart = new Cart((int)$_POST['ik_pm_no']);
    $currency = new Currency((int)$cart->id_currency);
    $interkassa->validateOrder($cart->id, _PS_OS_PAYMENT_, $_POST['ik_am'], $interkassa->displayName, NULL,array
    ('transaction_id'=>$_POST['ik_inv_id']));
    $order = new Order($interkassa->currentOrder);
}elseif ($_POST['ik_inv_st'] == 'waitAccept'){
    $cart = new Cart((int)$_POST['ik_pm_no']);
    $currency = new Currency((int)$cart->id_currency);
    $state = Configuration::get('INTERKASSA_PENDING');
    $interkassa->validateOrder($cart->id, $state, $_POST['ik_am'], $interkassa->displayName, NULL,array
    ('transaction_id'=>$_POST['ik_inv_id']));
    $order = new Order($interkassa->currentOrder);
}
Tools::redirectLink(__PS_BASE_URI__ . 'history');


