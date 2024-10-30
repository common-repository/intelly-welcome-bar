//IntellyWP
var IWB={};
IWB.stripos=function(haystack, needle, offset) {
    var haystack=(haystack + '').toLowerCase();
    var needle=(needle + '').toLowerCase();
    var index=0;

    if ((index=haystack.indexOf(needle, offset))!==-1) {
        return index;
    }
    return false;
};
IWB.strpos=function(haystack, needle, offset) {
    var haystack=(haystack + '');
    var needle=(needle + '');
    var index=0;

    if ((index=haystack.indexOf(needle, offset))!==-1) {
        return index;
    }
    return false;
};
IWB.val=function(name, defaults) {
    var result=[];
    var $self=IWB.jQuery(name);
    if($self!==false) {
        $self.each(function(i,v) {
            var $this=jQuery(this);
            var type=IWB.attr($this, 'type', '');
            if(type=='checkbox') {
                v=IWB.check($this);
            } else if(type=='radio') {
                v=IWB.radio($this);
            } else {
                v=$this.val();
            }
            result.push(v);
        });
    }
    if(result.length==0 || (result.length==1 && result[0]===null)) {
        result=defaults;
    } else {
        result=result.join('|');
    }
    return result;
};
IWB.check=function(name) {
    var $self=IWB.jQuery(name);
    return ($self.is(':checked') ? 1 : 0);
};
IWB.radio=function(name) {
    var $self=IWB.jQuery(name);
    return ($self.filter(':checked').val());
};
IWB.visible=function(name, visible) {
    if(visible) {
        jQuery(name).hide();
    } else {
        jQuery(name).show();
    }
};
IWB.aval=function(name) {
    var data={};
    jQuery("[name^='"+name+"']").each(function(i,v) {
        var $this=jQuery(this);
        var k=$this.attr('name');
        var v=$this.val();
        if($this.attr('type')=='checkbox') {
            v=IWB.check(k);
        } else if($this.attr('type')=='radio') {
            v=IWB.radio(k);
        }
        data[k]=v;
    });
    //console.log(data);
    return data;
};
IWB.formatColorOption=function(option) {
    if (!option.id) {
        return option.text;
    }

    var color=jQuery(option.element).css('background-color');
    var font=jQuery(option.element).css('color');
    var $option=jQuery('<div></div>')
        .html(option.text)
        .css('background-color', color)
        .css('color', font)
        .addClass('lbColorSelectItem');
    return $option;
};
IWB.hideShow=function(name) {
    var $source=IWB.jQuery(name);
    if ($source.attr('iwb-hideIfTrue') && $source.attr('iwb-hideShow')) {
        var $destination=jQuery('[name=' + $source.attr('iwb-hideShow') + ']');
        if ($destination.length == 0) {
            $destination=jQuery('#' + $source.attr('iwb-hideShow'));
        }
        if ($destination.length > 0) {
            var isChecked=$source.is(":checked");
            var hideIfTrue=($source.attr('iwb-hideIfTrue').toLowerCase() == 'true');

            if (isChecked) {
                if (hideIfTrue) {
                    $destination.hide();
                } else {
                    $destination.show();
                }
            } else {
                if (hideIfTrue) {
                    $destination.show();
                } else {
                    $destination.hide();
                }
            }
        }
    }
};
IWB.jQuery=function(name) {
    var $self=name;
    if(jQuery.type(name)=='string' || jQuery.type(name)=='array') {
        $self=false;
        var array=[];
        var names=[];
        switch (jQuery.type(name)) {
            case 'string':
                names=name.split('|');
                break;
            case 'array':
                names=name;
                break;
        }
        jQuery.each(names, function(i,v) {
            var selector='[name='+v+']';
            if(jQuery(selector).length>0) {
                array.push(selector);
            } else {
                selector='#'+v;
                if(jQuery(selector).length>0) {
                    array.push(selector);
                }
            }
        });
        if(array.length>0) {
            array=array.join(',');
            $self=jQuery(array);
        }
    }
    return $self;
}
IWB.attr=function($self, name, v) {
    $self=IWB.jQuery($self);
    var result=v;
    if($self.length>0) {
        result=$self.attr(name);
    }
    if ((typeof result === typeof undefined) || (result===false)) {
        result=v;
    }
    return result;
};
IWB.select2=function($self, options) {
    IWB.destroy($self);

    var $self=IWB.jQuery($self);
    var name=$self.attr('name');
    var multiple=IWB.attr($self, 'multiple', false);
    if (multiple!==false) {
        multiple=true;
    }

    hasSelection=false;
    if($self.html().indexOf('selected')>0) {
        //jQuery fails if you dont have any selected item return the first
        $self.find("option").each(function() {
            var $option=jQuery(this);
            if(IWB.attr($option, 'selected', '')!='') {
                hasSelection=true;
            }
        });
    }

    var help=IWB.attr($self, 'iwb-help', '');
    var ajax=IWB.attr($self, 'iwb-ajax', false);
    var parent=IWB.attr($self, 'iwb-master', '');
    var settings={};
    if(ajax===false || ajax==='') {
        settings={
            placeholder: help
            , width: '100%'
            , allowClear: true
        }
    } else {
        settings={
            placeholder: help
            , width: '100%'
            , allowClear: true
            , ajax: {
                type: 'POST'
                , dataType: 'json'
                , delay: 250
                , data: function (params) {
                    var result={
                        q: params.term
                        , page: params.page
                        , action: 'lb_ajax_ll'
                        , lb_action: ajax
                    };
                    if(parent!='') {
                        result['parentId']=IWB.val(parent);
                    }
                    return result;
                }
                , processResults: function (data, page) {
                    return {results: data};
                }
                , cache: true
            }
            , minimumInputLength: 2
        }
    }
    settings=jQuery.extend(settings, options);
    $self.select2(settings);
    $self.hide();
    if(!hasSelection) {
        $self.val(null).trigger('change');
    }
};
IWB.destroy=function($self) {
    var name=$self.attr('name');
    try {
        if($self.data('select2') != null) {
            //IWB.log('[%s] DESTROY SELECT2', name);
            $self.select2("destroy");
            $self.html("<option><option>");
            $self.val('').trigger('change');
        }
    } catch(ex) {}
};
IWB.inArray=function(v, array) {
    var result=false;
    if(!array || !jQuery.isArray(array) || array.length==0) {
        if(jQuery.type(array)=='string' && v==array) {
            result=true;
        }
    } else {
        for(i=0; i<array.length; i++) {
            c=array[i];
            if(v==c) {
                result=true;
                break;
            }
        }
    }
    return result;
}
IWB.changeShowOptions=function($self) {
    var selection=$self.val();
    var name=IWB.attr($self, 'name', '');
    var $options=$self.children('option');
    //console.log('NAME=[%s] OPTIONS=N.%s', name, $options.length);
    var toShow={};
    var toHide={};
    $options.each(function(i,v) {
        $option=jQuery(v);
        var text=IWB.attr($option, 'show', '');
        if(text!='') {
            text=text.split('|');
            var j=0;
            for(j=0; j<text.length; j++) {
                var show=text[j];
                var $show=jQuery('#'+show);
                if($show.length>0) {
                    var value=IWB.attr($option, 'value', '');
                    if(IWB.inArray(value, selection)) {
                        toShow[show]=show;
                    } else {
                        toHide[show]=show;
                    }
                } else {
                    console.log('changeShowOptions ID=[%s] NOT FOUND', show);
                }
            }
        }
    });

    jQuery.each(toShow, function(k,v) {
        delete toHide[k];
        var $w=jQuery('#'+k);
        $w.show();
    });
    jQuery.each(toHide, function(k,v) {
        var $w=jQuery('#'+k);
        $w.hide();
    });
}
IWB.setCookie=function(name,value,days) {
    name='IWB_'+name;
    if (days) {
        var date = new Date();
        date.setTime(date.getTime()+(days*24*60*60*1000));
        var expires = "; expires="+date.toGMTString();
    }
    else var expires = "";
    document.cookie = name+"="+value+expires+"; path=/";
}
IWB.getCookie=function(name) {
    name='IWB_'+name;
    var nameEQ = name + "=";
    var ca = document.cookie.split(';');
    for(var i=0;i < ca.length;i++) {
        var c = ca[i];
        while (c.charAt(0)==' ') c = c.substring(1,c.length);
        if (c.indexOf(nameEQ) == 0) return c.substring(nameEQ.length,c.length);
    }
    return null;
}
IWB.removeCookie=function(name) {
    createCookie(name,"",-1);
}
IWB.getDateCookie=function(name) {
    var result=IWB.getCookie(name);
    if(result!==null) {
        //console.log('getDateCookie RESULT=%s', result);
        result=moment(result).toDate();
        //console.log('getDateCookie MOMENT=%s', result);
    }
    return result;
}
IWB.setDateCookie=function(name, value, days) {
    if(value!==null && value!=='') {
        //console.log('setDateCookie RESULT=%s', value);
        value=moment(value).format();
        //console.log('setDateCookie MOMENT=%s', value);
    }
    IWB.setCookie(name, value, days);
}
IWB.formatTimer=function(time) {
    if(!(time+'').match(/^\d+$/)) {
        if(jQuery.type(time)=='string') {
            time=time.replace(' ', ':');
            time=time.replace('.', ':');
            time=time.replace('/', ':');
            time=time.split(':');

            days=0;
            hours=0;
            minutes=0;
            var length=time.length;
            secs=parseInt(time[length-1]);
            if(length>1) {
                minutes=parseInt(time[length-2]);
                if(length>2) {
                    hours=parseInt(time[length-3]);
                    if(length>3) {
                        days=parseInt(time[length-4]);
                    }
                }
            }
            days=(isNaN(days) ? 0 : days);
            hours=(isNaN(hours) ? 0 : hours);
            minutes=(isNaN(minutes) ? 0 : minutes);
            secs=(isNaN(secs) ? 0 : secs);
            time=days*86400+hours*3600+minutes*60+secs;
        } else {
            time=0;
        }
    } else {
        time=parseInt(time);
    }

    secs=time%60;
    time=(time-secs)/60;
    minutes=time%60;
    time=(time-minutes)/60;
    hours=time%24;
    days=(time-hours)/24;

    result=[];
    result.push(days);
    result.push((hours<10 ? '0' : '')+hours);
    result.push((minutes<10 ? '0' : '')+minutes);
    result.push((secs<10 ? '0' : '')+secs);
    result=result.join(':');
    return result;
}
IWB.parseTimer=function(time) {
    time=IWB.formatTimer(time);
    time=time.split(':');
    result=parseInt(time[0])*86400+parseInt(time[1])*3600+parseInt(time[2])*60+parseInt(time[3]);
    return result;
}
IWB.isTrue=function(value) {
    var result=false;
    if(value===true) {
        result=true;
    } else if(value===false) {
        result=false;
    } else {
        value=(value+'').toLowerCase();
        result=(value=='yes' || value=='true' || value=='1');
    }
    return result;
}
IWB.getFixedHeaders=function(value) {
    //if we can't find it with selectors, the loop through every element
    //in <body> - stopping once we find a position:fixed element and return that.
    var $headers=[];
    var $this;
    jQuery('html div').each(function() {
        $this=jQuery(this);
        if($this.css('position')=='fixed') {
            $headers.push($this);
        }
    });
    /*if(jQuery('#blackbox-web-debug').length>0) {
        $this=jQuery('#blackbox-web-debug');
        $headers.push($this);
    }*/
    return $headers;
}