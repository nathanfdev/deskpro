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
return a.replace(/(https?:\/\/[^\s]+)/gi,'<a href="$1">$1</a>')};Orb.appendQueryData=function(c,b,a){var d=b;if(a!==undefined){d+="="+encodeURI(a)
}if(c.indexOf("?")===-1){c+="?"+d}else{c+="&"+d}return c};Orb.strRepeat=function(c,b){var a=[];while(b-->0){a.push(c)}return a.join("")
};Orb.arrayChunk=function(e,d){var a=[],b=[],c;for(c=0;c<e.length;c++){if(b.length==d){a.push(b);b=[]}if(b.length<d){b.push(e[c])
}}if(b.length){a.push(b)}return a};Orb.resourceLoader={batches:{},batchesCallback:{},loadScript:function(a,b){this.loadBatch([{type:"script",url:a}],b)
},loadStylesheet:function(a,b){this.loadBatch([{type:"css",url:a}],b)},loadBatch:function(f,h){var b=Orb.uuid();var d=$("head");
this.batches[b]=[];this.batchesCallback[b]=h;var c=null;while(c=f.shift()){var g=Orb.uuid();var e=function(){Orb.resourceLoader._resourceDoneLoading(b,g)
};if(c.type=="script"){var a=document.createElement("script");a.type="text/javascript";a.src=c.url}else{if(c.type=="stylesheet"){var a=document.createElement("link");
a.rel="stylesheet";a.type="text/css";a.href=c.url;a.media="screen";if(c.media!=undefined){a.media=c.media}}}a.onreadystatechange=function(){if(this.readyState=="complete"){e()
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
e[b]=325;e[f]=false;$.event.special[j]={setup:function(){if(!e[f]&&this[k]){return false}var m=$(this);a=a.add(m);$.data(this,d,{w:m.width(),h:m.height()});
if(a.length===1){g()}},teardown:function(){if(!e[f]&&this[k]){return false}var m=$(this);a=a.not(m);m.removeData(d);if(!a.length){clearTimeout(i)
}},add:function(m){if(!e[f]&&this[k]){return false}var o;function n(t,p,q){var r=$(this),s=$.data(this,d);s.w=p!==c?p:r.width();
s.h=q!==c?q:r.height();o.apply(this,arguments)}if($.isFunction(m)){o=m;return n}else{o=m.handler;m.handler=n}}};function g(){i=h[k](function(){a.each(function(){var o=$(this),n=o.width(),m=o.height(),p=$.data(this,d);
if(n!==p.w||m!==p.h){o.trigger(j,[p.w=n,p.h=m])}});g()},e[b])}})(jQuery,this);function strtotime(g,b){var e,f,k,j="",c="";
j=g;j=j.replace(/\s{2,}|^\s|\s$/g," ");j=j.replace(/[\t\r\n]/g,"");if(j=="now"){return(new Date()).getTime()/1000}else{if(!isNaN(c=Date.parse(j))){return(c/1000)
}else{if(b){b=new Date(b*1000)}else{b=new Date()}}}j=j.toLowerCase();var d={day:{sun:0,mon:1,tue:2,wed:3,thu:4,fri:5,sat:6},mon:{jan:0,feb:1,mar:2,apr:3,may:4,jun:5,jul:6,aug:7,sep:8,oct:9,nov:10,dec:11}};
var a=function(i){var p=(i[2]&&i[2]=="ago");var o=(o=i[0]=="last"?-1:1)*(p?-1:1);switch(i[0]){case"last":case"next":switch(i[1].substring(0,3)){case"yea":b.setFullYear(b.getFullYear()+o);
break;case"mon":b.setMonth(b.getMonth()+o);break;case"wee":b.setDate(b.getDate()+(o*7));break;case"day":b.setDate(b.getDate()+o);
break;case"hou":b.setHours(b.getHours()+o);break;case"min":b.setMinutes(b.getMinutes()+o);break;case"sec":b.setSeconds(b.getSeconds()+o);
break;default:var n;if(typeof(n=d.day[i[1].substring(0,3)])!="undefined"){var q=n-b.getDay();if(q==0){q=7*o}else{if(q>0){if(i[0]=="last"){q-=7
}}else{if(i[0]=="next"){q+=7}}}b.setDate(b.getDate()+q)}}break;default:if(/\d+/.test(i[0])){o*=parseInt(i[0],10);switch(i[1].substring(0,3)){case"yea":b.setFullYear(b.getFullYear()+o);
break;case"mon":b.setMonth(b.getMonth()+o);break;case"wee":b.setDate(b.getDate()+(o*7));break;case"day":b.setDate(b.getDate()+o);
break;case"hou":b.setHours(b.getHours()+o);break;case"min":b.setMinutes(b.getMinutes()+o);break;case"sec":b.setSeconds(b.getSeconds()+o);
break}}else{return false}break}return true};f=j.match(/^(\d{2,4}-\d{2}-\d{2})(?:\s(\d{1,2}:\d{2}(:\d{2})?)?(?:\.(\d+))?)?$/);
if(f!=null){if(!f[2]){f[2]="00:00:00"}else{if(!f[3]){f[2]+=":00"}}k=f[1].split(/-/g);for(e in d.mon){if(d.mon[e]==k[1]-1){k[1]=e
}}k[0]=parseInt(k[0],10);k[0]=(k[0]>=0&&k[0]<=69)?"20"+(k[0]<10?"0"+k[0]:k[0]+""):(k[0]>=70&&k[0]<=99)?"19"+k[0]:k[0]+"";
return parseInt(this.strtotime(k[2]+" "+k[1]+" "+k[0]+" "+f[2])+(f[4]?f[4]/1000:""),10)}var h="([+-]?\\d+\\s(years?|months?|weeks?|days?|hours?|min|minutes?|sec|seconds?|sun\\.?|sunday|mon\\.?|monday|tue\\.?|tuesday|wed\\.?|wednesday|thu\\.?|thursday|fri\\.?|friday|sat\\.?|saturday)|(last|next)\\s(years?|months?|weeks?|days?|hours?|min|minutes?|sec|seconds?|sun\\.?|sunday|mon\\.?|monday|tue\\.?|tuesday|wed\\.?|wednesday|thu\\.?|thursday|fri\\.?|friday|sat\\.?|saturday))(\\sago)?";
f=j.match(new RegExp(h,"gi"));if(f==null){return false}for(e=0;e<f.length;e++){if(!a(f[e].split(" "))){return false}}return(b.getTime()/1000)
}if(!Orb){var Orb={}}Orb.Class_Instances={};Orb.Class_GC_Callbacks=[];Orb.Class_GC_PrintDebug=false;Orb.Class_GC_Start=function(a){};
Orb.Class_GC_Cycle=function(){var b,a;if(this.isRunning){return}this.isRunning=true;if(Orb.Class_GC_PrintDebug){console.log("[GC] Cycle")
}Object.each(Orb.Class_Instances,function(c){if(c.OBJ_DESTROYED){Orb.Class_GC_Cycle_Class(c)}});this.isRunning=false};Orb.Class_GC_Cycle_Class=function(b){var c=b.OBJ_ID,a=null;
if(b.OBJ_DESTROYED&&!b.OBJ_DONE_DESTROYED){b.OBJ_DONE_DESTROYED=true;if(Orb.Class_GC_PrintDebug){console.log("[GC] Destroyed %s: %o",c,b)
}for(a=0,l=Orb.Class_GC_Callbacks.length;a<l;a++){Orb.Class_GC_Callbacks[a](b,c)}delete Orb.Class_Instances[c]}};Orb.Class=function(k){if(k.DisableParentCall){function d(i){return false
}}else{var f=(function(){xyz}).toString().indexOf("xyz")!=-1;function d(i){if(!f){return true}return i.toString().indexOf("this.parent(")!=-1
}}delete k.DisableParentCall;if(!k.Extends){k.Extends=function(){}}var o=k.Extends;var a=o.prototype;o.__is_prototyping=true;
var h=new o;delete o.__is_prototyping;delete k.Extends;if(k.Implements){for(var g=0,e=k.Implements.length;g!=e;++g){var p=k.Implements[g];
for(var c in p){if(!p.prototype||p.prototype.hasOwnProperty(c)){if(typeof p[c]=="function"){h[c]=p[c]}}}}}delete k.Implements;
var b=null;if(k.ClassVars){b=k.ClassVars;delete k.ClassVars}if(k.destroy){k.__destroy=k.destroy;k.destroy=(function(i){return function(){if(!this.OBJ_DESTROYED){i.apply(this)
}this.OBJ_DESTROYED=true;Orb.Class_GC_Cycle_Class(this)}})(k.__destroy)}else{k.destroy=(function(){return function(){this.OBJ_DESTROYED=true;
Orb.Class_GC_Cycle_Class(this)}})()}for(var c in k){if(k.prototype&&!k.prototype.hasOwnProperty(c)){continue}var m=k[c];if(typeof m=="function"){if(c!="destroy"&&d(m)){m=(function(n,i){return function(){this.parent=a[i];
return n.apply(this,arguments)}})(m,c)}h[c]=m}else{console.error("[Orb.Class] Non-function property in class: %o extends %o",this,k);
throw"Error: Non-function property in class";return}}var j;j=function(){if(j.__is_prototyping){return this}this.CLASS=j;this.SUPER=o;
this.OBJ_ID=Orb.uuid();this.OBJ_DESTROYED=false;Orb.Class_Instances[this.OBJ_ID]=this;if(this.initialize){this.initialize.apply(this,arguments)
}return this};if(b){for(c in b){if(!b.prototype||b.prototype.hasOwnProperty(c)){j[c]=b[c]}}}j.prototype=h;j.constructor=j;
delete k;delete b;delete c;delete m;delete d;return j};Orb.createNamespace("Orb.Util");Orb.Util.Options={setOptions:function(b){var a=$.extend(true,{},this.options||{},b);
if(this.addEvent){for(var c in a){if(c=="defaultEventContext"){this.setDefaultEventContext(a[c]);delete a[c]}else{if(typeof a[c]=="function"&&(/^on[A-Z]/).test(c)){this.addEvent(c,a[c]);
delete a[c]}}}}this.options=a;return this},getOption:function(b,a){if(typeof this.options[b]===undefined){return a}return this.options[b]
}};Orb.createNamespace("Orb.Util");Orb.Util.Events={__initEventsObj:function(){if(!this.__events){this.__events={};this.__events_tagged={};
this.__preventCleanupTagged=false}},setDefaultEventContext:function(a){this.__events_default_context=a},normalizeEventName:function(a){return a.toLowerCase().replace(/^on/,"")
},addEvent:function(d,c,b,a){this.__initEventsObj();d=this.normalizeEventName(d);if(!b){b=this.__events_default_context}if(!this.__events[d]){this.__events[d]=[]
}this.__events[d].push([c,b]);if(b&&b.OBJ_ID){a=(a||[]).push(b.OBJ_ID)}else{if(c.OBJ_ID){a=(a||[]).push(c.OBJ_ID)}}if(a){Array.each(a,function(e){if(!this.__events_tagged[e]){this.__events_tagged[e]=[]
}this.__events_tagged[e].push([d,c,b])},this)}return this},addEvents:function(c,b,a){for(var d in c){this.addEvent(d,c[d],b,a)
}return this},fireEvent:function(d,c,b){this.__initEventsObj();d=this.normalizeEventName(d);if(!this.__events[d]){return this
}var a=this.__events_default_context||this;c=Array.from(c);Object.each(this.__events[d],function(e){if(b){e[0].delay(b,e[1]||a,c)
}else{e[0].apply(e[1]||a,c)}});return this},removeEvent:function(d,c,a){var e=[],b=false;this.__initEventsObj();d=this.normalizeEventName(d);
if(!this.__events[d]){return this}if(!a){a=null}Array.each(this.__events[d],function(f){if(f[0]==c&&f[1]==a){b=true}else{e.push(f)
}});if(b){this.__events[d]=e;if(!this.__preventCleanupTagged){this.__cleanupTaggedEvents()}}return this},removeEvents:function(d,c){var e;
this.__initEventsObj();this.__preventCleanupTagged=true;for(e in this.__events){if(d&&d!=e){continue}var b=this.__events[e];
for(var a=b.length;a--;){if(a in b){this.removeEvent(e,b[a],c)}}}this.__preventCleanupTagged=false;this.__cleanupTaggedEvents();
return this},removeTaggedEvents:function(a){if(!this.__events_tagged[a]){return}this.__preventCleanupTagged=true;Array.each(this.__events_tagged[a],function(b){this.removeEvent(b[0],b[1],b[2])
},this);this.__preventCleanupTagged=false;this.__cleanupTaggedEvents()},__cleanupTaggedEvents:function(){Object.each(this.__events_tagged,function(d,a){var b=[],c=false;
Array.each(d,function(e){if(fn!=e){b.push(fn)}else{c=true}});if(c){if(b.length){this.__events_tagged[a]=b}else{delete this.__events_tagged[a]
}}else{b=null}})}};Orb.createNamespace("Orb.Util");Orb.Util.TimeAgo={_watchEls:[],_watchTimer:null,refreshPeriod:60000,phrases:{sec_less:"less than a second",sec:"1 second",secs:"{0} seconds",min:"1 minute",mins:"{0} minutes",hour:"1 hour",hours:"{0} hours",day:"1 day",days:"{0} days",week:"1 week",weeks:"{0} weeks",month:"1 month",months:"{0} months",year:"1 year",years:"{0} years",ago:"ago"},get:function(a){return this.getForMs(this.getDateDiff(a))
},applyToElements:function(b){var a=this;b.each(function(c){a._refreshElements([c]);a._watchEls.push(c)});if(this._watchTimer===null){window.setInterval(this._refreshElements.bind(this),this.refreshPeriod)
}},applyToJquery:function(a){this.applyToElements(a.toArray())},_refreshElements:function(b){if(!b){b=this._watchEls}var a=this;
b.each(function(f){if(!f.parentNode){return}f=$(f);if(!f.data("timeago")){var i=f.get(0).tagName.toLowerCase()=="time";var e=i&&f.attr("datetime")?f.attr("datetime"):f.attr("title");
if(!e){return}var d=e.replace(/\.\d\d\d+/,"");d=d.replace(/-/,"/").replace(/-/,"/");d=d.replace(/T/," ").replace(/Z/," UTC");
d=d.replace(/([\+-]\d\d)\:?(\d\d)/," $1$2");f.data("timeago",{datetime:new Date(d)});var c=$.trim(f.text());if(c.length>0){f.attr("title",c)
}}var g=f.data("timeago");if(!isNaN(g.datetime)){var h=a.get(g.datetime);if(!f.data("timeago-no-ago")){h+=" "+a.phrases.ago
}f.text(h)}})},getRelativeInfo:function(b){var d=0,e=0,a=0,f=0,c=0;d=parseInt(b/1000);c=parseInt(d/29030400);d-=c*29030400;
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
DeskPRO.IntervalCaller=new Orb.Class({Implements:[Orb.Util.Options],initialize:function(a){this.options={touchResets:true,touchRequired:true,resetTimeForce:0,callback:function(){},timeout:null,autostart:true};
this.setOptions(a);if(this.options.autostart){this.start()}this.touched=false;this.paused=false;this.lastTime=new Date()},start:function(){if(this.timer){window.clearTimeout(this.timer);
this.timer=null}this.timer=window.setTimeout(this.exec.bind(this),this.options.timeout)},stop:function(){if(this.timer){window.clearTimeout(this.timer);
this.timer=null}},touch:function(){this.touched=true;if(this.options.touchResets){if(this.options.resetTimeForce){var a=new Date();
var b=a.getTime()-this.lastTime.getTime();if(b>this.options.resetTimeForce){return}}this.start()}},exec:function(a){if(this.lastTime){this.lastTime=new Date()
}if(!a&&this.options.touchRequired&&!this.touched){this.start();return}this.touched=false;this.options.callback();this.start()
},execNow:function(){this.exec()},destroy:function(){this.stop();this.options=null;this.lastTime=null}});Orb.createNamespace("DeskPRO");
DeskPRO.MessageBroker=new Orb.Class({Implements:[Orb.Util.Events],sendMessage:function(a,d){this.fireEvent(a,[d,a]);var b=a.split(".");
var c=null;while(b.pop()){c=b.join(".")+".*";this.fireEvent(c,[d,c])}},addMessageListener:function(b,d,c,a){this.addEvent(b,d,c,a)
},removeMessageListener:function(a,c,b){this.removeEvent(a,c,b)},removeTaggedListeners:function(a){this.removeTaggedEvents(a)
}});Orb.createNamespace("DeskPRO");DeskPRO.BasicWindow=new Orb.Class({Implements:[Orb.Util.Options],initialize:function(a){this.DEBUG={};
this.options=this.getDefaultOptions();this.registry={};this.messageBroker=this.messageBroker=new DeskPRO.MessageBroker();
if(a){this.setOptions(a)}this.init()},init:function(){},initPage:function(){},getDefaultOptions:function(){return{}},get:function(b,a){if(this.registry[b]===undefined){return a
}return this.registry[b]},set:function(b,a){this.registry[b]=a},getMessageBroker:function(){return this.messageBroker},getDebug:function(a){if(this.DEBUG[a]===undefined){return false
}return this.DEBUG[a]}});Orb.createNamespace("DeskPRO.UI");DeskPRO.UI.SimpleTabs=new Orb.Class({Implements:[Orb.Util.Options,Orb.Util.Events],initialize:function(b){this.options={triggerElements:".tab-trigger",activeClassname:"on",context:document,autoSelectFirst:true};
this.lastActiveTab=null;this.triggerEls=null;if(b){this.setOptions(b)}this.triggerEls=this.options.triggerElements;if(typeOf(this.triggerEls)=="string"){this.triggerEls=$(this.triggerEls,this.options.context)
}var a=this;this.triggerEls.click(function(d){d.cancel=false;d.tabEl=$(this);a.fireEvent("tabClick",[d]);if(!d.cancel){a._handleTabClick(this,d)
}});if(this.options.autoSelectFirst){var c=this.triggerEls.filter(".on:first");if(!c.length){c=this.triggerEls.first()}this.activateTab(c)
}},addTriggerElement:function(b){var a=this;this.triggerEls.add(b);b.click(function(c){c.cancel=false;c.tabEl=$(this);a.fireEvent("tabClick",[c]);
if(!c.cancel){a._handleTabClick(this,c)}})},_handleTabClick:function(b,c){var a=$(b);this.activateTab(a,c)},activateTab:function(c,b){var a={event:b||null,tabEl:c,lastTabEl:this.lastActiveTab,manager:this,cancel:false};
this.fireEvent("beforeTabSwitch",a);if(a.cancel){return}delete a.cancel;if(this.lastActiveTab){this.lastActiveTab.removeClass(this.options.activeClassname);
this.getContentElFromTab(this.lastActiveTab).removeClass(this.options.activeClassname).hide();this.lastActiveTab=null}this.lastActiveTab=c;
this.lastActiveTab.addClass(this.options.activeClassname);a.tabContent=this.getContentElFromTab(this.lastActiveTab).addClass(this.options.activeClassname).show();
this.fireEvent("tabSwitch",a)},getContentElFromTab:function(b){if(!b.data("tab-for")){console.error("tab has no tab-for: %o",b);
console.trace();return $()}var a=$(b.data("tab-for"),this.options.context);if(a.length<1){console.error("no tab content exists for tab: %o",b);
console.trace()}return a}});Orb.createNamespace("DeskPRO.UI");DeskPRO.UI.Overlay=new Orb.Class({Implements:[Orb.Util.Options,Orb.Util.Events],initialize:function(a){this.objectId=null,this.options={triggerElement:null,contentMethod:"element",contentElement:null,contentAjax:{url:"",type:"GET",dataType:"html"},iframeUrl:null,iframeId:false,maxHeight:700,maxWidth:900,destroyOnClose:false,customClassname:"",isModal:true,zIndex:1000000,escapeClose:true,modalClickClose:true,objectGroup:"default",addClose:true};
this.isThisDestroyed=false;this.hasInit=false;this.hasSentAjax=false;this.elements={};if(a){this.setOptions(a)}if(this.options.triggerElement){this.setupTriggerElement($(this.options.triggerElement))
}if(this.options.escapeClose){$(document).keydown((function(b){if(b.which==27){this.closeOverlay()}}).bind(this))}},isOpen:function(){return this.isOverlayOpen()
},isOverlayOpen:function(){if(!this.hasInit){return false}return this.elements.wrapper.is(":visible")},open:function(){return this.openOverlay()
},openOverlay:function(){if(!this.initOverlay()){return}if(this.isOverlayOpen()){return}var e={overlay:this,cancel:false};
this.fireEvent("beforeOverlayOpened",e);if(e.cancel){return}if(!this.options.zIndex){this.options.zIndex=Orb.findHighestZindex()+1
}this.elements.modal.css({"z-index":this.options.zIndex,position:"absolute",top:0,right:0,bottom:0,left:0});this.elements.modal.fadeIn(200);
if(this.options.contentMethod=="iframe"){var j=$(window).width()-250;var d=$(window).height()-150;if(j>this.options.maxWidth){j=this.options.maxWidth
}if(d>this.options.maxHeight){d=this.options.maxHeight}$("iframe:first",this.elements.wrapper).css({width:j,height:d});var i=($(window).width()-this.elements.wrapperOuter.outerWidth())/2;
var g=($(window).height()-this.elements.wrapperOuter.outerHeight())/2;this.elements.wrapperOuter.css({left:i,top:g})}else{var j=this.elements.wrapperOuter.outerWidth();
var c=$(window).width();var f=(c/2)-(j/2);var d=this.elements.wrapperOuter.outerHeight();var a=$(window).height();var b=(a/2)-(d/2);
this.elements.wrapperOuter.css({top:b,left:f})}this.elements.wrapperOuter.css({"z-index":this.options.zIndex+1,position:"absolute",left:f});
this.elements.wrapperOuter.fadeIn(450,(function(){this.fireEvent("overlayOpened",{overlay:this})}).bind(this))},close:function(){return this.closeOverlay()
},closeOverlay:function(){if(!this.isOverlayOpen()){return}var a={overlay:this,cancelClose:false};this.fireEvent("beforeOverlayClosed",a);
if(a.cancelClose){return}this.elements.modal.fadeOut(450);this.elements.wrapperOuter.fadeOut(200);this.fireEvent("overlayClosed",{overlay:this});
if(this.options.destroyOnClose){this.destroy()}},initOverlay:function(){if(this.hasInit){return true}if(this.options.isModal){this.elements.modal=$('<div class="deskpro-overlay-overlay '+this.options.customClassname+'" style="display:none" />');
this.elements.modal.appendTo("body");if(this.options.modalClickClose){this.elements.modal.click((function(){this.closeOverlay()
}).bind(this))}}this.elements.wrapperOuter=$('<div class="deskpro-overlay-outer '+this.options.customClassname+'" style="display:none" />');
this.elements.wrapperOuter.appendTo("body");this.elements.wrapper=$('<div class="deskpro-overlay '+this.options.customClassname+'">');
this.elements.wrapper.appendTo(this.elements.wrapperOuter);switch(this.options.contentMethod){case"element":var c=$(this.options.contentElement);
this._setContent(c);this.hasInit=true;return true;break;case"ajax":if(this.hasSentAjax){return false}this.hasSentAjax=true;
var b=Object.merge(this.options.contentAjax,{success:this._handleAjaxSuccess.bind(this)});$.ajax(b);this.fireEvent("ajaxStart",{overlay:this});
return false;break;case"iframe":var a="iframe_"+Orb.uuid();var c=$('<iframe name="'+a+'" src="'+this.options.iframeUrl+'"></iframe>');
if(this.options.iframeId){c.attr("id",this.options.iframeId)}this._setContent(c);this.hasInit=true;this.elements.wrapper.addClass("no-pad").addClass("iframe");
return true;break}console.error("Unknown content method: %s",this.options.contentMethod);return false},_handleAjaxSuccess:function(c){var d=$(c);
if(d.length!=1){var a=$("<div />");a.append(d)}else{var a=d}a.show();this._setContent(a);this.hasInit=true;var b={overlay:this,ajaxData:c};
this.fireEvent("ajaxDone",b);this.openOverlay()},_setContent:function(b){this.elements.wrapper.empty();b.detach().appendTo(this.elements.wrapper);
b.show();if(!$("div.overlay-content:first",b).length){var a=b;var b=$('<div class="overlay-content" />');a.wrap(b)}if(this.options.addClose){$("div.overlay-content:first",this.elements.wrapper).prepend('<a class="close-overlay close-trigger">Close</a>')
}$(".overlay-close-trigger, .close-trigger",this.elements.wrapper).click((function(c){c.preventDefault();this.closeOverlay()
}).bind(this));if(!$(".overlay-footer:first",b).length){$("div.overlay-content:first",b).addClass("no-footer")}this.fireEvent("contentSet",{overlay:this,contentEl:b,wrapperEl:this.elements.wrapper})
},setContent:function(a){this._setContent(a)},setupTriggerElement:function(b){b=$(b);var a=(function(c){this.openOverlay();
c.preventDefault()}).bind(this);if(b.is(".dbl-click-trigger")){b.dblclick(a)}else{b.click(a)}},getWrapper:function(){return $(this.elements.wrapperOuter)
},destroy:function(){this.fireEvent("beforeDestroy",[this]);if(this.elements.wrapperOuter){this.elements.wrapperOuter.remove()
}if(this.elements.modal){this.elements.modal.remove()}this.isThisDestroyed=true;this.fireEvent("destroyed",[this])},isDestroyed:function(){return this.isThisDestroyed
}});Orb.createNamespace("DeskPRO.User");DeskPRO.User.Window=new Orb.Class({Extends:DeskPRO.BasicWindow,init:function(){this.PAGE=null
},initPage:function(){var a=function(){var g=$("#dp_sidebar");var f=$("#dp_content");var i=$("#dp_content .dp-content-block");
if(!$("body").is(".dp-no-resize-block")){if(g.height()>f.height()){var e=i.last();if(i.length==1){var d=g.height()}else{var b;
var c=0;for(b=0;b<i.length-1;b++){var c=i.eq(b).outerHeight()}var d=g.height()-c}e.css("min-height",d)}}$(".dp-with-nav.dp-box-content").each(function(){var h=$(this);
var k=h.closest(".dp-content-block");var m=40;var j=h.height()+m;h.css("min-height",k.height());if(j<k.height()){h.css("min-height",k.height()-m-90)
}})};a();if(this.PAGE){this.PAGE.initPage()}this.elementHandlers={};this.initFeatures(document)},initFeatures:function(b){var a=this;
this.ideaVoteHelper=new DeskPRO.User.ElementHandler.Helper.IdeaVote();$(".with-handler[data-element-handler]",b).each(function(){var e=$(this);
var d=e.data("element-handler");var c=Orb.getNamespacedObject(d);if(!c){console.error("Unknown portal handler `%s` on element %o",d,this);
return}if(!e.attr("id")){e.attr("id",Orb.getUniqueId("portal_"))}var f=new c({el:e});a.elementHandlers[e.attr("id")]=f});
$("form.with-form-validator",b).each(function(){var c=new DeskPRO.Form.FormValidator($(this));$(this).data("form-validator-inst",c)
});$("a.in-overlay").click(function(f){f.preventDefault();var e=$(this);var d=e.attr("href");if(d.indexOf("?")!==-1){d+="&_partial"
}else{d+="?_partial"}var c=new DeskPRO.UI.Overlay({contentMethod:"ajax",contentAjax:{url:d},destroyOnClose:true});c.open()
});$(".timeago").timeago();$(document).delegate(".dp-bound-faded","click",function(){var c=$(this).parent();var d=$("a[href]",c).first();
window.location=d.attr("href")})},getHandler:function(a){return this.handlers[a]},hasHandler:function(a){return !!this.handlers[a]
},setPageHandler:function(a){this.PAGE=a},getPageHandler:function(){return this.PAGE}});Orb.createNamespace("DeskPRO.User.ElementHandler");
DeskPRO.User.ElementHandler.ElementHandlerAbstract=new Orb.Class({Implements:[Orb.Util.Options],initialize:function(a){this.options={};
if(a){this.setOptions(a)}this.el=null;if(this.options.el){this.el=$(this.options.el)}this.init()},init:function(){}});Orb.createNamespace("DeskPRO.User.ElementHandler");
DeskPRO.User.ElementHandler.MoreLoader=new Orb.Class({Extends:DeskPRO.User.ElementHandler.ElementHandlerAbstract,init:function(){this.listEl=$(".content-list:first ul:first",this.el);
this.moreWrap=$(".content-more:first",this.el);this.moreBtn=$("button:first, a.button:first",this.el).first();this.loadUrl=this.el.data("load-url");
this.currentPage=1;var a=this;this.moreBtn.click(function(b){b.preventDefault();b.stopPropagation();a.loadNextPage()})},loadNextPage:function(){if(this.moreWrap.is(".loading")){return
}this.moreWrap.addClass("loading");var a=this.loadUrl.replace("{page}",this.currentPage+1);if(a.indexOf("?")===-1){a+="?_partial=more"
}else{a+="&_partial=more"}$.ajax({url:a,dataType:"html",context:this,success:function(b){this.insertNewItems(b.trim())}})
},insertNewItems:function(b){if(b.length){var a=$(b)}else{var a=null}this.currentPage++;this.moreWrap.removeClass("loading");
if(!a||!a.length||!a.last().is(".has-more")){this.moreWrap.remove()}this.listEl.append(a)}});Orb.createNamespace("DeskPRO.User.ElementHandler.Helper");
DeskPRO.User.ElementHandler.Helper.IdeaVote=new Orb.Class({initialize:function(){var a=this;$("body").delegate(".dp-idea-vote","click",function(){a.voteOnElement($(this))
})},voteOnElement:function(b){var a;if(b.is(".dp-voted")){a=0}else{a=1}$.ajax({url:b.data("vote-url"),data:{rating:a},dataType:"json",context:this,success:function(c){if(c.voted){b.addClass("dp-voted")
}else{b.removeClass("dp-voted")}$("em",b).first().text(c.total_rating||0)}})}});Orb.createNamespace("DeskPRO.User.ElementHandler");
DeskPRO.User.ElementHandler.LoginBox=new Orb.Class({Extends:DeskPRO.User.ElementHandler.ElementHandlerAbstract,init:function(){this.loginLink=$("#dp_login_link");
this.loginBox=$("#dp_login_box");this.loginBoxTitle=$("#dp_login_box_title");this.loginSection=$(".dp-login-section",this.el);
this.resetSection=$(".dp-reset-section",this.el);this.loginLink.click((function(a){a.stopPropagation();a.preventDefault();
this.open()}).bind(this));this.loginBoxTitle.click((function(a){a.stopPropagation();a.preventDefault();this.close()}).bind(this));
this.loginBox.click(function(a){a.stopPropagation()});this._initResetSection();$(document).click(this.close.bind(this))},updatePositions:function(){var c=this.loginLink.offset();
var b=this.loginLink.width();var j=this.loginLink.height();var h=this.loginBoxTitle.outerWidth();var e=this.loginBoxTitle.outerHeight();
var a=-2;var g=-8;this.loginBoxTitle.css({top:c.top+a,left:c.left+g});var d=$("#dp_header_bar").offset();var f=$("#dp_header_bar").width();
var i=this.loginBox.width();this.loginBox.css({top:c.top+a+e,left:d.left+f-i})},open:function(){this.updatePositions();this.loginBoxTitle.show();
this.loginBox.slideDown("fast")},close:function(){this.loginBox.slideUp("fast",(function(){this.loginBoxTitle.hide();this.hideReset(true)
}).bind(this))},_initResetSection:function(){$(".forgot",this.el).click((function(a){a.preventDefault();this.showReset()}).bind(this));
$(".back",this.resetSection).click((function(a){this.hideReset()}).bind(this));$(".dp-do-send",this.resetSection).click((function(a){a.preventDefault();
this.sendReset()}).bind(this))},sendReset:function(){this.resetSection.addClass("loading");$.ajax({url:BASE_URL+"login/reset-password/send",type:"POST",data:{email:$("#dp_login_email").val()},dataType:"json",context:this,success:function(){this.resetSection.removeClass("loading");
var b=$(".dp-reset-desc",this.resetSection);var a=$(".dp-reset-sent",this.resetSection);b.slideUp("fast",function(){a.slideDown()
})}})},showReset:function(){this.loginSection.slideUp("fast",(function(){this.resetSection.slideDown("fast")}).bind(this))
},hideReset:function(a){if(a){this.resetSection.hide();this.loginSection.show();$(".dp-reset-desc",this.resetSection).show();
$(".dp-reset-sent",this.resetSection).hide()}else{this.resetSection.slideUp("fast",(function(){this.loginSection.slideDown("fast");
$(".dp-reset-desc",this.resetSection).show();$(".dp-reset-sent",this.resetSection).hide()}).bind(this))}}});Orb.createNamespace("DeskPRO.User.ElementHandler");
DeskPRO.User.ElementHandler.NewTicket=new Orb.Class({Extends:DeskPRO.User.ElementHandler.ElementHandlerAbstract,init:function(){this.titleTxt=$("#newticket_ticket_subject");
this.messageTxt=$("#newticket_ticket_message");this.ticketForm=$("#dp_newticket_form");this._initSuggestionsBox();this._initFields();
this._initLoginForm(this.el);this._initPreticketStatus()},_initSuggestionsBox:function(){this.inlineSuggestions=new DeskPRO.User.InlineSuggestions({elementWrapper:this.el,titleText:this.titleTxt,contentText:this.messageTxt,onResolved:this.setTicketSolvedAjax.bind(this),onResolvedRedirect:this.setTicketSolvedRedirect.bind(this),onNotResolved:this.setTicketUnsolvedAjax.bind(this)})
},setTicketSolvedAjax:function(b,a){this.setTicketSolvedStatusAjax(b,a,false)},setTicketUnsolvedAjax:function(b,a){this.setTicketSolvedStatusAjax(b,a,true)
},setTicketSolvedStatusAjax:function(b,a,c){var d=$("#dp_newticket_preticket_status_id").val();if(d>0){url=BASE_URL+"tickets/new/content-solved-save.json?preticket_status_id="+escape(d)+"&content_type="+escape(b)+"&content_id="+escape(a);
if(c){url+="&add_unsolved=1"}$.ajax({url:url,type:"GET"})}},setTicketSolvedRedirect:function(b,c,a){var d=$("#dp_newticket_preticket_status_id").val();
if(d>0){b=BASE_URL+"tickets/new/content-solved-redirect?preticket_status_id="+escape(d)+"&content_type="+escape(c)+"&content_id="+escape(a)+"&url="+escape(b)
}window.location=b},_initFields:function(){this.depSelect=$("select.department_id",this.el);this.departmentId=0;var a=this;
this.depSelect.change(function(){a.handleDepChange()});this.depSelect.data("original-name",this.depSelect.attr("name"));$("select.sub_department_id",this.el).change(function(){a.setDepartment($(this).val())
});$(".with-sub-options:not(.department_id_wrapper)",this.el).each(function(){var b=$(".parent-option",this);b.data("original-name",b.attr("name"));
var c=this;b.change(function(){var f=$(this).val();var e=$(".sub-options-"+f,c);var d=$(".dp-sub-options",c).hide();$("select",d).attr("name","");
e.show();if(e.length){b.attr("name","");$("select",e).attr("name",b.data("original-name"))}else{b.attr("name",b.data("original-name"))
}})});$("form",this.el).submit(function(b){$(".sub-options:hidden",this.el).remove();$(".with.dp-sub-options",this.el).each(function(){var d=$(".dp-sub-options",this);
if(d){var c=$(".parent-option");c.attr("name","")}})})},handleDepChange:function(){var e=$(".department_id_wrapper",this.el);
var a=$(".dp-sub-options",e).hide();$("select",a).attr("name","");var c=this.depSelect.val();var d=$(".sub-options-"+c,e);
if(!d.length){this.depSelect.attr("name",this.depSelect.data("original-name"));this.setDepartment(c);return}else{this.depSelect.attr("name","");
$("select",d).attr("name",this.depSelect.data("original-name"))}d.show();var b=$("select.department_id",d);this.setDepartment(b)
},setDepartment:function(c){this.clearAll();if(c==this.departmentId){return}this.departmentId=c;if(!window.DESKPRO_TICKET_DISPLAY){return
}var a=this.departmentId;if(!window.DESKPRO_TICKET_DISPLAY[a]){if(!window.DESKPRO_TICKET_CAT_PARENTS){return}while(true){var a=window.DESKPRO_TICKET_CAT_PARENTS[a];
if(!a){return}if(window.DESKPRO_TICKET_DISPLAY[a]){return}}}var b=window.DESKPRO_TICKET_DISPLAY[a];console.log("depItems %o",b);
Array.each(b,function(e){var f=this.getItemId(e);var d=$("."+f+":first");d.show()},this)},clearAll:function(){$(".ticket-display-field").hide()
},getItemId:function(a){var b=a.item_type;if(a.item_id){b+="_"+a.item_id}return b},_initLoginForm:function(a){this.inlineLogin=new DeskPRO.User.InlineLoginForm({context:this.el})
},_initPreticketStatus:function(){$("input, select",this.ticketForm).change((function(){this.updatePreticketStatus()}).bind(this))
},updatePreticketStatus:function(){var a=this.ticketForm.serializeArray();$.ajax({url:BASE_URL+"tickets/new/save-status",type:"POST",data:a,dataType:"json",success:function(b){$("#dp_newticket_preticket_status_id").val(b.preticket_status_id)
}})}});Orb.createNamespace("DeskPRO.User.ElementHandler");DeskPRO.User.ElementHandler.FormUploadHandler=new Orb.Class({Extends:DeskPRO.User.ElementHandler.ElementHandlerAbstract,init:function(){var a=this.el;
if(this.el.data("drop-document")=="1"){a=$(document)}this.el.fileupload({url:this.el.data("upload-to"),dropZone:a,autoUpload:true,formData:{security_token:this.el.data("security-token")},uploadTemplate:$(".dptpl-attach-upload",this.el),downloadTemplate:$(".dptpl-attach-download",this.el)});
$(".dp-fallback",this.el).remove();$(".dp-good-upload",this.el).show()}});Orb.createNamespace("DeskPRO.User.ElementHandler");
DeskPRO.User.ElementHandler.OmniSearch=new Orb.Class({Extends:DeskPRO.User.ElementHandler.ElementHandlerAbstract,init:function(){var a=this;
this.backdrop=$('<div class="dp-backdrop" />').hide().appendTo("body");this.assistEl=$("#dp_search_assist");this.searchboxEl=$("#deskpro_search");
this.resultsEl=$("div.results",this.assistEl);this.searchboxEl.focus(this.activateAssist.bind(this));this.backdrop.click(function(b){b.stopPropagation();
a.deactivateAssist()});this.isActivated=false;this.searchTimer=new DeskPRO.IntervalCaller({touchResets:true,touchRequired:true,resetTimeForce:1500,timeout:750,autostart:true,callback:this.updateResults.bind(this)});
this.searchboxEl.keypress(function(){if(!$(this).val().trim().length){a.close()}else{a.searchTimer.touch()}});this.searchboxEl.focus(function(){a.activateAssist()
});this.lastTerms=null;$(".foot a",this.assistEl).click(function(d){var c=$(this);if(c.is(".no-omni-trigger")){return}d.preventDefault();
d.stopPropagation();var b=c.attr("href");b=Orb.appendQueryData(b,"q",a.searchboxEl.val().trim());window.location=b})},activateAssist:function(){this.isActivated=true;
this.updatePosition();if(this.searchboxEl.val().trim().length&&$("li",this.resultsEl).length){this.open()}else{this.close()
}this.searchTimer.execNow()},open:function(){this.assistEl.show();this.backdrop.show()},deactivateAssist:function(){this.isActivated=false;
this.close()},close:function(){this.assistEl.hide();this.backdrop.hide()},updatePosition:function(){var c=this.searchboxEl.offset();
var a=this.searchboxEl.outerWidth();var b=this.searchboxEl.outerHeight();this.assistEl.css({top:c.top+b,left:c.left-1,width:a-1})
},updateResults:function(){if(!this.isActivated){return}var a=this.searchboxEl.val().trim();if(a==this.lastTerms||a===""){return
}this.lastTerms=a;$.ajax({url:BASE_URL+"search/omnisearch/"+encodeURI(a),dataType:"html",context:this,success:function(b){var c=$(b);
this.resultsEl.empty();if(!$("li",c).length){this.close()}else{this.resultsEl.append(c);this.open()}}})}});Orb.createNamespace("DeskPRO.User.ElementHandler");
DeskPRO.User.ElementHandler.TicketList=new Orb.Class({Extends:DeskPRO.User.ElementHandler.ElementHandlerAbstract,init:function(){$("a.close-ticket-trigger").click(function(a){var b="Once a ticket is closed, our agents will not reply to it anymore. Are you sure you want to close this ticket?";
if(!confirm(b)){a.preventDefault()}})}});Orb.createNamespace("DeskPRO.User.ElementHandler");DeskPRO.User.ElementHandler.TicketView=new Orb.Class({Extends:DeskPRO.User.ElementHandler.ElementHandlerAbstract,init:function(){$(".feedback-link",this.el).each(function(){var a=$(this);
$("a",this).click(function(b){b.preventDefault();window.open($(this).attr("href"),"feedback","status=no,toolbar=no,location=no,menubar=no,resizable=no,scrollbars=yes,height=310,width=720")
}).mouseover(function(){if($(this).is(".helpful")){a.addClass("rating-helpful").removeClass("rating-not-helpful")}else{a.removeClass("rating-helpful").addClass("rating-not-helpful")
}}).mouseout(function(){a.removeClass("rating-helpful").removeClass("rating-not-helpful")})})}});Orb.createNamespace("DeskPRO.User.ElementHandler");
DeskPRO.User.ElementHandler.InlineEmailManage=new Orb.Class({Extends:DeskPRO.User.ElementHandler.ElementHandlerAbstract,init:function(){var b=this;
this.emailField=$(".dp-email-field",this.el).click(function(){$(this).blur()});this.newEmailField=$(".dp_inline_email_new",this.el);
this.emailList=$(".dp-email-manage-list",this.el);this.controlsEl=$(".dp-email-manage-controls",this.el);$('input[name="dp_inline_email_choice"]',this.controlsEl).click(function(){var d=$(this).val();
if(d=="NEW"){b.setNewMode()}else{b.emailField.val(d)}});this.emailError=$(".error-message",this.el);this.newEmailField.keypress(function(d){if(d.keyCode==13){d.preventDefault();
d.stopPropagation()}});this.newEmailField.keyup(function(d){if(b.mode=="new"){b.emailField.val(b.newEmailField.val())}});
this.mode="normal";if($('input[name="dp_inline_email_choice"]:selected').val()=="NEW"){this.setNewMode()}var a=$(".change-email",this.el).click(function(d){d.preventDefault();
b.controlsEl.slideDown("fast");a.hide();c.show()});var c=$(".change-email-close",this.el).click(function(d){d.preventDefault();
b.controlsEl.slideUp("fast");a.show();c.hide()})},setNormalMode:function(){this.mode="normal"},setNewMode:function(){this.mode="new";
this.emailField.val(this.newEmailField.val())}});Orb.createNamespace("DeskPRO.User.ElementHandler");DeskPRO.User.ElementHandler.CommentFormLogin=new Orb.Class({Extends:DeskPRO.User.ElementHandler.ElementHandlerAbstract,init:function(){var c=this;
var b=$("a",this.el).first();b.click(function(a){a.preventDefault();c.openWindow($(this).attr("href"))});this.type=this.el.data("auth-type");
this.jsTell=null},getJsTell:function(){if(this.jsTell){return this.jsTell}var a=this;var b=Orb.uuid();this.jsTell=b;window[b]=function(c){a.authFinished(c)
};return this.jsTell},authFinished:function(b){$("#comments_login_info .email-address-row").hide();switch(this.type){case"twitter":var a='<a href="http://twitter.com/'+b.identity_friendly+'">'+b.identity_friendly+"</a>";
$("#comments_login_info .source-extra-name").html(a);$("#comments_login_info .source-extra").show();$("#comments_login_info .display-name-field").val(b.fullname);
break;case"facebook":var a='<a href="'+b.link+'">'+b.name+"</a> ("+b.email+")";$("#comments_login_info .source-extra-name").html(a);
$("#comments_login_info .source-extra").show();$("#comments_login_info .display-name-field").val(b.name);break;default:var a=b.person_name;
if(b.person_email){a+=" ("+b.person_name+")"}$("#comments_login_info .source-extra-name").html(a);$("#comments_login_info .source-extra").show();
$("#comments_login_info .display-name-field").val(b.person_name);break}$("#comments_login_info .nav").hide();$("#comments_login_info").addClass("no-nav")
},openWindow:function(a){a=Orb.appendQueryData(a,"js_tell",this.getJsTell());window.open(a,this.getJsTell(),"width=600,height=350,location=0,menubar=0,scrollbars=0,status=0,toolbar=0,resizable=0")
}});Orb.createNamespace("DeskPRO.User.ElementHandler");DeskPRO.User.SuggestedContentOverlay=new Orb.Class({Implements:[Orb.Util.Options,Orb.Util.Events],initialize:function(a){this.options={template:null,url:null,pageUrl:null,contentType:null,contentId:null,destroyOnClose:true,maxHeight:700,openNear:null};
this.setOptions(a);this.overlayEl=null;this.backdropEl=null;this.runningAjax=false},_initOverlay:function(){if(this._hasInit){return
}this._hasInit=true;var a=this;this.overlayEl=$(this.options.template).hide().appendTo("body");this.controlsWrap=$(".dp-controls",this.overlayEl).hide();
this.backdropEl=$('<div class="dp-backdrop dp-faded" />').appendTo("body");this.backdropEl.click(function(b){a.close()});
$(".dp-close-btn",this.overlayEl).click(function(b){b.preventDefault();a.close()});this.runningAjax=$.ajax({url:this.options.url,type:"GET",context:this,error:function(){this.close()
},success:function(b){this.controlsWrap.show();$(".dp-content-holder",this.overlayEl).empty().html(b);$(".dp-section-toggle",this.controlsWrap).click(function(c){c.preventDefault();
var d=$(this).data("toggle-section");$(".dp-control-section",a.controlsWrap).fadeOut("fast",function(){window.setTimeout(function(){$(d,a.controlsWrap).fadeIn()
},150)})});$(".dp-toggle-sel",this.overlayEl).click(function(d){d.preventDefault();var c=$($(this).data("toggle-sel"),a.overlayEl);
if($(this).is(".open")){$(this).removeClass("open");c.slideUp()}else{if($(this).data("toggle-self")){$(this).slideUp("fast",function(){c.slideDown()
}).addClass("open")}else{c.slideDown();$(this).addClass("open")}}});if(a.options.pageUrl){$(".dp-open-full",this.overlayEl).click(function(c){c.preventDefault();
window.open(a.options.pageUrl);a.close()});$(".dp-open-full a",this.overlayEl).attr("href",a.options.pageUrl)}this.fireEvent("init",[this.overlayEl,this.controlsEl,this])
}})},open:function(){this._initOverlay();var g={top:40,left:100,width:null};this.fireEvent("preOpen",[g,this]);if(g.cancel){return
}var f=this.overlayEl.height();var d=$(window).height();f=d*0.7;if(f<250){f=250}if(f>this.options.maxHeight){f=this.options.maxHeight
}var b=$(document).scrollTop();var i=b+d;if(this.options.openNear){var e=$(this.options.openNear);var k=e.offset();var c=e.outerWidth();
var a=k.top+f;if(a>i){g.top=b+20}else{g.top=b+50}var j=40;g.top=k.left;g.left=k.left-(j/2);g.width=c+j}this.overlayEl.css({top:g.top,left:g.left});
if(g.width){this.overlayEl.css("width",g.width)}if(f){this.overlayEl.css("height",f)}this.overlayEl.fadeIn("fast").addClass("open");
this.backdropEl.show()},close:function(){if(!this._hasInit){return}if(!this.overlayEl.is(".open")){return}this.overlayEl.fadeOut("fast",(function(){if(this.options.destroyOnClose){this.destroy()
}}).bind(this));this.backdropEl.hide()},destroy:function(){if(this._hasInit){return}this.overlayEl.remove();this.backdropEl.remove();
this.overlayEl=null;this.backdropEl=null;if(this.runningAjax){this.runningAjax.abort();this.runningAjax=null}}});Orb.createNamespace("DeskPRO.User.ElementHandler");
DeskPRO.User.InlineSuggestions=new Orb.Class({Implements:[Orb.Util.Options,Orb.Util.Events],initialize:function(a){this.options={elementWrapper:null,titleText:"#__dp_nomatch",contentText:"#__dp_nomatch"};
this.setOptions(a);this.el=$(this.options.elementWrapper);this.titleTxt=$(this.options.titleText);this.messageTxt=$(this.options.contentText);
this._initSuggestionsBox()},_initSuggestionsBox:function(){this.suggestionsBox=$(".dp-related-search",this.el);this.resultsEl=$(".results",this.suggestionsBox);
this.moreLink=$(".more-link",this.suggestionsBox);this.lastSuggestions=null;this.lastString=null;this.notAnsweredResults=[];
this.hasStartedSearch=false;this.moreLink.click((function(a){this.moreLink.hide();$("li",this.resultsEl).show()}).bind(this));
this.suggestionsUrl=this.el.data("suggestions-url");this.sugTitleTimer=null;this.sugMessageTimer=null;this.titleTxt.keypress((function(){if(!this.hasStartedSearch){return
}if(this.sugTitleTimer){return}this.sugTitleTimer=this.updateSuggestions.delay(400,this)}).bind(this));this.titleTxt.blur((function(){this.hasStartedSearch=true;
this.updateSuggestions()}).bind(this));this.messageTxt.keypress((function(){if(!this.hasStartedSearch){return}if(this.sugMessageTimer){return
}this.sugMessageTimer=this.updateSuggestions.delay(1200,this)}).bind(this))},updateSuggestions:function(){if(this.sugTitleTimer){window.clearTimeout(this.sugTitleTimer);
this.sugTitleTimer=null}if(this.sugMessageTimer){window.clearTimeout(this.sugMessageTimer);this.sugMessageTimer=null}var a=(this.titleTxt.val().trim()+" "+this.messageTxt.val().trim()).trim();
if(this.lastSearchString&&this.lastSearchString==a){return}this.lastSearchString=a;if(!a.length){this.suggestionsBox.hide();
return}if(this.doSuggestResend){return}if(this.isSuggestActive){this.doSuggestResend=true;return}this.isSuggestActive=true;
$.ajax({url:this.suggestionsUrl,dataType:"html",data:{content:a},context:this,success:function(e){this.isSuggestActive=false;
if(this.doSuggestResend){this.doSuggestResend=false;this.updateSuggestions()}if(this.lastSuggestions&&this.lastSuggestions==e){return
}this.lastSuggestions=e;this.resultsEl.empty().html(e);if(this.notAnsweredResults.length){var b;for(b=0;b<this.notAnsweredResults.length;
b++){$("li."+this.notAnsweredResults[b],this.resultsEl).remove()}}if(!$("li:first",this.resultsEl).length){this.suggestionsBox.hide();
this.lastSuggestions=null}else{if(!this.moreLink.data("has-mored")){this.moreLink.data("has-mored",true);var f=$("li",this.resultsEl).length;
if(f>6){var d=f-6;$(".count",this.moreLink).text(d);this.moreLink.show();$("li",this.resultsEl).slice(5).hide()}else{this.moreLink.hide()
}}else{this.moreLink.hide()}var c=this;$("li a[href]",this.suggestionsBox).click(function(g){g.preventDefault();c.openSuggestedContent($(this))
});this.suggestionsBox.show()}}})},openSuggestedContent:function(d){var e=d.attr("href");var c=Orb.appendQueryData(e,"_partial","overlay");
var g=d.data("content-type");var f=d.data("content-id");var a=this;var b=new DeskPRO.User.SuggestedContentOverlay({template:$(".related-content-overlay-tpl",this.el).get(0).innerHTML,url:c,pageUrl:e,contentType:d.data("content-type"),contentId:d.data("content-id"),destroyOnClose:true,openNear:$(".dp-related-search",this.el),onInit:(function(j,h,i){$(".dp-set-answered",h).click(function(k){k.preventDefault();
a.fireEvent("resolved",[g,f,true])});$(".dp-answererd",h).click(function(m){m.preventDefault();var k=$(this).data("type");
if(k=="close"){a.fireEvent("resolvedRedirect",[e,g,f,this])}else{i.close()}});$(".dp-not-answered",h).click(function(k){k.preventDefault();
a.fireEvent("notResolved",[g,f,true]);d.parent().addClass("not-answered");a.notAnsweredResults.push(g+"-"+f);i.close()})}).bind(this)});
b.open()}});Orb.createNamespace("DeskPRO.User.ElementHandler");DeskPRO.User.InlineLoginForm=new Orb.Class({Implements:[Orb.Util.Options,Orb.Util.Events],initialize:function(b){var a=this;
this.options={emailSel:"#dp_inline_login_email",passwordSel:"#dp_inline_login_pass",context:null};this.setOptions(b);this.context=this.options.context||document;
this._initLoginForm($(".dp-inline-login",this.context))},_initLoginForm:function(b){var a=this;this.el=b;this.loginWrapper=b;
this.passwordRow=$(".dp-inline-login-pass",b);this.nonloginWrapper=$(".dp-inline-non-login",this.context);this.loginBtn=$(".dp-login-trigger",b);
this.loginSection=$(".dp-login-section",this.el);this.resetSection=$(".dp-reset-section",this.el);$(".dp-inline-login-open",b).click((function(c){c.preventDefault();
c.stopPropagation();if(this.loginWrapper.is(".open")){this.closeLogin()}else{this.openLogin()}}).bind(this));this.loginBtn.click((function(c){c.preventDefault();
c.stopPropagation();this.processLogin()}).bind(this));$(this.options.passwordSel,b).keypress(function(c){if(c.keyCode==13){c.preventDefault();
if(a.isOpen()){a.processLogin()}}});$(this.options.emailSel,b).keypress(function(c){if(c.keyCode==13){c.preventDefault();
if(a.isOpen()){a.processLogin()}}});this._initResetSection()},isOpen:function(){return this.loginWrapper.is(".open")},openLogin:function(){this.loginWrapper.addClass("open");
this.passwordRow.slideDown("fast");this.nonloginWrapper.animate({opacity:"0.4",duration:"fast"})},closeLogin:function(){this.passwordRow.slideUp("fast",(function(){this.loginWrapper.removeClass("open")
}).bind(this));this.nonloginWrapper.animate({opacity:"1",duration:"fast"})},processLogin:function(){var a=[];a.push({name:"email",value:$(this.options.emailSel,this.context).val()});
a.push({name:"password",value:$(this.options.passwordSel,this.context).val()});if(this.loginBtn.is(".mode-advanced")){a.push({name:"mode",value:"advanced"})
}$.ajax({url:BASE_URL+"login/inline-login",type:"POST",data:a,dataType:"json",context:this,success:function(b){var c=$(b.html);
if(b.person_id){DeskPRO_Window.initFeatures(c);$("#dp_inline_login_row").replaceWith(c);this.nonloginWrapper.css({opacity:"1"})
}else{$("#dp_inline_login_row").replaceWith(c);$(".dp-inline-login-pass",c).show()}if(b.sections_replace){Object.each(b.sections_replace,function(d,e){$("#"+e).empty().replaceWith(d)
})}this._initLoginForm(c);this.fireEvent("success",[b,this])}})},_initResetSection:function(){$(".forgot",this.el).click((function(a){a.preventDefault();
this.showReset()}).bind(this));$(".back",this.resetSection).click((function(a){this.hideReset()}).bind(this));$(".dp-do-send",this.resetSection).click((function(a){a.preventDefault();
this.sendReset()}).bind(this))},sendReset:function(){this.resetSection.addClass("loading");$.ajax({url:BASE_URL+"login/reset-password/send",type:"POST",data:{email:$(this.options.emailSel,this.el).val()},dataType:"json",context:this,success:function(){this.resetSection.removeClass("loading");
var b=$(".dp-reset-desc",this.resetSection);var a=$(".dp-reset-sent",this.resetSection);b.slideUp("fast",function(){a.slideDown()
})}})},showReset:function(){this.loginSection.slideUp("fast",(function(){this.resetSection.slideDown("fast")}).bind(this))
},hideReset:function(a){if(a){this.resetSection.hide();this.loginSection.show();$(".dp-reset-desc",this.resetSection).show();
$(".dp-reset-sent",this.resetSection).hide()}else{this.resetSection.slideUp("fast",(function(){this.loginSection.slideDown("fast");
$(".dp-reset-desc",this.resetSection).show();$(".dp-reset-sent",this.resetSection).hide()}).bind(this))}}});Orb.createNamespace("DeskPRO.Form");
DeskPRO.Form.FormValidator=new Orb.Class({Implements:[Orb.Util.Events],initialize:function(b){var a=this;this.el=$(b);if(this.el.is("form")){this.el.submit(function(c){a.validateAll();
if(a.hasErrors()){c.preventDefault()}});$("[required]",this.el).each(function(){$(this).attr("required",false)})}this.refreshElements()
},refreshElements:function(){var a=this.el;this.formElements=$("[data-field-validators]",this.el).each(function(){var g,c,b,e,d;
g=$(this).data("field-validators").split(",");b=[];for(c=0;c<g.length;c++){if($(this).data("field-validators-inst")){Array.each($(this).data("field-validators-inst"),function(h){h.destroy()
});$(this).data("field-validators-inst",null)}var e=g[c].trim();var d=Orb.getNamespacedObject(e);if(!d){console.error("Unknown form validator `%s` on element %o",e,this);
continue}var f=new d($(this));b.push(f)}if(b.length){$(this).data("field-validators-inst",b)}})},validateAll:function(){this.formElements.each(function(){var a,b;
if(!$(this).is(":visible")){return}a=$(this).data("field-validators-inst");for(b=0;b<a.length;b++){a[b].validate("submit")
}})},hasErrors:function(){if($(".dp-error:visible",this.el).length>0){return true}return false}});Orb.createNamespace("DeskPRO.Form");
DeskPRO.Form.FieldValidator=new Orb.Class({Implements:[Orb.Util.Events],initialize:function(a){this.el=$(a);if(this.el.data("val-wrap-sel")){this.wrapper=this.el.closest(this.el.data("val-wrap-sel"))
}else{this.wrapper=this.el.closest(".dp-form-row")}this.init()},init:function(){},setErrorCodes:function(a){this.wrapper.removeClass("dp-error-"+this._getMyErrorCodes().join(" dp-error-"));
if(a&&a.length){if(typeof a=="string"){a=[a]}this.wrapper.addClass("dp-error-"+a.join(" dp-error-"));this.wrapper.addClass("dp-error")
}else{if(!this.wrapper.is('[class*="dp-error-"]')){this.wrapper.removeClass("dp-error")}}},hasError:function(){if(this.wrapper.is(".dp-error-"+this._getMyErrorCodes().join(", .dp-error-"))){return true
}return false},validate:function(a){},_getMyErrorCodes:function(){},destroy:function(){}});Orb.createNamespace("DeskPRO.Form");
DeskPRO.Form.LengthValidator=new Orb.Class({Extends:DeskPRO.Form.FieldValidator,init:function(){var a=this;this.min=1;this.max=-1;
this.excludeBlank=true;if(this.el.data("min-len")){this.min=parseInt(this.el.data("min-len"))}if(this.el.data("max-len")){this.max=parseInt(this.el.data("max-len"))
}if(this.el.data("exclude-blank")){this.excludeBlank=parseInt(this.el.data("exclude-blank"));this.excludeBlank=this.excludeBlank?true:false
}this.el.change(function(){a.validate()})},validate:function(c){var a=0;if(this.el.is("select")){if(this.excludeBlank){$("option:selected",this.el).each(function(){if($(this).val()!="0"&&$(this).val().trim()!==""){a++
}})}else{a=$("option:selected",this.el).length}}else{a=this.el.val().trim().length}var b=[];if(this.min>-1&&a<this.min){b.push("len_too_short")
}if(this.max>-1&&a>this.max){b.push("len_too_long")}this.setErrorCodes(b)},_getMyErrorCodes:function(){return["len_too_long","len_too_short"]
}});Orb.createNamespace("DeskPRO.Form");DeskPRO.Form.EmailValidator=new Orb.Class({Extends:DeskPRO.Form.FieldValidator,init:function(){var a=this;
this.el.change(function(){a.validate("change")})},validate:function(b){var a=0;var c=this.el.val().trim();if(c.length<3||c.indexOf("@")<1){this.setErrorCodes(["invalid_email"])
}else{this.setErrorCodes([])}},_getMyErrorCodes:function(){return["invalid_email"]}});Orb.createNamespace("DeskPRO.Form");
DeskPRO.Form.TwoLevelSelectValidator=new Orb.Class({Extends:DeskPRO.Form.FieldValidator,init:function(){var a=this;this.parentSel=$("select.parent-option",this.el);
this.childSel=$(".dp-sub-options > select",this.el);this.parentSel.change(function(){a.validate("change")});this.childSel.change(function(){a.validate("change")
})},validate:function(b){if(b=="change"&&!this.hasError()){return}var a=[];var c=this.childSel.filter(":visible");if(c.length){var d=$("option:selected",c).val();
if(d===""||d=="0"){a.push("select_child_value")}}else{var d=$("option:selected",this.parentSel).val();if(d===""||d=="0"){a.push("select_value")
}}this.setErrorCodes(a)},_getMyErrorCodes:function(){return["select_value","select_child_value"]}});