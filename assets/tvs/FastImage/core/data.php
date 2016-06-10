<?php namespace FastImageTV;

include_once(MODX_BASE_PATH.'assets/lib/MODxAPI/autoTable.abstract.php');
include_once(MODX_BASE_PATH . 'assets/lib/Helpers/FS.php');
include_once (MODX_BASE_PATH . 'assets/lib/Helpers/PHPThumb.php');

class Data extends \autoTable {
    protected $fs = null;

    protected $table = 'user_files';
    protected $pkName = 'id';

    protected $default_field = array(
        'id' => 0,
        'parent' => 0,
        'class' => '',
        'path' => '',
        'file' => '',
        'type' => '',
        'owner' => 'manager',
        'size' => 0,
        'createdon' => '',
        'createdby' => ''
    );
    public $uid = 0;
    public $config = array();
    public $documentData = array();

    public function __construct($modx) {
        parent::__construct($modx);
        $this->fs = \Helpers\FS::getInstance();
        $this->loadConfig('default');
    }

    public function upload($data) {
        if (!is_array($data) || empty($data)) return false;
        $file = $data['path'].$data['file'];
        $this->create($data);
        $this->touch('createdon');
        $this->set('createdby',$this->modx->getLoginUserID('mgr'));
        if (!$this->config['clientResize']) {
            $options = array();
            if (!empty($maxWidth = $this->config['imageTransform']['maxWidth'])) $options[] = 'w='.$maxWidth;
            if (!empty($maxHeight = $this->config['imageTransform']['maxHeight'])) $options[] = 'h='.$maxHeight;
            if (!empty($quality = $this->config['imageTransform']['quality'])) {
                $options[] = 'q='.round($quality * 100,0);
            }
            $options[] = 'ar=x';
            $thumb = new \Helpers\PHPThumb();
            $thumb->create(MODX_BASE_PATH.$file,MODX_BASE_PATH.$file,implode('&',$options));
        }
        $this->set('size',$this->fs->fileSize($file));
        $out = $this->save(true);
        if ($out) {
            if (!empty($this->config['thumbnail'])) {
                $value = $this->config['thumbnail'];
                $this->makeThumb($value['folder'],$file,$value['options']);
                $this->set('thumbnail',$this->getThumbnail($file));
            }
            if (!empty($this->config['previews'])) {
                foreach ($this->config['previews'] as $key=>$value) {
                    $this->makeThumb($value['folder'],$file,$value['params']);
                }
            }
        }
        return $out;
    }
    
    public function deleteFile($file, $class, $parent) {
        $result = $this->query("SELECT `id`, `parent`, `path`, `class`, `file` FROM {$this->makeTable($this->table)} WHERE `parent`=$parent AND `class`='{$class}' AND CONCAT(`path`,`file`)='{$file}'");
        if ($this->modx->db->getRecordCount($result)) {
            $row = $this->modx->db->getRow($result);
            $this->delete($row['id']);
        }
    }

    public function delete($ids, $fire_events = null) {
        $_ids = $this->sanitarIn($ids);
        $result = $this->query("SELECT * FROM {$this->makeTable($this->table)} WHERE `id` IN ({$_ids})");

        if ($this->modx->db->getRecordCount($result)) {
            while($row = $this->modx->db->getRow($result)) {
                $this->loadConfig();
                $this->loadConfig($row['class']);
                $file = $row['path'] . $row['file'];
                $this->fs->unlink($file);
                if (!empty($thumbnail = $this->getThumbnail($file))) {
                    $this->fs->unlink($thumbnail);
                    @rmdir($this->fs->takeFileDir($thumbnail));
                }
                if (!empty($this->config['previews'])) {
                    foreach ($this->config['previews'] as $key => $value) {
                        $old = $row['path'] . '/' . $value['folder'] . '/' . $row['file'];
                        $this->fs->unlink($old);
                        @rmdir($this->fs->takeFileDir($old));
                    }
                }
                @rmdir($this->fs->takeFileDir($file));
            }
        }
        parent::delete($ids, $fire_events);

        $this->query("ALTER TABLE {$this->makeTable($this->table)} AUTO_INCREMENT = 1");
    }

    public function stripName($name) {
        $filename = $this->fs->takeFileName($name);
        $ext = $this->fs->takeFileExt($name);
        return $this->modx->stripAlias($filename).'.'.$ext;
    }
    
