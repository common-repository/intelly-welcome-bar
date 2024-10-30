<?php

class IWB_PluginOptions extends IWB_Options {
    public function __construct() {
    }

    //ShortcodeUsed
    public function isShortcodeUsed() {
        return $this->getRequest('ShortcodeUsed', FALSE);
    }
    public function setShortcodeUsed($value) {
        $this->setRequest('ShortcodeUsed', $value);
    }

    //ArrayCountdowns
    public function getArrayWelcomeBars() {
        $result=$this->getOption('ArrayWelcomeBars', array());
        if(!is_array($result)) {
            $result=array();
        }
        foreach($result as $k=>$v) {
            if($v===FALSE || !($v instanceof IWB_WelcomeBar)) {
                unset($result[$k]);
            }
        }
        return $result;
    }
    public function setArrayWelcomeBars($array) {
        $this->setOption('ArrayWelcomeBars', $array);
    }

    //PluginSettings
    public function getPluginSettings() {
        /* @var $result IWB_PluginSettings */
        $result=$this->getClassOption('IWB_PluginSettings', 'PluginSettings');
        if($result->allowUsageTracking===null) {
            $result->allowUsageTracking=0;
        }
        if($result->showPoweredBy===null) {
            $result->showPoweredBy=0;
        }
        if($result->httpReferer=='') {
            $result->httpReferer='welcome';
        }
        return $result;
    }
    public function setPluginSettings(IWB_PluginSettings $value, $overwrite=FALSE) {
        global $iwb;
        $current=$this->getPluginSettings();
        $this->setTrackingEnable($value->allowUsageTracking ? 1 : 0);
        if($current->allowUsageTracking!=$value->allowUsageTracking) {
            $iwb->Tracking->sendTracking(TRUE);
        }
        $this->setClassOption('PluginSettings', $value, $overwrite);
    }
}