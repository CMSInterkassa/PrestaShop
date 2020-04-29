<?php
/**
 * @name Интеркасса 2.0
 * @description Модуль предназначен для CMS Prestashop 1.7.x
 * @author interkassa
 * @last_update 29.04.2020
 * @version 1.3
 */

use PrestaShop\PrestaShop\Core\Payment\PaymentOption;

if (!defined('_PS_VERSION_'))
    exit;

class Interkassa2 extends PaymentModule
{
    const IK_CO_ID = '';
    const S_KEY = '';
    const T_KEY = '';

    public function __construct()
    {
        $this->name = 'interkassa2';
        $this->tab = 'payments_gateways';
        $this->version = '1.2';
        $this->ps_versions_compliancy = array('min' => '1.7', 'max' => _PS_VERSION_);
        $this->currencies = true;
        $this->currencies_mode = 'radio';

        parent::__construct();

        $this->author = 'GateOn';
        $this->page = basename(__FILE__, '.php');
        $this->displayName = $this->l('Interkassa 2.0');
        $this->description = $this->getTranslator()->trans('Does this and that', array(), 'Modules.interkassa2.Admin');
        $this->description = $this->l('Accepting payments by credit card quickly and safely with Interkassa 2.0');
        $this->confirmUninstall = $this->l('Are you sure you want to delete all the settings?');

    }

    public function install()
    {
        //При установке будет создан новый статус заказа для pending
        $ikStatePending = new OrderState();
        foreach (Language::getLanguages() AS $language)
        {
            $ikStatePending->name[$language['id_lang']] = 'Pending payment of Interkassa';
        }
        $ikStatePending ->send_mail = 0;
        $ikStatePending ->template = "interkassa2";
        $ikStatePending ->invoice = 1;
        $ikStatePending ->color = "#007cf9";
        $ikStatePending ->unremovable = false;
        $ikStatePending ->logable = 0;
        $ikStatePending ->add();

        //При установке будет создан новый статус заказа для оплаты после pending
        $ikStatePaid = new OrderState();
        foreach (Language::getLanguages() AS $language)
        {
            $ikStatePaid->name[$language['id_lang']] = 'Paid via Interkassa';
        }
        $ikStatePaid ->send_mail = 1;
        $ikStatePaid ->template = "interkassa2";
        $ikStatePaid ->invoice = 1;
        $ikStatePaid ->color = "#27ae60";
        $ikStatePaid ->unremovable = false;
        $ikStatePaid ->logable = 1;
        $ikStatePaid ->paid = 1;
        $ikStatePaid ->add();

        if (!parent::install()
            OR !$this->registerHook('paymentOptions')
            OR !$this->registerHook('paymentReturn')
            OR !Configuration::updateValue('INTERKASSA2_CO_ID', '')
            OR !Configuration::updateValue('INTERKASSA2_S_KEY', '')
            OR !Configuration::updateValue('INTERKASSA2_T_KEY', '')
            OR !Configuration::updateValue('INTERKASSA2_TEST_MODE', 'test')
            OR !Configuration::updateValue('INTERKASSA2_PAY_TEXT', 'Pay with Interkassa')
            OR !Configuration::updateValue('INTERKASSA2_API_MODE', 'off')
            OR !Configuration::updateValue('INTERKASSA2_API_ID', '')
            OR !Configuration::updateValue('INTERKASSA2_API_KEY', '')
            OR !Configuration::updateValue('INTERKASSA_PENDING',$ikStatePending->id)
            OR !Configuration::updateValue('INTERKASSA_PAID',$ikStatePaid->id)
        ) {
            return false;
        }


        return true;
    }

    public function uninstall()
    {
        return (parent::uninstall()
            AND Configuration::deleteByName('INTERKASSA2_CO_ID')
            AND Configuration::deleteByName('INTERKASSA2_S_KEY')
            AND Configuration::deleteByName('INTERKASSA2_T_KEY')
            AND Configuration::deleteByName('INTERKASSA2_TEST_MODE')
            AND Configuration::deleteByName('INTERKASSA2_PAY_TEXT')
            AND Configuration::deleteByName('INTERKASSA2_API_MODE')
            AND Configuration::deleteByName('INTERKASSA2_API_ID')
            AND Configuration::deleteByName('INTERKASSA2_API_KEY')
            AND Configuration::deleteByName('INTERKASSA_PENDING')
            AND Configuration::deleteByName('INTERKASSA_PAID')
        );
    }

