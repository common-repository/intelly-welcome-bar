<?php
function iwb_ui_whats_new() {
    global $iwb, $ecf;
    $iwb->Options->setShowWhatsNew(FALSE);
    ?>
    <style>
        .iwb-headline {
            font-size:40px;
            font-weight:bold;
            text-align:center;
        }
        .iwb-sub-headline {
            font-size:35px;
            font-weight:normal;
            text-align:center;
        }
    </style>

    <p class="iwb-headline">Getting started with CA Enhancer</p>
    <p class="iwb-sub-headline">Watch this video before begin!</p>
    <div style="text-align: center">
        <iframe width="854" height="480" src="//www.youtube.com/embed/c3yfd5oiVGk?autoplay=1"></iframe>
        <br>
        <br>
        <?php
        $ecf->prefix='License';
        $args=array(
            'uri'=>IWB_TAB_SETTINGS_URI
            , 'theme'=>'primary'
            , 'class'=>'btn-lg'
        );
        $ecf->button('fbConnect', $args);
        ?>
    </div>
<?php }