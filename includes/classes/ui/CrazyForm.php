<?php
if (!defined('ABSPATH')) exit;

class IWB_CrazyForm {
    var $prefix='';
    var $namePrefix='';
    var $readonly=FALSE;

    var $labels=TRUE;
    var $newline;
    var $helps=FALSE;
    var $textCenter=FALSE;
    var $tooltips=FALSE;
    var $blockOpened=FALSE;

    private $search=FALSE;
    private $noncePresent=FALSE;
    private $hiddenActionCreated=FALSE;
    private $buttonPresent=FALSE;
    var $icon=FALSE;

    public function __construct() {
    }

    public function newline() { ?>
        <div class="iwb-form-newline"></div>
    <?php }

    public function formStarts($options=array()) {
        global $iwb;
        $defaults=array(
            'method'=>'POST'
            , 'action'=>''
            , 'class'=>''
            , 'openBlock'=>FALSE
        );
        $options=$iwb->Utils->parseArgs($options, $defaults);
        ?>
        <form method="<?php echo $options['method']?>" action="<?php echo $options['action']?>" class="<?php echo $options['class']?>">
    <?php
        if($options['openBlock']) {
            $this->openBlock();
        }
    }
    public function formEnds($options=array()) {
        global $iwb;
        $defaults=array(
            'noncePresent'=>$this->noncePresent
            , 'buttonPresent'=>$this->buttonPresent
        );
        $options=$iwb->Utils->parseArgs($options, $defaults);

        if(!$options['noncePresent']) {
            $this->nonce();
        }
        if(!$options['buttonPresent']) {
            $this->submit();
        }
        $this->closeBlock();
        ?>
        </form>
        <?php /*<div style="clear:both;"></div>*/?>
        <?php
        $this->noncePresent=FALSE;
    }
    public function divStarts($args=array()) {
        global $iwb;

        if(is_bool($args)) {
            $args=array('style'=>'display:'.($args ? 'block': 'none'));
        }

        $defaults=array();
        $other=$iwb->Utils->getTextArgs($args, $defaults);
        ?>
        <div <?php echo $other?>>
    <?php }
    public function divEnds($clear=FALSE) { ?>
        </div>
        <?php if($clear) { ?>
            <div style="clear:both;"></div>
        <?php } ?>
    <?php }

    public function i($message, $v1=NULL, $v2=NULL, $v3=NULL, $v4=NULL, $v5=NULL) {
        global $iwb; ?>
        <i><?php $iwb->Lang->P($message, $v1, $v2, $v3, $v4, $v5) ?></i>
    <?php }
    public function p($message, $v1=NULL, $v2=NULL, $v3=NULL, $v4=NULL, $v5=NULL) {
        global $iwb;
        ?>
        <p style="font-weight:bold;">
            <?php
            $iwb->Lang->P($message, $v1, $v2, $v3, $v4, $v5);
            if($iwb->Lang->H($message.'Subtitle')) { ?>
                <br/>
                <span style="font-weight:normal;">
                    <i><?php $iwb->Lang->P($message.'Subtitle', $v1, $v2, $v3, $v4, $v5)?></i>
                </span>
            <?php } ?>
        </p>
    <?php }
    public function br() { ?>
        <br/>
    <?php }
    public function clearBoth() { ?>
        <div style="clear:both;"></div>
    <?php }
    private function getTooltipAttributes($tooltip, $options=array(), $echo=TRUE) {
        global $iwb;
        if($tooltip===FALSE || $tooltip=='') {
            return;
        }

        $data=array(
            'data-toggle'=>'tooltip'
            , 'data-placement'=>'top'
            , 'title'=>$iwb->Lang->L($tooltip)
        );
        $dump='';
        foreach($data as $k=>$v) {
            $dump.=' '.$k.'="'.str_replace('"', '', $v).'"';
        }
        if($echo) {
            echo $dump;
        } else {
            return $dump;
        }
    }
    private function openInput($name, $options=array()) {
        global $iwb;

        $defaults=array(
            'name'=>$name
            , 'class'=>($this->icon ? 'field prepend-icon' : 'field prepend-noicon')
            , 'label'=>TRUE
            , 'textLabel'=>''
            , 'md9'=>TRUE
            , 'style'=>''
            , 'labelPrefix'=>''
            , 'col-md'=>'col-md-3'
            , 'tooltipPlacement'=>'top'
            , 'row-hidden'=>FALSE
        );
        $options=$iwb->Utils->parseArgs($options, $defaults);
        $k=$this->prefix;
        if($k!='') {
            $k.='.';
        }
        $name=$options['name'];
        $name=str_replace('[]', '', $name);
        $class=$options['class'];
        $k.=$name;

        $label=$k;
        if(is_string($options['label'])) {
            $label=$options['label'];
        }

        $tooltip=(isset($options['tooltip']) ? $options['tooltip'] : '');
        if(!isset($options['tooltip']) && $this->tooltips) {
            $tooltip=$label.'.Tooltip';
        }
        //$mb=($this->search ? 'mb15' : 'row mb10');
        $mb=($this->search ? 'mb15' : 'row mb0');
        if($this->search) { ?>
            <h5><small><?php $iwb->Lang->P($label) ?></small></h5>
        <?php }
        $style='';
        if($options['row-hidden']) {
            $style.='; display:none;';
        }
        ?>
        <div class="section <?php echo $mb?>" id="<?php $this->getName($name) ?>-row" style="<?php echo $style?>">
            <?php if(!$this->search) { ?>
                <label for="<?php $this->getName($name) ?>" class="field-label <?php echo $options['col-md']?> text-left" style="<?php echo $options['style'] ?>" <?php $this->getTooltipAttributes($tooltip, $options) ?>>
                    <?php
                    $l='';
                    if(isset($options['textLabel']) && $options['textLabel']!='') {
                        $l=$options['textLabel'];
                    } else {
                    if(isset($options['labelPrefix']) && $options['labelPrefix']!='') {
                        $l=$iwb->Lang->L($options['labelPrefix']);
                    }
                    $l.=' '.$iwb->Lang->L($label);
                    }
                    $l=trim($l);
                    echo $l;
                    ?>
                </label>
                <?php if($options['md9']) { ?>
                    <div class="col-md-9">
                <?php } ?>
            <?php }
            if(is_bool($options['label']) && $options['label']) { ?>
                <label for="<?php $this->getName($name)?>" class="<?php echo $class?>">
            <?php }
    }
    private function closeInput($name, $options=array()) {
        global $iwb;

        $defaults=array(
            'name'=>$name
            , 'class'=>'field-icon'
            , 'label'=>TRUE
            , 'md9'=>TRUE
        );
        $options=$iwb->Utils->parseArgs($options, $defaults);
        $name=$options['name'];
        $icon='';
        if($this->icon) {
            if(!isset($options['icon']) ||
                ($options['icon']!==FALSE) && $options['icon']!=='') {
                $icon=$this->getIcon($name);
            }
        }
        if($icon!='') {
            if ($options['class'] == $defaults['class']) { ?>
                <label for="<?php $this->getName($name) ?>" class="field-icon">
                    <i class="fa fa-<?php echo $icon ?>"></i>
                </label>
            <?php } else { ?>
                <i class="<?php echo $options['class'] ?>"></i>
            <?php }
        }
        if($options['label']) { ?>
            </label>
        <?php }
        if(!$this->search) {
            if(isset($options['afterLabel']) && $options['afterLabel']!='') {
                echo $options['afterLabel'];
            }
            if($options['md9']) { ?>
            </div>
        <?php }
        } ?>
    </div>
    <?php }

    public function getIcon($name) {
        global $iwb;
        $icons=array(
            'user'=>'name|surname|username|user'
            , 'barcode'=>'taxCode|key'
            , 'envelope-o'=>'email|send'
            , 'at'=>'email'
            , 'phone'=>'phone'
            , 'lock'=>'password'
            , 'unlock'=>'confirmPassword|disconnect'
            , 'mobile'=>'mobile'
            , 'fax'=>'fax'
            , 'map-marker'=>'address'
            , 'certificate'=>'star|zip'
            , 'building-o'=>'region|province|country|city|place'
            , 'euro'=>'price|currency|amount|cost|advance'
            , 'edit'=>'note|description|body|subject|comment'
            , 'globe'=>'website|site'
            , 'tag'=>'tag'
            , 'calendar'=>'date|dt1|dt2'
            , 'home'=>'home|company'
            , 'clock-o'=>'time'
            , 'arrows-v'=>'scroll'
            , 'floppy-o'=>'save'
            , 'angle-double-right'=>'next'
            , 'angle-double-left'=>'previous|back'
            , 'trash-o'=>'remove|delete|trash'
            , 'refresh'=>'sync|refresh|change'
            , 'plus-circle'=>'add|plus'
            , 'clone'=>'clone'
            , 'ban'=>'ban|cancel|abort'
            , 'facebook-square'=>'facebook|fb|fbconnect'
            , 'plug'=>'plug|authorize'
            , 'bug'=>'bug|error'
            , 'sign-in'=>'login'
            , 'thumbs-o-down'=>'suspend|stop'
            , 'thumbs-o-up'=>'activate'
            , 'slack'=>'id|day'
            , 'undo'=>'undo'
            , 'pencil'=>'edit'
            , 'check-square-o'=>'finish'
            , 'check'=>'confirm'
            , 'upload'=>'import'
        );
        $result=$iwb->Utils->match($name, $icons, 'question');
        return $result;
    }

