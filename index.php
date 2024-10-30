<?php
/*
Plugin Name: Welcome Bar
Plugin URI: https://intellywp.com/welcome-bar/
Description: Increase engagement and drive specific offers to the visitors coming from a specific traffic source. As seen on BetaList and ProductHunt.
Author: IntellyWP
Author URI: http://intellywp.com/
Email: support@intellywp.com
Version: 2.0.4
*/
if(defined('IWB_PLUGIN_NAME')) {
    function IWB_FREE_admin_notices() {
        global $iwb; ?>
        <div style="clear:both"></div>
        <div class="error iwp" style="padding:10px;">
            <?php $iwb->Lang->P('PluginProAlreadyInstalled'); ?>
        </div>
        <div style="clear:both"></div>
    <?php }
    add_action('admin_notices', 'IWB_FREE_admin_notices');
    return;
}

define('IWB_PLUGIN_PREFIX', 'IWB_');
define('IWB_PLUGIN_FILE',__FILE__);
define('IWB_PLUGIN_SLUG', 'intelly-welcome-bar');
define('IWB_PLUGIN_NAME', 'Welcome Bar');
define('IWB_PLUGIN_VERSION', '2.0.4');
define('IWB_PLUGIN_AUTHOR', 'IntellyWP');
define('IWB_PLUGIN_DIR', dirname(__FILE__).'/');

define('IWB_PLUGIN_URI', plugins_url('/', __FILE__));
define('IWB_PLUGIN_ASSETS_URI', IWB_PLUGIN_URI.'assets/');
define('IWB_PLUGIN_IMAGES_URI', IWB_PLUGIN_ASSETS_URI.'images/');

define('IWB_LOGGER', FALSE);
define('IWB_AUTOSAVE_LANG', FALSE);

define('IWB_QUERY_POSTS_OF_TYPE', 1);
define('IWB_QUERY_POST_TYPES', 2);
define('IWB_QUERY_CATEGORIES', 3);
define('IWB_QUERY_TAGS', 4);

define('IWB_ENGINE_SEARCH_CATEGORIES_TAGS', 0);
define('IWB_ENGINE_SEARCH_CATEGORIES', 1);
define('IWB_ENGINE_SEARCH_TAGS', 2);

define('IWB_INTELLYWP_SITE', 'https://intellywp.com/');
define('IWB_INTELLYWP_ENDPOINT', IWB_INTELLYWP_SITE.'wp-content/plugins/intellywp-manager/data.php');
define('IWB_PAGE_FAQ', IWB_INTELLYWP_SITE.IWB_PLUGIN_SLUG);
define('IWB_PAGE_PREMIUM', IWB_INTELLYWP_SITE.IWB_PLUGIN_SLUG);
define('IWB_PAGE_HOME', admin_url().'options-general.php?page='.IWB_PLUGIN_SLUG);

define('IWB_TAB_PLUGINS', 'plugins');
define('IWB_TAB_PLUGINS_URI', 'https://intellywp.com/plugins/');
define('IWB_TAB_DOCS', 'docs');
define('IWB_TAB_DOCS_URI', 'https://intellywp.com/docs/welcome-bar/');
define('IWB_TAB_SUPPORT', 'support');
define('IWB_TAB_SUPPORT_URI', 'https://intellywp.com/contact/');
define('IWB_TAB_PREMIUM_URI', 'https://intellywp.com/welcome-bar/');

define('IWB_TAB_SETTINGS', 'settings');
define('IWB_TAB_SETTINGS_URI', IWB_PAGE_HOME.'&tab='.IWB_TAB_SETTINGS);
define('IWB_TAB_EDITOR', 'editor');
define('IWB_TAB_EDITOR_URI', IWB_PAGE_HOME.'&tab='.IWB_TAB_EDITOR);
define('IWB_TAB_MANAGER', 'manager');
define('IWB_TAB_MANAGER_URI', IWB_PAGE_HOME.'&tab='.IWB_TAB_MANAGER);
define('IWB_TAB_WHATS_NEW', 'whatsnew');
define('IWB_TAB_WHATS_NEW_URI', IWB_PAGE_HOME.'&tab='.IWB_TAB_WHATS_NEW);

define('IWB_BLOG_URL', get_bloginfo('wpurl'));
define('IWB_BLOG_EMAIL', get_bloginfo('admin_email'));
define('IWB_BARS_LIMIT', 3);

/*if (!function_exists('hex2bin')) {
    function hex2bin($str) {
        $result="";
        $len=strlen($str);
        for ($i=0; $i<$len; $i+=2) {
            $result.=pack("H*", substr($str, $i, 2));
        }
        return $result;
    }
}*/

include_once(dirname(__FILE__).'/autoload.php');
iwb_include_php(dirname(__FILE__).'/includes/');

global $iwb;
$iwb=new IWB_Singleton();
$iwb->init();
