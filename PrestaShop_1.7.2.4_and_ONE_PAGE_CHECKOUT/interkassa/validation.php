<?php
include(dirname(__FILE__) . '/../../config/config.inc.php');
include(dirname(__FILE__).'/../../init.php');
$context = Context::getContext();
$cart = $context->cart;
$interkassa = Module::getInstanceByName('interkassa');

if($cart->id_customer==0 OR $cart->id_address_delivery==0 OR $cart->id_address_invoice==0 OR !$interkassa->active)Tools::redirect('index.php?controller=order&step=1');

$authorized = false;
foreach(Module::getPaymentModules() as $module)if($module['name']=='interkassa'){$authorized=true;break;}
if(!$authorized)die($interkassa->getTranslator()->trans('This payment method is not available.', array(), 'Modules.Interkassa.Shop'));

$customer=new Customer((int)$cart->id_customer);
if(!Validate::isLoadedObject($customer))Tools::redirect('index.php?controller=order&step=1');

$currency = $context->currency;
$total = (float)($cart->getOrderTotal(true, Cart::BOTH));

if(Configuration::get('INTERKASSA_CO_ID') == $_POST['ik_co_id']){
$interkassa->validateOrder($cart->id,Configuration::get('PS_OS_BANKWIRE'),$total,$interkassa->displayName,NULL,array(),(int)$currency->id,false,$customer->secure_key);
$order = new Order($interkassa->currentOrder);
Tools::redirect('index.php?controller=order-confirmation&id_cart='.$cart->id.'&id_module='.$interkassa->id.'&id_order='.$interkassa->currentOrder.'&key='.$customer->secure_key);
}else Tools::redirect('index.php?controller=order&step=4');
