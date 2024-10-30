<?php
function iwb_notice_pro_features() {
    global $iwb;

    ?>
    <br/>
    <div class="message updated below-h2 iwp" style="width: 100%">
        <div style="height:10px;"></div>
        <?php
        $i=1;
        while($iwb->Lang->H('Notice.ProHeader'.$i)) {
            $iwb->Lang->P('Notice.ProHeader'.$i);
            echo '<br/>';
            ++$i;
        }
        $i=1;
        ?>
        <br/>
        <?php

        /*$options = array('public' => TRUE, '_builtin' => FALSE);
        $q=get_post_types($options, 'names');
        if(is_array($q) && count($q)>0) {
            sort($q);
            $q=implode(', ', $q);
            $q='(<b>'.$q.'</b>)';
        } else {
            $q='';
        }*/
        $q='';
        while($iwb->Lang->H('Notice.ProFeature'.$i)) { ?>
            <div style="clear:both; margin-top: 2px;"></div>
            <div style="float:left; vertical-align:middle; height:24px; margin-right:5px; margin-top:-5px;">
                <img src="<?php echo IWB_PLUGIN_IMAGES_URI?>tick.png" />
            </div>
            <div style="float:left; vertical-align:middle; height:24px;">
                <?php $iwb->Lang->P('Notice.ProFeature'.$i, $q)?>
            </div>
            <?php ++$i;
        }
        ?>
        <div style="clear:both;"></div>
        <div style="height:10px;"></div>
        <div style="float:right;">
            <?php
            $url=IWB_TAB_PREMIUM_URI.'?utm_source=free-users&utm_medium=wp-cta&utm_campaign=wp-plugin';
            ?>
            <a href="<?php echo $url?>" target="_blank">
                <b><?php $iwb->Lang->P('Notice.ProCTA')?></b>
            </a>
        </div>
        <div style="height:10px; clear:both;"></div>
    </div>
    <br/>
<?php }

