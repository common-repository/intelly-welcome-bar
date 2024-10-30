<?php
if (!defined('ABSPATH')) exit;

class IWB_WelcomeBarUi {
    var $barContainer='';
    var $barText='';
    var $ctaButton='';
    var $closeButton='';
    var $closeButtonMobile='';
    var $credits='';
    var $barBackground='';

    public function __construct() {
    }
    private function generateClasses($options=array()) {
        global $iwb;
        if($this->barContainer!='') {
            return;
        }

        $defaults=array('preview'=>FALSE);
        $options=$iwb->Utils->parseArgs($options, $defaults);

        $this->barContainer='iwb-barContainer';
        $this->barText='iwb-barText';
        $this->ctaButton='iwb-barButton';
        $this->closeButton='iwb-closeButton';
        $this->closeButtonMobile='iwb-closeButtonMobile';
        $this->credits='iwb-credits';
        $this->barBackground='iwb-barBackground';

        $this->barContainer=$this->generateMd5($this->barContainer);
        $this->barText=$this->generateMd5($this->barText);
        $this->ctaButton=$this->generateMd5($this->ctaButton);
        $this->closeButton=$this->generateMd5($this->closeButton);
        $this->closeButtonMobile=$this->generateMd5($this->closeButtonMobile);
        $this->credits=$this->generateMd5($this->credits);
        $this->barBackground=$this->generateMd5($this->barBackground);
    }
    private function generateMd5($name) {
        $prefix='a';
        $name.='-t'.time();
        $result=$prefix.md5($name);
        return $result;
    }
    public function html(IWB_WelcomeBar $welcome, $options=array()) {
        global $iwb;
        $defaults=array('preview'=>FALSE);
        $options=$iwb->Utils->parseArgs($options, $defaults);

        $settings=$iwb->Options->getPluginSettings();
        $this->generateClasses($options);
        ?>
        <div id="<?php echo $this->barContainer ?>">
            <div class="_table">
            <div id="<?php echo $this->barText ?>">
                <?php if($welcome->titleText!='') { ?>
                    <h4><?php echo $welcome->titleText?></h4>
                <?php } ?>
                <?php if($welcome->subtitleText!='') { ?>
                    <h2><?php echo $welcome->subtitleText?></h2>
                <?php } ?>
            </div>
            	<div class="_clear"></div>
            </div>
            <?php if($options['preview'] || ($welcome->showCtaButton && $welcome->ctaText!='')) { ?>
                <a id="<?php echo $this->ctaButton ?>"  href="<?php echo $welcome->ctaUri?>" target="<?php echo $welcome->ctaTarget?>">
                    <?php echo $welcome->ctaText?>
                </a>
            <?php } ?>
            <?php if($welcome->showCloseButton || $options['preview']) { ?>
                <div id="<?php echo $this->closeButton ?>"></div>
                <div id="<?php echo $this->closeButtonMobile ?>"></div>
            <?php } ?>
            <?php if(!$options['preview'] && $settings->showPoweredBy) { ?>
                <div id="<?php echo $this->credits ?>">
                    <p>
                        <a href="https://intellywp.com/welcome-bar/?utm_campaign=poweredby" target="_blank">
                            Powered By<br/><strong>IntellyWP</strong>
                        </a>
                    </p>
                </div>
            <?php } ?>
            <div id="<?php echo $this->barBackground ?>"></div>
        </div>
    <?php }
    public function style(IWB_WelcomeBar $welcome, $options=array()) {
        global $iwb;
        $defaults=array('preview'=>FALSE);
        $options=$iwb->Utils->parseArgs($options, $defaults);
        $this->generateClasses($options);
        ?>
        <style>
	        #<?php echo $this->barContainer ?> ._table{
		        width: 100%;
		        height: 100%;
		        display: table;
	        }
			#<?php echo $this->barContainer ?> ._clear{
				clear: both;
			}
			body{
				line-height: inherit!important;
			}
            #<?php echo $this->barContainer ?> {
                height: <?php echo $welcome->height ?>px;
                width: 100%;
                z-index: 9999;
