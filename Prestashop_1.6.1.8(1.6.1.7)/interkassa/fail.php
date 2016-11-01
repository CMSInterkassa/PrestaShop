<?php
/**
 * @name Интеркасса 2.0
 * @description Модуль разработан в компании GateOn предназначен для CMS Prestashop 1.6.1.8
 * @author www.gateon.net
 * @email www@smartbyte.pro
 * @version 1.3
 * @update 1.11.2016
 */

include(dirname(__FILE__).'/../../config/config.inc.php');
include(dirname(__FILE__).'/../../header.php'); 
include(dirname(__FILE__).'/interkassa.php');

if($_POST['ik_inv_st']=='canceled'){

	$interkassa = new Interkassa();
	$cart = new Cart(intval($_REQUEST['ik_pm_no']));
	$currency = new Currency(intval($cart->id_currency));
	$interkassa->validateOrder($cart->id, _PS_OS_PAYMENT_, false, $interkassa->displayName, NULL, NULL);
	$order = new Order($interkassa->currentOrder);

}

?>

<h3>Оплата заказа № <?php echo $_REQUEST['ik_pm_no']; ?>  не прошла </h3>
<br/>
<p class="cart_navigation">
	<a href='/' class="exclusive_large">Далее</a>
</p>

<?php
include(dirname(__FILE__).'/../../footer.php');
?>