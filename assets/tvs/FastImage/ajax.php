<?php
define('MODX_API_MODE', true);
define('IN_MANAGER_MODE', true);

include_once(__DIR__."/../../../index.php");
$modx->db->connect();
if (empty ($modx->config)) {
    $modx->getSettings();
}
if(!isset($_SESSION['mgrValidated'])){
    die();
}

$mode = (isset($_REQUEST['mode']) && is_scalar($_REQUEST['mode'])) ? $_REQUEST['mode'] : null;
$out = null;
include_once (MODX_BASE_PATH . 'assets/tvs/FastImage/core/controller.php');

$controller = new \FastImageTV\Controller($modx);
if (!empty($mode) && method_exists($controller, $mode)) {
    $out = call_user_func_array(array($controller, $mode), array());
}else{
    $out = array('success'=>false,'message'=>'Неизвестная ошибка.');
}
echo ($out = is_array($out) ? json_encode($out) : $out);