<?php
include(dirname(__FILE__) . '/../../config/config.inc.php');
include(dirname(__FILE__) . '/interkassa.php');

if(!empty($_POST)&&isset($_POST['ik_pm_no'])){
    if(isset($_POST['ik_act'])&&$_POST['ik_act']=='process')
    {
      $sign = Interkassa::IkSignFormation($_POST);
      $_POST['ik_sign'] = $sign;
      echo json_encode(Interkassa::getAnswerFromAPI($_POST));
    }
    else echo json_encode(array('sign'=>Interkassa::IkSignFormation($_POST)));
}
