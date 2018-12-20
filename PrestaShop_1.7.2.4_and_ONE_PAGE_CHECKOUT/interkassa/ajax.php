<?php
include(dirname(__FILE__).'/../../config/config.inc.php');
include(dirname(__FILE__).'/interkassa.php');

if(!empty($_POST)){
  if(isset($_POST['ik_act'])&&$_POST['ik_act']=='process')
  {
    $sign = Interkassa::IkSignFormation($_POST);
    $_POST['ik_sign'] = $sign;
    $return = Interkassa::getAnswerFromAPI($_POST);
  }
  else $return = array('sign'=>Interkassa::IkSignFormation($_POST));
  echo json_encode($return);
}
