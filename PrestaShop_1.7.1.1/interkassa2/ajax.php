<?php
include(dirname(__FILE__) . '/../../config/config.inc.php');
include(dirname(__FILE__) . '/interkassa2.php');

if(!empty($_POST) && isset($_POST['ik_pm_no'])){
    print_r(Interkassa2::IkSignFormation($_POST));
}