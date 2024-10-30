<?php
//from Settings_API_Tabs_Demo_Plugin
class IWB_Tabs {
    private $tabs=array();

    function init() {
        global $iwb;
        add_filter('wp_enqueue_scripts', array(&$this, 'siteEnqueueScripts'));
        if($iwb->Utils->isAdminUser()) {
            add_action('admin_menu', array(&$this, 'attachMenu'));
            add_filter('plugin_action_links', array(&$this, 'pluginActions'), 10, 2);
            if($iwb->Utils->isPluginPage()) {
                add_action('admin_enqueue_scripts', array(&$this, 'adminEnqueueScripts'), 9999);
            }
        }
    }

    function attachMenu() {
        global $iwb;
        if($iwb->Utils->isAdminUser()) {
            add_submenu_page('options-general.php'
                , IWB_PLUGIN_NAME, IWB_PLUGIN_NAME
                , 'manage_options', IWB_PLUGIN_SLUG, array(&$this, 'showTabPage'));
        }
    }
    function pluginActions($links, $file) {
        global $iwb;
        if($file==IWB_PLUGIN_SLUG.'/index.php'){
            $settings=array();
            $settings[]="<a href='".IWB_TAB_MANAGER_URI."'>".$iwb->Lang->L('Settings').'</a>';
            $settings[]="<a href='".IWB_TAB_PREMIUM_URI."'>".$iwb->Lang->L('PREMIUM').'</a>';
            $links=array_merge($settings, $links);
        }
        return $links;
    }
    function siteEnqueueScripts() {
        wp_enqueue_script('jquery');
        $this->wpEnqueueScript('assets/js/library.js');
    }
    function adminEnqueueScripts() {
        global $iwb;
        $iwb->Utils->dequeueScripts('select2|woocommerce|page-expiration-robot');
        $iwb->Utils->dequeueStyles('select2|woocommerce|page-expiration-robot');

        wp_enqueue_media();
        wp_enqueue_script('jquery');
        wp_enqueue_script('jquery-ui-datepicker');
        wp_enqueue_script('jquery-ui-autocomplete');
        wp_enqueue_script('suggest');

        $uri='//maxcdn.bootstrapcdn.com/font-awesome/4.4.0/css/font-awesome.min.css';
        wp_enqueue_style('font-awesome', $uri);

        $this->wpEnqueueStyle('assets/css/theme.css');
        $this->wpEnqueueStyle('assets/css/admin-forms.css');
        $this->wpEnqueueStyle('assets/css/all-themes.css');
        $this->wpEnqueueStyle('assets/css/style.css');
        $this->wpEnqueueScript('assets/deps/starrr/starrr.js');
        //$this->wpEnqueueScript('assets/deps/qtip/jquery.qtip.min.js');

        $this->wpEnqueueStyle('assets/deps/select2/css/core.css');
        $this->wpEnqueueScript('assets/deps/select2/select2.min.js');

        $this->wpEnqueueScript('assets/deps/qtip/jquery.qtip.min.js');
        $this->wpEnqueueStyle('assets/deps/magnific/magnific-popup.css');
        $this->wpEnqueueScript('assets/deps/magnific/jquery.magnific-popup.js');

        $this->wpEnqueueScript('assets/deps/moment/moment.js');

        $this->wpEnqueueStyle('assets/deps/datepicker/css/bootstrap-datetimepicker.css');
        $this->wpEnqueueScript('assets/deps/datepicker/js/bootstrap-datetimepicker.js');

        $this->wpEnqueueStyle('assets/deps/colorpicker/css/bootstrap-colorpicker.min.css');
        $this->wpEnqueueScript('assets/deps/colorpicker/js/bootstrap-colorpicker.min.js');

        $this->wpEnqueueScript('assets/js/utility.js');
        $this->wpEnqueueScript('assets/js/library.js');
        $this->wpEnqueueScript('assets/js/plugin.js');
    }
    function wpEnqueueStyle($uri, $name='') {
        if($name=='') {
            $name=explode('/', $uri);
            $name=$name[count($name)-1];
            $dot=strrpos($name, '.');
            if($dot!==FALSE) {
                $name=substr($name, 0, $dot);
            }
            $name=IWB_PLUGIN_PREFIX.'_'.$name;
        }

        $v='?v='.IWB_PLUGIN_VERSION;
        wp_enqueue_style($name, IWB_PLUGIN_URI.$uri.$v);
    }
    function wpEnqueueScript($uri, $name='', $version=FALSE) {
        if($name=='') {
            $name=explode('/', $uri);
            $name=$name[count($name)-1];
            $dot=strrpos($name, '.');
            if($dot!==FALSE) {
                $name=substr($name, 0, $dot);
            }
            $name=IWB_PLUGIN_PREFIX.'_'.$name;
        }

        $v='?v='.IWB_PLUGIN_VERSION;
        $deps=array();
        wp_enqueue_script($name, IWB_PLUGIN_URI.$uri.$v, $deps, $version, FALSE);
    }

