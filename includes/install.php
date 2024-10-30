<?php
register_activation_hook(IWB_PLUGIN_FILE, 'iwb_install');
function iwb_install($networkwide=NULL) {
	global $wpdb, $iwb;

    $time=$iwb->Options->getPluginInstallDate();
    if($time==0) {
        $iwb->Options->setPluginInstallDate(time());
        $iwb->Options->setTrackingEnable(TRUE);
        $iwb->Tracking->sendTracking(TRUE);
    } elseif($iwb->Options->isTrackingEnable()) {
        $iwb->Tracking->sendTracking(TRUE);
    }
    //iwb_database_update();
    $iwb->Options->setPluginUpdateDate(time());
    $iwb->Options->setPluginFirstInstall(TRUE);
    $iwb->Options->setTrackingLastSend(0);
}

/*function iwb_database_update($force=FALSE) {
    global $ec;

    //remove OLD CAE issue
    $crons=_get_cron_array();
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
    _set_cron_array($crons);

    $md5=$ec->Options->getDatabaseVersion();
    $compare=$ec->Dao->Utils->getDatabaseVersion();
    if($force || $md5!=$compare) {
        if($ec->Dao->Utils->databaseUpdate()) {
            $ec->Options->setDatabaseVersion($compare);
            $ec->Options->setDatabaseUpdateDate(time());
        }
    }
}*/

add_action('admin_init', 'iwb_first_redirect');
function iwb_first_redirect() {
    global $iwb;
    if ($iwb->Options->isPluginFirstInstall()) {
        $iwb->Options->setPluginFirstInstall(FALSE);
        $iwb->Options->setShowActivationNotice(TRUE);

        $iwb->Options->setShowWhatsNew(FALSE); //TRUE
        $iwb->Utils->redirect(IWB_TAB_SETTINGS_URI);
    }
}