    private function timerText($name, $suffix, $value) {
        global $iwb;
        $name.=$suffix;

        $options=array(
            'noLayout'=>TRUE
            , 'class'=>'gui-input col-xs-1 text-center'
            , 'style'=>'width:10%'
        );
        $value=intval($value);
        $this->number($name, $value, $options);
        ?>
        <label for="<?php $this->getName($name) ?>" class="field-label col-xs-1 text-center" style="width:10%">
            <?php $iwb->Lang->P(lcfirst($suffix))?>
        </label>
    <?php }
    public function timer($name, $value='', $options=array()) {
        global $iwb;
        if(!is_array($options)) {
            $options=array();
        }

        $value=$iwb->Utils->get($name, $value, $value);
        $value=$iwb->Utils->formatTimer($value);
        $values=explode(':', $value);

        if($iwb->Utils->get($options, 'noLayout', FALSE)===FALSE) {
            $args=array();
            if(isset($options['row-hidden'])) {
                $args['row-hidden']=$options['row-hidden'];
            }
            if(isset($options['textLabel'])) {
                $args['textLabel']=$options['textLabel'];
            }
            $args['label']=FALSE;
            $this->openInput($name, $args);
        }

        $this->timerText($name, 'Days', $values[0]);
        $this->timerText($name, 'Hours', $values[1]);
        $this->timerText($name, 'Minutes', $values[2]);
        $this->timerText($name, 'Seconds', $values[3]);

        $options['class']='iwb-timer';
        $this->hidden($name, $value, $options);

        if($iwb->Utils->get($options, 'noLayout', FALSE)===FALSE) {
            $args=array();
            $args['label']=FALSE;
            $this->closeInput($name, $args);
        }
    }
    public function upload($name, $value='', $options=array()) {
        global $iwb;
        $default=array(
            'multiple'=>FALSE
        );
        $options=$iwb->Utils->parseArgs($options, $default);

        $value=$iwb->Utils->get($name, $value, $value);
        if($iwb->Utils->get($options, 'noLayout', FALSE)===FALSE) {
            $args=array(
                'label'=>TRUE
            );
            if(isset($options['row-hidden'])) {
                $args['row-hidden']=$options['row-hidden'];
            }
            if(isset($options['textLabel'])) {
                $args['textLabel']=$options['textLabel'];
            }
            $this->openInput($name, $args);
        }
        $multiple=($options['multiple'] ? 'true' : 'false');
        ?>
        <span class="btn btn-primary iwb-upload-button" ui-multiple="<?php echo $multiple ?>" data-id="<?php echo $name ?>">
            <?php $iwb->Lang->P('Upload.Button')?>
        </span>
        <?php
        $args=array(
            'noLayout'=>TRUE
            , 'class'=>'gui-input text-left'
            , 'placeholder'=>$iwb->Lang->L('Upload.Placeholder')
        );
        $this->text($name, $value, $args);

        if($iwb->Utils->get($options, 'noLayout', FALSE)===FALSE) {
            $args=array();
            $args['label']=FALSE;
            $this->closeInput($name, $args);
        }
    }
    public function text($name, $value='', $options=array()) {
        global $iwb;

        $value=$iwb->Utils->get($name, $value, $value);
        $type='text';
        if(isset($options['type'])) {
            $type=$options['type'];
        }

        $defaults=array('class'=>'gui-input');
        if($this->textCenter) {
            $defaults['class'].=' text-center';
            if(isset($options['class'])) {
                $options['class'].=' text-center';
            }
        }
        $other=$iwb->Utils->getTextArgs($options, $defaults, 'type|label|noLayout|textLabel');
        $options=$iwb->Utils->parseArgs($options, $defaults);

        if($iwb->Utils->get($options, 'noLayout', FALSE)===FALSE) {
            $args=array();
            if(isset($options['row-hidden'])) {
                $args['row-hidden']=$options['row-hidden'];
            }
            if(isset($options['textLabel'])) {
                $args['textLabel']=$options['textLabel'];
            }
            $this->openInput($name, $args);
        }
        $id=$this->getName($name, $options, FALSE);
        $text=$value;
        if($id!='') {
            $text='';
        }
        ?>
        <input type="<?php echo $type?>" id="<?php echo $id ?>" name="<?php echo $id ?>" value="<?php echo $text?>" <?php echo $other?> />
        <?php if($id!='') { ?>
        <script>
                jQuery('#<?php echo $id ?>').val("<?php echo str_replace('"', '\"', $value) ?>");
        </script>
        <?php } ?>
        <?php
        if($iwb->Utils->get($options, 'noLayout', FALSE)===FALSE) {
            $this->closeInput($name, $args);
        }
    }
    private function getName($name, $options=array(), $echo=TRUE) {
        $name=$this->namePrefix.$name;
        $name=str_replace('.', '_', $name);
        if($options===FALSE) {
            $options=array();
            $echo=FALSE;
        }

        //if(!is_array($options)) {
        //    $options=array();
        //}
        //dopo se lo faccio potrebbe succedere un casino con le validazioni etc
        //inoltre poi un campo senza nome nn può essere READONLY
        //if(count($options)>0 && isset($options['readonly']) && $options['readonly']!='') {
        //    $name='';
        //}

        if($echo) {
            echo $name;
        } else {
            return $name;
        }
    }
    public function hidden($name, $value='', $options=NULL) {
        global $iwb;
        if($name=='_action') {
            $this->hiddenActionCreated=TRUE;
        }
        $value=$iwb->Utils->get($name, $value, $value);
        if(is_bool($value)) {
            $value=($value ? 1 : 0);
        }
        $defaults=array();
        $other=$iwb->Utils->getTextArgs($options, $defaults, 'type|label|noLayout|textLabel');
        ?>
        <input type="hidden" id="<?php $this->getName($name) ?>" name="<?php $this->getName($name) ?>" value="<?php echo $value ?>" <?php echo $other?> />
    <?php }

    public function nonce($action='nonce', $name='_wpnonce', $referer=true, $echo=true) {
        if($name=='') {
            $name=$action;
        }
        $this->noncePresent=TRUE;
        wp_nonce_field($action, $name, $referer, $echo);
    }