    function showTabPage() {
        global $iwb;

        $page=$iwb->Utils->qs('page');
        if($iwb->Utils->startsWith($page, IWB_PLUGIN_SLUG) && $page!=IWB_PLUGIN_SLUG) {
            $_POST['page']=IWB_PLUGIN_SLUG;
            $_GET['page']=IWB_PLUGIN_SLUG;
            $tab=substr($page, strlen(IWB_PLUGIN_SLUG)+1);
            $_POST['tab']=$tab;
            $_GET['tab']=$tab;
        }

        $id=$iwb->Utils->iqs('id', 0);
        $defaultTab=IWB_TAB_MANAGER;
        if($iwb->Options->isShowWhatsNew()) {
            $tab=IWB_TAB_WHATS_NEW;
            $defaultTab=$tab;
            $this->tabs[IWB_TAB_WHATS_NEW]=$iwb->Lang->L('What\'s New');
            //$this->tabs[TCM_TAB_MANAGER]=$tcm->Lang->L('Start using the plugin!');
        } else {
            $tab=$iwb->Utils->qs('tab', $defaultTab);
            $uri='';
            switch ($tab) {
                case IWB_TAB_DOCS:
                    $uri=IWB_TAB_DOCS_URI;
                    break;
                case IWB_TAB_PLUGINS:
                    $uri=IWB_TAB_PLUGINS_URI;
                    break;
                case IWB_TAB_SUPPORT:
                    $uri=IWB_TAB_SUPPORT_URI;
                    break;
            }
            if($uri!='') {
                $iwb->Utils->redirect($uri);
            }

            $this->tabs[IWB_TAB_EDITOR]=$iwb->Lang->L($id>0 && $tab==IWB_TAB_EDITOR ? 'Edit Welcome Bar' : 'New Welcome Bar');
            $this->tabs[IWB_TAB_MANAGER]=$iwb->Lang->L('Manager');
            $this->tabs[IWB_TAB_SETTINGS]=$iwb->Lang->L('Settings');
            $this->tabs[IWB_TAB_DOCS]=$iwb->Lang->L('FAQ & Docs');
            //$this->tabs[IWB_TAB_DOCS]=$ec->Lang->L('Docs');
            //$this->tabs[IWB_TAB_ABOUT]=$ec->Lang->L('About');
        }

        ?>
        <div class="wrap" style="margin:5px;">
            <?php
            $this->showTabs($defaultTab);
            $header='';
            switch ($tab) {
                case IWB_TAB_EDITOR:
                    $header=($id>0 ? 'Edit' : 'Add');
                    break;
                case IWB_TAB_MANAGER:
                    $header='Manager';
                    break;
                case IWB_TAB_SETTINGS:
                    $header='Settings';
                    break;
                case IWB_TAB_WHATS_NEW:
                    $header='';
                    break;
            }?>
            
            <?php
            if($iwb->Lang->H($header.'Title')) { ?>
                <h2><?php $iwb->Lang->P($header . 'Title', IWB_PLUGIN_VERSION) ?></h2>
                <?php if ($iwb->Lang->H($header . 'Subtitle')) { ?>
                    <div><?php $iwb->Lang->P($header . 'Subtitle') ?></div>
                <?php } ?>
                <br/>
                <div style="clear:both;"></div>
            <?php }

            if($tab!=IWB_TAB_WHATS_NEW) {
                iwb_ui_first_time();
            }
            
            if($tab==IWB_TAB_DOCS) {
                $iwb->Utils->redirect(IWB_TAB_DOCS);
            }
            ?>
            <div style="float:left; margin:5px;">
                <?php
                $styles=array();
                $styles[]='float:left';
                $styles[]='margin-right:20px';
                if ($tab == IWB_TAB_EDITOR) {
                    $styles[]='width:750px';
                }
                if($tab!=IWB_TAB_WHATS_NEW) {
                    $styles[]='max-width:750px';
                }
                $styles=implode('; ', $styles);
                ?>
                <div id="iwb-page" style="<?php echo $styles?>">
                    <?php switch ($tab) {
                        case IWB_TAB_WHATS_NEW:
                            iwb_ui_whats_new();
                            break;
                        case IWB_TAB_EDITOR:
                            iwb_ui_editor();
                            break;
                        case IWB_TAB_MANAGER:
                            iwb_ui_manager();
                            break;
                        case IWB_TAB_SETTINGS:
                            iwb_ui_settings();
                            break;
                    } ?>
                </div>

                <?php if($iwb->Options->isShowWhatsNew()) {
                    $iwb->Options->setShowWhatsNew(FALSE);
                } ?>
                <?php if($tab!=IWB_TAB_WHATS_NEW) { ?>
                    <div id="iwb-sidebar" style="float:left; max-width: 250px;">
                        <?php
                        $count=$this->getPluginsCount();
                        $plugins=array();
                        while(count($plugins)<2) {
                            $id=rand(1, $count);
                            if(!isset($plugins[$id])) {
                                $plugins[$id]=$id;
                            }
                        }

                        $this->drawContactUsWidget();
                        foreach($plugins as $id) {
                            $this->drawPluginWidget($id);
                        }
                        ?>
                    </div>
                <?php } ?>
            </div>
        </div>
    <?php }

