<?php

if (!IN_MANAGER_MODE) die();

include_once(MODX_BASE_PATH.'assets/tvs/FastImage/core/tv.php');

global $content;
$documentData = array(
    'id' => 0,
    'template' => 0,
    'parent' => 0
);

if (isset($content['id'])) $documentData['id'] = $content['id'];
if (isset($content['template'])) $documentData['template'] = $content['template'];
if (isset($content['parent'])) $documentData['parent'] = $content['parent'];

$tv = new \FastImageTV\TV (
    $modx,
    $row,
    $documentData
);

echo $tv->render();