    public function textarea($name, $value='', $options=NULL) {
        global $iwb;

        $value=$iwb->Utils->get($name, $value, $value);
        //$defaults=array('rows'=>10, 'class'=>'gui-textarea');
        $defaults=array('class'=>'gui-textarea');
        $other=$iwb->Utils->getTextArgs($options, $defaults, 'noLayout|textLabel');
        $options=$iwb->Utils->parseArgs($options, $defaults);

        if($iwb->Utils->get($options, 'noLayout', FALSE)===FALSE) {
            $args=array();
            if(isset($options['row-hidden'])) {
                $args['row-hidden']=$options['row-hidden'];
            }
            if(isset($options['textLabel'])) {
                $args['textLabel']=$options['textLabel'];
            }
            $this->openInput($name, $args);
        }
        ?>
            <textarea dir="ltr" dirname="ltr" id="<?php $this->getName($name) ?>" name="<?php $this->getName($name, $options) ?>" <?php echo $other?> ></textarea>
            <script>
                jQuery('#<?php $this->getName($name) ?>').val("<?php echo str_replace('"', '\"', $value) ?>");
            </script>
        <?php
        if($iwb->Utils->get($options, 'noLayout', FALSE)===FALSE) {
            $args=array();
            $this->closeInput($name, $args);
        }
    }
    public function email($name, $value='', $options=NULL) {
        global $iwb;
        $defaults=array('type'=>'email');
        $options=$iwb->Utils->parseArgs($options, $defaults);
        $this->text($name, $value, $options);
    }
    public function password($name, $value='', $options=NULL) {
        global $iwb;
        $defaults=array('type'=>'password');
        $options=$iwb->Utils->parseArgs($options, $defaults);
        $this->text($name, $value, $options);
    }
    public function currency($name, $value='', $options=NULL) {
        global $iwb;
        //number does not support comma
        //$defaults=array('type'=>'number');
        //$options=$ec->Utils->parseArgs($options, $defaults);
        $this->text($name, $value, $options);
    }
    public function number($name, $value='', $options=NULL) {
        global $iwb;
        $defaults=array('type'=>'number');
        $options=$iwb->Utils->parseArgs($options, $defaults);
        $this->text($name, $value, $options);
    }
    public function tags($name, $value, $values, $options=NULL) {
        global $iwb;
        if(!is_array($options)) {
            $options=array();
        }
        $options['type']='tags';
        $value=$iwb->Utils->toArray($value);
        foreach($value as $k) {
            $exists=FALSE;
            foreach($values as $v) {
                if($v['id']==$k) {
                    $exists=TRUE;
                    break;
                }
            }

            if(!$exists) {
                $values[]=array('id'=>$k, 'text'=>$k);
            }
        }
        $this->dropdown($name, $value, $values, TRUE, $options);
    }
    public function multiselect($name, $value, $values, $options=array()) {
        if(!is_array($options)) {
            $options=array();
        }
        $options['type']='multiselect';
        $options['optgroup']=TRUE;
        $this->dropdown($name, $value, $values, TRUE, $options);
    }
    public function dropdown($name, $value, $values, $multiple=FALSE, $options=NULL) {
        global $iwb;
        $value=$iwb->Utils->get($name, $value, $value);

        if(!is_array($options)) {
            $options=array();
        }
        if(isset($options['readonly'])) {
            $options['disabled']="disabled";
            unset($options['readonly']);
        }
        if(isset($options['multiple'])) {
            $multiple=$options['multiple'];
            unset($options['multiple']);
        }
        if(!isset($options['type'])) {
            $options['type']='dropdown';
        }
        if(!isset($options['class'])) {
            $options['class']='';
        }
        $options['class'].=' iwb-'.$options['type'];

        $help=$this->prefix;
        if($help!='') {
            $help.='.';
        }
        $help.=$name.'.Help';
        if($iwb->Lang->H($help)) {
            $help=$iwb->Lang->L($help);
        } else {
            $help='Dropdown.'.($multiple ? 'SelectAtLeastOneValue' : 'SelectOneValue');
            if($options['type']=='tags') {
                $help='Dropdown.SelectTagValue';
            }
            $help=$iwb->Lang->L($help);
        }

        $defaults=array(
            'class'=>$options['class']
            , 'iwb-ajax'=>''
            , 'iwb-lazy'=>''
            , 'iwb-domain'=>''
            , 'iwb-class'=>''
            , 'iwb-help'=>$help
            , 'optgroup'=>FALSE
        );
        $other=$iwb->Utils->getTextArgs($options, $defaults, 'title|noLayout|type|optgroup|textLabel');
        $options=$iwb->Utils->parseArgs($options, $defaults);

        if(!is_array($value)) {
            $value=array($value);
        }
        if(is_string($values)) {
            $values=explode(',', $values);
        }
        if(is_array($values) && count($values)>0) {
            if(!isset($values[0]['id']) && !isset($values[0]['text'])) {
                //this is a normal array so I use the values for "id" field and the "name" into the txt file
                $temp=array();
                foreach($values as $v) {
                    if(is_numeric($v) || !is_null($v)) {
                    $temp[]=array('id'=>$v, 'text'=>$iwb->Lang->L($this->prefix.'.'.$name.'.'.$v));
                    }
                }
                $values=$temp;
            }
        }

        foreach($value as $v) {
            if(is_numeric($v) && intval($v)==-1) {
                //[All] option
                $value=array(-1);
                break;
            }
        }

        //sort array
        $values=$iwb->Utils->sortOptions($values);
        if($iwb->Utils->get($options, 'noLayout', FALSE)===FALSE) {
            $args=array('class'=>'field select');
            if(isset($options['row-hidden'])) {
                $args['row-hidden']=$options['row-hidden'];
            }
            if(isset($options['textLabel'])) {
                $args['textLabel']=$options['textLabel'];
            }
            $this->openInput($name, $args);
        }
        ?>
        <select id="<?php $this->getName($name) ?>" name="<?php $this->getName($name, $options) ?><?php echo ($multiple ? '[]' : '')?>" <?php echo ($multiple ? 'multiple="multiple"' : '')?> <?php echo $other?> style="display:none;">
            <?php
            if($options['optgroup']) {
                $label=$this->prefix.'.'.$name.'.Optgroup';
                echo '<optgroup label="'.$iwb->Lang->L($label).'">';
            }
            foreach($values as $v) {
                $other='';
                if(isset($v['style'])) {
                    $other.=' style="'.$v['style'].'"';
                }
                if(isset($v['data'])) {
                    $other.=' data="'.$v['data'].'"';
                }
                if(isset($v['show'])) {
                    $other.=' show="'.$v['show'].'"';
                }

                $selected='';
                if($iwb->Utils->inArray($v['id'], $value)) {
                    $selected=' selected="selected"';
                }
                ?>
                <option value="<?php echo $v['id']?>" <?php echo $selected?> <?php echo $other?>><?php echo (isset($v['text']) ? $v['text'] : $v['name'])?></option>
            <?php }
            if($options['optgroup']) {
                echo '</optgroup>';
            }
            ?>
        </select>
        <?php
        if($iwb->Utils->get($options, 'noLayout', FALSE)===FALSE) {
            $args=array('icon'=>FALSE);
            $this->closeInput($name, $args);
        }
    }

    public function checklist($name, $value, $values, $options=NULL) {
        global $iwb;
        $defaults=array('type'=>'checkbox');
        $options=$iwb->Utils->parseArgs($options, $defaults);

        $selected=$iwb->Utils->get($name, $value, $value);
        $selected=$iwb->Utils->toArray($selected);

        if($iwb->Utils->get($options, 'noLayout', FALSE)===FALSE) {
            $args=array(
                //switch-round
                'class'=>'switch switch-primary block mt5'
                , 'icon'=>FALSE
                , 'label'=>FALSE
            );
            if(isset($options['row-hidden'])) {
                $args['row-hidden']=$options['row-hidden'];
            }
            if(isset($options['textLabel'])) {
                $args['textLabel']=$options['textLabel'];
            }
            $this->openInput($name, $args);
        }
        ?>
        <div class="option-group field">
            <?php foreach($values as $v) {

                $k=$v['id'];
                if(isset($v['text'])) {
                    $v=$v['text'];
                } else {
                    $v=$v['name'];
                }

                $checked=in_array($k, $selected);
                ?>
                <label class="option option-primary block mt10">
                    <input type="<?php echo $options['type']?>" name="<?php $this->getName($name, $options) ?>[]" id="<?php $this->getName($name) ?>_<?php echo $k ?>" value="<?php echo $selected?>" <?php echo ($checked ? 'checked="checked"' : '')?>>
                    <span class="<?php echo $options['type']?>"></span><?php echo $v?>
                </label>
            <?php } ?>
        </div>
        <?php
        if($iwb->Utils->get($options, 'noLayout', FALSE)===FALSE) {
            $this->closeInput($name, $args);
        }
    }
    public function radiolist($name, $value, $values, $options=NULL) {
        if(!$options || !is_array($options)) {
            $options=array();
        }
        $options['type']='radio';
        $this->checklist($name, $value, $values, $options);
    }
    function getInfoUpload($value) {
        global $iwb;
        $files=array();
        $text='';
        $value=$iwb->Utils->toArray($value);
        foreach($value as $v) {
            if($v=='') {
                continue;
            }

            $v=str_replace("\\", "/", $v);
            $v=explode("/", $v);

            if($text!='') {
                $text.=',';
            }
            $text.=$v[count($v)-1];
            $v=IWB_UPLOAD_BASE_DIR.implode("/", $v);
            $f=$iwb->Utils->getFileInfo($v);
            if($f!==FALSE) {
                $files[]=$f;
            }
        }

        $result=array(
            'files'=>$files
            , 'text'=>$text
        );
        return $result;
    }
    public function checkbox($name, $value='', $selected=1, $options=array()) {
        if(!is_array($options)) {
            $options=array();
        }
        $options['class']='checkbox-custom checkbox-primary block mt5'; //mb5 mt10
        $this->toggle($name, $value, $selected, $options);
    }
    public function toggle($name, $value='', $selected=1, $options=array()) {
        global $iwb;

        $value=$iwb->Utils->get($name, $value, $value);
        if(is_bool($value)) {
            $value=($value ? 1 : 0);
        }
        $checked=($value==$selected);
        $id=$name;
        if($iwb->Utils->endsWith($id, '[]')) {
            $id=substr($id, 0, strlen($id)-2);
            $id.='_'.$selected;
        }

        $defaults=array(
            'data-on'=>$iwb->Lang->L('Toggle.Yes')
            , 'data-off'=>$iwb->Lang->L('Toggle.No')
            , 'afterText'=>''
            , 'class'=>'switch switch-round switch-primary block mt5' //mt10
            , 'ui-visible'=>''
        );
        $options=$iwb->Utils->parseArgs($options, $defaults);
        $otherText='';
        if($options['ui-visible']) {
            $otherText=' ui-visible="'.$options['ui-visible'].'" ';
        }
        $disabled='';
        if(isset($options['disabled']) || isset($options['readonly'])) {
            $options['readonly']='readonly';
            $disabled='disabled="disabled"';
        }

        if($iwb->Utils->get($options, 'noLayout', FALSE)===FALSE) {
            $args=array(
                'class'=>$options['class']
                , 'icon'=>FALSE
            );
            if(isset($options['row-hidden'])) {
                $args['row-hidden']=$options['row-hidden'];
            }
            if(isset($options['textLabel'])) {
                $args['textLabel']=$options['textLabel'];
            }
            $this->openInput($id, $args);
        } else { ?>
            <label for="<?php $this->getName($id)?>" class="<?php echo $options['class']?>">
        <?php }

        $dataCss='';
        if(strpos($options['class'], 'checkbox')!==FALSE) {
            $dataCss='height:21px; ';
        }
        ?>
        <input type="checkbox" name="<?php $this->getName($name, $options) ?>" id="<?php $this->getName($id) ?>" value="<?php echo $selected?>" <?php echo ($checked ? 'checked="checked"' : '')?> <?php echo $disabled?>  <?php echo $otherText?>>
        <label for="<?php echo $id ?>" data-on="<?php echo $options['data-on'] ?>" data-off="<?php echo $options['data-off'] ?>" style="<?php echo $dataCss?>"></label>
        <?php if($options['afterText']!='') { ?>
            <span><?php echo $options['afterText']?></span>
        <?php } ?>

        <?php
        if($iwb->Utils->get($options, 'noLayout', FALSE)===FALSE) {
            $this->closeInput($name, $args);
        } else { ?>
            </label>
        <?php }
    }

