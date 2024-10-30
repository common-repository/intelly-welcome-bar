<?php
if (!defined('ABSPATH')) exit;

class IWB_Options {
    var $vars;
    private $cache;
    public function __construct() {
        $this->vars=array();
        $this->cache=array();
    }

    //always add a prefix to avoid conflicts with other plugins
    protected function getKey($key) {
        return 'IWB_'.$key;
    }
    //option
    protected function removeOption($key) {
        $key=$this->getKey($key);
        delete_option($key);
        unset($this->cache[$key]);
    }
    protected function getOption($key, $default=FALSE) {
        $key=$this->getKey($key);
        if(isset($this->cache[$key])) {
            $result=$this->cache[$key];
        } else {
        $result=get_option($key, $default);
            $this->cache[$key]=$result;
        }
        if(is_string($result)) {
            $result=trim($result);
        }
        return $result;
    }
    protected function setOption($key, $value, $autoload=TRUE) {
        $key=$this->getKey($key);
        if(is_bool($value)) {
            $value=($value ? 1 : 0);
        }
        $autoload=($autoload ? 'yes' : 'no');
        update_option($key, $value, $autoload);
        $this->cache[$key]=$value;
    }

    //Session
    protected function removeSession($key) {
        $session=IWB_Session::get_instance();
        if(isset($session[$key])) {
            unset($session[$key]);
        }
    }
    protected function getClassSession($class, $key, $default=FALSE) {
        global $iwb;
        $class=$iwb->Dao->Utils->getClass($class);
        $result=$this->getSession($key, $default);
        if($result!==$default) {
            if(is_object($result)) {
                $result=(array)$result;
            }
            $result=$iwb->Utils->jsonToClass($result, $class);
            if($result===FALSE) {
                $result=$default;
            }
        }
        return $result;
    }
    protected function setClassSession($class, $key, $value) {
        global $iwb;
        if(is_object($value)) {
            $value=$iwb->Utils->classToJson($value);
        }
        $this->setSession($key, $value);
    }

    protected function getClassOption($class, $key) {
        global $iwb;
        $class=$iwb->Dao->Utils->getClass($class);
        $result=$this->getOption($key, FALSE);
        if($result!==FALSE) {
            if(is_object($result)) {
                $result=(array)$result;
            }
            $result=$iwb->Utils->jsonToClass($result, $class);
        }
        if($result===FALSE) {
            $result=$iwb->Dao->Utils->newDomainClass($class);
        }
        return $result;
    }
    protected function setClassOption($key, $value, $overwrite=FALSE) {
        global $iwb;
        $class=get_class($value);
        $value=(array)$value;
        if(!$overwrite) {
            $previous=(array)$this->getClassOption($class, $key);
            $data=$iwb->Utils->parseArgs($value, $previous);
            $data=$iwb->Utils->classToJson($data);
            $value=$data;
        }
        $this->setOption($key, $value);
    }
    protected function getSession($key, $default=FALSE) {
        global $iwb;
        $session=IWB_Session::get_instance();
        $result=$default;
        if(isset($session[$key])) {
            $result=$session[$key];
            if(is_string($result)) {
                $result=json_decode($result, TRUE);
            }

            if(isset($session[$key.'Class'])) {
                $class=$session[$key.'Class'];
                if($class!='') {
                    $result=$iwb->Utils->jsonToClass($result, $class);
                }
            }
        }
        if(is_null($result)) {
            $result=$default;
        }
        return $result;
    }
    protected function setSession($key, $value) {
        $session=IWB_Session::get_instance();
        $class='';
        if(is_object($value)) {
            $class=get_class($value);
            $value=(array)$value;
        }
        $value=json_encode($value);
        $session[$key]=$value;
        if($class!='') {
            $session[$key.'Class']=$class;
        } elseif(isset($session[$key.'Class'])) {
            unset($session[$key.'Class']);
        }
    }

    //$_REQUEST
    //However WP enforces its own logic - during load process wp_magic_quotes() processes variables to emulate magic quotes setting and enforces $_REQUEST to contain combination of $_GET and $_POST, no matter what PHP configuration says.
    protected function removeRequest($key) {
        $key=$this->getKey($key);
        if(isset($this->vars[$key])) {
            unset($this->vars[$key]);
        }
    }
    protected function getRequest($key, $default=FALSE) {
        $key=$this->getKey($key);
        $result=$default;
        if(isset($this->vars[$key])) {
            $result=$this->vars[$key];
        }
        return $result;
    }
    protected function setRequest($key, $value) {
        $key=$this->getKey($key);
        $this->vars[$key]=$value;
    }

