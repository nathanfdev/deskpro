Orb.createNamespace("DeskPRO.Agent.PageFragment.ListPane");DeskPRO.Agent.PageFragment.ListPane.Basic=new Orb.Class({Extends:DeskPRO.Agent.PageFragment.Basic,initialize:function(a){this.parent(a);
this.addEvent("activate",function(){DeskPRO_Window.getMessageBroker().sendMessage("list-page-fragment.activated",{page:this})
},this)}});Orb.createNamespace("DeskPRO.Agent.PageFragment.ListPane");DeskPRO.Agent.PageFragment.ListPane.BasicTicketResults=new Orb.Class({Extends:DeskPRO.Agent.PageFragment.ListPane.Basic,initializeProperties:function(){this.parent();
this.wrapper=null;this.contentWrapper=null;this.barWrapper=null;this.layout=null;this.overlay=null;this.appendUrl=null;this.actionsBarHelper=null;
this.resultTypeName="basic";this.resultTypeId="general";this.changeManager=null;this.loadFirst=false},initPage:function(e){var k=this;
DeskPRO_Window.getMessageBroker().addMessageListener("agent-notification.tickets.unlocked",(function(a){var l=a.ticket_id;
$(".ticket-"+l,this.contentWrapper).removeClass("locked")}).bind(this));DeskPRO_Window.getMessageBroker().addMessageListener("agent-notification.tickets.locked",(function(a){var l=a.ticket_id;
$(".ticket-"+l,this.contentWrapper).addClass("locked")}).bind(this));DeskPRO_Window.getMessageBroker().addMessageListener("tickets.deleted",(function(l){var a=[];
Array.each(l,function(m){a.push(".ticket-"+m)});a=a.join(", ");$(a,this.contentWrapper).fadeOut(400,function(){$(this).remove()
})}).bind(this));this.loadFirst=this.getMetaData("loadFirst");if(this.loadFirst){this.loadFirst=false;var h=$("td.subject:first a.with-route:first",e);
if(h.length){DeskPRO_Window.runPageRouteFromElement(h)}}this.wrapper=$(e);this.topSection=$(".list-top-area",this.wrapper);
this.barWrapper=$("div.layout-footer:first",this.wrapper);if(!this.barWrapper.length){var b=false;var c=true}else{var b=true;
var c=false}this.contentWrapper=$(".layout-content:first",this.wrapper);if(b){this.listColDrag=new DeskPRO.Agent.PageHelper.ListColDrag({table:$("table:first",this.contentWrapper).get(0),onlyRowSel:".line-2",onlyRowColOffset:2});
this.listColResize=new DeskPRO.Agent.PageHelper.ListColResize({table:$("table:first",this.contentWrapper).get(0)});var d=Orb.getUniqueId("listpane_");
var j=Orb.getUniqueId("listpane_");this.contentWrapper.attr("id",d);this.barWrapper.attr("id",j);if(c){this.layout={wrapper:this.wrapper,paneWrapper:this.wrapper.parent(),content:this.contentWrapper,footer:$(),isFooterOpen:false,doLayout:function(){},expandFooter:function(){},collapseFooter:function(){}}
}else{this.layout=new DeskPRO.Agent.Layout.FooterActionbarLayout(this.wrapper)}}var g=this.contentWrapper;g.tinyscrollbar();
var k=this;$("div.scroll-content:first, div.scroll-viewport:first",this.contentWrapper).resize(function(){g.tinyscrollbar_update();
k.fireEvent("resized")});this.changeManager=new DeskPRO.Agent.TicketList.ChangeManager(this);this._initDisplayOptions();this._initFlagMenu();
this._initGroupingOptions();this._initSearchOptions();if(b){if(c){this.actionsBarHelper={page:null,wrapper:null,contentWrapper:null,tableEl:null,selectedActionData:null,ticketBar:null,barWrapper:null,initOverlay:function(){},getSelectedTicketIds:function(){},setActiveTable:function(){},handleTicketCheckClick:function(){},updateCount:function(){},applyActions:function(){},toggleMacroApplyBtn:function(){},saveActions:function(){}}
}else{this.actionsBarHelper=new DeskPRO.Agent.PageHelper.TicketActionsBar(this);this.actionsBarHelper.setActiveTable($("table.list:first",this.contentWrapper))
}}this.initFeaturesOnCollection(e,{routes:[".with-route"],times:[".timeago"]});if(this.getMetaData("noResults")){this.noMoreResults=true;
$(".no-more-results",this.contentWrapper).show()}DeskPRO_Window.getMessageBroker().addMessageListener("window.innerLayout.resize",function(){this._handleResize()
},this);if(this.getMetaData("isNewRecentSearch")){DeskPRO_Window.getMessageBroker().sendMessage("agent.new-recent-search")
}this.performActionsBtn=$(".perform-actions-trigger",this.wrapper);this.selectionBar=new DeskPRO.Agent.PageHelper.SelectionBar(this,{onButtonClick:function(){k.massActions.open()
}});this.ownObject(this.selectionBar);var f=new DeskPRO.UI.Menu({triggerElement:$("button.sub-group-trigger:first",this.contentWrapper),menuElement:$("ul.sub-group-menu:first",this.contentWrapper)});
this.ownObject(f);if(this.meta.groupingIgnore){var i=$(".group-by-menu",this.wrapper);Array.each(this.meta.groupingIgnore,function(a){$('[value="'+a+'"], [data-group-by="'+a+'"]',i).remove()
})}this.resultsHelper=new DeskPRO.Agent.PageHelper.Results(this,{resultIds:this.meta.ticketResultIds,perPage:this.meta.perPage||50});
this.ownObject(this.resultsHelper);delete this.meta.ticketResultIds;this.massActions=new DeskPRO.Agent.TicketList.MassActions.Widget(this,{});
this.ownObject(this.massActions)},_handleResize:function(){if(!this.layout){return}this.layout.resizeAll()},addTicket:function(b){if(!this.meta.loadSingleUrl){return
}var a=this.meta.loadSingleUrl.replace("$ticket_id",b).replace("$view_type",this.meta.viewType);$.ajax({url:a,dataType:"html",context:this,success:function(c){var d=$(c);
d.hide();$(".timeago",d).timeago();$(".deskpro-results-list",this.wrapper).prepend(d);d.slideDown()}})},delTicket:function(b){var a=$(".ticket-"+b,this.contentWrapper);
a.animate({height:"toggle",opacity:"toggle"},"slow",function(){a.remove()})},_initSearchOptions:function(){var b=$(".summary .edit",this.topSection);
b.click(this.showSearchForm.bind(this));var a=$("form.ticket-search-form",this.topSection);a.submit(function(d){d.preventDefault();
var c=a.attr("action");var e=a.serializeArray();DeskPRO_Window.loadListPane(c,{postData:e})})},showSearchForm:function(){var d=$(".search-form",this.topSection);
var e=$(".search-builder-tpl",this.topSection);var b=new DeskPRO.Form.RuleBuilder(e);this.ownObject(b);$(".add-term",d).data("add-count",0).click(function(){var f=parseInt($(this).data("add-count"));
var g="terms["+f+"]";$(this).data("add-count",f+1);b.addNewRow($(".search-terms",d),g)});var c=$(".search-form-data:first",this.topSection);
if(c.length){var a=c.get(0).innerHTML;a=$.parseJSON(a);if(a.terms){Array.each(a.terms,function(h,f){var g="terms[initial_"+f+"]";
b.addNewRow($(".search-terms",d),g,{type:h.type,op:h.op,options:h.options})})}if(a.order_by){$('[name="order_by"]',this.topSection).val(a.order_by)
}c.remove()}$(".summary",this.topSection).slideUp();$(".form-panel",this.topSection).slideDown()},_initGroupingOptions:function(){var a=this;
$("div.search-top ul.grouping-info > li[data-group-id]",this.contentWrapper).click(function(){a.switchToSubgroup($(this).data("group-id"),$(this))
})},switchToSubgroup:function(b,a){if(b=="NONE"){this.appendUrl=null}else{this.appendUrl="&group_field_id="+b}$("table.list tbody",this.contentWrapper).remove();
this.loadResultPage(1);$("div.search-top ul.grouping-info > li",this.contentWrapper).removeClass("on");if(a){a.addClass("on")
}},_initFlagMenu:function(){var a=this;this.flagMenu=new DeskPRO.UI.Menu({menuElement:$("> ul.ticket-flag-menu:first",this.contentWrapper),onItemClicked:function(b){a._handleFlagMenuClick(b)
}});this.ownObject(this.flagMenu);$("table.list:first",this.contentWrapper).delegate("span.ticket-flag","click",function(b){a.flagMenu.openMenu(b)
})},_handleFlagMenuClick:function(e){var d=$(e.itemEl);var c=d.data("flag");var b=$(e.menu.getOpenTriggerElement());var f=b.parent().parent().data("ticket-id");
var a=b.data("flag");b.removeClass("icon-flag-"+a);b.addClass("icon-flag-"+c);b.data("flag",c);console.debug("todo: loading element with flag click");
$.ajax({url:BASE_URL+"agent/tickets/"+f+"/ajax-save-flagged",type:"POST",context:this,data:{color:c},dataType:"json",success:function(g){}})
},_initDisplayOptions:function(){var b=this;if(this.meta.viewTypeUrl){var c=$("nav.mode-buttons:first",this.contentWrapper);
var b=this;$("li:not(.on)",c).click(function(e){e.preventDefault();var f=$(this).data("view-type");b.switchViewType(f)})}this.displayOptions=new DeskPRO.Agent.PageHelper.DisplayOptions(this,{prefId:"ticket-"+this.resultTypeName,resultId:this.resultTypeId,refreshUrl:this.meta.refreshUrl});
this.ownObject(this.displayOptions);var d=$(".order-by-menu-trigger",this.wrapper).first();this.sortingMenu=new DeskPRO.UI.Menu({triggerElement:d,menuElement:$(".order-by-menu",this.wrapper).first(),onItemClicked:function(i){var f=$(i.itemEl);
var j=f.data("order-by");var e=f.text().trim();$(".label",d).text(e);var h=b.displayOptions.getWrapperElement();var g=$("select.sel-order-by",h);
$("option",g).prop("selected",false);$("option."+j,g).prop("selected",true);b.displayOptions.saveAndRefresh()}});this.ownObject(this.sortingMenu);
var a=$(".group-by-menu-trigger",this.wrapper).first();this.groupingMenu=new DeskPRO.UI.Menu({triggerElement:a,menuElement:$(".group-by-menu",this.wrapper).first(),onItemClicked:function(h){var g=$(h.itemEl);
var i=g.data("group-by");var f=g.text().trim();$(".label",a).text(f);var e=b.meta.refreshUrl;e=Orb.appendQueryData(e,"group_by",i);
DeskPRO_Window.loadListPane(e)}});this.ownObject(this.groupingMenu)},switchViewType:function(g){var e=this.meta.viewTypeUrl.replace("$view_type",g);
if(g=="list"){var a=$(window).width()-100;var d=$(window).height()-100;var c=$("<div>Loading...</div>");c.width(a);c.height(d);
c.css("overflow","auto");var b=new DeskPRO.UI.Overlay({contentElement:c,destroyOnClose:true,customClassname:"no-padding",maxWidth:a,maxHeight:d});
b.openOverlay();var f=function(h){$.ajax({timeout:20000,type:"GET",url:h,dataType:"html",success:function(i){if(b.isDestroyed()){return
}var j=DeskPRO_Window.createPageFragment(i,"DeskPRO.Agent.PageFragment.ListPane.Basic");j.setMetaData("routeUrl",h);j.setMetaData("pageReloader",f);
c.html(j.html);j.fireEvent("render",[c]);j.fireEvent("activate")}})};f(e);return}DeskPRO_Window.loadListPane(e,null,function(){DeskPRO_Window.removePage(self)
})}});Orb.createNamespace("DeskPRO.Agent.PageFragment.ListPane");DeskPRO.Agent.PageFragment.ListPane.BasicOrganizationResults=new Orb.Class({Extends:DeskPRO.Agent.PageFragment.ListPane.Basic,initializeProperties:function(){this.parent();
this.wrapper=null;this.contentWrapper=null;this.overlay=null;this.appendUrl=null;this.actionsBarHelper=null;this.resultTypeName="basic";
this.resultTypeId="general"},initPage:function(a){this.wrapper=$(a);this.contentWrapper=$("div.content:first",this.wrapper);
this._initDisplayOptions();this.initFeaturesOnCollection(a,{routes:["tr .with-route"],times:["tr abbr.timeago"]});if(this.getMetaData("noResults")){this.noMoreResults=true;
$(".no-more-results",this.contentWrapper).show()}DeskPRO_Window.getMessageBroker().addMessageListener("window.innerLayout.resize",function(){this._handleResize()
},this)},_initDisplayOptions:function(){this.displayOptionsList=$(".display-options:first ul.sortable-list",this.contentWrapper);
var a=this.displayOptionsWrapper=$(".display-options:first",this.contentWrapper);this.displayOptionsOverlay=new DeskPRO.UI.Overlay({contentElement:a,triggerElement:$(".display-options-trigger",this.contentWrapper),onContentSet:function(c){$("ul.sortable-list",c.wrapperEl).sortable({axis:"y"})
}});this.ownObject(this.displayOptionsOverlay);$(".close-trigger",a).click((function(){this.displayOptionsOverlay.closeOverlay()
}).bind(this));$(".save-trigger",a).click((function(){this.saveDisplayOptions()}).bind(this));var b=this;$(".list thead th",this.contentWrapper).each(function(){$('li[data-field="'+$(this).data("field")+'"] input[type="checkbox"]',b.displayOptionsList).attr("checked",true)
})},saveDisplayOptions:function(){$(".buttons .loading-off",this.displayOptionsWrapper).hide();$(".buttons .loading-on",this.displayOptionsWrapper).show();
var d=[];var a="prefs[agent.ui.org-"+this.resultTypeName+"-display-fields."+this.resultTypeId+"][]";$('input[type="checkbox"]:checked',this.displayOptionsList).each(function(){d.push({name:a,value:$(this).attr("name")})
});d.push({name:"prefs[agent.ui.org-"+this.resultTypeName+"-order-by."+this.resultTypeId+"]",value:$('select[name="order_by"]',this.displayOptionsWrapper).val()});
var c=this.getMetaData("refreshUrl");if(this.appendUrl){c+=this.appendUrl}var b=this;$.ajax({timeout:20000,type:"POST",url:this.getMetaData("saveListPrefsUrl"),data:d,success:function(){DeskPRO_Window.loadListPane(c,null,function(){DeskPRO_Window.removePage(b)
})}})}});Orb.createNamespace("DeskPRO.Agent.PageFragment.ListPane");DeskPRO.Agent.PageFragment.ListPane.OrganizationCustomFilter=new Orb.Class({Extends:DeskPRO.Agent.PageFragment.ListPane.BasicOrganizationResults,initializeProperties:function(){this.parent();
this.TYPENAME="organization-custom-filter";this.resultTypeName="filter";this.resultTypeId=0},initPage:function(a){this.parent(a);
this.resultTypeId=this.getMetaData("cache_id")}});Orb.createNamespace("DeskPRO.Agent.PageFragment.ListPane");DeskPRO.Agent.PageFragment.ListPane.PeopleList=new Orb.Class({Extends:DeskPRO.Agent.PageFragment.ListPane.Basic,initializeProperties:function(){this.parent();
this.TYPENAME="people-list";this.wrapper=null;this.contentWrapper=null;this.overlay=null;this.appendUrl=null;this.actionsBarHelper=null;
this.resultTypeName="filter";this.resultTypeId=0},initPage:function(b){var a=this;this.wrapper=$(b);this.contentWrapper=$("div.content:first",this.wrapper);
this.resultTypeId=this.meta.cache_id||0;this.initFeaturesOnCollection(b,{routes:[".with-route"],times:["abbr.timeago"]});
if(this.getMetaData("noResults")){this.noMoreResults=true;$(".no-more-results",this.contentWrapper).show()}this.contentWrapper.addClass("scroll-content").tinyscrollbar();
this.displayOptions=new DeskPRO.Agent.PageHelper.DisplayOptions(this,{prefId:"people-filter",resultId:this.resultTypeId,refreshUrl:this.meta.refreshUrl});
this.ownObject(this.displayOptions);this.selectionBar=new DeskPRO.Agent.PageHelper.SelectionBar(this,{});this.ownObject(this.selectionBar);
$(".detail-view-trigger",this.wrapper).click((function(){this.switchViewType("list")}).bind(this));this.massActionsMenu=new DeskPRO.UI.Menu({triggerElement:$(".perform-actions-trigger:first",this.wrapper),menuElement:$(".actions-menu:first",this.wrapper),onItemClicked:function(h){var d=$(h.itemEl);
var e=d.parent();var g=d.data("action");if(e.is(".submenu")){g=e.data("action")}var c=a.selectionBar.getCheckedFormValues("ids");
var f=false;switch(g){case"delete":break;case"add-to-organization":var i=d.data("organization-id");if(!i){return}c.push({name:"organization_id",value:i});
break;case"del-from-organization":break;case"add-to-usergroup":var i=d.data("usergroup-id");if(!i){return}c.push({name:"usergroup_id",value:i});
break;case"del-from-usergroup":var i=d.data("usergroup-id");if(!i){return}c.push({name:"usergroup_id",value:i});break;default:return;
break}$.ajax({url:BASE_URL+"agent/ideas/filter/mass-actions/"+g,data:c,type:"POST",dataType:"json",success:function(j){if(f){a.selectionBar.getChecked().parent().fadeOut("fast")
}else{a.selectionBar.getChecked().each(function(){var k=$(".subject",$(this).parent());DeskPRO_Window.util.showSavePuff(k)
})}a.selectionBar.checkNone()}})}});this.ownObject(this.massActionsMenu)},destroyPage:function(){},switchViewType:function(g){var e=this.meta.viewTypeUrl.replace("$view_type",g);
if(g=="list"){var a=$(window).width()-100;var d=$(window).height()-100;var c=$("<div>Loading...</div>");c.width(a);c.height(d);
c.css("overflow","auto");var b=new DeskPRO.UI.Overlay({contentElement:c,destroyOnClose:true,customClassname:"no-padding",maxWidth:a,maxHeight:d});
b.openOverlay();var f=function(h){$.ajax({timeout:20000,type:"GET",url:h,dataType:"html",success:function(i){if(b.isDestroyed()){return
}var j=DeskPRO_Window.createPageFragment(i,"DeskPRO.Agent.PageFragment.ListPane.Basic");j.setMetaData("routeUrl",h);j.setMetaData("pageReloader",f);
c.html(j.html);j.fireEvent("render",[c]);j.fireEvent("activate")}})};f(e);return}DeskPRO_Window.loadListPane(e,null,function(){DeskPRO_Window.removePage(self)
})},saveDisplayOptions:function(){$(".loading-off",this.displayOptionsWrapper).hide();$(".loading-on",this.displayOptionsWrapper).show();
var d=[];var a="prefs[agent.ui.people-"+this.resultTypeName+"-display-fields."+this.resultTypeId+"][]";$('input[type="checkbox"]:checked',this.displayOptionsList).each(function(){d.push({name:a,value:$(this).attr("name")})
});d.push({name:"prefs[agent.ui.people-"+this.resultTypeName+"-order-by."+this.resultTypeId+"]",value:$('select[name="order_by"]',this.displayOptionsWrapper).val()});
var c=this.getMetaData("refreshUrl");if(this.appendUrl){c+=this.appendUrl}var b=this;$.ajax({timeout:20000,type:"POST",url:this.getMetaData("saveListPrefsUrl"),data:d,success:function(){DeskPRO_Window.loadListPane(c,null,function(){DeskPRO_Window.removePage(b)
})}})}});Orb.createNamespace("DeskPRO.Agent.PageFragment.ListPane");DeskPRO.Agent.PageFragment.ListPane.TicketFilter=new Orb.Class({Extends:DeskPRO.Agent.PageFragment.ListPane.BasicTicketResults,initializeProperties:function(){this.TYPENAME="ticket-filter";
this.resultTypeName="filter";this.resultTypeId=0},initPage:function(a){DeskPRO_Window.getMessageBroker().sendMessage("ticket-section.list-activated",{listType:"filter",id:this.getMetaData("filter_id")});
this.resultTypeId=this.getMetaData("filter_id");this.parent(a)},activate:function(){if(this.getMetaData("filter_id")){DeskPRO_Window.getMessageBroker().sendMessage("filter.view-activated",this.getMetaData("filter_id"))
}},deactivate:function(){if(this.getMetaData("filter_id")){DeskPRO_Window.getMessageBroker().sendMessage("filter.view-deactivated",this.getMetaData("filter_id"))
}}});Orb.createNamespace("DeskPRO.Agent.PageFragment.ListPane");DeskPRO.Agent.PageFragment.ListPane.TicketFlagged=new Orb.Class({Extends:DeskPRO.Agent.PageFragment.ListPane.BasicTicketResults,initializeProperties:function(){this.parent();
this.TYPENAME="ticket-flagged";this.resultTypeName="flagged";this.resultTypeId=0},initPage:function(a){this.meta.view_name="flag";
this.meta.view_extra=this.getMetaData("flag");DeskPRO.Agent.PageFragment.ListPane.BasicTicketResults.prototype.initPage.apply(this,[a]);
this.resultTypeId=this.getMetaData("flag")}});Orb.createNamespace("DeskPRO.Agent.PageFragment.ListPane");DeskPRO.Agent.PageFragment.ListPane.TicketDeletedList=new Orb.Class({Extends:DeskPRO.Agent.PageFragment.ListPane.BasicTicketResults,initializeProperties:function(){this.parent();
this.TYPENAME="ticket-deleted-list";this.resultTypeName="filter";this.resultTypeId=0},initPage:function(a){this.parent(a);
this.resultTypeId=this.getMetaData("cache_id")}});Orb.createNamespace("DeskPRO.Agent.PageFragment.ListPane");DeskPRO.Agent.PageFragment.ListPane.TicketCustomFilter=new Orb.Class({Extends:DeskPRO.Agent.PageFragment.ListPane.BasicTicketResults,initPage:function(a){if(this.getMetaData("view_label")){DeskPRO_Window.getMessageBroker().sendMessage("ticket-section.list-activated",{listType:"label",id:this.getMetaData("label")})
}else{if(this.getMetaData("view_flag")){DeskPRO_Window.getMessageBroker().sendMessage("ticket-section.list-activated",{listType:"flag",id:this.getMetaData("flag")})
}else{if(this.getMetaData("view_spam")){DeskPRO_Window.getMessageBroker().sendMessage("ticket-section.list-activated",{listType:"archive",id:this.getMetaData("spam")})
}else{if(this.getMetaData("view_validating")){DeskPRO_Window.getMessageBroker().sendMessage("ticket-section.list-activated",{listType:"archive",id:this.getMetaData("validating")})
}else{if(this.getMetaData("view_recycle_bin")){DeskPRO_Window.getMessageBroker().sendMessage("ticket-section.list-activated",{listType:"archive",id:this.getMetaData("recycle_bin")})
}}}}}this.parent(a);this.resultTypeId=this.getMetaData("cache_id")}});Orb.createNamespace("DeskPRO.Agent.PageFragment.ListPane");
DeskPRO.Agent.PageFragment.ListPane.TicketCustomFilterForm=new Orb.Class({Extends:DeskPRO.Agent.PageFragment.ListPane.Basic,initPage:function(a){this.wrapper=$(a);
this._initBasic();this._initFilterForm();if(this.getMetaData("autorun")){this.submitForm()}},destroyPage:function(){},_initBasic:function(){var a=this;
$("> .summary > .toggle",this.wrapper).click(function(){$("> .summary",a.wrapper).hide();$("> .criteria",a.wrapper).slideDown()
})},_initFilterForm:function(){var b=this;var d=new DeskPRO.Form.RuleBuilder($(".search-tpl",this.wrapper));d.addEvent("newRow",function(f){$(".remove",f).click(function(){f.remove()
})});$(".search-form .add-term").data("add-count",0).click(function(){var f=parseInt($(this).data("add-count"));var g="terms["+f+"]";
$(this).data("add-count",f+1);d.addNewRow($(".search-form .search-terms",b.wrapper),g)});var b=this;$("button.run-filter-trigger",this.wrapper).click(function(){b.submitForm()
});if(this.getMetaData("preselectTerms")){var e=0;var a=this.getMetaData("preselectTerms");for(var c=0;c<a.length;c++){if(!a[c]){continue
}d.addNewRow($(".search-form .search-terms",b.wrapper),"terms["+e+"]",a[c])}$(".search-form .add-term",this.wrapper).data("add-count",e)
}},submitForm:function(){var a=$("form.search-form-data",this.wrapper).serializeArray();$.ajax({cache:false,type:"POST",data:a,url:this.getMetaData("formSubmitUrl"),context:this,dataType:"html",success:function(b){$(" .criteria",this.wrapper).hide();
$(".summary",this.wrapper).show();this._handleAjaxResults(b)}})},_handleAjaxResults:function(b){DeskPRO_Window.removePage(this);
var a=DeskPRO_Window.createPageFragment(b);DeskPRO_Window.addListPage(a)}});Orb.createNamespace("DeskPRO.Agent.PageFragment.ListPane");
DeskPRO.Agent.PageFragment.ListPane.TwitterStatus=new Orb.Class({Extends:DeskPRO.Agent.PageFragment.ListPane.Basic,initPage:function(a){this.wrapper=$(a);
this.header=$(".header",this.wrapper);this.content=$(".content",this.wrapper);this.note=$(".form-note",this.wrapper);this.reply=$(".form-reply",this.wrapper);
this._initHeader();this._initContent();this._initControls()},_afterLoading:function(){this._initContent();this._initControls()
},_initHeader:function(){this._initOrderBySelectField();this._initIncludeFields()},_initContent:function(){this._initUserPageLinks();
this._initTimeago()},_initControls:function(){this._initFollow();this._initUnfollow();this._initAddNote();this._initAssign();
this._initRetweet();this._initReply();this._initArchive()},_initOrderBySelectField:function(){$(".display-options select[name=sortbydate]",this.header).change($.proxy(this.reload,this))
},_initIncludeFields:function(){$(".display-options input:checkbox",this.header).change($.proxy(this.reload,this));$(".display-options label",this.header).each($.proxy(function(a,d){var c=$(d),b=$(".display-options input[name="+c.data("for")+"]",this.header),e=Orb.getUniqueId("twitter_options_"+c.data("for"));
b.attr("id",e);c.attr("for",e)},this))},_initUserPageLinks:function(){$(".photo",this.content).click(function(){DeskPRO_Window.runPageRouteFromElement(this)
});$(".user",this.content).click(function(){DeskPRO_Window.runPageRouteFromElement(this)})},_initTimeago:function(){this.initTimesOnCollection($(".timeago",this.content))
},_getDisplayOptions:function(){var a={sortbydate:$(".display-options select[name=sortbydate] option:selected",this.header).val(),include:{}};
$(".display-options input:checkbox",this.header).each(function(){var b=$(this);a.include[b.attr("name")]=b.attr("checked")?1:0
});return a},reload:function(){$.ajax({url:this.getMetaData("statusListUrl"),dataType:"html",data:this._getDisplayOptions(),context:this,success:function(a){this.content.html(a);
this._afterLoading()}})},highlightStatus:function(a){$(".status",this.content).removeClass("highlight");$(".status-"+a,this.content).addClass("highlight")
},downlightStatus:function(a){$(".status-"+a,this.content).removeClass("highlight")},_initFollow:function(){var a=$(".follow a",this.content);
a.click($.proxy(function(b){this.doFollow($(b.target).parents(".status").attr("data-user-id"))},this))},doFollow:function(a){$.ajax({url:this.getMetaData("saveFollowUrl"),dataType:"json",data:{account_id:this.getMetaData("accountId"),user_id:a},context:this,success:function(b){if(b.success){this.reload()
}else{alert(b.error)}}})},_initUnfollow:function(){var a=$(".unfollow a",this.content);a.click($.proxy(function(b){this.doUnfollow($(b.target).parents(".status").attr("data-user-id"))
},this))},doUnfollow:function(a){$.ajax({url:this.getMetaData("saveUnfollowUrl"),dataType:"json",data:{account_id:this.getMetaData("accountId"),user_id:a},context:this,success:function(b){if(b.success){this.reload()
}else{alert(b.error)}}})},_initAddNote:function(){var a=$(".controls .note a",this.content);a.click($.proxy(function(f){if($(".form-note",$(f.target).parents(".status")).length){return false
}var b=$(f.target).parents(".status").attr("data-status-id"),c=this.note.clone(),d=$("textarea[name=text]",c);$(f.target).parents(".body").append(c);
this.highlightStatus(b);var g=$.proxy(function(h){if(h.which!=27){return true}this.downlightStatus(b);c.remove();$(document).unbind("keydown",g);
return true},this);$(document).keydown(g);d.keypress($.proxy(function(h){if(h.which!=13){return true}var i=d.val();c.remove();
this.doAddNote(b,i);h.preventDefault();return false},this));c.show();d.focus();f.preventDefault();return false},this))},doAddNote:function(b,a){$.ajax({url:this.getMetaData("saveNoteUrl"),dataType:"json",data:{status_id:b,text:a},context:this,success:function(c){if(c.success){this.reload()
}else{alert(c.error)}}})},_initAssign:function(){var a=$(".controls .assign a",this.content);a.click($.proxy(function(b){b.preventDefault();
return false},this))},_initRetweet:function(){var a=$(".controls .retweet a",this.content);a.click($.proxy(function(b){this.doRetweet($(b.target).parents(".status").attr("data-status-id"));
b.preventDefault();return false},this))},doRetweet:function(a){$.ajax({url:this.getMetaData("saveRetweetUrl"),dataType:"json",data:{status_id:a,account_id:this.getMetaData("accountId")},context:this,success:function(b){if(b.success){this.reload()
}else{alert(b.error)}}})},_initReply:function(){var a=$(".controls .reply a",this.content);a.click($.proxy(function(f){if($(".form-reply",$(f.target).parents(".status")).length){return false
}var b=$(f.target).parents(".status").attr("data-status-id"),c=this.reply.clone(),d=$("textarea[name=text]",c);$(f.target).parents(".body").append(c);
this.highlightStatus(b);var g=$.proxy(function(h){if(h.which!=27){return true}this.downlightStatus(b);c.remove();$(document).unbind("keydown",g);
return true},this);$(document).keydown(g);c.keypress($.proxy(function(j){if(j.which!=13){return true}var k=d.val(),i=$("input[type=radio][name=type]:checked",c).val(),h=$("select[name=account] option:selected",c).val();
c.remove();this.doReply(b,k,i,h);j.preventDefault();return false},this));c.show();d.focus();f.preventDefault();return false
},this))},doReply:function(d,c,b,a){$.ajax({url:this.getMetaData("saveReplyUrl"),dataType:"json",data:{status_id:d,account_id:d,text:c,type:b},context:this,success:function(e){if(e.success){this.reload()
}else{alert(e.error)}}})},_initArchive:function(){var a=$(".controls .archive a",this.content);a.click($.proxy(function(b){this.doArchive($(b.target).parents(".status").attr("data-status-id"));
b.preventDefault();return false},this))},doArchive:function(a){$.ajax({url:this.getMetaData("saveArchiveUrl"),dataType:"json",data:{status_id:a},context:this,success:function(b){if(b.success){this.reload()
}else{alert(b.error)}}})}});Orb.createNamespace("DeskPRO.Agent.PageFragment.ListPane");DeskPRO.Agent.PageFragment.ListPane.RecycleBin=new Orb.Class({Extends:DeskPRO.Agent.PageFragment.ListPane.Basic,initializeProperties:function(){this.parent();
this.TYPENAME="recyclebin";this.wrapper=null;this.contentWrapper=null;this.barWrapper=null;this.layout=null;this.overlay=null;
this.appendUrl=null;this.actionsBarHelper=null;this.resultTypeName="basic";this.resultTypeId="general";this.changeManager=null;
this.loadFirst=false},initPage:function(b){this.wrapper=b;var a=this;$(".type-list",b).each(function(){a.initTypeList($(this))
});$("time.timeago",this.wrapper).timeago()},initTypeList:function(c){var b=c.data("load-name");var a=this;$(".list-load-more",c).click(function(){a.loadMore(b)
})},loadMore:function(e){var b=$("."+e+"-list.type-list",this.wrapper);var d=$("table:first",b);var c=$(".list-load-more",b);
var f=$("tbody:last",d);var a=parseInt(f.data("page"))+1;$.ajax({url:BASE_URL+"agent/recycle-bin/"+e+"/"+a,dataType:"json",success:function(h){if(h.no_more_results){c.hide()
}if(h.count<1){return}var g=$(h.htmls);$("time.timeago",g).timeago();d.append(g)}})}});Orb.createNamespace("DeskPRO.Agent.PageFragment.ListPane");
DeskPRO.Agent.PageFragment.ListPane.KbGlossary=new Orb.Class({Extends:DeskPRO.Agent.PageFragment.ListPane.Basic,initPage:function(b){this.wrapper=b;
var a=this;$(".new-word-trigger",b).click(this.showAddDlg.bind(this));$(".edit-word-trigger",b).click(function(c){c.preventDefault();
a.showEditDlg($(this).data("word-id"))})},showAddDlg:function(){var a=this.getAddDlg();a.openOverlay()},showEditDlg:function(d){var a=this.getEditDlg();
var b=$(".form",a.elements.wrapper);var c=$(".loading",a.elements.wrapper);b.hide();c.show();a.openOverlay();$.ajax({url:BASE_URL+"agent/kb/glossary/"+d+".json",type:"GET",context:this,dataType:"json",success:function(e){$(".word",b).html(e.word);
$("input.word_id",b).val(e.id);$("textarea.content",b).val(e.content);c.hide();b.show()}})},getAddDlg:function(){if(this.addDlg){return this.addDlg
}var a=$(".add-dlg:first",this.wrapper);this.addDlg=new DeskPRO.UI.Overlay({contentElement:a});this.ownObject(this.addDlg);
$(".save-trigger",a).click(this.saveNewWord.bind(this));return this.addDlg},getEditDlg:function(){if(this.editDlg){return this.editDlg
}var a=$(".edit-dlg:first",this.wrapper);this.editDlg=new DeskPRO.UI.Overlay({contentElement:a});this.ownObject(this.editDlg);
$(".save-trigger",a).click(this.saveEditWord.bind(this));return this.editDlg},saveNewWord:function(){var a=[];a.push({name:"word",value:$("input.word",this.addDlg.elements.wrapperOuter).val().trim()});
a.push({name:"content",value:$("textarea.content",this.addDlg.elements.wrapperOuter).val().trim()});$.ajax({url:BASE_URL+"agent/kb/glossary/new-word.json",type:"POST",data:a,context:this,dataType:"json",success:function(h){var b=$(".counter-words",this.wrapper);
var f=parseInt(b.html());b.html(f+1);var e=h.letter;var c=h.word;var g=h.word_id;var j=$('<li><a class="edit-word-trigger" data-word-id="'+g+'">'+c+"</a></li>");
var d=$('dt[data-letter="'+e+'"]:first',this.wrapper);var i=$('dd[data-letter="'+e+'"]:first',this.wrapper);d.show();i.show();
$("ul",i).prepend(j);$("input.word",this.addDlg.elements.wrapperOuter).val("");$("textarea.content",this.addDlg.elements.wrapperOuter).val("");
this.addDlg.closeOverlay()}})},saveEditWord:function(){var b=$("input.word_id",this.editDlg.elements.wrapperOuter).val().trim();
var a=[];a.push({name:"word_id",value:b});a.push({name:"content",value:$("textarea.content",this.editDlg.elements.wrapperOuter).val().trim()});
$.ajax({url:BASE_URL+"agent/kb/glossary/"+b+"/edit.json",type:"POST",data:a,context:this,dataType:"json",success:function(c){}})
}});Orb.createNamespace("DeskPRO.Agent.PageFragment.ListPane");DeskPRO.Agent.PageFragment.ListPane.KbPendingArticles=new Orb.Class({Extends:DeskPRO.Agent.PageFragment.ListPane.Basic,initPage:function(b){var a=this;
this.wrapper=b;this.actionsMenu=new DeskPRO.UI.Menu({menuElement:$("ul.actions-menu:first",this.wrapper),onItemClicked:function(g){var d=a.selectionBar.getCheckedValues();
var c=[];var f=[];Array.each(d,function(h){f.push({name:"ids[]",value:h});c.push($("article.pending-article-"+h+":first",a.wrapper).get(0))
});$(c).fadeOut();var e=$(g.itemEl).data("action");$.ajax({url:BASE_URL+"agent/kb/pending-articles/mass-actions/"+e,data:f,type:"POST",dataType:"json",error:function(){$(c).show()
},success:function(){$(c).remove();DeskPRO_Window.util.modCountEl("#kb_pending_count","-",c.length)}})}});this.ownObject(this.actionsMenu);
this.selectionBar=new DeskPRO.Agent.PageHelper.SelectionBar(this,{onButtonClick:function(c){a.actionsMenu.open(c)}});this.ownObject(this.selectionBar);
DeskPRO_Window.getMessageBroker().addMessageListener("kb.pending_article_removed",function(c){a.removeFromList(c.pending_article_id)
});$(".add-new-trigger",this.wrapper).click(function(){var c=$(".add-new-form",a.wrapper);if(c.is(":visible")){c.slideUp()
}else{c.slideDown()}});$(".save-new-trigger",this.wrapper).click(this.saveNewPendingArticle.bind(this));$("section.pending-articles-list",this.wrapper).delegate(".pending-delete","click",function(d){d.stopPropagation();
var e=$(this);var c=0;while(!e.is("article")){if(c++>10){return}e=e.parent()}e.slideUp("fast");var f=$("input.item-select",e).val();
$.ajax({url:BASE_URL+"agent/kb/pending-articles/"+f+"/remove",type:"POST",dataType:"json",error:function(){e.show()},success:function(){e.remove();
DeskPRO_Window.util.modCountEl("#kb_pending_count","-")}})});$("section.pending-articles-list",this.wrapper).delegate(".pending-create","click",function(e){e.stopPropagation();
var f=$(this);var c=0;while(!f.is("article")){if(c++>10){return}f=f.parent()}var g=$("input.item-select",f).val();var d=$("input.item-select",f).data("ticket-route");
$.ajax({url:BASE_URL+"agent/kb/pending-articles/"+g+"/info",type:"POST",dataType:"json",success:function(h){if(d){DeskPRO_Window.runPageRoute(d)
}DeskPRO_Window.newArticleLoader.open(function(i){i.setPendingArticle(h);if(h.ticket_id){var j=h.ticket_id;i.addEvent("destroy",function(){Object.each(DeskPRO_Window.tabManager.getTabs(),function(k,l){if(k.page&&k.page.meta.ticket_id==j){DeskPRO_Window.removePage(k.page)
}})})}})}})})},saveNewPendingArticle:function(){var a=$(".add-new-form",this.wrapper);var c=$(".add-new-form textarea",this.wrapper).val().trim();
if(!c){a.slideUp();return}var b=[];b.push({name:"comment",value:c});$.ajax({url:BASE_URL+"agent/kb/pending-articles/new",type:"POST",data:b,context:this,dataType:"json",success:function(d){$("textarea:first",a).val("");
a.slideUp();var e=$(d.row_html);$("section.pending-articles-list",this.wrapper).prepend(e);DeskPRO_Window.util.modCountEl("#kb_pending_count","+")
}})},removeFromList:function(a){$("article.pending-article-"+a,this.wrapper).slideUp("fast");DeskPRO_Window.util.modCountEl("#kb_pending_count","-")
}});Orb.createNamespace("DeskPRO.Agent.PageFragment.ListPane");DeskPRO.Agent.PageFragment.ListPane.KbValidatingArticles=new Orb.Class({Extends:DeskPRO.Agent.PageFragment.ListPane.Basic,initPage:function(b){this.wrapper=b;
var a=this;$("a.view-link.edit",this.wrapper).click(function(c){c.preventDefault();a.loadPreviewEdit($(this).attr("href"))
});$("a.view-link.article",this.wrapper).click(function(c){c.preventDefault();a.loadPreviewArticle($(this).attr("href"))})
},loadPreviewEdit:function(c){var a=this;var b=new DeskPRO.UI.Overlay({destroyOnClose:true,contentMethod:"ajax",contentAjax:{url:c,type:"GET",context:this,dataType:"html"},maxWidth:500,maxHeight:700,onContentSet:function(f){var e=f.contentEl;
var d=f.overlay;$("button.approve-trigger",e).click(function(g){g.preventDefault();a.approveEdit($("input.article_id",e).val());
d.closeOverlay()});$("button.disapprove-trigger",e).click(function(g){g.preventDefault();a.disapproveEdit($("input.article_id",e).val());
d.closeOverlay()})}});b.openOverlay()},approveEdit:function(a){$.ajax({url:BASE_URL+"agent/kb/validating-articles/validate/"+a+".json",type:"POST",context:this,dataType:"json",success:function(c){var b=c.article_id;
$(".article-"+b,this.wrapper).remove();this.removeFromList(b,c.type)}})},disapproveEdit:function(a){$.ajax({url:BASE_URL+"agent/kb/validating-articles/disapprove/"+a+".json",type:"POST",context:this,dataType:"json",success:function(c){var b=c.article_id;
$(".article-"+b,this.wrapper).remove();this.removeFromList(b,c.type)}})},removeFromList:function(b,e){var a=$(".counter-total",this.wrapper);
var f=$(".counter-"+e,this.wrapper);var c=parseInt(a.html());var d=parseInt(a.html());a.html(c-1);f.html(d-1);var g=$(".wrap-"+e,this.wrapper);
if(!$("tr.article",g).length){g.remove()}},loadPreviewArticle:function(c){var a=this;var b=new DeskPRO.UI.Overlay({destroyOnClose:true,contentMethod:"ajax",contentAjax:{url:c,type:"GET",context:this,dataType:"html"},maxWidth:500,maxHeight:700,onContentSet:function(f){var e=f.contentEl;
var d=f.overlay;$("button.approve-trigger",e).click(function(g){g.preventDefault();a.approveEdit($("input.article_id",e).val());
d.closeOverlay()});$("button.disapprove-trigger",e).click(function(g){g.preventDefault();a.disapproveEdit($("input.article_id",e).val());
d.closeOverlay()})}});b.openOverlay()}});Orb.createNamespace("DeskPRO.Agent.PageFragment.ListPane");DeskPRO.Agent.PageFragment.ListPane.KbList=new Orb.Class({Extends:DeskPRO.Agent.PageFragment.ListPane.Basic,initPage:function(b){var a=this;
this.wrapper=b;this.displayOptions=new DeskPRO.Agent.PageHelper.DisplayOptions(this,{prefId:"kb-filter",resultId:this.meta.resultId,refreshUrl:this.meta.refreshUrl});
this.ownObject(this.displayOptions);this.selectionBar=new DeskPRO.Agent.PageHelper.SelectionBar(this,{});this.ownObject(this.selectionBar);
this.listWrapper=$("section.kb-simple-list",this.wrapper);DeskPRO_Window.getTabWatcher().addTabTypeWatcher("ticket",this);
this.addEvent("watchedTabActivated",function(c){a.initVisibleTicket()});this.addEvent("watchedTabDeactivated",function(c){a.removeVisibleTicket()
});if(DeskPRO_Window.getTabWatcher().isTabTypeActive("ticket")){a.initVisibleTicket()}$("section.kb-simple-list",this.wrapper).delegate("button.kb-insert-link","click",function(){a.insertIntoTicket($(this).data("article-id"),"link")
}).delegate("button.kb-insert-content","click",function(){a.insertIntoTicket($(this).data("article-id"),"content")});this.relatedContentList=new DeskPRO.Agent.PageHelper.RelatedContentList(this,{contentListEl:this.listWrapper});
this.ownObject(this.relatedContentList)},initVisibleTicket:function(){this.listWrapper.addClass("with-visible-ticket")},removeVisibleTicket:function(){this.listWrapper.removeClass("with-visible-ticket")
},insertIntoTicket:function(a,c){var b=DeskPRO_Window.getTabWatcher().getActiveTabIfType("ticket");if(!b){return}var d=b.page;
$.ajax({url:BASE_URL+"agent/kb/article/"+a+"/info",type:"GET",dataType:"json",success:function(e){if(c=="content"){d.appendToMessage(e.content)
}else{d.appendToMessage(e.permalink)}}})}});Orb.createNamespace("DeskPRO.Agent.PageFragment.ListPane");DeskPRO.Agent.PageFragment.ListPane.AgentChatHistory=new Orb.Class({Extends:DeskPRO.Agent.PageFragment.ListPane.Basic,initializeProperties:function(){this.parent();
this.wrapper=null;this.contentWrapper=null},initPage:function(a){this.wrapper=$(a);this.contentWrapper=$("div.content:first",this.wrapper);
this.initFeaturesOnCollection(a,{routes:[".with-route"],times:["abbr.timeago"]});if(this.getMetaData("noResults")){this.noMoreResults=true;
$(".no-more-results",this.contentWrapper).show()}this.contentWrapper.addClass("scroll-content").tinyscrollbar()}});Orb.createNamespace("DeskPRO.Agent.PageFragment.ListPane");
DeskPRO.Agent.PageFragment.ListPane.OpenChats=new Orb.Class({Extends:DeskPRO.Agent.PageFragment.ListPane.Basic,initPage:function(a){this.wrapper=a
}});Orb.createNamespace("DeskPRO.Agent.PageFragment.ListPane");DeskPRO.Agent.PageFragment.ListPane.IdeaFilter=new Orb.Class({Extends:DeskPRO.Agent.PageFragment.ListPane.Basic,initializeProperties:function(){this.parent();
this.wrapper=null;this.filterSearchForm=null},initPage:function(c){var b=this;this.wrapper=c;this.displayOptions=new DeskPRO.Agent.PageHelper.DisplayOptions(this,{prefId:"idea-filter",resultId:this.meta.resultId,refreshUrl:this.meta.refreshUrl});
this.ownObject(this.displayOptions);this.selectionBar=new DeskPRO.Agent.PageHelper.SelectionBar(this,{});this.ownObject(this.selectionBar);
var a=$("button.order-by-trigger:first",this.wrapper);this.orderByMenu=new DeskPRO.UI.Menu({triggerElement:a,menuElement:$("ul.order-by-menu:first",this.wrapper),onItemClicked:(function(h){var e=$(h.itemEl);
var i=e.data("field");var d=e.text().trim();$(".label",a).text(d);var g=this.displayOptions.getWrapperElement();var f=$("select.sel-order-by",g);
$("option",f).prop("selected",false);$("option."+i,f).prop("selected",true);this.displayOptions.saveAndRefresh()}).bind(this)});
this.ownObject(this.orderByMenu);this.listWrapper=$("section.idea-simple-list",this.wrapper);this.relatedContentList=new DeskPRO.Agent.PageHelper.RelatedContentList(this,{contentListEl:this.listWrapper});
this.ownObject(this.relatedContentList);this.massActionsMenu=new DeskPRO.UI.Menu({triggerElement:$(".perform-actions-trigger:first",this.wrapper),menuElement:$(".actions-menu:first",this.wrapper),onItemClicked:function(j){var f=$(j.itemEl);
var g=f.parent();var e=g.data("menu-type");var d=b.selectionBar.getCheckedFormValues("ids");var h=false;var i="";switch(e){case"idea-status-menu":i="set-status";
d.push({name:"status",value:f.data("option-value")});break;case"idea-category-menu":i="set-category";d.push({name:"category_id",value:f.data("category-id")});
break;case"idea-massactions-menu":switch(f.data("action")){case"delete":i="set-status";d.push({name:"status",value:"hidden.deleted"});
h=true;break;case"spam":i="set-status";d.push({name:"status",value:"hidden.spam"});h=true;break}break;default:return;break
}$.ajax({url:BASE_URL+"agent/ideas/filter/mass-actions/"+i,data:d,type:"POST",dataType:"json",success:function(k){if(h){b.selectionBar.getChecked().parent().fadeOut("fast")
}b.selectionBar.checkNone()}})}});this.ownObject(this.massActionsMenu)}});Orb.createNamespace("DeskPRO.Agent.PageFragment.ListPane");
DeskPRO.Agent.PageFragment.ListPane.NewCustomFilter=new Orb.Class({Extends:DeskPRO.Agent.PageFragment.ListPane.Basic,initializeProperties:function(){this.parent();
this.wrapper=null;this.filterSearchForm=null},initPage:function(c){this.wrapper=c;this.topSection=$(".list-top-area",this.wrapper);
var f=$(".search-form",this.topSection);var g=$(".search-builder-tpl",this.topSection);var b=new DeskPRO.Form.RuleBuilder(g);
$(".add-term",f).data("add-count",0).click(function(){var h=parseInt($(this).data("add-count"));var i="terms["+h+"]";$(this).data("add-count",h+1);
b.addNewRow($(".search-terms",f),i)});var e=$(".search-form-data:first",this.topSection);if(e.length){var a=e.get(0).innerHTML;
a=$.parseJSON(a);if(a.terms){Array.each(a.terms,function(j,h){var i="terms[initial_"+h+"]";b.addNewRow($(".search-terms",f),i,{type:j.type,op:j.op,options:j.options})
})}if(a.order_by){$('[name="order_by"]',this.topSection).val(a.order_by)}e.remove()}var d=$("form.ticket-search-form",this.topSection);
d.submit(function(i){i.preventDefault();var h=d.attr("action");var j=d.serializeArray();DeskPRO_Window.loadListPane(h,{postData:j})
})}});Orb.createNamespace("DeskPRO.Agent.PageFragment.ListPane");DeskPRO.Agent.PageFragment.ListPane.NewsList=new Orb.Class({Extends:DeskPRO.Agent.PageFragment.ListPane.Basic,initializeProperties:function(){this.parent();
this.wrapper=null},initPage:function(a){this.wrapper=a;this.displayOptions=new DeskPRO.Agent.PageHelper.DisplayOptions(this,{prefId:"news-filter",resultId:this.meta.resultId,refreshUrl:this.meta.refreshUrl});
this.ownObject(this.displayOptions);this.selectionBar=new DeskPRO.Agent.PageHelper.SelectionBar(this,{});this.ownObject(this.selectionBar);
this.listWrapper=$("section.news-simple-list",this.wrapper);this.relatedContentList=new DeskPRO.Agent.PageHelper.RelatedContentList(this,{contentListEl:this.listWrapper});
this.ownObject(this.relatedContentList)}});Orb.createNamespace("DeskPRO.Agent.PageFragment.ListPane");DeskPRO.Agent.PageFragment.ListPane.DownloadList=new Orb.Class({Extends:DeskPRO.Agent.PageFragment.ListPane.Basic,initPage:function(b){var a=this;
this.wrapper=b;this.displayOptions=new DeskPRO.Agent.PageHelper.DisplayOptions(this,{prefId:"download-filter",resultId:this.meta.resultId,refreshUrl:this.meta.refreshUrl});
this.ownObject(this.displayOptions);this.selectionBar=new DeskPRO.Agent.PageHelper.SelectionBar(this,{});this.ownObject(this.selectionBar);
this.listWrapper=$("section.downloads-simple-list",this.wrapper).delegate("button.dl-insert-link","click",function(){a.insertIntoTicket($(this).data("download-id"),"link")
}).delegate("button.dl-insert-attach","click",function(){a.insertIntoTicket($(this).data("download-id"),"attach")});DeskPRO_Window.getTabWatcher().addTabTypeWatcher("ticket",this);
this.addEvent("watchedTabActivated",function(c){a.initVisibleTicket()});this.addEvent("watchedTabDeactivated",function(c){a.removeVisibleTicket()
});if(DeskPRO_Window.getTabWatcher().isTabTypeActive("ticket")){a.initVisibleTicket()}this.relatedContentList=new DeskPRO.Agent.PageHelper.RelatedContentList(this,{contentListEl:this.listWrapper});
this.ownObject(this.relatedContentList)},initVisibleTicket:function(){this.listWrapper.addClass("with-visible-ticket")},removeVisibleTicket:function(){this.listWrapper.removeClass("with-visible-ticket")
},insertIntoTicket:function(a,c){var b=DeskPRO_Window.getTabWatcher().getActiveTabIfType("ticket");if(!b){return}var d=b.page;
$.ajax({url:BASE_URL+"agent/downloads/file/"+a+"/info",type:"GET",dataType:"json",success:function(e){if(c=="attach"){d.addAttachToList(e)
}else{d.appendToMessage(e.permalink)}}})}});Orb.createNamespace("DeskPRO.Agent.PageFragment.ListPane");DeskPRO.Agent.PageFragment.ListPane.PublishValidatingComments=new Orb.Class({Extends:DeskPRO.Agent.PageFragment.ListPane.Basic,initPage:function(c){var b=this;
this.wrapper=c;this.actionsMenu=new DeskPRO.UI.Menu({menuElement:$("ul.actions-menu:first",this.wrapper),onItemClicked:function(g){var f=[];
var d=[];$("input.item-select:checked",this.wrapper).each(function(){d.push($(this).parent().get(0));var h=$(this).data("content-type");
var i=$(this).data("comment-id");f.push({name:"content["+h+"][]",value:i})});if(!f.length){return}var e=$(g.itemEl).data("action");
$.ajax({url:BASE_URL+"agent/publish/comments/validating-mass-actions/"+e,data:f,type:"POST",dataType:"json",success:function(){$(d).fadeOut()
}})}});this.ownObject(this.actionsMenu);this.selectionBar=new DeskPRO.Agent.PageHelper.SelectionBar(this,{onButtonClick:function(d){b.actionsMenu.open(d)
}});this.ownObject(this.selectionBar);var a=function(f){var h=$(f);var e=$(f).closest("div.edit-comment");if(e.length){var h=$("article."+e.data("content-type")+"-"+e.data("comment-id"));
return a(h)}h=h.closest("article");var d=$("input.item-select",h);if(!d.length){return}var g={row:h,contentType:$(d).data("content-type"),commentId:$(d).data("comment-id")};
var e=$("div.edit-"+g.contentType+"-"+g.commentId,b.wrapper);console.log(e);g.editRow=e;return g};this.wrapper.delegate(".validate-approve","click",function(d){d.stopPropagation();
var e=a(this);b.approveComment(e.contentType,e.commentId,e.row)});this.wrapper.delegate(".validate-delete","click",function(d){d.stopPropagation();
var e=a(this);b.deleteComment(e.contentType,e.commentId,e.row)});this.wrapper.delegate(".validate-edit","click",function(d){d.stopPropagation();
var e=a(this);b.editComment(e.contentType,e.commentId,e.row,e)});this.wrapper.delegate(".comment-editsave-trigger","click",function(d){var e=a(this);
$.ajax({url:BASE_URL+"agent/publish/comments/save-comment/"+e.contentType+"/"+e.commentId,type:"POST",data:{comment:$("textarea:first",e.row).val()},dataType:"json",success:function(f){var g=$(".rendered",e.row);
g.html(f.comment_html);e.row.show();e.editRow.hide()}})});this.wrapper.delegate(".comment-editcancel-trigger","click",function(e){var f=a(this);
var d=f.editRow;f.row.show();d.hide()});this.wrapper.delegate(".validate-create-ticket","click",function(d){var e=a(this);
$.ajax({url:BASE_URL+"agent/publish/comments/new-ticket-info/"+e.contentType+"/"+e.commentId+".json",type:"GET",dataType:"json",success:function(f){DeskPRO_Window.newTicketLoader.open(function(g){g.setNewByComment(f)
})}})});DeskPRO_Window.getMessageBroker().addMessageListener("agent-ui.comment-remove",function(d){$("article."+d.comment_type+"-"+d.comment_id,this.wrapper).fadeOut()
})},deleteComment:function(c,a,b){if(!b){b=$("article."+c+"-"+a,this.wrapper)}b.fadeOut();this.updateCount("sub");$.ajax({url:BASE_URL+"agent/publish/comments/delete/"+c+"/"+a,type:"POST",context:this,dataType:"json",error:function(){this.updateCount("add");
b.fadeIn()},success:function(d){b.remove()}})},approveComment:function(c,a,b){if(!b){b=$("article."+c+"-"+a,this.wrapper)
}b.fadeOut();this.updateCount("sub");$.ajax({url:BASE_URL+"agent/publish/comments/approve/"+c+"/"+a,type:"POST",context:this,dataType:"json",error:function(){this.updateCount("add");
b.fadeIn()},success:function(d){b.remove()}})},editComment:function(d,b,c,e){c.hide();var a=e.editRow;if(!a.is(".rte-inited")){a.addClass("rte-inited");
$("textarea",a).tinymce({script_url:ASSETS_BASE_URL+"/vendor/tiny_mce/tiny_mce.js",theme:"advanced",plugins:"fullscreen",fullscreen_new_window:true,theme_advanced_buttons1:"bold,italic,underline,|,justifyleft,justifycenter,justifyright,|,fontselect,fontsizeselect,formatselect",theme_advanced_buttons2:",bullist,numlist,|,outdent,indent,|,link,unlink,anchor,image,|,code,removeformat,fullscreen",theme_advanced_buttons3:"",theme_advanced_toolbar_location:"top",theme_advanced_toolbar_align:"left",theme_advanced_resizing:true,theme_advanced_statusbar_location:"bottom"})
}a.show()},updateCount:function(b){var c=$("#publish_validating_comments_count");var a=parseInt(c.text());if(b=="add"){a++
}else{a--}if(a<0){a=0}var c=$("#publish_validating_comments_count").text(a)}});Orb.createNamespace("DeskPRO.Agent.PageFragment.ListPane");
DeskPRO.Agent.PageFragment.ListPane.PublishValidatingContent=new Orb.Class({Extends:DeskPRO.Agent.PageFragment.ListPane.Basic,initPage:function(b){var a=this;
this.wrapper=b;DeskPRO_Window.getMessageBroker().addMessageListener("publish.validating.list-remove",function(c){$("article."+c.typename+"-"+c.contentId).slideUp()
});this.actionsMenu=new DeskPRO.UI.Menu({menuElement:$("ul.actions-menu:first",this.wrapper),onItemClicked:function(g){var f=[];
var d=[];$("input.item-select:checked",this.wrapper).each(function(){d.push($(this).parent().get(0));var h=$(this).data("content-type");
var i=$(this).data("content-id");f.push({name:"content["+h+"][]",value:i})});if(!f.length){return}var e=$(g.itemEl).data("action");
var c=function(){$.ajax({url:BASE_URL+"agent/publish/content/validating-mass-actions/"+e,data:f,type:"POST",dataType:"json",success:function(){$(d).fadeOut()
}})};if(e=="disapprove"){DeskPRO_Window.showPrompt("Enter a reason or comment to send to the authors",function(h){f.push({name:"reason",value:h});
c()})}else{c()}}});this.ownObject(this.actionsMenu);this.selectionBar=new DeskPRO.Agent.PageHelper.SelectionBar(this,{onButtonClick:function(c){a.actionsMenu.open(c)
}});this.ownObject(this.selectionBar)}});Orb.createNamespace("DeskPRO.Agent.PageFragment.ListPane");DeskPRO.Agent.PageFragment.ListPane.PublishDraftsList=new Orb.Class({Extends:DeskPRO.Agent.PageFragment.ListPane.Basic,initPage:function(b){var a=this;
this.wrapper=b;DeskPRO_Window.getMessageBroker().addMessageListener("publish.drafts.list-remove",function(c){$("article."+c.typename+"-"+c.contentId,this.wrapper).slideUp()
},this);this.actionsMenu=new DeskPRO.UI.Menu({triggerElement:$("button.perform-actions-trigger:first",this.wrapper),menuElement:$("ul.actions-menu:first",this.wrapper),onItemClicked:function(f){var e=[];
var c=[];$("input.item-select:checked",this.wrapper).each(function(){c.push($(this).parent().get(0));var g=$(this).data("content-type");
var h=$(this).data("content-id");e.push({name:"content["+g+"][]",value:h})});if(!e.length){return}var d=$(f.itemEl).data("action");
$.ajax({url:BASE_URL+"agent/publish/drafts/mass-actions/"+d,data:e,type:"POST",dataType:"json",context:this,success:function(g){if(g.affected){Array.each(g.affected,function(h){DeskPRO_Window.getMessageBroker().sendMessage("publish.drafts.list-remove",h)
},this)}}})}});this.ownObject(this.actionsMenu);this.selectionBar=new DeskPRO.Agent.PageHelper.SelectionBar(this,{onButtonClick:function(c){a.actionsMenu.open(c)
}});this.ownObject(this.selectionBar)}});Orb.createNamespace("DeskPRO.Agent.PageFragment.ListPane");DeskPRO.Agent.PageFragment.ListPane.PublishSearchLog=new Orb.Class({Extends:DeskPRO.Agent.PageFragment.ListPane.Basic,initializeProperties:function(){this.parent();
this.TYPENAME="publish_searchlog"},initPage:function(a){this.tabs=new DeskPRO.UI.SimpleTabs({triggerElements:$("li",this.getEl("tabs"))});
this.ownObject(this.tabs)}});Orb.createNamespace("DeskPRO.Agent.PageFragment.ListPane");DeskPRO.Agent.PageFragment.ListPane.IdeaCommentsValidating=new Orb.Class({Extends:DeskPRO.Agent.PageFragment.ListPane.PublishValidatingComments});
Orb.createNamespace("DeskPRO.Agent.PageFragment.ListPane");DeskPRO.Agent.PageFragment.ListPane.IdeaContentValidating=new Orb.Class({Extends:DeskPRO.Agent.PageFragment.ListPane.PublishValidatingContent});