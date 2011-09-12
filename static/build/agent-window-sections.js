Orb.createNamespace("DeskPRO.Agent.WindowElement.Section");DeskPRO.Agent.WindowElement.Section.AbstractSection=new Orb.Class({Implements:[Orb.Util.Events,Orb.Util.Options],initialize:function(){this.addEvent("show",this.onShow);
this.addEvent("show",this._onFirstShowFire);this.addEvent("show",this._onShowSetVisible);this.addEvent("show",this._onShowActivateList);
this.addEvent("firstshow",this.onFirstShow);this.addEvent("hide",this.onHide);this.addEvent("hide",this._onHideSetVisible);
this.addEvent("hide",this._onHideDeactivateList);this._isVisible=false;this.init()},init:function(){},onShow:function(){},onFirstShow:function(){},onHide:function(){},setHasInitialLoaded:function(){this.hasLoaded=true;
$("#deskpro_outline_loading").removeClass("on")},setButtonElement:function(a){this.buttonEl=a},getButtonElement:function(){return this.buttonEl
},getSectionElement:function(){if(this.sectionEl){return this.sectionEl}return null},getListElement:function(){if(!this.listEl){this.setListElement()
}return this.listEl},setSectionElement:function(c,b){if(this.sectionEl){this.sectionEl.remove()}if(!c){c=$("<section></section>");
c.attr("id",Orb.getUniqueId("outline_"))}this.sectionEl=c;if(!c.parent().is("#dp_source")){this.sectionEl.detach().appendTo("#dp_source")
}if(!b){b=$("section.content",c);if(!b.length){var a=[];a.push('<div class="with-scrollbar '+this.sectionEl.attr("id")+'">');
a.push('<div class="scrollbar"><div class="track"><div class="thumb"><div class="end"></div></div></div></div>');a.push('<div class="scroll-viewport"><div class="scroll-content">');
a=a.join("");c=$(a);this.sectionEl.append(c);b=$("div.scroll-content:first",c)}}this.contentEl=b;var d=$(".with-scrollbar:first",this.sectionEl);
if(d.length){this.scrollerHandler=new DeskPRO.Agent.ScrollerHandler(this,d,{showEvent:"show",hideEvent:"hide"})}},setListElement:function(b,a){if(this.listEl){this.listEl.remove()
}if(!b){b=$("<section></section>");b.attr("id",Orb.getUniqueId("list_"))}this.listEl=b;if(!b.parent().is("#dp_list")){this.listEl.detach().appendTo("#dp_list")
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
DeskPRO_Window.getMessageChanneler().subscribeChannel("list-page-fragment.activated",this.highlightActiveSection.bind(this));
var a=this;this.getSectionElement().delegate("[data-route]","click",function(b){a.highlightNavItem($(this))});this.filterTicketIds={};
$.ajax({url:BASE_URL+"agent/tickets/get-section-data.json",context:this,success:function(b){this._initSection(b)}})},_initSection:function(b){this.setHasInitialLoaded();
this.contentEl.html(b.section_html);var a=this;this.tabs=new DeskPRO.UI.SimpleTabs({context:this.sectionEl,triggerElements:$("#tickets_outline_tabstrip li"),onTabSwitch:function(c){if(c.tabEl.is(".labels")){a.showLabelsList()
}else{if(c.tabEl.is(".flagged")){a.loadFlagCounts()}}}});this.filterTicketIds=b.filter_id_matches;this._initFilters();this._initFlagged();
$("#user_settings_filters_link").click(function(){var c=new DeskPRO.UI.Overlay({contentMethod:"iframe",iframeUrl:BASE_URL+"agent/settings/ticket-filters"});
c.openOverlay()});if(this.isVisible()&&!DeskPRO_Window.loadingListFragment){this._loadAutoLoadRoutes()}this.activeNavClass=null;
$(".show-hold-check",this.sectionEl).click(function(){a.toggleHoldDisplay()});this.filterGroupEditor=new DeskPRO.Agent.Widget.FilterGroupEditor({containerElement:"#tickets_outline .scroll-content",listElement:"#tickets_outline_sys_filters",boundListElement:"#tickets_outline_sys_hold_filters",triggerElement:"#ticket_filter_launch_editor",onGroupingChanged:function(c){a.refreshFilterGrouping([c])
}})},onShow:function(){this.activeNavClass=null},highlightActiveSection:function(c){if(!this.isVisible()){return}var b=c.page;
if(b.TYPENAME=="ticket-filter"){this.activeNavClass=".nav-filter-"+b.getMetaData("filter_id")}else{if(b.TYPENAME=="recyclebin"){this.activeNavClass=".archive-recycle-bin"
}else{if(b.TYPENAME=="ticket-custom-filter"||b.TYPENAME=="ticket-flagged"){var d=b.getMetaData("view_extra");var a=b.getMetaData("view_name");
switch(a){case"flag":if(d){this.activeNavClass=".nav-flag-"+d}break;case"label":if(d){this.activeNavClass=".nav-label-"+DeskPRO_Window.util.slugify(d)
}break;case"spam":this.activeNavClass=".nav-archive-spam";break;case"validating":this.activeNavClass=".nav-archive-validating";
break;case"pending":this.activeNavClass=".nav-archive-pending";break;case"resolved":this.activeNavClass=".nav-archive-resolved";
break;case"closed":this.activeNavClass=".nav-archive-closed";break}}}}this.highlightNav()},highlightNav:function(){if(this.activeNavClass){var a=$(this.activeNavClass,this.getSectionElement());
var b=$(".nav-selected",a);if(!b.length){$(".nav-selected",this.getSectionElement()).removeClass("nav-selected");a.addClass("nav-selected")
}}},highlightNavItem:function(a){if(!a.is("li")){a=a.closest("li")}$(".nav-selected",this.getSectionElement()).removeClass("nav-selected");
a.addClass("nav-selected")},_initFilters:function(){DeskPRO_Window.getPoller().addData([{name:"do[]",value:"get-sys-filter-counts"}],"filters.counts",{recurring:true,minDelay:120000});
DeskPRO_Window.getPoller().addData([{name:"do[]",value:"get-custom-filter-counts"}],"filters.counts",{recurring:true,minDelay:600000,minDelayAfterOne:true});
DeskPRO_Window.getMessageBroker().addMessageListener("filters.counts",this.updateFilterCounts.bind(this));$("ul#tickets_outline_filters_list").sortable({axis:"y",distance:8,update:function(){var a=[];
$("ul#tickets_outline_filters_list > li").each(function(){var b=$(this).data("filter-id");if(b){a.push({name:"prefs[agent.ui.ticket-filters-order][]",value:b})
}});$.ajax({timeout:20000,type:"POST",url:BASE_URL+"agent/misc/ajax-save-prefs",data:a})}});$("#tickets_outline_inbox_list .sub-toggle").click(function(c){c.stopPropagation();
var a=$(this).parent();var b=$("ul.sub-group",a);if(b.is(":visible")){b.slideUp();$(this).removeClass("open")}else{b.slideDown();
$(this).addClass("open")}});$("li.filter",this.sectionEl).each((function(b,c){c=$(c);var a=c.data("filter-id");if(this.filterTicketIds[a]){this.setFilterCount(a,this.filterTicketIds[a].length)
}else{this.setFilterCount(a,0)}}).bind(this));this._recountHold()},getFilterCount:function(a){return parseInt($("#ticket_filter_"+a+"_count").data("count")||0)
},setFilterCount:function(e,d){var c=d;e=parseInt(e);if(d>1000){c="1000+"}var a=DeskPRO_Window.getData("systemFilters")[e];
if(a){if(a=="all"){this.updateBadge(d)}var b=$("#ticket_filter_"+e+"_count").html(c).data("count",d);if(b.is(".is-hold-filter")){this._recountHold()
}}else{var b=$("#ticket_filter_"+e+"_count").html(c).data("count",d)}},_recountHold:function(){var a=0;$("#tickets_outline_sys_hold_filters li.filter",this.sectionEl).each((function(d,e){e=$(e);
var c=e.data("filter-id");if(this.filterTicketIds[c]){a+=this.filterTicketIds[c].length;this.setFilterCount(c,this.filterTicketIds[c].length)
}else{this.setFilterCount(c,0)}}).bind(this));$("#tickets_outline_sys_filters li.filter",this.sectionEl).each((function(d,e){e=$(e);
var c=e.data("filter-id");if(this.filterTicketIds[c]){a-=this.filterTicketIds[c].length;this.setFilterCount(c,this.filterTicketIds[c].length)
}else{this.setFilterCount(c,0)}}).bind(this));var b=$(".holdTicketCount",this.sectionEl);if(a<1){b.hide()}else{$(".count",b).text(a);
b.show()}},updateFilterCounts:function(a){Object.each(a,function(b,c){this.setFilterCount(c,b)},this)},filterUpdated:function(d){var a=parseInt(d.filter_id);
var e=parseInt(d.ticket_id);if(!this.filterTicketIds[a]){this.filterTicketIds[a]=[]}var c=null;if(this.listPage&&this.listPage.meta.filter_id==d.filter_id){c=this.listPage
}if(d.op=="add"){this.filterTicketIds[a].include(e);var b=this.filterTicketIds[a].length;this.setFilterCount(d.filter_id,b);
if(c&&d.ticket_id){c.addTicket(d.ticket_id)}}else{if(d.op=="del"){this.filterTicketIds[a].erase(e);var b=this.filterTicketIds[a].length;
this.setFilterCount(d.filter_id,b);if(c&&d.ticket_id){c.delTicket(d.ticket_id)}}}this.refreshFilterGrouping([a])},refreshFilterGrouping:function(d){var b=[];
var c=[];Array.each(d,function(f){f=parseInt(f);var i=$("li.filter-"+f,this.sectionEl);c.push(i.get(0));var h=null;var e=null;
if(i.data("filter-name")){h=$(".filter-"+i.data("filter-name")+"_w_hold",this.sectionEl);e=h.data("filter-id")}if(!this.filterTicketIds[f]&&(!e||!this.filterTicketIds[e])){return
}var g=this.getGroupingVar(f);if(!g||!g.length){this.setFilterGroupingContent(f,"");if(e){this.setFilterGroupingContent(e,"")
}return}b.push({name:"batches["+f+"][grouping]",value:g});Array.each(this.filterTicketIds[f],function(j){b.push({name:"batches["+f+"][ticket_ids][]",value:j})
});if(e){if(this.filterTicketIds[e]){b.push({name:"batches["+e+"][grouping]",value:g});Array.each(this.filterTicketIds[e],function(j){b.push({name:"batches["+e+"][ticket_ids][]",value:j})
})}else{this.setFilterGroupingContent(e,"")}}},this);var a=$(".listCounter",$(c)).first();a.addClass("loading");$.ajax({url:BASE_URL+"agent/ticket-search/group-tickets.json",type:"POST",dataType:"json",data:b,context:this,complete:function(){a.removeClass("loading")
},success:function(e){Object.each(e,function(g,f){this.setFilterGroupingContent(f,g)},this)}})},getGroupingVar:function(a){return $("#ticket_filter_group_editor .filter-"+a+" .field-option").val()
},setFilterGroupingContent:function(a,c){var e=$(".filter-"+a,this.sectionEl);var d=$("ul.subGroup",e);var g=this.getGroupingVar(a);
var f=$("a.groupHead",e).first().data("route");d.empty();if(c.length){d.html(c)}var b=$("> li",d);if(b.length){d.show();b.each(function(){var h=Orb.appendQueryData(f,"set_group_term",g);
h=Orb.appendQueryData(h,"set_group_option",$(this).data("grouping-option"));$("a",this).first().data("route",h);$("a",this).first().attr("data-route",h)
})}else{d.hide()}},toggleHoldDisplay:function(){var a=$(".show-hold-check",this.getSectionElement());a.toggleClass("checked");
var d=$("#tickets_outline_sys_filters > li > a > .listCounter");var c=$("#tickets_outline_sys_hold_filters > li > a > .listCounter");
var b=[];d.each(function(f){var e=c.eq(f);var h=parseInt($(this).text().trim());var g=parseInt(e.text().trim());if(h!=g){b.push(this);
b.push(e.get(0))}});b=$(b);b.addClass("loading");window.setTimeout(function(){if(a.is(".checked")){$("#tickets_outline_sys_filters").hide();
$("#tickets_outline_sys_hold_filters").show()}else{$("#tickets_outline_sys_hold_filters").hide();$("#tickets_outline_sys_filters").show()
}b.removeClass("loading")},200)},_initFlagged:function(){DeskPRO_Window.getMessageBroker().addMessageListener("filter-flagged.counts",this.updateFlagCounts.bind(this));
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
}})},_setLabelsList:function(a){$("#tickets_outline_labels").html(a);this.labelsTabs=new DeskPRO.UI.SimpleTabs({context:this.sectionEl,triggerElements:$("#ticketOutlineLabels li")})
}});Orb.createNamespace("DeskPRO.Agent.WindowElement.Section");DeskPRO.Agent.WindowElement.Section.People=new Orb.Class({Extends:DeskPRO.Agent.WindowElement.Section.AbstractSection,init:function(){this.buttonEl=$("#tickets_section");
this.setSectionElement($('<section id="people_outline"></section>'));$.ajax({url:BASE_URL+"agent/people/get-section-data.json",context:this,success:function(a){this._initSection(a)
}})},_initSection:function(b){this.setHasInitialLoaded();this.contentEl.html(b.section_html);var a=this;this.peopleTabs=new DeskPRO.UI.SimpleTabs({context:this.sectionEl,triggerElements:$("#people_outline_tabstrip li"),onTabSwitch:function(c){}});
this.orgTabs=new DeskPRO.UI.SimpleTabs({context:this.sectionEl,triggerElements:$("#people_outline_org_tabstrip li"),onTabSwitch:function(c){}});
this.contentEl.addClass("scroll-content").tinyscrollbar();if(this.isVisible()){this._onShowLoadList()}}});Orb.createNamespace("DeskPRO.Agent.WindowElement.Section");
DeskPRO.Agent.WindowElement.Section.Publish=new Orb.Class({Extends:DeskPRO.Agent.WindowElement.Section.AbstractSection,init:function(){this.buttonEl=$("#publish_section");
this.setSectionElement($('<section id="publish_outline"></section>'));$.ajax({url:BASE_URL+"agent/publish/get-section-data.json",context:this,success:function(a){this._initSection(a)
}})},_initSection:function(e){this.setHasInitialLoaded();this.contentEl.html(e.section_html);DeskPRO_Window.getMessageBroker().addMessageListener("publish.drafts.list-remove",function(g){DeskPRO_Window.util.modCountEl("#publish_drafts_count","-")
});DeskPRO_Window.getMessageBroker().addMessageListener("publish.drafts.list-add",function(g){DeskPRO_Window.util.modCountEl("#publish_drafts_count","+")
});var c=this;this.typeTabs=new DeskPRO.UI.SimpleTabs({context:this.sectionEl,triggerElements:$("#publish_outline_tabstrip li"),onTabSwitch:function(h){var i=h.tabContent.data("editor-class");
if(i){$("#publish_outline_edit_cats").data("editor-class",i).show()}else{$("#publish_outline_edit_cats").hide()}var g=$("a.all-route:first",h.tabContent);
if(g.length){}}});this._initGlossary();var d=["articles","downloads","news"];this.catEditors={};var b=function(h){var g=[];
Array.each(h,function(i){g.push({name:"orders[]",value:i})});return g};var a=function(h){var g=[];Object.each(h,function(i,j){g.push({name:"structure["+j+"]",value:i})
});return g};var f=function(g){var h=[];Object.each(g,function(i,j){h.push({name:"titles["+j+"]",value:i})});return h};Array.each(d,function(h){var i=$("#publish_outline_"+h+"cat_list");
var g=new DeskPRO.UI.CatListEditor({listEl:i,itemSelector:"li:not(.all)",newItemTplSelector:"#publish_outline_cat_list_newitem",editorBaseId:"publish_",onReordered:function(){$.ajax({url:BASE_URL+"agent/publish/categories/"+h+"/update-orders",data:b(g.getOrder()),type:"POST"})
},onRestructured:function(){$(".dp-cat-li",i).each(function(){var j=true;$(".list-counter",this).each(function(){if(parseInt($(this).text().trim())>0){j=false;
return false}});if(j){$(".delete-cat",this).show()}else{$(".delete-cat",this).hide()}});c.recountChildCounts(i);$.ajax({url:BASE_URL+"agent/publish/categories/"+h+"/update-structure",data:a(g.getStructure()),type:"POST"})
},onTitlesUpdated:function(j){$.ajax({url:BASE_URL+"agent/publish/categories/"+h+"/update-titles",data:f(j),type:"POST"})
},onNewAdded:function(j,k){if(j.is(".being-deleted")){return}var l=k.val().trim();$.ajax({url:BASE_URL+"agent/publish/categories/"+h+"/add-category",data:{title:l},type:"POST",dataType:"json",success:function(m){j.data("category-id",m.id);
$("a",j).data("route","listpane:"+m.url);$(".list-counter",j).attr("id",h+"_cat_count_"+m.id)}})}});$("#publish_outline_"+h+"cat_editmode").click(function(){var j=$(this).parent().parent();
j.toggleClass("edit-mode")});$("#publish_outline_"+h+"cat_edittiles").click(function(){if(g.isTitleEditing()){g.endEditTitles()
}else{g.showEditTitles()}});$("#publish_outline_"+h+"cat_addcat").click(function(){g.addNew()});$("#publish_outline_"+h+"cat_list").delegate(".edit-cat","click",function(k){var j=$(this).parent().parent();
g.showEditor(j)});$("#publish_outline_"+h+"cat_list").delegate(".delete-cat","click",function(m){var k=0;var j=$(this);while(!j.is("li")){if(k++>5){return
}j=j.parent()}var l=function(){$.ajax({url:BASE_URL+"agent/publish/categories/"+h+"/delete-category",data:{category_id:j.data("category-id")},type:"POST",dataType:"json",error:function(){j.show()
},success:function(n){j.remove()}})};j.addClass("being-deleted");if(j.data("category-id")){j.fadeOut("fast",l)}else{j.fadeOut("fast")
}});this.recountChildCounts(i)},this)},recountChildCounts:function(b){var a=this;$("> li",b).each(function(){var c=$(this);
var h=$(".list-counter:first",c);var e=parseInt(h.data("count"));var d=e;var g=$("> ul",c);var f=null;if(g.length){f=$("> li",g)
}if(f&&f.length){a.recountChildCounts(g);f.each(function(){d+=parseInt($(".list-counter:first",this).data("total-count"))
});h.text(e+"/"+d)}else{h.text(e)}h.data("total-count",d)})},_initGlossary:function(){this.glossaryWrapper=$("#publish_outline_glossary");
var a=this;$(".glossary-new-trigger",this.glossaryWrapper).click(this.showGlossaryAddDlg.bind(this));$(".glossary-word-trigger",this.glossaryWrapper).click(function(b){b.preventDefault();
a.showGlossaryEditDlg($(this).data("word-id"))})},showGlossaryAddDlg:function(){var a=this.getGlossaryAddDlg();a.openOverlay()
},showGlossaryEditDlg:function(d){var a=this.getGlossaryEditDlg();var b=$(".form",a.elements.wrapper);var c=$(".loading",a.elements.wrapper);
b.hide();c.show();a.openOverlay();$.ajax({url:BASE_URL+"agent/glossary/"+d+".json",type:"GET",context:this,dataType:"json",success:function(e){$(".word",b).html(e.word);
$("input.word_id",b).val(e.id);$("textarea.content",b).val(e.content);c.hide();b.show()}})},getGlossaryAddDlg:function(){if(this.addDlg){return this.addDlg
}var a=$(".glossary-add-dlg:first",this.glossaryWrapper);this.addDlg=new DeskPRO.UI.Overlay({contentElement:a});$(".save-trigger",a).click(this.saveNewWord.bind(this));
return this.addDlg},getGlossaryEditDlg:function(){if(this.editDlg){return this.editDlg}var a=$(".glossary-edit-dlg:first",this.glossaryWrapper);
this.editDlg=new DeskPRO.UI.Overlay({contentElement:a});$(".save-trigger",a).click(this.saveEditWord.bind(this));$(".delete-trigger",a).click(this.deleteEditWord.bind(this));
return this.editDlg},saveNewWord:function(){var a=[];a.push({name:"word",value:$("input.word",this.addDlg.elements.wrapperOuter).val().trim()});
a.push({name:"content",value:$("textarea.content",this.addDlg.elements.wrapperOuter).val().trim()});$.ajax({url:BASE_URL+"agent/glossary/new-word.json",type:"POST",data:a,context:this,dataType:"json",success:function(h){var b=$(".counter-words",this.glossaryWrapper);
var f=parseInt(b.html());b.html(f+1);var e=h.letter;var c=h.word;var g=h.word_id;var j=$('<li><a class="edit-word-trigger word-'+g+'" data-word-id="'+g+'">'+c+"</a></li>");
var d=$('dt[data-letter="'+e+'"]:first',this.glossaryWrapper);var i=$('dd[data-letter="'+e+'"]:first',this.glossaryWrapper);
d.show();i.show();$("ul",i).prepend(j);$("input.word",this.addDlg.elements.wrapperOuter).val("");$("textarea.content",this.addDlg.elements.wrapperOuter).val("");
DeskPRO_Window.util.modCountEl($(".glossary-word-count",this.getSectionElement()),"+");this.addDlg.closeOverlay()}})},saveEditWord:function(){var b=$("input.word_id",this.editDlg.elements.wrapperOuter).val().trim();
var a=[];a.push({name:"word_id",value:b});a.push({name:"content",value:$("textarea.content",this.editDlg.elements.wrapperOuter).val().trim()});
$.ajax({url:BASE_URL+"agent/glossary/"+b+"/edit.json",type:"POST",data:a,context:this,dataType:"json",success:function(d){var c=$(".word-"+b,this.glossaryWrapper);
DeskPRO_Window.util.showSavePuff(c);this.getGlossaryEditDlg().close()}})},deleteEditWord:function(){var a=$("input.word_id",this.editDlg.elements.wrapperOuter).val().trim();
$.ajax({url:BASE_URL+"agent/glossary/"+a+"/delete.json",type:"POST",context:this,dataType:"json",success:function(c){var b=$(".word-"+a,this.glossaryWrapper);
b.fadeOut("fast",function(){b.remove()});DeskPRO_Window.util.modCountEl($(".glossary-word-count",this.getSectionElement()),"-");
this.getGlossaryEditDlg().close()}})}});Orb.createNamespace("DeskPRO.Agent.WindowElement.Section");DeskPRO.Agent.WindowElement.Section.Twitter=new Orb.Class({Extends:DeskPRO.Agent.WindowElement.Section.AbstractSection,init:function(){this.buttonEl=$("#twitter_section");
this.setSectionElement($('<section id="twitter_outline"></section>'));$.ajax({url:BASE_URL+"agent/twitter/get-section-data.json",context:this,success:function(a){this._initSection(a)
}})},_initSection:function(a){this.setHasInitialLoaded();this.contentEl.html(a.section_html);if(this.isVisible()){this._onShowLoadList()
}this.contentEl.addClass("scroll-content").tinyscrollbar()}});Orb.createNamespace("DeskPRO.Agent.WindowElement.Section");
DeskPRO.Agent.WindowElement.Section.AgentChat=new Orb.Class({Extends:DeskPRO.Agent.WindowElement.Section.AbstractSection,init:function(){this.buttonEl=$("#agent_chat_section");
this.chatsWrapper=$("#agent_chats_wrapper");this.setSectionElement($('<section id="agent_chat_outline"></section>'));$("#agent_chat_conversation").template("agent_chat_conversation");
$("#agent_groupchat_conversation").template("agent_groupchat_conversation");$("#agent_chat_message").template("agent_chat_message");
$("#agent_chat_message_me").template("agent_chat_message_me");this._initMessageHandlers();this._initInterface()},onShow:function(){$.ajax({url:BASE_URL+"agent/agent-chat/get-section-data.json",context:this,success:function(a){this.setHasInitialLoaded();
this.contentEl.html(a.section_html)}})},_initMessageHandlers:function(){DeskPRO_Window.getMessageChanneler().subscribeChannel("agent_chat.new-message");
DeskPRO_Window.getMessageChanneler().subscribeChannel("agent.new-agent-online");DeskPRO_Window.getMessageBroker().addMessageListener("agent_chat.new-message",this.newIncomingMessage.bind(this));
DeskPRO_Window.getMessageBroker().addMessageListener("agent.new-agent-online",(function(a){var b=a.agent_id;this.addOnlineAgent.bind(b)
}).bind(this))},_initInterface:function(){this.panelEl=$("#agent_chat_panel");this.onlineListEl=$("#agent_online_list");this.offlineListEl=$("#agent_offline_list");
this.onlineCountEl=$("#chat_online_count");this.agentTeamList=$("#agent_team_list");$(".show-offline-opt",this.panelEl).click(function(){if($(this).is(":checked")){$("#agent_chat_panel").addClass("show-offline")
}else{$("#agent_chat_panel").removeClass("show-offline")}});this.panelEl.click(function(c){c.stopPropagation()});$(".show-section",this.panelEl).click(function(){DeskPRO_Window.switchToSection("agent_chat_section")
});$("#agent_chat_section").click((function(c){c.stopPropagation();this.panelEl.toggleClass("open")}).bind(this));$("body, #agent_chat_panel .close-trigger").click((function(){this.close()
}).bind(this));$.ajax({url:BASE_URL+"agent/agent-chat/get-online-agents.json",context:this,success:function(c){if(c.online_agents){Array.each(c.online_agents,function(d){this.addOnlineAgent(d)
},this)}}});this.chatsWrapper.click(function(c){c.stopPropagation()});var a=this;var b=function(c){c.stopPropagation();var d=$(this).data("agent-id");
a.newChatWindow([d])};this.onlineListEl.delegate("li","click",b);this.offlineListEl.delegate("li","click",b);this.agentTeamList.delegate("li","click",function(d){d.stopPropagation();
var c=$(this).data("member-ids");console.log(c);c=c.split(",");console.log(c);a.newChatWindow(c)});this.listTabs=new DeskPRO.UI.SimpleTabs({triggerElements:$("#agent_chat_panel_listviews > li"),context:$("#agent_chat_panel div.wrap:first")})
},close:function(){this.panelEl.removeClass("open");$("> section",this.chatsWrapper).removeClass("open")},newChatWindow:function(a){var b=DeskPRO.Agent.Widget.AgentChatWin_FindAgents(a);
if(!b){b=new DeskPRO.Agent.Widget.AgentChatWin({agentIds:a})}this.close();b.open()},newIncomingMessage:function(a){var b=DeskPRO.Agent.Widget.AgentChatWin_Find(a.conversation_id);
if(!b){b=new DeskPRO.Agent.Widget.AgentChatWin({convoId:a.conversation_id,agentIds:a.participant_ids})}b.showMessage(a.author_id,a.message);
b.open()},addOnlineAgent:function(d){if(DESKPRO_PERSON_ID&&d==DESKPRO_PERSON_ID){return}var b=$(".agent-"+d,this.offlineListEl);
if(!b.length){console.warn("No agent element for %i",d);return}if($(".agent-"+d,this.onlineListEl).length){return}var a=b.clone();
this.onlineListEl.append(a);b.hide();var c=parseInt(this.onlineCountEl.html());c++;this.onlineCountEl.html(c);$("li.no-agents",this.onlineListEl).hide()
},removeOnlineAgent:function(d){var a=$(".agent-"+d,this.onlineListEl);var c=$(".agent-"+d,this.offlineListEl);if(!a.length){return
}a.remove();c.show();var b=parseInt(this.onlineCountEl.html());b--;this.onlineCountEl.html(b);if(b<1){$("li.no-agents",this.onlineListEl).show()
}}});Orb.createNamespace("DeskPRO.Agent.WindowElement.Section");DeskPRO.Agent.WindowElement.Section.UserChat=new Orb.Class({Extends:DeskPRO.Agent.WindowElement.Section.AbstractSection,init:function(){this.buttonEl=$("#chat_section");
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
e.appendTo("body");DeskPRO_Window.handleSoundElements(e);$(".dismiss-trigger",e).click(function(){e.remove()});$(".accept-trigger",e).click(function(g){g.stopPropagation();
DeskPRO_Window.runPageRouteFromElement(this);e.remove()}).data("route","page:"+BASE_URL+"agent/chat/view/"+c);if(b){var f=$.tmpl("new_user_chat_alert_message",b);
$("div.messages",e).append(f).scrollTop(10000)}},showNewChatAlert:function(b,a){var d=$.tmpl("new_user_chat_alert");d.appendTo("body");
DeskPRO_Window.handleSoundElements(d);var c=$("audio",d).get(0);$(".dismiss-trigger",d).click(function(){if(c){c.pause()}d.remove()
});$(".accept-trigger",d).click(function(f){f.stopPropagation();DeskPRO_Window.runPageRouteFromElement(this);if(c){c.pause()
}d.remove()}).data("route","page:"+BASE_URL+"agent/chat/view/"+b);if(a){var e=$.tmpl("new_user_chat_alert_message",a);$("div.messages",d).append(e).scrollTop(10000)
}}});Orb.createNamespace("DeskPRO.Agent.WindowElement.Section");DeskPRO.Agent.WindowElement.Section.Ideas=new Orb.Class({Extends:DeskPRO.Agent.WindowElement.Section.AbstractSection,init:function(){this.buttonEl=$("#ideas_section");
this.setSectionElement($('<section id="ideas_outline"></section>'));$.ajax({url:BASE_URL+"agent/ideas/get-section-data.json",context:this,success:function(a){this._initSection(a)
}})},_initSection:function(b){this.setHasInitialLoaded();this.contentEl.html(b.section_html);var a=this;this.catTabs=new DeskPRO.UI.SimpleTabs({context:this.sectionEl,triggerElements:$("#ideas_outline_tabstrip li"),onTabSwitch:function(c){}});
this.contentEl.addClass("scroll-content").tinyscrollbar()}});