    public function colorpicker($name, $value='', $options=NULL) {
        global $iwb;

        $value=$iwb->Utils->get($name, $value, $value);
        if($iwb->Utils->get($options, 'noLayout', FALSE)===FALSE) {
            $args=array();
            if(isset($options['row-hidden'])) {
                $args['row-hidden']=$options['row-hidden'];
            }
            if(isset($options['textLabel'])) {
                $args['textLabel']=$options['textLabel'];
            }
            $this->openInput($name, $args);
        }
        ?>
        <div class="input-group colorpicker-component cursor iwb-colorpicker">
            <span class="input-group-addon">
                <i></i>
            </span>
            <input type="text" id="<?php $this->getName($name) ?>" name="<?php $this->getName($name, $options) ?>" value="<?php echo $value?>" class="gui-input" />
        </div>
        <?php
        if($iwb->Utils->get($options, 'noLayout', FALSE)===FALSE) {
            $this->closeInput($name, $args);
        }
    }
    public function date($name, $value='', $options=NULL) {
        global $iwb;

        $value=$iwb->Utils->get($name, $value, $value);
        $value=$iwb->Utils->formatDate($value);

        $defaults=array('class'=>'gui-input iwb-date');
        $options=$iwb->Utils->parseArgs($options, $defaults);
        $this->text($name, $value, $options);
    }
    public function time($name, $value='', $options=NULL) {
        global $iwb;

        $value=$iwb->Utils->get($name, $value, $value);
        $value=$iwb->Utils->formatTime($value);

        $defaults=array('class'=>'gui-input iwb-time');
        $options=$iwb->Utils->parseArgs($options, $defaults);
        $this->text($name, $value, $options);
    }
    public function datetime($name, $value='', $options=NULL) {
        global $iwb;

        $value=$iwb->Utils->get($name, $value, $value);
        $value=$iwb->Utils->formatDatetime($value);

        $defaults=array('class'=>'gui-input iwb-datetime');
        $options=$iwb->Utils->parseArgs($options, $defaults);
        $this->text($name, $value, $options);
    }
    function getMasterAjaxDomain($instance, $name, $value, &$options, $params=array()) {
        global $iwb;
        $result=FALSE;

        if(!is_array($params)) {
            $params=array();
        }
        if(isset($params['values'])) {
            $options['values']=$params['values'];
            return TRUE;
        }

        if(is_array($instance) && $name===TRUE) {
            //tricks :(
            $column=$instance;
        } else {
            $column=$iwb->Dao->Utils->getColumn($instance, $name);
        }

        $parentId=FALSE;
        $parent=$iwb->Utils->get($column, 'ui-master', '');
        if($parent!=='') {
            $array=explode('|', $parent);
            $parentId=array();
            foreach($array as $v) {
                $parentId[]=$iwb->Utils->get($instance, $v);
            }
            $parentId=implode('|', $parentId);
            $options['iwb-master']=$parent;
            $result=TRUE;
        }
        //domain
        $domain=$iwb->Utils->get($column, 'ui-domain', '');
        if($domain!=='') {
            $options['iwb-domain']=$domain;
            $_POST['domain']=$domain;
            $result=TRUE;
        }

        //ajax
        $action=$iwb->Utils->get($column, 'ui-ajax', '');
        if($action!=='' && method_exists($iwb->Lazy, $action)) {
            $options['iwb-ajax']=$action;

            $_POST['parentId']=$parentId;
            //$_POST['_ids']=$value;
            $values=call_user_func(array($iwb->Lazy, $action), $params);
            //unset($_POST['_ids']);
            unset($_POST['parentId']);

            $options['values']=$values;
            $result=TRUE;
        }

        //lazy
        $action=$iwb->Utils->get($column, 'ui-lazy', '');
        if($action!=='' && method_exists($iwb->Lazy, $action)) {
            $options['iwb-lazy']=$action;

            $_POST['parentId']=$parentId;
            //$_POST['_ids']=$value;
            $values=call_user_func(array($iwb->Lazy, $action), $params);
            //unset($_POST['_ids']);
            unset($_POST['parentId']);

            $options['values']=$values;
            $result=TRUE;
        }

        if(isset($options['values']) && isset($column['ui-all']) && $iwb->Utils->isTrue($column['ui-all'])) {
            $first=array();
            $first[]=array(
                'id'=>-1
                , 'text'=>'['.$iwb->Lang->L('All').']'
            );
            $options['values']=$iwb->Utils->arrayPush($first, $options['values']);
        }

        unset($_POST['domain']);
        return $result;
    }
    

