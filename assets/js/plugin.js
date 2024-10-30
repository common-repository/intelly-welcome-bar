//IntellyWP
jQuery('.wrap .updated.fade').remove();
jQuery('.woocommerce-message').remove();
jQuery('.error').remove();
jQuery('.info').remove();
jQuery('.update-nag').remove();

jQuery(function() {
    "use strict";
    //WooCommerce errors
    var removeWooUpdateTheme = setInterval(function () {
        if (jQuery('.wrap .updated.fade').length > 0) {
            jQuery('.wrap .updated.fade').remove();
            clearInterval(removeWooUpdateTheme);
        }
    }, 100);
    var removeWooMessage = setInterval(function () {
        if (jQuery('.woocommerce-message').length > 0) {
            jQuery('.woocommerce-message').remove();
            clearInterval(removeWooMessage);
        }
    }, 100);

    jQuery('.wrap .updated.fade').remove();
    jQuery('.woocommerce-message').remove();
    jQuery('.error').remove();
    jQuery('.info').remove();
    jQuery('.update-nag').remove();
});

jQuery(function() {
    if(jQuery('.wrap .updated.fade').length>0) {
        jQuery('.wrap .updated.fade').remove();
    }
    if(jQuery('.woocommerce-message').length>0) {
        jQuery('.woocommerce-message').remove();
    }
    jQuery('.update-nag,.updated,.error').each(function() {
        var $self=jQuery(this);
        if(!$self.hasClass('iwp')) {
            $self.remove();
        }
    });
});

