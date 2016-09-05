<?php
//Модуль разработан в компании GateOn предназначен для CMS Prestashop 1.6
//Сайт разработчикa: www.gateon.net
//E-mail: www@smartbyte.pro
//Версия: 1.1


class Interkassa extends PaymentModule
{
	private	$_html = '';
	private $_postErrors = array();

	public function __construct()
	{
		$this->name = 'interkassa';
		$this->tab = 'payments_gateways';
		$this->version = '1.1';
		
		$this->currencies = true;
		$this->currencies_mode = 'checkbox';
		
		$config = Configuration::getMultiple(array('ik_shop_id','secret_key', 'test_key'));
		if (isset($config['ik_shop_id']))
			$this->purse_r = $config['ik_shop_id'];
		if (isset($config['secret_key']))
			$this->ikey = $config['secret_key'];
		if(isset($config['test_key'])){
			$this->t_key = $config['test_key'];
		}
		parent::__construct();

		$this->displayName = $this->l('Interkassa');
		$this->description = $this->l('Accepts payments by Interkassa');
		$this->confirmUninstall = $this->l('Are you sure you want to delete your details ?');
		if (!isset($this->purse_r))
			$this->warning = $this->l('Add any purse');
		if (!isset($this->ikey))
			$this->warning = $this->l('Add secret key');
		
	}

	public function install()
	{
		if (!parent::install() OR !$this->registerHook('payment') OR !$this->registerHook('paymentReturn'))
			return false;
		return true;
	}

	public function uninstall()
	{
		if (!Configuration::deleteByName('ik_shop_id')
			OR !Configuration::deleteByName('ik_shop_id')
			OR !Configuration::deleteByName('secret_key')
			OR !Configuration::deleteByName('test_key')
			OR !parent::uninstall())
			return false;
		return true;
	}


	private function _postValidation()
	{
		if (isset($_POST['submitInterkassa']))
		{
			if (empty($_POST['purse_r']))
				$this->_postErrors[] = $this->l('Add any purse');
			if (empty($_POST['ikey']))
				$this->_postErrors[] = $this->l('Add secret key');
		}
	}
	
	private function _postProcess()
	{
		if (isset($_POST['submitInterkassa']))
		{
			Configuration::updateValue('ik_shop_id', $_POST['purse_r']);
			Configuration::updateValue('secret_key', $_POST['ikey']);
			Configuration::updateValue('test_key', $_POST['t_key']);
		}
		$this->_html .= '<div class="conf confirm"><img src="../img/admin/ok.gif" alt="'.$this->l('ok').'" /> '.$this->l('Settings updated').'</div>';
	}
	
	private function _displayInterkassa()
	{
		$this->_html .= '
		<img src="../modules/interkassa/logo_settings.png" style="float:left; margin-right:15px;" />
		<b>'.$this->l('This module allows you to accept payments by Interkassa.').'</b><br /><br />
		<br /><br />';
	}
	
