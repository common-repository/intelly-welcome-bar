<?php
if (!defined('ABSPATH')) exit;

class IWB_Cron {
	public function __construct() {

	}
    public function init() {
        add_filter('cron_schedules', array($this, 'addSchedules'));
        $this->scheduleEvents();
    }

    public function addSchedules($schedules=array()){
        /*$schedules[IWB_PLUGIN_PREFIX.'daily']=array(
            'interval'=> 86400
            , 'display'=>'{'.IWB_PLUGIN_NAME.'} Daily'
        );
        $schedules[IWB_PLUGIN_PREFIX.'weekly']=array(
            'interval'=> 604800
            , 'display'=>'{'.IWB_PLUGIN_NAME.'} Weekly'
        );
        $schedules[IWB_PLUGIN_PREFIX.'each1hour']=array(
            'interval'=> 60*60
            , 'display'=>'{'.IWB_PLUGIN_NAME.'} Each 1 hour'
        );
        $schedules[IWB_PLUGIN_PREFIX.'each1minute']=array(
            'interval'=> 10
            , 'display'=>'{'.IWB_PLUGIN_NAME.'} Each 1 minute'
        );*/
        return $schedules;
    }
    public function scheduleEvents() {

    }
    private function wpScheduleEvent($recurrence, $function) {
        global $iwb;
        if(!$iwb->Utils->functionExists($function)) {
            return;
        }

        //iwb_scheduler_daily|iwb_scheduler_weekly
        /*$crons=_get_cron_array();
        foreach($crons as $time=>$jobs) {
            foreach($jobs as $k=>$v) {
                switch (strtolower($k)) {
                    case 'iwb_scheduler_daily':
                    case 'iwb_scheduler_weekly':
                        unset($jobs[$k]);
                        break;
                }
                if(count($jobs)==0) {
                    unset($crons[$time]);
                }
            }
        }
        _set_cron_array($crons);*/

        $hook='cron_'.IWB_PLUGIN_PREFIX.$recurrence.'_'.$iwb->Utils->getFunctionName($function);
        if(!wp_next_scheduled($hook)) {
            wp_schedule_event(time(), $recurrence, $hook);
        }
        add_action($hook, $function);
    }
}
