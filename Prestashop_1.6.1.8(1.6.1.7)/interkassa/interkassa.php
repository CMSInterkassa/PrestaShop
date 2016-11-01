<?php

/**
 * @name Интеркасса 2.0
 * @description Модуль разработан в компании GateOn предназначен для CMS Prestashop 1.6.1.8
 * @author www.gateon.net
 * @email www@smartbyte.pro
 * @version 1.3
 * @update 1.11.2016
 */

class Interkassa extends PaymentModule
{
    private $_html = '';
    private $_postErrors = array();

    public function __construct()
    {
        $this->name = 'interkassa';
        $this->tab = 'payments_gateways';
        $this->version = '1.3';
        $this->author = 'GateOn';
        $this->currencies = true;
        $this->currencies_mode = 'checkbox';

        $config = Configuration::getMultiple(array(
                'ik_co_id',
                'secret_key',
                'test_key')
        );
        if (isset($config['ik_co_id'])) {
            $this->ik_co_id = $config['ik_co_id'];
        }
        if (isset($config['secret_key'])) {
            $this->s_key = $config['secret_key'];
        }
        if (isset($config['test_key'])) {
            $this->t_key = $config['test_key'];
        }
        parent::__construct();

        if (!isset($this->ik_co_id)) {
            $this->warning = $this->l('add ik_co_id');
        }

        if (!isset($this->s_key)) {
            $this->warning = $this->l('add secret key');
        }

        if (!isset($this->t_key)) {
            $this->warning = $this->l('add test key');
        }

        $this->displayName = $this->l('Interkassa2');
        $this->description = $this->l('Pay with Interkassa');
        $this->confirmUninstall = $this->l('Are you sure you want to remove?');
    }

    public function install()
    {
        if (!parent::install() OR !$this->registerHook('payment') OR !$this->registerHook('paymentReturn'))
            return false;
        return true;
    }

    public function uninstall()
    {
        if (!Configuration::deleteByName('ik_co_id')
            OR !Configuration::deleteByName('ik_co_id')
            OR !Configuration::deleteByName('secret_key')
            OR !Configuration::deleteByName('test_key')
            OR !parent::uninstall()
        )
            return false;
        return true;
    }


    private function _postValidation()
    {
        if (isset($_POST['ik_submit'])) {
            if (empty($_POST['ik_co_id'])){
                $this->_postErrors[] = $this->l('add ik_co_id');
            }
            if (empty($_POST['s_key'])){
                $this->_postErrors[] = $this->l('add secret key');
            }
            if (empty($_POST['t_key'])){
                $this->_postErrors[] = $this->l('add secret key');
            }
        }
    }

    private function _postProcess()
    {
        if (isset($_POST['ik_submit'])) {
            Configuration::updateValue('ik_co_id', $_POST['ik_co_id']);
            Configuration::updateValue('secret_key', $_POST['s_key']);
            Configuration::updateValue('test_key', $_POST['t_key']);
        }
        $this->_html .= '<div class="conf confirm"><img src="../img/admin/ok.gif" alt="' . $this->l('ok') . '" /> ' . $this->l('settings have been updated') . '</div>';
    }

    private function _displayInterkassa()
    {
        $this->_html .= '
		<img src="../modules/interkassa/logo_settings.png" style="float:left; margin-right:15px;" /><br><br>
		<h2><b>' . $this->l('Pay with Interkassa') . '</b></h2>';
    }