/*                 display: table; */
				display: block;
                background: none left center no-repeat <?php echo $welcome->backgroundColor?>;
                top: 0px;
                right: 0px;
                left: 0px;
                position: fixed;
                overflow: hidden;
                -webkit-box-sizing: border-box;
                box-sizing: border-box;
            }
            #<?php echo $this->barText ?> {
                width: 100%;
                position: relative;
                /*
                padding: 30px 20px 0;
                */
                margin: 0 auto;
                display: table-cell;
                vertical-align: middle;
                height: 100%;
                text-align: <?php echo $welcome->textAlign?>;
                padding-right: 30%;
                padding-left: 30%;
            }
            #<?php echo $this->barText ?> h4 {
                margin: 0;
                color: <?php echo $welcome->textColor?>;
                font-weight: 900;
                letter-spacing: 1px;
                font-size: <?php echo $welcome->titleFontSize?>px;
                text-align: <?php echo $welcome->textAlign?>;
                /*text-shadow: 0 1px 1px rgba(1, 1, 1, 0.33);*/
            }
            #<?php echo $this->barText ?> h2 {
                margin-bottom: 0;
                margin-top: 0;
                font-weight: normal;
                font-style: normal;
                color: <?php echo $welcome->textColor?>;
                padding-top: 0px;
                font-size: <?php echo $welcome->subtitleFontSize?>px;
                text-align: <?php echo $welcome->textAlign?>;
                /*text-shadow: 0 1px 1px rgba(1, 1, 1, 0.33);*/
                /*line-height: 1.2em;*/
            }
            a#<?php echo $this->ctaButton ?> {
                color: <?php echo $welcome->backgroundColor?>;
                background-color: <?php echo $welcome->textColor?>;
                font-size: <?php echo $welcome->ctaFontSize?>px;
                padding: 10px;
		height: 40px;
                font-size: 16px;
                position: absolute;
                vertical-align: middle;
                top: 50%;
                right: 15%;
                margin-top: -20px;
                font-weight: bold;
                text-align: center;
                border-radius: 4px;
                -webkit-border-radius: 4px;
                -moz-border-radius: 4px;
                -ms-border-radius: 4px;
                cursor: pointer;
            }
            a#<?php echo $this->ctaButton ?> {
                color: <?php echo $welcome->backgroundColor?>;
                text-decoration: none;
                border: 0px;
            }
            a#<?php echo $this->ctaButton ?>:hover {
                text-decoration: none;
            }
            #<?php echo $this->closeButton ?> {
                width: 19px;
                height: 19px;
                background: url(<?php echo IWB_PLUGIN_IMAGES_URI?>close_2.png) no-repeat;
                background-size: 19px;
                position: absolute;
                top: 10px;
                right: 10px;
                cursor: pointer;
                opacity: 0.4;
                z-index: 10000;
            }
            #<?php echo $this->closeButton ?>:hover {
                opacity: 1;
            }
            #<?php echo $this->closeButtonMobile ?> {
                /* This is shown only when we go mobile */
                display: none;
                width: 19px;
                height: 19px;
                background: url(<?php echo IWB_PLUGIN_IMAGES_URI?>close_2.png) no-repeat;
                background-size: 19px;
                position: absolute;
                top: 10px;
                right: 10px;
                cursor: pointer;
                opacity: 0.4;
                z-index: 10000;
            }
            #<?php echo $this->credits ?> {
                /*font-family: sans-serif;*/
                width: 84px;
                height: 37px;
                position: absolute;
                bottom: 10px;
                right: 10px;
                opacity: 0.6;
                cursor: pointer;
                color: <?php echo $welcome->textColor?>;
                text-align: center;
                font-size: 10px;
                /*line-height: 1.4em;*/
            }
            #<?php echo $this->credits ?> p strong {
                /*
                font-size: 1.1em;
                letter-spacing: 0.4em;
                */
            }
            #<?php echo $this->credits ?>:hover {
                opacity: 1;
            }
            #<?php echo $this->credits ?> a:hover
            , #<?php echo $this->credits ?> a:visited
            , #<?php echo $this->credits ?> a:active
            , #<?php echo $this->credits ?> a
            , #<?php echo $this->credits ?> strong {
                color: <?php echo $welcome->textColor?>;
                text-decoration: none;
                border: 0px;
            }

            @media only screen and (max-width: 767px) {
	    	#<?php echo $this->barContainer ?> ._table{
			height: auto;
	        }
                #<?php echo $this->barContainer ?> {
                    background-image: none;
                }
                #<?php echo $this->ctaButton ?> {
                    right: 6%;
                }
                #<?php echo $this->credits ?>{
                    display: none;
                }
                #<?php echo $this->barText ?> {
                    width: 100%;
                    padding-right: 5%;
                    padding-left: 5%;
                }
                #<?php echo $this->closeButton ?> {
                    display: none;
                }
                #<?php echo $this->closeButtonMobile ?> {
                    display: block !important;
                }
                #<?php echo $this->barText ?> {
                    text-align: left;
                    padding-right: 35%;

                    padding-top: 5%;
                    padding-bottom: 5%;
                }
            }
        </style>
    <?php }
    public function script(IWB_WelcomeBar $welcome, $options=array()) {
        global $iwb;
        $defaults=array('preview'=>FALSE);
        $options=$iwb->Utils->parseArgs($options, $defaults);
        $this->generateClasses($options);
        ?>
        <script>
            <?php if($options['preview']) { ?>
                jQuery('html').css('padding-top', 0);
            <?php } ?>

            var $fixedHeaders=IWB.getFixedHeaders();
            if($fixedHeaders.length>0) {
                jQuery.each($fixedHeaders, function(i,$this) {
                    if(IWB.attr($this, 'id', '')!='<?php echo $this->barContainer ?>') {
                        <?php if($options['preview']) { ?>
                            $this.remove();
                        <?php } else { ?>
                            var marginTop=parseInt($this.css('margin-top'));
                            $this.css('margin-top', marginTop+<?php echo $welcome->height?>);
                        <?php }?>
                    }
                });
            }

            var IWB_$bar=jQuery('#<?php echo $this->barContainer ?>');
            function IWB_closeBar() {
                var barHeight=IWB_$bar.height();

                IWB_$bar.animate({
                    marginTop: '-'+barHeight
                }, 400, function() {
                    // Animation complete.
                });

                jQuery('body').animate({
                    marginTop: 0
                }, 400, function() {
                    // Animation complete.
                });
            };

            jQuery(function() {
                <?php if(!$options['preview']) { ?>
                    jQuery('#<?php echo $this->closeButton ?>').click(function() {
                        IWB_closeBar();
                    });
                    jQuery('#<?php echo $this->closeButtonMobile ?>').click(function() {
                        IWB_closeBar();
                    });
                <?php } ?>

                var barHeight=IWB_$bar.height();
                jQuery('body').css('margin-top', barHeight+'px');

                <?php if($welcome->showCtaButton && $welcome->ctaText!='') { ?>
                    jQuery('#<?php echo $this->closeButton ?>').hide();
                    IWB_$bar.mouseover(function() {
                        jQuery('#<?php echo $this->closeButton ?>').show()
                    });
                    IWB_$bar.mouseout(function() {
                        jQuery('#<?php echo $this->closeButton ?>').hide()
                    });
                <?php } ?>
            });
        </script>
    <?php }
}