function iwb_ui_editor() {
    global $iwb;

    ?>
    <h2><?php $iwb->Lang->P('Title.Editor')?></h2>
    <?php

    $id=$iwb->Utils->iqs('id');
    if($id==0 && $iwb->Manager->isLimitReached(FALSE)) {
        $iwb->Ui->redirectManager();
    }

    /* @var $instance IWB_WelcomeBar */
    $instance=$iwb->Manager->get($id, TRUE);
    $iwb->Form->prefix='Editor';
    if($iwb->Check->is('_action', 'Save')) {
        $instance=$iwb->Dao->Utils->qs('WelcomeBar');

        $fields='active|name|key|textAlign|backgroundColor|textColor|height|showCloseButton|titleFontSize|subtitleFontSize|showCtaButton|ctaText|ctaUri|ctaTarget';
        $all=TRUE;
        $iwb->Ui->validateDomain($instance, $fields, $all);
        if(!$iwb->Options->hasErrorMessages()) {
            $iwb->Manager->store($instance);
            if(!$iwb->Options->hasErrorMessages()) {
                $iwb->Ui->redirectManager($instance->id);
            }
        }
    }
    $iwb->Options->writeMessages();

    $iwb->Form->formStarts();
    {
        $iwb->Form->hidden('id', $instance->id);
        $title=($instance->id>0 ? 'Edit' : 'Add');
        $iwb->Form->openPanel($title);
        {
            $fields='active|name|key|titleText|subtitleText|textAlign|expirationDate';
            $iwb->Form->inputsForm($fields, $instance);
        }
        $iwb->Form->closePanel();

        $iwb->Form->openPanel('Aspect');
        {
            $fields='backgroundColor|textColor|height|showCloseButton|titleFontSize|subtitleFontSize'; //|paddingTop|paddingLeft|paddingBottom
            $iwb->Form->inputsForm($fields, $instance);
        }
        $iwb->Form->closePanel();

        $iwb->Form->openPanel('Button');
        {
            $fields='showCtaButton|?ctaText|?ctaUri|?ctaTarget';
            $iwb->Form->inputsForm($fields, $instance);
            $buttons=array();
            $button=array(
                'submit'=>TRUE
            );
            $buttons['Save']=$button;
            $options=array('buttons'=>$buttons);

            iwb_notice_pro_features();
        }
        $iwb->Form->closePanel($options);
    }
    $iwb->Form->formEnds();

    $options=array('preview'=>TRUE);
    $iwb->Ui->WelcomeBar->style($instance, $options);
    $iwb->Ui->WelcomeBar->html($instance, $options);
    $iwb->Ui->WelcomeBar->script($instance, $options);
    iwb_editor_scripts();
}
function iwb_editor_scripts() {
    global $iwb;
    ?>
    <script>
        function IWB_editorPreview() {
            var $e;
            var showCloseButton=(IWB.check('showCloseButton')==1);
            var showCtaButton=(IWB.check('showCtaButton')==1);

            var height=IWB.val('height');

            $e=jQuery('#<?php echo $iwb->Ui->WelcomeBar->barContainer?>');
            $e.css('height', height+'px');
            jQuery('body').css('margin-top', height+'px');
            $e.css('background', 'none left center no-repeat '+IWB.val('backgroundColor'));

            $e=jQuery('#<?php echo $iwb->Ui->WelcomeBar->barText?>');
            $e.css('text-align', IWB.val('textAlign'));

            $e=jQuery('#<?php echo $iwb->Ui->WelcomeBar->barText ?> h4');
            $e.css('color', IWB.val('textColor'));
            $e.css('font-size', IWB.val('titleFontSize')+'px');
            $e.css('text-align', IWB.val('textAlign'));
            $e.html(IWB.val('titleText'));

            $e=jQuery('#<?php echo $iwb->Ui->WelcomeBar->barText ?> h2');
            $e.css('color', IWB.val('textColor'));
            $e.css('font-size', IWB.val('subtitleFontSize')+'px');
            $e.css('text-align', IWB.val('textAlign'));
            $e.html(IWB.val('subtitleText'));

            $e=jQuery('#<?php echo $iwb->Ui->WelcomeBar->ctaButton?>');
            $e.css('visibility', showCtaButton ? 'visible' : 'hidden');
            $e.css('color', IWB.val('backgroundColor'));
            $e.css('background-color', IWB.val('textColor'));
            //$e.css('font-size', IWB.val('ctaFontSize')+'px');
            $e=jQuery('#<?php echo $iwb->Ui->WelcomeBar->ctaButton?>');
            $e.css('color', IWB.val('backgroundColor'));
            $e.text(IWB.val('ctaText'));
            $e.attr('href', IWB.val('ctaUri'));
            $e.attr('target', IWB.val('ctaTarget'));

            $e=jQuery('#<?php echo $iwb->Ui->WelcomeBar->closeButton?>');
            $e.css('visibility', showCloseButton ? 'visible' : 'hidden');
            $e=jQuery('#<?php echo $iwb->Ui->WelcomeBar->closeButtonMobile?>');
            $e.css('visibility', showCloseButton ? 'visible' : 'hidden');

            $e=jQuery('#<?php echo $iwb->Ui->WelcomeBar->credits?>');
            $e.css('color', IWB.val('textColor'));
            $e=jQuery('#<?php echo $iwb->Ui->WelcomeBar->credits?> a');
            $e.css('color', IWB.val('textColor'));
            $e=jQuery('#<?php echo $iwb->Ui->WelcomeBar->credits?> strong');
            $e.css('color', IWB.val('textColor'));
        }

        jQuery('input,select,textarea').keyup(function() {
            IWB_editorPreview();
        });
        jQuery('input,select,textarea').click(function() {
            IWB_editorPreview();
        });
        jQuery('input,select,textarea').change(function() {
            IWB_editorPreview();
        });
        IWB_editorPreview();
    </script>
<?php }