    public function inputsForm($fields, $instance, $param=array()) {
        global $iwb;
        $fields=$iwb->Utils->toArray($fields);
        foreach($fields as $v) {
            $this->inputForm($instance, $v, $param);
        }
    }
    public function inputForm($instance, $name, $params=array()) {
        global $iwb;
        $options=array();
        if(isset($params['noLayout'])) {
            $options['noLayout']=$params['noLayout'];
            unset($params['noLayout']);
        }

        $name=$iwb->Ui->getFieldOptions($instance, $name, $options);
        $column=$iwb->Dao->Utils->getColumn($instance, $name);
        if(isset($column['alias'])) {
            $name=$column['alias'];
        }

        if(isset($column['ui-type'])) {
            $value=$iwb->Utils->get($instance, $name, '');
            $exists=isset($column['ui-exists']);
            if($value || !$exists) {
                if(isset($options['hidden']) && $options['hidden']) {
                    $value=$iwb->Dao->Utils->encode($instance, $name, $value, FALSE);
                    $this->hidden($name, $value);
                } else {
                    $multiple=$iwb->Utils->get($column, 'ui-multiple', FALSE);
                    $multiple=$iwb->Utils->isTrue($multiple);
                    $autocomplete=$iwb->Utils->get($column, 'ui-autocomplete', '');

                    if($autocomplete!='') {
                        $options['autocomplete']=$autocomplete;
                    }

                    //$prefix=get_class($instance);
                    //$prefix=str_replace(IWB_PLUGIN_PREFIX, '', $prefix).'_';
                    $type=strtolower($column['ui-type']);
                    switch ($type) {
                        case 'dropdown':
                        case 'tags':
                        case 'multiselect':
                            $values=$this->options($instance, $name);
                            $options['values']=$values;
                            $options['multiple']=$multiple;
                            //this function can override $options['values'] elements
                            $this->getMasterAjaxDomain($instance, $name, $value, $options, $params);
                            break;
                        case 'radiolist':
                        case 'checklist':
                            $values=$this->options($instance, $name);
                            $options['values']=$values;
                            break;
                    }
                    $this->inputComponent($type, $name, $value, $options);
                }
            }
        }
    }
    public function inputComponent($type, $name, $value, $options=array()) {
        $values=array();
        $multiple=FALSE;
        $selected=1;
        $md='';
        if(isset($options['col-md'])) {
            $md=$options['col-md'];
            unset($options['col-md']);
        }
        if(isset($options['selected'])) {
            $selected=$options['selected'];
            unset($options['selected']);
        }
        if(isset($options['values'])) {
            $values=$options['values'];
            unset($options['values']);
        }
        if(isset($options['multiple']) && $options['multiple']) {
            $multiple=TRUE;
            unset($options['multiple']);
        }

        if($md!='') {
            echo "\n<div class=\"".$md."\">\n";
        }
        $type=strtolower($type);
        switch ($type) {
            case 'color':
            case 'colorpicker':
                $this->colorpicker($name, $value, $options);
                break;
            case 'colordown':
                $this->colordown($name, $value, $options);
                break;
            case 'text':
                $this->text($name, $value, $options);
                break;
            case 'timer':
                $this->timer($name, $value, $options);
                break;
            case 'upload':
                $this->upload($name, $value, $options);
                break;
            case 'textarea':
                $this->textarea($name, $value, $options);
                break;
            case 'hidden':
                $this->hidden($name, $value, $options);
                break;
            case 'currency':
                $this->currency($name, $value, $options);
                break;
            case 'number':
                $this->number($name, $value, $options);
                break;
            case 'password':
                $this->password($name, $value, $options);
                break;
            case 'email':
                $this->email($name, $value, $options);
                break;
            case 'dropdown':
                $this->dropdown($name, $value, $values, $multiple, $options);
                break;
            case 'multiselect':
                $this->multiselect($name, $value, $values, $options);
                break;
            case 'tags':
                $this->tags($name, $value, $values, $options);
                break;
            case 'date':
                $this->date($name, $value, $options);
                break;
            case 'time':
                $this->time($name, $value, $options);
                break;
            case 'datetime':
                $this->datetime($name, $value, $options);
                break;
            case 'toggle':
                $this->toggle($name, $value, 1, $options);
                break;
            case 'check':
                $this->checkbox($name, $value, $selected, $options);
                break;
            case 'checklist':
                $this->checklist($name, $value, $values, $options);
                break;
            case 'radiolist':
                $this->radiolist($name, $value, $values, $options);
                break;
        }
        if($md!='') {
            echo "\n</div>\n";
        }
    }
    public function options($class, $name) {
        global $iwb;

        $values=array();
        $column=$iwb->Dao->Utils->getColumn($class, $name);
        $dropdownPrefix=$iwb->Utils->upperUnderscoreCase($name).'_';
        $dropdownPrefix=str_replace('_IDS_', '_', $dropdownPrefix);
        $dropdownPrefix=str_replace('_ID_', '_', $dropdownPrefix);
        if(isset($column['ui-prefix']) && $column['ui-prefix']!='') {
            $dropdownPrefix=$column['ui-prefix'];
        }
        if(strpos($dropdownPrefix, '::')==FALSE) {
            $v=$class;
            if(is_object($class)) {
                $v=get_class($class);
            }
            $dropdownPrefix=$v.'Constants::'.$dropdownPrefix;
        }

        $dropdownPrefix=explode('::', $dropdownPrefix);
        $dropdownPrefix[0]=str_replace('Search', '', $dropdownPrefix[0]);
        if(!class_exists($dropdownPrefix[0])) {
            $dropdownPrefix[0]=IWB_PLUGIN_PREFIX.$dropdownPrefix[0];
        }
        if(!class_exists($dropdownPrefix[0])) {
            $result=array();
            return $result;
        }

        $reflection=new ReflectionClass($dropdownPrefix[0]);
        $constants=$reflection->getConstants();
        foreach($constants as $k=>$v) {
            if($iwb->Utils->startsWith($k, $dropdownPrefix[1])) {
                $id=$v;
                $k='Dropdown.'.$dropdownPrefix[0].'.'.$k;
                $v=$iwb->Lang->L($k);
                $values[$v]=$id;
            }
        }

        $inverseKeys=TRUE;
        $result=array();
        if(is_array($values) && count($values)>0) {
            ksort($values);
            $i=0;
            foreach($values as $k=>$v) {
                $colors=$iwb->Utils->get($column, 'ui-style', '', $i);
                if(strpos($colors, ':')===FALSE) {
                    $colors.=':';
                }
                $colors=explode(':', $colors);
                $style='';
                if($colors[0]!='') {
                    $style.='color:'.$colors[0].'; ';
                }
                if($colors[1]!='') {
                    $style.='font-weight:'.$colors[1].'; ';
                }
                if($inverseKeys) {
                    $result[]=array('id'=>$v, 'text'=>$k, 'style'=>$style);
                } else {
                    $result[]=array('id'=>$k, 'text'=>$v, 'style'=>$style);
                }
                ++$i;
            }
        }
        return $result;
    }

