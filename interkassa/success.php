<?php
//Модуль разработан в компании GateOn предназначен для CMS Prestashop 1.6
//Сайт разработчикa: www.gateon.net
//E-mail: www@smartbyte.pro
//Версия: 1.1


include(dirname(__FILE__).'/../../config/config.inc.php');
include(dirname(__FILE__).'/../../header.php');
?>
<h3>Оплата заказа № <?php echo $_REQUEST['ik_pm_no']; ?> прошла успешно</h3>
<br/>
<p class="cart_navigation">
	<a href='/' class="exclusive_large">Далее</a>
</p>

<?include(dirname(__FILE__).'/../../footer.php');?>