    protected function removeCookie($key) {
        $key=$this->getKey($key);
        if(isset($_COOKIE[$key])) {
            unset($_COOKIE[$key]);
        }
    }
    protected function getCookie($key, $default=FALSE) {
        $key=$this->getKey($key);
        $result=$default;
        if(isset($_COOKIE[$key])) {
            $result=$_COOKIE[$key];
        }
        return $result;
    }
    protected function setCookie($key, $value) {
        $key=$this->getKey($key);
        if(is_array($value)) {
            $value=implode('|', $value);
        }
        $_COOKIE[$key]=$value;
        $expire=time() + (10 * 365 * 24 * 60 * 60);
        setcookie($key, $value, $expire, "/");
    }

    //ShowWhatsNew
    public function isShowWhatsNew() {
        return intval($this->getOption('ShowWhatsNew', FALSE));
    }
    public function setShowWhatsNew($value) {
        $this->setOption('ShowWhatsNew', $value);
    }

    //TrackingEnable
    public function isTrackingEnable() {
        return intval($this->getOption('TrackingEnable', 0));
    }
    public function setTrackingEnable($value) {
        $this->setOption('TrackingEnable', $value);
    }
    //TrackingNotice
    public function isTrackingNotice() {
        return intval($this->getOption('TrackingNotice', 0));
    }
    public function setTrackingNotice($value) {
        $this->setOption('TrackingNotice', $value);
    }

    public function isActive() {
        return intval($this->getOption('Active', 0));
    }
    public function setActive($value) {
        $this->setOption('Active', $value);
    }

    public function getTrackingLastSend() {
        return $this->getOption('TrackingLastSend['.IWB_PLUGIN_SLUG.']', 0);
    }
    public function setTrackingLastSend($value) {
        $this->setOption('TrackingLastSend['.IWB_PLUGIN_SLUG.']', $value);
    }
    public function getPluginInstallDate() {
        return $this->getOption('PluginInstallDate['.IWB_PLUGIN_SLUG.']', 0);
    }
    public function setPluginInstallDate($value) {
        $this->setOption('PluginInstallDate['.IWB_PLUGIN_SLUG.']', $value);
    }
    public function getPluginUpdateDate() {
        return $this->getOption('PluginUpdateDate['.IWB_PLUGIN_SLUG.']', 0);
    }
    public function setPluginUpdateDate($value) {
        $this->setOption('PluginUpdateDate['.IWB_PLUGIN_SLUG.']', $value);
    }

    public function isPluginFirstInstall() {
        return $this->getOption('PluginFirstInstall', FALSE);
    }
    public function setPluginFirstInstall($value) {
        $this->setOption('PluginFirstInstall', $value);
    }
    public function isShowActivationNotice() {
        return $this->getOption('ShowActivationNotice', FALSE);
    }
    public function setShowActivationNotice($value) {
        $this->setOption('ShowActivationNotice', $value);
    }

    public function getDatabaseVersion() {
        return $this->getOption('DatabaseVersion', '');
    }
    public function setDatabaseVersion($value) {
        $this->setOption('DatabaseVersion', $value);
    }
    public function getDatabaseUpdateDate() {
        return $this->getOption('DatabaseUpdateDate', 0);
    }
    public function setDatabaseUpdateDate($value) {
        $this->setOption('DatabaseUpdateDate', $value);
    }
    //LoggerEnable
    public function isLoggerEnable() {
        global $iwb;
        $settings=$iwb->Options->getPluginSettings();
        return $settings->debugMode;
    }
    public function setLoggerEnable($value) {
        $this->setOption('LoggerEnable', $value);
    }

    public function getFeedbackEmail() {
        return $this->getOption('FeedbackEmail', get_bloginfo('admin_email'));
    }
    public function setFeedbackEmail($value) {
        $this->setOption('FeedbackEmail', $value);
    }

    //Cache
    private function getCacheName($array) {
        if(!is_array($array)) {
            $array=array($array);
        }
        $result='Cache';
        foreach($array as $v) {
            if(is_object($v)) {
                $v=get_class($v);
            } elseif(is_array($v)) {
                $v=$v[0];
                if(is_object($v)) {
                    $v=get_class($v);
                }
            }
            $result.='_'.$v;
        }
        return $result;
    }
    public function getCache($name, $callable=NULL) {
        $key=$this->getCacheName($name);
        $result=$this->getRequest($key, FALSE);
        if($result===FALSE && $callable && is_callable($callable)) {
            $result=$callable();
            $this->setCache($name, $result);
        }
        return $result;
    }
    public function setCache($name, $value) {
        $key=$this->getCacheName($name);
        $this->setRequest($key, $value);
    }

    private function hasGenericMessages($type) {
        $result=$this->getSession($type.'Messages', NULL);
        return (is_array($result) && count($result)>0);
    }