    public function update($image, $data) {
        if (!isset($data['id']) || !$data['id']) return;
        $out = $image;
        $result = $this->query("SELECT `id`, `path`, `class`, `file` FROM {$this->makeTable($this->table)} WHERE `parent`=0 AND CONCAT(`path`,`file`)='{$image}'");
        if ($this->modx->db->getRecordCount($result)) {
            $row = $this->modx->db->getRow($result);
            $this->loadConfig();
            $this->loadConfig($row['class']);
            $this->edit($row['id'])->set('parent',$data['id']);
            $this->set('path',str_replace(array('__id__','__pid__'),array($data['id'],$data['parent']),$row['path']));
            $this->set('file',str_replace(array('__id__','__pid__'),array($data['id'],$data['parent']),$row['file']));
            $path = $this->get('path');
            $file = $this->get('file');
            $new = $path.$file;
            $this->save(false,false);
            $this->close();
            $this->fs->moveFile($image,$new);
            $out = $new;
            if (!empty($old = $this->getThumbnail($row['path'].$row['file']))) {
                $new = $this->getThumbnail($new);
                $this->fs->moveFile($old,$new);
                @rmdir($this->fs->takeFileDir($old));
            }
            if (!empty($this->config['previews'])) {
                foreach ($this->config['previews'] as $key=>$value) {
                    $old = $row['path'].'/'.$value['folder'].'/'.$row['file'];
                    $new = $path.'/'.$value['folder'].'/'.$file;
                    $this->fs->moveFile($old,$new);
                    @rmdir($this->fs->takeFileDir($old));
                }
            }
            @rmdir($this->fs->takeFileDir($image));
        }
        return $out;
    }

    public function getFastImageTVs () {
        $out = array();
        $result = $this->query("SELECT `name` FROM {$this->makeTable('site_tmplvars')} WHERE `type`='custom_tv:FastImage'");
        while ($row = $this->modx->db->getRow($result)) {
            $out[] = $row['name'];
        }
        return $out;
    }

    public function checkTable()
    {
        $table = $this->modx->db->config['table_prefix'].$this->table;
        $sql = "SHOW TABLES LIKE '{$table}'";
        return $this->modx->db->getRecordCount( $this->query($sql));
    }

    public function touch($field){
        $this->set($field, date('Y-m-d H:i:s', time() + $this->modx->config['server_offset_time']));
        return $this;
    }

    public function makeThumb($folder,$url,$options) {
        if (empty($url)) return false;
        $thumb = new \Helpers\PHPThumb();
        $inputFile = MODX_BASE_PATH . $this->fs->relativePath($url);
        $dir = $this->fs->takeFileDir($inputFile)."/{$folder}/";
        $outputFile = $dir . $this->fs->takeFileBasename($url);
        $this->fs->makeDir($dir);
        if ($thumb->create($inputFile,$outputFile,$options)) {
            return true;
        } else {
            $this->modx->logEvent(0, 3, $thumb->debugMessages,  __NAMESPACE__);
            return false;
        }
    }

    public function deleteUnused($files = array()) {
        $used = array();
        $classes = array();
        foreach ($files as $file) {
            if (!empty($file['file'])) {
                $used[] = $file['file'];
                $classes[] = $file['class'];
                $parent = $file['parent'];
            }
        }
        if (empty($used)) return;
        $used = $this->sanitarIn($used);
        $classes = $this->sanitarIn($classes);
        $result = $this->query("SELECT `id` FROM {$this->makeTable($this->table)} WHERE `class` IN ({$classes}) AND `parent`={$parent} AND CONCAT(`path`,`file`) NOT IN ({$used})");
        if ($this->modx->db->getRecordCount($result)) {
            $ids = $this->modx->db->getColumn('id',$result);
            $this->delete($ids);
        }
    }

    public function deleteParents($parents = array()) {
        if (empty($parents)) return;
        $parents = implode(',',$parents);
        $result = $this->query("SELECT `id` FROM {$this->makeTable($this->table)} WHERE `parent` IN ({$parents})");
        if ($this->modx->db->getRecordCount($result)) {
            $ids = $this->modx->db->getColumn('id',$result);
            $this->delete($ids);
        }
    }

    public function deleteLost($keepLost = 24) {
        $result = $this->query("SELECT `id` FROM {$this->makeTable($this->table)} WHERE `parent`=0 AND DATEDIFF(hour, `createdon`, getdate()) > {$keepLost}");
        if ($this->modx->db->getRecordCount($result)) {
            $ids = $this->modx->db->getColumn('id',$result);
            $this->delete($ids);
        }
    }

