Orb.createNamespace("DeskPRO.Admin");DeskPRO.Admin.Window=new Orb.Class({Implements:[Orb.Util.Events],initialize:function(){this.DEBUG={};
this.registry={};this.messageBroker=null;this.interfaceEffects=null},initPage:function(){this.interfaceEffects=new DeskPRO.Agent.InterfaceEffects();
this.interfaceEffects.initPage();this._initBasic();this._initWindowInterface();if(typeof window.DeskPRO_Window_Init=="function"){window.DeskPRO_Window_Init()
}$(".admin-help-header .close-trigger").click(function(){$(".admin-help-header").slideUp();DeskPRO_Window.dismissHelpMessage(this)
})},_initBasic:function(){this.messageBroker=new DeskPRO.MessageBroker()},_initWindowInterface:function(){var a=new DeskPRO.Admin.WindowElement.MainMenuOpener()
},getMessageBroker:function(){return this.messageBroker},get:function(a){if(this.registry[a]===undefined){return null}return this.registry[a]
},set:function(b,a){this.registry[b]=a},getUrl:function(b,c){if(!window.DESKPRO_URL_REGISTRY[b]){console.warn("Unknown url name %s",b);
return null}var a=window.DESKPRO_URL_REGISTRY[b];if(c){Object.each(c,function(e,d){a=a.replace("{"+d+"}",e)})}return a},dismissHelpMessage:function(b){b=$(b);
var a=b.data("message-id");b.remove();if(!a){return}$.ajax({dataType:"json",url:BASE_URL+"agent/misc/dismiss-help-message/"+escape(a),type:"GET"})
}});Orb.createNamespace("DeskPRO.Admin");DeskPRO.Admin.PopoutWindow=new Class({Extends:DeskPRO.Admin.Window,initialize:function(){$("body").layout({applyDefaultStyles:false})
}});Orb.createNamespace("DeskPRO.Admin.PageHandler");DeskPRO.Admin.PageHandler.Basic=new Class({Implements:[Events],meta:{},contextEl:null,options:{},messageBroker:null,initialize:function(b,a){if(b){this.contextEl=$(b)
}else{this.contextEl=$(document.body)}a=a||{};this.options=a;var c=this.getOpenerDeskPRO();if(c){this.messageBroker=c.getMessageBroker()
}else{if(window.DeskPRO_Window){this.messageBroker=window.DeskPRO_Window.getMessageBroker()}else{if(window.DeskPRO_Page){this.messageBroker=window.DeskPRO_Page.getMessageBroker()
}else{this.messageBroker=new DeskPRO.MessageBroker()}}}},getMessageBroker:function(){return this.messageBroker},initPage:function(){},initPopoutTriggers:function(b){if(!b){b=this.contextEl
}var a=this;$(".popout-trigger",b).click(function(h){var e=$(this);h.preventDefault();var d=e.attr("href");if(!d){d=e.data("href")
}var g=700;var f=900;var i=false;if(e.data("width")){f=e.data("width")}if(e.data("height")){g=e.data("height")}if(e.data("opener-id")){i=e.data("opener-id")
}var c=new DeskPRO.UI.Overlay({contentMethod:"iframe",iframeUrl:d,iframeId:i,destroyOnClose:true,maxWidth:f,maxHeight:g});
c.openOverlay();c.addEvent("destroyed",function(){delete c;a._openedOverlay=null});a._openedOverlay=c})},_openedOverlay:null,setMetaData:function(a,b){if(b===undefined&&typeOf(a)=="object"){this.meta=Object.merge(this.meta,a)
}else{this.meta[a]=b}},getAllMetaData:function(){return this.meta},getMetaData:function(b,a){if(a===undefined){a=null}if(this.meta[b]===undefined){return a
}return this.meta[b]},handleListChange:function(c){var b=$("ul.item-list:first");var a=$("li."+c.typename+"-"+c[c.typename+"_id"]);
var d=$(c.row_html);this.initPopoutTriggers(d);if(a.length){a.replaceWith(d)}else{b.prepend(d)}},getOpenerDeskPRO:function(b){var a="DeskPRO_Page";
var d="DeskPRO_Window";var c=null;if(window.parent&&window.parent[a]){c=window.parent[a]}else{if(window.opener&&window.opener[a]){c=window.opener[a]
}else{if(window.parent&&window.parent[d]){c=window.parent[d]}else{if(window.opener&&window.opener[d]){c=window.opener[d]}}}}return c
},closeThisPopout:function(){if(this.getThisOverlay()){this.getThisOverlay().closeOverlay()}},getThisOverlay:function(){if(window.parent&&window.parent.DeskPRO_Page&&window.parent.DeskPRO_Page._openedOverlay){return window.parent.DeskPRO_Page._openedOverlay
}return null}});Orb.createNamespace("DeskPRO.Agent");DeskPRO.Agent.InterfaceEffects=new Orb.Class({Implements:[Orb.Util.Events],initialize:function(){},initPage:function(){}});
Orb.createNamespace("DeskPRO.Agent.PageFragment");DeskPRO.Agent.PageFragment.Basic=new Class({Implements:[Events,DeskPRO.Agent.Widgetable],pageUuid:null,ZONE:"agent",TYPENAME:"basic",allowDupe:false,scripts:[],stylesheets:[],html:"",meta:{},urls:{},featureSelectors:{routes:[],times:[]},initialize:function(c){this.pageUid=Orb.uuid();
if(c){this.html=c}this.addEvent("activate",(function(){DeskPRO_Window.getMessageBroker().sendMessage("page-fragment.activated",{page:this})
}).bind(this));this.addEvent("deactivate",(function(){DeskPRO_Window.getMessageBroker().sendMessage("page-fragment.deactivated",{page:this})
}).bind(this));this.addEvent("render",(function(d){this.initFeaturesOnCollection(d)}).bind(this));if(this.getMetaData("initRoutesOn")){var b=this.getMetaData("initRoutesOn");
if(typeOf(b)=="string"){b=[b]}for(var a=0;a<b.length;a++){this.featureSelectors.routes.push(b[a])}}this.addEvent("render",function(d){if(this.getMetaData("widgets")){this.initWidgets(this.getMetaData("widgets"),{personId:DESKPRO_PERSON_ID,deskproPath:BASE_URL,proxyKey:DESKPRO_PROXY_KEY});
this.initWidgetsDom(d)}});this.addEvent("activate",this.activate);this.addEvent("deactivate",this.deactivate);this.addEvent("render",this.initPage);
this.addEvent("destroy",this.destroyPage);this.init()},init:function(){},activate:function(){},deactivate:function(){},initFeaturesOnCollection:function(b,a){a=a||this.featureSelectors;
if(a.routes&&a.routes.length){this.initRoutesOnCollection($(a.routes.join(", "),b))}if(a.times&&a.times.length){this.initTimesOnCollection($(a.times.join(", "),b))
}},initRoutesOnCollection:function(a){a.click(function(){DeskPRO_Window.runPageRouteFromElement(this)})},initTimesOnCollection:function(a){a.timeago()
},setMetaData:function(a,b){if(b===undefined&&typeOf(a)=="object"){this.meta=Object.merge(this.meta,a)}else{this.meta[a]=b
}},getAllMetaData:function(){return this.meta},getMetaData:function(b,a){if(a===undefined){a=null}if(this.meta[b]===undefined){return a
}return this.meta[b]},getUrl:function(b,c){if(!this.meta.urls){console.error("Unknown url name %s (no urls set)",b);return null
}if(!this.meta.urls[b]){console.error("Unknown url name %s",b);return null}var a=this.meta.urls[b];if(c){Object.each(c,function(e,d){a=a.replace("{"+d+"}",e)
})}return a},getScripts:function(){return this.scripts},getStylesheets:function(){return this.stylesheets},getHtml:function(){return this.html
},initPage:function(a){},destroyPage:function(){}});Orb.createNamespace("DeskPRO.Agent.PageFragment.Page");DeskPRO.Agent.PageFragment.Page.Loading=new Class({Extends:DeskPRO.Agent.PageFragment.Basic,allowDupe:true,TYPENAME:"loading",initPage:function(a){this.wrapper=$(a)
}});Orb.createNamespace("DeskPRO.Agent.Notifier");DeskPRO.Agent.Notifier.Notifier=new Class({Implements:[Events,Options],options:{notifySummaryButton:null,notifyList:null},button:null,list:null,notifyTypes:[],initialize:function(b){if(b){this.setOptions(b)
}this.button=this.options.notifySummaryButton;this.list=this.options.notifyList;this.button.click(this.toggleList.bind(this));
var a=this;$("span.dismiss",this.list).live("click",function(){a.handleDismissClick($(this))});Array.each(this.notifyTypes,function(c){c.addEvent("listUpdated",this.updateListForType.bind(this))
})},handleDismissClick:function(c){var a=c.parent();var b=a.parent();var d=b.parent();a.slideUp(function(){a.remove();if(!$("li",b).length){d.slideUp(function(){d.remove()
})}})},updateSummaryLine:function(a){this.button.text(a);this.flashButton()},flashButton:function(){this.button.effect("pulsate",{},500)
},toggleList:function(){if(this.list.is(":visible")){this.hideList()}else{this.showList()}},updateListForType:function(b){var a=[];
Array.each(this.notifyTypes,function(d){var c=d.getSummary();if(c){a.push(c)}});a=a.join(", ");this.updateSummaryLine(a);
$("li > ul > li",this.list).append('<span class="dismiss">dismiss</span>')},showList:function(){if(this.list.is(":visible")){return
}this.list.css({position:"absoloute",left:0,top:0});this.list.position({my:"right top",at:"right bottom",of:this.button,offset:"1 -2"}).slideDown(300);
this.button.addClass("on")},hideList:function(){if(!this.list.is(":visible")){return}var a=this.button;this.list.slideUp(200,function(){a.removeClass("on")
})}});Orb.createNamespace("DeskPRO.Agent.Notifier.Types");DeskPRO.Agent.Notifier.Types.Abstract=new Class({Implements:[Events],sectionId:null,sectionEl:null,sectionList:null,initialize:function(){this.sectionEl=$("#"+this.sectionId);
this.sectionList=$("> ul",this.sectionEl);this._initMessageListeners()},_initMessageListeners:function(){},getSummary:function(){},updateLines:function(){},listUpdated:function(){this.fireEvent("listUpdated",this)
}});Orb.createNamespace("DeskPRO.Agent.Notifier.Types");DeskPRO.Agent.Notifier.Types.Ticket=new Class({Extends:DeskPRO.Agent.Notifier.Types.Abstract,sectionId:"notify_list_tickets",items:{},_initMessageListeners:function(){},getSummary:function(){var a=Object.getLength(this.items);
if(!a){return null}return a+" new tickets"},updateLines:function(){var b={};Object.each(this.items,function(e,f){var d=e.department_id;
var c=e.subject;if(!b[d]){b[d]=[]}b[d].push([f,c])});var a=[];Object.each(b,function(c,d){var f="";var e=0;Array.each(c,function(g){e++;
if(e>2){return false}f+="<a>"+g.subject+"</a>, "});if(e>2){f+=" and "+(e-2)+" others "}f+="in the <a>"+DeskPRO_Window.getDisplayName("department",d)+"</a> department";
a.push(f)});if(!a.length){this.sectionEl.hide();return}a="<li>"+a.join("</li><li>")+"</li>";this.sectionList.html(a)}});Orb.createNamespace("DeskPRO.Agent.WindowElement");
DeskPRO.Agent.WindowElement.MainMenuOpener=new Orb.Class({Implements:[Orb.Util.Options],initialize:function(d){this.allMenus=null;
this.options={menuSelectors:[]};var c=this;this.options.menuSelectors.push("#header .nav > .wrapper-top-bar > ul > li");this.options.menuSelectors.push("#header .box-header.notifications");
this.options.menuSelectors.push("#header .box-header.current-user");this.options.menuSelectors.push("#header .box-header.actions > .wrapper-top-bar > ul > li.with-menu");
this.options.menuSelectors.push("#window_search_type");this.options.menuSelectors.push("#window_search_form");this.allMenus=$();
for(var e=0;e<this.options.menuSelectors.length;e++){var b=this.options.menuSelectors[e];if(typeOf(b)=="string"){b=$(b)}this.allMenus=this.allMenus.add(b)
}this.allMenus.filter(":not(.no-menu)").each(function(){var a=$(this);var g=c.getMenuHandlerClass(a);if(g){var f=new g(a,{mainMenuOpener:c});
a.data("menuHandler",f)}}).click(function(f){var a=$(this);c.openMenu(a,f)});$(document).click(function(a){c.closeMenus(a)
})},getMenuHandler:function(a){a=$(a);var b=a.data("menuHandler");return b},getMenuHandlerClass:function(a){var b=a.data("menu-handler");
if(b){if(DeskPRO_Window.DEBUG.disableMenuHandlers){if(!DeskPRO_Window.DEBUG.enableMenuHandlers||DeskPRO_Window.DEBUG.enableMenuHandlers.indexOf(a.data("menu-handler"))===-1){return false
}}return Orb.getNamespacedObject(a.data("menu-handler"))}return false},closeMenus:function(b){if(b&&b._noCloseMenu){return
}var a=this.allMenus.filter(".on:first").removeClass("on");if(a.length&&a.data("menuHandler")){a.data("menuHandler").fireEvent("menuClose")
}this.allMenus.removeClass("on off");$(".wrap-dropdown",this.allMenus).hide()},openMenu:function(a,c){if(c&&$(c.target).is("a[href]")){return
}var d=true;if(a.is(".on")){d=false}this.closeMenus();if(d){$(".wrap-dropdown",a).show();a.addClass("on");var b=a.parent();
$("> li:not(.on)",b).addClass("off");if(a.data("menuHandler")){if(!a.is(".has-opened")){a.data("menuHandler").fireEvent("menuFirstOpen");
a.addClass("has-opened")}a.data("menuHandler").fireEvent("menuOpen")}}if(c){c.preventDefault();c.stopPropagation()}}});Orb.createNamespace("DeskPRO.Agent.WindowElement.MainMenu");
DeskPRO.Agent.WindowElement.MainMenu.Abstract=new Class({Implements:[Events,Options],buttonClass:null,buttonEl:null,badgeEl:null,menuEl:null,tabEl:null,options:{},initialize:function(a,c){this.buttonEl=a;
if(c){this.setOptions(c)}this.badgeEl=$("span.nav-counter:first",this.buttonEl);this.menuEl=$("div.wrap-dropdown:first",this.buttonEl);
this.tabEl=$("ul.wrap-dropdown-tabs:first",this.buttonEl);var b=this;this.tabEl.delegate("[data-route]","click",function(d){DeskPRO_Window.runPageRouteFromElement(this)
});this.menuEl.delegate("[data-route]","click",function(d){var e={event:d,cancelClose:false,preventDefault:true};b.fireEvent("clickRoute",[d,e]);
if(e.preventDefault){d.preventDefault()}if(e.cancelClose){return}DeskPRO_Window.runPageRouteFromElement(this);b.closeMenu()
});this.menuEl.click(function(d){d.stopPropagation()});this.init()},init:function(){},updateBadge:function(a){if(a==0){this.badgeEl.fadeOut(300);
return}var b=this.badgeEl;b.fadeOut(300,function(){b.html(a).fadeIn(300)})},closeMenu:function(){if(this.options.mainMenuOpener){this.options.mainMenuOpener.closeMenus()
}}});Orb.createNamespace("DeskPRO.Agent.WindowElement");DeskPRO.Agent.WindowElement.MainMenu.Notifications=new Class({Extends:DeskPRO.Agent.WindowElement.MainMenu.Abstract,init:function(){Orb.DesktopNotify.askPermission();
var a=this;$("#notifications_list").delegate("em.remove","click",function(){a.removeElement($(this).parent())});$("a.mark-all-read").click(function(b){b.preventDefault();
b.stopPropagation();a.readAll()});DeskPRO_Window.getMessageBroker().addMessageListener("tickets.new-tickets",function(c){var b="ticket:"+BASE_URL+"agent/tickets/"+c.ticket_id;
a.addItem("tickets",c.ticket_id,c.subject,b)});DeskPRO_Window.getMessageBroker().addMessageListener("ui.ticket.opened",function(b){a.removeItem("tickets",b.ticketId)
})},addItem:function(d,f,e,c){var b=(new Date()).toUTCString();var a=$('<li class="'+d+" "+d+"-"+f+'" data-type="'+d+'" data-type-id="'+f+'"><em class="remove">mark as read</em><em class="timeago">'+b+'</em><span data-route="'+c+'">'+e+"</span></li>");
$(".timeago",a).timeago();$("span",a).click(function(){DeskPRO_Window.runPageRouteFromElement(this)});$("#notifications_list").prepend(a);
this.updateCount(d,"add",1);if(DeskPRO_Window.options.desktopNotifications){Orb.DesktopNotify.show({title:e,content:"Click to open",click:function(){DeskPRO_Window.runPageRouteFromElement(a)
}})}},removeElement:function(b){var a=b.data("type");var c=b.data("type-id");this.removeItem(a,c)},removeItem:function(b,c){var a=$("."+b+"-"+c,$("#notifications_list"));
if(!a.length){return}a.remove();this.updateCount(b,"sub",1)},updateCount:function(d,g,b){var c=$("#"+d+"_counter");var f=parseInt(c.text());
var e=b;if(g=="add"){e=f+b}else{if(g=="sub"){e=f-b}}if(e<0){e=0}c.text(e);if(e>0){c.parent().parent().show().addClass("on")
}else{c.parent().parent().hide().removeClass("on")}var a=$("li.on",$("#notifications_wrap .noti-menu"));if(!a.length){$("#notifications_wrap").hide()
}else{$("#notifications_wrap").show()}},readAll:function(){$("#notifications_list").empty();$("#notifications_wrap ul.noti-menu > li span.counter").text("0");
$("#notifications_wrap ul.noti-menu > li").hide();$("#notifications_wrap").hide()}});Orb.createNamespace("DeskPRO.Agent.WindowElement.MainMenu");
DeskPRO.Agent.WindowElement.MainMenu.SearchBoxResults=new Class({Extends:DeskPRO.Agent.WindowElement.MainMenu.Abstract,menuHasResults:false,init:function(){var a=this;
$("#window_search_box").focus(function(b){if(a.shouldShowMenu()&&!$("#window_search_form .search-drop:first").is(":visible")){a.options.mainMenuOpener.openMenu($("#window_search_form"),b)
}}).click(function(b){b.stopPropagation()}).keypress(this.queryChanged.bind(this))},shouldShowMenu:function(){var a=$("#window_search_form .results, #window_search_loading");
if(!a.is(".is-vis")){return false}return true},queryChanged_timeout:null,queryChanged:function(){if(!$("#window_search_box").val().trim().length){this.clearList();
return}$("#window_search_loading").show().addClass("is-vis");if(!$("#window_search_form .search-drop:first").is(":visible")){this.options.mainMenuOpener.openMenu($("#window_search_form"))
}if(this.queryChanged_timeout){return}this.queryChanged_timeout=this.updateResults.delay(600,this)},updateResults:function(){if(this.queryChanged_timeout){window.clearTimeout(this.queryChanged_timeout);
this.queryChanged_timeout=null}var a=$("#window_search_box").val().trim();if(!a.length){this.clearList();return}$.ajax({timeout:8000,type:"POST",url:BASE_URL+"agent/people-search/search-quick",data:{term:a,format:"simplelist",limit:5},context:this,success:function(b){$("#window_search_loading").hide().removeClass("is-vis");
this._handleUserResults(b)}})},_handleUserResults:function(b){var a=$(b);if(!$("> li",a).length){a=$("<li>No people found</li>")
}this.updateList("people",b)},updateList:function(c,a){var b=$("#window_search_"+c).addClass("is-vis");var d=$("> .results-list > ul",b);
d.html(a);b.show()},clearList:function(){$("#window_search_loading").hide().removeClass("is-vis");$("#window_search_form .results").hide().removeClass("is-vis");
$("#window_search_form .results-list > ul").html("");if(this.queryChanged_timeout){window.clearTimeout(this.queryChanged_timeout);
this.queryChanged_timeout=null}this.closeMenu()}});Orb.createNamespace("DeskPRO.Agent.WindowElement.MainMenu");DeskPRO.Agent.WindowElement.MainMenu.SearchBoxType=new Class({Extends:DeskPRO.Agent.WindowElement.MainMenu.Abstract,init:function(){$("li[data-search-type]",this.buttonEl).click(function(){var b=$(this).data("search-type");
$("#window_search_type").removeClass("all users tickets").addClass(b).data("search-type",b)});var a=this;$("#search-form").submit(function(b){b.preventDefault();
a.navToFirst()})},navToFirst:function(){var a=$("#window_search_rusults_wrap .results-list li[data-route]:first");if(a.length){DeskPRO_Window.runPageRouteFromElement(a);
this.closeMenu()}}});Orb.createNamespace("DeskPRO.Admin.WindowElement");DeskPRO.Admin.WindowElement.MainMenuOpener=new Orb.Class({Extends:DeskPRO.Agent.WindowElement.MainMenuOpener,getMenuHandlerClass:function(a){var b=this.parent(a);
if(!b){b=DeskPRO.Admin.WindowElement.BasicMainMenu}return b}});Orb.createNamespace("DeskPRO.Admin.WindowElement");DeskPRO.Admin.WindowElement.BasicMainMenu=new Class({Extends:DeskPRO.Agent.WindowElement.MainMenu.Abstract,initialize:function(a,c){this.parent(a,c);
var b=this;$("ol.icon-menu > li",this.menuEl).click(function(e){var d=$("a:first",this);if(d&&d.attr("href")){e.stopPropagation();
window.location=d.attr("href")}})}});Orb.createNamespace("DeskPRO.Admin.WindowElement.MainMenu");DeskPRO.Admin.WindowElement.MainMenu.AdminSearchBoxResults=new Class({Extends:DeskPRO.Agent.WindowElement.MainMenu.Abstract,menuHasResults:false,searchEls:null,init:function(){var a=this;
$("#window_search_box").focus(function(b){if(a.shouldShowMenu()&&!$("#window_search_form .search-drop:first").is(":visible")){a.options.mainMenuOpener.openMenu($("#window_search_form"),b)
}}).click(function(b){b.stopPropagation()}).keypress(this.queryChanged.bind(this));this.searchEls=$("a[data-search-keywords]","#admin_header_menus")
},shouldShowMenu:function(){var a=$("#window_search_form .results, #window_search_loading");if(!a.is(".is-vis")){return false
}return true},queryChanged_timeout:null,queryChanged:function(){if(!$("#window_search_box").val().trim().length){this.clearList();
return}$("#window_search_loading").show().addClass("is-vis");if(!$("#window_search_form .search-drop:first").is(":visible")){this.options.mainMenuOpener.openMenu($("#window_search_form"))
}if(this.queryChanged_timeout){return}this.queryChanged_timeout=this.updateResults.delay(300,this)},updateResults:function(){if(this.queryChanged_timeout){window.clearTimeout(this.queryChanged_timeout);
this.queryChanged_timeout=null}var b=$("#window_search_box").val().trim();if(!b.length){this.clearList();return}b=b.toLowerCase();
this._handleMenuItemResults();var a=[];this.searchEls.each(function(){if($(this).data("search-keywords").indexOf(b)!=-1){a.push('<li><a href="'+$(this).attr("href")+'">'+$(this).data("search-name")+"</a></li>")
}});a=a.join("");this._handleMenuItemResults(a)},_handleMenuItemResults:function(a){if(!a){a=""}if(!a.length){a="<li>No items found</li>"
}this.updateList("menuitems",a)},updateList:function(c,a){var b=$("#window_search_"+c).addClass("is-vis");var d=$("> .results-list > ul",b);
d.html(a);b.show()},clearList:function(){$("#window_search_loading").hide().removeClass("is-vis");$("#window_search_form .results").hide().removeClass("is-vis");
$("#window_search_form .results-list > ul").html("");if(this.queryChanged_timeout){window.clearTimeout(this.queryChanged_timeout);
this.queryChanged_timeout=null}this.closeMenu()}});