    private function pushGenericMessage($type, $message, $v1=NULL, $v2=NULL, $v3=NULL, $v4=NULL, $v5=NULL) {
        global $iwb;
        $array=$this->getSession($type.'Messages', array());
        $m=$iwb->Lang->L($message, $v1, $v2, $v3, $v4, $v5);
        $exists=FALSE;
        foreach($array as $v) {
            if($v==$m) {
                $exists=TRUE;
                break;
            }
        }
        if(!$exists) {
            $array[]=$m;
            $this->setSession($type.'Messages', $array);
        }
    }
    private function writeGenericMessages($type, $clean=TRUE) {
        global $iwb;
        $result=FALSE;
        $array=$this->getSession($type.'Messages', array());
        if(is_array($array) && count($array)>0) {
            $result=TRUE;
            $text="<p>".$iwb->Utils->implode("", "", "<br/>\n", $array)."</p>";
            $iwb->Ui->alert($type, $text);
            ?>
        <?php }
        if($clean) {
            $this->removeSession($type.'Messages');
        }
        return $result;
    }
    //WarningMessages
    public function hasWarningMessages() {
        return $this->hasGenericMessages('Warning');
    }
    public function pushWarningMessage($message, $v1=NULL, $v2=NULL, $v3=NULL, $v4=NULL, $v5=NULL) {
        return $this->pushGenericMessage('Warning', $message, $v1, $v2, $v3, $v4, $v5);
    }
    public function writeWarningMessages($clean=TRUE) {
        return $this->writeGenericMessages('Warning', $clean);
    }
    //SuccessMessages
    public function hasSuccessMessages() {
        return $this->hasGenericMessages('Success');
    }
    public function pushSuccessMessage($message, $v1=NULL, $v2=NULL, $v3=NULL, $v4=NULL, $v5=NULL) {
        return $this->pushGenericMessage('Success', $message, $v1, $v2, $v3, $v4, $v5);
    }
    public function writeSuccessMessages($clean=TRUE) {
        return $this->writeGenericMessages('Success', $clean);
    }
    //InfoMessages
    public function hasInfoMessages() {
        return $this->hasGenericMessages('Info');
    }
    public function pushInfoMessage($message, $v1=NULL, $v2=NULL, $v3=NULL, $v4=NULL, $v5=NULL) {
        return $this->pushGenericMessage('Info', $message, $v1, $v2, $v3, $v4, $v5);
    }
    public function writeInfoMessages($clean=TRUE) {
        return $this->writeGenericMessages('Info', $clean);
    }
    //ErrorMessages
    public function hasErrorMessages() {
        return $this->hasGenericMessages('Error');
    }
    public function pushErrorMessage($message, $v1=NULL, $v2=NULL, $v3=NULL, $v4=NULL, $v5=NULL) {
        return $this->pushGenericMessage('Error', $message, $v1, $v2, $v3, $v4, $v5);
    }
    public function writeErrorMessages($clean=TRUE) {
        return $this->writeGenericMessages('Error', $clean);
    }

    public function clearMessages() {
        $this->removeSession('InfoMessages');
        $this->removeSession('ErrorMessages');
        $this->removeSession('SuccessMessages');
        $this->removeSession('WarningMessages');
    }
    public function writeMessages($clean=TRUE) {
        $result=FALSE;
        if($this->writeInfoMessages($clean)) {
            $result=TRUE;
        }
        if($this->writeSuccessMessages($clean)) {
            $result=TRUE;
        }
        if($this->writeWarningMessages($clean)) {
            $result=TRUE;
        }
        if($this->writeErrorMessages($clean)) {
            $result=TRUE;
        }

        return $result;
    }
    public function pushMessage($success, $message, $v1=NULL, $v2=NULL, $v3=NULL, $v4=NULL, $v5=NULL) {
        if($success) {
            $this->pushSuccessMessage($message.'Success', $v1, $v2, $v3, $v4, $v5);
        } else {
            $this->pushErrorMessage($message.'Error', $v1, $v2, $v3, $v4, $v5);
        }
    }
    public function pushExMessage($success, $message, $v1=NULL, $v2=NULL, $v3=NULL, $v4=NULL, $v5=NULL) {
        if($success) {
            $this->pushSuccessMessage($message.'Success', $v1, $v2, $v3, $v4, $v5);
        } else {
            $this->pushWarningMessage($message.'Error', $v1, $v2, $v3, $v4, $v5);
        }
    }


    //License
    public function getLicense() {
        return $this->getOption('License', FALSE);
    }
    public function setLicense($value) {
        $this->setOption('License', $value);
    }
    //LicenseKey
    public function getLicenseKey() {
        return $this->getOption('LicenseKey', '');
    }
    public function setLicenseKey($value) {
        $this->setOption('LicenseKey', $value);
    }
    //LicenseStatus
    public function isLicenseSuccess() {
        return $this->getOption('LicenseSuccess', 0);
    }
    public function setLicenseSuccess($value) {
        $this->setOption('LicenseSuccess', $value);
    }
    //LicenseLastCheck
    public function getLicenseLastCheck() {
        return intval($this->getOption('LicenseLastCheck', 0));
    }
    public function setLicenseLastCheck($value) {
        $this->setOption('LicenseLastCheck', intval($value));
    }
}