jQuery(function() {
    "use strict";

    //WooCommerce errors
    var removeWooUpdateTheme=setInterval(function() {
        if(jQuery('.wrap .updated.fade').length>0) {
            jQuery('.wrap .updated.fade').remove();
            clearInterval(removeWooUpdateTheme);
        }
    }, 100);
    var removeWooMessage=setInterval(function() {
        if(jQuery('.woocommerce-message').length>0) {
            jQuery('.woocommerce-message').remove();
            clearInterval(removeWooMessage);
        }
    }, 100);

    jQuery('form').on('submit', function() {
        jQuery('input, select').prop('disabled', false);
        return true;
    });

    var LB_visibleChanges={};
    jQuery('input,select,textarea').each(function() {
        var $self=jQuery(this);
        var name=IWB.attr($self, 'name', '');
        var visible=IWB.attr($self, 'ui-visible', '');
        if(visible!='') {
            var conditions=visible.split('&');
            var index=0;
            for(index=0; index<conditions.length; index++) {
                var k=conditions[index].split(':');
                k=k[0];
                var v=LB_visibleChanges[k];
                if(v==undefined) {
                    v=new Array();
                }
                v.push(name);
                LB_visibleChanges[k]=v;
            }
        }
    });

    jQuery.each(LB_visibleChanges, function(k,v) {
        var $what=jQuery('[name='+k+']');
        $what.change(function() {
            jQuery.each(v, function(i,name) {
                var $self=jQuery('[name='+name+']');
                var $div=jQuery('#'+name+'-row');
                var visible=IWB.attr($self, 'ui-visible', '');

                var all=true;
                var conditions=visible.split('&');
                var index=0;
                for(index=0; index<conditions.length; index++) {
                    var text=conditions[index].split(':');
                    var $compare=IWB.jQuery(text[0]);
                    var current=IWB.val($compare);
                    var options=text[1];
                    options=options.split('|');

                    var found=false;
                    jQuery.each(options, function(i,compare) {
                        if(compare!='' && compare==current) {
                            found=true;
                            return false;
                        }
                    });

                    if(!found) {
                        all=false;
                        break;
                    }
                }

                if(all) {
                    $div.show();
                } else {
                    $div.hide();
                }
            });
        });
        $what.trigger('change');
        //console.log('WHAT=%s TRIGGER CHANGE', IWB.attr($what, 'name'));
    });

    if(jQuery().multiselect) {
        jQuery('.iwb-multiselect').multiselect({
            buttonClass: 'btn btn-default btn-sm ph15',
            dropRight: true
        });
    }
    jQuery('.iwb-dropdown').each(function() {
        var $self=jQuery(this);
        var options={};
        IWB.select2($self, options);
        IWB.changeShowOptions($self);

        var ajax=IWB.attr($self, 'iwb-ajax', false);
        var lazy=IWB.attr($self, 'iwb-lazy', false);
        var help=IWB.attr($self, 'iwb-help', '');
        var parent=IWB.attr($self, 'iwb-master', '');

        if (parent!=='') {
            var masters=parent.split('|');
            var $parent=false;
            if(masters.length==1) {
                $parent=IWB.jQuery(masters[0]);
            } else {
                //register only to the last
                $parent=IWB.jQuery(masters[masters.length-1]);
            }
            $parent.change(function() {
                //console.log('PARENT CHANGE %s > %s'
                //    , IWB.attr($parent, 'name'), IWB.attr($self, 'name'));
                IWB.select2($self, {data: []});

                var parentId=IWB.val(parent);
                var check=false;
                if(parentId!==undefined && parentId!='') {
                    var array=parentId.split('|');
                    check=true;
                    jQuery.each(array, function(i,v) {
                        if(v=='') {
                            check=false;
                            return false;
                        }
                    });
                }
                if(lazy && parentId!='' && check) {
                    $parent.prop('disabled', true);
                    $self.prop('disabled', true);

                    var id=$self.attr('id');
                    var $text=jQuery('#select2-'+id+'-container .select2-selection__placeholder');
                    var placeholder=$text.html();
                    $text.html('Loading data..');

                    jQuery.ajax({
                        type: 'POST'
                        , dataType: 'json'
                        , data: {
                            //action: 'lb_ajax_ll'
                            //, lb_action: lazy
                            action: lazy
                            , parentId: parentId
                        }
                        , success: function(data) {
                            //console.log('success');
                            //console.log(data);

                            IWB.select2($self, {data: data});
                            $self.prop('disabled', false);
                            $parent.prop('disabled', false);
                            $text.html(placeholder);
                        }
                        , error: function(data) {
                            //console.log('error');
                            //console.log(data);

                            $self.prop('disabled', false);
                            $parent.prop('disabled', false);
                            $text.html(placeholder);
                        }
                    });
                }
            });
            /*var v=$self.val();
            if(v==null || (jQuery.isArray(v) && v.length==0) || v=='') {
                $parent.trigger('change');
            }*/
        }
    });
    jQuery('.iwb-tags').each(function() {
        var $self=jQuery(this);
        var options={
            tags: true
            , tokenSeparators: [',', ' ']
        };
        IWB.select2($self, options);
        IWB.changeShowOptions($self);
    });

    jQuery('.iwb-tags, .iwb-dropdown').change(function() {
        var $self=jQuery(this);
        IWB.changeShowOptions($self);
    });

    jQuery(".iwb-hideShow").click(function () {
        IWB.hideShow(this);
    });
    jQuery(".iwb-hideShow").each(function () {
        IWB.hideShow(this);
    });
    jQuery(".alert-dismissable .close").on('click', function() {
        var $self=jQuery(this);
        $self.parent().parent().remove();
    });

    if(jQuery.colorpicker) {
        jQuery('.iwb-colorpicker').colorpicker();
    }

    //iwb-timer
    jQuery('.iwb-timer').on('change', function() {
        var $self=jQuery(this);
        var name=IWB.attr($self, 'name');

        var $days=IWB.jQuery(name+'Days');
        var $hours=IWB.jQuery(name+'Hours');
        var $minutes=IWB.jQuery(name+'Minutes');
        var $seconds=IWB.jQuery(name+'Seconds');

        var text=$days.val()+':'+$hours.val()+':'+$minutes.val()+':'+$seconds.val();
        text=IWB.formatTimer(text);
        $self.val(text);

        text=text.split(':');
        $days.val(parseInt(text[0]));
        $hours.val(parseInt(text[1]));
        $minutes.val(parseInt(text[2]));
        $seconds.val(parseInt(text[3]));
    });
    jQuery('.iwb-timer').each(function() {
        var $self=jQuery(this);
        var name=IWB.attr($self, 'name');

        var $days=IWB.jQuery(name+'Days');
        var $hours=IWB.jQuery(name+'Hours');
        var $minutes=IWB.jQuery(name+'Minutes');
        var $seconds=IWB.jQuery(name+'Seconds');

        $days.on('change', function() {
            $self.trigger('change');
        })
        $hours.on('change', function() {
            $self.trigger('change');
        })
        $minutes.on('change', function() {
            $self.trigger('change');
        })
        $seconds.on('change', function() {
            $self.trigger('change');
        })
        $self.trigger('change');
    });

    /*jQuery('.iwb-time:not([readonly])').timepicker({
        beforeShow: function(input, inst) {
            var themeClass='theme-primary';
            inst.dpDiv.wrap('<div class="'+themeClass+'"></div>');
        }
    });*/
    jQuery('.iwb-datetime:not([readonly])').datetimepicker({
        prevText: '<i class="fa fa-chevron-left"></i>'
        , nextText: '<i class="fa fa-chevron-right"></i>'
        , format: 'DD/MM/YYYY HH:mm'
        , beforeShow: function(input, inst) {
            var themeClass='theme-primary';
            inst.dpDiv.wrap('<div class="'+themeClass+'"></div>');
        }
        , firstDay: 1
    });
    if(jQuery(".iwb-date:not([readonly])").length>0) {
        jQuery(".iwb-date:not([readonly])").datepicker({
            prevText: '<i class="fa fa-chevron-left"></i>'
            , nextText: '<i class="fa fa-chevron-right"></i>'
            , showButtonPanel: false
            , dateFormat: 'dd/mm/yy'
            , beforeShow: function(input, inst) {
                var themeClass='theme-primary';
                inst.dpDiv.wrap('<div class="'+themeClass+'"></div>');
            }
            , firstDay: 1
        });
    }

    /*if(jQuery(".ecTags").length>0) {
        jQuery(".ecTags").select2({
            placeholder: "Type here..."
            , theme: "classic"
            , width: '300px'
        });
    }

    if(jQuery(".ecColorSelect").length>0) {
        jQuery(".ecColorSelect").select2({
            placeholder: "Type here..."
            , theme: "classic"
            , width: '300px'
            , formatResult: IWB_formatColorOption
            , formatSelection: IWB_formatColorOption
            , escapeMarkup: function(m) {
                return m;
            }
        });
    }*/
    jQuery('.iwb-button-toggle').on('click', function() {
        var $self=jQuery(this);
        var showClass=$self.attr('data-filter');
        if(showClass=='') {
            return;
        }
        var pos=showClass.lastIndexOf('-');
        var baseClass=showClass.substring(0, pos);

        //console.log('baseClass=%s, count=%s', showClass, jQuery('.'+baseClass).length);
        //console.log('showClass=%s, count=%s', showClass, jQuery('.'+showClass).length);

        $self.parent().children().each(function(i,v) {
            var $this=jQuery(this);
            if(!$this.hasClass('iwb-button-toggle')) {
                return;
            }

            var thisClass=$this.attr('data-filter');
            if(thisClass.indexOf(baseClass)===0) {
                $this.removeClass('active');
                $this.removeClass('btn-info');
            }
        });

        jQuery('.'+baseClass).hide();
        jQuery('.'+showClass).show();
        $self.addClass('active');
        $self.addClass('btn-info');
    });

    jQuery.browser = {};
    (function () {
    jQuery.browser.msie = false;
    jQuery.browser.version = 0;
    if (navigator.userAgent.match(/MSIE ([0-9]+)\./)) {
    jQuery.browser.msie = true;
    jQuery.browser.version = RegExp.$1;
    }
    })();

    if(jQuery('[data-toggle=tooltip]').qtip) {
        jQuery('[data-toggle=tooltip]').qtip({
            position: {
                corner: {
                    target: 'topMiddle',
                    tooltip: 'bottomLeft'
                }
            },
            show: {
                when: {event: 'mouseover'}
            },
            hide: {
                fixed: true,
                when: {event: 'mouseout'}
            },
            style: {
                tip: 'bottomLeft',
                name: 'blue'
            }
        });
    }

    var IWB_WpMedia;
    jQuery('.iwb-upload-button').on('click', function(e) {
        e.preventDefault();
        var $button=jQuery(this);
        var name=IWB.attr($button, 'data-id', '');
        var multiple=IWB.attr($button, 'ui-multiple', false);
        multiple=IWB.isTrue(multiple);
        var $text=IWB.jQuery(name);

        //If the uploader object has already been created, reopen the dialog
        if (IWB_WpMedia) {
            IWB_WpMedia.open();
            return;
        }
        //Extend the wp.media object
        IWB_WpMedia=wp.media.frames.file_frame=wp.media({
            title: 'Choose Image'
            , button: {
                text: 'Choose Image'
            }
            , multiple: multiple
        });

        //When a file is selected, grab the URL and set it as the text field's value
        IWB_WpMedia.on('select', function() {
            var attachment=IWB_WpMedia.state().get('selection').first().toJSON();
            $text.val(attachment.url);
        });

        //Open the uploader dialog
        IWB_WpMedia.open();
    });
    jQuery('.iwb-select-onfocus').focus(function() {
        var $self=jQuery(this);
        $self.select();
    });

    function IWB_formatColorOption(option) {
        if (!option.id) {
            return option.text;
        }

        var color=jQuery(option.element).css('background-color');
        var font=jQuery(option.element).css('color');
        var $option = jQuery('<div></div>')
            .html(option.text)
            .css('background-color', color)
            .css('color', font)
            .addClass('wb-colordown-item');
        return $option;
    }

    if(jQuery(".iwb-colordown").length>0) {
        var options={
            templateResult: IWB_formatColorOption
            , templateSelection: IWB_formatColorOption
            , escapeMarkup: function(m) {
                return m;
            }
        };
        jQuery(".iwb-colordown").each(function() {
            var $self=jQuery(this);
            IWB.select2($self, options);
            IWB.changeShowOptions($self);
        });
        /*jQuery(".iwb-colordown").select2({
            placeholder: "Type here..."
            , theme: "classic"
            , width: '300px'
            , formatResult: IRP_formatColorOption
            , formatSelection: IRP_formatColorOption
            , escapeMarkup: function(m) {
                return m;
            }
        });*/
    }
});
