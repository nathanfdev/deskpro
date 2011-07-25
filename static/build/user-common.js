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
}if(c.indexOf("?")===-1){c+="?"+d}else{c+="&"+d}return c};Orb.resourceLoader={batches:{},batchesCallback:{},loadScript:function(a,b){this.loadBatch([{type:"script",url:a}],b)
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
if(e==1){setTimeout(function(){if(e==1){a.call(d,f)}else{b.call(d,f)}e=0},c)}})}})};(function($,h,c){var a=$([]),e=$.resize=$.extend($.resize,{}),i,k="setTimeout",j="resize",d=j+"-special-event",b="delay",f="throttleWindow";
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
if(this.addEvent){for(var c in a){if(typeof a[c]!="function"||!(/^on[A-Z]/).test(c)){continue}this.addEvent(c,a[c]);delete a[c]
}}this.options=a;return this},getOption:function(b,a){if(typeof this.options[b]===undefined){return a}return this.options[b]
}};Orb.createNamespace("Orb.Util");Orb.Util.Events={__initEventsObj:function(){if(!this.__events){this.__events={}}},normalizeEventName:function(a){return a.toLowerCase().replace(/^on/,"")
},addEvent:function(c,b,a){c=this.normalizeEventName(c);this.__initEventsObj();this.__events[c]=(this.__events[c]||[]).include(b);
if(a){b.internal=true}return this},addEvents:function(a){for(var b in a){this.addEvent(b,a[b])}return this},fireEvent:function(d,b,a){d=this.normalizeEventName(d);
this.__initEventsObj();var c=this.__events[d];if(!c){return this}b=Array.from(b);c.each(function(e){if(a){e.delay(a,this,b)
}else{e.apply(this,b)}},this);return this},removeEvent:function(d,c){d=this.normalizeEventName(d);this.__initEventsObj();
var b=this.__events[d];if(b&&!c.internal){var a=b.indexOf(c);if(a!=-1){delete b[a]}}return this},removeEvents:function(c){var d;
if(typeOf(c)=="object"){for(d in c){this.removeEvent(d,c[d])}return this}this.__initEventsObj();for(d in this.__events){if(c&&c!=d){continue
}var b=this.__events[d];for(var a=b.length;a--;){if(a in b){this.removeEvent(d,b[a])}}}return this}};Orb.createNamespace("Orb.Compat.WebForms");
Orb.Compat.WebForms.isPlaceholderSupported=function(){this.isSupported=null;if(this.isSupported===null){this.isSupported=("placeholder" in document.createElement(input.tagName))
}return this.isSupported};Orb.Compat.WebForms.placeholder=function(a){if(!a){return null}input_col=$(a);if(!input_col.length){return null
}input_col.each(function(){var b=$(this);if(b.placeholder&&this.isPlaceholderSupported()){return}var c=b.attr("placeholder");
if(!c||!c.length){return}if(b.is(".has-placeholder")){return}b.addClass("has-placeholder");if(b.val()===""||b.val()==c){b.val(c);
b.addClass("placeholder-visible")}b.focus(function(){if(b.is(".placeholder-visible")){b.val("");b.removeClass("placeholder-visible")
}});b.blur(function(){if(b.val()===""){b.addClass("placeholder-visible");b.val(c)}else{b.removeClass("placeholder-visible")
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
}Array.each(this.tagged[a],function(b){this.removeMessageListener(b[0],b[1])},this)}});Orb.createNamespace("DeskPRO");DeskPRO.BasicWindow=new Orb.Class({Implements:[Orb.Util.Options],initialize:function(a){this.DEBUG={};
this.options=this.getDefaultOptions();this.registry={};this.messageBroker=this.messageBroker=new DeskPRO.MessageBroker();
if(a){this.setOptions(a)}this.init()},init:function(){},initPage:function(){},getDefaultOptions:function(){return{}},get:function(b,a){if(this.registry[b]===undefined){return a
}return this.registry[b]},set:function(b,a){this.registry[b]=a},getMessageBroker:function(){return this.messageBroker},getDebug:function(a){if(this.DEBUG[a]===undefined){return false
}return this.DEBUG[a]}});Orb.createNamespace("DeskPRO.UI");DeskPRO.UI.SimpleTabs=new Orb.Class({Implements:[Orb.Util.Options,Orb.Util.Events],initialize:function(b){this.options={triggerElements:".tab-trigger",activeClassname:"on",context:document};
this.lastActiveTab=null;this.triggerEls=null;if(b){this.setOptions(b)}this.triggerEls=this.options.triggerElements;if(typeOf(this.triggerEls)=="string"){this.triggerEls=$(this.triggerEls,this.options.context)
}var a=this;this.triggerEls.click(function(d){a._handleTabClick(this,d)});var c=this.triggerEls.filter(".on:first");if(!c.length){c=this.triggerEls.first()
}this.activateTab(c)},_handleTabClick:function(b,c){var a=$(b);this.activateTab(a,c)},activateTab:function(c,b){var a={event:b||null,tabEl:c,lastTabEl:this.lastActiveTab,manager:this,cancel:false};
this.fireEvent("beforeTabSwitch",a);if(a.cancel){return}delete a.cancel;if(this.lastActiveTab){this.lastActiveTab.removeClass(this.options.activeClassname);
this.getContentElFromTab(this.lastActiveTab).removeClass(this.options.activeClassname).hide();this.lastActiveTab=null}this.lastActiveTab=c;
this.lastActiveTab.addClass(this.options.activeClassname);this.getContentElFromTab(this.lastActiveTab).addClass(this.options.activeClassname).show();
this.fireEvent("tabSwitch",a)},getContentElFromTab:function(b){if(!b.data("tab-for")){console.warn("tab has no tab-for: %o",b);
return $()}var a=$(b.data("tab-for"),this.options.context);if(a.length<1){console.warn("no tab content exists for tab: %o",b)
}return a}});Orb.createNamespace("DeskPRO.UI");DeskPRO.UI.Overlay=new Orb.Class({Implements:[Orb.Util.Options,Orb.Util.Events],initialize:function(a){this.objectId=null,this.options={triggerElement:null,contentMethod:"element",contentElement:null,contentAjax:{url:"",type:"GET",dataType:"html"},iframeUrl:null,iframeId:false,maxHeight:700,maxWidth:900,destroyOnClose:false,customClassname:"",isModal:true,zIndex:100000,escapeClose:true,modalClickClose:true,objectGroup:"default",addClose:true};
this.isThisDestroyed=false;this.hasInit=false;this.hasSentAjax=false;this.elements={};if(a){this.setOptions(a)}if(this.options.triggerElement){this.setupTriggerElement($(this.options.triggerElement))
}if(this.options.escapeClose){$(document).keydown((function(b){if(b.which==27){this.closeOverlay()}}).bind(this))}},isOpen:function(){return this.isOverlayOpen()
},isOverlayOpen:function(){if(!this.hasInit){return false}return this.elements.wrapper.is(":visible")},open:function(){return this.openOverlay()
},openOverlay:function(){if(!this.initOverlay()){return}if(this.isOverlayOpen()){return}this.fireEvent("beforeOverlayOpened",{overlay:this});
if(!this.options.zIndex){this.options.zIndex=Orb.findHighestZindex()+1}this.elements.modal.css({"z-index":this.options.zIndex,position:"absolute",top:0,right:0,bottom:0,left:0});
this.elements.modal.fadeIn(200);if(this.options.contentMethod=="iframe"){var b=$(window).width()-250;var e=$(window).height()-150;
if(b>this.options.maxWidth){b=this.options.maxWidth}if(e>this.options.maxHeight){e=this.options.maxHeight}$("iframe:first",this.elements.wrapper).css({width:b,height:e});
var a=($(window).width()-this.elements.wrapperOuter.outerWidth())/2;var i=($(window).height()-this.elements.wrapperOuter.outerHeight())/2;
this.elements.wrapperOuter.css({left:a,top:i})}else{var b=this.elements.wrapperOuter.outerWidth();var f=$(window).width();
var c=(f/2)-(b/2);var e=this.elements.wrapperOuter.outerHeight();var g=$(window).height();var d=(g/2)-(e/2);this.elements.wrapperOuter.css({top:d,left:c})
}this.elements.wrapperOuter.css({"z-index":this.options.zIndex+1,position:"absolute",left:c});this.elements.wrapperOuter.fadeIn(450,(function(){this.fireEvent("overlayOpened",{overlay:this})
}).bind(this))},close:function(){return this.closeOverlay()},closeOverlay:function(){if(!this.isOverlayOpen()){return}var a={overlay:this,cancelClose:false};
this.fireEvent("beforeOverlayClosed",a);if(a.cancelClose){return}this.elements.modal.fadeOut(450);this.elements.wrapperOuter.fadeOut(200);
this.fireEvent("overlayClosed",{overlay:this});if(this.options.destroyOnClose){this.destroy()}},initOverlay:function(){if(this.hasInit){return true
}if(this.options.isModal){this.elements.modal=$('<div class="deskpro-overlay-overlay '+this.options.customClassname+'" style="display:none" />');
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
},initPage:function(){if(this.PAGE){this.PAGE.initPage()}this.elementHandlers={};this.initFeatures(document)},initFeatures:function(b){var a=this;
$(".with-handler[data-element-handler]",b).each(function(){var e=$(this);var d=e.data("element-handler");var c=Orb.getNamespacedObject(d);
if(!c){console.error("Unknown portal handler `%s` on element %o",d,this);return}if(!e.attr("id")){e.attr("id",Orb.getUniqueId("portal_"))
}var f=new c({el:e});a.elementHandlers[e.attr("id")]=f});$("a.in-overlay").click(function(f){f.preventDefault();var e=$(this);
var d=e.attr("href");if(d.indexOf("?")!==-1){d+="&_partial"}else{d+="?_partial"}var c=new DeskPRO.UI.Overlay({contentMethod:"ajax",contentAjax:{url:d},destroyOnClose:true});
c.open()})},getHandler:function(a){return this.handlers[a]},hasHandler:function(a){return !!this.handlers[a]},setPageHandler:function(a){this.PAGE=a
},getPageHandler:function(){return this.PAGE}});Orb.createNamespace("DeskPRO.User.Page");DeskPRO.User.Page.Abstract=new Orb.Class({Implements:[Orb.Util.Options],initialize:function(a){this.options=this.getDefaultOptions();
if(a){this.setOptions(a)}this.init()},init:function(){},initPage:function(){this.initFeatures()},getDefaultOptions:function(){return{}
},initFeatures:function(a){if(!a){a=document.body}$(".timeago",a).timeago()}});Orb.createNamespace("DeskPRO.User.ElementHandler");
DeskPRO.User.ElementHandler.ElementHandlerAbstract=new Orb.Class({Implements:[Orb.Util.Options],initialize:function(a){this.options={};
if(a){this.setOptions(a)}this.el=null;if(this.options.el){this.el=$(this.options.el)}this.init()},init:function(){}});Orb.createNamespace("DeskPRO.User.ElementHandler");
DeskPRO.User.ElementHandler.IdeaView=new Orb.Class({Extends:DeskPRO.User.ElementHandler.ElementHandlerAbstract,init:function(){var a=new DeskPRO.User.ElementHandler.Helper.IdeaVote();
this.btnEl=$("#submit_vote_trigger");this.btnEl.click(function(b){b.preventDefault();b.stopPropagation();a.openMenu($(this))
})}});Orb.createNamespace("DeskPRO.User.ElementHandler");DeskPRO.User.ElementHandler.Ideas=new Orb.Class({Extends:DeskPRO.User.ElementHandler.ElementHandlerAbstract,init:function(){this.useAjax=this.el.data("ajax-update");
this.initFilterForm();this.loadingTpl=$(".loading-tpl:first",this.el).detach();this.content=$(".portal_ideas:first",this.el);
var a=new DeskPRO.User.ElementHandler.Helper.IdeaVote();$(".idea-btn",this.el).click(function(b){b.preventDefault();b.stopPropagation();
a.openMenu($(this))})},initFilterForm:function(){var a=this;$("select.category_id",this.el).change(function(b){a.updateView($(this).val())
});if(this.useAjax){$("#idea_find_form_btm").submit(function(b){b.preventDefault();a.submitForm($(this))});$("a.page-link",this.el).click(function(b){b.preventDefault();
a.updateView($(this).attr("href"))})}},injectLoadingEl:function(a){var b=this.loadingTpl.clone();b.show();a.empty().append(b)
},submitForm:function(a){this.injectLoadingEl(this.content);var c=a.serializeArray();var b=a.attr("action");b=Orb.appendQueryData(b,"_partial");
$.ajax({url:b,data:c,dataType:"html",type:"POST",context:this,success:function(d){this.content.empty().html(d);DeskPRO_Window.initFeatures(content);
this.initFilterForm()}})},updateView:function(a,b){if(!this.useAjax){window.location=a;return}this.injectLoadingEl($(".content-wrapper:first",this.content));
if(b=="page"||b=="order"){var c=this.el.offset();$(document).scrollTop(c.top)}a=Orb.appendQueryData(a,"_partial");$.ajax({url:a,dataType:"html",context:this,success:function(d){this.content.empty().html(d);
DeskPRO_Window.initFeatures(this.content);this.initFilterForm()}})}});Orb.createNamespace("DeskPRO.User.ElementHandler");
DeskPRO.User.ElementHandler.MoreLoader=new Orb.Class({Extends:DeskPRO.User.ElementHandler.ElementHandlerAbstract,init:function(){this.listEl=$(".content-list:first ul:first",this.el);
this.moreWrap=$(".content-more:first",this.el);this.moreBtn=$("button:first, a.button:first",this.el).first();this.loadUrl=this.el.data("load-url");
this.currentPage=1;var a=this;this.moreBtn.click(function(b){b.preventDefault();b.stopPropagation();a.loadNextPage()})},loadNextPage:function(){if(this.moreWrap.is(".loading")){return
}this.moreWrap.addClass("loading");var a=this.loadUrl.replace("{page}",this.currentPage+1);if(a.indexOf("?")===-1){a+="?_partial=more"
}else{a+="&_partial=more"}$.ajax({url:a,dataType:"html",context:this,success:function(b){this.insertNewItems(b.trim())}})
},insertNewItems:function(b){if(b.length){var a=$(b)}else{var a=null}this.currentPage++;this.moreWrap.removeClass("loading");
if(!a||!a.length||!a.last().is(".has-more")){this.moreWrap.remove()}this.listEl.append(a)}});Orb.createNamespace("DeskPRO.User.ElementHandler.Helper");
DeskPRO.User.ElementHandler.Helper.IdeaVote=new Orb.Class({_initMenu:function(){if(this.hasInit){return}this.hasInit=true;
var a=this;this.menuEl=$(".idea_vote_menu_wrap:first");this.menuEl.detach().appendTo("body");this.menuEl.click(function(b){b.stopPropagation()
});$(".close-trigger",this.menuEl).click(function(){a.closeMenu()});$(document).click(function(){a.closeMenu()});$("li",this.menuEl).click(function(b){b.stopPropagation();
a.clickVoteOption($(this))});this.ideaEl=null},clickVoteOption:function(a){$("li",this.menuEl).removeClass("on");a.addClass("on");
var b=parseInt(this.ideaEl.data("votes-used"));var c=parseInt(a.data("num"));var d=0;d=b-c;$(".votes-count",this.ideaEl).text(parseInt($(".votes-count",this.ideaEl).text())-d);
this.ideaEl.data("votes-used",a.data("num"));$.ajax({url:this.ideaEl.data("vote-url"),data:{vote:a.data("num")},context:this})
},openMenu:function(a){this._initMenu();this.ideaEl=a;this.updateMenuDims();var b=IdeaVotesRemaining+parseInt(this.ideaEl.data("votes-used"));
if(b>IdeaVotesMaxPerIdea){b=IdeaVotesMaxPerIdea}$(".votes-allowed",this.menuEl).html(b);$("li.num",this.menuEl).hide();for(var c=1;
c<=b;c++){$("li.num-"+c,this.menuEl).show()}if(this.ideaEl.data("votes-used")){$("li",this.menuEl).removeClass("on");$("li.num-"+this.ideaEl.data("votes-used"),this.menuEl).addClass("on")
}else{$("li.num-0",this.menuEl).addClass("on")}this.menuEl.fadeIn("fast")},closeMenu:function(){this.menuEl.fadeOut("fast")
},updateMenuDims:function(){if(this.ideaEl.is(".idea-btn")){var c=this.ideaEl.offset()}else{var c=$(".idea-btn",this.ideaEl).offset()
}var b=c.top;var a=c.left;this.menuEl.css({top:b,left:a})}});