<?php
/**
 * @name Интеркасса 2.0
 * @description Модуль разработан в компании GateOn предназначен для CMS Prestashop 1.6.1.10
 * @author www.gateon.net
 * @email www@smartbyte.pro
 * @version 1.4
 * @update 10.01.2017
 */
include_once(dirname(__FILE__).'/../../config/config.inc.php');
include_once(dirname(__FILE__).'/interkassa.php');

if($_POST['ik_inv_st'] == 'waitAccept'){
	$interkassa = new Interkassa();
	$cart = new Cart(intval($_REQUEST['ik_pm_no']));
	$currency = new Currency(intval($cart->id_currency));
	$order_amount = number_format(Tools::convertPrice($cart->getOrderTotal(true, 3), $currency), 2, '.', '');
	$interkassa->validateOrder($cart->id,_PS_OS_PREPARATION_,$order_amount, $interkassa->displayName, NULL, NULL);
	$order = new Order($interkassa->currentOrder);
}


include_once(dirname(__FILE__).'/../../header.php');
if(isset($_POST['ik_inv_st'])){
	
	if($_POST['ik_inv_st']=='success'){ ?>
		<h3>Оплата заказа № <?php echo $_POST['ik_pm_no']; ?> прошла успешно</h3>
	<?php } else { ?>
		<h3>Заказ № <?php echo $_POST['ik_pm_no']; ?> ожидает оплаты</h3>
		<?php } ?>
	<br/>
	<p class="cart_navigation">
		<a href='/' class="exclusive_large">Далее</a>
	</p>

<?php } ?>

<?include(dirname(__FILE__).'/../../footer.php');?>