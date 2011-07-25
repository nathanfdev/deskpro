Orb.createNamespace("DeskPRO.Agent.PageHelper");DeskPRO.Agent.PageHelper.TicketActionsBar=new Class({page:null,wrapper:null,contentWrapper:null,tableEl:null,selectedActionData:null,ticketBar:null,barWrapper:null,initialize:function(b){this.layout=b.layout;
this.page=b;this.wrapper=this.page.wrapper;this.contentWrapper=this.page.contentWrapper;this.ticketBar=this.page.barWrapper;
this.initOverlay();this.page.changeManager.addEvent("changesCleared",(function(){$("table:first",this.page.contentWrapper).removeClass("preview-mode");
$("tr",this.page.contentWrapper).removeClass("with-line-3").removeClass("faded");$("tr.line-3",this.page.contentWrapper).hide().find("td > ul").html("");
this.page.actionsBarHelper._selectOp("none");this.toggleMacroApplyBtn("off")}).bind(this));var a=this;$("li.macros-apply").click(function(){a.saveActions()
});$(".macros-cancel").click(function(){a._removeTicketIdsToCurrentAction(a.getSelectedTicketIds());a.toggleMacroApplyBtn("off")
})},initOverlay:function(){var a=this;this.actionsWrap=$(".mass-actions:first",this.ticketBar);$("div.overlay-content:first",this.actionsWrap).css({width:$("#pane_list_content").width()+100,"max-height":$("#pane_list_content").height()});
this.actionsOverlay=new DeskPRO.UI.Overlay({contentElement:this.actionsWrap,triggerElement:$("li.actions",this.ticketBar),onBeforeOverlayOpened:function(){var d=$(".check-count span",a.ticketBar);
$(".check-count-overlay",a.actionsWrap).html(d.html())}});$("select.apply-macro-select",this.actionsWrap).change(function(){a.loadMacroActions()
});$(".save-trigger",this.actionsWrap).click((function(){this._loadActions(this.getSelectedTicketIds())}).bind(this));this.actionsEditor=new DeskPRO.Form.RuleBuilder($(".actions-tpl",this.actionsWrap));
this.actionsEditor.addEvent("newRow",function(d){$(".remove",d).click(function(){d.remove()})});var b=this.replySimpleTabs=new DeskPRO.UI.SimpleTabs({context:$(".ticket-reply",this.actionsWrap),triggerElements:$("li.tab-trigger",$(".ticket-reply",this.actionsWrap))});
var c=$(".actions-form .actions-terms",this.actionsWrap);$(".actions-form .add-term",this.actionsWrap).data("add-count",0).click(function(){var d=parseInt($(this).data("add-count"));
var e="actions["+d+"]";$(this).data("add-count",d+1);a.actionsEditor.addNewRow(c,e)})},loadMacroActions:function(){var b=parseInt($("select.apply-macro-select",this.actionsWrap).val());
if(!b){return}var a=$(".macro-selector .spinner",this.actionsWrap).show().empty();var c=new Spinner(a,{radii:[4,8],padding:0}).play();
$.ajax({cache:false,type:"POST",data:{macro_id:b},url:BASE_URL+"agent/ticket-search/ajax-get-macro-actions",context:this,dataType:"json",success:function(d){console.log(d);
var e=false;Object.each(d.macro_actions,function(j,f){var h=$(".actions-form .add-term",this.actionsWrap);var g=parseInt(h.data("add-count"));
var i="actions["+g+"]";h.data("add-count",g+1);if(j.type=="reply"){e=j.options.reply}else{this.actionsEditor.addNewRow($(".actions-terms",this.actionsWrap),i,{type:j.type,options:j.options})
}},this);if(e){$("textarea",this.actionsWrap).val(e)}},complete:function(){c.remove();a.empty()}})},getSelectedTicketIds:function(){var a=[];
$("input.ticket:checked",this.tableEl).each(function(){a.push(parseInt($(this).val()))});return a},setActiveTable:function(a){this.tableEl=$(a);
var b=this;$("> thead:first > tr > th.check-all > input.check-all-box:first",this.tableEl).click(function(c){c.stopPropagation();
if($(this).is(":checked")){b._selectOp("all")}else{b._selectOp("none")}});this.tableEl.delegate('input[type="checkbox"].ticket',"click",function(){b.handleTicketCheckClick($(this))
})},_getRowLines:function(b){if(b.is(".line-1")){var a=b.add(b.next()).add(b.next())}else{if(b.is(".line-2")){var a=b.add(b.prev()).add(b.next())
}else{var a=b.add(b.prev()).add(b.prev().prev())}}return a},handleTicketCheckClick:function(a){var c=$(".check-count span",this.ticketBar);
var b=parseInt(c.html());if(!b){b=0}if(a.is(":checked")){this._getRowLines(a.parent().parent()).addClass("on");b++;if(this.page.changeManager.hasChanges){this._addTicketIdsToCurrentAction(a.val())
}}else{this._getRowLines(a.parent().parent()).removeClass("on");b--;if(this.page.changeManager.hasChanges){this._removeTicketIdsToCurrentAction(a.val())
}}if(b<0){b=0}this.updateCount(b)},_selectOp:function(b){if(!this.tableEl){return}var a=this;if(b=="none"){$('input[type="checkbox"].ticket',this.tableEl).attr("checked",false);
$("tr.on",this.tableEl).removeClass("on")}else{if(b=="all"){$('tr:not(.locked) input[type="checkbox"].ticket',this.tableEl).attr("checked",true);
$("tr",this.tableEl).addClass("on")}else{if(b=="invert"){$('input[type="checkbox"].ticket',this.tableEl).each(function(){if($(this).is(":checked")&&$(this).parent().parent().is(":not(.locked)")){$(this).attr("checked",false);
a._getRowLines($(this).parent().parent()).removeClass("on")}else{$(this).attr("checked",true);a._getRowLines($(this).parent().parent()).addClass("on")
}})}}}this.updateCount($('input[type="checkbox"].ticket:checked',this.tableEl).length)},updateCount:function(a){a=parseInt(a);
if(a==0){this.layout.collapseFooter();$(".check-count span",this.ticketBar).html(0)}else{this.layout.expandFooter();$(".check-count span",this.ticketBar).html(a)
}},_addTicketIdsToCurrentAction:function(a){if(!$.isArray(a)){a=[a]}this._loadActions(a)},_removeTicketIdsToCurrentAction:function(a){if(!$.isArray(a)){a=[a]
}Array.each(a,function(b){this.page.changeManager.revertChangesForTicketId(b)},this)},_applyButtonCallback:null,createPropertyForTicket:function(e,f){var b=null;
var a=/^(.*?)\[(.*?)\]$/.exec(e);if(a!==null){e=a[1];b=a[2]}var c=this._getPropClass(e,b);if(!c){return false}var d=new c[0](this.page,f,c[1]);
return d},_applyButtonClicked:function(){if(this._applyButtonCallback){this._applyButtonCallback()}},_loadActions:function(e){console.debug("loading indicator TicketActionsBar._loadActions");
var b=$(".loading-off").hide();var a=$(".loading-on").show().empty();var d=new Spinner(a,{radii:[4,8],padding:0}).play();
var c=$(":input, select, textarea",$(".actions-terms",this.actionsWrap)).serializeArray();c.combine($(":input, select, textarea",$(".ticket-reply",this.actionsWrap)).serializeArray());
Array.each(e,function(f){c.push({name:"ticket_ids[]",value:f})});$.ajax({cache:false,type:"GET",data:c,url:BASE_URL+"agent/ticket-search/ajax-preview-actions",context:this,dataType:"json",success:function(f){this.actionsOverlay.closeOverlay();
d.remove();a.empty().hide();b.show();this.applyActions(f,e)}})},applyActions:function(b,c){var a=this.page.changeManager;
a.begin(c);Object.each(b.ticket_actions,function(e,d){Object.each(e,function(h,g){var f=this.createPropertyForTicket(g,d);
if(!f){return}if(h.value_display){h=h.value_display}a.addChange(f,h)},this)},this);a.applyChanges();this.toggleMacroApplyBtn("on");
this._applyButtonCallback=(function(){this.saveActions();this.toggleMacroApplyBtn("off")}).bind(this)},_getPropClass:function(c,a){var d=null;
var b=null;switch(c){case"department_id":case"category_id":case"product_id":case"priority_id":case"workflow_id":case"status":case"agent_id":case"agent_team_id":d=DeskPRO.Agent.TicketList.Property.StandardOption;
b={optionName:c};break;case"new_reply":d=DeskPRO.Agent.TicketList.Property.NewReply;break;case"add_labels":d=DeskPRO.Agent.TicketList.Property.Labels;
b={mode:"add"};break;case"remove_labels":d=DeskPRO.Agent.TicketList.Property.Labels;b={mode:"remove"};break;case"flag":d=DeskPRO.Agent.TicketList.Property.Flag;
break;case"ticket_field":d=DeskPRO.Agent.TicketList.Property.TicketField;b={fieldId:a};break}return[d,b]},toggleMacroApplyBtn:function(b,c){var a=$(".tab-bottom-tabs",this.ticketBar);
if(!b){if($(".macros",a).is(":visible")){b="on"}else{b="off"}}if(b=="off"){$(".macros",a).show();$(".macros-apply",a).hide();
$(".macros-cancel",a).hide()}else{$(".macros",a).hide();$(".macros-apply",a).show();$(".macros-cancel",a).show()}},saveActions:function(){var b=this.getSelectedTicketIds();
var a=$(":input, select, textarea",$(".actions-terms",this.actionsWrap)).serializeArray();a.combine($(":input, select, textarea",$(".ticket-reply",this.actionsWrap)).serializeArray());
Array.each(b,function(c){a.push({name:"ticket_ids[]",value:c})});this.page.changeManager.commitChanges();console.debug("loading indicator TicketActionsBar.saveActions");
$.ajax({cache:false,type:"POST",data:a,url:BASE_URL+"agent/ticket-search/ajax-save-actions",context:this,dataType:"json",success:function(){DeskPRO_Window.showStatusMessage("Actions were applied successfully")
}})}});Orb.createNamespace("DeskPRO.Agent.PageHelper");DeskPRO.Agent.PageHelper.NewUserOverlay=new Class({Implements:[Options,Events],options:{contentEl:"div.new-user-overlay",context:document,saveUrl:null,zIndex:1000000},contentEl:null,overlay:null,initialize:function(a){if(a){this.setOptions(a)
}this.contentEl=this.options.contentEl;if(typeOf(this.contentEl)=="string"){this.contentEl=$(this.contentEl,this.context)
}Orb.Compat.WebForms.placeholder($('input[name="person[first_name]"]',this.contentEl));Orb.Compat.WebForms.placeholder($('input[name="person[last_name]"]',this.contentEl))
},open:function(){this._initOverlay();this.overlay.openOverlay()},_hasInit:false,_initOverlay:function(){if(this._hasInit){return
}this._hasInit=true;this.overlay=new DeskPRO.UI.Overlay({contentElement:this.contentEl});$("button.save-trigger",this.contentEl).click(this._handleSave.bind(this))
},_handleSave:function(){var a=this.getFormElements();var c=a.serializeArray();var b={formData:c,contentEl:this.contentEl,overlay:this.overlay,cancel:false};
this.fireEvent("beforeSave",b);if(b.cancel){return}$.ajax({url:this.options.saveUrl,data:c,type:"POST",dataType:"json",success:this._handleSaveSuccess.bind(this)})
},_handleSaveSuccess:function(b){var a={contentEl:this.contentEl,overlay:this.overlay,data:b};if(b.isError){$(".error-message",this.contentEl).html(b.errorMessage).show();
return}this._clear();this.fireEvent("afterSave",a)},_clear:function(){$(".error-message",this.contentEl).hide()},getFormElements:function(){var a=$(":input",this.contentEl);
return a},destroy:function(){if(this.overlay){this.overlay.destroy()}else{this.contentEl.remove()}}});Orb.createNamespace("DeskPRO.Agent.PageHelper");
DeskPRO.Agent.PageHelper.ListColDrag=new Orb.Class({Implements:[Orb.Util.Events,Orb.Util.Options],initialize:function(b){this.options={table:null,onlyRowSel:null,onlyRowColOffset:0};
this.setOptions(b||{});this.moveIndicator=$('<div class="col-move"></div>').appendTo("body");this.table=$(this.options.table);
this.resetHeads();var a=this;this.heads.draggable({axis:"x",helper:function(d,e){var c=$('<div style="background-color:#EEF1F5;padding:3px;border: 1px solid #CAD0D7;" />').text($(this).text());
return c},start:function(d,e){d.stopPropagation();var c=$(this).index();$(this).closest("table").data("drag_col_index",c)
},stop:function(){a.moveIndicator.hide()},scope:"col-drag"});this.dropHeads.droppable({scope:"col-drag",over:function(f,g){var d=$(this).index();
var h=$(this).offset();var c=$(this).width();var e=h.left+c;a.moveIndicator.css({left:e,top:h.top-a.moveIndicator.height(),display:"block"})
},out:function(c,d){},drop:function(f,h){a.moveIndicator.hide();var i=a.table.data("drag_col_index");a.table.data("drag_col_index","null");
var c=$(this).index();if(c==i){return}if(a.options.onlyRowSel){var g=$(a.options.onlyRowSel,a.table)}else{var g=$("tr",a.table)
}g=g.filter(":not(.is-head)");var j="after";if(a.options.onlyRowColOffset){i-=a.options.onlyRowColOffset;c-=a.options.onlyRowColOffset;
if(c<0){c=0;j="before"}}var d=$(h.draggable);var e=$(this);d.detach().insertAfter(e);g.each(function(m,n){var k=$(n).find("td").eq(i);
var l=$(n).find("td").eq(c);if(j=="before"){k.detach().insertBefore(l)}else{k.detach().insertAfter(l)}});a.resetHeads();a.fireEvent("orderChanged",[a.heads,this])
}})},resetHeads:function(){var a=$("> thead:first > tr:first",this.table);a.addClass("is-head");$(".not-droppable").removeClass("not-droppable");
$(".not-draggable").addClass("not-droppable");this.heads=$("th:not(.not-draggable), td:not(.not-draggable)",a);this.heads.eq(0).prev().addClass("first-drop-target").removeClass("not-droppable");
this.dropHeads=$("th:not(.not-droppable), td:not(.not-droppable)",a)}});Orb.createNamespace("DeskPRO.Agent.PageHelper");DeskPRO.Agent.PageHelper.ListColResize=new Orb.Class({Implements:[Orb.Util.Events,Orb.Util.Options],initialize:function(b){this.options={table:null};
this.setOptions(b||{});this.table=$(this.options.table);var d=$("> thead:first > tr:first",this.table);var c=$("> td:not(.not-resizable), > th:not(.not-resizable)",d);
$("td, th",d).each(function(){$(this).css({width:$(this).width()})});this.table.css("table-layout","fixed");var a=this;c.each(function(){var f=$('<div class="col-resizer"></div>');
var e=$(this);e.prepend(f);f.mousedown(function(g){g.stopPropagation()});f.draggable({helper:function(){return $('<div class="col-resizer-helper"></div>')
},cursorAt:{left:5},axis:"x",start:function(g,h){g.stopPropagation()},stop:function(i,j){var k=e.offset();var g=j.offset;
var h=g.left-k.left;console.log("%i %i",e.width(),h);e.css("width",h);a.fireEvent("widthUpdated",[c,a])}})})}});Orb.createNamespace("DeskPRO.Agent.PageHelper");
DeskPRO.Agent.PageHelper.TicketDisplay=new Orb.Class({Implements:[Orb.Util.Options,Orb.Util.Events],initialize:function(b,a){this.options={wrapper:null,holders:".page-display-holders:first",inputHolders:".page-display-input:first",sectionProperties:".field-section-properties:first",sectionPropertiesContent:".field-section-properties-content:first",sectionPropertiesWrapTpl:".fields-wrap-properties",sectionBodyTabs:".field-section-bodytabs-tabs:first",sectionBodyTabContents:".field-section-bodytabs-tab-contents:first",sectionBodyTabsWrapTpl:".fields-wrap-bodytabs",sectionBodyTabsTabTpl:".fields-new-bodytabs-tab",sectionBodyTabsTabContentTpl:".fields-new-bodytabs-content",sectionMiddleTabs:".field-section-middletabs-tabs:first",sectionMiddleTabContents:".field-section-middletabs-tab-contents:first",sectionMiddleTabsWrapTpl:".fields-wrap-middletabs",sectionMiddleTabsTabTpl:".fields-new-middletabs-tab",sectionMiddleTabsTabContentTpl:".fields-new-middletabs-content",fieldWrapSelector:".display-item",fieldTabSelector:"li.field-tab",fieldTabContentSelector:".field-tab-content"};
this.setOptions(a);this.wrapper=$(this.options.wrapper);this.holders=$(this.options.holders,this.wrapper);this.inputHolders=$(this.options.inputHolders,this.wrapper);
this.sectionProperties=$(this.options.sectionProperties,this.wrapper);this.sectionPropertiesContent=$(this.options.sectionPropertiesContent,this.wrapper);
this.sectionPropertiesWrapTpl=$(this.options.sectionPropertiesWrapTpl,this.wrapper).get(0).innerHTML;this.sectionBodyTabs=$(this.options.sectionBodyTabs,this.wrapper);
this.sectionBodyTabContents=$(this.options.sectionBodyTabContents,this.wrapper);this.sectionBodyTabsWrapTpl=$(this.options.sectionBodyTabsWrapTpl,this.wrapper).get(0).innerHTML;
this.sectionBodyTabsTabTpl=$(this.options.sectionBodyTabsTabTpl,this.wrapper).get(0).innerHTML;this.sectionBodyTabsTabContentTpl=$(this.options.sectionBodyTabsTabContentTpl,this.wrapper).get(0).innerHTML;
this.sectionMiddleTabs=$(this.options.sectionMiddleTabs,this.wrapper);this.sectionMiddleTabContents=$(this.options.sectionMiddleTabContents,this.wrapper);
this.sectionMiddleTabsWrapTpl=$(this.options.sectionMiddleTabsWrapTpl,this.wrapper).get(0).innerHTML;this.sectionMiddleTabsTabTpl=$(this.options.sectionMiddleTabsTabTpl,this.wrapper).get(0).innerHTML;
this.sectionMiddleTabsTabContentTpl=$(this.options.sectionMiddleTabsTabContentTpl,this.wrapper).get(0).innerHTML;this.departmentId=null;
this.page=b;this.page.changeManager.addEvent("updateResult",this.handleChangeUpdateResult.bind(this));this._initHolders();
this.setDepartment(parseInt($("input.department_id",this.wrapper).val()||0))},handleChangeUpdateResult:function(a){if(a.holders){this.replaceHolders(a.holders);
this.setDepartment(parseInt($("input.department_id",this.wrapper).val()||0))}},replaceHolders:function(a){this.holders.remove();
this.holders=$(a).hide();this.wrapper.append(this.holders);this._initHolders();return this.holders},_initHolders:function(){var a=["category_id","product_id","priority_id","workflow_id"];
for(var b=0;b<a.length;b++){var d=a[b];var c=$("> ."+d+" .menu-trigger",this.holders);this.page.initTicketOptionsMenuForProp(d,c)
}},clearAll:function(){$(this.options.fieldTabSelector,this.sectionBodyTabs).remove();$(this.options.fieldTabContentSelector,this.sectionBodyTabContents).remove();
$(this.options.fieldWrapSelector,this.sectionPropertiesContent).remove();this.sectionProperties.hide()},setDepartment:function(b){b=parseInt(b);
console.log("Setting %i",b);this.clearAll();if(b==this.departmentId){return}this.departmentId=b;if(!window.DESKPRO_TICKET_DISPLAY||!window.DESKPRO_TICKET_DISPLAY[b]){return
}var a=window.DESKPRO_TICKET_DISPLAY[b];console.log("depItems %o",a);Array.each(a,function(e){switch(e.section){case"default":var h=this.getItemHolderEls(e);
if(!h){return}if(h.itemHolder.data("custom-field-handler")){e.custom_field_handler=h.itemHolder.data("custom-field-handler")
}var d=$(this.sectionPropertiesWrapTpl);h.itemTitle.detach().appendTo($(".display-title",d));h.itemContent.detach().appendTo($(".display-content",d));
d.appendTo(this.sectionPropertiesContent);e.sectionEl=this.sectionPropertiesContent;this._initWrapper(e,d);h.itemHolder.remove();
break;case"bodytabs":if(!e.items||!e.items.length){e.items=[]}var g="bodytabfieldtab_"+$(this.options.fieldTabSelector,this.sectionBodyTabs).length+1;
var f=$(this.sectionBodyTabsTabTpl.replace(/\{title\}/g,e.title).replace(/\{id\}/g,g));var c=$(this.sectionBodyTabsTabContentTpl.replace(/\{id\}/g,g));
f.appendTo(this.sectionBodyTabs);c.appendTo(this.sectionBodyTabContents);Array.each(e.items,function(j){j.section="bodytabs";
var k=this.getItemHolderEls(j);if(!k){return}if(k.itemHolder.data("custom-field-handler")){e.custom_field_handler=k.itemHolder.data("custom-field-handler")
}var i=$(this.sectionBodyTabsWrapTpl);k.itemTitle.detach().appendTo($(".display-title",i));k.itemContent.detach().appendTo($(".display-content",i));
i.appendTo(c);j.sectionEl=c;this._initWrapper(j,i);k.itemHolder.remove()},this);break;case"middletabs":if(!e.items||!e.items.length){e.items=[]
}var g="middletabfieldtab_"+$(this.options.fieldTabSelector,this.sectionMiddleTabs).length+1;var f=$(this.sectionMiddleTabsTabTpl.replace(/\{title\}/g,e.title).replace(/\{id\}/g,g));
var c=$(this.sectionMiddleTabsTabContentTpl.replace(/\{id\}/g,g));f.appendTo(this.sectionMiddleTabs);c.appendTo(this.sectionMiddleTabContents);
Array.each(e.items,function(j){j.section="middletabs";var k=this.getItemHolderEls(j);if(!k){return}if(k.itemHolder.data("custom-field-handler")){e.custom_field_handler=k.itemHolder.data("custom-field-handler")
}var i=$(this.sectionMiddleTabsWrapTpl);k.itemTitle.detach().appendTo($(".display-title",i));k.itemContent.detach().appendTo($(".display-content",i));
i.appendTo(c);j.sectionEl=c;this._initWrapper(j,i);k.itemHolder.remove()},this);break}},this);this.updateSectionDisplay()
},_initWrapper:function(j,i){if(j.item_type!="ticket_field"){return}var h=$(".edit-trigger",i);if(!h.length){return}var g=this.inputHolders;
var f=this.getItemId(j);var d=$("> ."+f,g);var b=$(".content:first",i);var c=$(".fields-edit-overlay:first",this.wrapper);
var a=this.page;var e=function(){var r=null;var q=null;if(i.is(".edit-open")){return}i.addClass("edit-open");console.log("showEditField: %s",f);
var r=$(c.get(0).innerHTML.replace("{id}",f));r.appendTo("body");var q=$("> .field-input",d);q.detach().appendTo($(".content",r));
var o=function(){i.removeClass("edit-open");r.slideUp(function(){q.detach().appendTo(d);r.remove()})};var m=function(){b.empty();
var t=new Spinner(b,{radii:[4,8],padding:0}).play();o();var s=$(":input, select, textarea",r).serializeArray();$.ajax({url:BASE_URL+"agent/tickets/"+a.getMetaData("ticket_id")+"/ajax-save-custom-fields",type:"POST",context:this,data:s,dataType:"html",success:function(u){var v=$("<div>"+u+"</div>");
var w=$("div.page-display-holders > ."+f+":first",v);var x=$("> .content:first",w);b.empty().append(x)}})};$(".close-trigger",r).click(o);
$(".save-trigger",r).click(m);var l=i;var k="left top";var n="left top";var p=i.width()-8;if(p<150){p=150}else{if(p>300){p=250;
l=$(".title:first",i);k="left top";n="right top"}}r.css({position:"absolute",width:p,"z-index":10000});r.position({my:k,at:n,of:l,collision:"fit"});
r.slideDown()};i.dblclick(e)},runRules:function(){var c=window.DESKPRO_TICKET_DISPLAY[department_id];if(!c){return}var b={getCategoryId:function(){if(this.categoryId){return this.categoryId
}this.categoryId=parseInt($("input.category_id",this.wrapper).val()||0);return this.categoryId},getProductId:function(){if(this.productId){return this.productId
}this.productId=parseInt($("input.product_id",this.wrapper).val()||0);return this.productId},getPriorityId:function(){if(this.priorityId){return this.priorityId
}this.priorityId=parseInt($("input.priority_id",this.wrapper).val()||0);return this.priorityId},getWorkflowId:function(){if(this.workflowId){return this.workflowId
}this.workflowId=parseInt($("input.workflow_id",this.wrapper).val()||0);return this.workflowId}};var a=[];Array.each(items,function(d){switch(d.section){case"default":var e=this.runCheckForItem(d);
if(e){a.push(e)}break;case"bodytabs":Array.each(d.items,function(f){var g=this.runCheckForItem(f);if(g){a.push(g)}},this);
break}},this);Array.each(a,function(d){if(d[2]=="visible"){d[1].show()}else{d[1].hide()}});this.updateSectionDisplay()},updateSectionDisplay:function(){if($(this.options.fieldWrapSelector+":first",this.sectionPropertiesContent).length){this.sectionProperties.show()
}else{this.sectionProperties.hide()}var a=this.sectionBodyTabContents;var b=this.options.fieldWrapSelector+":first";$("li.field-tab",this.sectionBodyTabs).each(function(){var d=$(this).data("field-tab-id");
var c=$("> ."+d,a);if($(b,c).length){$(this).show()}else{$(this).hide()}})},runCheckForItem:function(a){var c=this.getItemId(a);
if(a.initial_display=="visible"){var b=true}else{var b=false}if(a.check&&a.check(ticketReader)){b=!b}return[c,$("> ."+c,a.sectionEl),b]
},getItemHolderEls:function(c){var f=this.getItemId(c);var b=$("> ."+f+":first",this.holders);if(!b||!b.length){return}var a=$("> .title:first",b);
var d=$("> .content:first",b);function e(i,k,g){var j=$("."+k+".menu:first",this.wrapper);var h=$("li",j).show().removeClass("off");
if(!i||!i.length){return}$("li",j).each(function(){var o=$(this).data("option-value");if(i.indexOf(o+"")==-1&&i.indexOf(o)==-1){$("li."+g+"-"+o,j).hide().addClass("off")
}});var m=$("li:not(.off)",j);var n=m.first();if(n.is(".sep")){n.hide().addClass("off")}var l=m.last();if(l.is(".sep")){l.hide().addClass("off")
}}switch(c.item_type){case"ticket_category":e(c.ticket_categories,"category_id","cat");break;case"ticket_workflow":e(c.ticket_workflows,"workflow_id","work");
break;case"ticket_priority":e(c.ticket_priorities,"priority_id","pri");break;case"ticket_product":e(c.ticket_products,"product_id","prod");
break}return{itemHolder:b,itemTitle:a,itemContent:d}},getItemId:function(a){var b=a.item_type;if(a.item_id){b+="_"+a.item_id
}return b}});Orb.createNamespace("DeskPRO.Agent.PageHelper");DeskPRO.Agent.PageHelper.ListSearchForm=new Orb.Class({Implements:[Orb.Util.Events,Orb.Util.Options],initialize:function(b,a){this.page=b;
this.options={form:null,context:null,searchData:null};this.setOptions(a);this.form=this.options.form;this.topSection=this.options.context;
this._initSearchOptions()},_initSearchOptions:function(){var c=$(".summary .edit",this.topSection);c.click(this.showSearchForm.bind(this));
var b=this.form;var a=this;b.submit(function(e){e.preventDefault();var d=b.attr("action");var f=b.serializeArray();a.fireEvent("searchSubmit",[d,f])
})},showSearchForm:function(){var d=$(".search-form",this.topSection);var e=$(".search-builder-tpl",this.topSection);var b=new DeskPRO.Form.RuleBuilder(e);
$(".add-term",d).data("add-count",0).click(function(){var f=parseInt($(this).data("add-count"));var g="terms["+f+"]";$(this).data("add-count",f+1);
b.addNewRow($(".search-terms",d),g)});var c=this.searchData;if(c&&c.length){var a=c.get(0).innerHTML;a=$.parseJSON(a);if(a.terms){Array.each(a.terms,function(h,f){var g="terms[initial_"+f+"]";
b.addNewRow($(".search-terms",d),g,{type:h.type,op:h.op,options:h.options})})}c.remove()}$(".summary",this.topSection).slideUp();
$(".form-panel",this.topSection).slideDown()}});Orb.createNamespace("DeskPRO.Agent.PageFragment.Page");DeskPRO.Agent.PageFragment.Page.BasicTicket=new Class({Extends:DeskPRO.Agent.PageFragment.Basic,TYPENAME:"basicticket",wrapper:null,destroyEls:[],destroyMenus:[],destroyOverlays:[],changeManager:null,valueForm:null,layout:null,initPage:function(b){this.wrapper=b;
this.contentWrapper=this.wrapper.children(".layout-content").attr("id",Orb.getUniqueId());this.barWrapper=$(".bar-wrapper",this.wrapper);
this.valueForm=$("form.value-form:first",this.contentWrapper);this.changeManager=new DeskPRO.Agent.Ticket.ChangeManager(this);
window.TICKET=this;if(!this.meta.isDeleted){this._initTicketOptionsMenus();this._initCustomFieldsEditor();this._initReplyBar();
this._initAttachments();this._initParticipants()}DeskPRO_Window.getMessageBroker().addMessageListener("window.innerLayout.resize",(function(){this._handleResize()
}).bind(this));var a=this;$("div.ticket-messages > ul > li").each(function(){a._initMessage($(this))});$("input.date-field",this.contentWrapper).datepicker({dateFormat:"M d, yy"});
this.ticketDisplay=new DeskPRO.Agent.PageHelper.TicketDisplay(this,{wrapper:b});this.initFeaturesOnCollection(this.wrapper,{routes:[],times:[".timeago"]})
},_handleResize:function(){},destroyPage:function(){for(var a=0;a<this.destroyEls.length;a++){$(this.destroyEls[a]).remove()
}for(var a=0;a<this.destroyMenus.length;a++){this.destroyMenus[a].destroy()}for(var a=0;a<this.destroyOverlays.length;a++){this.destroyOverlays[a].destroy()
}},_initMessage:function(b){var a=$("ul.attachment-list li.is-image a",b);a.colorbox({title:function(){var c=$(this).attr("href");
return'<a href="'+c+'" target="_blank">Open In New Window</a>'},width:"50%",height:"50%",initialWidth:"200",initialHeight:"150",scalePhotos:true,photo:true,opacity:0.5,transition:"none"})
},incCount:function(c){var b=$("."+c+"-count",this.wrapper);var a=b.data("count")+1;b.data("count",a).html("("+a+")")},setCount:function(c,a){var b=$("."+c+"-count",this.wrapper);
b.data("count",a).html("("+a+")")},propertyManagers:{},getPropertyManager:function(b,c){if(this.propertyManagers[b]){return this.propertyManagers[b]
}var a=null;switch(b){case"department_id":case"category_id":case"product_id":case"workflow_id":case"priority_id":case"agent_id":case"agent_team_id":a=new DeskPRO.Agent.Ticket.Property.StandardOption(this,{optionName:b});
break;case"status":a=new DeskPRO.Agent.Ticket.Property.Status(this,{optionName:"status"});break;case"add_labels":a=new DeskPRO.Agent.Ticket.Property.Labels(this,{mode:"add"});
break;case"remove_labels":a=new DeskPRO.Agent.Ticket.Property.Labels(this,{mode:"remove"});break;case"flag":a=new DeskPRO.Agent.Ticket.Property.Flag(this);
break;case"new_reply":a=new DeskPRO.Agent.Ticket.Property.NewReply(this);break;case"ticket_field":a=new DeskPRO.Agent.Ticket.Property.TicketField(this,{fieldId:c});
break}this.propertyManagers[b]=a;return a},_initParticipants:function(){$(".agent-participants-edit",this.wrapper).click(this.showAgentParticipants.bind(this));
$(".user-participants-edit",this.wrapper).click(this.showUserParticipants.bind(this));this._initCcArea()},showAgentParticipants:function(b){if(!this.agentPartsSelector){var a=this;
var c=[];$("ul.agent-participants-list > li",this.wrapper).each(function(){c.push($(this).data("person-id"))});this.agentPartsSelector=new DeskPRO.Agent.Widget.AgentSelector({agentList:$("#agent_selector_list"),multipleChoice:true,startWith:c,onSelectionChanged:function(){a.updateAgentParticipants()
}})}this.agentPartsSelector.open(b)},updateAgentParticipants:function(){var a=this.agentPartsSelector.getSelection();var b=[];
Array.each(a,function(c){b.push({name:"person_ids[]",value:c})});$.ajax({url:BASE_URL+"agent/ticket/"+this.meta.ticket_id+"/save-agent-parts",data:b,dataType:"html",type:"POST",context:this,success:function(c){$("ul.agent-participants-list",this.wrapper).empty().html(c);
this.reloadCcReplyTab()}})},showUserParticipants:function(b){if(!this.userFind){var a=this;this.userFind=new DeskPRO.Agent.Widget.FindPerson({onChoosePerson:function(c){a.addUserPart(c.personId)
}})}this.userFind.open(b)},addUserPart:function(b){var a=[b];$("ul.user-participants-list > li",this.wrapper).each(function(){a.push($(this).data("person-id"))
});var c=[];Array.each(a,function(d){c.push({name:"person_ids[]",value:d})});$.ajax({url:BASE_URL+"agent/ticket/"+this.meta.ticket_id+"/save-user-parts",data:c,dataType:"html",type:"POST",context:this,success:function(d){$("ul.user-participants-list",this.wrapper).empty().html(d);
this.reloadCcReplyTab()}})},reloadCcReplyTab:function(){$.ajax({url:BASE_URL+"agent/ticket/"+this.meta.ticket_id+"/cc-reply-tab",dataType:"html",type:"GET",context:this,success:function(a){$(".cc-area",this.wrapper).empty().html(a);
this._initCcArea()}})},_initCcArea:function(){var e=$(".cc-area",this.wrapper);var c=$(".cc-new-parts",e);$("li",e).click(function(f){if(!$(f.target).is("input")){$("input",this).click()
}});var a=$(".new-part input",c);var d=$(".new-part button",c);$("section.cc-section .with-scrollbar",e).tinyscrollbar();
var b=$("section.cc-new-parts .with-scrollbar",e);d.click(function(){var g=a.val();var f=$("<li>"+g+'<input type="hidden" name="new_parts[]" value="'+g+'" />&nbsp;&nbsp;<span class="remove-trigger" style="cursor: pointer;">x</span></li>');
$(".remove-trigger",f).click(function(){f.remove()});$("ul",c).append(f);b.tinyscrollbar()})},_initAttachments:function(){var a=this;
var b=$(".file-list",this.barWrapper);$("input",b[0]).live("click",function(){var d=$(this);var c=d.parent();if(d.is(":checked")){c.removeClass("unchecked")
}else{c.addClass("unchecked")}});$("form.reply-form",this.barWrapper).fileUploadUI({url:this.getMetaData("uploadAttachUrl"),dropZone:$("div.reply",this.barWrapper),dropZoneEnlarge:function(){a.replySimpleTabs.activateTab($(".attachments.tab-trigger",a.ticketReplyTabs));
a.barWrapper.addClass("upload-drop-over")},dropZoneReduce:function(){a.barWrapper.removeClass("upload-drop-over")},formData:function(){return[]
},cancelSelector:".cancel-trigger",uploadTable:$(".file-list",this.barWrapper),downloadTable:$(".file-list",this.barWrapper),initProgressBar:function(){return null
},buildUploadRow:function(e,c){var d=e[c];return $('<li class="uploading">'+d.name+' <span class="cancel-trigger">Cancel</span></li>')
},buildDownloadRow:function(c){return $('<li class="uploaded"><input type="checkbox" checked="checked" name="attach[]" value="'+c.blob_id+'" /> <a href="'+c.download_url+'" target="_blank">'+c.filename+'</a> <span class="size">('+c.filesize_readable+")</span></li>")
}})},ticketOptionsMenus:{},ticketOptionsMenuEls:{},_initTicketOptionsMenus:function(){var c=["department_id","category_id","product_id","priority_id","workflow_id","status","agent_id","agent_team_id"];
var a=this;for(var e=0;e<c.length;e++){var d=c[e];var g=$(".menu."+d+":first",this.wrapper);var h=new DeskPRO.UI.Menu({menuElement:g,onItemClicked:function(i){a._handleTicketOptionClick(i)
}});this.ticketOptionsMenus[d]=h;this.ticketOptionsMenuEls[d]=g;this.destroyMenus.push(h)}var b=["department_id","status","agent_id","agent_team_id"];
for(var e=0;e<c.length;e++){var d=c[e];var f=$(".menu-trigger."+d+":first",this.wrapper);this.initTicketOptionsMenuForProp(d,f)
}},initTicketOptionsMenuForProp:function(b,a){var c=this.ticketOptionsMenus[b];if(!c){console.log("No menu for %s",b);return
}c.setupTriggerElement(a)},_handleTicketOptionClick:function(c){var a=$(c.itemEl);if(!a.data("option-name")){a=a.parent()
}if(!a.data("option-name")){a=a.parent()}if(!a.data("option-name")){a=a.parent()}if(!a.data("option-name")){a=a.parent()}var b=a.data("option-name");
var d=$(c.itemEl).data("option-id");if(!d){d=$(c.itemEl).data("option-value")}var e=this.getPropertyManager(b);this.changeManager.setInstantChange(e,d)
},custom_fields_display:null,custom_fields_edit:null,_initCustomFieldsEditor:function(){$(".ticket-custom-fields-edit-btn",this.wrapper).click((function(){this.showCustomFieldEditor()
}).bind(this));this.custom_fields_display=$(".ticket-custom-fields:not(.edit)",this.wrapper);this.custom_fields_edit=$(".ticket-custom-fields.edit",this.wrapper);
this.custom_fields_edit.detach().appendTo(this.custom_fields_display.parent().parent().parent().parent());$(".close-trigger",this.custom_fields_edit).click((function(){this.closeCustomFieldEditor()
}).bind(this));var a=this;$(".save-trigger",this.custom_fields_edit).click((function(){var b=$(":input",a.custom_fields_edit);
this._saveCustomFields(b)}).bind(this))},showCustomFieldEditor:function(){var b=this.custom_fields_display.position();var a=this.custom_fields_display.width();
if(a>690){b.left+=a-690;a=690}this.custom_fields_edit.css({position:"absolute",top:b.top,left:b.left,width:a});this.custom_fields_edit.slideDown()
},closeCustomFieldEditor:function(){this.custom_fields_edit.slideUp()},_saveCustomFields:function(a){console.warn("This method shold be overriden in a subclass!")
},ticketBar:null,ticketReply:null,ticketReplyTabs:null,ticketActionsMenu:null,ticketMacrosMenu:null,_initReplyBar:function(){this.ticketBar=$(".tab-bottom-open:first",this.barWrapper);
this.ticketReply=$("div.tab-bottom-open:first",this.barWrapper);this.ticketReplyTabs=$(".tab-bottom-tabs",this.barWrapper);
var a=this;$(".send-reply button",this.barWrapper).click(function(d){d.preventDefault();a._sendReply()});var b=this.replySimpleTabs=new DeskPRO.UI.SimpleTabs({context:this.ticketReply,triggerElements:$("li.tab-trigger",this.ticketReplyTabs)});
this.ticketActionsMenu=new DeskPRO.UI.Menu({triggerElement:$(".bar-actions li.actions",this.ticketBar),menuElement:$("ul.ticket-info-edit-menu:first",this.contentWrapper),onItemClicked:this._handleActionsMenuClick.bind(this)});
this.destroyMenus.push(this.ticketActionsMenu);this.ticketMacrosMenu=new DeskPRO.UI.Menu({triggerElement:$(".tab-bottom-tabs .macros",this.ticketBar),menuElement:$("ul.ticket-macros-menu:first",this.contentWrapper),onItemClicked:this._handleMacroClick.bind(this)});
this.destroyMenus.push(this.ticketMacrosMenu);$(".tab-bottom-tabs .macros-apply",this.ticketBar).click((function(){var d=[];
if(this._currentMacroId){d.push({name:"macro_id",value:this._currentMacroId})}this.changeManager.saveChanges(d);this.toggleMacroApplyBtn("off");
this._currentMacroId=null}).bind(this));$(".tab-bottom-tabs .macros-cancel",this.ticketBar).click((function(){this.changeManager.revertChanges();
this.toggleMacroApplyBtn("off");this._currentMacroId=null}).bind(this));var c=this.actionMenu=new DeskPRO.UI.Menu({triggerElement:$("span.trigger.agent_id",this.ticketReply),menuElement:$(".reply-agent_id-menu",this.ticketReply),onItemClicked:(function(e){var f=$(e.itemEl).data("option-value");
var d=DeskPRO_Window.getDisplayName("agent",f);$("span.prop-val.agent_id",this.ticketReply).html(d);$('input[name="options[agent_id]"]',this.ticketReply).val(f)
}).bind(this)});var c=this.actionMenu=new DeskPRO.UI.Menu({triggerElement:$("span.trigger.agent_team_id",this.ticketReply),menuElement:$(".reply-agent_team_id-menu",this.ticketReply),onItemClicked:(function(e){var f=$(e.itemEl).data("option-value");
var d=DeskPRO_Window.getDisplayName("agent_team",f);$("span.prop-val.agent_team_id",this.ticketReply).html(d);$('input[name="options[agent_team_id]"]',this.ticketReply).val(f)
}).bind(this)});var c=this.actionMenu=new DeskPRO.UI.Menu({triggerElement:$("span.trigger.status",this.ticketReply),menuElement:$(".reply-status-menu",this.ticketReply),onItemClicked:(function(e){var f=$(e.itemEl).data("option-value");
var d=DeskPRO_Window.getDisplayName("status",f);$("span.prop-val.status",this.ticketReply).html(f);$('input[name="options[status]"]',this.ticketReply).val(f)
}).bind(this)})},_handleActionsMenuClick:function(d){var e=$(d.itemEl).data("option-id");switch(e){case"delete":$.ajax({url:this.getMetaData("deleteTicketUrl"),type:"GET",data:{"ticket_ids[]":this.getMetaData("ticket_id")},context:this,dataType:"json",success:function(f){DeskPRO_Window.getMessageBroker().sendMessage("tickets.deleted",f.deleted_tickets)
}});break;case"print":var b=700;var a=550;var c=window.open(this.getMetaData("printTicketUrl"),"print_ticket_win_"+this.getMetaData("ticket_id"),"width="+b+",height="+a+",locationbar=false,directories=false,status=false,copyhistory=false");
break}},_currentMacroId:null,_handleMacroClick:function(b){if($(b.itemEl).data("no-macro")){var a=new DeskPRO.UI.Overlay({contentMethod:"iframe",iframeUrl:BASE_URL+"agent/settings/ticket-macros/new"});
a.openOverlay();return}this._currentMacroId=$(b.itemEl).data("macro-id");$.ajax({url:this.getMetaData("getMacroUrl").replace("$macro_id",this._currentMacroId),type:"GET",context:this,dataType:"json",success:function(c){this._performMacro(c)
}})},_performMacro:function(a){Object.each(a,function(d,c){var f=null;var b=/^(.*?)\[(.*?)\]$/.exec(c);if(b!==null){c=b[1];
f=b[2]}var e=this.getPropertyManager(c,f);if(e){if(typeOf(d)=="object"&&d.value_display){d=d.value_display}this.changeManager.addChange(e,d)
}else{console.warn("Unknown property `%s`. Actions: %o",c,a)}},this);this.changeManager.applyChanges();this.toggleMacroApplyBtn("on")
},toggleMacroApplyBtn:function(d){var b=$(".bar-actions",this.ticketBar);if(!d){if($("li.macros",b).is(":visible")){d="on"
}else{d="off"}}var a=$("li:not(.macro-on)",b);var c=$("li.macro-on",b);if(d=="on"){a.hide();c.show()}else{a.show();c.hide()
}},isSendingReply:false,_sendReply:function(){this._handleSendReply($(":input, textarea, select",this.ticketReply))},_handleSendReply:function(a){console.warn("This method should be overriden in a subclass!")
},_handleSendReplySuccess:function(b){var c=null;if(b.close_tab){var a=this;c=function(){DeskPRO_Window.removePage(a)}}this.displayNewMessage(b.message_html,c);
this.newReplyNewProps(b);this.afterNewReply()},newReplyNewProps:function(a){var b=null;b=this.getPropertyManager("agent_id");
b.setValue(a.agent_id);b=this.getPropertyManager("agent_team_id");b.setValue(a.agent_team_id);b=this.getPropertyManager("status");
b.setValue(a.status)},afterNewReply:function(a){if($(".attachments-area ul.file-list li",this.ticketReply).length){this.unloadTicketTab("attachments")
}this.unloadTicketTab("notes");this.updateCounts();this.resetReply()},updateCounts:function(){var a=$(".full-container-tabbed-tabs",this.wrapper);
$.ajax({url:this.getMetaData("getUpdatedCountsUrl"),type:"GET",context:this,dataType:"json",success:function(b){Object.each(b,function(d,c){var e=".ticket-"+c+"-count";
$(e,a).html("("+d+")")})}})},resetReply:function(){$('textarea[name="message"]',this.ticketReply).val("");$(".attachments-area ul.file-list",this.ticketReply).html("");
this.replySimpleTabs.activateTab($(".reply-reply.tab-trigger",this.ticketReplyTabs));if(this.meta.agentSignature){$('textarea[name="message"]',this.ticketReply).val("\n\n--\n"+this.meta.agentSignature)
}}});Orb.createNamespace("DeskPRO.Agent.PageFragment.Page");DeskPRO.Agent.PageFragment.Page.Ticket=new Class({Extends:DeskPRO.Agent.PageFragment.Page.BasicTicket,TYPENAME:"ticket",popout:null,popout_overview:null,isMouseOverPopout:false,hasInitPopout:false,popoutPage:null,initPage:function(b){this.parent(b);
var a=this.contentWrapper;a.tinyscrollbar();$("div.scroll-content:first, div.scroll-viewport:first",this.contentWrapper).resize(function(){a.tinyscrollbar_update()
});this.initRoutesOnCollection($(".with-route",this.wrapper));if(!this.meta.isDeleted){this._initTicketActionsMenu();this._initMessageActionsMenu();
this._initFlagMenu();this._initLabels()}else{$("button.undelete-trigger",this.wrapper).click(this.doTicketUndelete.bind(this))
}this._initPopout();this._initTicketTabs();this._initTicketNotes();this.resetReply();DeskPRO_Window.getMessageBroker().sendMessage("ui.ticket.opened",{ticketId:this.getMetaData("ticket_id")});
DeskPRO_Window.getMessageBroker().sendMessage("ui.tab.opened",{type:"tickets",id:this.getMetaData("ticket_id")});DeskPRO_Window.getMessageBroker().addMessageListener("tickets.deleted",(function(c){if(c.indexOf(this.getMetaData("ticket_id"))!==-1){DeskPRO_Window.removePage(this)
}}).bind(this),this.pageUid);DeskPRO_Window.getMessageBroker().addMessageListener("tickets.check."+this.getMetaData("ticket_id"),this.handleTicketCheck.bind(this),this.pageUid);
DeskPRO_Window.getMessageBroker().addMessageListener("tickets.new-messages."+this.getMetaData("ticket_id"),this.getNewTicketMessages.bind(this),this.pageUid);
Array.each(this.getMetaData("fieldHandlers",[]),function(c){if(!c){return}var d=c.classname;var e=c.wrap_id;var c=new c($("#"+e),this);
c.initPage()},this);var b=$(".container-tabbed-wrap.ticket-participants:first",this.wrapper);b.css("min-width",b.width());
this.addEvent("shortcutFocusReply",(function(){$("div.scroll-content:first, div.scroll-viewport:first",this.contentWrapper).scrollTop(100000);
$('textarea[name="message"]',this.ticketReply).focus()}).bind(this))},destroyPage:function(){this.parent();if(this.popoutPage){this.popoutPage.destroyPage()
}DeskPRO_Window.getMessageBroker().sendMessage("ui.ticket.closed",{ticketId:this.getMetaData("ticket_id")});DeskPRO_Window.getMessageBroker().removeTaggedListeners(this.pageUid)
},displayNewMessage:function(b,d){var c=$(b).hide();var a=this;d=d||function(){};c.appendTo($(".ticket-messages .messages-wrap",this.contentWrapper)).slideDown("fast",d);
this._initMessage(c);this.incCount("ticket-messages")},activate:function(){if(this.popoutPinIcon&&this.popoutPinIcon.is(".on")){this.popout.fadeIn(200)
}},deactivate:function(){if(this.popoutPinIcon&&this.popoutPinIcon.is(".on")){this.popout.fadeOut(200)}},handleTicketCheck:function(a){if(!a.isLocked){$("div.lock-bar:first",this.contentWrapper).hide()
}},newnoteWrapper:null,_initTicketNotes:function(){this.newnoteWrapper=$(".new-note:first",this.contentWrapper);$("button",this.newnoteWrapper).click(this.saveNewNote.bind(this))
},saveNewNote:function(){var b=$(".loading-on",this.newnoteWrapper).show();var a=$(".loading-off",this.newnoteWrapper).hide();
var c=[];c.push({name:"message",value:$("textarea",this.newnoteWrapper).val()});$.ajax({url:BASE_URL+"agent/tickets/"+this.getMetaData("ticket_id")+"/ajax-save-note",type:"POST",context:this,data:c,dataType:"html",success:function(d){b.hide();
a.show();$("textarea",this.newnoteWrapper).val("");var e=$(d);this.newnoteWrapper.before(e);this._initMessage(e);this.incCount("ticket-notes");
this.displayNewMessage(d)}})},labelsList:null,_initLabels:function(){this.labelsList=$(".ticket-tags ul",this.contentWrapper);
this.labelsTagit=this.labelsList.tagit({availableTags:this.getMetaData("labelsAutocompleteUrl"),enableBackspace:false,fieldName:"labels",onchange:this.saveLabels.bind(this)})
},_saveLabelsTimeout:null,saveLabels:function(){if(this.changeManager.hasChanges()){return}if(this._saveLabelsTimeout){window.clearTimeout(this._saveLabelsTimeout)
}this._saveLabelsTimeout=this._doSaveLabels.delay(2000,this)},_doSaveLabels:function(){var a=$(":input",this.labelsList).serializeArray();
$.ajax({url:this.getMetaData("labelsSaveUrl"),type:"POST",context:this,data:a,dataType:"json",success:function(b){this._handleSaveLabelsSuccess(b)
}})},_handleSaveLabelsSuccess:function(a){},getNewTicketMessages:function(){var a=$("li.message-item:last",this.contentWrapper).data("message-id");
$.ajax({url:this.getMetaData("getMessagesUrl"),type:"POST",context:this,data:{since:a},dataType:"json",success:function(b){Array.each(b.messages,function(c){this.displayNewMessage(c)
},this);if(b.has_notes){this.unloadTicketTab("ticket-notes")}}})},_saveCustomFields:function(a){$(".buttons .loading-off",this.custom_fields_edit).hide();
$(".buttons .loading-on",this.custom_fields_edit).show();var b=a.serializeArray();$.ajax({url:BASE_URL+"agent/tickets/"+this.getMetaData("ticket_id")+"/ajax-save-custom-fields",type:"POST",context:this,data:b,dataType:"html",success:function(c){this._handleSaveCustomFieldsSuccess(c)
}})},_handleSaveCustomFieldsSuccess:function(a){$(".buttons .loading-on",this.custom_fields_edit).hide();$(".buttons .loading-off",this.custom_fields_edit).show();
this.closeCustomFieldEditor();$(".wrap",this.custom_fields_display).html(a)},flagMenu:null,_initFlagMenu:function(){var a=this;
this.flagMenu=new DeskPRO.UI.Menu({triggerElement:$(".ticket-flag:first",this.wrapper),menuElement:$(".ticket-flag-menu:first",this.wrapper),onItemClicked:function(b){a._handleFlagMenuClick(b)
}});this.destroyMenus.push(this.flagMenu)},_handleFlagMenuClick:function(b){var a="flag";var c=$(b.itemEl).data("flag");var d=this.getPropertyManager(a);
this.changeManager.setInstantChange(d,c)},_handleFlagMenuClickSuccess:function(a,b){DeskPRO_Window.getMessageBroker().sendMessage("filter-flagged.flag-changed",{old_flag:a,new_flag:b})
},_initTicketTabs:function(){var b=this;var a=new DeskPRO.UI.SimpleTabs({context:$(".container-tabbed-wrap.ticket-participants",this.contentWrapper),triggerElements:$(".container-tabbed-tabs li",this.contentWrapper),activeClassname:"container-tabbed-tabs-active"});
var c=new DeskPRO.UI.SimpleTabs({context:$(".full-container-tabbed.messages-container",this.contentWrapper),triggerElements:$(".full-container-tabbed-tabs li",this.contentWrapper),onTabSwitch:function(d){if(d.tabEl.is(".ticket-log")){b._loadTicketTab_Log()
}else{if(d.tabEl.is(".ticket-attach")){b._loadTicketTab_Attach()}else{if(d.tabEl.is(".ticket-related-content")){b._loadTicketTab_RelatedContent()
}}}}})},unloadTicketTab:function(b){var a=$(".tab-content."+b,this.wrapper);a.html("").addClass("unloaded")},_loadTicketTab_Log:function(){var a=$(".tab-content.ticket-log",this.wrapper);
if(!a.is(".unloaded")){return}$.ajax({url:this.getMetaData("tabTicketLogUrl"),type:"GET",dataType:"html",context:this,success:function(b){a.html(b);
a.removeClass("unloaded");this.initFeaturesOnCollection(a,{routes:[".with-route"],times:[".timeago"]})}})},_loadTicketTab_RelatedContent:function(){var a=$(".tab-content.ticket-realted-content",this.wrapper);
if(!a.is(".unloaded")){return}$.ajax({url:this.getMetaData("tabRelatedContentUrl"),type:"GET",dataType:"html",success:function(b){a.html(b);
a.removeClass("unloaded")}})},_loadTicketTab_Attach:function(){var a=$(".tab-content.ticket-attach",this.wrapper);if(!a.is(".unloaded")){return
}$.ajax({url:this.getMetaData("tabAttachmentsUrl"),type:"GET",dataType:"html",success:function(b){a.html(b);a.removeClass("unloaded")
}})},ticketActionsMenu:null,_initTicketActionsMenu:function(){var a=this;this.ticketActionsMenu=new DeskPRO.UI.Menu({triggerElement:$(".ticket-actions-trigger:first",this.wrapper),menuElement:$(".ticket-actions-menu:first",this.wrapper),onItemClicked:function(b){var c=$(b.itemEl).data("op");
if(c=="delete"){a.showDeleteOverlay()}}});this.destroyMenus.push(this.ticketActionsMenu)},deleteOverlay:null,deleteOverlayEl:null,_initDeleteOverlay:function(){if(this.deleteOverlay){return
}this.deleteOverlayEl=$(".delete-ticket-overlay:first",this.wrapper);this.deleteOverlay=new DeskPRO.UI.Overlay({contentElement:this.deleteOverlayEl});
$(".save-trigger",this.deleteOverlayEl).click((function(){this.doTicketDelete()}).bind(this));this.destroyOverlays.push(this.deleteOverlay)
},showDeleteOverlay:function(){this._initDeleteOverlay();this.deleteOverlay.openOverlay()},doTicketDelete:function(){$(".loading-off",this.deleteOverlayEl).hide();
$(".loading-on",this.deleteOverlayEl).show();var b=[];b.push({name:"reason",value:$(".delete-reason",this.deleteOverlayEl).val()});
var a=this;$.ajax({url:BASE_URL+"agent/tickets/"+this.getMetaData("ticket_id")+"/delete",type:"POST",data:b,dataType:"json",success:function(c){a.deleteOverlay.closeOverlay();
DeskPRO_Window.removePage(a);DeskPRO_Window.loadPage(BASE_URL+"agent/tickets/"+a.getMetaData("ticket_id"),{ignoreExist:true},function(e){var d=a.getMetaData("title");
if(d.length>20){d=d.substr(0,20)+" ..."}d=Orb.escapeHtml(d);DeskPRO_Window.showUndoMessage('Deleted ticket "'+d+'"',function(){e.doTicketUndelete()
})})}})},doTicketUndelete:function(){var a=this.getPropertyManager("status");this.changeManager.setInstantChange(a,"open")
},messageActionsMenu:null,_initMessageActionsMenu:function(){var a=this;this.messageActionsMenu=new DeskPRO.UI.Menu({triggerElement:null,menuElement:$(".ticket-message-edit-menu:first",this.wrapper),onItemClicked:function(d){a._doMessageAction($(d.itemEl).data("option-id"),$(d.menu.getOpenTriggerElement()).data("message-id"))
}});this.destroyMenus.push(this.messageActionsMenu);var c=this.messageActionsMenu;var b=$(".messages-wrap:first",this.wrapper)[0];
$(".ticket-message-edit-btn",b).live("click",function(d){c.openMenu(d)})},_doMessageAction:function(c,b){switch(c){case"view-details":var a=new DeskPRO.UI.Overlay({contentMethod:"iframe",iframeUrl:this.getMetaData("viewMessageUnformattedUrl").replace("{message_id}",b),destroyOnClose:true});
a.openOverlay();break;case"quote":console.debug("todo loading indicator when loading _doMessageAction quote");$.ajax({url:this.getMetaData("getMessageQuoteUrl").replace("{message_id}",b),type:"GET",context:this,dataType:"json",success:function(d){this.toggleReplyBar("on");
$('div.reply-form-fields:first textarea[name="message"]:first',this.ticketReply).val(d.message_quote+"\n\n")}});break}},_handleSendReply:function(b){if(this.isSendingReply){return
}$(".send-reply button",this.ticketBar).hide();var a=$(".send-reply .spinner",this.ticketBar).show().empty();a.parent().addClass("is-loading");
var d=new Spinner(a,{radii:[4,8],padding:0}).play();this.isSendingReply=true;var c=b.serializeArray();c.push({name:"client_messages_since",value:DeskPRO_Window.getLastClientMessageId()});
$.ajax({url:BASE_URL+"agent/tickets/"+this.getMetaData("ticket_id")+"/ajax-save-reply",type:"POST",context:this,data:c,dataType:"json",success:function(e){this.isSendingReply=false;
if(e.client_messages){DeskPRO_Window.forwardClientMessageData(e.client_messages)}this._handleSendReplySuccess(e)},complete:function(){var e=$(".send-reply .spinner",this.ticketBar).hide().empty();
e.parent().removeClass("is-loading");this.afterNewReply();$(".send-reply button",this.ticketBar).show()}})},personPopoutHtml:null,personPopoutWaiting:false,_initPopout:function(){var a=this;
var c=this.wrapper;var b=this.getMetaData("viewPersonUrl");$.ajax({dataType:"text",url:b,type:"GET",success:function(d){a.personPopoutHtml=d;
if(a.personPopoutWaiting){a.personPopoutWaiting=false;a._initPopoutPageFragment()}}});$(".person-overview",c).css({cursor:"pointer"}).click(function(d){a.isMouseOverPopout=true;
a.openPopOut(d)})},_initPopoutEls_done:false,_initPopoutEls:function(){if(this._initPopoutEls_done){return}this._initPopoutEls_done=true;
var b=this.contentWrapper;var a=this;this.popout=$(".person-popout:first",b);this.popout.click(function(c){c.stopPropagation()
});this.popout.detach().appendTo("body");this.destroyEls.push(this.popout);this.popoutOuter=$(".person-popout-outer:first",b);
this.popoutOuter.detach().appendTo("body");this.destroyEls.push(this.popoutOuter);this.popoutTabs=$(".person-popout-tabs:first",b);
this.popoutTabs.detach().appendTo("body");this.destroyEls.push(this.popoutTabs);var a=this;$(".close:first",this.popoutTabs).click(function(){a.closePopout()
});$(".move-to-tab:first",this.popoutTabs).click(function(){DeskPRO_Window.runPageRouteFromElement($(".person-overview",a.wrapper));
a.closePopout()})},openPopOut:function(d){this._initPopoutEls();if(this.popout.is(":visible")){return}var g=$(".person-overview:first",this.wrapper);
var f=g.offset();var c=this.wrapper.offset();var b=f.left-35;if(b>780){b=780}var a=true;if(b<400){a=false}if(a){this.popout.css({position:"absolute",display:"block","z-index":999998,width:b,overflow:"auto"});
this.popout.css({top:(c.top-8),left:(f.left-this.popout.outerWidth()-20),bottom:30});var e=this.popout.offset();this.popoutOuter.css({position:"absolute",display:"block","z-index":999997,width:b+2+6,overflow:"auto",top:e.top-1,left:e.left-1,bottom:29});
this.popoutTabs.css({"z-index":999996,display:"block",top:(c.top-30),left:(f.left-260)})}if(!this.hasInitPopout&&a){if(this.personPopoutHtml){this._initPopoutPageFragment()
}else{this.personPopoutWaiting=true}}},closePopout:function(){this.popout.hide();this.popoutOuter.hide();this.popoutTabs.hide()
},_initPopoutPageFragment:function(){this.popoutPage=DeskPRO_Window.createPageFragment(this.personPopoutHtml);this.popout.html(this.personPopoutHtml);
this.personPopoutHtml=null;this.popoutPage.initPage(this.popout);this.hasInitPopout=true},_initReplyBar:function(){this.parent();
var a=this;$("input.reply-assign-trigger",this.ticketReply).click(function(f){if($(this).val()!="0"){$(this).attr("checked",false).val("0");
$("span.reply-assign-label",a.ticketReply).hide().html("")}else{f.preventDefault();f.customEvents=new Events();f.customEvents.addEvent("itemClicked",a._handleReplybarAssign.bind(a));
a.ticketOptionsMenus.agent_id.openMenu(f)}});$("span.reply-assign-label",this.ticketReply).click(function(f){f.customEvents=new Events();
f.customEvents.addEvent("itemClicked",a._handleReplybarAssign.bind(a));a.ticketOptionsMenus.agent_id.openMenu(f)});var c=new DeskPRO.UI.Menu({menuElement:$("ul.cc-to-menu:first",this.ticketReply)});
this.destroyMenus.push(c);var e=$("ul.cc-to-menu:first input",this.ticketReply);var b=$("input.cc-to-trigger",this.ticketReply).click(function(f){if($(this).val()!=""){$(this).attr("checked",false).val("0");
$("span.cc-to-label",a.ticketReply).hide().html("")}else{f.preventDefault();c.openMenu(f);b.focus()}});$("span.cc-to-label",this.ticketReply).click(function(f){c.openMenu(f)
});var d=$("button.cc-to-save-trigger",this.ticketReply).click(function(h){var i=e.val().trim();if(i.length){b.attr("checked",true).val(i);
var g=$("span.cc-to-label",a.ticketReply);var f=g.data("label").replace("%email%",i);g.html(f).show();c.closeMenu()}else{b.attr("checked",false).val("");
$("span.cc-to-label",a.ticketReply).hide().html("")}})},_handleReplybarAssign:function(d){var c=$(d.itemEl).data("option-id");
var e=$(d.itemEl).html();if(c){$("input.reply-assign-trigger",this.ticketReply).attr("checked",true).val(c);var b=$("span.reply-assign-label",this.ticketReply);
var a=b.data("label").replace("%agent%",e);b.html(a).show()}else{$("input.reply-assign-trigger",this.ticketReply).attr("checked",false).val("0");
$("span.reply-assign-label",this.ticketReply).hide().html("")}}});Orb.createNamespace("DeskPRO.Agent.PageFragment.Page");
DeskPRO.Agent.PageFragment.Page.NewTicket=new Class({Extends:DeskPRO.Agent.PageFragment.Page.BasicTicket,allowDupe:true,TYPENAME:"newticket",initPage:function(c){this.parent(c);
Orb.Compat.WebForms.placeholder($('input[name="subject"]',this.wrapper));Orb.Compat.WebForms.placeholder($("input.person-name-search",this.wrapper));
this._initNewUser();this._initUserChoice();var a=this;var b=$(":input",this.wrapper).change(function(d){a.userHasInputted=true
});this.addEvent("closeTab",this.handleUserCloseTab.bind(this))},handleUserCloseTab:function(b){var a=this;if(this.userHasInputted){b.deskpro.cancelClose=true;
DeskPRO_Window.showConfirm("This ticket has not been saved, are yo usure you want to close the tab?",function(){DeskPRO_Window.removePage(a)
})}},_initLayout:function(){this.layout=new DeskPRO.Agent.Layout.FooterLayout(this.wrapper)},newUserHelper:null,_initNewUser:function(){$("a.new-person-trigger",this.contentWrapper).click(this._openNewUser.bind(this));
this.newUserHelper=new DeskPRO.Agent.PageHelper.NewUserOverlay({context:this.contentWrapper,onAfterSave:this._handleNewUserSaved.bind(this),saveUrl:this.getMetaData("newUserUrl")})
},_openNewUser:function(a){a.preventDefault();this.newUserHelper.open()},_handleNewUserSaved:function(b){var a=b.data;this.loadUser(a)
},userSearchEl:null,_initUserChoice:function(){this.userSearchEl=$("input.person-name-search",this.wrapper);this.userSearchEl.autocomplete({minLenght:2,source:this.getMetaData("userSearchUrl"),select:this.userSelected.bind(this)})
},userSelected:function(a,b){this.loadUser(b.item)},loadUser:function(a){$('input[name="person_id"]',this.contentWrapper).val(a.id);
this.userSearchEl.val(a.label)},_saveCustomFields:function(){},_handleSendReply:function(a){var b=$(":input",this.contentWrapper).add(a);
var c=b.serializeArray();$.ajax({url:this.getMetaData("submitTicketUrl"),data:c,type:"POST",dataType:"json",success:this._handleTicketSubmit.bind(this)})
},_handleTicketSubmit:function(a){console.log(a);DeskPRO_Window.removePage(this);DeskPRO_Window.runPageRoute("ticket:"+a.loadUrl)
}});Orb.createNamespace("DeskPRO.Agent.PageFragment.Page");DeskPRO.Agent.PageFragment.Page.Organization=new Class({Extends:DeskPRO.Agent.PageFragment.Basic,TYPENAME:"organiztion",wrapper:null,contactSection:null,notesSection:null,initPage:function(d){this.wrapper=d;
var a=this;$("input[placeholder]",this.wrapper).each(function(){Orb.Compat.WebForms.placeholder(this)});var b=$("h3.name.editable:first",d);
if(!b.attr("id")){b.attr("id",Orb.getUniqueId())}var c=new DeskPRO.Form.InlineEdit({baseElement:d,ajax:{url:BASE_URL+"agent/organizations/"+this.meta.organization_id+"/ajax-save"}});
$(this.wrapper).click(function(e){c.handleDocumentClick(e)});this.editContactInfoMenu=new DeskPRO.UI.Menu({triggerElement:$(".edit-contact-info:first",this.wrapper),menuElement:$(".contact-info-edit-menu:first",this.wrapper),onItemClicked:function(f){var e=$(f.itemEl).data("edit-type");
if(e=="email"){a.email_dlg.dialog("open")}else{a.startContactAdd(e)}}});this.initNoteFormEditable();this.initNotePagination();
this._initLabels();this._initCustomFieldsEditor();this.initRoutesOnCollection($(".with-route"))},destroyPage:function(){},custom_fields_display:null,custom_fields_edit:null,_initCustomFieldsEditor:function(){$(".person-custom-fields-edit:first",this.wrapper).click((function(){this.showCustomFieldEditor()
}).bind(this));this.custom_fields_display=$(".person-custom-fields:not(.edit)",this.wrapper);this.custom_fields_edit=$(".person-custom-fields.edit",this.wrapper).detach().appendTo($("body"));
$(".close-trigger",this.custom_fields_edit).click((function(){this.closeCustomFieldEditor()}).bind(this));var a=this;$(".save-trigger",this.custom_fields_edit).click((function(){var b=$(":input",a.custom_fields_edit);
this._saveCustomFields(b)}).bind(this))},showCustomFieldEditor:function(){var a=this.custom_fields_display.width();if(a<350){a=350
}this.custom_fields_edit.css({position:"absolute",width:a,"z-index":10000});this.custom_fields_edit.position({my:"right top",at:"right top",of:$(".properties-info-list-wrap",this.wrapper)});
this.custom_fields_edit.slideDown()},closeCustomFieldEditor:function(){this.custom_fields_edit.slideUp()},_saveCustomFields:function(a){$(".buttons .loading-off",this.custom_fields_edit).hide();
$(".buttons .loading-on",this.custom_fields_edit).show();var b=a.serializeArray();$.ajax({url:this.getMetaData("saveFieldsUrl"),type:"POST",context:this,data:b,dataType:"json",success:function(c){this._handleSaveCustomFieldsSuccess(c)
}})},_handleSaveCustomFieldsSuccess:function(a){$(".buttons .loading-on",this.custom_fields_edit).hide();$(".buttons .loading-off",this.custom_fields_edit).show();
this.closeCustomFieldEditor();$(".wrap",this.custom_fields_display).html(a.custom_fields_html)},labelsList:null,_initLabels:function(){this.labelsList=$(".org-tags ul",this.wrapper).tagit({availableTags:this.getMetaData("labelsAutocompleteUrl"),enableBackspace:false,fieldName:"labels",onchange:this.saveLabels.bind(this)})
},_saveLabelsTimeout:null,saveLabels:function(){if(this._saveLabelsTimeout){window.clearTimeout(this._saveLabelsTimeout)}this._saveLabelsTimeout=this._doSaveLabels.delay(2000,this)
},_doSaveLabels:function(){var a=$(":input",this.labelsList).serializeArray();$.ajax({url:this.getMetaData("labelsSaveUrl"),type:"POST",context:this,data:a,dataType:"json",success:function(b){this._handleSaveLabelsSuccess(b)
}})},_handleSaveLabelsSuccess:function(a){},initNoteFormEditable:function(){this.notesSection=$(".notes-wrap:first",this.wrapper);
$(".trigger.new-note",this.notesSection).click((function(){this.openNoteEdtiable()}).bind(this));$(".new-note-form .trigger.cancel",this.notesSection).click((function(a){a.preventDefault();
this.closeNoteEditable()}).bind(this));$(".new-note-form .trigger.save",this.notesSection).click((function(){this.saveNote()
}).bind(this))},openNoteEdtiable:function(){$(".trigger.new-note",this.notesSection).hide();var a=$(".new-note-form",this.notesSection);
if(a.is(":hidden")){$(".new-note-form textarea",this.notesSection).val("");a.slideDown()}},closeNoteEditable:function(){$(".new-note-form textarea").val("");
var a=$(".new-note-form",this.notesSection);if(a.is(":visible")){a.slideUp()}$(".trigger.new-note",this.notesSection).show()
},saveNote:function(){$(".new-note-form").addClass("saving");var a=$(".new-note-form textarea").val();$.ajax({timeout:20000,type:"POST",url:BASE_URL+"agent/organizations/"+this.meta.organization_id+"/ajax-save-note",data:{note:a},success:this.handleNoteSave.bind(this)})
},handleNoteSave:function(b){var a=$(".note-list",this.notesSection);a.prepend(b.note_li_html);$(".new-note-form").removeClass("saving");
this.closeNoteEditable()},initNotePagination:function(){var a=$("ul.pages",this.notesSection);if(!a.length){return}var b=this;
$("li",a).click(function(){b.loadNotePage($(this).data("page"))})},loadNotePage:function(a){$.ajax({timeout:20000,type:"POST",url:BASE_URL+"agent/organizations/"+this.meta.organization_id+"/ajax-get-notes",data:{pp:$(".note-list",this.notesSection).data("limit"),p:a},success:this.handleGetNotes.bind(this)})
},handleGetNotes:function(b){var a=$(".note-list",this.notesSection);a.html(b.notes_html);$("ul.pages il",this.notesSection).removeClass("active");
$("ul.pages il.page-"+b.page,this.notesSection).addClass("active")},startContactAdd:function(c){var d=$(".contact-add-tpl.new."+c,this.wrapper).clone();
this.wrapper.append(d);var a=$(".contact-info-list-wrap:first",this.wrapper);var e=a.position();d.css({position:"absolute",top:10,left:10});
d.show();d.position({my:"right top",at:"right top",of:a});$(".close",d).click(function(){d.remove()});var b=this;$(".save",d).click(function(){b.saveContact(d)
})},saveContact:function(b){var a=$(":input, select, textarea",b).serializeArray();b.addClass("saving");$.ajax({timeout:20000,type:"POST",url:BASE_URL+"agent/organizations/"+this.meta.organization_id+"/ajax-save-contact",data:a,success:(function(c){this.handleSaveSuccess(c,b)
}).bind(this)})},handleSaveSuccess:function(b,a){$(".contact-info-list-wrap:first",this.wrapper).html(b.contact_html);a.remove()
}});Orb.createNamespace("DeskPRO.Agent.PageFragment.Page");DeskPRO.Agent.PageFragment.Page.Person=new Class({Extends:DeskPRO.Agent.PageFragment.Basic,TYPENAME:"person",wrapper:null,hasSetupEmailDlg:false,email_display:null,email_dlg:null,contactSection:null,notesSection:null,initPage:function(e){this.wrapper=e;
this.contentWrapper=$("div.layout-content:first",e);this.zIndex=999999;var b=this;var a=this.contentWrapper;a.tinyscrollbar();
$("div.scroll-content:first, div.scroll-viewport:first",this.contentWrapper).resize(function(){a.tinyscrollbar_update()});
this.initRoutesOnCollection($(".with-route",this.wrapper));this.initTimesOnCollection($("time.timeago",this.wrapper));$("input[placeholder]",this.wrapper).each(function(){Orb.Compat.WebForms.placeholder(this)
});var c=$("h3.name.editable:first",e);if(!c.attr("id")){c.attr("id",Orb.getUniqueId())}var d=new DeskPRO.Form.InlineEdit({baseElement:e,ajax:{url:BASE_URL+"agent/people/"+this.meta.person_id+"/ajax-save"}});
$(this.wrapper).click(function(g){d.handleDocumentClick(g)});this.initEmailDlg();this.email_display=$(".main .header .email:first",e);
this.editContactInfoMenu=new DeskPRO.UI.Menu({triggerElement:$(".edit-contact-info:first",this.wrapper),menuElement:$(".contact-info-edit-menu:first",this.wrapper),onItemClicked:function(h){var g=$(h.itemEl).data("edit-type");
if(g=="email"){b.showEmailEditor()}else{b.startContactAdd(g)}}});var f=new DeskPRO.UI.SimpleTabs({context:$(".full-container-tabbed",this.wrapper),triggerElements:$(".full-container-tabbed-tabs li",this.wrapper)});
this.initNoteFormEditable();this.initNotePagination();this.initOrgEditable();this._initLabels();this._initCustomFieldsEditor()
},destroyPage:function(){if(this.org_dlg){this.org_dlg.remove()}if(this.email_dlg){this.email_dlg.remove()}},updateCounts:function(){var a=$(".full-container-tabbed-tabs",this.wrapper);
$.ajax({url:this.getMetaData("getUpdatedCountsUrl"),type:"GET",context:this,dataType:"json",success:function(b){Object.each(b,function(d,c){var e=".person-"+c+"-count";
$(e,a).html("("+d+")")})}})},custom_fields_display:null,custom_fields_edit:null,_initCustomFieldsEditor:function(){$(".person-custom-fields-edit:first",this.wrapper).click((function(){this.showCustomFieldEditor()
}).bind(this));this.custom_fields_display=$(".person-custom-fields:not(.edit)",this.wrapper);this.custom_fields_edit=$(".person-custom-fields.edit",this.wrapper).detach().appendTo($("body"));
$(".close-trigger",this.custom_fields_edit).click((function(){this.closeCustomFieldEditor()}).bind(this));var a=this;$(".save-trigger",this.custom_fields_edit).click((function(){var b=$(":input",a.custom_fields_edit);
this._saveCustomFields(b)}).bind(this))},showCustomFieldEditor:function(){var a=this.custom_fields_display.width();if(a<350){a=350
}this.custom_fields_edit.css({position:"absolute",width:a,"z-index":this.zIndex});this.custom_fields_edit.position({my:"right top",at:"right top",of:$(".properties-info-list-wrap",this.wrapper)});
this.custom_fields_edit.slideDown()},closeCustomFieldEditor:function(){this.custom_fields_edit.slideUp()},_saveCustomFields:function(a){$(".buttons .loading-off",this.custom_fields_edit).hide();
$(".buttons .loading-on",this.custom_fields_edit).show();var b=a.serializeArray();$.ajax({url:this.getMetaData("saveFieldsUrl"),type:"POST",context:this,data:b,dataType:"json",success:function(c){this._handleSaveCustomFieldsSuccess(c)
}})},_handleSaveCustomFieldsSuccess:function(c){$(".buttons .loading-on",this.custom_fields_edit).hide();$(".buttons .loading-off",this.custom_fields_edit).show();
this.closeCustomFieldEditor();$(".wrap",this.custom_fields_display).html(c.custom_fields_html);var b=$("ul.usergroups-list",this.wrapper).html("<li>"+c.usergroup_names.join("</li><li>")+"</li>");
var a=$(".usergroups-list-wrap",this.wrapper);if($("li",b).length){a.show()}else{a.hide()}},labelsList:null,_initLabels:function(){this.labelsList=$(".people-tags ul",this.wrapper).tagit({availableTags:this.getMetaData("labelsAutocompleteUrl"),enableBackspace:false,fieldName:"labels",onchange:this.saveLabels.bind(this)})
},_saveLabelsTimeout:null,saveLabels:function(){if(this._saveLabelsTimeout){window.clearTimeout(this._saveLabelsTimeout)}this._saveLabelsTimeout=this._doSaveLabels.delay(2000,this)
},_doSaveLabels:function(){var a=$(":input",this.labelsList).serializeArray();$.ajax({url:this.getMetaData("labelsSaveUrl"),type:"POST",context:this,data:a,dataType:"json",success:function(b){this._handleSaveLabelsSuccess(b)
}})},_handleSaveLabelsSuccess:function(a){},initOrgEditable:function(){this.org_dlg=$(".org-edit-dlg",this.wrapper).detach().appendTo($("body"));
$(".close-trigger",this.org_dlg).click((function(){this.closeOrgEditor()}).bind(this));var a=this;$(".save-trigger",this.org_dlg).click((function(){var b=$(":input",a.org_dlg);
this.saveOrg()}).bind(this));$(".organization.hover-edit:first",this.wrapper).dblclick((function(){this.showOrgEditor()}).bind(this))
},showOrgEditor:function(){var a=this.org_dlg.width();if(a<350){a=350}this.org_dlg.css({position:"absolute",width:a,"z-index":this.zIndex});
this.org_dlg.position({my:"left top",at:"left top",of:$(".organization.hover-edit:first",this.wrapper)});this.org_dlg.slideDown()
},closeOrgEditor:function(){this.org_dlg.slideUp()},saveOrg:function(){var c=$('select[name="organization_id"]',this.org_dlg);
var a=$("option:selected",c);var b={organization_id:a.val(),organization_position:$('input[name="organization_position"]',this.org_dlg).val()};
$.ajax({timeout:20000,type:"POST",url:BASE_URL+"agent/people/"+this.meta.person_id+"/ajax-save-organization",data:b,success:this.handleOrgSave.bind(this)})
},handleOrgSave:function(a){$(".organization.hover-edit:first .name").html(a.organization_name);$(".organization.hover-edit:first .position").html(a.organization_position);
this.closeOrgEditor()},initNoteFormEditable:function(){this.notesSection=$(".notes-wrap:first",this.wrapper);$(".new-note-form .trigger.save",this.notesSection).click((function(){this.saveNote()
}).bind(this))},saveNote:function(){$(".new-note-form",this.notesSection).addClass("saving");var a=$(".new-note-form textarea",this.notesSection).val();
$.ajax({timeout:20000,type:"POST",url:BASE_URL+"agent/people/"+this.meta.person_id+"/ajax-save-note",data:{note:a},success:this.handleNoteSave.bind(this)})
},handleNoteSave:function(b){$(".new-note-form textarea",this.notesSection).val("");var a=$(".note-list",this.notesSection);
a.append(b.note_li_html);$(".new-note-form",this.notesSection).removeClass("saving");this.updateCounts()},initNotePagination:function(){var a=$("ul.pages",this.notesSection);
if(!a.length){return}var b=this;$("li",a).click(function(){b.loadNotePage($(this).data("page"))})},loadNotePage:function(a){$.ajax({timeout:20000,type:"POST",url:BASE_URL+"agent/people/"+this.meta.person_id+"/ajax-get-notes",data:{pp:$(".note-list",this.notesSection).data("limit"),p:a},success:this.handleGetNotes.bind(this)})
},handleGetNotes:function(b){var a=$(".note-list",this.notesSection);a.html(b.notes_html);$("ul.pages il",this.notesSection).removeClass("active");
$("ul.pages il.page-"+b.page,this.notesSection).addClass("active")},startContactAdd:function(c){var d=$(".contact-add-tpl.new."+c,this.wrapper).clone();
this.wrapper.append(d);var a=$(".contact-info-list-wrap:first",this.wrapper);var e=a.position();d.css({position:"absolute",top:10,left:10,"z-index":this.zIndex});
d.show();d.position({my:"right top",at:"right top",of:a});$(".close",d).click(function(){d.remove()});var b=this;$(".save",d).click(function(){b.saveContact(d)
})},saveContact:function(b){var a=$(":input, select, textarea",b).serializeArray();b.addClass("saving");$.ajax({timeout:20000,type:"POST",url:BASE_URL+"agent/people/"+this.meta.person_id+"/ajax-save-contact",data:a,success:(function(c){this.handleSaveSuccess(c,b)
}).bind(this)})},handleSaveSuccess:function(b,a){$(".contact-info-list-wrap:first",this.wrapper).html(b.contact_html);a.remove()
},initEmailDlg:function(){this.email_dlg=$(".email-edit-dlg",this.wrapper).detach().appendTo($("body"));$(".close-trigger",this.email_dlg).click((function(){this.closeEmailEditor()
}).bind(this));var a=this;$(".save-trigger",this.email_dlg).click((function(){this.saveEmails()}).bind(this));$(".organization.hover-edit:first",this.wrapper).dblclick((function(){this.showEmailEditor()
}).bind(this));$("ul.emails-list",this.email_dlg).click(function(c){var b=$(c.target);var d=b.parent();if(!d.length){return
}if(b.is(".delete")){d.addClass("delete");if(d.is(".new")){d.remove()}}else{if(b.is(".undelete")){d.removeClass("delete")
}else{if(b.is(".set-primary")){$("ul.emails-list li.primary",this.email_dlg).removeClass("primary");d.addClass("primary")
}}}});$(".new-email-btn",this.email_dlg).click((function(){var c=$(".new-email-input",this.email_dlg).val().trim();var b=$(".emails-list li.tpl",this.email_dlg).clone();
b.removeClass("tpl");b.attr("data-new-email",c);$(".email-address",b).html(c);$(".emails-list",this.email_dlg).append(b)}).bind(this))
},showEmailEditor:function(){var a=this.email_dlg.width();if(a<350){a=350}this.email_dlg.css({position:"absolute",width:a,"z-index":this.zIndex});
this.email_dlg.position({my:"left top",at:"left top",of:$(".contact-info-list-wrap:first",this.wrapper)});this.email_dlg.slideDown()
},closeEmailEditor:function(){this.email_dlg.slideUp()},saveEmails:function(){var a=[];var b=[];var d=0;$("ul.emails-list li",this.email_dlg).each((function(f,g){var g=$(g);
if(g.is(".tpl")){return}if(g.is(".exists")){if(g.is(".delete")){a.push(g.data("email-id"))}if(g.is(".primary")){d=g.data("email-id")
}}else{b.push(g.data("new-email"));if(g.is(".primary")){d=g.data("new-email")}}}).bind(this));var e=[];var c=null;while(c=a.pop()){e.push({name:"del_ids[]",value:c})
}while(c=b.pop()){e.push({name:"new_emails[]",value:c})}e.push({name:"primary_id",value:d});$.ajax({timeout:20000,type:"POST",url:BASE_URL+"agent/people/"+this.meta.person_id+"/ajax-save-emails",data:e,success:this.handleEmailSave.bind(this)})
},handleEmailSave:function(b){$("ul.emails-list",this.email_dlg).empty().html(b.dlg_html);var a="<li>"+b.emails_list.join("</li><li>")+"</li>";
$(".contact-info-list-wrap:first ul.emails-list:first",this.wrapper).html(a);this.closeEmailEditor()}});Orb.createNamespace("DeskPRO.Agent.PageFragment.Page");
DeskPRO.Agent.PageFragment.Page.PersonPopout=new Class({Extends:DeskPRO.Agent.PageFragment.Page.Person,TYPENAME:"person",initPage:function(a){this.parent(a)
}});Orb.createNamespace("DeskPRO.Agent.PageFragment.Page");DeskPRO.Agent.PageFragment.Page.TwitterUser=new Class({Extends:DeskPRO.Agent.PageFragment.Basic,el:null,tabs:null,initPage:function(a){this.el=$(a);
this._initTimeago();this._initTabs()},_initTimeago:function(){this.initTimesOnCollection($(".timeago",this.el))},_initTabs:function(){this.tabs=new DeskPRO.UI.SimpleTabs({context:$(".full-container-tabbed",this.el),triggerElements:$(".full-container-tabbed-tabs li",this.el)})
}});Orb.createNamespace("DeskPRO.Agent.PageFragment.Page");DeskPRO.Agent.PageFragment.Page.KbNewArticle=new Class({Extends:DeskPRO.Agent.PageFragment.Basic,TYPENAME:"kb_article_new",wrapper:null,initPage:function(b){this.wrapper=b;
var a=this;$(".save-trigger",this.wrapper).click(a.sendSave.bind(this))},sendSave:function(){var a=$(":input, select, textarea",this.wrapper).serializeArray();
$.ajax({url:BASE_URL+"agent/kb/article/new/save",type:"POST",context:this,dataType:"json",data:a,success:function(b){if(b.pending_article_id){DeskPRO_Window.getMessageBroker().sendMessage("kb.pending_article_removed",{pending_article_id:b.pending_article_id})
}DeskPRO_Window.runPageRoute("kb_article_edit:"+b.load_url);DeskPRO_Window.removePage(this)}})}});Orb.createNamespace("DeskPRO.Agent.PageFragment.Page");
DeskPRO.Agent.PageFragment.Page.KbViewArticle=new Class({Extends:DeskPRO.Agent.PageFragment.Basic,TYPENAME:"kb_article_view",wrapper:null,article_id:null,initPage:function(b){this.wrapper=b;
this.article_id=this.getMetaData("article_id");this._initBasic();this._initMenus();this._initLabels();this._initEditorEnable();
this._initCompareRevs();if(this.meta.has_validating){this._initValidating()}var a=this.wrapper;a.tinyscrollbar();$("div.scroll-content:first, div.scroll-viewport:first",this.wrapper).resize(function(){a.tinyscrollbar_update()
})},_initBasic:function(){var a=this;$(".edit-trigger",this.wrapper).click(function(){DeskPRO_Window.runPageRoute("kb_article_edit:"+BASE_URL+"agent/kb/article/"+a.article_id);
DeskPRO_Window.removePage(a)});$(".validate-trigger",this.wrapper).click(function(){DeskPRO_Window.runPageRoute("kb_article_edit:"+BASE_URL+"agent/kb/article/"+a.article_id+"?do_validate=1");
DeskPRO_Window.removePage(a)});var b=$("h3.title.editable:first",this.wrapper);if(!b.attr("id")){b.attr("id",Orb.getUniqueId())
}var c=new DeskPRO.Form.InlineEdit({baseElement:this.wrapper,ajax:{url:BASE_URL+"agent/kb/"+this.meta.article_id+"/ajax-save"}})
},_initMenus:function(){this.statusMenu=new DeskPRO.UI.Menu({triggerElement:$(".menu-trigger.status:first",this.wrapper),menuElement:$(".menu.status:first",this.wrapper)})
},labelsList:null,_initLabels:function(){this.labelsList=$(".kb-tags ul",this.contentWrapper);this.labelsTagit=this.labelsList.tagit({availableTags:this.getMetaData("labelsAutocompleteUrl"),enableBackspace:false,fieldName:"labels",onchange:this.saveLabels.bind(this)})
},_saveLabelsTimeout:null,saveLabels:function(){if(this.changeManager.hasChanges()){return}if(this._saveLabelsTimeout){window.clearTimeout(this._saveLabelsTimeout)
}this._saveLabelsTimeout=this._doSaveLabels.delay(2000,this)},_doSaveLabels:function(){var a=$(":input",this.labelsList).serializeArray();
$.ajax({url:this.getMetaData("labelsSaveUrl"),type:"POST",context:this,data:a,dataType:"json",success:function(b){this._handleSaveLabelsSuccess(b)
}})},_handleSaveLabelsSuccess:function(a){},_initValidating:function(){$("button.approve-article",this.wrapper).click(this.approveEdit.bind(this));
$("button.disapprove-article",this.wrapper).click(this.disapproveEdit.bind(this));$("button.skip-article",this.wrapper).click(this.skipValidateEdit.bind(this))
},approveEdit:function(){$.ajax({url:BASE_URL+"agent/kb/validating-articles/validate/"+this.meta.article_id+".json",type:"POST",context:this,dataType:"json",success:function(b){var a=b.next_article_id;
if(a){DeskPRO_Window.runPageRoute("kb_article_edit:"+BASE_URL+"agent/kb/article/"+a)}DeskPRO_Window.removePage(this)}})},disapproveEdit:function(){$.ajax({url:BASE_URL+"agent/kb/validating-articles/disapprove/"+this.meta.article_id+".json",type:"POST",context:this,dataType:"json",success:function(b){var a=b.next_article_id;
if(a){DeskPRO_Window.runPageRoute("kb_article_edit:"+BASE_URL+"agent/kb/article/"+a)}DeskPRO_Window.removePage(this)}})},skipValidateEdit:function(){$.ajax({url:BASE_URL+"agent/kb/validating-articles/get-next/"+this.meta.article_id+".json",type:"POST",context:this,dataType:"json",success:function(b){var a=b.next_article_id;
if(a){DeskPRO_Window.runPageRoute("kb_article_edit:"+BASE_URL+"agent/kb/article/"+a)}DeskPRO_Window.removePage(this)}})},_initEditorEnable:function(){var a=$(".kb-editor-edit",this.wrapper);
a.click(this.showEditor.bind(this))},showEditor:function(){if(!this.editor_has_loaded){this._initEditor();return}$(".kb-content.tab-content",this.wrapper).addClass("editor-on")
},_initEditor:function(){this.editor_has_loaded=true;$.ajax({url:this.getUrl("agent_kb_article_edit_geteditor"),type:"GET",context:this,dataType:"html",success:function(a){$(".kb-editor-wrap",this.wrapper).html(a);
this._initMediaBrowser();if(this.getMetaData("markup_mode")=="html"){this._initHtmlEditor()}else{this._initMarkdownEditor()
}this.showEditor()}})},_initMarkdownEditor:function(){var a=$(".kb-editor",this.wrapper);var b=$("> textarea",a);b.markItUp(MARKITUP_MARKDOWN_SETTINGS);
$(".dp-media-trigger",a).click(this.showMediaBrowser.bind(this));var c=this;this.mediaBrowser.addEvent("addLinkCode",function(d,e){$.markItUp({target:b,openWith:"",closeWith:d});
c.mediaBrowserOverlay.closeOverlay()});this.mediaBrowser.addEvent("addImageCode",function(d,e){$.markItUp({target:b,openWith:"",closeWith:d});
c.mediaBrowserOverlay.closeOverlay()});this.mediaBrowser.addEvent("addImageEditedCode",function(d,e){$.markItUp({target:b,openWith:"",closeWith:d});
c.mediaBrowserOverlay.closeOverlay()});this.mediaBrowser.addEvent("filesUploaded",function(d){if(c.mediaBrowserOverlay.isOverlayOpen()){return
}d.each(function(){var e=$(this);if(e.is(".is-image")){$(".image-trigger",e).click()}else{$(".link-trigger",e).click()}})
})},_initHtmlEditor:function(){var a=$(".kb-editor > textarea",this.wrapper);a.tinyMce({script_url:TINYMCE_URL,theme:"basic"})
},_initMediaBrowser:function(){if(this.mediabrowser_has_init){return}this.mediabrowser_has_init=true;this.mediaBrowserEl=$(".media-browser",this.wrapper);
this.mediaBrowserOverlay=new DeskPRO.UI.Overlay({contentElement:this.mediaBrowserEl});this.mediaBrowser=new DeskPRO.Agent.MediaBrowser({wrapper:this.mediaBrowserEl,additionalDropZone:$(".kb-editor > textarea",this.wrapper)})
},showMediaBrowser:function(){this._initMediaBrowser();this.mediaBrowserOverlay.openOverlay()},_initCompareRevs:function(){var a=$(".tab-content.kb-revs radio.old:selected",this.wrapper).val();
var b=$(".tab-content.kb-revs radio.new:selected",this.wrapper).val();var c=new DeskPRO.UI.Overlay({triggerElement:$("button.compare-trigger",this.wrapper),contentMethod:"ajax",contentAjax:{url:BASE_URL+"agent/kb/compare-revs/"+a+"/"+b},destroyOnClose:true});
c.openOverlay()}});Orb.createNamespace("DeskPRO.Agent.PageFragment.Page");DeskPRO.Agent.PageFragment.Page.KbEditArticle=new Class({Extends:DeskPRO.Agent.PageFragment.Basic,TYPENAME:"kb_article",wrapper:null,article_id:null,initPage:function(b){this.wrapper=b;
this.article_id=this.getMetaData("article_id");var a=this;$(".save-trigger",this.wrapper).click(a.sendSave.bind(this))},sendSave:function(){var a=$(":input, select, textarea",this.wrapper).serializeArray();
$.ajax({url:BASE_URL+"agent/kb/article/"+this.article_id+"/save",type:"POST",context:this,dataType:"json",data:a,success:function(b){}})
}});Orb.createNamespace("DeskPRO.Agent.PageFragment.Page");DeskPRO.Agent.PageFragment.Page.AgentChatTranscript=new Class({Extends:DeskPRO.Agent.PageFragment.Basic});
Orb.createNamespace("DeskPRO.Agent.PageFragment.Page");DeskPRO.Agent.PageFragment.Page.UserChat=new Class({Extends:DeskPRO.Agent.PageFragment.Basic,initPage:function(c){this.destroyEls=[];
this.wrapper=c;this.contentWrapper=this.wrapper.children(".layout-content").attr("id",Orb.getUniqueId());this.barWrapper=this.wrapper.children(".layout-footer").attr("id",Orb.getUniqueId());
DeskPRO_Window.getMessageBroker().addMessageListener("chat.new-message-"+this.meta.conversation_id,this.handleNewMessage.bind(this));
DeskPRO_Window.getMessageBroker().addMessageListener("chat.chat-ended-"+this.meta.conversation_id,this.chatHasEnded.bind(this));
DeskPRO_Window.getMessageBroker().addMessageListener("chat_user_agent.chat-parts-updated-"+this.meta.conversation_id,this.handleUpdateParts.bind(this));
this._initLayout();DeskPRO_Window.getMessageBroker().addMessageListener("window.innerLayout.resize",(function(){this._handleResize()
}).bind(this));var a=this;var b=$(".new-message",this.barWrapper);b.keypress(function(d){if(d.keyCode==13&&!d.metaKey){d.preventDefault();
var e=b.val().trim();b.val("");if(!e.length){return}a.sendMessage(e);a.addMessageRow(a.meta.youName,e)}});this._initMenus();
if(this.meta.viewPersonUrl){this._initPopout()}},_initLayout:function(){var a=this.contentWrapper;a.tinyscrollbar();$("div.scroll-content:first, div.scroll-viewport:first",this.contentWrapper).resize(function(){a.tinyscrollbar_update()
});this.layout=new DeskPRO.Agent.Layout.FooterLayout(this.wrapper);var b=this;var c=new DeskPRO.UI.SimpleTabs({context:this.contentWrapper,triggerElements:$(".full-container-tabbed-tabs li",this.contentWrapper),onTabSwitch:function(d){}})
},handleUpdateParts:function(a){this.updateActiveAgentList(a.agent_id,a.participant_ids)},updateActiveAgentList:function(a,c){var d=DeskPRO_Window.getDisplayName("agent",agent_id)||"Unassigned";
$("span.agent_id.val",this.wrapper).html(d);var b=$(".convo_participants ul",this.wrapper);b.empty();if(!c.lenght){b.append('<li class="agent-0">None</li>')
}else{Array.each(c,function(f){var e=DeskPRO_Window.getDisplayName("agent",f);b.append('<li class="agent-'+f+'">'+e+"</li>")
})}},_handleResize:function(){if(!this.layout){return}this.layout.doLayout()},_initMenus:function(){var a=this;this.qrMenu=new DeskPRO.UI.Menu({triggerElement:$("li.macros:first",this.barWrapper),menuElement:$("ul.quick-replies:first",this.wrapper),onItemClicked:function(c){var d=$(c.itemEl).data("qr-id");
a.loadQuickReply(d)}});this.assignMenu=new DeskPRO.UI.Menu({triggerElement:$("div.agent_id.menu-trigger:first, .convo_participants",this.wrapper),menuElement:$("ul.agent_id.menu:first",this.wrapper),onBeforeMenuOpened:function(g){var f=g.menu.elements.list;
$("li.sep",f).show();$("li.assign-to-me",f).show();$("li[data-option-id]",f).each(function(){var i=$(this).data("option-id");
var h=$("#agent_online_list > li.agent-"+i);if(h.length||i==DESKPRO_PERSON_ID||i=="0"){$(this).show()}else{$(this).hide()
}});var e=$(g.menu.getOpenTriggerElement());var d=false;if(e.is(".convo_participants")){d=true}else{var c=e.parentsUntil(".convo_participants");
if(c.eq(0).parent().is(".convo_participants")){d=true}}if(d){$("li.agent-0",f).hide();$("li.agent-"+DESKPRO_PERSON_ID,f).hide();
$("li.assign-to-me",f).hide();$("li.sep",f).hide()}},onItemClicked:function(h){var i=$(h.itemEl).data("option-id");var f=$(h.menu.getOpenTriggerElement());
var e=false;if(f.is(".convo_participants")){e=true}else{var d=f.parentsUntil(".convo_participants");if(d.eq(0).parent().is(".convo_participants")){e=true
}}console.log("part %i",e);if(e){var g=$(".convo_participants",this.wrapper);var c=$("li.agent-"+i,g);if(!c.length){$("li.agent-0",g).remove();
$('<li class="agent-'+i+'">'+DeskPRO_Window.getDisplayName("agent",i)+"</li>").appendTo($("ul",g));a.addPart(i)}}else{a.reassignConvo(i);
$("span.agent_id.val",this.wrapper).html(DeskPRO_Window.getDisplayName("agent",i)||"Unassigned")}}});var b=$("ul.end-menu",this.wrapper);
if(b.length){this.endMenu=new DeskPRO.UI.Menu({triggerElement:$("div.chat-status:first",this.wrapper),menuElement:b,onItemClicked:function(c){a.endChat()
}})}},loadQuickReply:function(a){$.ajax({url:BASE_URL+"agent/chat/get-qr/"+this.meta.conversation_id+"/"+a,context:this,contentType:"json",success:function(c){var b=$(".new-message",this.barWrapper);
b.val(b.val()+c.reply).focus()}})},endChat:function(){$.ajax({url:BASE_URL+"agent/chat/end-chat/"+this.meta.conversation_id,context:this,contentType:"json"});
this.addMessageRow("*","Chat ended","sys");this.chatHasEnded()},chatHasEnded:function(){if(this.hasEnded){return}this.hasEnded=true;
var a=$(".chat-status:first",this.wrapper);$(".open",a).hide();$(".ended",a).show();this.barWrapper.hide();this._handleResize()
},addPart:function(a){$.ajax({url:BASE_URL+"agent/chat/add-part/"+this.meta.conversation_id+"/"+a,context:this,contentType:"json"});
this.addMessageRow("*",(DeskPRO_Window.getDisplayName("agent",a))+" joined","sys")},reassignConvo:function(a){$.ajax({url:BASE_URL+"agent/chat/assign/"+this.meta.conversation_id+"/"+a,context:this,contentType:"json"});
this.addMessageRow("*","Chat assigned to "+(DeskPRO_Window.getDisplayName("agent",a)||"Unassigned"),"sys")},handleNewMessage:function(a){DeskPRO_Window.pageTabStrip.alertTab(this.meta.tabIdClass);
this.addMessageRow(a.author_name,a.message,a.author_type);var b=$.tmpl("user_chat_newmsg_sound");b.appendTo(this.wrapper);
DeskPRO_Window.handleSoundElements(b)},addMessageRow:function(a,e,c){if(c=="sys"){a="* "}else{a="&lt;"+a+"&gt; "}var d="";
if(c=="user"){d=" person-overview"}e=Orb.escapeHtml(e);e=Orb.linkUrls(e);var b=['<div class="message '+c+'">'];b.push('<span class="author'+d+'">'+a+"</span>");
b.push('<span class="message">'+e+"</span>");b.push("</div>");$(b.join("")).appendTo($(".chat-messages .messages-wrapper",this.wrapper));
$(".scroll-viewport",this.wrapper).scrollTop(10000)},sendMessage:function(a){$.ajax({url:BASE_URL+"agent/chat/send-message/"+this.meta.conversation_id,data:{content:a},context:this,contentType:"json"})
},personPopoutHtml:null,personPopoutWaiting:false,_initPopout:function(){var a=this;var c=this.wrapper;var b=this.getMetaData("viewPersonUrl");
$.ajax({dataType:"text",url:b,type:"GET",success:function(d){a.personPopoutHtml=d;if(a.personPopoutWaiting){a.personPopoutWaiting=false;
a._initPopoutPageFragment()}}});$(".person-overview",c).css({cursor:"pointer"}).click(function(d){a.isMouseOverPopout=true;
a.openPopOut(d)})},_initPopoutEls_done:false,_initPopoutEls:function(){if(this._initPopoutEls_done){return}this._initPopoutEls_done=true;
var b=this.contentWrapper;var a=this;this.popout=$(".person-popout:first",b);this.popout.click(function(c){c.stopPropagation()
});this.popout.detach().appendTo("body");this.destroyEls.push(this.popout);this.popoutOuter=$(".person-popout-outer:first",b);
this.popoutOuter.detach().appendTo("body");this.destroyEls.push(this.popoutOuter);this.popoutTabs=$(".person-popout-tabs:first",b);
this.popoutTabs.detach().appendTo("body");this.destroyEls.push(this.popoutTabs);var a=this;$(".close:first",this.popoutTabs).click(function(){a.closePopout()
});$(".move-to-tab:first",this.popoutTabs).click(function(){DeskPRO_Window.runPageRouteFromElement($(".person-overview",a.wrapper));
a.closePopout()})},openPopOut:function(d){this._initPopoutEls();if(this.popout.is(":visible")){return}var g=$(".person-overview:first",this.wrapper);
var f=g.offset();var c=this.wrapper.offset();var b=f.left-35;if(b>780){b=780}var a=true;if(b<400){a=false}if(a){this.popout.css({position:"absolute",display:"block","z-index":999998,width:b,overflow:"auto"});
this.popout.css({top:(c.top-8),left:(f.left-this.popout.outerWidth()-20),bottom:30});var e=this.popout.offset();this.popoutOuter.css({position:"absolute",display:"block","z-index":999997,width:b+2+6,overflow:"auto",top:e.top-1,left:e.left-1,bottom:29});
this.popoutTabs.css({"z-index":999996,display:"block",top:(c.top-30),left:(f.left-260)})}if(!this.hasInitPopout&&a){if(this.personPopoutHtml){this._initPopoutPageFragment()
}else{this.personPopoutWaiting=true}}},closePopout:function(){this.popout.hide();this.popoutOuter.hide();this.popoutTabs.hide()
},_initPopoutPageFragment:function(){this.popoutPage=DeskPRO_Window.createPageFragment(this.personPopoutHtml);this.popout.html(this.personPopoutHtml);
this.personPopoutHtml=null;this.popoutPage.initPage(this.popout);this.hasInitPopout=true}});Orb.createNamespace("DeskPRO.Agent.PageFragment.Page");
DeskPRO.Agent.PageFragment.Page.IdeaView=new Class({Extends:DeskPRO.Agent.PageFragment.Page.BasicTicket,TYPENAME:"ticket",popout:null,popout_overview:null,isMouseOverPopout:false,hasInitPopout:false,popoutPage:null,initPage:function(b){this.wrapper=$(b);
this.contentWrapper=$(".layout-content:first",this.wrapper).attr("id",Orb.getUniqueId());var a=this.contentWrapper;a.tinyscrollbar();
$("div.scroll-content:first, div.scroll-viewport:first",this.contentWrapper).resize(function(){a.tinyscrollbar_update()});
DeskPRO_Window.getMessageBroker().sendMessage("ui.tab.opened",{type:"ideas",id:this.getMetaData("idea_id")});this._initEditables();
this._initMenus();this._initComments();$("button.who-voted-trigger",this.wrapper).click(this.showWhoVoted.bind(this));if(this.meta.is_validating){this._initValidating();
if(!this.meta.from_listing_result){this.meta.from_listing_result=""}}},_initValidating:function(){$("button.approve-trigger",this.wrapper).click(this.approveIdea.bind(this));
$("button.disapprove-trigger",this.wrapper).click(this.disapproveIdea.bind(this));$("button.skip-trigger",this.wrapper).click(this.skipValidateIdea.bind(this))
},approveIdea:function(){$.ajax({url:BASE_URL+"agent/ideas/view/"+this.meta.idea_id+"/validate?from_result_id="+this.meta.from_listing_result,type:"POST",context:this,dataType:"json",success:function(b){DeskPRO_Window.getMessageBroker().sendMessage("validating-ideas.approved",{idea_id:this.meta.idea_id});
var a=b.next_url;if(b.next_url){DeskPRO_Window.runPageRoute("page:"+b.next_url)}DeskPRO_Window.removePage(this)}})},disapproveIdea:function(){$.ajax({url:BASE_URL+"agent/ideas/view/"+this.meta.idea_id+"/validate-delete?from_result_id="+this.meta.from_listing_result,type:"POST",context:this,dataType:"json",success:function(b){DeskPRO_Window.getMessageBroker().sendMessage("validating-ideas.deleted",{idea_id:this.meta.idea_id});
var a=b.next_url;if(b.next_url){DeskPRO_Window.runPageRoute("page:"+b.next_url)}DeskPRO_Window.removePage(this)}})},skipValidateIdea:function(){$.ajax({url:BASE_URL+"agent/ideas/view/"+this.meta.idea_id+"/validate-skip?from_result_id="+this.meta.from_listing_result,type:"POST",context:this,dataType:"json",success:function(b){var a=b.next_url;
if(b.next_url){DeskPRO_Window.runPageRoute("page:"+b.next_url)}DeskPRO_Window.removePage(this)}})},_initEditables:function(){var a=$("h3.title.prop:first",this.wrapper);
if(!a.attr("id")){a.attr("id",Orb.getUniqueId())}var b=new DeskPRO.Form.InlineEdit({baseElement:this.wrapper,ajax:{url:BASE_URL+"agent/ideas/view/"+this.meta.idea_id+"/ajax-save-editables"}})
},_initMenus:function(){var a=this;this.catMenu=new DeskPRO.UI.Menu({triggerElement:$(".menu-trigger.category_id:first",this.wrapper),menuElement:$(".menu.category_id:first",this.wrapper),onItemClicked:function(b){a.updateCategory($(b.itemEl).data("option-value"))
}});this.statusMenu=new DeskPRO.UI.Menu({triggerElement:$(".menu-trigger.status:first",this.wrapper),menuElement:$(".menu.status:first",this.wrapper),onItemClicked:function(b){a.updateStatus($(b.itemEl).data("option-value"),$(b.itemEl).data("status-type"))
}})},updateCategory:function(b){var a=$("li.cat-"+b,this.catMenu.getListElement());var c=a.data("full-title");$(".prop-val.category_id",this.wrapper).html(Orb.escapeHtml(c));
$.ajax({url:BASE_URL+"agent/ideas/view/"+this.meta.idea_id+"/ajax-update-category/"+b,type:"POST",context:this,dataType:"json",success:function(d){}})
},updateStatus:function(a,d){var b=$("li.status-"+a,this.statusMenu.getListElement());var h=b.html();var g=$(".prop-val.status",this.wrapper).html(Orb.escapeHtml(h));
var f=g.parent();var c=f.attr("class");c=c.replace(/\bstatus\-(.*?)\b/,"");c+=" status-"+d;f.attr("class",c);if(a!=d){var e=d+"."+a
}else{var e=a}$.ajax({url:BASE_URL+"agent/ideas/view/"+this.meta.idea_id+"/ajax-update-status/"+e,type:"POST",context:this,dataType:"json",success:function(i){}})
},showWhoVoted:function(){var c=$('<div style="width: 650px; height: 400px;"></div>"');var b=new Spinner(c,{radii:[15,9],padding:15}).play();
var a=new DeskPRO.UI.Overlay({contentElement:c,maxWidth:650,destroyOnClose:true});a.openOverlay();$.ajax({url:BASE_URL+"agent/ideas/view/"+this.meta.idea_id+"/who-voted",context:this,dataType:"html",success:function(d){var e=$('<div style="width: 650px; height: 500px;">'+d+"</div>");
a.setContent(e);b.remove();c.remove();this._initWhoVotedEl(a.elements.wrapper)}})},_initWhoVotedEl:function(c){var b=$(".who-voted-controls",c);
var a=this;$(".show-people, .show-guests",b).click(function(){var d=$(".show-people",b).is(":checked");var e=$(".show-guests",b).is(":checked");
if(!d&&!e){if($(this).is(".show-people")){$(".show-guests",b).attr("checked",true)}else{$(".show-people",b).attr("checked",true)
}}if(d){$("table.who-voted",c).addClass("do-show-people")}else{$("table.who-voted",c).removeClass("do-show-people")}if(e){$("table.who-voted",c).addClass("do-show-guests")
}else{$("table.who-voted",c).removeClass("do-show-guests")}})},_initComments:function(){this.commentWrapper=$(".messages-wrap",this.wrapper);
this.newCommentWrapper=$(".new-comment:first",this.wrapper);$("button",this.newCommentWrapper).click(this.submitNewComment.bind(this))
},submitNewComment:function(){var b=$(".loading-on",this.newCommentWrapper).show();var a=$(".loading-off",this.newCommentWrapper).hide();
var c=[];c.push({name:"content",value:$("textarea",this.newCommentWrapper).val()});$.ajax({url:BASE_URL+"agent/ideas/view/"+this.meta.idea_id+"/ajax-save-comment",type:"POST",context:this,data:c,dataType:"html",success:function(d){b.hide();
a.show();$("textarea",this.newCommentWrapper).val("");var e=$(d);this.newCommentWrapper.before(e);this._initMessage(e);this._handleSendReplySuccess(d)
}})},});Orb.createNamespace("DeskPRO.Agent.PageFragment.Page");DeskPRO.Agent.PageFragment.Page.NewsView=new Class({Extends:DeskPRO.Agent.PageFragment.Basic,TYPENAME:"news_view",wrapper:null,article_id:null,initPage:function(b){this.wrapper=b;
this.news_id=this.getMetaData("news_id");this._initBasic();this._initMenus();this._initLabels();this._initEditorEnable();
var a=this.wrapper;a.tinyscrollbar();$("div.scroll-content:first, div.scroll-viewport:first",this.wrapper).resize(function(){a.tinyscrollbar_update()
})},_initBasic:function(){var a=this;var c=$("h3.title.editable:first",this.wrapper);if(!c.attr("id")){c.attr("id",Orb.getUniqueId())
}var d=new DeskPRO.Form.InlineEdit({baseElement:this.wrapper,ajax:{url:BASE_URL+"agent/news/"+this.meta.article_id+"/ajax-save"}});
var b=new DeskPRO.UI.SimpleTabs({context:$(".full-container-tabbed.messages-container",this.contentWrapper),triggerElements:$(".full-container-tabbed-tabs li",this.contentWrapper)})
},_initMenus:function(){this.statusMenu=new DeskPRO.UI.Menu({triggerElement:$(".menu-trigger.status:first",this.wrapper),menuElement:$(".menu.status:first",this.wrapper)})
},labelsList:null,_initLabels:function(){this.labelsList=$(".news-tags ul",this.contentWrapper);this.labelsTagit=this.labelsList.tagit({availableTags:this.getMetaData("labelsAutocompleteUrl"),enableBackspace:false,fieldName:"labels",onchange:this.saveLabels.bind(this)})
},_saveLabelsTimeout:null,saveLabels:function(){if(this._saveLabelsTimeout){window.clearTimeout(this._saveLabelsTimeout)}this._saveLabelsTimeout=this._doSaveLabels.delay(2000,this)
},_doSaveLabels:function(){var a=$(":input",this.labelsList).serializeArray();$.ajax({url:this.getMetaData("labelsSaveUrl"),type:"POST",context:this,data:a,dataType:"json",success:function(b){this._handleSaveLabelsSuccess(b)
}})},_handleSaveLabelsSuccess:function(a){},_initEditorEnable:function(){var a=$(".kb-editor-edit",this.wrapper);a.click(this.showEditor.bind(this))
},showEditor:function(){$(".news-content-wrap",this.wrapper).hide();this._initMarkdownEditor();$(".news-content.tab-content",this.wrapper).addClass("editor-on")
},_initMarkdownEditor:function(){var a=$(".news-editor",this.wrapper).show();var b=$("> textarea",a)},_initMediaBrowser:function(){if(this.mediabrowser_has_init){return
}this.mediabrowser_has_init=true;this.mediaBrowserEl=$(".media-browser",this.wrapper);this.mediaBrowserOverlay=new DeskPRO.UI.Overlay({contentElement:this.mediaBrowserEl});
this.mediaBrowser=new DeskPRO.Agent.MediaBrowser({wrapper:this.mediaBrowserEl,additionalDropZone:$(".kb-editor > textarea",this.wrapper)})
},showMediaBrowser:function(){this._initMediaBrowser();this.mediaBrowserOverlay.openOverlay()}});