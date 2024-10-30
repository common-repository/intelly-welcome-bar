<?php
function iwb_ui_track($always=FALSE) {
    global $iwb;
    $settings=$iwb->Options->getPluginSettings();
    $iwb->Options->setPluginSettings($settings);

    $track=$iwb->Utils->qs('track', '');
    if($track!='') {
        $settings->allowUsageTracking=intval($track);
        $iwb->Options->setPluginSettings($settings);
    }

    if(!$always && $iwb->Options->isTrackingEnable()) {
        return;
    }

    if($iwb->Options->isTrackingEnable()) {
        $arg=array('track'=>0);
        $uri=$iwb->Utils->addQueryString($arg, IWB_TAB_SETTINGS_URI);
        $iwb->Options->pushSuccessMessage('Tracking.Enabled', $uri);
    } else {
        $arg=array('track'=>1);
        $uri=$iwb->Utils->addQueryString($arg, IWB_TAB_SETTINGS_URI);
        $iwb->Options->pushWarningMessage('Tracking.Disabled', $uri);
    }
    $iwb->Options->writeMessages();
}
function iwb_ui_settings() {
    global $iwb;

    ?>
    <h2><?php $iwb->Lang->P('Title.Settings')?></h2>
    <?php
    iwb_ui_track(TRUE);
    $settings=$iwb->Options->getPluginSettings();
    $iwb->Form->prefix='License';
    if($iwb->Check->nonce()) {
        if($iwb->Check->is('_action', 'Save')) {
            /* @var $newSettings IWB_PluginSettings */
            $newSettings=$iwb->Dao->Utils->qs('IWB_PluginSettings');
            //$newSettings->allowUsageTracking=$settings->allowUsageTracking;
            $iwb->Options->setPluginSettings($newSettings);
        }
    }

    $settings=$iwb->Options->getPluginSettings();
    $iwb->Options->writeMessages();

    $iwb->Form->formStarts();
    {
        $iwb->Form->openPanel('PluginSection');
        {
            $fields='^httpReferer|allowUsageTracking|showPoweredBy';
            $iwb->Form->inputsForm($fields, $settings);

            $buttons=array();
            $button=array(
                'submit'=>TRUE
            );
            $buttons['Save']=$button;
            $options=array('buttons'=>$buttons);
        }
        $iwb->Form->closePanel($options);
    }
    $iwb->Form->formEnds();
}