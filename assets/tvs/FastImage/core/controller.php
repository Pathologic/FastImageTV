<?php namespace FastImageTV;

include_once (MODX_BASE_PATH . 'assets/tvs/FastImage/core/data.php');
include_once (MODX_BASE_PATH . 'assets/lib/Helpers/FS.php');
include_once(MODX_BASE_PATH . 'assets/snippets/DocLister/lib/DLTemplate.class.php');

class Controller {
    protected $modx = null;
    public $data = null;
    public $uid = 0;
    public $config = array();
    public $class = '';
    public $documentData = array();

    public function __construct(\DocumentParser $modx){
        $this->modx = $modx;
        $this->uid = $modx->getLoginUserID('mgr');
        $this->data = new Data($modx);
        $this->fs = \Helpers\FS::getInstance();
        $this->class = empty($_REQUEST['class']) || !is_scalar($_REQUEST['class']) ? '' : $this->modx->db->escape($_REQUEST['class']);
        $this->data->loadConfig($this->class);
        $this->config = $this->data->config;
        $this->documentData = array(
            'id' => isset($_REQUEST['documentData']['id']) ? (int)$_REQUEST['documentData']['id'] : 0,
            'parent' => isset($_REQUEST['documentData']['parent']) ? (int)$_REQUEST['documentData']['parent'] : 0,
            'template' => isset($_REQUEST['documentData']['template']) ? (int)$_REQUEST['documentData']['template'] : 0
        );
        $this->data->documentData = $this->documentData;
    }

    public function upload() {
        if (!$this->class || !$this->uid) return array('success'=>false,'message'=>'Unknown error.');
        $out = '';
        $class= $this->class;
        if (!empty($this->config['folder'])) {
            $dir = $this->config['folder'];
            $allowedFiles = $this->config['allowed'];
        } else {
            return array('success'=>false,'message'=>'Invalid configuration.');
        }
        if (!empty($_FILES['file']) && !$_FILES['file']['error'] && is_uploaded_file($_FILES['file']['tmp_name'])) {
            $name = $_FILES['file']['name'];
            $ext = $this->fs->takeFileExt($name);
            if (!in_array(strtolower($ext),$allowedFiles)) return array('success'=>false,'message'=>'This file type is not allowed.');
            $dir = $this->data->prepare($dir);
            if ($this->fs->makeDir($dir)) {
                $name = $this->data->stripName($name);
                $name = $this->data->prepare(str_replace(
                    '[+filename+]',
                    $name,
                    empty($this->config['filename']) ? '[+filename+]' : $this->config['filename']
                    )
                );
                $name = $this->fs->getInexistantFilename($dir.$name);
                if (@move_uploaded_file($_FILES['file']['tmp_name'],MODX_BASE_PATH.$dir.$name)) {
                    $out = $this->data->upload(array(
                        'path'=>$dir,
                        'file'=>$name,
                        'class'=>$class,
                        'parent'=>$this->documentData['id'],
                        'owner'=>'manager',
                        'type'=>'image'
                    ));
                } else {
                    return array('success'=>false,'message'=>'File could not be uploaded.');
                }
            } else {
                return array('success'=>false,'message'=>'Could not upload file.');
            }
        }
        if ($out) $out = array('success'=>true,'data'=>$this->data->toArray());
        return $out;
    }

    public function delete() {
        if (!$this->class || !$this->uid || !isset($_REQUEST['file']) || !is_scalar(($_REQUEST['file']))) array('success'=>false,'message'=>'Operation failed.');
        $file = $this->modx->db->escape($_REQUEST['file']);
        $this->data->deleteFile($file,$this->class,$this->documentData['id']);
        return array('success'=>true);
    }
}