	private function _displayForm()
	{
		$this->_html .=
		'<form action="'.$_SERVER['REQUEST_URI'].'" method="post">
		<fieldset>
			<legend><img src="../img/admin/contact.gif" />'.$this->l('Settings').'</legend>
			<div><label>'.$this->l('ik_shop_id:').'</label>
				<div class="margin-form"><input type="text" size="33" maxlength="36" name="purse_r" value="'.htmlentities(Tools::getValue('purse_r', $this->purse_r), ENT_COMPAT, 'UTF-8').'" />
					<p>Введите 36-и значный индефикатор магазина </p></div>
					<div><label>'.$this->l('secret_key:').'</label>
						<div class="margin-form"><input type="text" size="33" maxlength="30" name="ikey" value="'.htmlentities(Tools::getValue('ikey', $this->ikey), ENT_COMPAT, 'UTF-8').'" />
							<p>Введите секретный ключ магазина максимум 30 символов </p>
						</div><label>Добавьте ваш тестовый ключ:</label>
						<div class="margin-form"><input type="text" size="33" maxlength="30" name="t_key" value="'.htmlentities(Tools::getValue('t_key', $this->t_key), ENT_COMPAT, 'UTF-8').'" />
							<p>Введите тестовый ключ магазина максимум 30 символов </p>
							<br /><center><input type="submit" name="submitInterkassa" value="'.$this->l('Save').'" class="button" /></center>
						</fieldset>
					</form><br /><br />
					<fieldset class="width3">
						<legend><img src="../img/admin/warning.gif" />'.$this->l('Information').'</legend>
						<b style="color: red;">'.$this->l('What connect Interkassa:').'</b><br />
						Зайдите на сайт <b>http://interkassa.com/</b> и пройдите процедуру &quot;Регистрации&quot;.После авторизации заполните поля "Название магазина", URl магазина и нажмите "Добавить (+)". <br />
						<br />После добавления магазина нажмите "Настроить" и произведите настройки по примеру:</p>
						<ul>
							<li><b>URL Успешной оплаты:</b>http://yourrestashop.com/modules/interkassa/success.php Метод передачи Success URL "LINK"</li>
							<li><b>URL Неуспешной оплаты:</b>http://yourrestashop.com/modules/interkassa/fail.php Метод передачи Fail URL "LINK"</li>
							<li><b>URL Ожидания оплаты:</b>http://yourrestashop.com/modules/interkassa/fail.php Метод передачи Fail URL "LINK"</li>
							<li><b>URL Взаимодействия:</b>http://yourrestashop.com/modules/interkassa/validation.php Метод передачи Status URL "POST"</li>
							<li>Также укажите в настройках вашей кассы в разделе Безопасность проверять ли цифровую подпись, это существенно поможет улучшить безопасность</li>
							<li><b style="color: red;">!!! Если Валюта отлична от USD то необхедимо указать "Курс валюты"</b></li>

						</ul>			
					</fieldset>
				</form>';
			}


			public function getContent()
			{
				$this->_html = '<h2>'.$this->displayName.'</h2>';

				if (!empty($_POST))
				{
					$this->_postValidation();
					if (!sizeof($this->_postErrors))
						$this->_postProcess();
					else
						foreach ($this->_postErrors AS $err)
							$this->_html .= '<div class="alert error">'. $err .'</div>';
					}
					else
						$this->_html .= '<br />';

					$this->_displayInterkassa();
					$this->_displayForm();

					return $this->_html;
				}




				public function hookPayment($params)
				{
					if (!$this->active)
						return ;

					global $smarty;

					$id_currency = intval($params['cart']->id_currency);
					$currency = new Currency(intval($id_currency));

					if ($currency->iso_code == 'RUB' && Configuration::get('ik_shop_id'))
						{$purse = Configuration::get('ik_shop_id');}
					else
						{$purse = Configuration::get('ik_shop_id'); 
				// $currency = $this->getCurrency();
				}


				$ik_shop_id = $purse;
				
				$ik_payment_amount = number_format(Tools::convertPrice($params['cart']->getOrderTotal(true, 3), $currency), 2, '.', '');
				$ik_payment_id = intval($params['cart']->id);

				$ik_payment_desc = 'Оплата заказа №'.$ik_payment_id;
				$ik_paysystem_alias = '';
				$ik_baggage_fields = '';

				$ik_sign_hash = '';
				$secret_key = Configuration::get('secret_key');


				$arg = [
				'ik_cur'=>$currency->iso_code,
				'ik_co_id'=>$purse,
				'ik_pm_no'=>intval($params['cart']->id),
				'ik_am'=>$ik_payment_amount,
				'ik_desc'=>'#'.intval($params['cart']->id)
				];
				ksort($arg,SORT_STRING);
				array_push($arg, $secret_key );
				$arg = implode(':', $arg);
				$signature = base64_encode(md5($arg, true));


				$smarty->assign(array(
					'purse' => $purse,
					'currency' => $currency->iso_code,
					'total' => $ik_payment_amount,
					'id_cart' => intval($params['cart']->id),
					'returnUrl' => 'http://'.htmlspecialchars($_SERVER['HTTP_HOST'], ENT_COMPAT, 'UTF-8').__PS_BASE_URI__.'modules/interkassa/validation.php',
					'cancelUrl' => 'http://'.htmlspecialchars($_SERVER['HTTP_HOST'], ENT_COMPAT, 'UTF-8').__PS_BASE_URI__.'order.php',
					'this_path' => $this->_path,
					'sign_hash' => $signature
					));

				return $this->display(__FILE__, 'interkassa.tpl');
			}		
		}
		?>
