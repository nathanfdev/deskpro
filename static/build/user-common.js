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
}}else{b=null}})}};Orb.createNamespace("Orb.Compat.WebForms");Orb.Compat.WebForms.isPlaceholderSupported=function(){this.isSupported=null;
if(this.isSupported===null){this.isSupported=("placeholder" in document.createElement(input.tagName))}return this.isSupported
};Orb.Compat.WebForms.placeholder=function(a){if(!a){return null}input_col=$(a);if(!input_col.length){return null}input_col.each(function(){var b=$(this);
if(b.placeholder&&this.isPlaceholderSupported()){return}var c=b.attr("placeholder");if(!c||!c.length){return}if(b.is(".has-placeholder")){return
}b.addClass("has-placeholder");if(b.val()===""||b.val()==c){b.val(c);b.addClass("placeholder-visible")}b.focus(function(){if(b.is(".placeholder-visible")){b.val("");
b.removeClass("placeholder-visible")}});b.blur(function(){if(b.val()===""){b.addClass("placeholder-visible");b.val(c)}else{b.removeClass("placeholder-visible")
}});if(b.get(0).form){$(b.get(0).form).submit(function(){if(b.is(".placeholder-visible")){b.val("")}})}})};Orb.createNamespace("DeskPRO");
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
console.trace()}return a}});Orb.createNamespace("DeskPRO.User");DeskPRO.User.Window=new Orb.Class({Extends:DeskPRO.BasicWindow,init:function(){this.PAGE=null
},initPage:function(){if(this.PAGE){this.PAGE.initPage()}this.elementHandlers={};this.initFeatures(document)},initFeatures:function(b){var a=this;
this.ideaVoteHelper=new DeskPRO.User.ElementHandler.Helper.IdeaVote();$(".with-handler[data-element-handler]",b).each(function(){var e=$(this);
var d=e.data("element-handler");var c=Orb.getNamespacedObject(d);if(!c){console.error("Unknown portal handler `%s` on element %o",d,this);
return}if(!e.attr("id")){e.attr("id",Orb.getUniqueId("portal_"))}var f=new c({el:e});a.elementHandlers[e.attr("id")]=f});
$("a.in-overlay").click(function(f){f.preventDefault();var e=$(this);var d=e.attr("href");if(d.indexOf("?")!==-1){d+="&_partial"
}else{d+="?_partial"}var c=new DeskPRO.UI.Overlay({contentMethod:"ajax",contentAjax:{url:d},destroyOnClose:true});c.open()
})},getHandler:function(a){return this.handlers[a]},hasHandler:function(a){return !!this.handlers[a]},setPageHandler:function(a){this.PAGE=a
},getPageHandler:function(){return this.PAGE}});Orb.createNamespace("DeskPRO.User.Page");DeskPRO.User.Page.Abstract=new Orb.Class({Implements:[Orb.Util.Options],initialize:function(a){this.options=this.getDefaultOptions();
if(a){this.setOptions(a)}this.init()},init:function(){},initPage:function(){this.initFeatures()},getDefaultOptions:function(){return{}
},initFeatures:function(a){if(!a){a=document.body}$(".timeago",a).timeago()}});Orb.createNamespace("DeskPRO.User.ElementHandler");
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
});this.remainCountEl=$(".dp-idea-votes-remain")},voteOnElement:function(b){var a;if(b.is(".dp-voted")){a=0}else{a=1}$.ajax({url:b.data("vote-url"),data:{rating:a},dataType:"json",context:this,success:function(c){if(c.voted){b.addClass("dp-voted")
}else{b.removeClass("dp-voted")}$("em",b).first().text(c.total_rating||0);this.remainCountEl.text(c.num_votes_remain)}})}});