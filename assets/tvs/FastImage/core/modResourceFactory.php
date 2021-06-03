<?php

class modResourceFactory{

    /**
     * @param DocumentParser $modx
     */
    public static function build($modx){
        $version = floatval($modx->getConfig('settings_version'));

        if($version >= 3){
            return new \Pathologic\EvolutionCMS\MODxAPI\modResource($modx);
        }
        else{
            include_once(MODX_BASE_PATH.'assets/lib/MODxAPI/modResource.php');
            return  new modResource($modx);
        }
    }
}