<?php
/**
 * @name Интеркасса 2.0
 * @description Модуль разработан в компании GateOn предназначен для CMS Prestashop 1.6.1.2
 * @author www.gateon.net
 * @email www@smartbyte.pro
 * @last_update 28.03.2017
 * @version 1.5
 */

include(dirname(__FILE__) . '/../../config/config.inc.php');
include(dirname(__FILE__) . '/interkassa.php');

if(!empty($_POST) && isset($_POST['ik_pm_no'])){
    print_r(Interkassa::IkSignFormation($_POST));
}
