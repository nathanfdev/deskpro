Orb.createNamespace("DeskPRO.Agent.PageFragment.ListPane");DeskPRO.Agent.PageFragment.ListPane.Basic=new Class({Extends:DeskPRO.Agent.PageFragment.Basic,initialize:function(a){this.parent(a);
this.addEvent("activate",(function(){DeskPRO_Window.getMessageBroker().sendMessage("list-page-fragment.activated",{page:this})
}).bind(this))},initPage:function(a){$("li",a).click(function(){DeskPRO_Window.runPageRouteFromElement(this)})}});Orb.createNamespace("DeskPRO.Agent.PageFragment.ListPane");
DeskPRO.Agent.PageFragment.ListPane.BasicTicketResults=new Class({Extends:DeskPRO.Agent.PageFragment.ListPane.Basic,wrapper:null,contentWrapper:null,barWrapper:null,layout:null,overlay:null,appendUrl:null,actionsBarHelper:null,resultTypeName:"basic",resultTypeId:"general",changeManager:null,loadFirst:false,initPage:function(d){window.TICKET_LIST=this;
this.loadFirst=this.getMetaData("loadFirst");if(this.loadFirst){this.loadFirst=false;var b=$("td.subject:first a.with-route:first",d);
if(b.length){DeskPRO_Window.runPageRouteFromElement(b)}}this.wrapper=$(d);this.topSection=$(".list-top-area",this.wrapper);
this.barWrapper=$("div.layout-footer:first",this.wrapper);if(!this.barWrapper.length){var f=true}else{var f=false}this.contentWrapper=$(".content:first",this.wrapper);
this.listColDrag=new DeskPRO.Agent.PageHelper.ListColDrag({table:$("table:first",this.contentWrapper).get(0),onlyRowSel:".line-2",onlyRowColOffset:2});
this.listColResize=new DeskPRO.Agent.PageHelper.ListColResize({table:$("table:first",this.contentWrapper).get(0)});var e=Orb.getUniqueId("listpane_");
var c=Orb.getUniqueId("listpane_");this.contentWrapper.attr("id",e);this.barWrapper.attr("id",c);if(f){this.layout={wrapper:this.wrapper,paneWrapper:this.wrapper.parent(),content:this.contentWrapper,footer:$(),isFooterOpen:false,doLayout:function(){},expandFooter:function(){},collapseFooter:function(){}}
}else{this.layout=new DeskPRO.Agent.Layout.FooterActionbarLayout(this.wrapper)}this.changeManager=new DeskPRO.Agent.TicketList.ChangeManager(this);
this._initDisplayOptions();this._initInfiniteScroll();this._initFlagMenu();this._initGroupingOptions();this._initSearchOptions();
if(f){this.actionsBarHelper={page:null,wrapper:null,contentWrapper:null,tableEl:null,selectedActionData:null,ticketBar:null,barWrapper:null,initOverlay:function(){},getSelectedTicketIds:function(){},setActiveTable:function(){},handleTicketCheckClick:function(){},updateCount:function(){},applyActions:function(){},toggleMacroApplyBtn:function(){},saveActions:function(){}}
}else{this.actionsBarHelper=new DeskPRO.Agent.PageHelper.TicketActionsBar(this);this.actionsBarHelper.setActiveTable($("table.list:first",this.contentWrapper))
}this.initFeaturesOnCollection(d,{routes:[".with-route"],times:[".timeago"]});DeskPRO_Window.runOpenTicketStateOnElement(d);
if(this.getMetaData("noResults")){this.noMoreResults=true;$(".no-more-results",this.contentWrapper).show()}DeskPRO_Window.getMessageBroker().addMessageListener("tickets.deleted",(function(g){var a=[];
Array.each(g,function(h){a.push("tr.ticket-"+h)});a=a.join(", ");$(a,this.contentWrapper).fadeOut(400,function(){$(this).remove()
})}).bind(this));DeskPRO_Window.getMessageBroker().addMessageListener("window.innerLayout.resize",(function(){this._handleResize()
}).bind(this));this.contentWrapper.addClass("scroll-content").tinyscrollbar();if(this.getMetaData("isNewRecentSearch")){DeskPRO_Window.getMessageBroker().sendMessage("agent.new-recent-search")
}},_handleResize:function(){if(!this.layout){return}this.layout.resizeAll()},destroyPage:function(){if(this.flagMenu){this.flagMenu.destroy()
}if(this.displayOptionsOverlay){this.displayOptionsOverlay.destroy()}},addTicket:function(b){if(!this.meta.loadSingleUrl){return
}var a=this.meta.loadSingleUrl.replace("$ticket_id",b).replace("$view_type",this.meta.viewType);$.ajax({url:a,dataType:"html",context:this,success:function(c){var d=$(c);
d.hide();$(".with-route",d).click(function(){DeskPRO_Window.runPageRouteFromElement(this)});$(".timeago",d).timeago();$(".deskpro-results-list",this.wrapper).prepend(d);
d.slideDown()}})},delTicket:function(b){var a=$(".ticket-"+b,this.contentWrapper);a.animate({height:"toggle",opacity:"toggle"},"slow",function(){a.remove()
})},_initSearchOptions:function(){var b=$(".summary .edit",this.topSection);b.click(this.showSearchForm.bind(this));var a=$("form.ticket-search-form",this.topSection);
a.submit(function(d){d.preventDefault();var c=a.attr("action");var e=a.serializeArray();DeskPRO_Window.loadListPane(c,{postData:e})
})},showSearchForm:function(){var d=$(".search-form",this.topSection);var e=$(".search-builder-tpl",this.topSection);var b=new DeskPRO.Form.RuleBuilder(e);
$(".add-term",d).data("add-count",0).click(function(){var f=parseInt($(this).data("add-count"));var g="terms["+f+"]";$(this).data("add-count",f+1);
b.addNewRow($(".search-terms",d),g)});var c=$(".search-form-data:first",this.topSection);if(c.length){var a=c.get(0).innerHTML;
a=$.parseJSON(a);if(a.terms){Array.each(a.terms,function(h,f){var g="terms[initial_"+f+"]";b.addNewRow($(".search-terms",d),g,{type:h.type,op:h.op,options:h.options})
})}if(a.order_by){$('[name="order_by"]',this.topSection).val(a.order_by)}c.remove()}$(".summary",this.topSection).slideUp();
$(".form-panel",this.topSection).slideDown()},_initGroupingOptions:function(){var a=this;$("div.search-top ul.grouping-info > li[data-group-id]",this.contentWrapper).click(function(){a.switchToSubgroup($(this).data("group-id"),$(this))
})},switchToSubgroup:function(b,a){if(b=="NONE"){this.appendUrl=null}else{this.appendUrl="&group_field_id="+b}$("table.list tbody",this.contentWrapper).remove();
this.loadResultPage(1);$("div.search-top ul.grouping-info > li",this.contentWrapper).removeClass("on");if(a){a.addClass("on")
}},flagMenu:null,_initFlagMenu:function(){var a=this;this.flagMenu=new DeskPRO.UI.Menu({menuElement:$("> ul.ticket-flag-menu:first",this.contentWrapper),onItemClicked:function(b){a._handleFlagMenuClick(b)
}});$("table.list:first",this.contentWrapper).delegate("span.ticket-flag","click",function(b){a.flagMenu.openMenu(b)})},_handleFlagMenuClick:function(e){var d=$(e.itemEl);
var c=d.data("flag");var b=$(e.menu.getOpenTriggerElement());var f=b.parent().parent().data("ticket-id");var a=b.data("flag");
b.removeClass("icon-flag-"+a);b.addClass("icon-flag-"+c);b.data("flag",c);console.debug("todo: loading element with flag click");
$.ajax({url:BASE_URL+"agent/tickets/"+f+"/ajax-save-flagged",type:"POST",context:this,data:{color:c},dataType:"json",success:function(g){}})
},displayOptionsWrapper:null,displayOptionsOverlay:null,displayOptionsList:null,_initDisplayOptions:function(){if(this.meta.viewTypeUrl){var c=$("nav.mode-buttons:first",this.contentWrapper);
var b=this;$("li:not(.on)",c).click(function(d){d.preventDefault();var e=$(this).data("view-type");b.switchViewType(e)})}this.displayOptionsList=$(".display-options:first ul.sortable-list",this.contentWrapper);
var a=this.displayOptionsWrapper=$(".display-options:first",this.contentWrapper);this.displayOptionsOverlay=new DeskPRO.UI.Overlay({contentElement:a,triggerElement:$(".display-options-trigger th:not(.no-option-trigger), header .display-options-trigger",this.contentWrapper),onContentSet:function(d){$("ul.sortable-list",d.wrapperEl).sortable({axis:"y"})
}});$(".save-trigger",a).click((function(){this.saveDisplayOptions()}).bind(this));var b=this;$(".list thead th",this.contentWrapper).each(function(){$('li[data-field="'+$(this).data("field")+'"] input[type="checkbox"]',b.displayOptionsList).attr("checked",true)
})},switchViewType:function(g){var e=this.meta.viewTypeUrl.replace("$view_type",g);if(g=="list"){var a=$(window).width()-100;
var d=$(window).height()-100;var c=$("<div>Loading...</div>");c.width(a);c.height(d);c.css("overflow","auto");var b=new DeskPRO.UI.Overlay({contentElement:c,destroyOnClose:true,customClassname:"no-padding",maxWidth:a,maxHeight:d});
b.openOverlay();var f=function(h){$.ajax({timeout:20000,type:"GET",url:h,dataType:"html",success:function(i){if(b.isDestroyed()){return
}var j=DeskPRO_Window.createPageFragment(i,"DeskPRO.Agent.PageFragment.ListPane.Basic");j.setMetaData("routeUrl",h);j.setMetaData("pageReloader",f);
c.html(j.html);j.fireEvent("render",[c]);j.fireEvent("activate")}})};f(e);return}DeskPRO_Window.loadListPane(e,null,function(){DeskPRO_Window.removePage(self)
})},saveDisplayOptions:function(){$(".loading-off",this.displayOptionsWrapper).hide();$(".loading-on",this.displayOptionsWrapper).show();
var d=[];var a="prefs[agent.ui.ticket-"+this.resultTypeName+"-display-fields."+this.resultTypeId+"][]";$('input[type="checkbox"]:checked',this.displayOptionsList).each(function(){d.push({name:a,value:$(this).attr("name")})
});d.push({name:"prefs[agent.ui.ticket-"+this.resultTypeName+"-order-by."+this.resultTypeId+"]",value:$('select[name="order_by"]',this.displayOptionsWrapper).val()});
var c=this.getMetaData("refreshUrl");if(this.appendUrl){c+=this.appendUrl}var b=this;$.ajax({timeout:20000,type:"POST",url:this.getMetaData("saveListPrefsUrl"),data:d,context:this,success:function(){if(this.meta.pageReloader){this.displayOptionsOverlay.closeOverlay();
this.fireEvent("destroy");this.meta.pageReloader(c)}else{DeskPRO_Window.loadListPane(c,null,function(){DeskPRO_Window.removePage(b)
})}}})},_initInfiniteScroll:function(){this.contentWrapper.scroll((function(){if(this.contentWrapper.scrollTop()+50>=this._scrollInnerHeights()-this.contentWrapper.height()){this.nextSearchPage()
}}).bind(this))},_scrollInnerHeights_cache:null,_scrollInnerHeights:function(){if(this._scrollInnerHeights_cache!==null){return this._scrollInnerHeights_cache
}var a=0;this.contentWrapper.children(":visible").each(function(){a+=$(this).height()});this._scrollInnerHeights_cache=a;
return a},isLoadingNext:false,noMoreResults:false,nextSearchPage:function(){var a=parseInt($(".page-set:last",this.contentWrapper).data("page"));
this.loadResultPage(a+1)},loadResultPage:function(b){if(this.isLoadingNext||this.noMoreResults){return}this.isLoadingNext=true;
var c=$(".loading-more",this.contentWrapper);c.detach().appendTo(this.contentWrapper);c.show();var a=this.getMetaData("pageUrl").replace("$page",b);
if(this.appendUrl){a+=this.appendUrl}$.ajax({cache:false,type:"GET",url:a,context:this,dataType:"json",success:function(d){this._handleAjaxSuccess(d)
}})},_handleAjaxSuccess:function(f){this.isLoadingNext=false;$(".loading-more",this.contentWrapper).hide();if(f.no_more_results){this.noMoreResults=true;
var c=$(".no-more-results",this.contentWrapper);c.detach().appendTo(this.contentWrapper);c.show();return}this._scrollInnerHeights_cache=null;
var d=f.html;var e=$(d);this.initFeaturesOnCollection(e,{routes:[".with-route"],times:[".timeago"]});DeskPRO_Window.runOpenTicketStateOnElement(e);
$("table.list",this.contentWrapper).append(e);if(this.loadFirst){this.loadFirst=false;var b=$("td.subject:first a.with-route:first",e);
if(b.length){DeskPRO_Window.runPageRouteFromElement(b)}}}});Orb.createNamespace("DeskPRO.Agent.PageFragment.ListPane");DeskPRO.Agent.PageFragment.ListPane.BasicOrganizationResults=new Class({Extends:DeskPRO.Agent.PageFragment.ListPane.Basic,wrapper:null,contentWrapper:null,overlay:null,appendUrl:null,actionsBarHelper:null,resultTypeName:"basic",resultTypeId:"general",initPage:function(a){this.wrapper=$(a);
this.contentWrapper=$("div.content:first",this.wrapper);this._initDisplayOptions();this.initFeaturesOnCollection(a,{routes:["tr .with-route"],times:["tr abbr.timeago"]});
if(this.getMetaData("noResults")){this.noMoreResults=true;$(".no-more-results",this.contentWrapper).show()}DeskPRO_Window.getMessageBroker().addMessageListener("window.innerLayout.resize",(function(){this._handleResize()
}).bind(this))},destroyPage:function(){if(this.displayOptionsOverlay){this.displayOptionsOverlay.destroy()}},activate:function(){this.attachInfiniteScroll()
},deactivate:function(){this.deattachInfiniteScroll()},displayOptionsWrapper:null,displayOptionsOverlay:null,displayOptionsList:null,_initDisplayOptions:function(){this.displayOptionsList=$(".display-options:first ul.sortable-list",this.contentWrapper);
var a=this.displayOptionsWrapper=$(".display-options:first",this.contentWrapper);this.displayOptionsOverlay=new DeskPRO.UI.Overlay({contentElement:a,triggerElement:$(".display-options-trigger",this.contentWrapper),onContentSet:function(c){$("ul.sortable-list",c.wrapperEl).sortable({axis:"y"})
}});$(".close-trigger",a).click((function(){this.displayOptionsOverlay.closeOverlay()}).bind(this));$(".save-trigger",a).click((function(){this.saveDisplayOptions()
}).bind(this));var b=this;$(".list thead th",this.contentWrapper).each(function(){$('li[data-field="'+$(this).data("field")+'"] input[type="checkbox"]',b.displayOptionsList).attr("checked",true)
})},saveDisplayOptions:function(){$(".buttons .loading-off",this.displayOptionsWrapper).hide();$(".buttons .loading-on",this.displayOptionsWrapper).show();
var d=[];var a="prefs[agent.ui.org-"+this.resultTypeName+"-display-fields."+this.resultTypeId+"][]";$('input[type="checkbox"]:checked',this.displayOptionsList).each(function(){d.push({name:a,value:$(this).attr("name")})
});d.push({name:"prefs[agent.ui.org-"+this.resultTypeName+"-order-by."+this.resultTypeId+"]",value:$('select[name="order_by"]',this.displayOptionsWrapper).val()});
var c=this.getMetaData("refreshUrl");if(this.appendUrl){c+=this.appendUrl}var b=this;$.ajax({timeout:20000,type:"POST",url:this.getMetaData("saveListPrefsUrl"),data:d,success:function(){DeskPRO_Window.loadListPane(c,null,function(){DeskPRO_Window.removePage(b)
})}})},attachInfiniteScroll:function(){this._handleOnScroll_bound=this._handleOnScroll.bind(this);this.wrapper.parent().scroll(this._handleOnScroll_bound)
},deattachInfiniteScroll:function(){if(this._handleOnScroll_bound){this.wrapper.parent().unbind("scroll",this._handleOnScroll_bound);
this._handleOnScroll_bound=null}},_handleOnScroll_bound:null,_handleOnScroll:function(){var a=this.wrapper.parent();var b=this.contentWrapper;
if((a.scrollTop()+50+a.height())>=b.height()){this.nextSearchPage()}},isLoadingNext:false,noMoreResults:false,nextSearchPage:function(){var a=parseInt($(".page-set:last",this.contentWrapper).data("page"));
this.loadResultPage(a+1)},loadResultPage:function(b){if(this.isLoadingNext||this.noMoreResults){return}this.isLoadingNext=true;
var c=$(".loading-more",this.contentWrapper);c.detach().appendTo(this.contentWrapper);c.show();var a=this.getMetaData("pageUrl").replace("$page",b);
if(this.appendUrl){a+=this.appendUrl}$.ajax({cache:false,type:"GET",url:a,context:this,dataType:"json",success:function(d){this._handleAjaxSuccess(d)
}})},_handleAjaxSuccess:function(f){this.isLoadingNext=false;$(".loading-more",this.contentWrapper).hide();if(f.no_more_results){this.noMoreResults=true;
var c=$(".no-more-results",this.contentWrapper);c.detach().appendTo(this.contentWrapper);c.show();return}this._scrollInnerHeights_cache=null;
var d=f.html;var e=$(d);this.initFeaturesOnCollection(e,{routes:[".with-route"],times:["abbr.timeago"]});$("table.list",this.contentWrapper).append(e);
if(this.loadFirst){this.loadFirst=false;var b=$("td.subject:first a.with-route:first",e);if(b.length){DeskPRO_Window.runPageRouteFromElement(b)
}}}});Orb.createNamespace("DeskPRO.Agent.PageFragment.ListPane");DeskPRO.Agent.PageFragment.ListPane.BasicPeopleResults=new Class({Extends:DeskPRO.Agent.PageFragment.ListPane.Basic,wrapper:null,contentWrapper:null,overlay:null,appendUrl:null,actionsBarHelper:null,resultTypeName:"basic",resultTypeId:"general",initPage:function(a){this.wrapper=$(a);
this.contentWrapper=$("div.content:first",this.wrapper);this._initDisplayOptions();this._initTermsOverlay();this.initFeaturesOnCollection(a,{routes:[".with-route"],times:["abbr.timeago"]});
if(this.getMetaData("noResults")){this.noMoreResults=true;$(".no-more-results",this.contentWrapper).show()}this.contentWrapper.addClass("scroll-content").tinyscrollbar();
DeskPRO_Window.getMessageBroker().addMessageListener("window.innerLayout.resize",(function(){this._handleResize()}).bind(this))
},destroyPage:function(){if(this.displayOptionsOverlay){this.displayOptionsOverlay.destroy()}if(this.termsOverlay){this.termsOverlay.destroy()
}},activate:function(){this.attachInfiniteScroll()},deactivate:function(){this.deattachInfiniteScroll()},_initTermsOverlay:function(){var a=this.displayTermsWrapper=$(".display-terms:first",this.contentWrapper);
var b=this.getMetaData("preselectTerms").terms;this.termsOverlay=new DeskPRO.UI.Overlay({contentElement:a,triggerElement:$(".edit-terms-trigger",this.contentWrapper),onContentSet:function(d){var c=new DeskPRO.Form.RuleBuilder($(".search-builder-tpl",a));
c.addEvent("newRow",function(e){$(".remove",e).click(function(){e.remove()})});$(".add-term",a).data("add-count",0).click(function(){var e=parseInt($(this).data("add-count"));
var f="terms["+e+"]";$(this).data("add-count",e+1);c.addNewRow($(".search-terms",a),f)});Object.each(b,function(g,f){var i=Orb.uuid();
var h=g[0];var e=g[1];c.addNewRow($(".search-terms",a),"terms[exist_"+i+"]",{rule_type:f,op:h,choice:e})})}});$(".save-trigger",a).click((function(){this.submitSearchTerms()
}).bind(this))},submitSearchTerms:function(){var c=$("form.people_search_form:first",this.displayTermsWrapper);var b=c.attr("action");
var d=c.serializeArray();$(".loading-off",this.displayTermsWrapper).hide();$(".loading-on",this.displayTermsWrapper).show();
var a=this;DeskPRO_Window.loadListPane(b,{postData:d},function(){DeskPRO_Window.removePage(a)})},displayOptionsWrapper:null,displayOptionsOverlay:null,displayOptionsList:null,_initDisplayOptions:function(){this.displayOptionsList=$(".display-options:first ul.sortable-list",this.contentWrapper);
var a=this.displayOptionsWrapper=$(".display-options:first",this.contentWrapper);this.displayOptionsOverlay=new DeskPRO.UI.Overlay({contentElement:a,triggerElement:$(".display-options-trigger",this.contentWrapper),onContentSet:function(c){$("ul.sortable-list",c.wrapperEl).sortable({axis:"y"})
}});$(".close-trigger",a).click((function(){this.displayOptionsOverlay.closeOverlay()}).bind(this));$(".save-trigger",a).click((function(){this.saveDisplayOptions()
}).bind(this));var b=this;$(".list thead th",this.contentWrapper).each(function(){$('li[data-field="'+$(this).data("field")+'"] input[type="checkbox"]',b.displayOptionsList).attr("checked",true)
})},saveDisplayOptions:function(){$(".loading-off",this.displayOptionsWrapper).hide();$(".loading-on",this.displayOptionsWrapper).show();
var d=[];var a="prefs[agent.ui.people-"+this.resultTypeName+"-display-fields."+this.resultTypeId+"][]";$('input[type="checkbox"]:checked',this.displayOptionsList).each(function(){d.push({name:a,value:$(this).attr("name")})
});d.push({name:"prefs[agent.ui.people-"+this.resultTypeName+"-order-by."+this.resultTypeId+"]",value:$('select[name="order_by"]',this.displayOptionsWrapper).val()});
var c=this.getMetaData("refreshUrl");if(this.appendUrl){c+=this.appendUrl}var b=this;$.ajax({timeout:20000,type:"POST",url:this.getMetaData("saveListPrefsUrl"),data:d,success:function(){DeskPRO_Window.loadListPane(c,null,function(){DeskPRO_Window.removePage(b)
})}})},attachInfiniteScroll:function(){this._handleOnScroll_bound=this._handleOnScroll.bind(this);this.wrapper.parent().scroll(this._handleOnScroll_bound)
},deattachInfiniteScroll:function(){if(this._handleOnScroll_bound){this.wrapper.parent().unbind("scroll",this._handleOnScroll_bound);
this._handleOnScroll_bound=null}},_handleOnScroll_bound:null,_handleOnScroll:function(){var a=this.wrapper.parent();var b=this.contentWrapper;
if((a.scrollTop()+50+a.height())>=b.height()){this.nextSearchPage()}},isLoadingNext:false,noMoreResults:false,nextSearchPage:function(){var a=parseInt($(".page-set:last",this.contentWrapper).data("page"));
this.loadResultPage(a+1)},loadResultPage:function(b){if(this.isLoadingNext||this.noMoreResults){return}this.isLoadingNext=true;
var c=$(".loading-more",this.contentWrapper);c.detach().appendTo(this.contentWrapper);c.show();var a=this.getMetaData("pageUrl").replace("$page",b);
if(this.appendUrl){a+=this.appendUrl}$.ajax({cache:false,type:"GET",url:a,context:this,dataType:"json",success:function(d){this._handleAjaxSuccess(d)
}})},_handleAjaxSuccess:function(f){this.isLoadingNext=false;$(".loading-more",this.contentWrapper).hide();if(f.no_more_results){this.noMoreResults=true;
var c=$(".no-more-results",this.contentWrapper);c.detach().appendTo(this.contentWrapper);c.show();return}this._scrollInnerHeights_cache=null;
var d=f.html;var e=$(d);this.initFeaturesOnCollection(e,{routes:[".with-route"],times:["abbr.timeago"]});$("table.list",this.contentWrapper).append(e);
if(this.loadFirst){this.loadFirst=false;var b=$("td.subject:first a.with-route:first",e);if(b.length){DeskPRO_Window.runPageRouteFromElement(b)
}}}});Orb.createNamespace("DeskPRO.Agent.PageFragment.ListPane");DeskPRO.Agent.PageFragment.ListPane.OrganizationCustomFilter=new Class({Extends:DeskPRO.Agent.PageFragment.ListPane.BasicOrganizationResults,TYPENAME:"organization-custom-filter",resultTypeName:"filter",resultTypeId:0,initPage:function(a){this.parent(a);
this.resultTypeId=this.getMetaData("cache_id")}});Orb.createNamespace("DeskPRO.Agent.PageFragment.ListPane");DeskPRO.Agent.PageFragment.ListPane.PeopleCustomFilter=new Class({Extends:DeskPRO.Agent.PageFragment.ListPane.BasicPeopleResults,TYPENAME:"people-custom-filter",resultTypeName:"filter",resultTypeId:0,initPage:function(a){this.parent(a);
this.resultTypeId=this.getMetaData("cache_id")}});Orb.createNamespace("DeskPRO.Agent.PageFragment.ListPane");DeskPRO.Agent.PageFragment.ListPane.TicketFilter=new Class({Extends:DeskPRO.Agent.PageFragment.ListPane.BasicTicketResults,TYPENAME:"ticket-filter",resultTypeName:"filter",resultTypeId:0,initPage:function(a){this.parent(a);
this.resultTypeId=this.getMetaData("filter_id")},activate:function(){if(this.getMetaData("filter_id")){DeskPRO_Window.getMessageBroker().sendMessage("filter.view-activated",this.getMetaData("filter_id"))
}},deactivate:function(){if(this.getMetaData("filter_id")){DeskPRO_Window.getMessageBroker().sendMessage("filter.view-deactivated",this.getMetaData("filter_id"))
}}});Orb.createNamespace("DeskPRO.Agent.PageFragment.ListPane");DeskPRO.Agent.PageFragment.ListPane.TicketFlagged=new Class({Extends:DeskPRO.Agent.PageFragment.ListPane.BasicTicketResults,TYPENAME:"ticket-flagged",resultTypeName:"flagged",resultTypeId:0,initPage:function(a){DeskPRO.Agent.PageFragment.ListPane.BasicTicketResults.prototype.initPage.apply(this,[a]);
this.resultTypeId=this.getMetaData("flag")}});Orb.createNamespace("DeskPRO.Agent.PageFragment.ListPane");DeskPRO.Agent.PageFragment.ListPane.TicketDeletedList=new Class({Extends:DeskPRO.Agent.PageFragment.ListPane.BasicTicketResults,TYPENAME:"ticket-deleted-list",resultTypeName:"filter",resultTypeId:0,initPage:function(a){this.parent(a);
this.resultTypeId=this.getMetaData("cache_id")}});Orb.createNamespace("DeskPRO.Agent.PageFragment.ListPane");DeskPRO.Agent.PageFragment.ListPane.TicketCustomFilter=new Class({Extends:DeskPRO.Agent.PageFragment.ListPane.BasicTicketResults,TYPENAME:"ticket-custom-filter",resultTypeName:"filter",resultTypeId:0,initPage:function(a){this.parent(a);
this.resultTypeId=this.getMetaData("cache_id")}});Orb.createNamespace("DeskPRO.Agent.PageFragment.ListPane");DeskPRO.Agent.PageFragment.ListPane.TicketCustomFilterForm=new Class({Extends:DeskPRO.Agent.PageFragment.ListPane.Basic,wrapper:null,initPage:function(a){this.wrapper=$(a);
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
DeskPRO.Agent.PageFragment.ListPane.TwitterStatus=new Class({Extends:DeskPRO.Agent.PageFragment.ListPane.Basic,wrapper:null,header:null,content:null,initPage:function(a){this.wrapper=$(a);
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
}else{alert(b.error)}}})}});Orb.createNamespace("DeskPRO.Agent.PageFragment.ListPane");DeskPRO.Agent.PageFragment.ListPane.RecycleBin=new Class({Extends:DeskPRO.Agent.PageFragment.ListPane.Basic,wrapper:null,contentWrapper:null,barWrapper:null,layout:null,overlay:null,appendUrl:null,actionsBarHelper:null,resultTypeName:"basic",resultTypeId:"general",changeManager:null,loadFirst:false,initPage:function(b){this.wrapper=b;
var a=this;$(".type-list",b).each(function(){a.initTypeList($(this))});$("time.timeago",this.wrapper).timeago();this.initRoutesOnCollection($(".with-route",this.wrapper))
},initTypeList:function(c){var b=c.data("load-name");var a=this;$(".list-load-more",c).click(function(){a.loadMore(b)})},loadMore:function(e){var b=$("."+e+"-list.type-list",this.wrapper);
var d=$("table:first",b);var c=$(".list-load-more",b);var f=$("tbody:last",d);var a=parseInt(f.data("page"))+1;$.ajax({url:BASE_URL+"agent/recycle-bin/"+e+"/"+a,dataType:"json",success:function(h){if(h.no_more_results){c.hide()
}if(h.count<1){return}var g=$(h.htmls);$("time.timeago",g).timeago();this.initRoutesOnCollection($(".with-route",g));d.append(g)
}})}});Orb.createNamespace("DeskPRO.Agent.PageFragment.ListPane");DeskPRO.Agent.PageFragment.ListPane.KbGlossary=new Class({Extends:DeskPRO.Agent.PageFragment.ListPane.Basic,wrapper:null,initPage:function(b){this.wrapper=b;
var a=this;$(".new-word-trigger",b).click(this.showAddDlg.bind(this));$(".edit-word-trigger",b).click(function(c){c.preventDefault();
a.showEditDlg($(this).data("word-id"))})},showAddDlg:function(){var a=this.getAddDlg();a.openOverlay()},showEditDlg:function(d){var a=this.getEditDlg();
var b=$(".form",a.elements.wrapper);var c=$(".loading",a.elements.wrapper);b.hide();c.show();a.openOverlay();$.ajax({url:BASE_URL+"agent/kb/glossary/"+d+".json",type:"GET",context:this,dataType:"json",success:function(e){$(".word",b).html(e.word);
$("input.word_id",b).val(e.id);$("textarea.content",b).val(e.content);c.hide();b.show()}})},getAddDlg:function(){if(this.addDlg){return this.addDlg
}var a=$(".add-dlg:first",this.wrapper);this.addDlg=new DeskPRO.UI.Overlay({contentElement:a});$(".save-trigger",a).click(this.saveNewWord.bind(this));
return this.addDlg},getEditDlg:function(){if(this.editDlg){return this.editDlg}var a=$(".edit-dlg:first",this.wrapper);this.editDlg=new DeskPRO.UI.Overlay({contentElement:a});
$(".save-trigger",a).click(this.saveEditWord.bind(this));return this.editDlg},saveNewWord:function(){var a=[];a.push({name:"word",value:$("input.word",this.addDlg.elements.wrapperOuter).val().trim()});
a.push({name:"content",value:$("textarea.content",this.addDlg.elements.wrapperOuter).val().trim()});$.ajax({url:BASE_URL+"agent/kb/glossary/new-word.json",type:"POST",data:a,context:this,dataType:"json",success:function(h){var b=$(".counter-words",this.wrapper);
var f=parseInt(b.html());b.html(f+1);var e=h.letter;var c=h.word;var g=h.word_id;var j=$('<li><a class="edit-word-trigger" data-word-id="'+g+'">'+c+"</a></li>");
var d=$('dt[data-letter="'+e+'"]:first',this.wrapper);var i=$('dd[data-letter="'+e+'"]:first',this.wrapper);d.show();i.show();
$("ul",i).prepend(j);$("input.word",this.addDlg.elements.wrapperOuter).val("");$("textarea.content",this.addDlg.elements.wrapperOuter).val("");
this.addDlg.closeOverlay()}})},saveEditWord:function(){var b=$("input.word_id",this.editDlg.elements.wrapperOuter).val().trim();
var a=[];a.push({name:"word_id",value:b});a.push({name:"content",value:$("textarea.content",this.editDlg.elements.wrapperOuter).val().trim()});
$.ajax({url:BASE_URL+"agent/kb/glossary/"+b+"/edit.json",type:"POST",data:a,context:this,dataType:"json",success:function(c){}})
}});Orb.createNamespace("DeskPRO.Agent.PageFragment.ListPane");DeskPRO.Agent.PageFragment.ListPane.KbPendingArticles=new Class({Extends:DeskPRO.Agent.PageFragment.ListPane.Basic,wrapper:null,initPage:function(b){this.wrapper=b;
this.initRoutesOnCollection($(".with-route",b));$(".new-pending-article-trigger",b).click(this.showAddDlg.bind(this));var a=this;
DeskPRO_Window.getMessageBroker().addMessageListener("kb.pending_article_removed",function(c){a.removeFromList(c.pending_article_id)
})},showAddDlg:function(){var a=this.getAddDlg();a.openOverlay()},getAddDlg:function(){if(this.addDlg){return this.addDlg
}var b=$(".add-dlg:first",this.wrapper);this.addDlg=new DeskPRO.UI.Overlay({contentElement:b});var a=this;$(".save-trigger",b).click(function(){a.addDlg.closeOverlay();
a.saveNewPendingArticle()});return this.addDlg},saveNewPendingArticle:function(){var a=[];a.push({name:"comment",value:$(".comment",this.addDlg.elements.wrapperOuter).val().trim()});
$.ajax({url:BASE_URL+"agent/kb/pending-articles/new",type:"POST",data:a,context:this,dataType:"json",success:function(d){var b=$(".counter-pending",this.wrapper);
var c=parseInt(b.html());b.html(c+1);$(".pending-articles-wrap",this.wrapper).show();var e=$(d.row_html);this.initRoutesOnCollection($(".with-route",e));
$("tbody.pending-articles-list",this.wrapper).prepend(e)}})},removeFromList:function(d){var a=$(".counter-pending",this.wrapper);
var c=parseInt(a.html());a.html(c-1);var b=$(".pending-articles-wrap",this.wrapper);$("tr.pending_article-"+d,b).remove();
if(!$("tr.pending_article",b).length){b.hide()}}});Orb.createNamespace("DeskPRO.Agent.PageFragment.ListPane");DeskPRO.Agent.PageFragment.ListPane.KbValidatingArticles=new Class({Extends:DeskPRO.Agent.PageFragment.ListPane.Basic,wrapper:null,initPage:function(b){this.wrapper=b;
this.initRoutesOnCollection($(".with-route",b));var a=this;$("a.view-link.edit",this.wrapper).click(function(c){c.preventDefault();
a.loadPreviewEdit($(this).attr("href"))});$("a.view-link.article",this.wrapper).click(function(c){c.preventDefault();a.loadPreviewArticle($(this).attr("href"))
})},loadPreviewEdit:function(c){var a=this;var b=new DeskPRO.UI.Overlay({destroyOnClose:true,contentMethod:"ajax",contentAjax:{url:c,type:"GET",context:this,dataType:"html"},maxWidth:500,maxHeight:700,onContentSet:function(f){var e=f.contentEl;
var d=f.overlay;$("button.approve-trigger",e).click(function(g){g.preventDefault();a.approveEdit($("input.article_id",e).val());
d.closeOverlay()});$("button.disapprove-trigger",e).click(function(g){g.preventDefault();a.disapproveEdit($("input.article_id",e).val());
d.closeOverlay()})}});b.openOverlay()},approveEdit:function(a){$.ajax({url:BASE_URL+"agent/kb/validating-articles/validate/"+a+".json",type:"POST",context:this,dataType:"json",success:function(c){var b=c.article_id;
$(".article-"+b,this.wrapper).remove();this.removeFromList(b,c.type)}})},disapproveEdit:function(a){$.ajax({url:BASE_URL+"agent/kb/validating-articles/disapprove/"+a+".json",type:"POST",context:this,dataType:"json",success:function(c){var b=c.article_id;
$(".article-"+b,this.wrapper).remove();this.removeFromList(b,c.type)}})},removeFromList:function(b,e){var a=$(".counter-total",this.wrapper);
var f=$(".counter-"+e,this.wrapper);var c=parseInt(a.html());var d=parseInt(a.html());a.html(c-1);f.html(d-1);var g=$(".wrap-"+e,this.wrapper);
if(!$("tr.article",g).length){g.remove()}},loadPreviewArticle:function(c){var a=this;var b=new DeskPRO.UI.Overlay({destroyOnClose:true,contentMethod:"ajax",contentAjax:{url:c,type:"GET",context:this,dataType:"html"},maxWidth:500,maxHeight:700,onContentSet:function(f){var e=f.contentEl;
var d=f.overlay;$("button.approve-trigger",e).click(function(g){g.preventDefault();a.approveEdit($("input.article_id",e).val());
d.closeOverlay()});$("button.disapprove-trigger",e).click(function(g){g.preventDefault();a.disapproveEdit($("input.article_id",e).val());
d.closeOverlay()})}});b.openOverlay()}});Orb.createNamespace("DeskPRO.Agent.PageFragment.ListPane");DeskPRO.Agent.PageFragment.ListPane.KbList=new Class({Extends:DeskPRO.Agent.PageFragment.ListPane.Basic,wrapper:null,initPage:function(a){this.wrapper=a;
this.initRoutesOnCollection($(".with-route",a));this.listSearchForm=new DeskPRO.Agent.PageHelper.ListSearchForm(this,{form:$("form.kb-search-form",this.topSection),context:this.topSection,searchData:$(".search-form-data:first",this.topSection)});
this.listSearchForm.addEvent("searchSubmit",function(b,c){DeskPRO_Window.loadListPane(b,{postData:c})})}});Orb.createNamespace("DeskPRO.Agent.PageFragment.ListPane");
DeskPRO.Agent.PageFragment.ListPane.AgentChatHistory=new Class({Extends:DeskPRO.Agent.PageFragment.ListPane.Basic,wrapper:null,contentWrapper:null,initPage:function(a){this.wrapper=$(a);
this.contentWrapper=$("div.content:first",this.wrapper);this.initFeaturesOnCollection(a,{routes:[".with-route"],times:["abbr.timeago"]});
if(this.getMetaData("noResults")){this.noMoreResults=true;$(".no-more-results",this.contentWrapper).show()}this.contentWrapper.addClass("scroll-content").tinyscrollbar()
},activate:function(){this.attachInfiniteScroll()},deactivate:function(){this.deattachInfiniteScroll()},attachInfiniteScroll:function(){this._handleOnScroll_bound=this._handleOnScroll.bind(this);
this.wrapper.parent().scroll(this._handleOnScroll_bound)},deattachInfiniteScroll:function(){if(this._handleOnScroll_bound){this.wrapper.parent().unbind("scroll",this._handleOnScroll_bound);
this._handleOnScroll_bound=null}},_handleOnScroll_bound:null,_handleOnScroll:function(){var a=this.wrapper.parent();var b=this.contentWrapper;
if((a.scrollTop()+50+a.height())>=b.height()){this.nextSearchPage()}},isLoadingNext:false,noMoreResults:false,nextSearchPage:function(){var a=parseInt($(".page-set:last",this.contentWrapper).data("page"));
this.loadResultPage(a+1)},loadResultPage:function(b){if(this.isLoadingNext||this.noMoreResults){return}this.isLoadingNext=true;
var c=$(".loading-more",this.contentWrapper);c.detach().appendTo(this.contentWrapper);c.show();var a=this.getMetaData("pageUrl").replace("$page",b);
$.ajax({cache:false,type:"GET",url:a,context:this,dataType:"json",success:function(d){this._handleAjaxSuccess(d)}})},_handleAjaxSuccess:function(d){this.isLoadingNext=false;
$(".loading-more",this.contentWrapper).hide();if(d.no_more_results){this.noMoreResults=true;var a=$(".no-more-results",this.contentWrapper);
a.detach().appendTo(this.contentWrapper);a.show();return}this._scrollInnerHeights_cache=null;var b=d.html;var c=$(b);this.initFeaturesOnCollection(c,{routes:[".with-route"],times:["abbr.timeago"]});
$("table.list",this.contentWrapper).append(c);if(this.loadFirst){this.loadFirst=false}}});Orb.createNamespace("DeskPRO.Agent.PageFragment.ListPane");
DeskPRO.Agent.PageFragment.ListPane.OpenChats=new Class({Extends:DeskPRO.Agent.PageFragment.ListPane.Basic,wrapper:null,initPage:function(a){this.wrapper=a;
$("tr.with-route",a).click(function(){DeskPRO_Window.runPageRouteFromElement(this)})}});Orb.createNamespace("DeskPRO.Agent.PageFragment.ListPane");
DeskPRO.Agent.PageFragment.ListPane.IdeaFilter=new Class({Extends:DeskPRO.Agent.PageFragment.ListPane.Basic,wrapper:null,filterSearchForm:null,initPage:function(a){this.wrapper=a;
this.initRoutesOnCollection($(".with-route",a));this.filterSearchForm=$(".idea-filter-form",a);if(this.filterSearchForm){this._initFilterSearch()
}else{this.filterSearchForm=null}if(this.meta.isValidating){DeskPRO_Window.getMessageBroker().addMessageListener("validating-ideas.deleted",this.handleRemoveIdea.bind(this));
DeskPRO_Window.getMessageBroker().addMessageListener("validating-ideas.approved",this.handleRemoveIdea.bind(this))}},handleRemoveIdea:function(a){$(".idea-"+a.idea_id,this.wrapper).fadeOut()
},_initFilterSearch:function(){var a=this;$(":input",this.filterSearchForm).change(function(){$(".submit-row",a.filterSearchForm).show()
});$(".submit-trigger",this.filterSearchForm).click(function(d){d.preventDefault();var e=$("select, :input",a.filterSearchForm).serializeArray();
var b=a.meta.submitFilterUrl;var c={postData:e};DeskPRO_Window.loadListPane(b,c)})}});Orb.createNamespace("DeskPRO.Agent.PageFragment.ListPane");
DeskPRO.Agent.PageFragment.ListPane.IdeaCommentsValidating=new Class({Extends:DeskPRO.Agent.PageFragment.ListPane.Basic,wrapper:null,filterSearchForm:null,initPage:function(a){this.wrapper=a;
this.initRoutesOnCollection($(".with-route",a));this.initCommentRows(this.wrapper)},initCommentRows:function(b){var a=this;
$("button.delete-trigger",b).click(function(){a.handleDisapprove($(this).data("comment-id"))});$("button.approve-trigger",b).click(function(){a.handleApprove($(this).data("comment-id"))
})},handleApprove:function(a){$("article.comment-"+a,this.wrapper).fadeOut();$.ajax({url:BASE_URL+"agent/ideas/approve-comment/"+a,type:"POST",dataType:"json"})
},handleDisapprove:function(a){$("article.comment-"+a,this.wrapper).fadeOut();$.ajax({url:BASE_URL+"agent/ideas/disapprove-comment/"+a,type:"POST",dataType:"json"})
}});Orb.createNamespace("DeskPRO.Agent.PageFragment.ListPane");DeskPRO.Agent.PageFragment.ListPane.NewCustomFilter=new Class({Extends:DeskPRO.Agent.PageFragment.ListPane.Basic,wrapper:null,filterSearchForm:null,initPage:function(c){this.wrapper=c;
this.topSection=$(".list-top-area",this.wrapper);var f=$(".search-form",this.topSection);var g=$(".search-builder-tpl",this.topSection);
var b=new DeskPRO.Form.RuleBuilder(g);$(".add-term",f).data("add-count",0).click(function(){var h=parseInt($(this).data("add-count"));
var i="terms["+h+"]";$(this).data("add-count",h+1);b.addNewRow($(".search-terms",f),i)});var e=$(".search-form-data:first",this.topSection);
if(e.length){var a=e.get(0).innerHTML;a=$.parseJSON(a);if(a.terms){Array.each(a.terms,function(j,h){var i="terms[initial_"+h+"]";
b.addNewRow($(".search-terms",f),i,{type:j.type,op:j.op,options:j.options})})}if(a.order_by){$('[name="order_by"]',this.topSection).val(a.order_by)
}e.remove()}var d=$("form.ticket-search-form",this.topSection);d.submit(function(i){i.preventDefault();var h=d.attr("action");
var j=d.serializeArray();DeskPRO_Window.loadListPane(h,{postData:j})})}});Orb.createNamespace("DeskPRO.Agent.PageFragment.ListPane");
DeskPRO.Agent.PageFragment.ListPane.NewsList=new Class({Extends:DeskPRO.Agent.PageFragment.ListPane.Basic,wrapper:null,initPage:function(a){this.wrapper=a;
this.topSection=$(".list-top-area:first",this.wrapper);this.initRoutesOnCollection($(".with-route",a));this.listSearchForm=new DeskPRO.Agent.PageHelper.ListSearchForm(this,{form:$("form.news-search-form",this.topSection),context:this.topSection,searchData:$(".search-form-data:first",this.topSection)});
this.listSearchForm.addEvent("searchSubmit",function(b,c){DeskPRO_Window.loadListPane(b,{postData:c})})}});Orb.createNamespace("DeskPRO.Agent.PageFragment.ListPane");
DeskPRO.Agent.PageFragment.ListPane.DownloadList=new Class({Extends:DeskPRO.Agent.PageFragment.ListPane.Basic,wrapper:null,initPage:function(a){this.wrapper=a;
this.initRoutesOnCollection($(".with-route",a));this.topSection=$(".list-top-area:first",this.wrapper);this.listSearchForm=new DeskPRO.Agent.PageHelper.ListSearchForm(this,{form:$("form.download-search-form",this.topSection),context:this.topSection,searchData:$(".search-form-data:first",this.topSection)});
this.listSearchForm.addEvent("searchSubmit",function(b,c){DeskPRO_Window.loadListPane(b,{postData:c})})}});Orb.createNamespace("DeskPRO.Agent.PageFragment.ListPane");
DeskPRO.Agent.PageFragment.ListPane.PublishValidatingComments=new Class({Extends:DeskPRO.Agent.PageFragment.ListPane.Basic,wrapper:null,initPage:function(b){this.wrapper=b;
this.initRoutesOnCollection($(".with-route",b));var a=this;$("button.ignore-trigger",this.wrapper).click(function(c){c.preventDefault();
a.ignoreComment($(this).parent().parent().parent().parent())});$("button.approve-trigger",this.wrapper).click(function(c){c.preventDefault();
a.approveComment($(this).data("url"),$(this).parent().parent().parent().parent())});$("button.delete-trigger",this.wrapper).click(function(c){c.preventDefault();
a.deleteComment($(this).data("url"),$(this).parent().parent().parent().parent())})},ignoreComment:function(a){a.fadeOut()
},deleteComment:function(a,b){b.fadeOut();this.updateCount("sub");$.ajax({url:a,type:"POST",context:this,dataType:"json",error:function(){this.updateCount("add");
b.fadeIn()},success:function(c){b.remove()}})},approveComment:function(a,b){b.fadeOut();this.updateCount("sub");$.ajax({url:a,type:"POST",context:this,dataType:"json",error:function(){this.updateCount("add");
b.fadeIn()},success:function(c){b.remove()}})},updateCount:function(b){var c=$("#publish_validating_comments_count");var a=parseInt(c.text());
if(b=="add"){a++}else{a--}var c=$("#publish_validating_comments_count").text(a)}});