Orb.createNamespace("DeskPRO.Agent.WindowElement.Section");DeskPRO.Agent.WindowElement.Section.AbstractSection=new Orb.Class({Implements:[Orb.Util.Events,Orb.Util.Options],initialize:function(){this.addEvent("show",this.onShow);
this.addEvent("show",this._onFirstShowFire);this.addEvent("show",this._onShowSetVisible);this.addEvent("show",this._onShowActivateList);
this.addEvent("firstshow",this.onFirstShow);this.addEvent("hide",this.onHide);this.addEvent("hide",this._onHideSetVisible);
this.addEvent("hide",this._onHideDeactivateList);this._isVisible=false;this.init()},init:function(){},onShow:function(){},onFirstShow:function(){},onHide:function(){},setHasInitialLoaded:function(){this.hasLoaded=true;
$("#deskpro_outline_loading").removeClass("on")},setButtonElement:function(a){this.buttonEl=a},getButtonElement:function(){return this.buttonEl
},getSectionElement:function(){if(this.sectionEl){return this.sectionEl}return null},getListElement:function(){if(!this.listEl){this.setListElement()
}return this.listEl},setSectionElement:function(c,b){if(this.sectionEl){this.sectionEl.remove()}if(!c){c=$("<section></section>");
c.attr("id",Orb.getUniqueId("outline_"))}this.sectionEl=c;if(!c.parent().is("#deskpro_outline")){this.sectionEl.detach().appendTo("#deskpro_outline")
}if(!b){b=$("section.content",c);if(!b.length){var a=[];a.push('<div class="with-scrollbar '+this.sectionEl.attr("id")+'">');
a.push('<div class="scrollbar"><div class="track"><div class="thumb"><div class="end"></div></div></div></div>');a.push('<div class="scroll-viewport"><div class="scroll-content">');
a=a.join("");c=$(a);this.sectionEl.append(c);b=$("div.scroll-content:first",c)}}this.contentEl=b;var d=$(".with-scrollbar:first",this.sectionEl);
if(d.length){this.scrollerHandler=new DeskPRO.Agent.ScrollerHandler(this,d,{showEvent:"show",hideEvent:"hide"})}},setListElement:function(b,a){if(this.listEl){this.listEl.remove()
}if(!b){b=$("<section></section>");b.attr("id",Orb.getUniqueId("list_"))}this.listEl=b;if(!b.parent().is("#deskpro_list")){this.listEl.detach().appendTo("#deskpro_list")
}if(!a){a=$('<section class="content"></section>')}this.listEl.append(a);this.listContentEl=a},setListPageFragment:function(b){if(this.listPage){this.listPage.fireEvent("destroy");
this.listPage=null}this.listPage=b;var a=$("section.content:first",this.getListElement());a.empty();a.html(b.html);this.getListElement().addClass("on");
$("#deskpro_list_loading").removeClass("on");b.fireEvent("render",[a]);b.fireEvent("activate")},isVisible:function(){return this._isVisible
},updateBadge:function(d){var c=$(".nav-counter",this.buttonEl);var b=$("span",c);var d=parseInt(d);var a=d;if(d){if(a>=1000){a="1000+"
}b.html(a);c.show()}else{d=0;b.html("0");c.hide()}this.badgeCount=d;DeskPRO_Window.getMessageBroker().sendMessage("agent.ui.badge_updated",{section:this,sectionId:this.buttonEl.attr("id"),count:d})
},getBadgeCount:function(){return this.badgeCount||0},_onShowSetVisible:function(){this._isVisible=true},_onHideSetVisible:function(){this._isVisible=false
},_onFirstShowFire:function(){if(this.has_shown){return}this.has_shown=true;this.fireEvent("firstshow");this._loadAutoLoadRoutes()
},_loadAutoLoadRoutes:function(){if(DeskPRO_Window.getDebug("noAutoLoadList")){return}var a=$(".auto-load-route",this.sectionEl);
if(!a.length||!a.data("route")){return}DeskPRO_Window.runPageRoute(a.data("route"))},_onShowActivateList:function(){if(this.listPage){this.listPage.fireEvent("activate")
}if(this.hasLoaded){$("#deskpro_outline_loading").hide()}},_onHideDeactivateList:function(){if(this.listPage){this.listPage.fireEvent("deactivate")
}}});Orb.createNamespace("DeskPRO.Agent.WindowElement.Section");DeskPRO.Agent.WindowElement.Section.Tickets=new Orb.Class({Extends:DeskPRO.Agent.WindowElement.Section.AbstractSection,init:function(){this.buttonEl=$("#tickets_section");
this.setSectionElement($('<section id="tickets_outline"></section>'));DeskPRO_Window.getMessageChanneler().subscribeChannel("agent.filter-update",this.filterUpdated.bind(this));
DeskPRO_Window.getMessageChanneler().subscribeChannel("agent.new-recent-search",this.refreshRecentSearches.bind(this));DeskPRO_Window.getMessageChanneler().subscribeChannel("list-page-fragment.activated",this.highlightActiveSection.bind(this));
var a=this;this.getSectionElement().delegate("li[data-route]","click",function(){a.highlightNavItem($(this))});$.ajax({url:BASE_URL+"agent/tickets/get-section-data.json",context:this,success:function(b){this._initSection(b)
}})},_initSection:function(b){this.setHasInitialLoaded();this.sectionEl.html(b.section_html);var a=this;this.tabs=new DeskPRO.UI.SimpleTabs({context:this.sectionEl,triggerElements:$("#tickets_outline_tabstrip li"),onTabSwitch:function(c){if(c.tabEl.is(".labels")){a.showLabelsList()
}else{if(c.tabEl.is(".flagged")){a.loadFlagCounts()}}}});this.inboxViewTabs=new DeskPRO.UI.SimpleTabs({triggerElements:$("#tickets_outline_viewtypetabs li")});
this._initFilters();this._initFlagged();$("#user_settings_filters_link").click(function(){var c=new DeskPRO.UI.Overlay({contentMethod:"iframe",iframeUrl:BASE_URL+"agent/settings/ticket-filters"});
c.openOverlay()});if(this.isVisible()&&!DeskPRO_Window.loadingListFragment){this._loadAutoLoadRoutes()}this.activeNavClass=null
},onShow:function(){this.activeNavClass=null},highlightActiveSection:function(b){if(!this.isVisible()){return}var a=b.page;
if(a.TYPENAME=="ticket-filter"){this.activeNavClass=".nav-filter-"+a.getMetaData("filter_id")}else{if(a.TYPENAME=="ticket-custom-filter"){if(a.getMetaData("recent_search_id")){this.activeNavClass=".nav-recent-search-"+a.getMetaData("recent_search_id")
}}}this.highlightNav()},highlightNav:function(){$(".active-nav",this.getSectionElement()).removeClass("active-nav");if(this.activeNavClass){$(this.activeNavClass,this.getSectionElement()).addClass("active-nav")
}},highlightNavItem:function(a){$(".active-nav",this.getSectionElement()).removeClass("active-nav");a.addClass("active-nav")
},_initFilters:function(){DeskPRO_Window.getPoller().addData([{name:"do[]",value:"get-sys-filter-counts"}],"filters.counts",{recurring:true,minDelay:120000});
DeskPRO_Window.getPoller().addData([{name:"do[]",value:"get-custom-filter-counts"}],"filters.counts",{recurring:true,minDelay:600000,minDelayAfterOne:true});
DeskPRO_Window.getMessageBroker().addMessageListener("filters.counts",this.updateFilterCounts.bind(this));$("ul#tickets_outline_filters_list").sortable({axis:"y",distance:8,update:function(){var a=[];
$("ul#tickets_outline_filters_list > li").each(function(){var b=$(this).data("filter-id");if(b){a.push({name:"prefs[agent.ui.ticket-filters-order][]",value:b})
}});$.ajax({timeout:20000,type:"POST",url:BASE_URL+"agent/misc/ajax-save-prefs",data:a})}});$("#tickets_outline_inbox_list .sub-toggle").click(function(c){c.stopPropagation();
var a=$(this).parent();var b=$("ul.sub-group",a);if(b.is(":visible")){b.slideUp();$(this).removeClass("open")}else{b.slideDown();
$(this).addClass("open")}})},getFilterCount:function(a){return parseInt($("#ticket_filter_"+a+"_count").data("count")||0)
},setFilterCount:function(e,d){var c=d;e=parseInt(e);if(d>1000){c="1000+"}var a=DeskPRO_Window.getData("systemFilters")[e];
if(a){if(a=="all"){this.updateBadge(d)}var b=$("#ticket_filter_"+e+"_count").html(c).data("count",d)}else{var b=$("#ticket_filter_"+e+"_count").html(c).data("count",d)
}},updateFilterCounts:function(a){Object.each(a,function(b,c){this.setFilterCount(c,b)},this)},filterUpdated:function(c){var a=this.getFilterCount(c.filter_id);
var b=null;if(this.listPage&&this.listPage.meta.filter_id==c.filter_id){b=this.listPage}if(c.op=="add"){a++;this.setFilterCount(c.filter_id,a);
if(b&&c.ticket_id){b.addTicket(c.ticket_id)}}else{if(c.op=="del"){a--;if(a<1){a=0}this.setFilterCount(c.filter_id,a);if(b&&c.ticket_id){b.delTicket(c.ticket_id)
}}}},refreshRecentSearches:function(){$.ajax({url:BASE_URL+"agent/ticket-search/get-recent-search-list",type:"GET",context:this,dataType:"html",success:function(a){$("#tickets_outline_searches_list").empty().html(a);
this.highlightNav()}})},_initOverview:function(){this.overviewGroupMenuEl=null;this.overviewGroupEl1=null;this.overviewGroupEl2=null;
this.overviewGroupEl2_yes=null;this.overviewGroupingMenu=null;this.overviewModeMenu=null;this._initoverviewGroupingMenu();
this.overviewLoadList()},_initoverviewGroupingMenu:function(){this.overviewGroupMenuEl=$("#overview_grouping_menu");this.overviewModeMenuEl=$("#overview_mode_menu");
this.overviewModeEl=$("#ticket_grouping_options .mode");this.overviewGroupEl1=$("#ticket_grouping_options .grouping1");this.overviewGroupEl2=$("#ticket_grouping_options .grouping2");
var a=this;this.overviewGroupingMenu=new DeskPRO.UI.Menu({triggerElement:$("#ticket_grouping_options .grouping-menu-trigger"),menuElement:this.overviewGroupMenuEl,onItemClicked:function(b){b.event.stopPropagation();
a._handleGroupingChanged(b)},onBeforeMenuOpened:function(e){$("li[data-groupby]",a.overviewGroupMenuEl).show();var d=e.menu.getOpenTriggerEvent();
var c=$(d.target);if(c.is(".grouping1")){$('li[data-groupby="none"]',a.overviewGroupMenuEl).hide()}else{var b=a.overviewGroupEl1.data("groupby");
$('li[data-groupby="'+b+'"]',a.overviewGroupMenuEl).hide()}}});this.overviewModeMenu=new DeskPRO.UI.Menu({triggerElement:$("#ticket_grouping_options .mode-menu-trigger"),menuElement:this.overviewModeMenuEl,onItemClicked:function(b){a._handleModeChanged(b)
}})},_handleModeChanged:function(c){var a=$(c.itemEl);var d=a.data("mode");var b=a.text();this.overviewModeEl.text(b).data("mode",d);
this.overviewLoadList()},_handleGroupingChanged:function(f){var d=this.overviewGroupEl1.data("groupby");var b=this.overviewGroupEl2.data("groupby");
var e=f.menu.getOpenTriggerEvent();var c=$(e.target);var a=$(f.itemEl);if(c.is(".grouping1")){d=a.data("groupby");if(d==b){b=""
}}else{b=a.data("groupby")}if(!d||d=="none"){d="department"}if(!b||b=="none"){b=""}this.overviewUpdateGrouping(d,b);this.overviewLoadList()
},overviewUpdateGrouping:function(c,b){var d=$('[data-groupby="'+c+'"]',this.overviewGroupMenuEl);if(b&&b.length){var a=$('[data-groupby="'+b+'"]',this.overviewGroupMenuEl)
}else{b=false;var a=$()}this.overviewGroupEl1.html(d.html()).data("groupby",c);if(b){this.overviewGroupEl2.html(a.html()).data("groupby",b)
}else{this.overviewGroupEl2.html("none").data("groupby","")}},overviewLoadList:function(){var b=this.overviewGroupEl1.data("groupby");
var a=this.overviewGroupEl2.data("groupby")||"";var d=this.overviewModeEl.data("mode");var c={group1:b,group2:a,mode:d};$.ajax({url:BASE_URL+"agent/ticket-search/overview-nav",type:"GET",data:c,context:this,dataType:"html",success:function(e){this._overviewGroupListLoaded(e)
}})},_overviewGroupListLoaded:function(a){var b=$("#ticket_grouping_list > ul").html(a)},_initFlagged:function(){DeskPRO_Window.getMessageBroker().addMessageListener("filter-flagged.counts",this.updateFlagCounts.bind(this));
DeskPRO_Window.getMessageBroker().addMessageListener("filter-flagged.flag-changed",this.changeFlagCountsForSwitch.bind(this));
var a=this;$("#tickets_outline_flagged ul").sortable({axis:"y",distance:8,update:function(){var b=[];$("#tickets_outline_flagged ul > li").each(function(){var c=$(this).data("flag");
if(c){b.push({name:"prefs[agent.ui.ticket-flag-order][]",value:c})}});$.ajax({timeout:20000,type:"POST",url:BASE_URL+"agent/misc/ajax-save-prefs",data:b})
}});$("#tickets_outline_flagged li").dblclick(function(e){e.preventDefault();e.stopPropagation();var b=$(this);var f=$('<input type="text" />');
f.val($("a",b).text().trim());var d=function(i){if(i.keyCode==13&&!i.metaKey){h()}};var h=function(){var i=f.val().trim();
if(i.length){$.ajax({type:"POST",url:BASE_URL+"agent/misc/ajax-save-prefs",data:[{name:"prefs[agent.ui.flag."+b.data("flag")+"]",value:i}]});
$("a",b).text(i)}c.remove();g.remove()};var c=$('<div class="backdrop"></div>');c.appendTo("body");c.click(h);var g=$('<div class="field-overlay"><div class="close-trigger"></div></div>');
f.appendTo(g);g.css({left:b.offset().left,top:b.offset().top});g.appendTo("body").show();f.keypress(d).focus();$(".close-trigger",g).click(h)
})},loadFlagCounts:function(){$.ajax({url:BASE_URL+"agent/tickets/get-flagged-section-data.json",context:this,success:function(a){this.updateFlagCounts(a.flag_counts)
}})},updateFlagCounts:function(a){$("ol#ticket_flagged_list span.list-counter").html("0");Object.each(a,(function(c,b){this.updateFlagCountFor(b,c)
}).bind(this))},updateFlagCountFor:function(a,d){var c=d;if(d>=1000){c="1000+"}else{if(d<0){d=0;c="0"}}var b=$("#ticket_flag_"+a+"_count").html(c)
},changeFlagCountsForSwitch:function(c){var b=parseInt($("#ticket_flag_"+c.old_flag+"_count").html());var a=parseInt($("#ticket_flag_"+c.new_flag+"_count").html());
this.updateFlagCountFor(c.old_flag,b-1);this.updateFlagCountFor(c.new_flag,a+1)},showLabelsList:function(){if(this.hasLoadedLabels){return
}this.hasLoadedLabels=true;$.ajax({timeout:20000,type:"POST",url:BASE_URL+"agent/ticket-search/labels-index-pane",dataType:"html",context:this,success:function(a){this._setLabelsList(a)
}})},_setLabelsList:function(a){$("#tickets_outline_labels").html(a);this.labelsTabs=new DeskPRO.UI.SimpleTabs({context:$("#tickets_outline_labels"),triggerElements:$("#tickets_outline_labels .deskpro-sub-tabstrip li")})
}});Orb.createNamespace("DeskPRO.Agent.WindowElement.Section");DeskPRO.Agent.WindowElement.Section.People=new Orb.Class({Extends:DeskPRO.Agent.WindowElement.Section.AbstractSection,init:function(){this.buttonEl=$("#tickets_section");
this.setSectionElement($('<section id="people_outline"></section>'));$.ajax({url:BASE_URL+"agent/people/get-section-data.json",context:this,success:function(a){this._initSection(a)
}})},_initSection:function(b){this.setHasInitialLoaded();this.contentEl.html(b.section_html);var a=this;this.peopleTabs=new DeskPRO.UI.SimpleTabs({context:this.sectionEl,triggerElements:$("#people_outline_tabstrip li"),onTabSwitch:function(c){}});
this.orgTabs=new DeskPRO.UI.SimpleTabs({context:this.sectionEl,triggerElements:$("#people_outline_org_tabstrip li"),onTabSwitch:function(c){}});
this.contentEl.addClass("scroll-content").tinyscrollbar();if(this.isVisible()){this._onShowLoadList()}}});Orb.createNamespace("DeskPRO.Agent.WindowElement.Section");
DeskPRO.Agent.WindowElement.Section.Publish=new Orb.Class({Extends:DeskPRO.Agent.WindowElement.Section.AbstractSection,init:function(){this.buttonEl=$("#publish_section");
this.setSectionElement($('<section id="publish_outline"></section>'));$.ajax({url:BASE_URL+"agent/publish/get-section-data.json",context:this,success:function(a){this._initSection(a)
}})},_initSection:function(b){this.setHasInitialLoaded();this.contentEl.html(b.section_html);var a=this;this.typeTabs=new DeskPRO.UI.SimpleTabs({context:this.sectionEl,triggerElements:$("#publish_outline_tabstrip li"),onTabSwitch:function(c){var d=c.tabContent.data("editor-class");
if(d){$("#publish_outline_edit_cats").data("editor-class",d).show()}else{$("#publish_outline_edit_cats").hide()}}});$("#publish_outline_edit_cats").click(function(){a.openCatEditor($(this).data("editor-class"))
});this._initGlossary()},_initGlossary:function(){this.glossaryWrapper=$("#publish_outline_glossary");var a=this;$(".glossary-new-trigger",this.glossaryWrapper).click(this.showGlossaryAddDlg.bind(this));
$(".glossary-word-trigger",this.glossaryWrapper).click(function(b){b.preventDefault();a.showGlossaryEditDlg($(this).data("word-id"))
})},showGlossaryAddDlg:function(){var a=this.getGlossaryAddDlg();a.openOverlay()},showGlossaryEditDlg:function(d){var a=this.getGlossaryEditDlg();
var b=$(".form",a.elements.wrapper);var c=$(".loading",a.elements.wrapper);b.hide();c.show();a.openOverlay();$.ajax({url:BASE_URL+"agent/glossary/"+d+".json",type:"GET",context:this,dataType:"json",success:function(e){$(".word",b).html(e.word);
$("input.word_id",b).val(e.id);$("textarea.content",b).val(e.content);c.hide();b.show()}})},getGlossaryAddDlg:function(){if(this.addDlg){return this.addDlg
}var a=$(".glossary-add-dlg:first",this.glossaryWrapper);this.addDlg=new DeskPRO.UI.Overlay({contentElement:a});$(".save-trigger",a).click(this.saveNewWord.bind(this));
return this.addDlg},getGlossaryEditDlg:function(){if(this.editDlg){return this.editDlg}var a=$(".glossary-edit-dlg:first",this.glossaryWrapper);
this.editDlg=new DeskPRO.UI.Overlay({contentElement:a});$(".save-trigger",a).click(this.saveEditWord.bind(this));return this.editDlg
},saveNewWord:function(){var a=[];a.push({name:"word",value:$("input.word",this.addDlg.elements.wrapperOuter).val().trim()});
a.push({name:"content",value:$("textarea.content",this.addDlg.elements.wrapperOuter).val().trim()});$.ajax({url:BASE_URL+"agent/glossary/new-word.json",type:"POST",data:a,context:this,dataType:"json",success:function(h){var b=$(".counter-words",this.glossaryWrapper);
var f=parseInt(b.html());b.html(f+1);var e=h.letter;var c=h.word;var g=h.word_id;var j=$('<li><a class="edit-word-trigger" data-word-id="'+g+'">'+c+"</a></li>");
var d=$('dt[data-letter="'+e+'"]:first',this.glossaryWrapper);var i=$('dd[data-letter="'+e+'"]:first',this.glossaryWrapper);
d.show();i.show();$("ul",i).prepend(j);$("input.word",this.addDlg.elements.wrapperOuter).val("");$("textarea.content",this.addDlg.elements.wrapperOuter).val("");
this.addDlg.closeOverlay()}})},saveEditWord:function(){var b=$("input.word_id",this.editDlg.elements.wrapperOuter).val().trim();
var a=[];a.push({name:"word_id",value:b});a.push({name:"content",value:$("textarea.content",this.editDlg.elements.wrapperOuter).val().trim()});
$.ajax({url:BASE_URL+"agent/glossary/"+b+"/edit.json",type:"POST",data:a,context:this,dataType:"json",success:function(c){}})
},openCatEditor:function(a){if(!this.catEditors){this.catEditors={}}this._initCatEditor(a);if(!this.catEditors[a]){console.error("No category editor for %s",a);
return}var b=this.catEditors[a];b.open()},_initCatEditor:function(a){if(this.catEditors[a]){return}var c=new DeskPRO.Agent.PageHelper.CategoryEdit({wrapper:$("."+a,this.contentEl)});
this.catEditors[a]=c;var b=c.wrapper.data("type");c.addEvent("save",function(d){var e=d.encodeForm();$.ajax({url:BASE_URL+"agent/publish/save-categories/"+b,data:e,type:"GET",dataType:"json"})
})}});Orb.createNamespace("DeskPRO.Agent.WindowElement.Section");DeskPRO.Agent.WindowElement.Section.Twitter=new Orb.Class({Extends:DeskPRO.Agent.WindowElement.Section.AbstractSection,init:function(){this.buttonEl=$("#twitter_section");
this.setSectionElement($('<section id="twitter_outline"></section>'));$.ajax({url:BASE_URL+"agent/twitter/get-section-data.json",context:this,success:function(a){this._initSection(a)
}})},_initSection:function(a){this.setHasInitialLoaded();this.contentEl.html(a.section_html);if(this.isVisible()){this._onShowLoadList()
}this.contentEl.addClass("scroll-content").tinyscrollbar()}});Orb.createNamespace("DeskPRO.Agent.WindowElement.Section");
DeskPRO.Agent.WindowElement.Section.AgentChat=new Orb.Class({Extends:DeskPRO.Agent.WindowElement.Section.AbstractSection,init:function(){this.buttonEl=$("#agent_chat_section");
this.chatsWrapper=$("#agent_chats_wrapper");this.setSectionElement($('<section id="agent_chat_outline"></section>'));$("#agent_chat_conversation").template("agent_chat_conversation");
$("#agent_chat_message").template("agent_chat_message");$("#agent_chat_message_me").template("agent_chat_message_me");this._initMessageHandlers();
this._initInterface()},onShow:function(){$.ajax({url:BASE_URL+"agent/agent-chat/get-section-data.json",context:this,success:function(a){this.setHasInitialLoaded();
this.contentEl.html(a.section_html)}})},_initMessageHandlers:function(){DeskPRO_Window.getMessageChanneler().subscribeChannel("agent_chat.new-message");
DeskPRO_Window.getMessageChanneler().subscribeChannel("agent.new-agent-online");DeskPRO_Window.getMessageBroker().addMessageListener("agent_chat.new-message",this.showNewMessage.bind(this));
DeskPRO_Window.getMessageBroker().addMessageListener("agent.new-agent-online",(function(a){var b=a.agent_id;this.addOnlineAgent.bind(b)
}).bind(this))},_initInterface:function(){this.panelEl=$("#agent_chat_panel");this.onlineListEl=$("#agent_online_list");this.offlineListEl=$("#agent_offline_list");
this.onlineCountEl=$("#chat_online_count");$(".show-offline-opt",this.panelEl).click(function(){if($(this).is(":checked")){$("#agent_chat_panel").addClass("show-offline")
}else{$("#agent_chat_panel").removeClass("show-offline")}});this.panelEl.click(function(b){b.stopPropagation()});$(".show-section",this.panelEl).click(function(){DeskPRO_Window.switchToSection("agent_chat_section")
});$("#agent_chat_section").click((function(b){b.stopPropagation();this.panelEl.toggleClass("open")}).bind(this));$("body, #agent_chat_panel .close-trigger").click((function(){this.panelEl.removeClass("open");
$("> section",this.chatsWrapper).removeClass("open")}).bind(this));$.ajax({url:BASE_URL+"agent/agent-chat/get-online-agents.json",context:this,success:function(b){if(b.online_agents){Array.each(b.online_agents,function(c){this.addOnlineAgent(c)
},this)}this._initDemo()}});this.chatsWrapper.click(function(b){b.stopPropagation()});var a=this;this.onlineListEl.delegate("li","click",function(c){c.stopPropagation();
var d=$(this).data("agent-id");var b=$(this).data("agent-short-name");var e=$(this).data("picture-url");a.addChatBox(b,d,e);
a.openChatBox(d);a.panelEl.removeClass("open")});this.listTabs=new DeskPRO.UI.SimpleTabs({triggerElements:$("#agent_chat_panel_listviews > li"),context:$("#agent_chat_panel div.wrap:first")})
},_initDemo:function(){var c=$("li:not(.no-agents):first",this.onlineListEl);console.log(c);var a={author_id:c.data("agent-id"),author_name:c.data("agent-name"),author_short_name:c.data("agent-short-name"),author_picture:c.data("picture-url"),author_picture_sizable:c.data("picture-url-sizable")};
console.log(a);var b=["Lorem ipsum dolor sit amet, consectetur adipiscing elit","Proin venenatis, dui vitae congue pretium, enim tellus pharetra ante, vel laoreet purus felis sed orci","Ut bibendum ipsum sed arcu gravida at tristique risus congue","Sed in auctor arcu. Sed nec felis massa, id pulvinar augue","Ut vel nulla sit amet ante pharetra dictum id at nunc","Fusce est est, vestibulum ac ultrices vel, pharetra eget purus","Aliquam mattis ullamcorper laoreet. Fusce facilisis rhoncus rhoncus","Maecenas pellentesque sollicitudin lectus, sed venenatis augue adipiscing ut.","Etiam eget odio dui. Mauris urna odio, gravida tincidunt aliquam nec, aliquet vitae ipsum","In tortor sapien, accumsan vel aliquet eget, egestas quis dui","In id ante eget nisi posuere varius","Fusce gravida, enim sit amet faucibus semper, dui nisl scelerisque mauris, non ultricies sem justo nec magna"];
Array.each(b,function(e,d){if(d%2==0){a.message=e;this.showNewMessage(a)}else{this.showMyMessage(c.data("agent-id"),e)}},this)
},addOnlineAgent:function(d){if(DESKPRO_PERSON_ID&&d==DESKPRO_PERSON_ID){return}var b=$(".agent-"+d,this.offlineListEl);if(!b.length){console.warn("No agent element for %i",d);
return}if($(".agent-"+d,this.onlineListEl).length){return}var a=b.clone();this.onlineListEl.append(a);b.hide();var c=parseInt(this.onlineCountEl.html());
c++;this.onlineCountEl.html(c);$("li.no-agents",this.onlineListEl).hide()},removeOnlineAgent:function(d){var a=$(".agent-"+d,this.onlineListEl);
var c=$(".agent-"+d,this.offlineListEl);if(!a.length){return}a.remove();c.show();var b=parseInt(this.onlineCountEl.html());
b--;this.onlineCountEl.html(b);if(b<1){$("li.no-agents",this.onlineListEl).show()}},addChatBox:function(c,h,f){var e=$("#agent_chat_conversation_"+h);
if(e.length){return}var b=$.tmpl("agent_chat_conversation",{to_agent_name:c,to_agent_id:h,to_agent_picture:f});var g=$("> section.agent-chat:last",this.chatsWrapper);
if(g.length){var d=g.position().left+$("> nav",g).outerWidth()+8;b.css("left",d)}this.chatsWrapper.append(b);b.addClass("new-message");
var i=this;$("textarea",b).keypress(function(j){if(j.keyCode==13&&!j.metaKey){var k=$(this).val().trim();$(this).val("");
i.sendMessage(h,k);i.showMyMessage(h,k)}});var a=$("> nav",b);a.click(function(j){j.stopPropagation();if(b.is(".open")){b.removeClass("open")
}else{i.openChatBox(h)}});$(".close-trigger",a).click(function(j){j.stopPropagation();b.remove()})},getChatBox:function(b){var a=$("#agent_chat_conversation_"+b);
return a},openChatBox:function(b){var a=this.getChatBox(b);if(a.is(".open")){return}$("> section",this.chatsWrapper).removeClass("open");
a.addClass("open");a.removeClass("new-message");$("textarea",a).focus()},getChatContainerForMessage:function(b){var a=$("#agent_chat_conversation_"+b.author_id);
if(!a.length){var c=$(".agent-"+b.author_id,this.offlineListEl);if(c.length){b.author_picture=c.data("picture-url-sizable").replace("{SIZE}",20)
}this.addChatBox(b.author_short_name,b.author_id,b.author_picture)}a=$("#agent_chat_conversation_"+b.author_id);return a},getChatContainer:function(b){var a=$("#agent_chat_conversation_"+b);
if(!a.length){console.warn("No chat box for %i",b);return null}return a},showNewMessage:function(c){var b=this.getChatContainerForMessage(c);
var a=$.tmpl("agent_chat_message",{author_id:c.author_id,author_name:c.author_name,author_picture:c.author_picture,message:c.message});
$(".messages-container:first",b).append(a).scrollTop(100000)},showMyMessage:function(c,d){var b=this.getChatContainer(c);
var a=$.tmpl("agent_chat_message_me",{message:d});$(".messages-container:first",b).append(a).scrollTop(100000)},sendMessage:function(a,b){var c=[];
c.push({name:"content",value:b});$.ajax({url:BASE_URL+"agent/agent-chat/send-agent-message/"+a,data:c,context:this,contentType:"json"})
}});Orb.createNamespace("DeskPRO.Agent.WindowElement.Section");DeskPRO.Agent.WindowElement.Section.UserChat=new Orb.Class({Extends:DeskPRO.Agent.WindowElement.Section.AbstractSection,init:function(){this.buttonEl=$("#chat_section");
this.setSectionElement($('<section id="chat_outline"></section>'));$("#new_user_chat_alert").template("new_user_chat_alert");
$("#new_user_chat_alert_message").template("new_user_chat_alert_message");$("#added_part_user_chat_alert").template("added_part_user_chat_alert");
$("#user_chat_newmsg_sound").template("user_chat_newmsg_sound");this._initMessageHandlers();this.poller=new DeskPRO.AjaxPoller.Poller({ajaxUrl:BASE_URL+"agent/chat/get-section-counts.json",interval:5000,alwaysRequest:true});
this.poller.addEvent("ajaxSuccess",this.handleUpdateCounts.bind(this))},onShow:function(){this.setHasInitialLoaded();$.ajax({url:BASE_URL+"agent/chat/get-section-data.json",context:this,success:function(a){this.contentEl.html(a.section_html)
}})},handleUpdateCounts:function(b){$("#chat_outline .agent-chat-count").hide();$("#userchat_navitem_0 .list-counter").html("0");
if(!b.counts){return}var a=0;Object.each(b.counts,function(c,d){if(d=="0"){a=c}$("#userchat_navitem_"+d+" .list-counter").html(c);
$("#userchat_navitem_"+d).show()});this.updateBadge(a)},_initMessageHandlers:function(){DeskPRO_Window.getMessageChanneler().subscribeChannel("chat.new-chat");
DeskPRO_Window.getMessageChanneler().subscribeChannel("chat.message");DeskPRO_Window.getMessageChanneler().subscribeChannel("chat.chat-ended");
DeskPRO_Window.getMessageChanneler().subscribeChannel("chat_user_agent.chat-assigned");DeskPRO_Window.getMessageChanneler().subscribeChannel("chat_user_agent.added-as-part");
DeskPRO_Window.getMessageBroker().addMessageListener("chat.message",this.handleNewMessage.bind(this));DeskPRO_Window.getMessageBroker().addMessageListener("chat.chat-ended",this.handleChatEnded.bind(this));
DeskPRO_Window.getMessageBroker().addMessageListener("chat.new-chat",this.handleNewChat.bind(this));DeskPRO_Window.getMessageBroker().addMessageListener("chat_user_agent.chat-assigned",this.handleChatAssigned.bind(this));
DeskPRO_Window.getMessageBroker().addMessageListener("chat_user_agent.chat-parts-updated",this.handlePartsUpdated.bind(this));
DeskPRO_Window.getMessageBroker().addMessageListener("chat_user_agent.added-as-part",this.handleAddedAsPart.bind(this))},handleNewMessage:function(a){DeskPRO_Window.getMessageBroker().sendMessage("chat.new-message-"+a.conversation_id,a)
},handleChatEnded:function(a){DeskPRO_Window.getMessageBroker().sendMessage("chat.chat-ended-"+a.conversation_id,a)},handlePartsUpdated:function(a){DeskPRO_Window.getMessageBroker().sendMessage("chat_user_agent.chat-parts-updated-"+a.conversation_id,a)
},handleNewChat:function(a){this.showNewChatAlert(a.conversation_id,{name:a.author_name,message:a.message})},handleChatAssigned:function(b){var a=$("#new_user_chat_alert_"+b.conversation_id);
a.remove()},handleAddedAsPart:function(d){console.log(d);var a=$("#deskpro_tabstrip li.user_chat_tab_"+d.conversation_id);
if(a.length){return}var c=d.conversation_id;var b={name:d.author_name,message:d.message};var e=$.tmpl("added_part_user_chat_alert");
e.appendTo("body");DeskPRO_Window.handleSoundElements(e);$(".dismiss-trigger",e).click(function(){e.remove()});$(".accept-trigger",e).click(function(){DeskPRO_Window.runPageRouteFromElement(this);
e.remove()}).data("route","page:"+BASE_URL+"agent/chat/view/"+c);if(b){var f=$.tmpl("new_user_chat_alert_message",b);$("div.messages",e).append(f).scrollTop(10000)
}},showNewChatAlert:function(b,a){var c=$.tmpl("new_user_chat_alert");c.appendTo("body");DeskPRO_Window.handleSoundElements(c);
$(".dismiss-trigger",c).click(function(){c.remove()});$(".accept-trigger",c).click(function(){DeskPRO_Window.runPageRouteFromElement(this);
c.remove()}).data("route","page:"+BASE_URL+"agent/chat/view/"+b);if(a){var d=$.tmpl("new_user_chat_alert_message",a);$("div.messages",c).append(d).scrollTop(10000)
}}});Orb.createNamespace("DeskPRO.Agent.WindowElement.Section");DeskPRO.Agent.WindowElement.Section.Ideas=new Orb.Class({Extends:DeskPRO.Agent.WindowElement.Section.AbstractSection,init:function(){this.buttonEl=$("#ideas_section");
this.setSectionElement($('<section id="ideas_outline"></section>'));$.ajax({url:BASE_URL+"agent/ideas/get-section-data.json",context:this,success:function(a){this._initSection(a)
}})},_initSection:function(b){this.setHasInitialLoaded();this.contentEl.html(b.section_html);var a=this;this.catTabs=new DeskPRO.UI.SimpleTabs({context:this.sectionEl,triggerElements:$("#ideas_outline_tabstrip li"),onTabSwitch:function(c){}});
this.contentEl.addClass("scroll-content").tinyscrollbar()}});