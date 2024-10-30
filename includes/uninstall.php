<?php
register_deactivation_hook(IWB_PLUGIN_FILE, 'iwb_uninstall');
function iwb_uninstall($networkwide=NULL) {
	global $wpdb, $iwb;
    $iwb->Options->setActive(FALSE);
}
?>