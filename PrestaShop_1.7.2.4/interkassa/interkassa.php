<?php
/**
 * @name Интеркасса
 * @description Модуль разработан в компании GateOn предназначен для CMS Prestashop 1.7.2.4
 * @author tim frio
 * @email web5@marat.ua
 * @last_update 19.12.2017
 * @version 1.1
 */

use PrestaShop\PrestaShop\Core\Payment\PaymentOption;
if(!defined('_PS_VERSION_'))exit;
class Interkassa extends PaymentModule
{
    const IK_CO_ID = '';
    const S_KEY = '';
    const T_KEY = '';

    public function __construct()
    {
      $this->name = 'interkassa';
      $this->tab = 'payments_gateways';
      $this->version = '171219';
      $this->ps_versions_compliancy = array('min' => '1.7.2.4', 'max' => _PS_VERSION_);
      $this->currencies = true;
      $this->currencies_mode = 'radio';

      parent::__construct();

      $this->author = 'Tim Frio';
      $this->page = basename(__FILE__, '.php');
      $this->displayName = $this->l('Interkassa');
      $this->description = $this->getTranslator()->trans('Does this and that', array(), 'Modules.interkassa.Admin');
      $this->description = $this->l('Accepting payments by credit card quickly and safely with Interkassa');
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
        OR !Configuration::updateValue('INTERKASSA_CO_ID', '')
        OR !Configuration::updateValue('INTERKASSA_S_KEY', '')
        OR !Configuration::updateValue('INTERKASSA_T_KEY', '')
        OR !Configuration::updateValue('INTERKASSA_TEST_MODE', 'test')
        OR !Configuration::updateValue('INTERKASSA_PAY_TEXT', 'Pay with Interkassaasdf')
        OR !Configuration::updateValue('INTERKASSA_API_MODE', 'off')
        OR !Configuration::updateValue('INTERKASSA_API_ID', '')
        OR !Configuration::updateValue('INTERKASSA_API_KEY', '')
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
        AND Configuration::deleteByName('INTERKASSA_CO_ID')
        AND Configuration::deleteByName('INTERKASSA_S_KEY')
        AND Configuration::deleteByName('INTERKASSA_T_KEY')
        AND Configuration::deleteByName('INTERKASSA_TEST_MODE')
        AND Configuration::deleteByName('INTERKASSA_PAY_TEXT')
        AND Configuration::deleteByName('INTERKASSA_API_MODE')
        AND Configuration::deleteByName('INTERKASSA_API_ID')
        AND Configuration::deleteByName('INTERKASSA_API_KEY')
        AND Configuration::deleteByName('INTERKASSA_PENDING')
        AND Configuration::deleteByName('INTERKASSA_PAID')
        );
    }

    public function getContent()
    {
      global $cookie;

      if (Tools::isSubmit('submitInterkassa')) {
        if ($ik_text = Tools::getValue('interkassa_pay_text')) Configuration::updateValue('INTERKASSA_PAY_TEXT', $ik_text);
        if ($ik_co_id = Tools::getValue('ik_co_id')) Configuration::updateValue('INTERKASSA_CO_ID', $ik_co_id);
        if ($s_key = Tools::getValue('s_key')) Configuration::updateValue('INTERKASSA_S_KEY', $s_key);
        if ($t_key = Tools::getValue('t_key')) Configuration::updateValue('INTERKASSA_T_KEY', $t_key);
        if ($ik_test_mode = Tools::getValue('ik_test_mode')) Configuration::updateValue('INTERKASSA_TEST_MODE', $ik_test_mode);
        if ($ik_api_mode = Tools::getValue('api_mode')) Configuration::updateValue('INTERKASSA_API_MODE', $ik_api_mode);
        if ($ik_api_id = Tools::getValue('api_id')) Configuration::updateValue('INTERKASSA_API_ID', $ik_api_id);
        if ($ik_api_key = Tools::getValue('api_key')) Configuration::updateValue('INTERKASSA_API_KEY', $ik_api_key);

        }
        $html = '<div style="width:550px">
           <p style="text-align:center;">
               <a href="https://www.interkassa.com/" target="_blank">
                <img  src="' . __PS_BASE_URI__ . 'modules/interkassa/interkassa.png" alt="Interkassa2" border="0" width="300px" align="center " />
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
                <option value="live"' . (Configuration::get('INTERKASSA_TEST_MODE') == 'live' ? ' selected="selected"' : '') . '>' . $this->l('Work mode')
            . '&nbsp;&nbsp;
                </option>
                <option value="test"' . (Configuration::get('INTERKASSA_TEST_MODE') == 'test' ? ' selected="selected"' : '') . '>' . $this->l('Test mode')
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
              <input type="text" name="ik_co_id" value="' . Tools::getValue('INTERKASSA_CO_ID', Configuration::get('INTERKASSA_CO_ID')) . '" />
            </div>
            <label>
              ' . $this->l('Secret key') . '
            </label>
            <div class="margin-form">
              <input type="text" name="s_key" value="' . trim(Tools::getValue('INTERKASSA_S_KEY', Configuration::get('INTERKASSA_S_KEY'))) . '" />
            </div>
            <p>' . $this->l('Secret security key can be found in the Security tab in the settings of your cash desk') . '</p>' .
            '<label>
            ' . $this->l('Test key') . '
             </label>
            <div class="margin-form">
              <input type="text" name="t_key" value="' . trim(Tools::getValue('INTERKASSA_T_KEY', Configuration::get('INTERKASSA_T_KEY'))) . '" />
            </div>
            <p>' . $this->l('Test security key can be found in the Security tab in the settings of your cash desk') . '</p>
            <label>
            ' . $this->l('The text of the form of payment') . '
            </label>
             <div class="margin-form" style="margin-top:5px">
               <input type="text" name="interkassa_pay_text" value="' . Configuration::get('INTERKASSA_PAY_TEXT') . '">
             </div><br>
             <label>
             ' . $this->l('Preview') . '
             </label>
                  <div align="center">' . Configuration::get('INTERKASSA_PAY_TEXT') . '&nbsp&nbsp
                  <img width="100px" alt="Pay via Interkassa" title="Pay via Interkassa" src="' . __PS_BASE_URI__
            . 'modules/interkassa/interkassa.png">
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
                <option value="on"' . (Configuration::get('INTERKASSA_API_MODE') == 'on'||true ? ' selected="selected"' : '') . '>' . $this->l('ON')
            . '&nbsp;&nbsp;
                </option>
                <option value="off"' . (Configuration::get('INTERKASSA_API_MODE') == 'off' ? ' selected="selected"' : '') . '>' . $this->l('OFF')
            . '&nbsp;
                &nbsp;
                </option>
              </select>
            </div>

              <label>
              ' . $this->l('Interkassa API Id') . '
            </label>
            <div class="margin-form">
              <input type="text"' . (Configuration::get('INTERKASSA_API_MODE') == 'on' ? ' required="required"' : '') . '  name="api_id" value="' . trim(Tools::getValue('INTERKASSA_API_ID',
                Configuration::get('INTERKASSA_API_ID'))) .
            '"  />
            </div>
            <label>
              ' . $this->l('Interkassa API Key') . '
            </label>
            <div class="margin-form">
              <input type="text" name="api_key"' . (Configuration::get('INTERKASSA_API_MODE') == 'on' ? ' required="required"' : '') . '  value="'
            . trim(Tools::getValue('INTERKASSA_API_KEY', Configuration::get('INTERKASSA_API_KEY'))) .
            '" />
            </div>
            <div style="float:right;"><input type="submit" name="submitInterkassa" class="button btn btn-default pull-right" value="' . $this->l('Save') . '" /></div><div
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
        $s_key = Configuration::get('INTERKASSA_S_KEY');

        $data = array();

        if (Configuration::get('INTERKASSA_TEST_MODE') == 'test') {
            $data['fields']['ik_pw_via'] = 'test_interkassa_test_xts';
        }
        $data['INTERKASSA_PAY_TEXT'] = Configuration::get('INTERKASSA_PAY_TEXT');
        $data['fields']['ik_co_id'] = Configuration::get('INTERKASSA_CO_ID');
        $data['fields']['ik_pm_no'] = $cart->id;
        $data['fields']['ik_desc'] = '#' . $cart->id;
        $data['fields']['ik_am'] = number_format(sprintf("%01.2f", $total), 2, '.', '');
        $data['fields']['ik_cur'] = $currency->iso_code;
        $data['fields']['ik_suc_u'] = Tools::getHttpHost(true) . __PS_BASE_URI__ . 'modules/interkassa/validation.php';
        #$this->context->link->getPageLink('order-confirmation',null, null, 'key=' . $cart->secure_key . '&id_cart=' . (int)
        #    ($cart->id) . '&id_module=' . (int)($this->id));
        $data['fields']['ik_fal_u'] = Tools::getHttpHost(true) . __PS_BASE_URI__ . 'modules/interkassa/fail.php';
        $data['fields']['ik_pnd_u'] = Tools::getHttpHost(true) . __PS_BASE_URI__ . 'modules/interkassa/fail.php';
        $data['fields']['ik_ia_u'] = Tools::getHttpHost(true) . __PS_BASE_URI__ . 'modules/interkassa/validation.php';

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
        if (Configuration::get('INTERKASSA_TEST_MODE') == 'test') {
            $form['ik_pw_via'] = ['name' => 'ik_pw_via',
                'type' => 'hidden',
                'value' => $data['fields']['ik_pw_via'],
            ];
        }

        if (Configuration::get('INTERKASSA_API_MODE') == 'on') {
            $payment_systems = $this->getIkPaymentSystems(Configuration::get('INTERKASSA_CO_ID'),Configuration::get('INTERKASSA_API_ID'),
                Configuration::get('INTERKASSA_API_KEY'));
        }


        $externalOption = new PaymentOption();
        $externalOption->setCallToActionText($this->l('Оплатить через Интеркассу'))
            ->setAction('https://sci.interkassa.com/')
            ->setInputs($form)
            ->setAdditionalInformation($this->context->smarty->assign(array(
              'ik_co_id'=>Configuration::get('INTERKASSA_CO_ID'),
              'ik_pm_no'=>$cart->id,
              'ik_desc'=>'#'.$cart->id,
              'ik_am'=>number_format(sprintf("%01.2f", $total), 2, '.', ''),
              'ik_cur'=>$currency->iso_code,
              'ik_suc_u'=>Tools::getHttpHost(true).__PS_BASE_URI__.'modules/interkassa/validation.php',
              'ik_fal_u'=>Tools::getHttpHost(true).__PS_BASE_URI__.'modules/interkassa/fail.php',
              'ik_pnd_u'=>Tools::getHttpHost(true).__PS_BASE_URI__.'modules/interkassa/fail.php',
              'ik_ia_u'=>Tools::getHttpHost(true).__PS_BASE_URI__.'modules/interkassa/validation.php',
              'ik_sign'=>$signature,
                'api_mode'=>Configuration::get('INTERKASSA_API_MODE'),
                'mode'=>Configuration::get('INTERKASSA_TEST_MODE'),
                'payment_systems'=>$payment_systems,
                'ajax_url'=> Tools::getHttpHost(true) . __PS_BASE_URI__ . 'modules/interkassa/ajax.php',
                'ik_dir'=> Tools::getHttpHost(true) . __PS_BASE_URI__ . 'modules/interkassa/',
                'shop_cur'=>$currency->iso_code
            ))->fetch('module:interkassa/interkassa2_info.tpl'))
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
                'status' => $_POST['ik_inv_st'],
                'reference' => $params['order']->reference,
                'contact_url' => $this->context->link->getPageLink('contact', true)
            ));
            return $this->fetch('module:interkassa/interkassa2_notification.tpl');
        }

    }

  public static function IkSignFormation($data)
  {
    if(!empty($data['ik_sign']))unset($data['ik_sign']);

    $dataSet = array();
    foreach ($data as $key => $value) {
      if (!preg_match('/ik_/', $key)) continue;
      $dataSet[$key] = $value;
    }

    ksort($dataSet, SORT_STRING);
    array_push($dataSet, Configuration::get('INTERKASSA_S_KEY'));
    $arg = implode(':', $dataSet);
    $ik_sign = base64_encode(md5($arg, true));

    return $ik_sign;
  }
  public static function getAnswerFromAPI($data)
  {
    $ch = curl_init('https://sci.interkassa.com/');
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    return curl_exec($ch);
  }
    public function getIkPaymentSystems($ik_co_id,$ik_api_id,$ik_api_key)
    {
      $username = $ik_api_id;
      $password = $ik_api_key;
      $remote_url = 'https://api.interkassa.com/v1/paysystem-input-payway?checkoutId='.$ik_co_id;

      // Create a stream
      $opts = array(
        'http'=>array(
          'method'=>"GET",
          'header' => "Authorization: Basic " . base64_encode("$username:$password")
        )
      );

      $context = stream_context_create($opts);
      $file = file_get_contents($remote_url, false, $context);
      $json_data=json_decode($file);

      $payment_systems = array();
      foreach ($json_data->data as $ps => $info){
        $payment_system = $info->ser;
        if(!array_key_exists($payment_system,$payment_systems)){
          $payment_systems[$payment_system] = array();
          foreach ($info->name as $name){
            //ВЫБРАЛИ ТОЛЬКО АНГЛИЙСКИЙ ПЕРЕВОД ТАК КАК ОН ЕСТЬ У ВСЕХ МЕТОДОВ
            if($name->l == 'en'){
              $payment_systems[$payment_system]['title'] = ucfirst($name->v);
            }
            $payment_systems[$payment_system]['name'][$name->l] = $name->v;
          }
        }
        $payment_systems[$payment_system]['currency'][strtoupper($info->curAls)] = $info->als;
      }
      return $payment_systems;
    }
}
