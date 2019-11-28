<?php

/**
 * @name Интеркасса 2.0
 * @description Модуль разработан в компании GateOn предназначен для CMS Prestashop 1.6.1.12
 * @author www.gateon.net
 * @email www@smartbyte.pro
 * @version 1.5
 * @update 25.05.2017
 */

class Interkassa extends PaymentModule
{
    private $_html = '';
    private $_postErrors = array();

    public function __construct()
    {
        $this->name = 'interkassa';
        $this->tab = 'payments_gateways';
        $this->version = '1.5';
        $this->author = 'GateOn';
        $this->currencies = true;
        $this->currencies_mode = 'checkbox';

        $config = Configuration::getMultiple(array(
                'ik_co_id',
                'secret_key',
                'test_key',
                'api_mode',
                'api_id',
                'api_key',
                )
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
        if(isset($config['test_key'])){
            $this->test_mode = $config['test_mode'];
        }
        if (isset($config['api_mode'])) {
            $this->api_mode = $config['api_mode'];
        }
        if (isset($config['api_id'])) {
            $this->api_id = $config['api_id'];
        }
        if (isset($config['api_key'])) {
            $this->api_key = $config['api_key'];
        }
        parent::__construct();



        $this->displayName = $this->l('Interkassa2');
        $this->description = $this->l('Pay with Interkassa');
        $this->confirmUninstall = $this->l('Are you sure you want to remove?');
    }

    public function install()
    {
        //При установке будет создан новый статус заказа для оплаты после pending
        $ikStatePaid = new OrderState();
        foreach (Language::getLanguages() AS $language)
        {
            if (strtolower($language['iso_code']) == 'ru')
                $ikStatePaid->name[$language['id_lang']] = 'Оплачено с помощью Интеркассы';
            else
                $ikStatePaid->name[$language['id_lang']] = 'Paid with Interkassa';
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
            OR !$this->registerHook('payment')
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
        )
            return false;
        return true;
    }

    public function uninstall()
    {
        if (!Configuration::deleteByName('ik_co_id')
            OR !Configuration::deleteByName('secret_key')
            OR !Configuration::deleteByName('test_key')
            OR !Configuration::deleteByName('api_mode')
            OR !Configuration::deleteByName('INTERKASSA2_TEST_MODE')
            OR !Configuration::deleteByName('api_id')
            OR !Configuration::deleteByName('api_key')
            OR !Configuration::deleteByName('INTERKASSA_PAID')
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
                $this->_postErrors[] = $this->l('add test key');
            }
            if (empty($_POST['api_mode'])){
                $this->_postErrors[] = $this->l('add api mode');
            }
            if (empty($_POST['api_id'])){
                $this->_postErrors[] = $this->l('add api id');
            }
            if (empty($_POST['api_key'])){
                $this->_postErrors[] = $this->l('add api key');
            }
        }
    }

