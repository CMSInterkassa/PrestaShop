<?php
/**
 * @name Интеркасса 2.0
 * @description Модуль разработан в компании GateOn предназначен для CMS Prestashop 1.7.0.x
 * @author www.gateon.net
 * @email www@smartbyte.pro
 * @last_update 13.01.2017
 * @version 1.2
 */

include(dirname(__FILE__) . '/../../config/config.inc.php');
include(dirname(__FILE__) . '/interkassa2.php');

if(!empty($_POST) && isset($_POST['ik_pm_no'])){
    print_r(Interkassa2::IkSignFormation($_POST));
}