    private function getOpenTag($instance, $name, $options) {
        global $iwb;
        if($options===FALSE || $options==='') {
            return '';
        }
        if(is_string($options)) {
            $options=array('tag'=>$options);
        }
        $defaults=array(
            'tag'=>''
            , 'style'=>''
            , 'align'=>''
        );
        $options=$iwb->Utils->parseArgs($options, $defaults);
        if($options['tag']=='') {
            return '';
        }

        if(is_object($instance)) {
            $instance=get_class($instance);
        }
        foreach ($instance as &$value) {
            $value = print_r($value, true);
        }
        $instance=str_replace(IWB_PLUGIN_PREFIX, '', $instance);
        $instance=str_replace('Search', '', $instance);
        $column=$iwb->Dao->Utils->getColumn($instance, $name);
        if($options['align']!='') {
            $column['ui-align']=$options['align'];
        } elseif(!isset($column['ui-align']) || $column['ui-align']=='') {
            $column['ui-align']='center';
        }
        $class=' class="text-'.$column['ui-align'].'" ';
        $result='<'.$options['tag'].$class.' style="'.$options['style'].'">';
        return $result;
    }
    private function getCloseTag($tag) {
        if($tag===FALSE || $tag=='') {
            return '';
        }
        $result="</".$tag.">";
        return $result;
    }
    public function inputHeader($instance, $name, $options) {
        global $iwb;
        $defaults=array(
            'tag'=>FALSE
            , 'echo'=>TRUE
            , 'style'=>''
            , 'align'=>''
            , 'header'=>''
            , 'rawColumnName'=>FALSE
        );
        $options=$iwb->Utils->parseArgs($options, $defaults);
        $args=array();
        $name=$iwb->Ui->getFieldOptions($instance, $name, $args);
        $buffer=$this->getOpenTag($instance, $name, $options);
        $column=$iwb->Dao->Utils->getColumn($instance, $name);
        if($options['header']!='') {
            $header=$iwb->Lang->L($options['header']);
        } else {
            $header='';
            if($this->prefix!='') {
                $header=$this->prefix.'.';
            }
            $header.=$name.'.Header';
            if($iwb->Lang->H($header) || !$options['rawColumnName']) {
                $header=$iwb->Lang->L($header);
            } else {
                $header=$name;
            }
        }
        $buffer.=$header;
        if(isset($column['ui-type'])) {
            $suffix='';
            switch (strtolower($column['ui-type'])) {
                case 'percentage':
                    $symbol=true;
                    if(isset($column['ui-symbol'])) {
                        $symbol=$iwb->Utils->isTrue($column['ui-symbol']);
                    }
                    if($symbol) {
                        $suffix=' %';
                    }
                    break;
                case 'currency':
                    $symbol=$iwb->Utils->getDefaultCurrencySymbol();
                    $symbol=$iwb->Utils->getCurrencySymbol($symbol);
                    $suffix=' '.$symbol;
                    break;
            }
            $buffer.=$suffix;
        }
        $buffer.=$this->getCloseTag($options['tag']);
        if($options['echo']) {
            echo $buffer;
        }
    }
    private function getUiStyle($column, $i) {
        global $iwb;
        if(is_bool($i)) {
            $i=($i ? 1 : 0);
        }
        $colors=$iwb->Utils->get($column, 'ui-style', '', $i);
        if(strpos($colors, ':')===FALSE) {
            $colors.=':';
        }
        $colors=explode(':', $colors);
        $style='';
        if($colors[0]!='') {
            $style.='color:'.$colors[0].'; ';
        }
        if($colors[1]!='') {
            $style.='font-weight:'.$colors[1].'; ';
        }
        return $style;
    }
    public function inputGet($instance, $name, $tag=FALSE, $echo=TRUE) {
        global $iwb;
        $options=array();
        if(is_array($echo)) {
            $options=$echo;
            $echo=TRUE;
        }
        $name=$iwb->Ui->getFieldOptions($instance, $name, $options);
        $value='#'.$name.'#??';
        if(is_array($instance)) {
            if(isset($instance[$name])) {
                $value=$instance[$name];
            } else {
                $value='';
            }
            if(isset($options['format_'.$name])) {
                switch (strtolower($options['format_'.$name])) {
                    case 'datetime':
                        $value=$iwb->Utils->formatDatetime($value);
                        break;
                    case 'time':
                        $value=$iwb->Utils->formatTime($value);
                        break;
                    case 'date':
                        $value=$iwb->Utils->formatDate($value);
                        break;
                    case 'gravatar':
                        $value=$iwb->Utils->getGravatarImage($value);
                        break;
                    case 'currency':
                        $value=$iwb->Utils->formatCurrencyMoney($value);
                        break;
                    case 'percentage':
                        $value=$iwb->Utils->formatPercentage($value);
                        break;
                }
            }
        } else {
        $column=$iwb->Dao->Utils->getColumn($instance, $name);
        if(isset($column['alias'])) {
            $name=$column['alias'];
        }

        if($column===FALSE) {
            $value='#'.$name.'#??';
        } elseif(!isset($column['ui-type'])) {
            $value='ui-type #'.$name.'#??';
        } elseif(isset($options['check']) && $options['check']) {
            $ids=$iwb->Utils->qs('ids', array());
            $value=$iwb->Utils->get($instance, $name, '');
            $options['selected']=$value;
            $options['noLayout']=TRUE;
            if(!$iwb->Utils->inArray($value, $ids)) {
                $value=FALSE;
            }

            ob_start();
                unset($options['readonly']);
                unset($options['disabled']);
            $this->inputComponent('check', 'ids[]', $value, $options);
            $value=ob_get_clean();
        } else {
            $value=$iwb->Utils->get($instance, $name, '');
            $source=$value;

            $type=strtolower($column['ui-type']);
            switch ($type) {
                case 'currency':
                        $value=round(floatval($value), 4);
                    if($value!=0) {
                            $value=$iwb->Utils->formatCurrencyMoney($value);
                    } else {
                        $value='';
                    }
                    break;
                case 'number':
                    $value=intval($value);
                        break;
                    case 'percentage':
                        $symbol=true;
                        if(isset($column['ui-symbol'])) {
                            $symbol=$iwb->Utils->isTrue($column['ui-symbol']);
                        }
                        $value=$iwb->Utils->formatPercentage($value, $symbol);
                    break;
                case 'checklist':
                case 'radiolist':
                    $values=$this->options($instance, $name);
                    $value=$this->optionsText($values, $value);
                    break;
                case 'dropdown':
                case 'tags':
                    case 'select':
                    $values=$this->options($instance, $name);
                    $_GET['_inputGet']=$value;
                    if($this->getMasterAjaxDomain($instance, $name, $value, $options)) {
                        if(isset($options['values'])) {
                            $values=$options['values'];
                            unset($options['values']);
                        }
                    }
                        unset($_GET['_inputGet']);
                    $value=$this->optionsText($values, $value);
                    break;
                case 'date':
                    $value=$iwb->Utils->formatDate($value);
                    break;
                case 'time':
                    $value=$iwb->Utils->formatTime($value);
                    break;
                case 'datetime':
                    $value=$iwb->Utils->formatDatetime($value);
                    break;
                case 'toggle':
                    $value=$iwb->Utils->isTrue($value);
                    $style=$this->getUiStyle($column, $value);
                    $value=$iwb->Lang->L($value ? 'Toggle.Yes' : 'Toggle.No');
                    if($style!='') {
                        $value='<span style="'.$style.'">'.$value.'</span>';
                    }
                    break;
                }
            }
        }

        $buffer=$this->getOpenTag($instance, $name, $tag);
        if(isset($options['ui-link']) && $options['ui-link']) {
            $target='_self';
            if(isset($options['ui-target']) && $options['ui-target']) {
                $target=$options['ui-target'];
            }
            if($value!='') {
                $buffer.='<a href="'.$options['ui-link'].$instance->id.'" target="'.$target.'">';
                $buffer.=$value;
                $buffer.='</a>';
            }
        } else {
            $buffer.=$value;
        }
        $buffer.=$this->getCloseTag($tag);
        if($echo) {
            echo $buffer;
        } else {
            return $buffer;
        }
    }
    public function inputSearch($instance, $name) {
        $prev=$this->search;
        $this->search=TRUE;
        $this->inputForm($instance, $name);
        $this->search=$prev;
    }
    public function optionsText($options, $value) {
        global $iwb;
        $value=$iwb->Utils->toArray($value);
        if($options===FALSE || count($options)==0 || count($value)==0) {
            return '';
        }

        $buffer='';
        foreach($options as $v) {
            if(isset($v['id']) && in_array($v['id'], $value)) {
                if($buffer!='') {
                    $buffer.=', ';
                }
                if(!isset($v['text']) && isset($v['name'])) {
                    $v['text']=$v['name'];
                }

                if(isset($v['style']) && $v['style']!='') {
                    $buffer.='<span style="'.$v['style'].'">'.$v['text']."</span>";
                } else {
                    $buffer.=$v['text'];
                }
            }
        }
        return $buffer;
    }

    public function submit($name='', $options=array()) {
        global $iwb;
        $defaults=array(
            'name'=>'btnSubmit'
            , 'prompt'=>FALSE
            , 'submit'=>TRUE
        );
        $options=$iwb->Utils->parseArgs($options, $defaults);
        if($name=='') {
            $name='Save';
        }
        $this->button($name, $options);
    }
    public function buttonset($name, $buttons, $options=array()) {
        global $iwb;
        if(count($buttons)==0) {
            return;
        }

        $defaults=array(
            'theme'=>''
            , 'icon'=>''
            , 'class'=>''
            , 'rowClass'=>''
            , 'buttonClass'=>''
            , 'clearBoth'=>FALSE
            , 'br'=>FALSE
            , 'noLayout'=>FALSE
        );
        $options=$iwb->Utils->parseArgs($options, $defaults);

        $inputArgs=array(
            'md9'=>FALSE
            , 'label'=>$this->prefix.'.'.$name
                //, 'style'=>'font-size:11px;'
            , 'col-md'=>'col-md-3'
            , 'rowClass'=>$options['rowClass']
        );
        $class='bs-component';
        if(!$options['noLayout']) {
            $class='col-md-9 bs-component';
            $this->openInput($name, $inputArgs);
        }

        $args=array('class'=>$class);
        $this->divStarts($args);
        {
            $args=array('class'=>'btn-group');
            $this->divStarts($args);
            {
                foreach($buttons as $v) {
                    if(is_string($v)) {
                        $v=array('value'=>$v);
                    } elseif(!is_array($v)) {
                        throw new Exception('buttonset: VALUE MUST BE STRING OR ARRAY');
                    }
                    $name=$v['value'];
                    $defaults=array(
                        'theme'=>$options['theme']
                        , 'icon'=>$options['icon']
                        , 'class'=>$options['buttonClass']
                        , 'data-filter'=>''
                        , 'data-id'=>''
                            //, 'class'=>'light mr5'
                        , 'script'=>FALSE
                    );
                    $v=$iwb->Utils->parseArgs($v, $defaults);
                    $this->button($name, $v);
                }
            }
            $this->divEnds();
            if($options['br']) {
                $this->br();
            }
            if($options['clearBoth']) {
                $this->clearBoth();
            }
        }
        $this->divEnds();

        if(!$options['noLayout']) {
            $this->closeInput($name, $inputArgs);
        }
    }
    public function button($value, $options=NULL) {
        global $iwb;
        if(!$this->hiddenActionCreated) {
            $this->hidden('_action', '');
        }

        $this->buttonPresent=TRUE;
        $defaults=array(
            'theme'=>'primary'
            , 'icon'=>$this->getIcon($value, 'cog')
            , 'id'=>'btn'.$value
            , 'name'=>'btn'.$value
            , 'uri'=>FALSE
            , 'type'=>'button'
            , 'prompt'=>FALSE
            , 'rightSpace'=>TRUE
            , 'leftSpace'=>FALSE
            , 'submit'=>FALSE
            , 'class'=>''
            , 'style'=>''
            , 'data-id'=>''
        );
        $options=$iwb->Utils->parseArgs($options, $defaults);
        $uri=$options['uri'];
        $onclick=($uri===FALSE || $uri==='' ? '' : 'onclick="window.location=\''.$uri.'\';"');

        $icon=$options['icon'];
        $leftIcon=TRUE;
        $nextWords=$iwb->Utils->toArray('next|finish|save');
        foreach($nextWords as $w) {
            if(stripos($value, $w)!==FALSE) {
                $leftIcon=FALSE;
                break;
            }
        }
        //btn-block means 100% width
        if($options['leftSpace']) {
            echo '&nbsp;';
        }
        ?>
        <button type="<?php echo $options['type']?>" id="<?php $this->getName($options['id'])?>" name="<?php $this->getName($options['name'])?>" class="btn <?php echo $options['class']?> btn-<?php echo $options['theme']?>" <?php echo $onclick?> value="<?php echo $value?>" data-id="<?php echo $options['data-id']?>" style="<?php echo $options['style']?>">
            <?php if($leftIcon) { ?>
                <i class="fa fa-<?php echo $icon?>"></i>&nbsp;
            <?php } ?>
            <?php $iwb->Lang->P($this->prefix.'.Button.'.$value)?>
            <?php if(!$leftIcon) { ?>
                &nbsp;<i class="fa fa-<?php echo $icon?>"></i>
            <?php } ?>
        </button>
    <?php
        if($options['rightSpace']) {
            echo '&nbsp;';
        }

        if($options['prompt']!==FALSE) {
            $args=$options['prompt'];
            if(!is_array($args)) {
                $args=array();
            }
            if(!isset($args['submit'])) {
                $args['submit']=$options['submit'];
            }
            if(!isset($args['btnConfirmTheme']) && isset($options['theme'])) {
                $args['btnConfirmTheme']=$options['theme'];
            }
            $this->prompt($options['id'], $args);
        }
        if($options['prompt']==FALSE) { ?>
            <script>
                jQuery(function() {
                    <?php $this->jQueryBtnConfirm($options['id'], $options['id'], $options) ?>
                });
            </script>
        <?php }
    }

