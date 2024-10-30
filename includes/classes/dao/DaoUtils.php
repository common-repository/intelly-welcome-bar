<?php
if (!defined('ABSPATH')) exit;

class IWB_DaoUtils {
    var $data;
    var $tables;

    public function __construct() {
        $this->data=array();
        $this->tables=array();
    }

    public function getDatabaseVersion() {
        ob_start();
        var_dump($this->tables);
        $buffer=ob_get_clean();
        $buffer=md5($buffer);
        return $buffer;
    }
    private function getColumnCreationSql($table, $primary, $name, $column) {
        global $iwb;
        if(!isset($column['type']) || $column['type']=='') {
            return '';
        }

        $type=strtolower($column['type']);
        switch ($type) {
            case 'array':
                $type='text';
                //if(!isset($column['len'])) {
                //    $column['len']=100;
                //}
                break;
            case 'json':
                $type='longtext';
                unset($column['len']);
                break;
            case 'pointer':
                $type='int';
                break;
        }
        $type=strtoupper($type);
        $sql=$iwb->Dao->encodeColumn($table, $column['column'])." ".$type;

        if(isset($column['len'])) {
            $sql.="(".$column['len'].")";
        }
        if(isset($column['default']) && !$iwb->Utils->startsWith($column['default'], '{')) {
            $sql.=" DEFAULT '".$column['default']."'";
        }
        if((isset($column['required']) && $column['required']) || (isset($column['primary']) && $column['primary'])) {
            $sql.=" NOT NULL";
        }
        if(isset($primary[$name]) && count($primary)==1) {
            $sql.=" AUTO_INCREMENT";
        }
        return $sql;
    }
    public function databaseUpdate() {
        global $iwb;
        $previous=intval(ini_get('error_reporting'));
        ini_set('error_reporting', $previous&~E_NOTICE);
        //TODO: this can cause issue wp-admin
        require_once(ABSPATH.'wp-admin/includes/upgrade.php');
	$result=FALSE;
        foreach ($this->tables as $k=>$v) {
            $table=$v['table'];
            $primary=$this->getPrimaryColumns($table);

            $options=array('includeNested'=>FALSE);
            $columns=$this->getColumns($k, $options);

            $sql='';
            //id as first columns :)
            foreach($primary as $k=>$v) {
                $text=$this->getColumnCreationSql($table, $primary, $k, $v);
                if($text!='') {
                    if($sql=='') {
                        $sql.="\n\t";
                    } else {
                        $sql.=",\n\t";
                    }
                    $sql.=$text;
                }
                unset($columns[$k]);
            }
            //ACTUNG!!!!
            //cannot insert the comma as first character of the line
            //MUST be the last character before newline
            foreach($columns as $k=>$v) {
                $text=$this->getColumnCreationSql($table, $primary, $k, $v);
                if($text!='') {
                    if($sql=='') {
                        $sql.="\n\t";
                    } else {
                        $sql.=",\n\t";
                    }
                    $sql.=$text;
                }
            }

            $sql="CREATE TABLE ".strtolower($table)." (".$sql;
            $array=array();
            foreach($primary as $k=>$v) {
                $array[]=$v['column'];
            }
            if(count($array)>0) {
                $sql.=",\n\tUNIQUE KEY id (".implode(',', $array).")";
            }
            $sql.="\n);";
            $iwb->Log->debug('CREATING TABLE %s...', $table);
            $iwb->Log->debug('SCRIPT: %s', $sql);
            if(dbDelta($sql)!==FALSE) {
                $result=FALSE;
            }
        }
        ini_set('error_reporting', $previous);
        return $result;
    }

