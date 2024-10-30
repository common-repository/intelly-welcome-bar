<?php
class IWB_Singleton {
    var $Lang;
    var $Utils;
    var $Options;
    var $Log;
    var $Cron;
    var $Tracking;
    var $Tabs;
    var $Lazy;
    var $Ui;
    var $Manager;
    var $Dao;

    var $Form;
    var $Check;

    function __construct() {
        $this->Lang=new IWB_Language();
        $this->Utils=new IWB_Utils();
        $this->Options=new IWB_PluginOptions();
        $this->Log=new IWB_Logger();
        $this->Cron=new IWB_Cron();
        $this->Tracking=new IWB_Tracking();
        $this->Tabs=new IWB_Tabs();
        $this->Lazy=new IWB_LazyLoader();
        $this->Dao=new IWB_Dao();
        $this->Ui=new IWB_Ui();
        $this->Manager=new IWB_Manager();
        $this->Form=new IWB_CrazyForm();
        $this->Check=new IWB_Check();
    }
    function init() {
        $this->Lang->load('iwb', IWB_PLUGIN_DIR.'languages/Lang.txt');
        $this->Lang->bundle->autoPush=TRUE;
        $this->Dao->Utils->load(IWB_PLUGIN_PREFIX, IWB_PLUGIN_DIR.'includes/classes/domain/');
        $this->Tabs->init();
        $this->Manager->init();
        $this->Cron->init();
    }
}