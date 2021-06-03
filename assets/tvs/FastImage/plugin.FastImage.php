<?php
$e = $modx->event;
if ($e->name == 'OnDocFormSave') {
    include_once(MODX_BASE_PATH.'assets/tvs/FastImage/core/data.php');
    include_once(MODX_BASE_PATH.'assets/tvs/FastImage/core/modResourceFactory.php');
    $doc = modResourceFactory::build($modx);
    $fi = new \FastImageTV\Data($modx);
    $tvs = $fi->getFastImageTVs();
    $doc->edit($id);
    $flag = array_intersect(array_keys($doc->toArray()),$tvs);
    if(!$flag) return;
    $values = array();
    foreach ($tvs as $tv) {
        $value = $doc->get($tv);
        if (!empty($value)) {
            if ($mode == 'new') {
                $value = $fi->update($value,array('id'=>$id,'parent'=>$doc->get('parent')));
                if (!empty($value)) {
                    $doc->set($tv,$value);
                }
            }
        }
        $values[] = array('class'=>$tv,'file'=>$doc->get($tv), 'parent'=>$id);
    }
    $doc->save(false,false);
    $fi->deleteUnused($values, $id);
}
if ($e->name == 'OnDocDuplicate') {
    include_once(MODX_BASE_PATH.'assets/tvs/FastImage/core/data.php');
    $fi = new \FastImageTV\Data($modx);
    $fi->duplicate($id, $new_id);
}
if ($e->name == 'OnEmptyTrash') {
    if (empty($ids)) return;
    include_once(MODX_BASE_PATH.'assets/tvs/FastImage/core/data.php');
    $fi = new \FastImageTV\Data($modx);
    $fi->deleteParents($ids);
}
if ($e->name == 'ManagerPageInit') {
    include_once(MODX_BASE_PATH.'assets/tvs/FastImage/core/data.php');
    $fi = new \FastImageTV\Data($modx);
    $fi->deleteLost($keepLost);
}