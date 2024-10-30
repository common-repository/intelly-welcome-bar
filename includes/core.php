<?php
function IWB_wp_head() {
    global $iwb;
    if($iwb->Options->isShortcodeUsed()) {
        return;
    }

    $settings=$iwb->Options->getPluginSettings();
    $key=(isset($_GET[$settings->httpReferer]) ? $_GET[$settings->httpReferer] : '');
    if($key!='') {
        $welcome=$iwb->Manager->get($key);
        if($welcome===FALSE) {
            return;
        }

        if($welcome->active) {
            $iwb->Ui->WelcomeBar->style($welcome);
            $iwb->Ui->WelcomeBar->html($welcome);
            $iwb->Ui->WelcomeBar->script($welcome);
        }
    }
}
add_filter('wp_head', 'IWB_wp_head');

function IWB_wp_footer() {
    global $iwb;
}
add_filter('wp_footer', 'IWB_wp_footer');

function IWB_admin_footer() {
    global $iwb;
    if($iwb->Lang->bundle->autoPush && IWB_AUTOSAVE_LANG) {
        $iwb->Lang->bundle->store(IWB_PLUGIN_DIR.'languages/Lang.txt');
    }
}
add_filter('admin_footer', 'IWB_admin_footer');

function iwb_ui_first_time() {
    global $iwb;
    if($iwb->Options->isShowActivationNotice()) {
        //$tcmp->Options->pushSuccessMessage('FirstTimeActivation');
        //$tcmp->Options->writeMessages();
        $iwb->Options->setShowActivationNotice(FALSE);
    }
}
