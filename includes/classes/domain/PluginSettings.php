<?php
if (!defined('ABSPATH')) exit;

//@iwp
class IWB_PluginSettings {
    //@type=text
    //@ui-type=text
    var $licenseKey;
    //@type=int
    //@ui-type=number @ui-readonly
    var $licenseSiteCount;
    //@type=int
    //@ui-type=number @ui-readonly
    var $licensePlan;

    //@type=varchar @len=255
    //@ui-type=text
    var $httpReferer;

    //@type=int
    //@ui-type=toggle
    var $allowUsageTracking;
    //@type=int
    //@ui-type=toggle
    var $debugMode;
    //@type=int
    //@ui-type=toggle
    var $showPoweredBy;

    public function __costruct() {

    }
}