    public function openBlock() {
        $this->blockOpened=TRUE;
        ?>
        <div class="mw1000 left-block">
    <?php }
    public function closeBlock() {
        if(!$this->blockOpened) {
            return;
        } ?>
        </div>
    <?php }
    public function panel($title, $callback=FALSE, $options=array()) {
        if(is_callable($title) && $callback==FALSE) {
            $callback=$title;
            $title='';
        }
        $style=(isset($options['style']) ? $options['style'] : 'primary');
        $this->openPanel($title, $style);
        $callback();
        $this->closePanel();
    }
    public function openPanel($options=array()) {
        global $iwb;
        if(is_string($options)) {
            $options=array('title'=>$options);
        }
        $defaults=array(
            'title'=>''
            , 'titleText'=>''
            , 'subtitle'=>TRUE
            , 'style'=>''
            , 'panelTop'=>FALSE
            , 'panelColor'=>''
            , 'icon'=>''
            , 'class'=>''
            , 'body'=>TRUE
	        , 'buttons'=>FALSE
            , 'id'=>''
        );
        $options=$iwb->Utils->parseArgs($options, $defaults);
        if($options['title']=='') {
            $options['title']='Name';
        }
        $options['title']=ucfirst($options['title']);

        $title='Panel.'.$this->prefix.'.'.$options['title'];
        $title=$iwb->Lang->L($title);
        if($options['titleText']!='') {
            $title=$options['titleText'];
        }

        $style=$options['style'];
        $panel='';
        if($style!='') {
            //list($style, $color)=$ec->Utils->pickColor();
            $panel='panel-'.$style;
        }
        if($options['panelTop']) {
            $panel.=' panel-border top';
        }

        $subtitle='';
        if(is_bool($options['subtitle']) && $options['subtitle']) {
            $subtitle=$iwb->Lang->L('Panel.'.$this->prefix.'.'.$options['title'].'Subtitle');
        } elseif(is_string($options['subtitle']) && $options['subtitle']!='') {
            $subtitle=$iwb->Lang->L($options['subtitle']);
        }
        ?>
        <div class="panel <?php echo $panel?> mt20 mb20 <?php echo $options['class']?>" id="<?php echo $options['id']?>">
            <?php if(is_array($options['buttons']) && count($options['buttons'])>0) { ?>
                <div class="panel-header-buttons text-left">
                    <?php $iwb->Form->buttons($options['buttons']); ?>
                </div>
            <?php } else { ?>
                <div class="panel-heading">
                    <?php if ($options['icon'] != '') { ?>
                        <span class="panel-icon">
                            <i class="fa fa-<?php echo $options['icon'] ?>"></i>
                        </span>
                    <?php } ?>
                    <span class="panel-title">
                        <?php echo $title ?>
                    </span>
                </div>
            <?php } ?>

            <?php if ($options['body']) { ?>
                <div class="panel-body bg-light dark">
                    <?php if ($subtitle != '') { ?>
                        <div class="panel-subtitle">
                            <?php echo $subtitle ?>
                        </div>
                    <?php } ?>
                    <div class="admin-form">
            <?php }
    }
    public function closePanel($options=array()) {
        global $iwb;
        $defaults = array('body'=>TRUE, 'buttons'=>FALSE);
        $options = $iwb->Utils->parseArgs($options, $defaults);

        if ($options['body']) { ?>
                </div>
            </div>
            <?php
            if (is_array($options['buttons']) && count($options['buttons']) > 0) { ?>
                <div class="panel-footer-buttons text-right">
                    <?php $iwb->Form->buttons($options['buttons']); ?>
                </div>
            <?php } ?>
        </div>
    <?php }
    }