    function load($prefix, $root) {
        $h=opendir($root);
        $slash=substr($root, strlen($root)-1);
        if($slash!='/' && $slash!='\\') {
            $root.='/';
        }

        while($file=readdir($h)) {
            if(is_dir($root.$file) && $file != '.' && $file != '..'){
                $this->load($prefix, $root.$file);
            } elseif(strlen($file)>5) {
                $ext='.php';
                $length=strlen($ext);
                $start=$length*-1; //negative
                if(strcasecmp(substr($file, $start), $ext)==0) {
                    $this->loadClass($prefix, $root, $file);
                }
            }
        }
    }
    function loadClass($prefix, $root, $file) {
        global $wpdb,$iwb;
        $source=$root.$file;
        if(!file_exists($source)) {
            return FALSE;
        }

        $result=FALSE;
        $source=file_get_contents($source);
        if($file!=NULL && strlen($source)>0) {
            $source=str_replace("  ", " ", $source);
            $source=str_replace("\r\n", "\n", $source);
            $source=str_replace("\n\n", "\n", $source);
            $source=explode("\n", $source);

            $patternClass="class ";
            $patternVar="var $";
            $patternAnnotation="//@";
            $annotations=FALSE;

            $data=array(
                'class'=>''
                , 'fields'=>array()
                , 'columns'=>array()
            );

            $exit=FALSE;
            foreach($source as $row) {
                $row=trim($row);
                if($exit) {
                    break;
                }
                if($row=='') {
                    continue;
                }

                $patterns=array($patternClass, $patternVar, $patternAnnotation);
                foreach($patterns as $p) {
                    $pos=strpos($row, $p);
                    if($pos!==FALSE) {
                        switch ($p) {
                            case $patternClass:
                                if(is_array($annotations)!==FALSE) {
                                    $row=substr($row, $pos+strlen($patternClass));
                                    $row=explode(' ', $row);
                                    $class=$row[0];
                                    $data=$iwb->Utils->merge(TRUE, $data, $annotations);
                                    $data['class']=$class;
                                } else {
                                    //class without @table annotation
                                    $exit=TRUE;
                                }
                                $annotations=FALSE;
                                break;
                            case $patternVar:
                                if(is_array($annotations)!==FALSE) {
                                    $row=substr($row, $pos+strlen($patternVar));
                                    $row=explode(';', $row);
                                    $field=$row[0];
                                    $field=explode('=', $field);
                                    $field=$field[0];
                                    if(!isset($annotations['column'])) {
                                        //TODO: camelCase to camel_case?
                                        $annotations['column']=$field;
                                    }

                                    $v=$iwb->Utils->get($annotations, 'primary', FALSE);
                                    $v=$iwb->Utils->isTrue($v);
                                    $annotations['primary']=$v;
                                    $v=$iwb->Utils->get($annotations, 'required', FALSE);
                                    $v=$iwb->Utils->isTrue($v);
                                    $annotations['required']=$v;
                                    if((!isset($annotations['type']) || $annotations['type']=='') && $annotations['primary']) {
                                        $annotations['type']='int';
                                    }
                                    $annotations['field']=$field;

                                    $data['fields'][$field]=$annotations;
                                    $data['columns'][$annotations['column']]=$annotations;
                                }
                                $annotations=FALSE;
                                break;
                            case $patternAnnotation:
                                //@annotaion1=value1 @annotation2=value2 @annotation3=value3
                                $row=substr($row, $pos+strlen($patternAnnotation)-1);
                                $row=str_replace('  ', ' ', $row);
                                $row=explode(' ', $row);

                                if(!is_array($annotations)) {
                                    $annotations=array();
                                }
                                foreach($row as $r) {
                                    $r=explode('=', $r);
                                    if(count($r)==1) {
                                        $defaultsTrue=array('@ui-required', '@ui-readonly', '@ui-multiple', '@primary');
                                        if(in_array($r[0], $defaultsTrue)) {
                                            //default true
                                            $r[]=TRUE;
                                        } else {
                                            //default empty
                                            $r[]='';
                                        }
                                    }
                                    if(count($r)==2) {
                                        $k=trim($r[0]);
                                        $v=trim($r[1]);
                                        //check if is an @annotation
                                        if(substr($k, 0, 1)=='@') {
                                            $k=substr($k, 1);
                                            $annotations[$k]=$v;
                                        }
                                    }
                                }
                                break;
                            default:
                                $annotations=FALSE;
                                break;
                        }
                        break;
                    }
                }
            }

            if(isset($data['class']) && $data['class']!='') {
                if(isset($data['table']) && $data['table']!='') {
                    $this->data[$data['table']]=$data;
                    if($prefix!='') {
                        $data['table']=$wpdb->prefix.$prefix.$data['table'];
                        $this->data[$data['table']]=$data;
                    }
                    $this->tables[$data['class']]=$data;
                }

                $class=$data['class'];
                $this->data[$class]=$data;
                $class=str_replace(IWB_PLUGIN_PREFIX, '', $class);
                $this->data[$class]=$data;
                $result=TRUE;
            }
        }
        return $result;
    }
    public function getTableClass($class) {
        $table=$this->getTable($class);
        $result='';
        if($table!==FALSE && isset($table['class'])) {
            $result=$table['class'];
            if(!class_exists($result)) {
                $result='';
            }
        }
        return $result;
    }
    public function getClass($class) {
        if(is_array($class)) {
            if(count($class)==0) {
                $class='';
            } else {
                foreach($class as $k=>$v) {
                    $class=$v;
                    break;
                }
                if(is_array($class)) {
                    $class='';
                }
            }
        }
        if($class!='') {
            if(is_object($class)) {
                $class=get_class($class);
            } elseif(!class_exists($class)) {
            $class=IWB_PLUGIN_PREFIX.$class;
            if(!class_exists($class)) {
                    $class='';
                }
            }
        }
        return $class;
    }
    public function getTableName($class) {
        global $iwb;
        $key=array('DaoUtils.getTableName', $class);
        $result=$iwb->Options->getCache($key);
        if($result===FALSE) {
            $table=$this->getTable($class);
            $result='';
            if($table!==FALSE) {
                $result=strtolower($table['table']);
            }
            $iwb->Options->setCache($key, $result);
        }
        return $result;
    }
    public function getTable($class) {
        global $iwb;
        $key=array('DaoUtils.getTable', $class);
        $result=$iwb->Options->getCache($key);
        if($result===FALSE) {
            if(is_object($class)) {
                $class=get_class($class);
            }
            $classes=array();
            $classes[]=$class;
            if(strpos($class, IWB_PLUGIN_PREFIX)!==FALSE) {
                $class=str_replace(IWB_PLUGIN_PREFIX, '', $class);
                $classes[]=$class;
            }
            if(strpos($class, 'Search')!==FALSE) {
                $class=str_replace('Search', '', $class);
                $classes[]=$class;
            }

            $result=FALSE;
            foreach($classes as $class) {
                if(isset($this->data[$class])) {
                    $v=$this->data[$class];
                    if(isset($v['table']) && $v['table']!='') {
                        $result=$v;
                        break;
                    } elseif(isset($v['tableClass']) && $v['tableClass']!='') {
                        $v=$v['tableClass'];
                        if(isset($v['table']) && $v['table']!='') {
                            $result=$v;
                            break;
                        }
                    }
                }
            }
            $iwb->Options->setCache($key, $result);
        }
        return $result;
    }
    public function getId($instance) {
        $primary=$this->getPrimary($instance);
        $result=-1;
        if(isset($instance->$primary)) {
            $result=$instance->$primary;
            $result=intval($result);
        }
        return $result;
    }
    public function getPrimary($class) {
        global $iwb;
        $key=array('DaoUtils.getPrimary', $class);
        $result=$iwb->Options->getCache($key);
        if($result===FALSE) {
            $columns=$this->getPrimaryColumns($class);
            $result='';
            foreach($columns as $k=>$v) {
                $result=$k;
                break;
            }
            $iwb->Options->setCache($key, $result);
        }
        return $result;
    }
    public function getPointersColumns($class) {
        global $iwb;
        if(is_object($class)) {
            $class=get_class($class);
        }
        $class=str_replace(IWB_PLUGIN_PREFIX, '', $class);
        $class=str_replace('Search', '', $class);

        $key=array('DaoUtils.getPointersColumns', $class);
        $result=$iwb->Options->getCache($key);
        if($result===FALSE) {
            $options=array(
                'includeUiTypes'=>'pointer'
                , 'includeNested'=>FALSE
            );
            $result=$this->getColumns($class, $options);
            $iwb->Options->setCache($key, $result);
        }
        return $result;
    }
    public function getAllColumns($class) {
        global $iwb;
        $key=array('DaoUtils.getAllColumns', $class);
        $result=$iwb->Options->getCache($key);
        if($result===FALSE) {
            $options=array(
                'includeUiTypes'=>FALSE
                , 'excludeUiTypes'=>FALSE
                , 'includeNested'=>TRUE
                , 'includeNestedAlias'=>TRUE
            );
            $result=$this->getColumns($class, $options);
            $iwb->Options->setCache($key, $result);
        }
        return $result;
    }
    public function getColumnsNames($class, $options=array()) {
        $columns=$this->getColumns($class, $options);
        $result=array_keys($columns);
        return $result;
    }
    public function getColumns($class, $options=array()) {
        global $iwb;

        $defaults=array(
            'includeUiTypes'=>FALSE
            , 'excludeUiTypes'=>FALSE
            , 'includeNested'=>FALSE
            , 'includeNestedAlias'=>TRUE
        );
        $options=$iwb->Utils->parseArgs($options, $defaults);

        //we cannot do this due to $class could be a search class and not a table class
        //$class=$this->getTable($class);
        $result=array();
        if(is_array($class)) {
            foreach($class as $c) {
                $class=$c;
                break;
            }
        }
        if(is_object($class)) {
            $class=get_class($class);
        }
        if(isset($this->data[$class])) {
            $v=$this->data[$class];
            if(isset($v['fields'])) {
                $result=$v['fields'];
            }
        }

        if(strpos($class, 'Search')!==FALSE) {
            $domainClass = str_replace('Search', '', $class);
            if (isset($this->data[$domainClass])) {
                $defaults = $this->data[$domainClass];
                $defaults = $defaults['fields'];
                //the name of the field in ..Search class is not the same
                //we use pattern suffix to determe the query type (Equals, Like, etc)
                $patterns=$iwb->Dao->getSearchPatterns();
                foreach ($result as $name=>$value) {
                    foreach ($patterns as $p) {
                        if ($p=='' || $iwb->Utils->endsWith($name, $p)) {
                            $k=$name;
                            if (strlen($p)>0) {
                                $k=substr($name, 0, strlen($name) - strlen($p));
                            }
                            if(isset($defaults[$k])) {
                                //no default value will be copied
                                $array=$defaults[$k];
                                unset($array['default']);
                                $value=$iwb->Utils->parseArgs($value, $array);
                                switch (strtolower($p)) {
                                    case 's':
                                    case 'Ids':
                                        //could happens that the original  type is int but for ...
                                        //Search class we need to search more that one value su use the array type instead
                                        //mmmh...why this??
                                        //$value['type']='array';
                                        break;
                                }
                                $result[$name]=$value;
                                break;
                            }
                        }
                    }
                }
            }
        } else {
            $all=$result;
            foreach($result as $k=>$v) {
                if(isset($v['ui-type']) && $v['ui-type']=='pointer') {
                    if(strtolower($k)!='id' && $iwb->Utils->endsWith($k, 'Id')) {
                        $n=substr($k, 0, strlen($k)-2);
                        $v['instance']=$n;
                        $all[$k]=$v;

                        if($options['includeNested']) {
                            $k=$n;
                            $class=$this->getClass($v['rel']);
                            $columns=$this->getColumns($class, $options);
                            if($columns!==FALSE && count($columns)>0) {
                                $kk=$iwb->Utils->lowerUnderscoreCase($k);
                                $kk=explode('_', $kk);
                                $alias='';
                                foreach($kk as $t) {
                                    $alias.=substr($t, 0, 1);
                                }
                                foreach($columns as $n=>$c) {
                                    $all[$k.'.'.$n]=$c;

                                    if($options['includeNestedAlias']) {
                                        $cc=$c;
                                        $cc['alias']=$k.'.'.$n;
                                        $all[$alias.'.'.$n]=$cc;
                                    }
                                }
                            }
                        }
                    }
                }
            }
            $result=$all;
        }

        $includes=$iwb->Utils->toArray($options['includeUiTypes']);
        $excludes=$iwb->Utils->toArray($options['excludeUiTypes']);
        if(count($includes)>0 || count($excludes)>0) {
            foreach($result as $k=>$v) {
                $include=(isset($v['ui-type']) && $v['ui-type']!='');
                if($include && !$iwb->Utils->inArray($v['ui-type'], $includes)) {
                    $include=FALSE;
                }
                if($include && $iwb->Utils->inArray($v['ui-type'], $excludes)) {
                    $include=FALSE;
                }

                if(!$include) {
                    unset($result[$k]);
                }
            }
        }

        if(is_array($result)) {
            ksort($result);
        } else {
            $iwb->Log->error('NO COLUMNS FOUND FOR CLASS=[%s]', $class);
        }

        return $result;
    }
    public function getColumn($class, $name) {
        global $iwb;
        $class=$this->getClass($class);
        if($class=='') {
            return array();
        }
        $key=array('DaoUtils.getColumn', $class, $name);
        $result=$iwb->Options->getCache($key);
        if($result===FALSE) {
            $columns=$this->getAllColumns($class);
            $result=FALSE;
            if(isset($columns[$name])) {
                $result=$columns[$name];
            }
            $iwb->Options->setCache($key, $result);
        }
        return $result;
    }
    public function getPrimaryColumns($class) {
        global $iwb;
        $key=array('DaoUtils.getPrimaryColumns', $class);
        $result=$iwb->Options->getCache($key);
        if($result===FALSE) {
            $result=array();
            $options=array('includeNested'=>FALSE);
            $columns=$this->getColumns($class, $options);
            foreach($columns as $k=>$v) {
                if(isset($v['primary']) && $v['primary']) {
                    $result[$k]=$v;
                }
            }
            $iwb->Options->setCache($key, $result);
        }
        return $result;
    }
    public function getColumnsDefaults($class) {
        global $iwb;
        $key=array('DaoUtils.getColumnsDefaults', $class);
        $result=$iwb->Options->getCache($key);
        if($result===FALSE) {
            $result=array();
            $options=array('includeNested'=>FALSE);
            $fields=$this->getColumns($class, $options);
            foreach($fields as $k=>$v) {
                if(isset($v['default']) && $v['default']!=='') {
                    $v=$v['default'];
                    if(!$iwb->Utils->startsWith($v, '{')) {
                        $result[$k]=$v;
                    }
                    /*if($v=='currentCallCenterId') {
                        $u=$ec->Session->getUser();
                        $v=$u->id;
                        if($v>0 && $u->userRight==IWB_UserConstants::USER_RIGHT_CALL_CENTER) {
                            $result[$k]=$v;
                        }
                    } elseif($v=='currentAgentId') {
                        $u=$ec->Session->getUser();
                        $v=$u->id;
                        if($v>0 && ($u->userRight==IWB_UserConstants::USER_RIGHT_AGENT || $u->userRight==IWB_UserConstants::USER_RIGHT_AGENT_MANAGER)) {
                            $result[$k]=$v;
                        }
                    } elseif($v=='currentUserId') {
                        $v=$ec->Session->getUserId();
                        if($v>0) {
                            $result[$k]=$v;
                        }
                    }*/
                }
            }
            $iwb->Options->setCache($key, $result);
        }
        return $result;
    }
    public function getColumnsFormats($class) {
        global $iwb;
        $key=array('DaoUtils.getColumnsFormats', $class);
        $result=$iwb->Options->getCache($key);
        if($result===FALSE) {
            $result=array();
            $options=array('includeNested'=>FALSE);
            $fields=$this->getColumns($class, $options);
            foreach($fields as $k=>$v) {
                if(isset($v['type']) && $v['type']!='') {
                    $result[$k]='%s';
                    switch (strtolower($v['type'])) {
                        case 'int':
                        case 'long':
                            $result[$k]='%d';
                            break;
                        case 'float':
                        case 'double':
                        case 'numeric':
                            $result[$k]='%f';
                            break;
                        case 'date':
                        case 'datetime':
                        case 'time':
                            $result[$k]='%s';
                            break;
                    }
                }
            }
            $iwb->Options->setCache($key, $result);
        }
        return $result;
    }
    //create class from $_POST/$_GET variables...so cool! :)
    function qs($class, $prefix='') {
        global $iwb;

        $result=FALSE;
        $instance=$this->newDomainClass($class);
        $columns=$this->getAllColumns($class);
        if($instance!==FALSE) {
            $match=FALSE;
            foreach($columns as $name=>$column) {
                if(isset($column['ui-type']) && $column['ui-type']!='') {
                    $k=$prefix.str_replace('_', '.', $name);
                    $v=$iwb->Utils->qs($k, FALSE);
                    $allowEmpty=FALSE;
                    switch ($column['ui-type']) {
                        case 'toggle':
                        case 'dropdown':
                        case 'tags':
                        case 'check':
                        case 'checklist':
                        case 'radio':
                        case 'radiolist':
                            $allowEmpty=TRUE;
                            break;
                    }

                    if($v!==FALSE || $allowEmpty) {
                        $value=$this->decode($class, $name, $v);
                        if($iwb->Utils->set($instance, $name, $value)) {
                            if($value!=='' || !$allowEmpty) {
                                $match=TRUE;
                            }
                        }
                    }
                }
            }
            if($match) {
                $result=$instance;
            }
        }

        if(is_object($class)) {
            $class=get_class($class);
            $class=str_replace(IWB_PLUGIN_PREFIX, '', $class);
        }
        return $result;
    }
    //decode data from database to class
    public function decode($class, $name, $value) {
        global $iwb;
        if($value!==0 && is_null($value)) {
            return $value;
        }

        $name=str_replace(IWB_PLUGIN_PREFIX, '', $name);
        $name=str_replace('_', '.', $name);
        $column=$this->getColumn($class, $name);
        if($column && isset($column['type'])) {
            switch (strtolower($column['type'])) {
                case 'bool':
                    $value=$iwb->Utils->isTrue($value);
                    break;
                case 'int':
                case 'long':
                case 'float':
                case 'double':
                case 'numeric':
                    if(is_numeric($value)) {
                        $value=$iwb->Utils->parseNumber($value);
                    } else {
                        if(isset($column['ui-type']) && $column['ui-type']=='toggle') {
                            $value=$iwb->Utils->parseNumber($value);
                        } else {
                            $value='';
                        }
                    }
                    break;
                case 'datetime':
                case 'date':
                case 'time':
                    $value=$iwb->Utils->parseDateToTime($value);
                    break;
                case 'array':
                    $value=$iwb->Utils->dbarray($value, FALSE);
                    break;
                case 'json':
                    $value=json_decode($value, TRUE);
                    break;
                default:
                    if(is_array($value)) {
                        if(isset($value['name'])) {
                            $value=$value['name'];
                        } else {
                            $value='';
                        }
                    } else {
                        $value=trim($value);
                        //$value=str_replace("\\'", "'", $value);
                        $value=stripslashes($value);
                    }
                    break;
            }
            if(isset($column['ui-type'])) {
                switch ($column['ui-type']) {
                    case 'toggle':
                        if(is_null($value) || $value==='') {
                            $value=0;
                        }
                        break;
                    case 'timer':
                        if($value!='') {
                            $value=$iwb->Utils->parseTimer($value);
                            $value=$iwb->Utils->formatTimer($value);
                        }
                        break;
                }
            }
        }
        return $value;
    }
    public function isColumnDate($class, $name) {
        $result=FALSE;
        $column=$this->getColumn($class, $name);
        if($column && isset($column['type'])) {
            switch (strtolower($column['type'])) {
                case 'datetime':
                case 'date':
                case 'time':
                    $result=TRUE;
                    break;
            }
        }
        return $result;
    }
    public function isColumnText($class, $name) {
        global $iwb;
        $result=FALSE;
        $column=$this->getColumn($class, $name);
        if($column && isset($column['type'])) {
            if($iwb->Utils->contains($column['type'], 'text') || $iwb->Utils->contains($column['type'], 'char')) {
                $result=TRUE;
            }
        }
        return $result;
    }
    public function isColumnArray($class, $name) {
        $result=FALSE;
        $column=$this->getColumn($class, $name);
        if($column && isset($column['type'])) {
            switch (strtolower($column['type'])) {
                case 'array':
                    $result=TRUE;
                    break;
            }
        }
        return $result;
    }
    public function isColumnNumeric($class, $name) {
        $result=FALSE;
        $column=$this->getColumn($class, $name);
        if($column && isset($column['type'])) {
            switch (strtolower($column['type'])) {
                case 'int':
                case 'long':
                case 'float':
                case 'double':
                case 'numeric':
                    $result=TRUE;
                    break;
            }
        }
        return $result;
    }
    public function encodeQuote($class, $name, $value) {
        return $this->encode($class, $name, $value, TRUE);
    }
    //encode data from class to database including quote if needed
    public function encode($class, $name, $value, $quote) {
        global $iwb;

        $requireQuote=TRUE;
        $column=$this->getColumn($class, $name);
        if($column) {
            switch (strtolower($column['type'])) {
                case 'bool':
                    $value=($iwb->Utils->isTrue($value) ? 1 : 0);
                    break;
                case 'int':
                case 'long':
                case 'float':
                case 'double':
                case 'numeric':
                    $value=''.$iwb->Utils->parseNumber($value);
                    $value=str_replace(',', '.', $value);
                    $requireQuote=FALSE;
                    break;
                case 'datetime':
                    $value=$iwb->Utils->formatSqlDatetime($value);
                    break;
                case 'date':
                    $value=$iwb->Utils->formatSqlDate($value);
                    break;
                case 'time':
                    $value=$iwb->Utils->formatSqlTime($value);
                    break;
                case 'array':
                    $array=$iwb->Utils->dbarray($value, FALSE);
                    $value='';
                    foreach($array as $v) {
                        $value.=','.$v.',';
                    }
                    break;
                case 'json':
                    if(is_array($value) || is_object($value)) {
                        $value=json_encode($value);
                    }
                    break;
                default:
                    if(is_array($value)) {
                        $value=implode('|', $value);
                    } elseif(is_object($value)) {
                        throw new Exception('VALUE OF CLASS='.get_class($value).' CANNOT BE PASSED IN ENCODE');
                    } else {
                        $value=trim($value);
                    }
                    //$value=str_replace('"', '""', $value);
                    break;
            }
        }
        if($requireQuote && $quote) {
            //$value=str_replace('\\', '\\\\"', $value);
            //$value=str_replace('"', '\\"', $value);
            //$value=str_replace('"', '""', $value);
            $value=addslashes($value);
            $value="\"".$value."\"";
        }
        return $value;
    }
    public function newInnerClass($domainClass, $property, $innerClass) {
        global $iwb;
        $result=$this->newDomainClass($innerClass);
        $column=$iwb->Dao->Utils->getColumn($domainClass, $property);
        if(isset($column['defaults']) && $column['defaults']!='') {
            $defaults=$iwb->Utils->toArray($column['defaults']);
            foreach($defaults as $v) {
                $v=explode(':', $v);
                if(count($v)>1) {
                    $k=$v[0];
                    $v=$v[1];
                    $v=$iwb->Utils->getConstant($innerClass, $k, $v);
                    $result->$k=$iwb->Dao->Utils->decode($innerClass, $k, $v);
                }
            }
        }
        return $result;
    }
    public function newDomainClass($class) {
        global $iwb;

        $result=$class;
        if(is_string($class)) {
            $class=$this->getClass($class);
            $result=new $class();

        } elseif(!is_object($class)) {
            return FALSE;
        }

        $columns=$iwb->Dao->Utils->getAllColumns($result);
        foreach($columns as $k=>$v) {
            if(isset($v['default'])&& $v['default']!='') {
                $default=$v['default'];
                $default=str_replace('{url}', IWB_BLOG_URL, $default);
                $default=str_replace('{domain}', $iwb->Utils->trimHttp(IWB_BLOG_URL), $default);
                $default=str_replace('{email}', IWB_BLOG_EMAIL, $default);
                $default=$iwb->Dao->Utils->decode($result, $k, $default);
                $iwb->Utils->set($result, $k, $default);
            }

            if(isset($v['ui-type'])) {
                if($v['ui-type']=='pointer') {
                    $innerClass=$iwb->Dao->Utils->getClass($v['rel']);
                    if ($v['instance']!='') {
                        $k=$v['instance'];
                        $v=$this->newInnerClass($result, $k, $innerClass);
                        $iwb->Utils->set($result, $k, $v);
                    }
                } elseif($v['ui-type']=='toggle') {
                    $t=$iwb->Utils->get($result, $k);
                    if(is_null($t)) {
                        $iwb->Utils->set($result, $k, 0);
                    }
                }
            }
        }
        return $result;
    }
    public function getUiFields($instance, $prefix='', $all=TRUE) {
        $result=array();
        $columns=$this->getAllColumns($instance);
        foreach($columns as $k=>$v) {
            if (!isset($v['ui-type']) || $v['ui-type'] == '' || $v['ui-type']=='pointer') {
                continue;
            }
            if(isset($v['alias']) && $v['alias']!='') {
                continue;
            }

            $k=$prefix.$k;
            $k=str_replace('.', '_', $k);
            if($all || !is_null($v)) {
                $result[$k]=$v;
            }
        }
        return $result;
    }
    public function isColumnVisible($instance, $name) {
        global $iwb;
        $column=$this->getColumn($instance, $name);
        $result=TRUE;
        if(isset($column['ui-visible']) && $column['ui-visible']!='') {
            $visible=$column['ui-visible'];
            $conditions=explode('&', $visible);
            $all=TRUE;
            foreach($conditions as $c) {
                $options=explode(':', $c);
                $k=$options[0];
                $v=$iwb->Utils->get($instance, $k, '');
                $options=$options[1];
                $options=$iwb->Utils->toArray($options);
                if(!in_array($v, $options)) {
                    $all=FALSE;
                    break;
                }
            }

            if(!$all) {
                $result=FALSE;
            }
        }
        return $result;
    }
}