    private function _displayForm()
    {
        $this->_html .=
            '<form action="' . $_SERVER['REQUEST_URI'] . '" method="post" class="defaultForm form-horizontal bootstrap">
		<fieldset>
			<legend><img src="../img/admin/contact.gif" />' . $this->l('Settings') . '</legend>
			<div><label>' . $this->l('ik_shop_id:') . '</label>
				<div class="margin-form"><input type="text" size="33" maxlength="36" name="ik_co_id" value="' . htmlentities(Tools::getValue('ik_co_id', $this->ik_co_id), ENT_COMPAT, 'UTF-8') . '" />
					<p>Введите 36-и значный идентификатор </p></div>
					<div><label>' . $this->l('secret_key:') . '</label>
						<div class="margin-form"><input type="text" size="33" maxlength="30" name="s_key" value="' . htmlentities(Tools::getValue
            ('s_key', $this->s_key), ENT_COMPAT, 'UTF-8') . '" />
							<p>Введите секретный ключ максимум 33 символов </p>
						</div><label>' . $this->l('test_key:') . '</label>
						<div class="margin-form"><input type="text" size="33" maxlength="30" name="t_key" value="' . htmlentities(Tools::getValue('t_key', $this->t_key), ENT_COMPAT, 'UTF-8') . '" />
							<p>Введите тестовый ключ максимум 33 символов </p>
								<button type="submit" value="1" id="module_form_submit_btn" name="ik_submit" class="btn btn-default pull-right">
							<i class="process-icon-save"></i> '. $this->l('Save') .'
						</button>
						</fieldset>
					</form><br /><br />
					<fieldset class="width3">
						<legend><img src="../img/admin/warning.gif" />' . $this->l('Information') . '</legend>
						<b style="color: red;">' . $this->l('how to connect Interkassa:') . '</b><br />
						Зайдите на сайт <b>http://interkassa.com/</b> и пройдите процедуру &quot;Регистрации&quot;.После авторизации заполните поля "Название магазина", URl магазина и нажмите "Добавить (+)". <br />
						<br />В настройках вашей кассы Интеркассы ,во вкладке <b>Интерфейс</b> разрешите переопределение в запросе во всех полях.</p>
						<b style="color: red;">!!! Если Валюта отлична от USD то необхедимо указать "Курс валюты"</b></li>		
					</fieldset>
				</form>';
    }


    public function getContent()
    {
        $this->_html = '<h2>' . $this->displayName . '</h2>';

        if (!empty($_POST)) {
            $this->_postValidation();
            if (!sizeof($this->_postErrors)) {
                $this->_postProcess();
            } else {
                foreach ($this->_postErrors AS $err) {
                    $this->_html .= '<div class="alert error">' . $err . '</div>';
                }
            }
        } else {
            $this->_html .= '<br />';
        }
        $this->_displayInterkassa();
        $this->_displayForm();
        return $this->_html;

    }

    public function hookPayment($params)
    {
        global $smarty;

        if (!$this->active)
            return;

        $id_currency = intval($params['cart']->id_currency);
        $currency = new Currency(intval($id_currency));

        $ik_co_id = Configuration::get('ik_co_id');
        if ($currency->iso_code == 'RUR') {
            $cur = 'RUB';
        } else {
            $cur = $currency->iso_code;
        }
        $ik_am = number_format(Tools::convertPrice($params['cart']->getOrderTotal(true, 3), $currency), 2, '.', '');
        $ik_pm_no = intval($params['cart']->id);
        $ik_desc = '#' . $ik_pm_no;
        $secret_key = Configuration::get('secret_key');

        $arg = [
            'ik_cur' => $cur,
            'ik_co_id' => $ik_co_id,
            'ik_pm_no' => $ik_pm_no,
            'ik_am' => $ik_am,
            'ik_desc' => $ik_desc,
            'ik_ia_u' => 'http://' . htmlspecialchars($_SERVER['HTTP_HOST'], ENT_COMPAT, 'UTF-8') . __PS_BASE_URI__ . 'modules/interkassa/validation.php',
            'ik_suc_u' => 'http://' . htmlspecialchars($_SERVER['HTTP_HOST'], ENT_COMPAT, 'UTF-8') . __PS_BASE_URI__ . 'modules/interkassa/success.php',
            'ik_fal_u' => 'http://' . htmlspecialchars($_SERVER['HTTP_HOST'], ENT_COMPAT, 'UTF-8') . __PS_BASE_URI__ . 'modules/interkassa/fail.php',
            'ik_pnd_u' => 'http://' . htmlspecialchars($_SERVER['HTTP_HOST'], ENT_COMPAT, 'UTF-8') . __PS_BASE_URI__ . 'modules/interkassa/success.php',
        ];

        ksort($arg, SORT_STRING);
        array_push($arg, $secret_key);
        $arg = implode(':', $arg);
        $signature = base64_encode(md5($arg, true));
//        array_push($arg,$signature);

        $smarty->assign(array(
            'ik_co_id' => $ik_co_id,
            'ik_cur' => $cur,
            'ik_am' => $ik_am,
            'ik_desc' => $ik_desc,
            'ik_pm_no' => $ik_pm_no,
            'ik_ia_u' => 'http://' . htmlspecialchars($_SERVER['HTTP_HOST'], ENT_COMPAT, 'UTF-8') . __PS_BASE_URI__ . 'modules/interkassa/validation.php',
            'ik_suc_u' => 'http://' . htmlspecialchars($_SERVER['HTTP_HOST'], ENT_COMPAT, 'UTF-8') . __PS_BASE_URI__ . 'modules/interkassa/success.php',
            'ik_fal_u' => 'http://' . htmlspecialchars($_SERVER['HTTP_HOST'], ENT_COMPAT, 'UTF-8') . __PS_BASE_URI__ . 'modules/interkassa/fail.php',
            'ik_pnd_u' => 'http://' . htmlspecialchars($_SERVER['HTTP_HOST'], ENT_COMPAT, 'UTF-8') . __PS_BASE_URI__ . 'modules/interkassa/success.php',
            'img_path' => $this->_path,
            'ik_sign' => $signature
        ));

        return $this->display(__FILE__, 'interkassa.tpl');
    }
}

?>