    public function getContent()
    {
        global $cookie;

        if (Tools::isSubmit('submitInterkassa2')) {
            if ($ik_text = Tools::getValue('interkassa2_pay_text')) Configuration::updateValue('INTERKASSA2_PAY_TEXT', $ik_text);
            if ($ik_co_id = Tools::getValue('ik_co_id')) Configuration::updateValue('INTERKASSA2_CO_ID', $ik_co_id);
            if ($s_key = Tools::getValue('s_key')) Configuration::updateValue('INTERKASSA2_S_KEY', $s_key);
            if ($t_key = Tools::getValue('t_key')) Configuration::updateValue('INTERKASSA2_T_KEY', $t_key);
            if ($ik_test_mode = Tools::getValue('ik_test_mode')) Configuration::updateValue('INTERKASSA2_TEST_MODE', $ik_test_mode);
            if ($ik_api_mode = Tools::getValue('api_mode')) Configuration::updateValue('INTERKASSA2_API_MODE', $ik_api_mode);
            if ($ik_api_id = Tools::getValue('api_id')) Configuration::updateValue('INTERKASSA2_API_ID', $ik_api_id);
            if ($ik_api_key = Tools::getValue('api_key')) Configuration::updateValue('INTERKASSA2_API_KEY', $ik_api_key);

        }
        $html = '<div style="width:550px">
           <p style="text-align:center;">
               <a href="https://www.interkassa.com/" target="_blank">
                <img  src="' . __PS_BASE_URI__ . 'modules/interkassa2/interkassa.png" alt="Interkassa2" border="0" width="300px" align="center " />
               </a>
            </p>
        <form action="' . $_SERVER['REQUEST_URI'] . '" method="post">
          <fieldset>
          <legend><img width="20px" src="' . __PS_BASE_URI__ . 'modules/interkassa2/logo.gif" />' . $this->l('Settings') . '</legend>
            <p>' . $this->l('Use the test mode to go directly to the test payment system, without the possibility of choice of other payment systems') . '
            </p>
            <label>
              ' . $this->l('Mode') . '
            </label>
            <div class="margin-form" style="width:110px;">
              <select name="ik_test_mode">
                <option value="live"' . (Configuration::get('INTERKASSA2_TEST_MODE') == 'live' ? ' selected="selected"' : '') . '>' . $this->l('Work mode')
            . '&nbsp;&nbsp;
                </option>
                <option value="test"' . (Configuration::get('INTERKASSA2_TEST_MODE') == 'test' ? ' selected="selected"' : '') . '>' . $this->l('Test mode')
            . '&nbsp;
                &nbsp;
                </option>
              </select>
            </div>
            <p>' . $this->l('ID cash desk, you can find in the settings of your cash desk next to its name') . '</p>
            <label>
              ' . $this->l('ID cash desk') . '
            </label>
            <div class="margin-form">
              <input type="text" name="ik_co_id" value="' . Tools::getValue('INTERKASSA2_CO_ID', Configuration::get('INTERKASSA2_CO_ID')) . '" />
            </div>
            <label>
              ' . $this->l('Secret key') . '
            </label>
            <div class="margin-form">
              <input type="text" name="s_key" value="' . trim(Tools::getValue('INTERKASSA2_S_KEY', Configuration::get('INTERKASSA2_S_KEY'))) . '" />
            </div> 
            <p>' . $this->l('Secret security key can be found in the Security tab in the settings of your cash desk') . '</p>' .
            '<label>
            ' . $this->l('Test key') . '
             </label>
            <div class="margin-form">
              <input type="text" name="t_key" value="' . trim(Tools::getValue('INTERKASSA2_T_KEY', Configuration::get('INTERKASSA2_T_KEY'))) . '" />
            </div> 
            <p>' . $this->l('Test security key can be found in the Security tab in the settings of your cash desk') . '</p>
            <label>
            ' . $this->l('The text of the form of payment') . '
            </label>
             <div class="margin-form" style="margin-top:5px">
               <input type="text" name="interkassa2_pay_text" value="' . Configuration::get('INTERKASSA2_PAY_TEXT') . '">
             </div><br>
             <label>
             ' . $this->l('Preview') . '
             </label>
                  <div align="center">' . Configuration::get('INTERKASSA2_PAY_TEXT') . '&nbsp&nbsp
                  <img width="100px" alt="Pay via Interkassa" title="Pay via Interkassa" src="' . __PS_BASE_URI__
            . 'modules/interkassa2/interkassa.png">
                    </div><br>
               <h2>' . $this->l('You are able to use convenient choice of payment system on the payment methods selection page'). '</h2>
               <p>' . $this->l('API settings locate in your Interkassa account settings in API section'). '</p>
               <p>' . $this->l('To use Interkassa API select API mode. On the payment methods selection page you will see button') . '
            </p>
            <label>
              ' . $this->l('API mode') . '
            </label>
            <div class="margin-form" style="width:110px;">
              <select name="api_mode">
                <option value="on"' . (Configuration::get('INTERKASSA2_API_MODE') == 'on' ? ' selected="selected"' : '') . '>' . $this->l('ON')
            . '&nbsp;&nbsp;
                </option>
                <option value="off"' . (Configuration::get('INTERKASSA2_API_MODE') == 'off' ? ' selected="selected"' : '') . '>' . $this->l('OFF')
            . '&nbsp;
                &nbsp;
                </option>
              </select>
            </div>
               
              <label>
              ' . $this->l('Interkassa API Id') . '
            </label>
            <div class="margin-form">
              <input type="text"' . (Configuration::get('INTERKASSA2_API_MODE') == 'on' ? ' required="required"' : '') . '  name="api_id" value="' . trim(Tools::getValue('INTERKASSA2_API_ID',
                Configuration::get('INTERKASSA2_API_ID'))) .
            '"  />
            </div> 
            <label>
              ' . $this->l('Interkassa API Key') . '
            </label>
            <div class="margin-form">
              <input type="text" name="api_key"' . (Configuration::get('INTERKASSA2_API_MODE') == 'on' ? ' required="required"' : '') . '  value="'
            . trim(Tools::getValue('INTERKASSA2_API_KEY', Configuration::get('INTERKASSA2_API_KEY'))) .
            '" />
            </div>    
            <div style="float:right;"><input type="submit" name="submitInterkassa2" class="button btn btn-default pull-right" value="' . $this->l('Save') . '" /></div><div 
            class="clear"></div>
          </fieldset>
        </form>
        <br /><br />
        <fieldset>
          <legend><img src="../img/admin/warning.gif" />' . $this->l('Information') . '</legend>
          <p>- ' . $this->l('To use this payment module, you must specify the cash desk ID, the private key and to test - test security key.') . '
          </p>
         </fieldset>
        </div>';

        return $html;
    }

    //Возвращает новый способ оплаты
    public function hookPaymentOptions($params)
    {
        if (!$this->active) {
            return;
        }
        $payment_options = [
            $this->getCardPaymentOption()
        ];
        return $payment_options;
    }

    public function getCardPaymentOption()
    {
        global $cookie, $cart;


        $total = $cart->getOrderTotal();
        $currency = $this->getCurrency((int)$cart->id_currency);
        $s_key = Configuration::get('INTERKASSA2_S_KEY');

        $data = array();

        if (Configuration::get('INTERKASSA2_TEST_MODE') == 'test') {
            $data['fields']['ik_pw_via'] = 'test_interkassa_test_xts';
        }
        $data['INTERKASSA2_PAY_TEXT'] = Configuration::get('INTERKASSA2_PAY_TEXT');
        $data['fields']['ik_co_id'] = Configuration::get('INTERKASSA2_CO_ID');
        $data['fields']['ik_pm_no'] = $cart->id;
        $data['fields']['ik_desc'] = '#' . $cart->id;
        $data['fields']['ik_am'] = number_format(sprintf("%01.2f", $total), 2, '.', '');
        $data['fields']['ik_cur'] = $currency->iso_code;
        $data['fields']['ik_suc_u'] = $this->context->link->getPageLink('order-confirmation', null, null, 'key=' . $cart->secure_key . '&id_cart=' . (int)
            ($cart->id) . '&id_module=' . (int)($this->id));
        $data['fields']['ik_fal_u'] = Tools::getHttpHost(true) . __PS_BASE_URI__ . 'modules/interkassa2/fail.php';
        $data['fields']['ik_pnd_u'] = Tools::getHttpHost(true) . __PS_BASE_URI__ . 'modules/interkassa2/fail.php';
        $data['fields']['ik_ia_u'] = Tools::getHttpHost(true) . __PS_BASE_URI__ . 'modules/interkassa2/validation.php';

        ksort($data['fields'], SORT_STRING);
        array_push($data['fields'], $s_key);
        $arg = implode(':', $data['fields']);
        $signature = base64_encode(md5($arg, true));


        $data['fields']['ik_sign'] = $signature;


        $form = [
            'ik_co_id' => ['name' => 'ik_co_id',
                'type' => 'hidden',
                'value' => $data['fields']['ik_co_id'],
            ],

            'ik_pm_no' => ['name' => 'ik_pm_no',
                'type' => 'hidden',
                'value' => $data['fields']['ik_pm_no'],
            ],

            'ik_desc' => ['name' => 'ik_desc',
                'type' => 'hidden',
                'value' => $data['fields']['ik_desc'],
            ],

            'ik_am' => ['name' => 'ik_am',
                'type' => 'hidden',
                'value' => $data['fields']['ik_am'],
            ],

            'ik_cur' => ['name' => 'ik_cur',
                'type' => 'hidden',
                'value' => $data['fields']['ik_cur'],
            ],

            'ik_suc_u' => ['name' => 'ik_suc_u',
                'type' => 'hidden',
                'value' => $data['fields']['ik_suc_u'],
            ],

            'ik_fal_u' => ['name' => 'ik_fal_u',
                'type' => 'hidden',
                'value' => $data['fields']['ik_fal_u'],
            ],

            'ik_pnd_u' => ['name' => 'ik_pnd_u',
                'type' => 'hidden',
                'value' => $data['fields']['ik_pnd_u'],
            ],

            'ik_ia_u' => ['name' => 'ik_ia_u',
                'type' => 'hidden',
                'value' => $data['fields']['ik_ia_u'],
            ],
            'ik_sign' => ['name' => 'ik_sign',
                'type' => 'hidden',
                'value' => $data['fields']['ik_sign'],
            ],

        ];
        if (Configuration::get('INTERKASSA2_TEST_MODE') == 'test') {
            $form['ik_pw_via'] = ['name' => 'ik_pw_via',
                'type' => 'hidden',
                'value' => $data['fields']['ik_pw_via'],
            ];
        }

        if (Configuration::get('INTERKASSA2_API_MODE') == 'on') {
            $payment_systems = $this->getIkPaymentSystems(Configuration::get('INTERKASSA2_CO_ID'),Configuration::get('INTERKASSA2_API_ID'),
                Configuration::get('INTERKASSA2_API_KEY'));
        }


        $externalOption = new PaymentOption();
        $externalOption->setCallToActionText($this->l(Configuration::get('INTERKASSA2_PAY_TEXT')))
            ->setAction('https://sci.interkassa.com/')
            ->setInputs($form)
            ->setAdditionalInformation($this->context->smarty->assign(array(
                'api_mode'=>Configuration::get('INTERKASSA2_API_MODE'),
                'payment_systems'=>$payment_systems,
                'ajax_url'=> Tools::getHttpHost(true) . __PS_BASE_URI__ . 'modules/interkassa2/ajax.php',
                'ik_dir'=> Tools::getHttpHost(true) . __PS_BASE_URI__ . 'modules/interkassa2/',
                'shop_cur'=>$currency->iso_code
            ))->fetch('module:interkassa2/interkassa2_info.tpl'))
            ->setLogo(Media::getMediaPath(_PS_MODULE_DIR_ . $this->name . '/payment.png'));

        return $externalOption;
    }

    public function hookPaymentReturn($params)
    {
        if(!empty($_POST)){
            $this->context->smarty->assign(array(
                'shop_name' => $this->context->shop->name,
                'total' => Tools::displayPrice(
                    $params['order']->getOrdersTotalPaid(),
                    new Currency($params['order']->id_currency),
                    false
                ),
                'status' => 'success',
                'reference' => $params['order']->reference,
                'contact_url' => $this->context->link->getPageLink('contact', true)
            ));
            return $this->fetch('module:interkassa2/interkassa2_notification.tpl');
        }
    }

    public static function IkSignFormation($data, $useSecret = true)
	{
        if (!empty($data['ik_sign'])) unset($data['ik_sign']);

		$dataSet = array();
        foreach ($data as $key => $value) {
			if (!preg_match('/ik_/', $key)) continue;

            $dataSet[$key] = $value;
        }
        ksort($dataSet, SORT_STRING);
		$key = $useSecret ? Configuration::get('INTERKASSA2_S_KEY') : Configuration::get('INTERKASSA2_T_KEY');
        array_push($dataSet, $key);
        $arg = implode(':', $dataSet);
        $ik_sign = base64_encode(md5($arg, true));

        return $ik_sign;
    }

    public function getIkPaymentSystems($ik_co_id, $ik_api_id,$ik_api_key)
    {
        $username = $ik_api_id;
        $password = $ik_api_key;
        $remote_url = 'https://api.interkassa.com/v1/paysystem-input-payway?checkoutId=' . $ik_co_id;


        $businessAcc = $this->getIkBusinessAcc($username, $password);


        $ikHeaders = [];
        $ikHeaders[] = "Authorization: Basic " . base64_encode("$username:$password");
        if (!empty($businessAcc)) {
            $ikHeaders[] = "Ik-Api-Account-Id: " . $businessAcc;
        }


        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $remote_url);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $ikHeaders);
        $response = curl_exec($ch);

        $json_data = json_decode($response);
        if (empty($json_data))
            return '<strong style="color:red;">Error!!! System response empty!</strong>';

        if ($json_data->status != 'error') {
            $payment_systems = array();
            if (!empty($json_data->data)) {

                foreach ($json_data->data as $ps => $info) {
                    $payment_system = $info->ser;
                    if (!array_key_exists($payment_system, $payment_systems)) {
                        $payment_systems[$payment_system] = array();
                        foreach ($info->name as $name) {
                            if ($name->l == 'en') {
                                $payment_systems[$payment_system]['title'] = ucfirst($name->v);
                            }
                            $payment_systems[$payment_system]['name'][$name->l] = $name->v;
                        }
                    }
                    $payment_systems[$payment_system]['currency'][strtoupper($info->curAls)] = $info->als;
                }
            }

            return !empty($payment_systems) ? $payment_systems : '<strong style="color:red;">API connection error or system response empty!</strong>';
        } else {
            if (!empty($json_data->message))
                return '<strong style="color:red;">API connection error!<br>' . $json_data->message . '</strong>';
            else
                return '<strong style="color:red;">API connection error or system response empty!</strong>';
        }
    }
    
    public function getIkBusinessAcc($username = '', $password = '')
    {
        $tmpLocationFile = __DIR__ . '/tmpLocalStorageBusinessAcc.ini';
        $dataBusinessAcc = function_exists('file_get_contents') ? file_get_contents($tmpLocationFile) : '{}';
        $dataBusinessAcc = json_decode($dataBusinessAcc, 1);
        $businessAcc = is_string($dataBusinessAcc['businessAcc']) ? trim($dataBusinessAcc['businessAcc']) : '';
        if (empty($businessAcc) || sha1($username . $password) !== $dataBusinessAcc['hash']) {
            $curl = curl_init();
            curl_setopt($curl, CURLOPT_URL, 'https://api.interkassa.com/v1/' . 'account');
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($curl, CURLOPT_FOLLOWLOCATION, false);
            curl_setopt($curl, CURLOPT_HEADER, false);
            curl_setopt($curl, CURLOPT_HTTPHEADER, ["Authorization: Basic " . base64_encode("$username:$password")]);
            $response = curl_exec($curl);
            $response = json_decode($response, 1);


            if (!empty($response['data'])) {
                foreach ($response['data'] as $id => $data) {
                    if ($data['tp'] == 'b') {
                        $businessAcc = $id;
                        break;
                    }
                }
            }

            if (function_exists('file_put_contents')) {
                $updData = [
                    'businessAcc' => $businessAcc,
                    'hash' => sha1($username . $password)
                ];
                file_put_contents($tmpLocationFile, json_encode($updData, JSON_PRETTY_PRINT));
            }

            return $businessAcc;
        }

        return $businessAcc;
    }
        
    public function wrlog($text)
    {
        $tmpLocationFile = __DIR__ . '/log.txt';
        file_put_contents($tmpLocationFile, $text);
    }
}