    private function _postProcess()
    {
        if (isset($_POST['ik_submit'])) {
            Configuration::updateValue('ik_co_id', $_POST['ik_co_id']);
            Configuration::updateValue('secret_key', $_POST['s_key']);
            Configuration::updateValue('test_key', $_POST['t_key']);
            Configuration::updateValue('api_mode', $_POST['api_mode']);
            Configuration::updateValue('api_id', $_POST['api_id']);
            Configuration::updateValue('api_key', $_POST['api_key']);
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
         if ($ik_test_mode = Tools::getValue('ik_test_mode')) Configuration::updateValue('INTERKASSA2_TEST_MODE', $ik_test_mode);
        $this->_html .='
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
            <div><label>' . $this->l('ik_shop_id:') . '</label>
                <div class="margin-form"><input type="text" size="33" maxlength="36" name="ik_co_id" value="' . htmlentities(Tools::getValue('ik_co_id', $this->ik_co_id), ENT_COMPAT, 'UTF-8') . '" />
                    <p>'.$this->l('No more than').'</p></div>
                    <div><label>' . $this->l('secret_key:') . '</label>
                        <div class="margin-form"><input type="text" size="33" maxlength="30" name="s_key" value="' . htmlentities(Tools::getValue
            ('s_key', $this->s_key), ENT_COMPAT, 'UTF-8') . '" />
                            <p>'.$this->l('No more than').'</p>
                        </div><label>' . $this->l('test_key:') . '</label>
                        <div class="margin-form"><input type="text" size="33" maxlength="30" name="t_key" value="' . htmlentities(Tools::getValue('t_key', $this->t_key), ENT_COMPAT, 'UTF-8') . '" />
                            <p>'.$this->l('No more than').'</p></div>
                    <div class="margin-form">       
                <h2><strong>' . $this->l('Use new Interkassa API'). '</strong></h2>
               <h3>' . $this->l('API settings locate in your Interkassa account settings in API section'). '</h3>
               <h3>' . $this->l('To use Interkassa API select API mode. On the payment methods selection page you will see button') . '
            </h3>
            </div>
            <label>
              ' . $this->l('API mode') . '
            </label>
            <div class="margin-form">
              <select name="api_mode">
                <option value="no_selected"'.(htmlentities(Tools::getValue('api_mode', $this->api_mode)) == 'no_selected' ? ' selected="selected"' : '') .
            '>' . $this->l('no_selected_api_mode') .'</option>
                <option value="on"' . (htmlentities(Tools::getValue('api_mode', $this->api_mode)) == 'on' ? ' selected="selected"' : '') . '>' . $this->l('ON')
            . '</option>
                <option value="off"' . (htmlentities(Tools::getValue('api_mode', $this->api_mode)) == 'off' ? ' selected="selected"' : '') . '>' . $this->l('OFF')
            . '</option>
              </select>
            </div>
              <label>
              ' . $this->l('Interkassa API Id') . '
            </label>
            <div class="margin-form">
              <input type="text"' . (htmlentities(Tools::getValue('api_id', $this->api_id)) == 'on' ? ' required="required"' : '') . '  name="api_id" value="' . htmlentities(Tools::getValue('api_id', $this->api_id)). '"  />
            </div> 
            <label>
              ' . $this->l('Interkassa API Key') . '
            </label>
            <div class="margin-form">
              <input type="text"' . (htmlentities(Tools::getValue('api_key', $this->api_key)) == 'on' ? ' required="required"' : '') . '  name="api_key" value="' . htmlentities(Tools::getValue('api_key', $this->api_key)). '"  />
            </div> 
                                <button type="submit" value="1" id="module_form_submit_btn" name="ik_submit" class="btn btn-default pull-right">
                            <i class="process-icon-save"></i> '. $this->l('Save') .'
                        </button>
                        </fieldset>
                    </form><br /><br />
                    <fieldset class="width3">
                        <legend><img src="../img/admin/warning.gif" />' . $this->l('Information') . '</legend>
                        <b style="color: red;">' . $this->l('additional information') . '</b>
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

        $protocol = isset($_SERVER["HTTPS"]) ? 'https://' : 'http://';
        

        $parameters = array(
            'ik_cur' => $cur,
            'ik_co_id' => $ik_co_id,
            'ik_pm_no' => $ik_pm_no,
            'ik_am' => $ik_am,
            'ik_desc' => $ik_desc,
            'ik_ia_u' => $protocol . htmlspecialchars($_SERVER['HTTP_HOST'], ENT_COMPAT, 'UTF-8') . __PS_BASE_URI__ . 'modules/interkassa/validation.php',
            'ik_suc_u' => $protocol . htmlspecialchars($_SERVER['HTTP_HOST'], ENT_COMPAT, 'UTF-8') . __PS_BASE_URI__ . 'modules/interkassa/success.php',
            'ik_fal_u' => $protocol . htmlspecialchars($_SERVER['HTTP_HOST'], ENT_COMPAT, 'UTF-8') . __PS_BASE_URI__ . 'modules/interkassa/fail.php',
            'ik_pnd_u' => $protocol . htmlspecialchars($_SERVER['HTTP_HOST'], ENT_COMPAT, 'UTF-8') . __PS_BASE_URI__ . 'modules/interkassa/success.php',
        );


        if (Configuration::get('INTERKASSA2_TEST_MODE') == 'test') {
            $parameters['ik_pw_via'] = 'test_interkassa_test_xts';
        }
        
        $signature = self::IkSignFormation($arg);

        $parameters['ik_sign'] = $signature;

        if (Configuration::get('api_mode') == 'on'){
            $api_id = Configuration::get('api_id');
            $api_key = Configuration::get('api_key');
            $parameters['payment_systems'] = $this->getIkPaymentSystems($ik_co_id, $api_id, $api_key);
            $parameters['payment_systems_path'] = $this->_path . 'paysystems/';
            $parameters['img_path'] = $this->_path;
            $parameters['ajax_url'] = Tools::getHttpHost(true) . __PS_BASE_URI__ . 'modules/interkassa/ajax.php';
            $parameters['api_mode'] = true;
        }else{
            $parameters['api_mode'] = false;
        }
        
        
        
        $smarty->assign($parameters);

        return $this->display(__FILE__, 'interkassa.tpl');
    }
    public function getIkPaymentSystems($ik_co_id, $ik_api_id,$ik_api_key){
        $username = $ik_api_id;
        $password = $ik_api_key;
        $remote_url = 'https://api.interkassa.com/v1/paysystem-input-payway?checkoutId='.$ik_co_id;
        
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
        
    public function getIkBusinessAcc($username = '', $password = '')         {
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
                $response = json_decode($response,1);


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
 

    public static function IkSignFormation($data){

        if (!empty($data['ik_sign'])) unset($data['ik_sign']);

        ksort($data, SORT_STRING);
        array_push($data, Configuration::get('secret_key'));
        $arg = implode(':', $data);
        $ik_sign = base64_encode(md5($arg, true));
        json_decode($ik_sign);

        return $ik_sign;
    }
}

?>
