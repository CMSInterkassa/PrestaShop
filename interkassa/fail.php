<?php
include(dirname(__FILE__).'/../../config/config.inc.php');
include(dirname(__FILE__).'/../../header.php'); 
include(dirname(__FILE__).'/interkassa.php');

if($_REQUEST['ik_inv_st']=='canceled'){

	$interkassa = new Interkassa();

	$cart = new Cart(intval($_REQUEST['ik_pm_no']));
	$currency = new Currency(intval($cart->id_currency));
	$order_amount = number_format(Tools::convertPrice($cart->getOrderTotal(true, 3), $currency), 2, '.', '');
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