    //popup
    public function prompt($buttonId, $options=array()) {
        global $iwb;
        $p=$this->prefix.'.Prompt.'.$buttonId.'.';
        $defaults=array(
            'btnAbort'=>$buttonId.'Abort'
            , 'btnAbortTheme'=>''
            , 'btnAbortText'=>$p.'ButtonAbort'
            , 'btnConfirm'=>$buttonId.'Confirm'
            , 'btnConfirmTheme'=>'primary'
            , 'btnConfirmText'=>$p.'ButtonConfirm'
            , 'uri'=>''
            , 'submit'=>TRUE
            , 'effect'=>'newspaper'
        );
        $options=$iwb->Utils->parseArgs($options, $defaults);
        $options['btnAbortText']=$iwb->Lang->L($options['btnAbortText']);
        $options['btnConfirmText']=$iwb->Lang->L($options['btnConfirmText']);
        if(!isset($options['btnAbortIcon'])) {
            $options['btnAbortIcon']=$this->getIcon($options['btnAbortText']);
        }
        if(!isset($options['btnConfirmIcon'])) {
            $options['btnConfirmIcon']=$this->getIcon($options['btnConfirmText']);
        }
        $modalId='modal-prompt-'.$buttonId;
        ?>
        <!-- Panel popup -->
        <div id="<?php echo $modalId?>" class="popup-basic bg-none mfp-with-anim mfp-hide">
            <div class="panel panel-<?php echo $options['btnConfirmTheme']?>">
                <div class="panel-heading">
                    <span class="panel-icon">
                        <i class="fa fa-question-circle"></i>
                    </span>
                    <span class="panel-title"><?php $iwb->Lang->P($p.'Title')?></span>
                </div>
                <div class="panel-body">
                    <p><?php $iwb->Lang->P($p.'Text')?></p>
                </div>
                <div class="panel-footer text-right">
                    <button id="<?php echo $options['btnAbort']?>" class="btn btn-<?php echo $options['btnAbortTheme']?>" type="button">
                        <?php if($options['btnAbortIcon']!==FALSE) { ?>
                            <i class="fa fa-<?php echo $options['btnAbortIcon'] ?>"></i>
                            &nbsp;
                        <?php } ?>
                        <?php echo $options['btnAbortText'] ?>
                    </button>
                    <button id="<?php echo $options['btnConfirm']?>" class="btn btn-<?php echo $options['btnConfirmTheme']?>" type="button">
                        <?php if($options['btnConfirmIcon']!==FALSE) { ?>
                            <i class="fa fa-<?php echo $options['btnConfirmIcon'] ?>"></i>
                            &nbsp;
                        <?php } ?>
                        <?php echo $options['btnConfirmText'] ?>
                    </button>
                </div>
            </div>
        </div>
        <script>
            jQuery(function() {
                jQuery('#<?php echo $buttonId?>').on('click', function() {
                    jQuery.magnificPopup.open({
                        removalDelay: 0//500
                        , items: {
                            src: '#<?php echo $modalId?>'
                        }
                        //, overflowY: 'hidden'
                        /*, callbacks: {
                            beforeOpen: function(e) {
                                this.st.mainClass='mfp-<?php echo $options['effect']?>';
                            }
                        }*/
                        , midClick: true
                    });
                });
                <?php $this->jQueryBtnConfirm($buttonId, $options['btnConfirm'], $options) ?>
                jQuery('#<?php echo $options['btnAbort']?>').on('click', function(e) {
                    e.preventDefault();
                    jQuery.magnificPopup.close();
                });
            });
        </script>
    <?php }
    private function jQueryBtnConfirm($btnButtonId, $btnConfirmId, $options) { ?>
        jQuery('#<?php echo $btnConfirmId ?>').on('click', function(e) {
            e.preventDefault();
        <?php if($options['uri']!==FALSE && $options['uri']!='') { ?>
            location.href="<?php echo $options['uri']?>";
        <?php } elseif($options['submit']) { ?>
            var $btn=jQuery('#<?php echo $btnButtonId?>');
            var $form=$btn.closest('form');
            var $action=jQuery('[name=_action]');
            if($action.length>0) {
                $action.val($btn.val());
            }

            if($form.length>0) {
                jQuery('input, select').prop('disabled', false);
                $form[0].submit();
            }
        <?php } ?>
        });
    <?php }
    private function getColumnDetails($options, $name) {
        global $iwb;
        $defaults=array(
            'style'=>''
            , 'header'=>''
            , 'function'=>FALSE
            , 'align'=>''
            , 'class'=>''
        );
        $result=(isset($options[$name]) ? $options[$name] : array());
        $result=$iwb->Utils->parseArgs($result, $defaults);
        return $result;
    }
    public function inputTable($fields, $items, $options=array()) {
        global $iwb;
        $defaults=array(
            'class'=>''
            , 'style'=>''
            , 'data-filter'=>''
            , 'rowOptions'=>array()
            , 'bgClass'=>FALSE
            , 'rawColumnName'=>FALSE
        );
        $options=$iwb->Utils->parseArgs($options, $defaults);
        $fields=$iwb->Utils->toArray($fields);
        ?>
        <div class="table-responsive">
            <table class="table bg-white tc-checkbox-1 <?php echo $options['class']?>" style="<?php echo $options['style']?>" data-filter="<?php echo $options['data-filter']?>">
            <thead>
                    <tr class="bg-light">
                    <?php
                        foreach($fields as $name) {
                            $details=$this->getColumnDetails($options, $name);
                            $args=array(
                                'tag'=>'th'
                                , 'rawColumnName'=>$options['rawColumnName']
                                , 'style'=>$details['style']
                                , 'header'=>$details['header']
                                , 'align'=>$details['align']
                            );
                            $this->inputHeader($items, $name, $args);
                    } ?>
                </tr>
            </thead>
            <tbody>
                <?php
                $i=0;
                foreach($items as $instance) {
                    $i++;
                    $bgClass='';
                    if($options['bgClass']!==FALSE) {
                        $bgClass=$options['bgClass']($instance);
                    } else {
                        $bgClass=($i%2==0 ? 'even' : 'odd');
                    }
                    ?>
                    <tr class="<?php echo $bgClass?>">
                        <?php foreach($fields as $name) {
                            $details=$this->getColumnDetails($options, $name);
                            $rowOptions=$options['rowOptions'];
                            if(!is_array($rowOptions)) {
                                $rowOptions=array();
                            }
                            $args=array();
                            $columnName=$iwb->Ui->getFieldOptions($instance, $name, $args);
                            $column=$iwb->Dao->Utils->getColumn($instance, $columnName);
                            $align=$iwb->Utils->get($column, 'ui-align', '');
                            if($align=='') {
                                $alignKey='align_'.$name;
                                $align=(isset($rowOptions[$alignKey]) ? $rowOptions[$alignKey] : '');
                            }
                            if($align=='') {
                                $align=$details['align'];
                                if($align=='') {
                                    $align='center';
                                }
                            }

                            $columnKey='column_'.$columnName;
                            $alignKey='class_'.$name;
                            $class=(isset($rowOptions[$alignKey]) ? $rowOptions[$alignKey] : '');
                            if($class=='') {
                                $class=$details['class'];
                            }

                            echo '<td class="'.$class.' text-'.$align.'" style="'.$details['style'].'">';
                            if(isset($options[$columnKey])) {
                                $iwb->Utils->functionCall($options[$columnKey], $instance);
                            } elseif($details['function']!==FALSE) {
                                $iwb->Utils->functionCall($details['function'], $instance);
                            } else {
                                $this->inputGet($instance, $name, '', $rowOptions);
                            }
                            echo '</td>';
                        } ?>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
        </div>
    <?php }
    public function submits($buttons) {
        if($buttons===FALSE || count($buttons)==0) {
            return;
        }
        foreach ($buttons as $k=>$v) {
            $v['submit']=TRUE;
            $this->button($k, $v);
        }
    }
    public function buttons($buttons) {
        if($buttons===FALSE || count($buttons)==0) {
            return;
        }
        foreach ($buttons as $k=>$v) {
            $this->button($k, $v);
        }
    }
    public function colordown($name, $value, $options=array()) {
        //$colors['(Default)']=array('color'=>'', 'fontColor'=>'#464646');
        $colors=array();
        $colors['WHITE']=array('color'=>'#FFFFFF', 'fontColor'=>'#464646');
        $colors['LIGHT GREY']=array('color'=>'#ECF0F1', 'fontColor'=>'#464646');
        $colors['DARK GREY']=array('color'=>'#555555');
        $colors['BLACK']=array('color'=>'#000000');

        $colors['WHITE']=array('color'=>'#FFFFFF', 'fontColor'=>'#464646');
        $colors['LIGHT GREY']=array('color'=>'#ECF0F1', 'fontColor'=>'#464646');
        $colors['DARK GREY']=array('color'=>'#555555');
        $colors['BLACK']=array('color'=>'#000000'); //black

        $colors['TURQUOISE AQUA']=array('color'=>'#1ABC9C'); //Aqua
        $colors['EMERALD GREEN']=array('color'=>'#2ECC71'); //green
        $colors['AMETHYST VIOLET']=array('color'=>'#9B59B6');//violet
        $colors['PETER RIVER BLUE']=array('color'=>'#3498DB');//blue
        $colors['WET ASPHALT BLUE']=array('color'=>'#34495E');//blue
        $colors['SUN FLOWER YELLOW']=array('color'=>'#F1C40F');//yellow
        $colors['CARROT ORANGE']=array('color'=>'#E67E22');//orange
        $colors['ALIZARIN RED']=array('color'=>'#E74C3C');//red
        $colors['CLOUDS GREY']=array('color'=>'#ECF0F1', 'fontColor'=>'#464646');//grey
        $colors['CONCRETE GREY']=array('color'=>'#95A5A6');//grey (+grey)

        $colors['(Transparent)']=array('color'=>'', 'fontColor'=>'#464646');
        $colors['GREEN SEA AQUA']=array('color'=>'#16A085');
        $colors['NEPHRITIS GREEN']=array('color'=>'#27AE60');
        $colors['WISTERIA VIOLET']=array('color'=>'#8E44AD');
        $colors['BELIZE HOLE BLUE']=array('color'=>'#2980B9');
        $colors['MIDNIGHT BLUE ']=array('color'=>'#2C3E50');
        $colors['ORANGE']=array('color'=>'#F39C12');
        $colors['PUMPKIN ORANGE']=array('color'=>'#D35400');
        $colors['POMEGRANATE RED']=array('color'=>'#C0392B');
        $colors['SILVER GREY']=array('color'=>'#BDC3C7');
        $colors['ASBESTOS GREY']=array('color'=>'#7F8C8D');

        $array=array();
        foreach($colors as $k=>$v) {
            $color=(isset($v['color']) ? $v['color'] : '');
            $fontColor=(isset($v['fontColor']) ? $v['fontColor'] : 'white');
            $style='';
            if($color!='') {
                if($style!='') {
                    $style.='; ';
                }
                $style.='background-color:'.$color;
            }
            if($fontColor!='') {
                if($style!='') {
                    $style.='; ';
                }
                $style.='color:'.$fontColor.'; font-weight:bold';
            }
            $v['id']=$color;
            $v['name']=$k;
            $v['style']=$style;
            $array[]=$v;
        }
        $options['type']='colordown';
        return $this->dropdown($name, $value, $array, FALSE, $options);
    }
}