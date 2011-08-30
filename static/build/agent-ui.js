Orb.createNamespace("DeskPRO");DeskPRO.BasicWindow=new Orb.Class({Implements:[Orb.Util.Options],initialize:function(a){this.DEBUG={};
this.options=this.getDefaultOptions();this.registry={};this.messageBroker=this.messageBroker=new DeskPRO.MessageBroker();
if(a){this.setOptions(a)}this.init()},init:function(){},initPage:function(){},getDefaultOptions:function(){return{}},get:function(b,a){if(this.registry[b]===undefined){return a
}return this.registry[b]},set:function(b,a){this.registry[b]=a},getMessageBroker:function(){return this.messageBroker},getDebug:function(a){if(this.DEBUG[a]===undefined){return false
}return this.DEBUG[a]}});Orb.createNamespace("DeskPRO.Agent");DeskPRO.Agent.Window=new Orb.Class({Extends:DeskPRO.BasicWindow,init:function(){this.routePrefixes={};
this.messageChanneler=null;this.poller=null;this.sections={};this.openSection=null;this.listPage=null;this.pageTabStrip=null;
this.innerLayout=null;this.notifier=null;this._alertOverlay=null;this._confirmOverlay=null;this.loadingIndicatorEl=null;this.loadingIndicatorCount=0;
this.ajaxErrorOverlay=null;this.winStateQueue=[];this.util={modCountEl:function(b,d,a){b=$(b);if(!a){a=1}var c=parseInt(b.text().trim());
if(d=="-"||d=="rem"||d=="del"){c-=a;if(c<0){c=0}}else{c+=a}b.text(c)},getPlainTpl:function(a){var a=$(a);return a.get(0).innerHTML
},showSavePuff:function(a){var b=$('<div class="load-puff" style="display: none; opacity: 0" />');b.appendTo("body");var e=a.offset();
b.css({top:e.top+15,left:e.left+a.width()-4});var d=e.top-5;var c=e.top-15;b.show();b.animate({top:d,opacity:1},200,"swing",function(){window.setTimeout(function(){b.animate({top:c,opacity:0},200,"swing",function(){b.remove()
})},225)})}}},initPage:function(){this._initBasic();this._initSections();this._initRoutes();this._initWindowInterface();this._initLayout();
$("#page_loading").remove();$("#loading_css").remove();this.fragmentRouter=window.DeskPRO_FragmentRouter;this.fragmentRouter.setBaseUrl(BASE_URL);
var a=this;$.history.init(function(b){a.loadHashPath(b)},{unescape:",/:"})},loadHashPath:function(c){if(this.DEBUG.disableUrlFragments){return
}if(this.cancelHashLaod){this.cancelHashLaod=false;return}var b=c.split(",");var a=null;Array.each(b,function(j,k){var d=false;
if(j.match(/\.o:/)){d=true;j=j.replace(/\.o:/,":")}var g=this.pageTabStrip.findTabByFragment(j);if(g){if(!a||d){a=g}return
}var n=this.getCurrentListPage();if(n&&n.getMetaData("url_fragment")==j){return}var h=j.match(/^(.*?)(\.(.*?))?:(.*?)$/);
if(!h){return}var g=null;if(h[3]){var g=h[3]}var f=h[1];var l=h[4];l=l.split(":");if(!this.fragmentRouter.hasFragment(f)){return
}var e=this.fragmentRouter.getUrl(f,l);var m=this.fragmentRouter.getFragmentType(f);if(m=="list"){this.loadingListFragment=j;
this.loadListPane(e,{url_fragment:j})}else{this.loadingPageFragment=j;this.loadPage(e,{url_fragment:j})}},this);if(a){this.pageTabStrip.activateTabById(a)
}},updateWindowUrlFragment:function(){if(this.DEBUG.disableUrlFragments){return}this.cancelHashLaod=true;var b=[];var d=this.getCurrentListPage();
if(d&&d.getMetaData("url_fragment")){b.push(d.getMetaData("url_fragment"))}var e=this.pageTabStrip.getActiveTab();var c=this.pageTabStrip.getTabs();
Object.each(c,function(g,i){var f=g.page;var h=f.getMetaData("url_fragment");if(h){if(g.id==e.id){if(h.indexOf(":")!==-1){h=h.replace(/:/,".o:")
}else{h=h+".o"}}b.push(h)}});var a="";a=b.join(",");jQuery.history.load(a)},windowStateUpdated:function(a){return;if(this.DEBUG.disableSaveState){return
}this.winStateQueue.include(a);if(this.saveWindowState_timeout){window.clearTimeout(this.saveWindowState_timeout)}this.saveWindowState_timeout=this.saveWindowState.delay(4500,this)
},saveWindowState:function(){var a=[];if(this.winStateQueue.contains("tabs")){Object.each(this.pageTabStrip.getTabs(),function(b){if(b.page&&b.page.getMetaData("routeUrl")&&!b.page.noRestoreTab){a.push({name:"tabs[]",value:"page:"+b.page.getMetaData("routeUrl")})
}});Object.each(this.listTabStrip.getTabs(),function(b){if(b.page&&b.page.getMetaData("routeUrl")&&!b.page.noRestoreTab){a.push({name:"tabs[]",value:"listpane:"+b.page.getMetaData("routeUrl")})
}})}this.winStateQueue=[];if(!a.length){return}$.ajax({dataType:"json",url:BASE_URL+"agent/misc/ajax-save-state",type:"POST",data:a,success:function(){},error:function(){}})
},getLastClientMessageId:function(){if(this.messageChanneler.lastMessageId){return this.messageChanneler.lastMessageId}return 0
},forwardClientMessageData:function(a){if(this.messageChanneler.handleMessageAjax){this.messageChanneler.handleMessageAjax(a)
}},getNotifier:function(){return this.notifier},getPoller:function(){return this.poller},getTabWatcher:function(){return this.tabWatcher
},getDisplayName:function(a,b){if(!window.DESKPRO_NAME_REGISTRY[a]||!window.DESKPRO_NAME_REGISTRY[a][b]){if(!window.DESKPRO_NAME_REGISTRY[a]){console.warn("Unknown name type %s",a)
}return null}return window.DESKPRO_NAME_REGISTRY[a][b]},getAgentInfo:function(b){var a=$("#agent_offline_list .agent-"+b);
if(!a.length){console.error("Unknow agent %i",b);return null}return{id:b,name:a.data("agent-name"),email:a.data("email"),shortName:a.data("agent-short-name"),pictureUrl:a.data("picture-url"),pictureUrlSizable:a.data("picture-url-sizable")}
},getTeamInfo:function(b){b=parseInt(b);var a=$("#agent_team_list .team-"+b);if(!a.length){console.error("Unknow team %i",b);
return null}return{id:b,name:a.data("team-name"),pictureUrl:a.data("picture-url"),pictureUrlSizable:a.data("picture-url-sizable")}
},getUrl:function(b,c){if(!window.DESKPRO_URL_REGISTRY[b]){console.warn("Unknown url name %s",b);return null}var a=window.DESKPRO_URL_REGISTRY[b];
if(c){Object.each(c,function(e,d){a=a.replace("{"+d+"}",e)})}return a},getData:function(a){if(!window.DESKPRO_DATA_REGISTRY[a]){console.warn("Unknown data name %s",a);
return null}return window.DESKPRO_DATA_REGISTRY[a]},showAlert:function(c,b){this._initAlertOverlay();if(typeof c=="string"){var c=$(c)
}$("#alert_overlay_msg").empty().append(c);var d=this._alertOverlay.elements.wrapperOuter;var a=this._alertOverlay.elements.modal;
if(d.data("added-class")){d.removeClass(d.data("added-class"));a.removeClass(d.data("added-class"));d.data("added-class",null)
}if(b){d.addClass(b);a.addClass(b);d.data("added-class",b)}this._alertOverlay.openOverlay()},showConfirm:function(c,b,a){this._initConfirmOverlay();
this._confirmOverlay_callback_yes=b||function(){};this._confirmOverlay_callback_no=a||function(){};$("#confirm_overlay_msg").html(c);
this._confirmOverlay.openOverlay()},showPrompt:function(b,a,c){this._initPromptOverlay();this._promptOverlay_callback_yes=a||function(){};
this._promptOverlay_callback_no=c||function(){};$("#prompt_overlay_msg").html(b);this._promptOverlay.openOverlay()},_initAlertOverlay:function(){if(this._alertOverlay){return
}this._alertOverlay=new DeskPRO.UI.Overlay({contentElement:$("#alert_overlay"),zIndex:10000000,onContentSet:function(a){$(".close-trigger",a.wrapperEl).click((function(){a.overlay.closeOverlay()
}).bind(this))}});this._alertOverlay.initOverlay()},_initConfirmOverlay:function(){if(this._confirmOverlay){return}this._confirmOverlay_callback_yes=function(){};
this._confirmOverlay_callback_no=function(){};var a=this;this._confirmOverlay=new DeskPRO.UI.Overlay({contentElement:$("#confirm_overlay"),zIndex:10000000,onContentSet:function(b){$(".cancel-trigger",b.wrapperEl).click((function(){b.overlay.closeOverlay();
a._confirmOverlay_callback_no();a._confirmOverlay_callback_no=function(){}}).bind(this));$(".okay-trigger",b.wrapperEl).click((function(){b.overlay.closeOverlay();
a._confirmOverlay_callback_yes();a._confirmOverlay_callback_yes=function(){}}).bind(this))}})},_initPromptOverlay:function(){if(this._promptOverlay){return
}this._promptOverlay_callback_yes=function(){};this._promptOverlay_callback_no=function(){};var a=this;this._promptOverlay=new DeskPRO.UI.Overlay({contentElement:$("#prompt_overlay"),zIndex:10000000,onContentSet:function(b){$(".cancel-trigger",b.wrapperEl).click((function(){b.overlay.closeOverlay();
a._promptOverlay_callback_no($("#prompt_overlay_input").val());a._promptOverlay_callback_no=function(){};$("#prompt_overlay_input").val("")
}).bind(this));$(".okay-trigger",b.wrapperEl).click((function(){b.overlay.closeOverlay();a._promptOverlay_callback_yes($("#prompt_overlay_input").val());
a._promptOverlay_callback_yes=function(){};$("#prompt_overlay_input").val("")}).bind(this))}})},showStatusMessage:function(c,a){a=Object.merge({btnCallback:null,btnText:"Dismiss",autoClose:4500,extraClasses:""},a||{});
if(a.undoCallback){a.btnCallback=a.undoCallback;delete a.undoCallback}var b=$("#status_box");if(b.data("added-classes")){b.removeClass(b.data("added-classes"));
b.data("added-classes",null)}if(a.extraClasses){b.addClass(a.extraClasses);b.data("added-classes",a.extraClasses)}var d=null;
var e=function(){b.fadeOut(250);if(d){window.clearTimeout(d)}};if(a.autoClose){d=e.delay(a.autoClose)}$("#status_message").html(c);
$("#status_dismiss_button em").html(a.btnText);if(a.btnCallback){$("#status_dismiss_button").one("click",function(f){e();
a.btnCallback(f,a)})}else{$("#status_dismiss_button").one("click",function(f){e()})}b.fadeIn(300)},showUndoMessage:function(a,b){this.showStatusMessage(a,{btnCallback:b,btnText:"Undo",extraClasses:"undo"})
},addListPage:function(a){console.warn("Invalid call to addListPage for %o",a);this.setListPage(a)},setListPage:function(c){var a=function(d){return c.getMetaData("fragmentClass","").indexOf(d)!=-1
};var b=null;if(a(".Kb")||a(".News")||a(".Download")||a(".Publish")){b=this.sections.publish_section}else{if(a(".Ticket")||a(".NewCustomFilter")){b=this.sections.tickets_section
}else{if(a(".People")||a(".Org")){b=this.sections.people_section}else{if(a(".AgentChat")){b=this.sections.agent_chat_section
}else{if(a(".OpenChats")){b=this.sections.chat_section}else{if(a(".Idea")){b=this.sections.ideas_section}else{if(a(".RecycleBin")){b=this.sections.tickets_section
}}}}}}}if(!b){console.error("List page fragment has no section: %s: %o",c.getMetaData("fragmentClass",""),c);return}b.setListPageFragment(c);
this.listPage=c;this.updateWindowUrlFragment()},getListPage:function(){return this.listPage},addPageTab:function(a){this.pageTabStrip.addTab(a)
},removePage:function(b){var a=this.pageTabStrip.findTabByPage(b);if(a){this.pageTabStrip.removeTabById(a)}},addPageRouteLoader:function(a,b){if(this.routePrefixes[a]==undefined){this.routePrefixes[a]=[]
}this.routePrefixes[a].push(b)},runPageRoute:function(a){var h=a.split(":");var d=h.shift();var g=null;if(d.indexOf(".")!=-1){var c=d.split(".");
d=c.shift();g=c.pop()}var b=h.pop();var e={route:a,master:d,masterTag:g,sections:h,url:b,stopListeners:false};var f=false;
Object.each(this.routePrefixes,function(i,j){if(a.indexOf(j)==0){Array.each(i,function(k){k(e);f=true});if(e.stopListeners){return true
}}},this);if(!f){console.warn("Unknown route: %s",a)}},runPageRouteFromElement:function(a){a=$(a);if(a.is(".cancel-route")){return
}if(!a.data("route")){console.warn("Element has no route: %o",a)}this.runPageRoute(a.data("route"));if(a.data("route-alt")){this.runPageRoute(a.data("route-alt"))
}},loadRoute:function(a){a.openInSection=a.master;switch(a.openInSection){case"listpane":this.loadListPane(a.url,a);break;
default:this.loadPage(a.url,a);break}},loadListPane:function(a,b,c){$("#deskpro_list > section").removeClass("on");$("#deskpro_list_loading").addClass("on");
this._doAjaxLoadRoute(a,b,(function(e){$("#deskpro_list_loading").removeClass("on");var d=this.createPageFragment(e,"DeskPRO.Agent.PageFragment.ListPane.Basic");
d.setMetaData("routeUrl",a);if(b){d.setMetaData("routeData",b)}this.setListPage(d);if(c){c(d)}}).bind(this))},loadPage:function(b,c,d){if($("#pane_tabs li").length>=10){DeskPRO_Window.showAlert("You have too many tabs open on the right. Close one before trying to open another","error");
return}if(!c||(!c.ignoreExist)){var a=this.pageTabStrip.getTabByRouteUrl(b);if(a&&!(a.page.allowDupe&&a.page.TYPENAME!="loading")){this.pageTabStrip.removeTabById(a.id);
return}}c.tabPlaceholderId=this.pageTabStrip.addTabPlaceholder(b,c);this._doAjaxLoadRoute(b,c,(function(f){var e=this.createPageFragment(f);
e.setMetaData("routeUrl",b);if(c){e.setMetaData("routeData",c);if(c.tabPlaceholderId){e.setMetaData("tabPlaceholderId",c.tabPlaceholderId)
}}if(c.fragment){e.setMetaData("fragment",c.fragment)}this.addPageTab(e);if(d){d(e)}}).bind(this))},_doAjaxLoadRoute:function(c,d,a){d=d||{};
if(!c){console.error("No URL provided! routeData: %o",d);return}var b=this;if(d.tabPlaceholderId){var f=function(){b.pageTabStrip.removeTabById(d.tabPlaceholderId)
}}else{var f=function(){}}if(d&&d.postData){var e=$.ajax({dataType:"text",url:c,type:"POST",data:d.postData,success:(function(g){a(g)
}).bind(this),error:f,noErrorOverride:true});d.xhr=e}else{var e=$.ajax({dataType:"text",url:c,type:"GET",success:(function(g){a(g)
}).bind(this),error:f,noErrorOverride:true});d.xhr=e}},createPageFragment:function(html,classname){pageMeta={title:false,fragmentClass:classname||"DeskPRO.Agent.PageFragment.Basic"};
var regex=/<script>([\s\S]*?)<\/script>/im;var matches=regex.exec(html);if(!matches||!matches.length){var regex=/<script\s*type="text\/javascript">([\s\S]*?)<\/script>/im;
var matches=regex.exec(html)}if(matches&&matches.length){try{eval(matches[1])}catch(err){console.error("Page fragment JS eval error: %o",err)
}}if(pageMeta&&pageMeta.goToLogin){window.location=BASE_URL+"agent/";var page=new DeskPRO.Agent.PageFragment.Basic("");return page
}var fragment_class=Orb.getNamespacedObject(pageMeta.fragmentClass);var page=new fragment_class(html);page.setMetaData(pageMeta);
return page},getCurrentListPage:function(){return this.listPage},getCurrentTabPage:function(){var a=this.pageTabStrip.getActiveTab();
if(!a){return null}return a.page},reloadSelectedTab:function(){var b=this.pageTabStrip.getActiveTab();if(!b){return}var a=b.page.meta.routeData.route;
this.pageTabStrip.removeTabById(b.id);this.runPageRoute(a)},getMessageChanneler:function(){return this.messageChanneler},dismissHelpMessage:function(b){b=$(b);
var a=b.data("message-id");b.remove();if(!a){return}$.ajax({dataType:"json",url:BASE_URL+"agent/misc/dismiss-help-message/"+escape(a),type:"GET"})
},playSound:function(e,a){a=a||{};options=$.extend({},{autoplay:true,volume:false,loop:false,destroyAfter:true},a);if(this.volume==0){return null
}if(typeof e=="string"){e=[e]}var d=this.volume;if(options.volume){d=options.volume}var b=[];b.push("<audio ");if(d!=1){b.push(' volume="'+d+'" ')
}if(options.autoplay){b.push(' autoplay="autoplay" ')}if(options.loop){b.push(' loop="loop" ')}b.push(">");Array.each(e,function(g){b.push('<source src="'+g+'" />')
});b.push("</audio>");b=b.join("");var c=$(b);if(options.destroyAfter){c.bind("ended",function(){$(this).remove()})}if(options.appendTo){$(options.appendTo).append(c)
}else{c.appendTo("body")}return c},playLibrarySound:function(b,a){var c=[ASSETS_BASE_URL+"sounds/"+b+".mp3",ASSETS_BASE_URL+"sounds/"+b+".ogg"];
this.playSound(c,a)},handleSoundElements:function(b){var a=this;$("[data-play-sound]",b).each(function(){a.playLibrarySound($(this).data("play-sound"),{appendTo:b})
});if($(b).is("[data-play-sound]")){a.playLibrarySound($(b).data("play-sound"),{appendTo:b})}},_globalHandleAjaxComplete:function(e,f,c){var b=false;
if(f.status&&f.status==200){b=true}else{if(f.status=="0"||(f.statusText&&f.statusText=="abort")){b=true}}if(c&&c.dpIsPolling){if(b){$("#network_status_indicator").addClass("active");
$("#network_status_indicator span").html("0")}else{$("#network_status_indicator").removeClass("active");var a=$("#network_status_indicator span");
var d=parseInt(a.html())||0;d++;a.html(d)}}},_globalHandleAjaxError:function(a,k,d,i){if(k.status=="0"||(k.statusText&&k.statusText=="abort")){return
}var g=k.responseText;try{g=$.parseJSON(g)}catch(j){g=null}if(k&&k.status&&k.status=="403"){if(g&&g.error&&g.error=="session_expired"){var b=g.redirect_login;
b+="?return="+encodeURIComponent(window.location.href);window.location=b;d.error=null;d.complete=null;this.showStatusMessage("Your session has timed out, you must log in");
return}if(g&&g.error&&g.error=="not_allowed"){this.showStatusMessage("The action you attempted to execute is not allowed:<br />"+g.errorMessage);
return}}if(d&&d.error&&!d.noErrorOverride){return}if(d&&d.dpIsPolling){return}var c=null;if(g&&g.sn){c=g.sn}else{var h=/\[SN([0-9A-Z]{8})\]/.exec(k.responseText);
if(h){c=h[1]}}console.log(c);if(c){var f="SN"+c;if(DESKPRO_PERSON_ISADMIN){f='<a href="'+BASE_URL+"admin/logs/sn/SN"+c+'">SN'+c+"</a>"
}this._showAjaxError("<div>If the error persists, give your administrator this code: "+f+"</div>")}else{this._showAjaxError()
}},_showAjaxError:function(a){$("#global_ajax_error_info").empty();if(a){$("#global_ajax_error_info").html(a)}if(!this.ajaxErrorOverlay){this.ajaxErrorOverlay=new DeskPRO.UI.Overlay({contentElement:$("#global_ajax_error"),zIndex:10000000})
}this.ajaxErrorOverlay.initOverlay();this.ajaxErrorOverlay.elements.wrapperOuter.addClass("error");this.ajaxErrorOverlay.openOverlay()
},_initBasic:function(){this.messageChanneler=new DeskPRO.MessageChanneler.AjaxChanneler(this.messageBroker,this.options.messageChanneler);
this.messageChanneler.subscribeChannel("agent-notification");this.poller=new DeskPRO.AjaxPoller.MessagePoller(this.messageBroker,{ajaxUrl:BASE_URL+"agent/poller",interval:5000});
var a=this;this.notifier=new DeskPRO.Agent.Notifier.Notifier({notifySummaryButton:$("#notify_button"),notifyList:$("#notify_list")})
},_initRoutes:function(){this.addPageRouteLoader("navpane",this.loadRoute.bind(this));this.addPageRouteLoader("listpane",this.loadRoute.bind(this));
this.addPageRouteLoader("page",this.loadRoute.bind(this));this.addPageRouteLoader("ticket",(function(c){var b=c.url.match(/tickets\/([0-9]+)/);
var d=b[1];c.tabLoad=function(){DeskPRO_Window.getMessageBroker().sendMessage("ui.ticket.opened",{ticketId:d})};c.tabUnload=function(){DeskPRO_Window.getMessageBroker().sendMessage("ui.ticket.closed",{ticketId:d})
};this.loadRoute(c)}).bind(this));this.addPageRouteLoader("person",this.loadRoute.bind(this));this.addPageRouteLoader("kb_article_view",this.loadRoute.bind(this));
this.addPageRouteLoader("kb_article_new",this.loadRoute.bind(this));this.addPageRouteLoader("kb_article_edit",this.loadRoute.bind(this));
var a=this;$("#header li a[data-route]").click(function(b){b.preventDefault();a.runPageRouteFromElement(this)})},_initWindowInterface:function(){var c=this;
this.keyboardShortcuts=new DeskPRO.Agent.KeyboardShortcuts();var d=new DeskPRO.Agent.WindowElement.MainMenuOpener();$("#user_settings_link").click(function(){var e=new DeskPRO.UI.Overlay({contentMethod:"iframe",iframeUrl:BASE_URL+"agent/settings"});
e.openOverlay()});$(document).ajaxError(this._globalHandleAjaxError.bind(this));$(document).ajaxComplete(this._globalHandleAjaxComplete.bind(this));
this.faviconBadge=new DeskPRO.FaviconBadge({favicon:"#favicon"});if(this.options.faviconCount){this.getMessageBroker().addMessageListener("agent.ui.badge_updated",function(f){if(c.optionsfaviconCount=="tickets"){if(f.sectionId!="tickets"){return
}var e=f.count}else{var e=0;Object.each(c.sections,function(g){e+=g.getBadgeCount()})}c.faviconBadge.updateBadge(e)})}$("#agent_status").click(function(e){e.preventDefault();
c.toggleAgentStatus()});this.volume=0.8;$("#volume_controls .slider").slider({orientation:"vertical",range:"min",min:0,max:100,value:80,slide:function(e,f){c.volume=parseInt(f.value)/100;
if(c.volume==0||c.volume==0){c.volume=0;$("#sound_icon").addClass("off")}else{$("#sound_icon").removeClass("off")}}});var b=function(){$("#volume_controls_back").hide();
$("#volume_controls").fadeOut()};$("#volume_controls_back").click(function(e){e.stopPropagation();b()});var a=function(){$("#volume_controls_back").show();
$("#volume_controls").css({top:30,left:$("#sound_icon").offset().left-7});$("#volume_controls").fadeIn()};$("#sound_icon a").click(function(e){e.preventDefault();
e.stopPropagation();a()});this.createMenu=new DeskPRO.UI.Menu({triggerElement:"#create_content_trigger",menuElement:"#create_content_menu"});
this.newTicketLoader=new DeskPRO.Agent.Widget.BackgroundPopout({loadUrl:BASE_URL+"agent/tickets/new",tabRoute:"page:"+BASE_URL+"agent/tickets/new"});
this.newArticleLoader=new DeskPRO.Agent.Widget.BackgroundPopout({loadUrl:BASE_URL+"agent/kb/article/new",tabRoute:"page:"+BASE_URL+"agent/kb/article/new"});
this.newNewsLoader=new DeskPRO.Agent.Widget.BackgroundPopout({loadUrl:BASE_URL+"agent/news/new",tabRoute:"page:"+BASE_URL+"agent/news/new"});
this.newDownloadLoader=new DeskPRO.Agent.Widget.BackgroundPopout({loadUrl:BASE_URL+"agent/downloads/new",tabRoute:"page:"+BASE_URL+"agent/news/new"});
this.newIdeaLoader=new DeskPRO.Agent.Widget.BackgroundPopout({loadUrl:BASE_URL+"agent/ideas/new",tabRoute:"page:"+BASE_URL+"agent/ideas/new"});
$("#create_ticket_btn").click(function(){DeskPRO_Window.newTicketLoader.toggle()});$("#create_article_btn").click(function(){DeskPRO_Window.newArticleLoader.toggle()
});$("#create_news_btn").click(function(){DeskPRO_Window.newNewsLoader.toggle()});$("#create_download_btn").click(function(){DeskPRO_Window.newDownloadLoader.toggle()
});$("#create_idea_btn").click(function(){DeskPRO_Window.newIdeaLoader.toggle()});this.omnisearch=new DeskPRO.Agent.OmniSearchBox()
},toggleAgentStatus:function(a){var b=$("#agent_status");a=a||false;if(a||b.is(".off")){$("#agent_status_away_overlay").remove();
b.removeClass("off");$.ajax({url:BASE_URL+"agent/misc/set-agent-status/available",type:"GET"})}else{var c=$('<div id="agent_status_away_overlay" />').appendTo("body");
c.click(function(d){d.preventDefault();d.stopPropagation()});b.addClass("off");$.ajax({url:BASE_URL+"agent/misc/set-agent-status/away",type:"GET"})
}},_initSections:function(){var a=this;var b=null;$("#deskpro_sections [data-section-handler]").each(function(){var e=$(this);
if(!e.attr("id")){e.attr("id",Orb.getUniqueId("section_"))}var f=e.data("section-handler");if(DeskPRO_Window.DEBUG.disableSectionHandlers){if(!DeskPRO_Window.DEBUG.enableSectionHandlers||DeskPRO_Window.DEBUG.enableSectionHandlers.indexOf(f)===-1){return
}}if(!b){b=e}var d=Orb.getNamespacedObject(f);var c=new d();a.sections[e.attr("id")]=c;if(!e.is(".no-click-switch")){e.click(function(){a.switchToSection(e.attr("id"))
})}});$("#deskpro_outline").delegate("[data-route]","click",function(c){c.stopPropagation();DeskPRO_Window.runPageRouteFromElement(this)
});if(b){this.switchToSection(b.attr("id"))}},switchToSection:function(b){console.debug("Switching to %s",b);var d=this.sections[b];
var c=$("#"+b);if(c.is(".on")){return}if(this.openSection){this.openSection.fireEvent("hide")}$("#deskpro_sections li.on").removeClass("on");
c.addClass("on");$("#deskpro_outline > section.on").removeClass("on");$("#deskpro_list > section.on").removeClass("on");$("#deskpro_list_loading, #deskpro_outline_loading").addClass("on");
if(this.openSection){this.openSection.fireEvent("afterhide")}$("#deskpro_list_loading").removeClass("on");d.fireEvent("show");
var a=d.getSectionElement();if(a){a.addClass("on")}var e=d.getListElement();if(e){e.addClass("on")}d.fireEvent("aftershow");
this.openSection=d},getOpenSection:function(){return this.openSection},_initLayout:function(){this.layout=new DeskPRO.Agent.Layout.DeskproWindow();
this.layout.doResize();this.pageTabStrip=new DeskPRO.Agent.TabStrip($("#deskpro_tabstrip > ul:first"),new DeskPRO.Agent.TabManager("#deskpro_viewport"));
this.tabManager=this.pageTabStrip.tabManager;this.tabWatcher=new DeskPRO.Agent.TabWatcher({tabManager:this.tabManager});this.tabWatcher.addTabTypeWatcher("ticket",new DeskPRO.Agent.WindowElement.TabWatcher.Tickets());
this.pageTabStrip.tabManager.addEvent("addTab",function(){DeskPRO_Window.windowStateUpdated("tabs")});this.pageTabStrip.tabManager.addEvent("removeTab",function(){DeskPRO_Window.windowStateUpdated("tabs")
})}});Orb.createNamespace("DeskPRO.Agent.Layout");DeskPRO.Agent.Layout.DeskproWindow=Orb.Class({Implements:[Orb.Util.Events],initialize:function(){var a=this;
this.LEFT_START=244;$(window).resize(function(){if(a._resizeTimeout){window.clearTimeout(a._resizeTimeout)}a._resizeTimeout=a.doResize.delay(300,a)
})},doResize:function(){var b=$(window).width();var a=b-this.LEFT_START;var c=a*0.39;if(c<255){c=255}$("#deskpro_list").width(c);
$("#deskpro_content").css("left",this.LEFT_START+c+1);this.fireEvent("resized",[this])}});Orb.createNamespace("DeskPRO.Agent.Layout");
DeskPRO.Agent.Layout.WindowLayout=Orb.Class({Implements:[Orb.Util.Events],initialize:function(){var c=this;this.TOP_POS=107;
this.PADDING=5;this.TABSTRIP_HEIGHT=32;this.TABSTRIP_W_ALTER=7;this.winWidth=$(window).width();this.winHeight=$(window).height();
var d=$(document).width();var b=$(document).height();$("#window_panes").css({position:"absolute",top:this.TOP_POS,left:0,right:0,bottom:0}).addClass("has-layout").data("layout",this).show();
this.columns=$("#window_panes > tbody > tr > td");var e=this.calculateHeight(b);var a=d;var f=Math.floor(a/this.columns.length)-(2*this.PADDING)-((this.columns.length-1)*this.PADDING);
this.columns.each(function(){c.resizeColumn($(this),f,e)});this.drawDraggers();$(window).resize(function(){if(c._resizeTimeout){window.clearTimeout(c._resizeTimeout)
}c._resizeTimeout=c.handleResize.delay(350,c)})},calculateHeight:function(a){a-=this.TOP_POS;a-=(4*this.PADDING);return a
},handleResize:function(){var e=$(window).width();var b=$(window).height();var d=this.winWidth;var j=this.winHeight;this.winWidth=e;
this.winHeight=b;var f=null;if(b!=j){f=this.calculateHeight(b)}var k=null;var a=null;var i=null;if(e!=d){k=Math.abs(e-d);
a=Math.floor(k/this.columns.length);i=k-(a*this.columns.length);if(e<d){k=0-k;a=0-a;i=0-i}}var l=this;this.columns.each(function(m){var h=null;
if(a){h=$(this).width();h+=a;if(m==0&&i){h+=i}}l.resizeColumn($(this),h,f)});var c=this.columns.first();var g=c.offset();
$("#pane_resizer").css({left:g.left+c.outerWidth()-3});this.fireEvent("resized",[this])},resizeColumn:function(c,a,d){var e=$("> .pane > .pane-content",c);
var b=$("> .pane > .pane-tabs",c);e.css("overflow","auto");if(d){var f=d;if(b.length){f-=this.TABSTRIP_HEIGHT}e.css({height:f})
}if(a){e.css({width:a-2});$(b).width(a-2)}},resizeColumnWidths:function(b,d){var f=this.columns.eq(b);var c=this.columns.eq(b+1);
var e=f.width()+c.width();var a=e-d;this.resizeColumn(f,d);this.resizeColumn(c,a)},drawDraggers:function(){var a=$("#pane_resizer");
var b=this.columns.first();var c=b.offset();a.css({position:"absolute",top:c.top+5,bottom:8,left:c.left+b.outerWidth()-3});
a.draggable({axis:"x",stop:this.dragResized.bind(this)})},dragResized:function(c,d){var e=$("#pane_resizer");var b=this.columns.first();
var a=e.position().left-13;this.resizeColumnWidths(0,a);this.fireEvent("resized",[this])}});Orb.createNamespace("DeskPRO.Agent.Layout");
DeskPRO.Agent.Layout.FooterLayout=Orb.Class({Implements:[Orb.Util.Events],initialize:function(b){this.paneWrapper=b.parent();
this.wrapper=b;this.wrapper.addClass("has-layout").data("layout",this);this.content=$("div.layout-content:first",this.wrapper);
this.viewPort=$("> div.scroll-viewport:first",this.content);if(!this.viewPort.length){this.viewPort=null}this.footer=$("div.layout-footer:first",this.wrapper);
var a=this.paneWrapper.closest(".has-layout");if(a.length){a.data("layout").addEvent("resized",this.doLayout.bind(this))}this.doLayout()
},doLayout:function(){var a=this.paneWrapper.width();var b=this.paneWrapper.height();if(this.footer.is(":visible")){if(this.footer.is(".no-expander")){var c=166
}else{if(this.footer.is(".is-ticket-list")){if(this.isFooterOpen){var c=175}else{var c=33}}else{var c=26;if($(".tab-bottom",this.footer).length){c=33;
if(this.isFooterOpen){c=198}}}}this.footer.css({height:c,width:a,overflow:"hidden"})}else{var c=0}this.content.css({height:b-c,width:a,overflow:"auto"});
if(this.viewPort){this.viewPort.css({height:b-c});this.content.css({overflow:"hidden"})}this.fireEvent("resized",[this])},expandFooter:function(){this.isFooterOpen=true;
this.doLayout()},collapseFooter:function(){this.isFooterOpen=false;this.doLayout()}});Orb.createNamespace("DeskPRO.Agent.Layout");
DeskPRO.Agent.Layout.FooterActionbarLayout=Orb.Class({Implements:[Orb.Util.Events],initialize:function(b){this.paneWrapper=b.parent();
this.wrapper=b;this.wrapper.addClass("has-layout").data("layout",this);this.content=$("div.layout-content:first",this.wrapper);
this.footer=$("div.layout-footer:first",this.wrapper);this.isFooterOpen=true;var a=this.paneWrapper.closest(".has-layout");
if(a.length){a.data("layout").addEvent("resized",this.doLayout.bind(this))}this.doLayout()},doLayout:function(){var a=this.paneWrapper.width();
var b=this.paneWrapper.height();if(this.isFooterOpen){var c=33;this.footer.css({width:a,height:c,overflow:"hidden"});if(!this.footer.is(".expanded")){this.footer.show()
}this.footer.addClass("expanded")}else{var c=0;this.footer.css({width:a,overflow:"hidden",display:"none"});this.footer.removeClass("expanded")
}this.content.css({height:b-c,width:a,overflow:"auto"});this.fireEvent("resized",[this])},expandFooter:function(){this.isFooterOpen=true;
this.doLayout()},collapseFooter:function(){this.isFooterOpen=false;this.doLayout()}});Orb.createNamespace("DeskPRO.Agent");
DeskPRO.Agent.TabManager=new Orb.Class({Implements:[Orb.Util.Events,Orb.Util.Options],initialize:function(b,a){this.options={defaultHideMode:"hide",activateNew:true};
this.tabs={};this.currentTabId=null;this.isActivating=false;this.containerEl=$(b);this.setOptions(a)},getActiveTabId:function(){return this.currentTabId
},getActiveTab:function(){return this.getTab(this.getActiveTabId())},getTab:function(a){if(this.tabs[a]==undefined){return null
}return this.tabs[a]},getTabs:function(){return this.tabs},addTab:function(b,a){if(typeOf(a)=="string"){a={html:a}}a.id=b;
if(a.hideMode==undefined){a.hideMode=this.options.defaultHideMode}a.wrapperId=Orb.getUniqueId("tab_");a.isInserted=false;
a.html='<div id="'+a.wrapperId+'">'+a.html+"</div>";this.tabs[b]=a;this.fireEvent("addTab",[a,this]);if(!this.currentTabId||this.options.activateNew){this.activateTab(b)
}},activateTab:function(d){if(this.tabs[d]==undefined){console.warn("Unknown tab: %s",d);return false}if(d==this.currentTabId){return
}this.isActivating=true;if(this.currentTabId){this.deactivateCurrentTab()}var b=this.tabs[d];if(b.isInserted&&b.hideMode=="hide"){console.log("Re-showing tab node: %s",d);
var c=$("#"+b.wrapperId,this.containerEl).show();if(b.callback_reinsert!==undefined){b.callback_reinsert(b,$("#"+b.wrapperId,this.containerEl),this)
}this.fireEvent("activateTabReinsert",[b,c,this])}else{console.log("Rendering tab content: %s",d);var a=$(b.html).appendTo(this.containerEl);
b.isInserted=true;a.show();if(b.callback_render!==undefined){b.callback_render(b,$("#"+b.wrapperId,this.containerEl),this)
}this.fireEvent("activateTabRender",[b,$("#"+b.wrapperId,this.containerEl),this])}if(b.callback_activate!==undefined){b.callback_activate(b,this.containerEl,this)
}this.currentTabId=d;this.fireEvent("activateTab",[b,this.containerEl,this]);this.isActivating=false;b.isActive=true},deactivateCurrentTab:function(){if(!this.currentTabId){return
}var a=this.tabs[this.currentTabId];a.isActive=false;this.fireEvent("deactivateTabBefore",[a,this.containerEl,this.isActivating,this]);
if(a.callback_deactivate!==undefined){a.callback_deactivate(a,$("#"+a.wrapperId,this.containerEl),this)}if(a.hideMode=="remove"){console.log("Removing tab content: %o",this.currentTabId);
$("#"+a.wrapperId,this.containerEl).remove();a.isInserted=false;if(a.callback_remove_content!==undefined){a.callback_remove_content(a,$("#"+a.wrapperId,this.containerEl),this)
}}else{console.log("Hiding tab content: %o, id: %s",this.currentTabId,a.wrapperId);$("#"+a.wrapperId,this.containerEl).hide();
if(a.callback_hide_content!==undefined){a.callback_hide_content(a,$("#"+a.wrapperId,this.containerEl),this)}}this.fireEvent("deactivateTab",[a,$("#"+a.wrapperId,this.containerEl),this.isActivating,this]);
this.currentTabId=null},removeTab:function(c){Tipped.hideAll();(function(){Tipped.hideAll()}).delay(300);if(this.tabs[c]==undefined){return false
}if(this.currentTabId==c){this.deactivateCurrentTab()}var b=this.tabs[c];delete this.tabs[c];if(b.callback_remove_content!==undefined){b.callback_remove_content(b,$("#"+b.wrapperId,this.containerEl),this)
}$("#"+b.wrapperId,this.containerEl).remove();this.fireEvent("removeTab",[b,this]);var a=Object.keys(this.tabs).getLast();
if(a){this.activateTab(a)}}});Orb.createNamespace("DeskPRO.Agent");DeskPRO.Agent.TabStrip=new Orb.Class({Implements:[Orb.Util.Events,Orb.Util.Options],initialize:function(c,d){this.tabStrip=c;
this.tabManager=d;this.cancelClickActivate=false;this.uniqueCounter=0;var b=this;this.tabStrip.mouseup(this._tabStripClick.bind(this));
this.tabManager.addEvents({addTab:this._onTabAdd.bind(this),activateTab:this._onTabActivate.bind(this),deactivateTab:this._onTabDeactivate.bind(this),removeTab:this._onTabRemove.bind(this)});
var a=(b.tabStrip.parent().width()/2);a=a-(a/4);scroll_el=this.tabStrip.parent();$(".tabs_scroll_left").click(function(){scroll_el.animate({scrollLeft:"-="+a},200)
});$(".tabs_scroll_right").click(function(){scroll_el.animate({scrollLeft:"+="+a},200)});this.tabStrip.parent().mousewheel(function(e,f){if(f>0){scroll_el.animate({scrollLeft:"+="+a},100)
}else{scroll_el.animate({scrollLeft:"-="+a},100)}})},alertTab:function(a){var b=$("li."+a,this.tabStrip);if(!b.length||b.is(".active-tab")||b.is(".is-alerting")){return
}b.addClass("is-alerting");var c=this._alertTabDoHighlight.periodical(1500,this,[b]);b.data("alerting-timeout",c)},_alertTabDoHighlight:function(a){a.toggleClass("alert-highlight")
},getTabs:function(){return this.tabManager.getTabs()},getActiveTab:function(){return this.tabManager.getActiveTab()},activateTabById:function(a){this.tabManager.activateTab(a)
},removeTabById:function(a){this.tabManager.removeTab(a)},findTabByPage:function(b){var a=false;Object.each(this.tabManager.getTabs(),function(d,c){if(d.page==b){a=c;
return false}});return a},findTabByFragment:function(b){var a=false;Object.each(this.tabManager.getTabs(),function(c){if(c.page.getMetaData("url_fragment")==b){a=c.id;
return false}});return a},getTabByFragment:function(b){var a=this.findTabByFragment(b);if(!a){return null}return this.getTabById(a)
},findTabByRouteUrl:function(b){var a=false;Object.each(this.tabManager.getTabs(),function(c){if(c.page.getMetaData("routeUrl")==b){a=c.id;
return false}});return a},getTabByRouteUrl:function(b){var a=this.findTabByRouteUrl(b);if(!a){return null}return this.getTabById(a)
},getTabById:function(a){return this.tabManager.getTab(a)},addTab:function(a){var b="tab"+this.uniqueCounter++;this.tabManager.addTab(b,{html:a.getHtml(),page:a,title:a.getMetaData("title","Untitled"),callback_render:function(e,c,d){c=$(c);
a.fireEvent("render",[c])},callback_remove_content:function(e,c,d){a.fireEvent("destroy")},callback_activate:function(){a.fireEvent("activate")
},callback_deactivate:function(){a.fireEvent("deactivate")}});this.resizeTabListWidth();return b},addTabPlaceholder:function(a,c){var b=$("#tab_loading_template").get(0).innerHTML;
b=b.replace("%endScript%","<\/script>");var d=DeskPRO_Window.createPageFragment(b,"DeskPRO.Agent.PageFragment.Page.Loading");
d.meta.routeUrl=a;d.meta.routeData=c;var e=this.addTab(d);if(c.tabLoad){c.tabLoad()}return e},resizeTabListWidth:function(){var a=0;
$("> li",this.tabStrip).each(function(){a+=$(this).outerWidth()});if(a<this.tabStrip.parent().width()){a=this.tabStrip.parent().width()
}this.tabStrip.css({width:a});if(this.tabStrip.width()>this.tabStrip.parent().width()){this.tabStrip.parent().addClass("with-scroller");
a+=30;this.tabStrip.css({width:a})}else{this.tabStrip.parent().removeClass("with-scroller")}},_tabStripClick:function(e){if(this.cancelClickActivate){this.cancelClickActivate=false;
return}this.cancelClickActivate=true;var b=$(e.target);if(b.is("li.tab")){var d=b}else{if(b.parent().is("li.tab")){var d=b.parent()
}else{var d=b.parentsUntil("li.tab");if(d.length){d=d.parent()}}}if(!d.is("li.tab")){console.log("not click %o",e.target);
return}e.preventDefault();e.stopPropagation();var a=d.data("tab-id");if(b.is(".close-tab")||e.which==2||e.isDbl){var c=this.getTabById(a);
if(c.page&&c.page.fireEvent){e.deskpro={cancelClose:false};c.page.fireEvent("closeTab",[e,c]);if(e.deskpro.cancelClose){return
}}c.isCloseClick=true;this.tabManager.removeTab(a);c.isCloseClick=false;this.cancelClickActivate=false;return}this.tabManager.activateTab(a);
this.cancelClickActivate=false},_onTabAdd:function(b){b.btnId=b.id;if(!b.page.getMetaData("url_fragment")&&b.page.TYPENAME!="loading"){}var c=b.page.getMetaData("tabIdClass","");
var d='<li id="'+b.btnId+'" data-tab-id="'+b.id+'" class="tab tipped '+c;if(b.page.TYPENAME!="basic"){d+=" "+b.page.TYPENAME
}if(b.page.getMetaData("tabTip")){if(b.page.getMetaData("tabTip").indexOf(".")===0){b.page.setMetaData("fetchTabTip",true);
d+='" data-tipped="'+b.btnId+'_tip" data-tipped-options="inline: true, hook: \'topmiddle\'">'}else{d+='" data-tipped="'+b.tabTip+'" data-tipped-options="hook: \'topmiddle\'">'
}}else{d+='" data-tipped="'+b.title+'" data-tipped-options="hook: \'topmiddle\'">'}d+='<a class="link">'+b.title+"</a>";d+='<a class="close-tab">Close</a>';
d+="</li>";var a=$(d);if(b.page&&b.page.meta.tabPlaceholderId){var e=this.getTabById(b.page.meta.tabPlaceholderId);var f=e.btnId;
loadingTabData=this.getTabById(b.page.meta.tabPlaceholderId);loadingTabData.isReplacing=true;this.removeTabById(b.page.meta.tabPlaceholderId);
$("#"+f).replaceWith(a);if(!$("li.active-tab",this.tabStrip).length){a.addClass("active-tab")}}else{a.appendTo(this.tabStrip);
this.resizeTabListWidth()}},_onTabDeactivate:function(a,b,c){$("#tiptip_holder").clearQueue().hide()},_onTabActivate:function(b){$("li",this.tabStrip).removeClass("active-tab");
var d=$("#"+b.btnId).addClass("active-tab");if(d.is(".is-alerting")){d.removeClass("alert-highlight").removeClass("is-alerting");
var c=d.data("alerting-timeout");if(c){window.clearTimeout(c);d.data("alerting-timeout",false)}}if(b.page.getMetaData("fetchTabTip")){b.page.setMetaData("fetchTabTip",null);
var a=b.btnId+"_tip";if(!document.getElementById(a)){$("#"+b.wrapperId+" "+b.page.getMetaData("tabTip")).attr("id",a)}}DeskPRO_Window.updateWindowUrlFragment()
},_onTabRemove:function(a){$("#tiptip_holder").clearQueue().hide();if(a.isReplacing){return}$("#"+a.btnId).remove();if(a.page.meta.routeData&&a.page.meta.routeData.xhr){a.page.meta.routeData.xhr.abort()
}if(a.page.meta.routeData&&a.page.meta.routeData.tabUnload){a.page.meta.routeData.tabUnload()}this.resizeTabListWidth()}});
Orb.createNamespace("DeskPRO.Agent.WindowElement");DeskPRO.Agent.TabWatcher=new Orb.Class({Implements:[Orb.Util.Options,Orb.Util.Events],initialize:function(a){this.options={tabManager:null};
this.setOptions(a);this.tabManager=this.options.tabManager;this.selectionHistory=[];this.tabManager.addEvent("activateTab",this._activateTab.bind(this));
this.tabManager.addEvent("deactivateTab",this._deactivateTab.bind(this));this.tabManager.addEvent("removeTab",this._removeTab.bind(this));
this.watchedTypes={}},_activateTab:function(c,d,a){var e=c.id;this.selectionHistory.erase(e);this.selectionHistory.push(e);
var b=this.getTabType(c);if(this.watchedTypes[b]){Array.each(this.watchedTypes[b],function(f){f.fireEvent("watchedTabActivated",[c])
})}},_deactivateTab:function(c,d,a){var b=this.getTabType(c);if(this.watchedTypes[b]){Array.each(this.watchedTypes[b],function(e){e.fireEvent("watchedTabDeactivated",[c])
})}},_removeTab:function(c,a){this.selectionHistory.erase(c.id);var b=this.getTabType(c);if(this.watchedTypes[b]){Array.each(this.watchedTypes[b],function(d){d.fireEvent("watchedTabRemoved",[c])
})}},addTabTypeWatcher:function(b,a){if(!this.watchedTypes[b]){this.watchedTypes[b]=[]}this.watchedTypes[b].push(a)},removeTabTypeWatcher:function(b,a){if(!this.watchedTypes[b]){return
}this.watchedTypes[b].erase(a)},getActiveTab:function(){return this.tabManager.getActiveTab()},getActiveTabIfType:function(b){var a=this.getActiveTab();
if(this.getTabType(a)!=b){return null}return a},getActiveTabType:function(){return this.getTabType(this.getActiveTab())},isTabTypeActive:function(a){return this.getActiveTabType()==a
},getTabType:function(a){if(!a){return null}if(a.page&&a.page.TYPENAME){return a.page.TYPENAME}return"general"},getLastSelectedTab:function(b){var a=this.selectionHistory.length-1;
a-=b;if(a<0){return null}return this.getTab(this.selectionHistory[a])},getLastSelectedTabType:function(c){var a=this.selectionHistory.length-1;
if(!a){return null}while(a-->0){var b=this.getTab(a);if(this.getTabType(b)==c){return b}}return null},getSelectionHistory:function(){var a=[];
Array.each(this.selectionHistory,function(b){a.push(this.getTab(b))},this);a.reverse();return a},findTabType:function(b){var a=[];
Object.each(this.tabManager.getTabs(),function(c){if(this.getTabType(c)==b){a.push(c)}},this);return a}});Orb.createNamespace("DeskPRO.Agent");
DeskPRO.Agent.ScrollerHandler=new Orb.Class({Implements:[Orb.Util.Options],initialize:function(d,c,b){this.pageObject=d;this.element=c=$(c);
this.options={showEvent:"activate",hideEvent:"deactivate"};this.setOptions(b);c.tinyscrollbar();this.initUpdateTimer();if(d.addEvent){var a=this;
d.addEvent(this.options.showEvent,function(){a.initUpdateTimer()});d.addEvent(this.options.hideEvent,function(){a.removeResizeTimer()
})}},initUpdateTimer:function(){var b=this.element;if(b.data("resize-special-event")){return}var a=$("div.scroll-viewport:first",b);
a.resize(function(){b.tinyscrollbar_update()})},removeResizeTimer:function(){this.element.unbind("resize")}});Orb.createNamespace("DeskPRO.Agent");
DeskPRO.Agent.KeyboardShortcuts=new Orb.Class({Implements:[Orb.Util.Events,Orb.Util.Options],initialize:function(){$(document).bind("keydown","ctrl+left",this.tabLeft.bind(this));
$(document).bind("keydown","ctrl+right",this.tabRight.bind(this));$(document).bind("keydown","ctrl+shift+c",this.closeTab.bind(this));
$(document).bind("keydown","t",this.showNewTicket.bind(this));$(document).bind("keydown","k",this.showNewArticle.bind(this));
$(document).bind("keydown","n",this.showNewNews.bind(this));$(document).bind("keydown","d",this.showNewDownload.bind(this));
$(document).bind("keydown","i",this.showNewIdea.bind(this));this.boundShortkuts={};this.addContextShortcut("ticket","ctrl+shift+r","shortcutFocusReply")
},addContextShortcut:function(c,b,a){if(!this.boundShortkuts[b]){this.boundShortkuts[b]={};$(document).bind("keydown",b,(function(d){this.dispatchShortcutEvent(d,b)
}).bind(this))}this.boundShortkuts[b][c]=a},dispatchShortcutEvent:function(b,a){if(!this.boundShortkuts[a]){return}var c=DeskPRO_Window.getCurrentTabPage();
if(!c||!c.TYPENAME||!this.boundShortkuts[a][c.TYPENAME]){return}c.fireEvent(this.boundShortkuts[a][c.TYPENAME],[b,a])},showNewTicket:function(){DeskPRO_Window.newTicketLoader.toggle()
},showNewArticle:function(){DeskPRO_Window.newArticleLoader.toggle()},showNewNews:function(){DeskPRO_Window.newNewsLoader.toggle()
},showNewDownload:function(){DeskPRO_Window.newDownloadLoader.toggle()},showNewIdea:function(){DeskPRO_Window.newIdeaLoader.toggle()
},tabLeft:function(){var a=$("li.active-tab",DeskPRO_Window.pageTabStrip.tabStrip);var b=a.prev();if(!b.length){b=$("li:last",DeskPRO_Window.pageTabStrip.tabStrip)
}if(!b.is(".active-tab")){DeskPRO_Window.pageTabStrip.activateTabById(b.data("tab-id"))}},tabRight:function(){var a=$("li.active-tab",DeskPRO_Window.pageTabStrip.tabStrip);
var b=a.next();if(!b.length){b=$("li:first",DeskPRO_Window.pageTabStrip.tabStrip)}if(!b.is(".active-tab")){DeskPRO_Window.pageTabStrip.activateTabById(b.data("tab-id"))
}},closeTab:function(){var a=DeskPRO_Window.pageTabStrip.getActiveTab();if(a){DeskPRO_Window.pageTabStrip.removeTabById(a.id)
}}});Orb.createNamespace("DeskPRO.Agent.PageFragment");DeskPRO.Agent.PageFragment.Basic=new Class({Implements:[Events,DeskPRO.Agent.Widgetable],pageUuid:null,ZONE:"agent",TYPENAME:"basic",allowDupe:false,scripts:[],stylesheets:[],html:"",meta:{},urls:{},featureSelectors:{routes:[],times:[]},initialize:function(d){this.pageUid=Orb.uuid();
if(d){this.html=d}this.addEvent("activate",(function(){DeskPRO_Window.getMessageBroker().sendMessage("page-fragment.activated",{page:this})
}).bind(this));this.addEvent("deactivate",(function(){DeskPRO_Window.getMessageBroker().sendMessage("page-fragment.deactivated",{page:this})
}).bind(this));this.addEvent("render",(function(e){this.initFeaturesOnCollection(e)}).bind(this));if(this.getMetaData("initRoutesOn")){var c=this.getMetaData("initRoutesOn");
if(typeOf(c)=="string"){c=[c]}for(var b=0;b<c.length;b++){this.featureSelectors.routes.push(c[b])}}this.addEvent("render",function(e){if(this.getMetaData("widgets")){this.initWidgets(this.getMetaData("widgets"),{personId:DESKPRO_PERSON_ID,deskproPath:BASE_URL,proxyKey:DESKPRO_PROXY_KEY});
this.initWidgetsDom(e)}});var a=this;this.addEvent("activate",this.activate);this.addEvent("deactivate",this.deactivate);
this.addEvent("render",this.initPage);this.addEvent("destroy",this.destroyPage);this.init()},init:function(){},activate:function(){},deactivate:function(){},initFeaturesOnCollection:function(b,a){a=a||this.featureSelectors;
if(a.routes&&a.routes.length){this.initRoutesOnCollection($(a.routes.join(", "),b))}if(a.times&&a.times.length){this.initTimesOnCollection($(a.times.join(", "),b))
}if(this.wrapper){this.initTipsOnCollection($(".person-tip",this.wrapper))}},initRoutesOnCollection:function(a){a.click(function(){DeskPRO_Window.runPageRouteFromElement(this)
})},initTimesOnCollection:function(a){a.timeago()},initTipsOnCollection:function(a){$(a).each(function(){var c=$(this);if(c.is(".person-tip")){var b=BASE_URL+"agent/person/"+c.data("person-id")+"/tip";
c.addClass("tipped");c.attr("data-tipped",b);c.attr("data-tipped-options",'ajax:true, showOn: "click", hideOn: { element: "target", event: "click" }, hideOnClickOutside: true ');
c.click(function(d){Tipped.toggle(this)});if(c.is(".with-route")){c.addClass("cancel-route")}if(c.parent().is(".with-route")){c.parent().addClass("cancel-route")
}}})},setMetaData:function(a,b){if(b===undefined&&typeOf(a)=="object"){this.meta=Object.merge(this.meta,a)}else{this.meta[a]=b
}},getAllMetaData:function(){return this.meta},getMetaData:function(b,a){if(a===undefined){a=null}if(this.meta[b]===undefined){return a
}return this.meta[b]},getUrl:function(b,c){if(!this.meta.urls){console.error("Unknown url name %s (no urls set)",b);return null
}if(!this.meta.urls[b]){console.error("Unknown url name %s",b);return null}var a=this.meta.urls[b];if(c){Object.each(c,function(e,d){a=a.replace("{"+d+"}",e)
})}return a},getScripts:function(){return this.scripts},getStylesheets:function(){return this.stylesheets},getHtml:function(){return this.html
},initPage:function(a){},destroyPage:function(){},getEl:function(b){if(this.getMetaData("baseId")){b=this.getMetaData("baseId")+"_"+b
}var a=null;if(this.wrapper){a=this.wrapper}return $("#"+b,a)},closeSelf:function(){DeskPRO_Window.removePage(this)}});Orb.createNamespace("DeskPRO.Agent.PageFragment.Page");
DeskPRO.Agent.PageFragment.Page.Loading=new Class({Extends:DeskPRO.Agent.PageFragment.Basic,allowDupe:true,TYPENAME:"loading",initPage:function(a){this.wrapper=$(a)
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
a.push(f)});if(!a.length){this.sectionEl.hide();return}a="<li>"+a.join("</li><li>")+"</li>";this.sectionList.html(a)}});Orb.createNamespace("DeskPRO.Agent.WindowElement.TabWatcher");
DeskPRO.Agent.WindowElement.TabWatcher.Tickets=new Orb.Class({Implements:[Orb.Util.Events],initialize:function(){this.addEvent("activateTab",this.activateTab.bind(this));
this.addEvent("deactivateTab",this.deactivateTab.bind(this));this.releasing={};DeskPRO_Window.getMessageBroker().addMessageListener("agent-notification.tickets.unlocked",(function(a){var b=a.ticket_id;
Array.each(DeskPRO_Window.getTabWatcher().findTabType("ticket"),function(c){if(c.page.getMetaData("ticket_id")==b&&c.page.ticketLocked){c.page.ticketLocked.unlock()
}})}).bind(this))},activateTab:function(a){var b=a.page.getMetaData("ticket_id");if(this.releasing[b]){this.releasing[b].abort();
delete this.releasing[b]}},deactivateTab:function(a){if(a.page.getMetaData("isLocked")){return}var b=a.page.getMetaData("ticket_id");
this.releasing[b]=$.ajax({url:BASE_URL+"agent/ticket-search/ajax-release-locks",type:"GET",data:[{name:"ticket_ids[]",value:b}],dataType:"json"})
}});Orb.createNamespace("DeskPRO.Agent.WindowElement");DeskPRO.Agent.WindowElement.MainMenuOpener=new Orb.Class({Implements:[Orb.Util.Options],initialize:function(d){this.allMenus=null;
this.options={menuSelectors:[]};var c=this;this.options.menuSelectors.push("#header .nav > .wrapper-top-bar > ul > li");this.options.menuSelectors.push("#header .box-header.notifications");
this.options.menuSelectors.push("#header .box-header.zone-switcher");this.options.menuSelectors.push("#header .box-header.actions > .wrapper-top-bar > ul > li.with-menu");
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
b.stopPropagation();a.readAll()});DeskPRO_Window.getMessageBroker().addMessageListener("agent-notification.new-ticket",function(c){var b="ticket:"+BASE_URL+"agent/tickets/"+c.ticket_id;
a.addItem("tickets",c.ticket_id,c.subject,b)});DeskPRO_Window.getMessageBroker().addMessageListener("agent-notification.new-idea",function(c){var b="page:"+BASE_URL+"agent/ideas/view/"+c.idea_id;
a.addItem("ideas",c.idea_id,c.title,b)});DeskPRO_Window.getMessageBroker().addMessageListener("ui.tab.opened",function(b){a.removeItem(b.type,b.id)
});this.addItem("tickets",1,"Custom style","");this.addItem("tickets",2,"Survey module","");this.addItem("tickets",3,"New user from gateway","");
this.addItem("tickets",4,"Invoices","");this.addItem("tickets",5,"Company CC emails log","");this.addItem("tickets",6,"Email to user ticket participants","");
this.addItem("chats",1,"New chat","");this.addItem("chats",2,"New chat 2","")},addItem:function(d,f,e,c){var b=(new Date()).toUTCString();
var a=$('<li class="'+d+" "+d+"-"+f+'" data-type="'+d+'" data-type-id="'+f+'"><em class="remove">mark as read</em><em class="timeago">'+b+'</em><span data-route="'+c+'">'+e+"</span></li>");
$(".timeago",a).timeago();$("span",a).click(function(){DeskPRO_Window.runPageRouteFromElement(this)});$("#notifications_list").prepend(a);
this.updateCount(d,"add",1);if(DeskPRO_Window.options&&DeskPRO_Window.options.desktopNotifications){Orb.DesktopNotify.show({title:e,content:"Click to open",click:function(){DeskPRO_Window.runPageRouteFromElement(a)
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
var c=$("span",this).text();$("#window_search_type").removeClass("all users tickets").addClass(b).data("search-type",b);$("#window_search_type .type-text").text(c)
});var a=this;$("#search-form").submit(function(b){b.preventDefault();a.navToFirst()})},navToFirst:function(){var a=$("#window_search_rusults_wrap .results-list li[data-route]:first");
if(a.length){DeskPRO_Window.runPageRouteFromElement(a);this.closeMenu()}}});Orb.createNamespace("DeskPRO.UI.OmniSearch");
DeskPRO.UI.OmniSearch.SearchBox=new Orb.Class({Implements:[Orb.Util.Options,Orb.Util.Events],initialize:function(b){this.options={wrapperEl:null,inputEl:null,contextBtnEl:null};
this.setOptions(this.getDefaultOptions());this.setOptions(b);var a=this;this.wrapperEl=$(this.options.wrapperEl);this.inputEl=$(this.options.inputEl);
this.contextBtnEl=$(this.options.contextBtnEl);$(".clear-trigger:first",this.wrapperEl).click((function(c){c.preventDefault();
this.clear()}).bind(this));$(".builder-trigger:first",this.wrapperEl).click((function(c){c.preventDefault();this.showBuilder()
}).bind(this));this.contexts={};this.activeContextId=null;this.wrapperEl.click(function(c){if(c.target==this){a.inputEl.focus()
}});this.inputEl.keypress(function(c){if(c.which==58||c.which==61){c.preventDefault();var d=$(this).val().trim();$(this).val("");
$(this).blur();a.addSearchTermByTrigger(d)}else{if(c.which==8){if(!$(this).val().trim().length){var e=a.inputEl.prev();if(e.length&&e.is(".term")){a.removeSearchTerm(e)
}}}}});$(".remove-all-terms-trigger:first",this.wrapperEl).click(function(c){c.preventDefault();c.stopPropagation();$(".term",a.wrapperEl).each(function(){a.removeSearchTerm($(this))
});a.inputEl.focus()});this.addEvent("termInputDone",function(){a.inputEl.focus()});this.init()},getDefaultOptions:function(){return{}
},init:function(){},_getContextMenuEl:function(){if(!this.contextMenuEl){this.contextMenuEl=$('<ul style="display:none;" id="omnisearch_context_menu" />');
this.contextMenuEl.appendTo("body");this.contextMenu=new DeskPRO.UI.Menu({triggerElement:this.contextBtnEl,menuElement:this.contextMenuEl,defaultEventContext:this,onItemClicked:function(b){var a=$(b.itemEl).data("context");
this.activateContext(a)}})}return this.contextMenuEl},addContext:function(c,b){this.contexts[c]=b;var a=$("<li />");a.text(b.getLabel());
a.data("context",c);a.appendTo(this._getContextMenuEl());if(!this.activeContext){this.activateContext(c)}},getContext:function(a){return this.contexts[a]
},activateContext:function(b){if(this.activeContextId){this.getContext(this.activeContextId).fireEvent("deactivate");this.activeContextId=null
}this.activeContextId=b;var a=this.getContext(b);a.fireEvent("activate");$(".label",this.contextBtnEl).text(a.getLabel())
},getActiveContext:function(){return this.getContext(this.activeContextId)},getActiveContextId:function(){return this.activeContextId
},addSearchTermByTrigger:function(f){var d=this.getActiveContext();var b=d.getTermIdByTrigger(f);if(!b){return}var c=d.getTerm(b);
var e=c.createTermElement(this);e.data("term",b);e.insertBefore(this.inputEl);if(e.data("handler")&&e.data("handler").afterAdd){e.data("handler").afterAdd(e)
}var a=$(".remove-term-trigger:first",e);if(a.length){a.click((function(){this.removeSearchTerm(e)}).bind(this))}this.wrapperEl.addClass("with-terms")
},removeSearchTerm:function(a){if(a.data("handler")&&a.data("handler").destroy){a.data("handler").destroy()}a.remove();if(!$(".term:first",this.wrapperEl).length){this.wrapperEl.removeClass("with-terms")
}},showBuilder:function(){},clear:function(){},getFormTermNamePrefix:function(){return"terms["+Orb.uuid()+"]"}});Orb.createNamespace("DeskPRO.Agent");
DeskPRO.Agent.OmniSearchBox=new Orb.Class({Extends:DeskPRO.UI.OmniSearch.SearchBox,getDefaultOptions:function(){return{wrapperEl:"#omnisearch",inputEl:"#omnisearch_input",contextBtnEl:"#omnisearch_type"}
},init:function(){var b=this;var c=$("#ticket_search_terms_global");var a=new DeskPRO.UI.OmniSearch.Context.TicketsContext();
$("div.type[data-term-type]",c).each(function(){var e=$(this);var l=e.data("term-type");var g=e.data("rule-type");var k=e.attr("title");
var i=e.data("term-triggers").split(",");var f=null;switch(l){case"GenericInputTerm":var f=new DeskPRO.UI.OmniSearch.Term.GenericInputTerm({inputName:h,label:k,fields:{op:"is",type:g},triggerWords:i});
break;case"GenericMenuTerm":var j=$("<ul />").hide();var d=$(".options select",e);var h=d.attr("name");$("option",d).each(function(){var m=$("<li />");
m.data("prop-val",d.val());m.text($(this).text().trim());j.append(m)});j.appendTo("body");var f=new DeskPRO.UI.OmniSearch.Term.GenericMenuTerm({menuEl:j,inputName:h,label:k,menuDataKey:"prop-val",fields:{op:"is",type:g},triggerWords:i});
break;case"GenericDateTerm":var f=new DeskPRO.UI.OmniSearch.Term.GenericDateTerm({inputName:h,label:k,fields:{op:"is",type:g},triggerWords:i});
break}if(f){a.addTerm(g,f)}});this.addContext("tickets",a)}});Orb.createNamespace("DeskPRO.UI.OmniSearch.Context");DeskPRO.UI.OmniSearch.Context.ContextAbstract=new Orb.Class({Implements:[Orb.Util.Options,Orb.Util.Events],initialize:function(a){this.options=this.getDefaultOptions();
this.setOptions(a);this.terms={};this.init();this.triggerWords={}},init:function(){},getDefaultOptions:function(){return{}
},addTerm:function(b,a){this.terms[b]=a;Array.each(a.getTriggerWords(),function(c){this.triggerWords[c]=b},this)},getTerm:function(a){return this.terms[a]||null
},getTermLabels:function(){var a={};Object.each(this.terms,function(b,c){a[c]=b.getLabel()});return a},getTermIdByTrigger:function(a){return this.triggerWords[a.toLowerCase()]||null
},getTermByTrigger:function(b){var a=this.getTermIdFromTrigger(b);if(!a){return null}return this.getTerm(a)},getLabel:function(){console.error("Abstract method")
}});Orb.createNamespace("DeskPRO.UI.OmniSearch.Context");DeskPRO.UI.OmniSearch.Context.TicketsContext=new Orb.Class({Extends:DeskPRO.UI.OmniSearch.Context.ContextAbstract,init:function(){},getLabel:function(){return"Tickets"
}});Orb.createNamespace("DeskPRO.UI.OmniSearch.Term");DeskPRO.UI.OmniSearch.Term.TermAbstract=new Orb.Class({Implements:[Orb.Util.Options,Orb.Util.Events],initialize:function(a){this.options=this.getDefaultOptions();
this.setOptions(a);this.terms={};this.init()},init:function(){},createTermElement:function(c){var a=document.getElementById("omnisearch_term_tpl").innerHTML;
var b=$(a);$(".label",b).text(this.getLabel());$(".value",b).text("double-click to edit");return b},getDefaultOptions:function(){return{}
},getTriggerWords:function(){return this.options.triggerWords},getLabel:function(){return this.options.label}});Orb.createNamespace("DeskPRO.UI.OmniSearch.Term.Tickets");
DeskPRO.UI.OmniSearch.Term.GenericInputTerm=new Orb.Class({Extends:DeskPRO.UI.OmniSearch.Term.TermAbstract,createTermElement:function(h){var c=document.getElementById("omnisearch_term_input_tpl").innerHTML;
var f=$(c);var b=$("input",f);var d=$(".display-value",f);var g=h.getFormTermNamePrefix();$(".label",f).text(this.getLabel());
d.text("double-click to edit");f.addClass("edit");f.click(function(){f.addClass("edit");b.focus()});var a=function(){f.removeClass("edit");
var i=b.val().trim();d.text(i);if(!i.length){h.removeSearchTerm(f)}h.fireEvent("termInputDone",[f])};b.blur(a).keypress(function(i){if(i.which==13||i.which==9){a()
}});b.attr("name",g+"["+this.getInputName()+"]");Object.each(this.getHiddenFields(),function(k,i){var j=$('<input type="hidden" />');
j.attr("name",g+"["+i+"]");j.val(k);j.appendTo(f)});var e={afterAdd:function(i){b.focus()}};f.data("handler",e);return f},getHiddenFields:function(){return this.options.fields
},getInputName:function(){return this.options.inputName}});Orb.createNamespace("DeskPRO.UI.OmniSearch.Term.Tickets");DeskPRO.UI.OmniSearch.Term.GenericMenuTerm=new Orb.Class({Extends:DeskPRO.UI.OmniSearch.Term.TermAbstract,createTermElement:function(i){var b=this.parent(i);
var g=$(".value",b).text("");var d=i.getFormTermNamePrefix();var c=this.getDataKey();var e=this.getHiddenFields();var f=this.getInputName();
e[f]="";Object.each(e,function(m,k){var l=$('<input type="hidden" />');l.attr("name",d+"["+k+"]");l.val(m);if(k==f){l.addClass("term-val")
}l.appendTo(b)});var h=$("input.term-val",b);var a=new DeskPRO.UI.Menu({triggerElement:g,menuElement:this.options.menuEl,onItemClicked:function(l){var m=$(l.itemEl).data(c);
var k=$(l.itemEl).text().trim();if(!m){i.removeSearchTerm(b)}else{h.val(m);g.text(k)}},onMenuClosed:function(){if(!h.val()){i.removeSearchTerm(b)
}}});var j={afterAdd:function(k){a.open({target:k})}};b.data("handler",j);return b},getHiddenFields:function(){return this.options.fields
},getInputName:function(){return this.options.inputName},getDataKey:function(){return this.options.menuDataKey}});Orb.createNamespace("DeskPRO.UI.OmniSearch.Term.Tickets");
DeskPRO.UI.OmniSearch.Term.GenericDateTerm=new Orb.Class({Extends:DeskPRO.UI.OmniSearch.Term.TermAbstract,createTermElement:function(h){var c=document.getElementById("omnisearch_term_date_tpl").innerHTML;
var f=$(c);var d=$(".display-value",f).text("");var g=h.getFormTermNamePrefix();var b=this.getHiddenFields();Object.each(b,function(k,i){var j=$('<input type="hidden" />');
j.attr("name",g+"["+i+"]");j.val(k);j.appendTo(f)});var a=new DeskPRO.UI.DateChooser({rowEl:f});var e={afterAdd:function(i){a.open()
},destroy:function(){a.destroy()}};f.data("handler",e);return f},getDateChooser:function(){return this.options.dateChooser
},getHiddenFields:function(){return this.options.fields},getInputName:function(){return this.options.inputName}});