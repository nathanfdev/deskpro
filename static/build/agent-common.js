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
}}else{b=null}})}};Orb.createNamespace("Orb.Util");Orb.Util.EventObj=new Orb.Class({Implements:[Orb.Util.Events,Orb.Util.Options],initialize:function(a){this.setOptions(a)
}});Orb.createNamespace("Orb.Util");Orb.Util.TimeAgo={_watchEls:[],_watchTimer:null,refreshPeriod:60000,phrases:{sec_less:"less than a second",sec:"1 second",secs:"{0} seconds",min:"1 minute",mins:"{0} minutes",hour:"1 hour",hours:"{0} hours",day:"1 day",days:"{0} days",week:"1 week",weeks:"{0} weeks",month:"1 month",months:"{0} months",year:"1 year",years:"{0} years",ago:"ago"},get:function(a){return this.getForMs(this.getDateDiff(a))
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
DeskPRO.ElementHandler_Exec=function(a){$("[data-element-handler]:not(.with-handler)",a||document).each(function(){var d=$(this);
var c=d.data("element-handler");var b=Orb.getNamespacedObject(c);if(!b){console.error("Unknown element handler `%s` on element %o",c,this);
return}if(!d.attr("id")){d.attr("id",Orb.getUniqueId("dp_"))}var e=new b(d);d.addClass("with-handler")})};DeskPRO.ElementHandler=new Orb.Class({Implements:[Orb.Util.Events,Orb.Util.Options],initialize:function(a){this.el=a;
this.options={};this.childHandlers={};this.parentHandlerElement=null;this.init();this.el.data("handler",this);var c=true;
if(this.el.data("register-handler")){var b=this.el.data("register-handler");if(b=="1"||!b.length||b=="yes"||b=="true"){b=".with-handler"
}var d=this.el.closest(b);if(d.length){if(d.data("handler")){this.parentHandlerElement=d;d.data("handler")._registerChildHandler(this.el)
}else{console.warn("Parent handler element %s has no handler object on element %o and handler %o",b,this.el,this)}c=false
}else{console.warn("Unknow parent handler element %s on element %o and handler %o",b,this.el,this)}}if(c){this.initPage()
}},init:function(){},initPage:function(){},_registerChildHandler:function(b){this.childHandlers[b.attr("id")]=b;var a=this.registerChildHandler(b.data("handler"),b.data("handler").getHandlerName(),b)||{};
this.fireEvent("childHandler",[b,a,this]);b.data("handler").setParentReturnOptions(a)},registerChildHandler:function(c,a,b){return{}
},setParentReturnOptions:function(a){this.setOptions(a);this.fireEvent("parentReturn",[a,this.parentHandlerElement,this]);
this.initPage()},getHandlerName:function(){return"element_handler"}});Orb.createNamespace("DeskPRO.Agent.ElementHandler");
DeskPRO.Agent.ElementHandler.TwitterFeed=new Orb.Class({Extends:DeskPRO.ElementHandler,init:function(){this.twitterUsername=this.el.data("twitter-username");
this.tpl=DeskPRO_Window.util.getPlainTpl($(".twitter-list-item-tpl",this.el));this.list=$(".twitter-list",this.el);this.limit=parseInt(this.el.data("tweet-limit"))||5
},initPage:function(){var a=function(){return{entities:function(b){return b.replace(/(&[a-z0-9]+;)/g,function(c){return ENTITIES[c]
})},link:function(b){return b.replace(/[a-z]+:\/\/([a-z0-9-_]+\.[a-z0-9-_:~\+#%&\?\/.=]+[^:\.,\)\s*$])/ig,function(c,d){return'<a title="'+c+'" href="'+c+'">'+((d.length>36)?d.substr(0,35)+"&hellip;":d)+"</a>"
})},at:function(b){return b.replace(/(^|[^\w]+)\@([a-zA-Z0-9_]{1,15}(\/[a-zA-Z0-9-_]+)*)/g,function(c,e,d){return e+'@<a href="http://twitter.com/'+d+'">'+d+"</a>"
})},hash:function(b){return b.replace(/(^|[^&\w'"]+)\#([a-zA-Z0-9_^"^<]+)/g,function(c,e,d){return c.substr(-1)==='"'||c.substr(-1)=="<"?c:e+'#<a href="http://search.twitter.com/search?q=%23'+d+'">'+d+"</a>"
})},clean:function(b){return this.hash(this.at(this.link(b)))}}}();$.ajax({url:"http://api.twitter.com/1/statuses/user_timeline.json?screen_name="+this.twitterUsername,dataType:"jsonp",context:this,success:function(b){Array.each(b,function(f,c){if(c>this.limit){return false
}var d=$(this.tpl);var e=f.text;e=a.clean(e);$(".tweet",d).html(e);this.list.append(d);this.el.show()},this)}})}});Orb.createNamespace("DeskPRO.Agent.ElementHandler");
DeskPRO.Agent.ElementHandler.FormSaver=new Orb.Class({Extends:DeskPRO.ElementHandler,init:function(){var a=this;this.textarea=$("textarea",this.el);
this.list=null;this.resultHtmlKey=this.el.data("form-result-html-key")||"html";if(this.el.data("form-list-selector")){this.list=this.el.closest(this.el.data("form-list-selector"))
}console.log(this.list);this.url=this.el.data("form-save-url");this.statusSave=$("header .save",this.el);this.statusSaved=$("header .saved",this.el);
this.statusSaving=$("header .is-loading",this.el);this.statusSave.click(function(b){b.preventDefault();a.save()});this.textarea.change(this.touch.bind(this));
this.textarea.keypress(this.touch.bind(this));this.countEl=null;if(this.el.data("form-count-el")){this.countEl=$(this.el.data("form-count-el"))
}},touch:function(){this.statusSave.show();this.statusSaved.hide();this.statusSaving.hide()},save:function(){this.statusSave.hide();
this.statusSaved.hide();this.statusSaving.show();var a=$("input, textarea, select",this.el).serializeArray();$.ajax({url:this.url,type:"POST",data:a,dataType:"json",context:this,complete:function(){this.statusSave.hide();
this.statusSaved.show();this.statusSaving.hide();window.setTimeout((function(){this.statusSaved.fadeOut("slow")}).bind(this),1000)
},success:function(c){if(this.list){var b=$(c[this.resultHtmlKey]);DeskPRO_Window.initInterfaceServices(b);if(this.el.parent().get(0)==this.list.get(0)){b.insertBefore(this.el)
}else{this.list.append(b)}this.textarea.val("")}if(this.countEl){DeskPRO_Window.util.modCountEl(this.countEl,"+")}}})}});
Orb.createNamespace("DeskPRO.Agent.ElementHandler");DeskPRO.Agent.ElementHandler.TicketReplyBox=new Orb.Class({Extends:DeskPRO.ElementHandler,init:function(){this.baseId=this.el.data("base-id");
this.headerRows=$("")},initPage:function(){var a=this;this.getElById("replybox_replytab_btn").click(function(){$(this).addClass("on");
a.getElById("replybox_notetab_btn").removeClass("on");$(".hide-note:not(.is-hidden)",a.el).show();a.getElById("is_note").val("0")
});this.getElById("replybox_notetab_btn").click(function(){$(this).addClass("on");a.getElById("replybox_replytab_btn").removeClass("on");
$(".hide-note",a.el).hide();a.getElById("is_note").val("1")});$(".expander").click(function(){var f=$($(this).data("target"));
if(f.is(":visible")){$(this).removeClass("expanded").addClass("is-hidden");f.slideUp("fast")}else{$(this).addClass("expanded").removeClass("is-hidden");
f.slideDown("fast")}});this.getElById("cc_input").tokenField();this.el.fileupload({url:this.el.data("upload-url"),dropZone:this.el,autoUpload:true,uploadTemplate:$(".template-upload",this.replyBox),downloadTemplate:$(".template-download",this.replyBox)});
this.el.bind("fileuploaddone",function(){a.getElById("attach_row").slideDown().removeClass("is-hidden")});this.el.bind("fileuploadstart",function(){a.getElById("attach_row").slideDown().removeClass("is-hidden")
});$(".option-buttons",this.el).delegate("li.toggle","click",function(){var f=$(":checkbox",this);if(!f.length){return}if(f.is(":checked")){f.attr("checked",false);
$(this).removeClass("on")}else{f.attr("checked",true);$(this).addClass("on")}});this.snippetsViewer=new DeskPRO.Agent.Widget.SnippetViewer({viewUrl:this.el.data("snippet-viewer-url"),triggerElement:this.getElById("text_snippets_btn"),onSnippetClick:function(f){a.getElById("replybox_txt").val(a.getElById("replybox_txt").val()+"\n\n"+f.snippet)
}});var e=this.getElById("status_detail");this.statusMenu=new DeskPRO.UI.Menu({triggerElement:$(".status-trigger",e),menuElement:this.getElById("status_menu"),onItemClicked:function(g){var f=$(g.itemEl);
var h=f.data("status");if(h=="no-change"){a.getElById("ticket_do_status").val(0);e.removeClass("changed")}else{$(".new-val-label",e).text(f.text().trim());
a.getElById("ticket_do_status").val(1);a.getElById("ticket_status").val(h);e.addClass("changed")}}});var c=this.getElById("assign_detail_agent");
var b=this.getElById("assign_detail_agent_team");var d=this.getElById("assign_detail_followers");this.assignOptionBox=new DeskPRO.UI.OptionBox({element:this.getElById("agent_selector"),trigger:this.getElById("assign_btn"),onClose:function(g){var h=g.getAllSelected();
var f=parseInt(a.getElById("exist_agent_id").val());var j=parseInt(a.getElById("exist_agent_team_id").val());var i=parseInt(h.agents||0);
if(i==f){c.removeClass("changed");a.getElById("do_agent_id").val("0")}else{c.addClass("changed");a.getElById("do_agent_id").val("0");
a.getElById("agent_id").val(i);var n=$(".agent-label-"+i,a.getElById("agent_selector")).text().trim();$(".new-val-label",c).text(n)
}var o=parseInt(h.teams||0);if(o==j){b.removeClass("changed");a.getElById("do_agent_team_id").val("0")}else{b.addClass("changed");
a.getElById("do_agent_team_id").val("1");a.getElById("agent_team_id").val(o);var n=$(".agent-team-label-"+o,a.getElById("agent_selector")).text().trim();
$(".new-val-label",b).text(n)}var m=[];var k=$(".inputs",d).empty();Array.each(h.followers,function(p){var q=$(".agent-part-label-"+p,a.getElById("agent_selector")).text().trim();
m.push(q);var r=$('<input type="hidden" name="agent_parts[]" value="'+p+'" />');k.append(r)});if(m.length){$(".no-followers",d).hide();
$(".is-followers",d).show().find(".names").text(m.join(", "))}else{$(".no-followers",d).show();$(".is-followers",d).text("").hide()
}}});this.el.submit(function(f){f.preventDefault();f.stopPropagation()});this.getElById("send_btn").click(function(f){f.preventDefault();
f.stopPropagation();var g=a.el.serializeArray();a.el.trigger("replyboxsubmit",[g,a])})},getElById:function(b){var a=$("#"+this.baseId+"_"+b);
return a},destroy:function(){}});Orb.createNamespace("DeskPRO.Agent.ElementHandler");DeskPRO.Agent.ElementHandler.TabBox=new Orb.Class({Extends:DeskPRO.ElementHandler,initPage:function(){var a=$("nav ul",this.el).first();
this.tabs=new DeskPRO.UI.SimpleTabs({triggerElements:$("li",a),context:this.el})},destroy:function(){if(this.tabs){this.tabs.destroy();
this.tabs=null}this.el=null}});Orb.createNamespace("DeskPRO.Agent.PageHelper");DeskPRO.ElementHandler.ListRadio=new Orb.Class({Extends:DeskPRO.ElementHandler,init:function(){var a=this;
this.list=$("ul, ol",this.el).first();this.list.delegate("li","click",function(){$("li",a.list).removeClass("on");$(this).addClass("on");
a.el.trigger("listradiochange",[$(this).data("value"),$(this),this])})}});Orb.createNamespace("DeskPRO");DeskPRO.MessageBroker=new Orb.Class({Implements:[Orb.Util.Events],sendMessage:function(a,d){this.fireEvent(a,[d,a]);
var b=a.split(".");var c=null;while(b.pop()){c=b.join(".")+".*";this.fireEvent(c,[d,c])}},addMessageListener:function(b,d,c,a){this.addEvent(b,d,c,a)
},removeMessageListener:function(a,c,b){this.removeEvent(a,c,b)},removeTaggedListeners:function(a){this.removeTaggedEvents(a)
}});Orb.createNamespace("DeskPRO");DeskPRO.IntervalCaller=new Orb.Class({Implements:[Orb.Util.Options],initialize:function(a){this.options={touchResets:true,touchRequired:true,resetTimeForce:0,callback:function(){},timeout:null,autostart:true};
this.setOptions(a);if(this.options.autostart){this.start()}this.touched=false;this.paused=false;this.lastTime=new Date()},start:function(){if(this.timer){window.clearTimeout(this.timer);
this.timer=null}this.timer=window.setTimeout(this.exec.bind(this),this.options.timeout)},stop:function(){if(this.timer){window.clearTimeout(this.timer);
this.timer=null}},touch:function(){this.touched=true;if(this.options.touchResets){if(this.options.resetTimeForce){var a=new Date();
var b=a.getTime()-this.lastTime.getTime();if(b>this.options.resetTimeForce){return}}this.start()}},exec:function(a){if(this.lastTime){this.lastTime=new Date()
}if(!a&&this.options.touchRequired&&!this.touched){this.start();return}this.touched=false;this.options.callback();this.start()
},execNow:function(){this.exec()},destroy:function(){this.stop();this.options=null;this.lastTime=null}});Orb.createNamespace("DeskPRO.AjaxPoller");
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
this.messageBroker=b;this.addEvent("ajaxSuccess",this._sendMessages,this)},getMessageBroker:function(){return this.messageBroker
},_sendMessages:function(b){if(b.messages===undefined||typeOf(b.messages)!="array"){return}var a=null;while(a=b.messages.shift()){this.messageBroker.sendMessage(a[0],a[1])
}}});Orb.createNamespace("DeskPRO.MessageChanneler");DeskPRO.MessageChanneler.AbstractChanneler=new Orb.Class({Implements:[Orb.Util.Options],initialize:function(b,a){this.channels=[];
this.options={};this.messageBroker=b;if(a){this.setOptions(a)}this._init()},_init:function(){},subscribeChannel:function(b,c,a){},_doneSubscribeChannels:function(a){Array.each(a,function(b){this.channels.include(b)
},this)},unsubscribeChannel:function(a){},_doneSubscribeChannels:function(a){Array.each(a,function(b){this.channels.erase(b)
},this)},sendMessage:function(b,a){if(DeskPRO_Window&&DeskPRO_Window.getDebug("logClientMessages")){console.log("channel(%s): %o",b,a)
}this.messageBroker.sendMessage(b,a)}});Orb.createNamespace("DeskPRO.MessageChanneler");DeskPRO.MessageChanneler.AjaxChanneler=new Orb.Class({Extends:DeskPRO.MessageChanneler.AbstractChanneler,_init:function(){this._add_subs=[];
this._add_subs_timeout=null;this._del_subs=[];this._del_subs_timeout=null;this.lastMessageId=-1;this.poller=new DeskPRO.AjaxPoller.Poller({ajaxUrl:this.options.ajaxMessagesUrl,interval:5000,ajaxType:"GET"});
this.poller.addData((function(){if(!this.lastMessageId){return null}return{since:this.lastMessageId}}).bind(this),"since",{recurring:true});
this.poller.addData({is_initial_poll:1},"is_initial_poll");this.poller.addEvent("ajaxSuccess",this.handleMessageAjax.bind(this));
if(this.options.lastMessageId){this.lastMessageId=this.options.lastMessageId}},handleMessageAjax:function(a){if(a.messages){Array.each(a.messages,function(b){if(b[0]<=this.lastMessageId&&(!b[3]||!b[3]["offline_messsage"])){return
}this.lastMessageId=b[0];this.sendMessage(b[1],b[2])},this)}if(a.last_id&&a.last_id>this.lastMessageId){this.lastMessageId=a.last_id
}},subscribeChannel:function(b,c,a){this._add_subs.include(b);if(this._add_subs_timeout){window.clearTimeout(this._add_subs_timeout)
}this._add_subs_timeout=this._sendSubscribeChannels.delay(300,this);if(c){this.messageBroker.addMessageListener(b,c,a)}},_sendSubscribeChannels:function(){var a=[];
Array.each(this._add_subs,function(b){a.push({name:"channels[]",value:b})});this._add_subs=[];$.ajax({url:this.options.ajaxSubscribeUrl,type:"POST",data:a,dataType:"json",context:this,success:function(b){this._doneSubscribeChannels(b.subscribed_channels)
}})},unsubscribeChannel:function(a){this._del_subs.include(a);if(this._del_subs_timeout){window.clearTimeout(this._del_subs_timeout)
}this._del_subs_timeout=this._sendUnsubscribeChannels.delay(300,this)},_sendUnsubscribeChannels:function(){var a=[];Array.each(this._add_subs,function(b){a.push({name:"channels[]",value:b})
});this._add_subs=[];$.ajax({url:this.options.ajaxUnsubscribeUrl,type:"POST",data:a,dataType:"json",context:this,success:function(b){this._doneUnsubscribeChannels(b.unsubscribed_channels)
}})}});