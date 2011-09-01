Modernizr.addTest("osmac",function(){if(!navigator||!navigator.appVersion){return false}return(navigator.appVersion.indexOf("Mac")!=-1)
});var Orb={};if(window.console===undefined){window.console={};["error","log","warn","info","debug"].each(function(a){window.console[a]=function(){}
})}Orb.createNamespace=function(b,d){var c=b.split(".");var a=window;c.forEach(function(e){if(!a[e]){a[e]={}}a=a[e]})};Orb.getNamespacedObject=function(a){var c=window;
fullname_parts=a.split(".");var b=null;while(b=fullname_parts.shift()){if(c[b]===undefined){console.warn("Orb.getNamespacedObject(%s) is an invalid name",a);
return null}c=c[b]}return c};Orb.getUniqueId=function(a){if(!a){a=""}var b="";do{b=a+Orb.uuid()}while(document.getElementById(b));
return b};Orb.uuid=function(){return"orb_uuid_"+(++Orb.uuid_num)};Orb.uuid_num=0;Orb.getEl=function(a){if(typeOf(a)=="element"){return a
}return document.getElementById(a)};$el=function(a){return Orb.getEl(a)};Orb.sleep=function(a){var c=new Date().getTime();
for(var b=0;b<10000000;b++){if((new Date().getTime()-c)>a){break}}};Orb.mouseInElement=function(c,b,e){var f=e.offset();var d=e.outerWidth();
var a=e.outerHeight();if(c<f.left||c>f.left+d){return false}if(b<f.top||b>f.top+a){return false}return true};Orb.findHighestZindex=function(a){if(!a){a=$("body > *")
}var b=0;a.each(function(){var c=parseInt($(this).css("z-index"));if(c>b){b=c}});return b};Orb.escapeHtml=function(a){a=a||"";
return a.replace(/&/g,"&amp;").replace(/>/g,"&gt;").replace(/</g,"&lt;").replace(/"/g,"&quot;")};Orb.linkUrls=function(a){a=a||"";
return a.replace(/(https?:\/\/[^\s]+)/gi,'<a href="$1">$1</a>')};Orb.appendQueryData=function(c,b,a){var d=b;if(a!==undefined){d+="="+a
}if(c.indexOf("?")===-1){c+="?"+d}else{c+="&"+d}return c};Orb.strRepeat=function(c,b){var a=[];while(b-->0){a.push(c)}return a.join("")
};Orb.resourceLoader={batches:{},batchesCallback:{},loadScript:function(a,b){this.loadBatch([{type:"script",url:a}],b)},loadStylesheet:function(a,b){this.loadBatch([{type:"css",url:a}],b)
},loadBatch:function(f,h){var b=Orb.uuid();var d=$("head");this.batches[b]=[];this.batchesCallback[b]=h;var c=null;while(c=f.shift()){var g=Orb.uuid();
var e=function(){Orb.resourceLoader._resourceDoneLoading(b,g)};if(c.type=="script"){var a=document.createElement("script");
a.type="text/javascript";a.src=c.url}else{if(c.type=="stylesheet"){var a=document.createElement("link");a.rel="stylesheet";
a.type="text/css";a.href=c.url;a.media="screen";if(c.media!=undefined){a.media=c.media}}}a.onreadystatechange=function(){if(this.readyState=="complete"){e()
}};a.onload=e;this.batches[b].push(g)}},_resourceDoneLoading:function(a,c){if(this.batches[a]==undefined){return false}this.batches[a].erase(c);
if(!this.batches[a].length){var b=this.batchesCallback[a];delete this.batches[a];delete this.batchesCallback[a];b()}}};Orb.DesktopNotify=(function(){if(window.webkitNotifications){var a=true
}else{var a=false}this.isSupported=function(){return a};if(a&&window.webkitNotifications.checkPermission()!=0){b=true}var b=this.hasPermission=function(){return(a&&window.webkitNotifications.checkPermission()!=0)
};this.askPermission=function(c){if(!a||b()){return}c=c||function(){};window.webkitNotifications.requestPermission(c)};this.show=function(d){if(!a||!b()){return
}d=$.extend({},d,{iconUrl:null,title:"",content:"",click:null,show:null,close:null,error:null});var c=window.webkitNotifications.createNotification(d.iconUrl,d.title,d.content);
if(d.click){c.onclick=d.click}if(d.show){c.onshow=d.show}if(d.close){c.onclose=d.close}if(d.error){c.onerror=d.error}return c
};this.showUrl=function(d){if(typeof d=="string"){d={url:d}}var c=window.webkitNotifications.createHTMLNotification(d.url);
if(d.click){c.onclick=d.click}if(d.show){c.onshow=d.show}if(d.close){c.onclose=d.close}if(d.error){c.onerror=d.error}return c
};return this})();$.fn.single_double_click=function(a,b,c){c=c||250;return this.each(function(){var e=0;var d=this;if($.browser.msie){$(this).bind("dblclick",function(f){e=2;
b.call(d,f)});$(this).bind("click",function(f){setTimeout(function(){if(e!=2){a.call(d,f)}e=0},c)})}else{$(this).bind("click",function(f){e++;
if(e==1){setTimeout(function(){if(e==1){a.call(d,f)}else{b.call(d,f)}e=0},c)}})}})};jQuery.fn.extend({insertAtCaret:function(a){return this.each(function(d){if(document.selection){this.focus();
sel=document.selection.createRange();sel.text=a;this.focus()}else{if(this.selectionStart||this.selectionStart=="0"){var c=this.selectionStart;
var b=this.selectionEnd;var e=this.scrollTop;this.value=this.value.substring(0,c)+a+this.value.substring(b,this.value.length);
this.focus();this.selectionStart=c+a.length;this.selectionEnd=c+a.length;this.scrollTop=e}else{this.value+=a;this.focus()
}}})}});(function($,h,c){var a=$([]),e=$.resize=$.extend($.resize,{}),i,k="setTimeout",j="resize",d=j+"-special-event",b="delay",f="throttleWindow";
e[b]=325;e[f]=false;$.event.special[j]={setup:function(){if(!e[f]&&this[k]){return false}var l=$(this);a=a.add(l);$.data(this,d,{w:l.width(),h:l.height()});
if(a.length===1){g()}},teardown:function(){if(!e[f]&&this[k]){return false}var l=$(this);a=a.not(l);l.removeData(d);if(!a.length){clearTimeout(i)
}},add:function(l){if(!e[f]&&this[k]){return false}var n;function m(s,o,p){var q=$(this),r=$.data(this,d);r.w=o!==c?o:q.width();
r.h=p!==c?p:q.height();n.apply(this,arguments)}if($.isFunction(l)){n=l;return m}else{n=l.handler;l.handler=m}}};function g(){i=h[k](function(){a.each(function(){var n=$(this),m=n.width(),l=n.height(),o=$.data(this,d);
if(m!==o.w||l!==o.h){n.trigger(j,[o.w=m,o.h=l])}});g()},e[b])}})(jQuery,this);function strtotime(g,b){var e,f,k,j="",c="";
j=g;j=j.replace(/\s{2,}|^\s|\s$/g," ");j=j.replace(/[\t\r\n]/g,"");if(j=="now"){return(new Date()).getTime()/1000}else{if(!isNaN(c=Date.parse(j))){return(c/1000)
}else{if(b){b=new Date(b*1000)}else{b=new Date()}}}j=j.toLowerCase();var d={day:{sun:0,mon:1,tue:2,wed:3,thu:4,fri:5,sat:6},mon:{jan:0,feb:1,mar:2,apr:3,may:4,jun:5,jul:6,aug:7,sep:8,oct:9,nov:10,dec:11}};
var a=function(i){var o=(i[2]&&i[2]=="ago");var n=(n=i[0]=="last"?-1:1)*(o?-1:1);switch(i[0]){case"last":case"next":switch(i[1].substring(0,3)){case"yea":b.setFullYear(b.getFullYear()+n);
break;case"mon":b.setMonth(b.getMonth()+n);break;case"wee":b.setDate(b.getDate()+(n*7));break;case"day":b.setDate(b.getDate()+n);
break;case"hou":b.setHours(b.getHours()+n);break;case"min":b.setMinutes(b.getMinutes()+n);break;case"sec":b.setSeconds(b.getSeconds()+n);
break;default:var l;if(typeof(l=d.day[i[1].substring(0,3)])!="undefined"){var p=l-b.getDay();if(p==0){p=7*n}else{if(p>0){if(i[0]=="last"){p-=7
}}else{if(i[0]=="next"){p+=7}}}b.setDate(b.getDate()+p)}}break;default:if(/\d+/.test(i[0])){n*=parseInt(i[0],10);switch(i[1].substring(0,3)){case"yea":b.setFullYear(b.getFullYear()+n);
break;case"mon":b.setMonth(b.getMonth()+n);break;case"wee":b.setDate(b.getDate()+(n*7));break;case"day":b.setDate(b.getDate()+n);
break;case"hou":b.setHours(b.getHours()+n);break;case"min":b.setMinutes(b.getMinutes()+n);break;case"sec":b.setSeconds(b.getSeconds()+n);
break}}else{return false}break}return true};f=j.match(/^(\d{2,4}-\d{2}-\d{2})(?:\s(\d{1,2}:\d{2}(:\d{2})?)?(?:\.(\d+))?)?$/);
if(f!=null){if(!f[2]){f[2]="00:00:00"}else{if(!f[3]){f[2]+=":00"}}k=f[1].split(/-/g);for(e in d.mon){if(d.mon[e]==k[1]-1){k[1]=e
}}k[0]=parseInt(k[0],10);k[0]=(k[0]>=0&&k[0]<=69)?"20"+(k[0]<10?"0"+k[0]:k[0]+""):(k[0]>=70&&k[0]<=99)?"19"+k[0]:k[0]+"";
return parseInt(this.strtotime(k[2]+" "+k[1]+" "+k[0]+" "+f[2])+(f[4]?f[4]/1000:""),10)}var h="([+-]?\\d+\\s(years?|months?|weeks?|days?|hours?|min|minutes?|sec|seconds?|sun\\.?|sunday|mon\\.?|monday|tue\\.?|tuesday|wed\\.?|wednesday|thu\\.?|thursday|fri\\.?|friday|sat\\.?|saturday)|(last|next)\\s(years?|months?|weeks?|days?|hours?|min|minutes?|sec|seconds?|sun\\.?|sunday|mon\\.?|monday|tue\\.?|tuesday|wed\\.?|wednesday|thu\\.?|thursday|fri\\.?|friday|sat\\.?|saturday))(\\sago)?";
f=j.match(new RegExp(h,"gi"));if(f==null){return false}for(e=0;e<f.length;e++){if(!a(f[e].split(" "))){return false}}return(b.getTime()/1000)
}if(!Orb){var Orb={}}Orb.Class=function(k){if(k.DisableParentCall){function d(i){return false}}else{var f=(function(){xyz
}).toString().indexOf("xyz")!=-1;function d(i){if(!f){return true}return i.toString().indexOf("this.parent(")!=-1}}delete k.DisableParentCall;
if(!k.Extends){k.Extends=function(){}}var m=k.Extends;var a=m.prototype;m.__is_prototyping=true;var h=new m;delete m.__is_prototyping;
delete k.Extends;if(k.Implements){for(var g=0,e=k.Implements.length;g!=e;++g){var o=k.Implements[g];for(var c in o){if(!o.prototype||o.prototype.hasOwnProperty(c)){if(typeof o[c]=="function"){h[c]=o[c]
}}}}}delete k.Implements;var b=null;if(k.ClassVars){b=k.ClassVars;delete k.ClassVars}for(var c in k){if(k.prototype&&!k.prototype.hasOwnProperty(c)){continue
}var l=k[c];if(typeof l=="function"){if(d(l)){l=(function(n,i){return function(){this.parent=a[i];return n.apply(this,arguments)
}})(l,c)}h[c]=l}else{throw"Error: Non-function property in class. Set properties in an initializer method, never in the class body!"
}}var j=function(){if(j.__is_prototyping){return this}if(this.initialize){this.initialize.apply(this,arguments)}this.CLASS=j;
this.SUPER=m;return this};if(b){for(c in b){if(!b.prototype||b.prototype.hasOwnProperty(c)){j[c]=b[c]}}}j.prototype=h;j.constructor=j;
return j};Orb.createNamespace("Orb.Util");Orb.Util.Options={setOptions:function(b){var a=$.extend(true,{},this.options||{},b);
if(this.addEvent){for(var c in a){if(c=="defaultEventContext"){this.setDefaultEventContext(a[c]);delete a[c]}else{if(typeof a[c]=="function"&&(/^on[A-Z]/).test(c)){this.addEvent(c,a[c]);
delete a[c]}}}}this.options=a;return this},getOption:function(b,a){if(typeof this.options[b]===undefined){return a}return this.options[b]
}};Orb.createNamespace("Orb.Util");Orb.Util.Events={__initEventsObj:function(){if(!this.__events){this.__events={}}if(!this.__events_default_context){this.__events_default_context=null
}},setDefaultEventContext:function(a){this.__events_default_context=a},normalizeEventName:function(a){return a.toLowerCase().replace(/^on/,"")
},addEvent:function(d,c,b,a){d=this.normalizeEventName(d);this.__initEventsObj();this.__events[d]=(this.__events[d]||[]).include(c);
if(b){c.context=b}if(a){c.internal=true}return this},addEvents:function(a){for(var b in a){this.addEvent(b,a[b])}return this
},fireEvent:function(d,b,a){d=this.normalizeEventName(d);this.__initEventsObj();var c=this.__events[d];if(!c){return this
}b=Array.from(b);c.each(function(e){if(a){e.delay(a,e.context||this.__events_default_context||this,b)}else{e.apply(e.context||this.__events_default_context||this,b)
}},this);return this},removeEvent:function(d,c){d=this.normalizeEventName(d);this.__initEventsObj();var b=this.__events[d];
if(b&&!c.internal){var a=b.indexOf(c);if(a!=-1){delete b[a]}}return this},removeEvents:function(c){var d;if(typeOf(c)=="object"){for(d in c){this.removeEvent(d,c[d])
}return this}this.__initEventsObj();for(d in this.__events){if(c&&c!=d){continue}var b=this.__events[d];for(var a=b.length;
a--;){if(a in b){this.removeEvent(d,b[a])}}}return this}};Orb.createNamespace("Orb.Util");Orb.Util.EventObj=new Orb.Class({Implements:[Orb.Util.Events,Orb.Util.Options],initialize:function(a){this.setOptions(a)
}});Orb.createNamespace("Orb.Util");Orb.Util.TimeAgo={_watchEls:[],_watchTimer:null,refreshPeriod:60000,phrases:{sec_less:"less than a second",sec:"1 second",secs:"{0} seconds",min:"1 minute",mins:"{0} minutes",hour:"1 hour",hours:"{0} hours",day:"1 day",days:"{0} days",week:"1 week",weeks:"{0} weeks",month:"1 month",months:"{0} months",year:"1 year",years:"{0} years",ago:"ago"},get:function(a){return this.getForMs(this.getDateDiff(a))
},applyToElements:function(b){var a=this;b.each(function(c){a._refreshElements([c]);a._watchEls.push(c)});if(this._watchTimer===null){window.setInterval(this._refreshElements.bind(this),this.refreshPeriod)
}},applyToJquery:function(a){this.applyToElements(a.toArray())},_refreshElements:function(b){if(!b){b=this._watchEls}var a=this;
b.each(function(e){if(!e.parentNode){return}e=$(e);if(!e.data("timeago")){var h=e.get(0).tagName.toLowerCase()=="time";var d=h&&e.attr("datetime")?e.attr("datetime"):e.attr("title");
if(!d){return}var c=d.replace(/\.\d\d\d+/,"");c=c.replace(/-/,"/").replace(/-/,"/");c=c.replace(/T/," ").replace(/Z/," UTC");
c=c.replace(/([\+-]\d\d)\:?(\d\d)/," $1$2");e.data("timeago",{datetime:new Date(c)});var g=$.trim(e.text());if(!e.data("timeago-no-ago")){g+=" "+a.phrases.ago
}if(g.length>0){e.attr("title",g)}}var f=e.data("timeago");if(!isNaN(f.datetime)){var g=a.get(f.datetime);if(!e.data("timeago-no-ago")){g+=" "+a.phrases.ago
}e.text(g)}})},getRelativeInfo:function(b){var d=0,e=0,a=0,f=0,c=0;d=parseInt(b/1000);c=parseInt(d/29030400);d-=c*29030400;
f=parseInt(d/86400);d-=f*86400;a=parseInt(d/3600);d-=a*3600;e=parseInt(d/60);d-=e*60;return{secs:d,mins:e,hours:a,days:f,years:c}
},getForMs:function(b){var d=this.getRelativeInfo(b);var h=parseInt(b/1000);if(h<=120){return this.getPhraseFor("sec",d.secs).replace("{0}",d.secs)
}else{if(h<=1200){return this.getPhraseFor("min",d.mins).replace("{0}",d.mins)}else{if(h<=86400){var j;if(d.mins<=15){j=""
}else{if(d.mins<=30){j="1/4"}else{if(d.mins<=45){j="1/2"}else{if(d.mins<=60){j="3/4"}}}}var f=d.hours;var i=d.hours+"";if(j!==""){f+=1;
i+=" "+j}return this.getPhraseFor("hour",f).replace("{0}",i)}else{if(h<=259200){var e=this.getPhraseFor("day",d.days).replace("{0}",d.days);
if(d.hours>0){e+=" "+this.getPhraseFor("hour",d.hours).replace("{0}",d.hours)}return e}else{if(h<=2419200){return this.getPhraseFor("day",d.days).replace("{0}",d.days)
}else{if(h<=7257600){var a=parseInt(d.days/7);return this.getPhraseFor("week",a).replace("{0}",a)}else{if(h<=29030400){var c=parseInt(d.days/30);
return this.getPhraseFor("month",d.months).replace("{0}",c)}else{if(h<=145152000){var g=this.getPhraseFor("year",d.years).replace("{0}",d.years);
if(d.months>0){g+=" "+this.getPhraseFor("month",d.months).replace("{0}",d.months)}return g}else{return this.getPhraseFor("year",d.years).replace("{0}",d.years)
}}}}}}}}},getDateDiff:function(a,b){var c=(new Date().getTime()-a.getTime());if(b){c/=1000}return c},getPhraseFor:function(c,b){if(c=="sec"&&b<=0){return this.phrases.sec_less
}var a=c;if(b!=1){a+="s"}return this.phrases[a]}};if(jQuery){jQuery.fn.timeago=function(){Orb.Util.TimeAgo.applyToJquery(this);
return this}}Orb.createNamespace("Orb.Compat.WebForms");Orb.Compat.WebForms.isPlaceholderSupported=function(){this.isSupported=null;
if(this.isSupported===null){this.isSupported=("placeholder" in document.createElement(input.tagName))}return this.isSupported
};Orb.Compat.WebForms.placeholder=function(a){if(!a){return null}input_col=$(a);if(!input_col.length){return null}input_col.each(function(){var b=$(this);
if(b.placeholder&&this.isPlaceholderSupported()){return}var c=b.attr("placeholder");if(!c||!c.length){return}if(b.is(".has-placeholder")){return
}b.addClass("has-placeholder");if(b.val()===""||b.val()==c){b.val(c);b.addClass("placeholder-visible")}b.focus(function(){if(b.is(".placeholder-visible")){b.val("");
b.removeClass("placeholder-visible")}});b.blur(function(){if(b.val()===""){b.addClass("placeholder-visible");b.val(c)}else{b.removeClass("placeholder-visible")
}});if(b.get(0).form){$(b.get(0).form).submit(function(){if(b.is(".placeholder-visible")){b.val("")}})}})};Orb.createNamespace("DeskPRO");
DeskPRO.MessageBroker=new Orb.Class({initialize:function(){this.messageTransformers={};this.messageListeners={};this.tagged={}
},addForwarder:function(b,a){this.addMessageListener(b,function(d,c){a.sendMessage(c,d)})},sendMessage:function(a,d){d=this.transformMessage(a,d);
if(this.messageListeners[a]!==undefined){this.messageListeners[a].each(function(e){e(d,a)})}var b=a.split(".");var c=null;
while(b.pop()){c=b.join(".")+".*";if(this.messageListeners[c]!==undefined){this.messageListeners[c].each(function(e){e(d,a)
})}}},transformMessage:function(a,d){if(this.messageTransformers[a]!==undefined){this.messageTransformers[a].each(function(e){d=e(d,a)
})}var b=a.split(".");var c=null;while(b.pop()){c=b.join(".")+".*";if(this.messageTransformers[c]!==undefined){this.messageTransformers[c].each(function(e){d=e(d,a)
})}}return d},addMessageTransformer:function(a,b){if(this.messageTransformers[a]===undefined){this.messageTransformers[a]=[]
}this.messageTransformers[a].push(b)},addMessageListener:function(b,c,a){if(this.messageListeners[b]===undefined){this.messageListeners[b]=[]
}this.messageListeners[b].push(c);if(a){if(!this.tagged[a]){this.tagged[a]=[]}this.tagged[a].push([b,c])}},removeMessageListener:function(b,c){if(this.messageListeners[b]===undefined){return
}var a=this.messageListeners[b].indexOf(c);if(a!=-1){this.messageListeners[b].splice(a,1)}},removeTaggedListeners:function(a){if(!this.tagged[a]){return
}Array.each(this.tagged[a],function(b){this.removeMessageListener(b[0],b[1])},this)}});Orb.createNamespace("DeskPRO.AjaxPoller");
DeskPRO.AjaxPoller.Poller=new Orb.Class({Implements:[Orb.Util.Events,Orb.Util.Options],initialize:function(a){this.dataTransformers=[];
this.filterdData=[];this.messageBroker=null;this.maxDelayTimers=[];this.autoSendTimeout=null;this.options={ajaxUrl:null,interval:6000,alwaysRequest:false,ajaxType:"POST"};
this.disabled=false;this.setOptions(a);this.autoSendTimeout=this.send.delay(this.options.interval,this)},addDataTransformer:function(a,b){this.dataTransformers.push(b)
},transformData:function(b,e,a){var c=b.split(".");var d=null;while(c.pop()){d=c.join(".")+".*";if(this.dataTransformers[d]!==undefined){this.dataTransformers[d].each(function(f){e=f(e,a,b)
})}}return e},addData:function(c,b,a){b=b||"default";a=a||{};if(a.addedTime===undefined){a.addedTime=new Date()}if(a.maxDelay){(function(){this.send()
}).delay(a.maxDelay,this)}this.filterdData.push([b,c,a])},send:function(){this._clearDelays();if(!this.options.alwaysRequest&&!this.filterdData.length){this.autoSendTimeout=this.send.delay(this.options.interval,this);
return}var d=new Date();var c=[];var h=[];var f=this.filterdData;this.filterdData=[];var e=null;while(e=f.shift()){var g=e[0];
var b=item_orig_data=e[1];var a=e[2];if(a.minDelay&&!(a.minDelayAfterOne&&!a.sentCount)){if(a.minDelay>(d.getTime()-a.addedTime.getTime())){this.addData(item_orig_data,g,a);
continue}}if(typeOf(b)=="function"){b=b(g,{},a)}b=this.transformData(g,b,a);if(!b){continue}if(typeOf(b)=="array"){c.append(b)
}else{Object.each(b,function(j,i){c.push({name:i,value:j})})}h.push([item_orig_data,g,a])}if(!this.options.alwaysRequest&&!h.length){this._handleAjaxSuccess({},h);
return}$.ajax({cache:false,type:this.options.ajaxType,url:this.options.ajaxUrl,context:this,data:c,dataType:"json",dpIsPolling:true,success:function(i){this._handleAjaxSuccess(i,h)
},error:function(j,k,i){this._handleAjaxError(h,j,k,i)}})},_handleAjaxSuccess:function(a,b){this.resetSentItems(b);this.fireEvent("ajaxSuccess",a);
this.autoSendTimeout=this.send.delay(this.options.interval,this)},resetSentItems:function(e){var c=null;while(c=e.shift()){var d=c[0];
var b=c[1];var a=c[2];if(a.recurring){a.lastSent=new Date();if(a.sentCount===undefined){a.sentCount=0}a.sentCount++;delete a.addedTime;
this.addData(d,b,a)}}},_handleAjaxError:function(d,b,c,a){this.resetSentItems(d);console.error("Polling Error %s for %o",c,b);
this.fireEvent("ajaxError",[b,c,a]);this.autoSendTimeout=this.send.delay(this.options.interval,this)},_clearDelays:function(){this.autoSendTimeout=window.clearTimeout(this.autoSendTimeout);
this.autoSendTimeout=null;var a=null;while(a=this.maxDelayTimers.pop()){window.clearTimeout(a)}}});Orb.createNamespace("DeskPRO.AjaxPoller");
DeskPRO.AjaxPoller.MessagePoller=new Orb.Class({Extends:DeskPRO.AjaxPoller.Poller,initialize:function(b,a){this.parent(a);
this.messageBroker=b;this.addEvent("ajaxSuccess",this._sendMessages.bind(this),true)},getMessageBroker:function(){return this.messageBroker
},_sendMessages:function(b){if(b.messages===undefined||typeOf(b.messages)!="array"){return}var a=null;while(a=b.messages.shift()){this.messageBroker.sendMessage(a[0],a[1])
}}});Orb.createNamespace("DeskPRO.MessageChanneler");DeskPRO.MessageChanneler.AbstractChanneler=new Orb.Class({Implements:[Orb.Util.Options],initialize:function(b,a){this.channels=[];
this.options={};this.messageBroker=b;if(a){this.setOptions(a)}this._init()},_init:function(){},subscribeChannel:function(a,b){},_doneSubscribeChannels:function(a){Array.each(a,function(b){this.channels.include(b)
},this)},unsubscribeChannel:function(a){},_doneSubscribeChannels:function(a){Array.each(a,function(b){this.channels.erase(b)
},this)},sendMessage:function(b,a){if(DeskPRO_Window&&DeskPRO_Window.getDebug("logClientMessages")){console.log("channel(%s): %o",b,a)
}this.messageBroker.sendMessage(b,a)}});Orb.createNamespace("DeskPRO.MessageChanneler");DeskPRO.MessageChanneler.AjaxChanneler=new Class({Extends:DeskPRO.MessageChanneler.AbstractChanneler,_init:function(){this._add_subs=[];
this._add_subs_timeout=null;this._del_subs=[];this._del_subs_timeout=null;this.lastMessageId=-1;this.poller=new DeskPRO.AjaxPoller.Poller({ajaxUrl:this.options.ajaxMessagesUrl,interval:5000,ajaxType:"GET"});
this.poller.addData((function(){if(!this.lastMessageId){return null}return{since:this.lastMessageId}}).bind(this),"since",{recurring:true});
this.poller.addData({is_initial_poll:1},"is_initial_poll");this.poller.addEvent("ajaxSuccess",this.handleMessageAjax.bind(this));
if(this.options.lastMessageId){this.lastMessageId=this.options.lastMessageId}},handleMessageAjax:function(a){if(a.messages){Array.each(a.messages,function(b){if(b[0]<=this.lastMessageId&&(!b[3]||!b[3]["offline_messsage"])){return
}this.lastMessageId=b[0];this.sendMessage(b[1],b[2])},this)}if(a.last_id&&a.last_id>this.lastMessageId){this.lastMessageId=a.last_id
}},subscribeChannel:function(a,b){this._add_subs.include(a);if(this._add_subs_timeout){window.clearTimeout(this._add_subs_timeout)
}this._add_subs_timeout=this._sendSubscribeChannels.delay(300,this);if(b){this.messageBroker.addMessageListener(a,b)}},_sendSubscribeChannels:function(){var a=[];
Array.each(this._add_subs,function(b){a.push({name:"channels[]",value:b})});this._add_subs=[];$.ajax({url:this.options.ajaxSubscribeUrl,type:"POST",data:a,dataType:"json",context:this,success:function(b){this._doneSubscribeChannels(b.subscribed_channels)
}})},unsubscribeChannel:function(a){this._del_subs.include(a);if(this._del_subs_timeout){window.clearTimeout(this._del_subs_timeout)
}this._del_subs_timeout=this._sendUnsubscribeChannels.delay(300,this)},_sendUnsubscribeChannels:function(){var a=[];Array.each(this._add_subs,function(b){a.push({name:"channels[]",value:b})
});this._add_subs=[];$.ajax({url:this.options.ajaxUnsubscribeUrl,type:"POST",data:a,dataType:"json",context:this,success:function(b){this._doneUnsubscribeChannels(b.unsubscribed_channels)
}})}});