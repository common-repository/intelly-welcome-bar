<?php
function iwb_ui_manager_clone($ids) {
    global $iwb;
    $success=$iwb->Manager->copy($ids);
    $iwb->Options->pushMessage($success, 'CloneWelcomeBar');
    $iwb->Ui->redirectManager();
}
function iwb_ui_manager_delete($ids) {
    global $iwb;
    $success=$iwb->Manager->delete($ids);
    $iwb->Options->pushMessage($success, 'DeleteWelcomeBar');
    $iwb->Ui->redirectManager();
}
function iwb_ui_check_incompatible_plugins() {
    global $iwb;
    if(class_exists('PageExpirationRobot')) {
        $iwb->Options->pushWarningMessage('PleaseDeactivatePageExpirationRobot');
    }
}
function iwb_ui_manager() {
    global $iwb;
    iwb_ui_check_incompatible_plugins();
    iwb_ui_track(FALSE);

    ?>
    <h2 class="mb10"><?php $iwb->Lang->P('Title.Manager', IWB_PLUGIN_NAME, IWB_PLUGIN_VERSION)?></h2>
    <?php

    $iwb->Form->prefix='Manager';
    $action=$iwb->Utils->qs('_action', '');
    $function=FALSE;
    if($iwb->Check->nonce() && $action!=='') {
        $action=strtolower($action);
        $ids=$iwb->Utils->toArray($iwb->Utils->qs('ids', array()));
        $onlyOne=FALSE;
        $allowEmpty=FALSE;
        switch ($action) {
            case 'clone':
                $onlyOne=TRUE;
                break;
        }
        if(!$allowEmpty && ($iwb->Utils->isEmpty($ids))) {
            $iwb->Options->pushWarningMessage('SelectWelcomeBarToAction');
        } elseif(!$iwb->Utils->isEmpty($ids) && count($ids)>1 && $onlyOne) {
            $iwb->Options->pushWarningMessage('SelectOnlyOneWelcomeBarToAction');
        } else {
            $function='iwb_ui_manager_'.$action;
            $iwb->Utils->functionCall($function, $ids);
            $function=TRUE;
        }
    }

    $id=$iwb->Utils->iqs('id', 0);
    if($id>0) {
        $instance=$iwb->Manager->get($id);
        if($instance!==FALSE && !$function) {
            $iwb->Options->pushSuccessMessage('WelcomeBarUpdated');
        }
    }

    $iwb->Manager->isLimitReached(TRUE);
    $iwb->Options->writeMessages();

    $items=$iwb->Manager->query();
    if (count($items)>0) {
        $options=array('class'=>'admin-form');
        $iwb->Form->formStarts($options);
        $iwb->Form->hidden('_action', '');
        ?>
        <table style="width:100%;">
            <tr>
                <td>
                    <?php
                        $options=array(
                            'theme'=>'primary'
                            , 'uri'=>IWB_TAB_EDITOR_URI
                            , 'style'=>'margin-right:5px;'
                            , 'rightSpace'=>FALSE
                            , 'leftSpace'=>FALSE
                        );
                        $iwb->Form->button('Add', $options);
                    ?>
                </td>
                <td>
                    <?php
                        $options=array(
                            'theme'=>'success'
                            , 'style'=>'margin-right:5px;'
                            , 'rightSpace'=>FALSE
                            , 'leftSpace'=>FALSE
                        );
                        $iwb->Form->submit('Clone', $options);
                    ?>
                </td>
                <td>
                    <?php
                        $options=array(
                            'theme'=>'danger'
                            , 'prompt'=>TRUE
                            , 'style'=>'margin-right:5px;'
                            , 'rightSpace'=>FALSE
                            , 'leftSpace'=>FALSE
                        );
                        $iwb->Form->submit('Delete', $options);
                    ?>
                </td>
                <td style="width: 100%">
                    <?php
                        $options=array(
                            'noLayout'=>TRUE
                            , 'placeholder'=>$iwb->Lang->L('PasteHereYourURL')
                            , 'style'=>'float:left; width:100%'
                        );
                        $iwb->Form->text('search', '', $options);
                    ?>
                </td>
            </tr>
        </table>
        <br/>

        <?php
            $fields='@id|#name|active|key|height|uri';
            $args=array(
                'height'=>array(
                    'function'=>'iwb_column_height'
                )
                , 'uri'=>array(
                    'function'=>'iwb_column_uri'
                    , 'align'=>'left'
                )
            );
            $iwb->Form->inputTable($fields, $items, $args);
        ?>
    <?php
        $iwb->Form->formEnds();
        iwb_manager_scripts();
		iwb_notice_pro_features();
    } else { ?>
        <h2><?php $iwb->Lang->P('EmptyWelcomeBarList', IWB_TAB_EDITOR_URI)?></h2>
    <?php }
}

function iwb_manager_scripts() {
    global $iwb;
    $settings=$iwb->Options->getPluginSettings();
    ?>
    <script>
        function IWB_managerUriChange() {
            var $search=jQuery('#search');
            var uri=$search.val();
            uri=uri.trim();
            if(uri=='') {
                uri='<?php echo IWB_BLOG_URL?>';
            }
            if(uri.indexOf('?')==-1) {
                uri+='?';
            } else {
                uri+='&';
            }
            uri+='<?php echo $settings->httpReferer?>=';
            jQuery('.iwb-table-uri').each(function() {
                var $this=jQuery(this);
                var key=IWB.attr($this, 'data-id');
                $this.val(uri+key);
            })
        }
        jQuery('#search').change(function() {
            IWB_managerUriChange();
        });
        jQuery('#search').keyup(function() {
            IWB_managerUriChange();
        });
        jQuery('.iwb-table-uri').on('click', function() {
            var $self=jQuery(this);
            $self.select();
        });
        jQuery('#search').trigger('change');
    </script>
<?php }
function iwb_column_height($v) {
    /* @var $v IWB_WelcomeBar */
    echo intval($v->height).'px';
}
function iwb_column_uri($v) {
    /* @var $v IWB_WelcomeBar */
    global $iwb;
    $args=array(
        'noLayout'=>TRUE
        , 'style'=>'width:100%'
        , 'class'=>'gui-input iwb-table-uri iwb-select-onfocus'
        , 'readonly'=>TRUE
        , 'data-id'=>$v->key
    );
    $iwb->Form->text('', '', $args);
}