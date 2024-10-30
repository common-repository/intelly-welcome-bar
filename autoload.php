<?php
spl_autoload_register('iwb_autoload');
function iwb_autoload($class) {
    $root=dirname(__FILE__).'/includes/classes/';
    iwb_autoload_root($root, $class);
}
function iwb_autoload_root($root, $class) {
    $slash=substr($root, strlen($root)-1);
    if($slash!='/' && $slash!='\\') {
        $root.='/';
    }
    $name=str_replace(IWB_PLUGIN_PREFIX, '', $class);
    if(strpos($class, IWB_PLUGIN_PREFIX)===FALSE) {
        //autoload only plugin classes
        return;
    }
    if(!file_exists($root)) {
        return;
    }

    $h=opendir($root);
    while($file=readdir($h)) {
        if(is_dir($root.$file) && $file != '.' && $file != '..') {
            iwb_autoload_root($root.$file, $class);
        } elseif(file_exists($root.$name.'.php')) {
            include_once($root.$name.'.php');
        } elseif(file_exists($root.$class.'.php')) {
            include_once($root.$class.'.php');
        }
    }
}
function iwb_include_php($root) {
    $options=array('ext'=>'.php');
    $files=iwb_read_files($root, $options);
    foreach($files as $file) {
        include_once($file);
    }
}
function iwb_read_files($root, $options=array()) {
    $defaults=array('ext'=>'');
    $options=wp_parse_args($options, $defaults);
    $result=array();

    $root=str_replace('//', '/', $root);
    $root=str_replace('\\\\', '\\', $root);
    if(!file_exists($root)) {
        return $result;
    }
    $h=opendir($root);
    $slash=substr($root, strlen($root)-1);
    if($slash!='/' && $slash!='\\') {
        $root.='/';
    }

    while($file=readdir($h)) {
        $source=$root.$file;
        if(is_dir($source) && $file!='.' && $file!='..'){
            $others=iwb_read_files($source, $options);
            $result=array_merge($result, $others);
        } else {
            if($options['ext']!='') {
                $needle=strtolower($options['ext']);
                $haystack=strtolower($source);

                $length=strlen($needle);
                $start=$length*-1; //negative
                if(substr($haystack, $start)===$needle) {
                    $result[]=$source;
                }
            } else {
                $result[]=$source;
            }
        }
    }
    sort($result);
    return $result;
}