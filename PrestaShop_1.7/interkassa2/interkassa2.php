<?php
/**
 * @name Интеркасса 2.0
 * @description Модуль разработан в компании GateOn предназначен для CMS Prestashop 1.7.0.x
 * @author www.gateon.net
 * @email www@smartbyte.pro
 * @version 1.1
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
        $this->version = '1.1';
        $this->ps_versions_compliancy = array('min' => '1.7', 'max' => _PS_VERSION_);
        $this->currencies = true;
        $this->currencies_mode = 'radio';

        parent::__construct();

        $this->author = 'GateOn';
        $this->page = basename(__FILE__, '.php');
        $this->displayName = $this->l('Интеркасса 2.0');
        $this->description = $this->l('Прием платежей с помощью кредитной карты быстро и безопасно с Интеркасса 2.0');
        $this->confirmUninstall = $this->l('Вы уверенны что хотите удалить все настройки?');

    }

    public function install()
    {
        //При установке будет создан новый статус заказа для pending
        $ikStatePending = new OrderState();
        foreach (Language::getLanguages() AS $language)
        {
            $ikStatePending->name[$language['id_lang']] = 'Ожидает оплаты от Интеркассы';
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
            $ikStatePaid->name[$language['id_lang']] = 'Оплачено с помощью Интеркассы';
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
            OR !Configuration::updateValue('INTERKASSA2_PAY_TEXT', 'Оплатить с помощью Интеркассы')
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

        }

        $html = '<div style="width:550px">
           <p style="text-align:center;">
               <a href="https://www.interkassa.com/" target="_blank">
                <img  src="' . __PS_BASE_URI__ . 'modules/interkassa2/interkassa.png" alt="Interkassa2" border="0" width="300px" align="center " />
               </a>
            </p>
        <form action="' . $_SERVER['REQUEST_URI'] . '" method="post">
          <fieldset>
          <legend><img width="20px" src="' . __PS_BASE_URI__ . 'modules/interkassa2/logo.gif" />' . $this->l('Настройки') . '</legend>
            <p>' . $this->l('Используйте тестовый режим для непосредственного перехода на тестовую платежную систему, без возможности выбора других 
            платежных систем') . '
            </p>
            <label>
              ' . $this->l('Режим') . '
            </label>
            <div class="margin-form" style="width:110px;">
              <select name="ik_test_mode">
                <option value="live"' . (Configuration::get('INTERKASSA2_TEST_MODE') == 'live' ? ' selected="selected"' : '') . '>' . $this->l('Рабочий 
                режим')
            . '&nbsp;&nbsp;
                </option>
                <option value="test"' . (Configuration::get('INTERKASSA2_TEST_MODE') == 'test' ? ' selected="selected"' : '') . '>' . $this->l('Тестовый режим')
            . '&nbsp;
                &nbsp;
                </option>
              </select>
            </div>
            <p>' . $this->l('Идентификатор кассы вы можете найти в настройках вашей кассы рядом с ее названием') . '</p>
            <label>
              ' . $this->l('Идентификатор кассы') . '
            </label>
            <div class="margin-form">
              <input type="text" name="ik_co_id" value="' . Tools::getValue('INTERKASSA2_CO_ID', Configuration::get('INTERKASSA2_CO_ID')) . '" />
            </div>
            <label>
              ' . $this->l('Секретный ключ') . '
            </label>
            <div class="margin-form">
              <input type="text" name="s_key" value="' . trim(Tools::getValue('INTERKASSA2_S_KEY', Configuration::get('INTERKASSA2_S_KEY'))) . '" />
            </div> 
            <p>' . $this->l('Секретный ключ безопасности вы можете найти во вкладке Безопасность, в настройках вашей кассы') . '</p>' .
            '<label>
            ' . $this->l('Тестовый ключ') . '
             </label>
            <div class="margin-form">
              <input type="text" name="t_key" value="' . trim(Tools::getValue('INTERKASSA2_T_KEY', Configuration::get('INTERKASSA2_T_KEY'))) . '" />
            </div> 
            <p>' . $this->l('Тестовый ключ безопасности вы можете найти во вкладке Безопасность, в настройках вашей кассы') . '</p>
            <label>
            ' . $this->l('Текст формы оплаты') . '
            </label>
             <div class="margin-form" style="margin-top:5px">
               <input type="text" name="interkassa2_pay_text" value="' . Configuration::get('INTERKASSA2_PAY_TEXT') . '">
             </div><br>
             <label>
             ' . $this->l('Предварительный просмотр') . '
             </label>
                  <div align="center">' . Configuration::get('INTERKASSA2_PAY_TEXT') . '&nbsp&nbsp
                  <img width="100px" alt="Оплачивайте с помощью Интеркассы" title="Оплачивайте с помощью Интеркассы" src="' . __PS_BASE_URI__
            . 'modules/interkassa2/interkassa.png">
                    </div><br>
            <div style="float:right;"><input type="submit" name="submitInterkassa2" class="button btn btn-default pull-right" value="' . $this->l('Сохранить') . '" /></div><div 
            class="clear"></div>
          </fieldset>
        </form>
        <br /><br />
        <fieldset>
          <legend><img src="../img/admin/warning.gif" />' . $this->l('Информация') . '</legend>
          <p>- ' . $this->l('Чтобы использовать этот платежный модуль вы должны указать идентификатор кассы, секретный ключ и для тестирования - 
          тестовый ключ безопасности.') . '
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

        $externalOption = new PaymentOption();
        $externalOption->setCallToActionText($this->l(Configuration::get('INTERKASSA2_PAY_TEXT')))
            ->setAction('https://sci.interkassa.com/')
            ->setInputs($form)
            ->setAdditionalInformation($this->context->smarty->fetch('module:interkassa2/interkassa2_info.tpl'))
            ->setLogo(Media::getMediaPath(_PS_MODULE_DIR_ . $this->name . '/payment.png'));

        return $externalOption;
    }

    public function hookPaymentReturn($params)
    {
        if(!empty($_POST)){
            $this->smarty->assign(array(
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
            return $this->fetch('module:interkassa2/interkassa2_notification.tpl');
        }

    }

}
