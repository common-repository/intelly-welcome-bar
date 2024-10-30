<?php
if (!defined('ABSPATH')) exit;

//@iwp
class IWB_WelcomeBar {
    //@type=int @primary
    //@ui-type=number
    var $id;
    //@type=text
    //@ui-type=text @ui-align=left
    var $name;

    //@type=int
    //@ui-type=toggle
    var $active;

    //@type=text
    //@ui-type=colordown
    var $backgroundColor;
    //@type=text
    //@ui-type=colordown
    var $textColor;

    //@type=int
    //@ui-type=toggle
    var $showCloseButton;
    //@type=int
    //@ui-type=toggle
    var $showCtaButton;
    //@type=text
    //@ui-type=text @ui-visible=showCtaButton:1
    var $ctaText;
    //@type=int
    //@ui-type=number @ui-min=0 @ui-visible=showCtaButton:1
    var $ctaFontSize;
    //@type=text
    //@ui-type=text @ui-visible=showCtaButton:1
    var $ctaUri;
    //@type=text
    //@ui-type=dropdown @ui-visible=showCtaButton:1
    var $ctaTarget;

    //@type=text
    //@ui-type=text
    var $key;
    //@type=text
    //@ui-type=text
    var $titleText;
    //@type=text
    //@ui-type=text
    var $subtitleText;
    //@type=text
    //@ui-type=dropdown
    var $textAlign;

    //@type=int
    //@ui-type=number
    var $paddingTop;
    //@type=int
    //@ui-type=number
    var $paddingLeft;
    //@type=int
    //@ui-type=number
    var $paddingBottom;

    //@type=int
    //@ui-type=number @ui-min=0
    var $titleFontSize;
    //@type=int
    //@ui-type=number @ui-min=0
    var $subtitleFontSize;

    //@type=int
    //@ui-type=dropdown @ui-min=0
    var $height;

    public function __costruct() {

    }
}