    function showTabs($defaultTab) {
        global $iwb;
        $tab=$iwb->Utils->qs('tab', $defaultTab);
        if($tab==IWB_TAB_DOCS) {
            $iwb->Utils->redirect(IWB_TAB_DOCS_URI);
        }
        ?>
        <h2 class="nav-tab-wrapper" style="float:left; width:97%;">
            <?php
            foreach ($this->tabs as $k=>$v) {
                $active = ($tab==$k ? 'nav-tab-active' : '');
                $target='_self';

                $styles=array();
                $styles[]='float:left';
                $styles[]='margin-left:10px';
                if($k==IWB_TAB_DOCS) {
                    $target='_blank';
                    $styles[] ='background-color:#F2E49B';
                }
                $styles=implode(';', $styles);
                ?>
                <a target="<?php echo $target ?>"  style="<?php echo $styles?>" class="nav-tab <?php echo $active?>" href="?page=<?php echo IWB_PLUGIN_SLUG?>&tab=<?php echo $k?>"><?php echo $v?></a>
            <?php
            }
            ?>
            <style>
                .starrr {display:inline-block}
                .starrr i{font-size:16px;padding:0 1px;cursor:pointer;color:#2ea2cc;}
            </style>
            <div style="float:right; display:none;" id="rate-box">
                <span style="font-weight:700; font-size:13px; color:#555;"><?php $iwb->Lang->P('Rate us')?></span>
                <div id="iwb-rate" class="starrr" data-connected-input="iwb-rate-rank"></div>
                <input type="hidden" id="iwb-rate-rank" name="iwb-rate-rank" value="5" />
                <?php  $iwb->Utils->twitter('intellywp') ?>
            </div>

            <script>
                jQuery(function() {
                    jQuery(".starrr").starrr();
                    jQuery('#iwb-rate').on('starrr:change', function(e, value){
                        var url='https://wordpress.org/support/plugin/intelly-welcome-bar/reviews/#new-post?rate=5#postform';
                        window.open(url);
                    });
                    jQuery('#rate-box').show();
                });
            </script>
        </h2>
        <div style="clear:both;"></div>
    <?php }
    function getPluginsCount() {
        global $iwb;
        $index=1;
        while($iwb->Lang->H('Plugin'.$index.'.Name')) {
            $index++;
        }
        return $index-1;
    }
    function drawPluginWidget($id) {
        global $iwb;
        ?>
        <div class="iwb-plugin-widget">
            <b><?php $iwb->Lang->P('Plugin'.$id.'.Name') ?></b>
            <br>
            <i><?php $iwb->Lang->P('Plugin'.$id.'.Subtitle') ?></i>
            <br>
            <ul style="list-style: circle;">
                <?php
                $index=1;
                while($iwb->Lang->H('Plugin'.$id.'.Feature'.$index)) { ?>
                    <li><?php $iwb->Lang->P('Plugin'.$id.'.Feature'.$index) ?></li>
                    <?php $index++;
                } ?>
            </ul>
            <a style="float:right;" class="button-primary" href="<?php $iwb->Lang->P('Plugin'.$id.'.Permalink') ?>" target="_blank">
                <?php $iwb->Lang->P('PluginCTA')?>
            </a>
            <div style="clear:both"></div>
        </div>
        <br>
    <?php }
    function drawContactUsWidget() {
        global $iwb;
        ?>
        <b><?php $iwb->Lang->P('Sidebar.Title') ?></b>
        <ul style="list-style: circle;">
            <?php
            $index=1;
            while($iwb->Lang->H('Sidebar'.$index.'.Name')) { ?>
                <li>
                    <a href="<?php $iwb->Lang->P('Sidebar'.$index.'.Url')?>" target="_blank">
                        <?php $iwb->Lang->P('Sidebar'.$index.'.Name')?>
                    </a>
                </li>
                <?php $index++;
            } ?>
        </ul>
    <?php }
}
