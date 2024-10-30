<?php
if (!defined('ABSPATH')) exit;

class IWB_Tracking {
    public function __construct() {
        add_action('iwb_weekly_scheduled_events', array($this, 'sendTracking'));
    }

    private function getThemeData() {
        $theme_data=wp_get_theme();
        $theme=array(
            'name'=>$theme_data->display('Name', false, false),
            'theme_uri'=>$theme_data->display('ThemeURI', false, false),
            'version'=>$theme_data->display('Version', false, false),
            'author'=>$theme_data->display('Author', false, false),
            'author_uri'=>$theme_data->display('AuthorURI', false, false),
        );
        $theme_template=$theme_data->get_template();
        if ($theme_template !=='' && $theme_data->parent()) {
            $theme['template']=array(
                'version'=>$theme_data->parent()->display('Version', false, false),
                'name'=>$theme_data->parent()->display('Name', false, false),
                'theme_uri'=>$theme_data->parent()->display('ThemeURI', false, false),
                'author'=>$theme_data->parent()->display('Author', false, false),
                'author_uri'=>$theme_data->parent()->display('AuthorURI', false, false),
            );
        }
        else {
            $theme['template']='';
        }
        unset($theme_template);
        return $theme;
    }
    private function getPluginData() {
        if(!function_exists('get_plugins')) {
            include ABSPATH.'/wp-admin/includes/plugin.php';
        }

        $plugins=array();
        $active_plugin=get_option('active_plugins');
        foreach ($active_plugin as $plugin_path) {
            if (! function_exists('get_plugin_data')) {
                require_once(ABSPATH.'wp-admin/includes/plugin.php');
            }
            $plugin_info=get_plugin_data(WP_PLUGIN_DIR.'/'.$plugin_path);
            $slug=str_replace('/'.basename($plugin_path), '', $plugin_path);
            $plugins[$slug]=array(
                'version'=>$plugin_info['Version']
                , 'name'=>$plugin_info['Name']
                , 'plugin_uri'=>$plugin_info['PluginURI']
                , 'author'=>$plugin_info['AuthorName']
                , 'author_uri'=>$plugin_info['AuthorURI']
           );
        }
        unset($active_plugins, $plugin_path);
        return $plugins;
    }
    //obtain tracking data into an associative array
    public function getData() {
        global $iwb;

        //retrieve blog info
        $result['wp_url']=home_url();
        $result['wp_version']=get_bloginfo('version');
        $result['wp_language']=get_bloginfo('language');
        $result['wp_wpurl']=get_bloginfo('wpurl');
        $result['wp_admin_email']=get_bloginfo('admin_email');

        $result['plugins']=$this->getPluginData();
        $result['theme']=$this->getThemeData();

        //to obtain for each post type its count
        $post_types=$iwb->Utils->query(IWB_QUERY_POST_TYPES);
        $data=array();
        foreach ($post_types as $v) {
            $v=$v['name'];
            $data[$v]=intval(wp_count_posts($v)->publish);
        }
        $result['post_types']=$data;

        $data=array();
        $result['iwpm_plugin_name']=IWB_PLUGIN_SLUG;
        $result['iwpm_plugin_version']=IWB_PLUGIN_VERSION;
        $result['iwpm_plugin_data']=$data;
        $result['iwpm_plugin_install_date']=$iwb->Options->getPluginInstallDate();
        $result['iwpm_plugin_update_date']=$iwb->Options->getPluginUpdateDate();

        $result['iwpm_tracking_enable']=$iwb->Options->isTrackingEnable();
        $result['iwpm_logger_enable']=$iwb->Options->isLoggerEnable();
        $result['iwpm_feedback_email']=$iwb->Options->getFeedbackEmail();
        return $result;
    }

    public function sendTracking($override=FALSE) {
        global $iwb;

        $result=-1;
        if(!$override && !$iwb->Options->isTrackingEnable())
            return $result;

        // Send a maximum of once per week
        $last_send=$iwb->Options->getTrackingLastSend();
        if(!$override && $last_send>strtotime('-1 week'))
            return $result;

        //add_filter('https_local_ssl_verify', '__return_false');
        //add_filter('https_ssl_verify', '__return_false');
        //add_filter('block_local_requests', '__return_false');
        $data=$iwb->Utils->remotePost('usage', $this->getData());
        if($data) {
            $result=intval($data['id']);
            $iwb->Options->setTrackingLastSend(time());
        }
        return $result;
    }
}