    public function duplicate($source,$target) {
        include_once(MODX_BASE_PATH.'assets/lib/MODxAPI/modResource.php');
        $doc = new \modResource($this->modx);
        $doc->edit($target);
        $table = $this->makeTable($this->table);
        $result = $this->modx->db->select('*', $table, "parent='{$source}'");
        $parent = $this->modx->db->select('parent',$this->makeTable('site_content'),"id='{$source}'");
        $parent = $this->modx->db->getRow('parent',$parent);
        if ($this->modx->db->getRecordCount($result)) {
            while ($row = $this->modx->db->getRow($result)) {
                unset($row['id']);
                $this->documentData['id'] = $target;
                $this->documentData['parent'] = $parent;
                $row['parent'] = $target;
                $this->loadConfig();
                $this->loadConfig($row['class']);
                $dir = $this->config['folder'];
                $dir = $this->prepare($dir);
                $this->fs->copyFile($row['path'].$row['file'],$dir.$row['file']);
                $doc->set($row['class'],$dir.$row['file']);
                if (!empty($old = $this->getThumbnail($row['path'].$row['file']))) {
                    $new = $this->getThumbnail($dir.$row['file']);
                    $this->fs->copyFile($old,$new);
                }
                if (!empty($this->config['previews'])) {
                    foreach ($this->config['previews'] as $key=>$value) {
                        $old = $row['path'].'/'.$value['folder'].'/'.$row['file'];
                        $new = $dir.'/'.$value['folder'].'/'.$row['file'];
                        $this->fs->copyFile($old,$new);
                    }
                }
                $row['path'] = $dir;
                $this->modx->db->insert($row, $table);
            }
            $doc->save();
        }
    }
    
    public function getThumbnail($image) {
        $out = '';
        if (!empty($this->config['thumbnail']['folder'])) {
            $path = $this->fs->takeFileDir($image);
            $path = $path.'/'.$this->config['thumbnail']['folder'].'/'.$this->fs->takeFileBasename($image);
            $out =$this->fs->relativePath($path);
        }

        return $out;
    }

    public function prepare($str) {
        $ph = array(
            '[+id+]'      => $this->documentData['id'] ? $this->documentData['id'] : '__id__',    // Resource ID
            '[+parent+]'  => $this->documentData['parent'] ? $this->documentData['parent'] : '__pid__',      // Resource Parent ID
            '[+uid+]'     => $this->modx->getLoginUserID('mgr'),    // User ID
            '[+rand+]'    => substr(uniqid(), 0, 6),          // Random string
            '[+time+]'    => time(),    // Timestamp
            '[+year+]'    => date('Y'), // Year
            '[+month+]'   => date('m'), // Month
            '[+day+]'     => date('d'), // Day
            '[+hour+]'    => date('H'), // Hour
            '[+minute+]'  => date('i'), // Minute
            '[+second+]'  => date('s'), // Second
        );
        return str_replace(array_keys($ph),$ph,$str);
    }

    public function createTable() {
        $sql = <<< OUT
CREATE TABLE IF NOT EXISTS {$this->makeTable($this->table)} (
`id` int(10) NOT NULL auto_increment,
`parent` int(10) NOT NULL default '0',
`class` varchar(100) NOT NULL default '',
`path` TEXT NOT NULL default '',
`file` varchar(255) NOT NULL default '',
`type` varchar(50) NOT NULL default '',
`owner` varchar(50) NOT NULL default '',
`size` int(10) NOT NULL default '0',
`createdon` datetime NOT NULL default '0000-00-00 00:00:00',
`createdby` int(10) NOT NULL default '0',
PRIMARY KEY  (`id`),
KEY `parent` (`parent`)
) ENGINE=MyISAM COMMENT='Datatable for User Files.';
OUT;
        return $this->query($sql);
    }

    public function loadConfig ($config = 'default') {
        if (empty($config)) $config = 'default';
        $file = MODX_BASE_PATH."assets/tvs/FastImage/config/{$config}.php";
        if ($this->fs->checkFile($file)) {
            $_config = include($file);
            if (is_array($_config)) $this->config = $config == 'default' ? $_config : array_merge($this->config,$_config);
        }
    }
}