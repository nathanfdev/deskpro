Orb.createNamespace("DeskPRO.Agent.PageHelper");DeskPRO.Agent.PageHelper.TicketActionsBar=new Class({page:null,wrapper:null,contentWrapper:null,tableEl:null,selectedActionData:null,ticketBar:null,barWrapper:null,initialize:function(b){this.layout=b.layout;
this.page=b;this.wrapper=this.page.wrapper;this.contentWrapper=this.page.contentWrapper;this.ticketBar=this.page.barWrapper;
this.initOverlay();this.page.changeManager.addEvent("changesCleared",(function(){$("table:first",this.page.contentWrapper).removeClass("preview-mode");
$("tr",this.page.contentWrapper).removeClass("with-line-3").removeClass("faded");$("tr.line-3",this.page.contentWrapper).hide().find("td > ul").html("");
this.page.actionsBarHelper._selectOp("none");this.toggleMacroApplyBtn("off")}).bind(this));var a=this;$("li.macros-apply").click(function(){a.saveActions()
});$(".macros-cancel").click(function(){a._removeTicketIdsToCurrentAction(a.getSelectedTicketIds());a.toggleMacroApplyBtn("off")
})},initOverlay:function(){var a=this;this.actionsWrap=$(".mass-actions:first",this.ticketBar);$("div.overlay-content:first",this.actionsWrap).css({width:$("#pane_list_content").width()+100,"max-height":$("#pane_list_content").height()});
this.actionsOverlay=new DeskPRO.UI.Overlay({contentElement:this.actionsWrap,triggerElement:$("li.actions",this.ticketBar),onBeforeOverlayOpened:function(){var c=$(".check-count span",a.ticketBar);
$(".check-count-overlay",a.actionsWrap).html(c.html())}});$("select.apply-macro-select",this.actionsWrap).change(function(){a.loadMacroActions()
});$(".save-trigger",this.actionsWrap).click((function(){this._loadActions(this.getSelectedTicketIds())}).bind(this));this.actionsEditor=new DeskPRO.Form.RuleBuilder($(".actions-tpl",this.actionsWrap));
this.actionsEditor.addEvent("newRow",function(c){$(".remove",c).click(function(){c.remove()})});var b=$(".actions-form .actions-terms",this.actionsWrap);
$(".actions-form .add-term",this.actionsWrap).data("add-count",0).click(function(){var c=parseInt($(this).data("add-count"));
var d="actions["+c+"]";$(this).data("add-count",c+1);a.actionsEditor.addNewRow(b,d)})},loadMacroActions:function(){var b=parseInt($("select.apply-macro-select",this.actionsWrap).val());
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
if(a==0){$(".check-count span",this.ticketBar).html(0)}else{$(".check-count span",this.ticketBar).html(a)}},_addTicketIdsToCurrentAction:function(a){if(!$.isArray(a)){a=[a]
}this._loadActions(a)},_removeTicketIdsToCurrentAction:function(a){if(!$.isArray(a)){a=[a]}Array.each(a,function(b){this.page.changeManager.revertChangesForTicketId(b)
},this)},_applyButtonCallback:null,createPropertyForTicket:function(e,f){var b=null;var a=/^(.*?)\[(.*?)\]$/.exec(e);if(a!==null){e=a[1];
b=a[2]}var c=this._getPropClass(e,b);if(!c){return false}var d=new c[0](this.page,f,c[1]);return d},_applyButtonClicked:function(){if(this._applyButtonCallback){this._applyButtonCallback()
}},_loadActions:function(e){console.debug("loading indicator TicketActionsBar._loadActions");var b=$(".loading-off").hide();
var a=$(".loading-on").show().empty();var d=new Spinner(a,{radii:[4,8],padding:0}).play();var c=$(":input, select, textarea",$(".actions-terms",this.actionsWrap)).serializeArray();
c.combine($(":input, select, textarea",$(".ticket-reply",this.actionsWrap)).serializeArray());Array.each(e,function(f){c.push({name:"ticket_ids[]",value:f})
});$.ajax({cache:false,type:"GET",data:c,url:BASE_URL+"agent/ticket-search/ajax-preview-actions",context:this,dataType:"json",success:function(f){this.actionsOverlay.closeOverlay();
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
}})}});Orb.createNamespace("DeskPRO.Agent.PageHelper");DeskPRO.Agent.PageHelper.TicketMassActions=new Orb.Class({Implements:[Orb.Util.Options],initialize:function(a){this.options={ticketsWrapper:null,selectionBar:null,changeManager:null};
this.setOptions(a);this.ticketsWrapper=this.options.ticketsWrapper;this.selectionBar=this.options.selectionBar;this.actionsWrap=$(".mass-actions",this.selectionBar.options.selectionBar).first();
this.changeManager=this.options.changeManager;this._applyButtonCallback=null;this.initOverlay()},initOverlay:function(){var a=this;
this.actionsOverlay=new DeskPRO.UI.Overlay({contentElement:this.actionsWrap,onBeforeOverlayOpened:function(c){var b=$("input.ticket-select:checked",a.ticketsWrapper).length;
$(".check-count-overlay",a.actionsWrap).html(b)}});var a=this;$(".radio-option",this.actionsWrap).click(function(){var b=$(this);
if(b.is(".radio-on")){b.removeClass("radio-on")}else{var c=b.data("radio-group");if(c){$("."+c+".radio-option",this.actionsWrap).removeClass("radio-on")
}b.addClass("radio-on")}});$(".reply-check",this.actionsWrap).click(function(){if($(this).is(":checked")){$(".reply-area",a.actionsWrap).slideDown()
}else{$(".reply-area",a.actionsWrap).slideUp()}})},open:function(){this.actionsOverlay.open()},loadMacroActions:function(){var b=parseInt($("select.apply-macro-select",this.actionsWrap).val());
if(!b){return}var a=$(".macro-selector .spinner",this.actionsWrap).show().empty();var c=new Spinner(a,{radii:[4,8],padding:0}).play();
$.ajax({cache:false,type:"POST",data:{macro_id:b},url:BASE_URL+"agent/ticket-search/ajax-get-macro-actions",context:this,dataType:"json",success:function(d){console.log(d);
var e=false;Object.each(d.macro_actions,function(j,f){var h=$(".actions-form .add-term",this.actionsWrap);var g=parseInt(h.data("add-count"));
var i="actions["+g+"]";h.data("add-count",g+1);if(j.type=="reply"){e=j.options.reply}else{this.actionsEditor.addNewRow($(".actions-terms",this.actionsWrap),i,{type:j.type,options:j.options})
}},this);if(e){$("textarea",this.actionsWrap).val(e)}},complete:function(){c.remove();a.empty()}})},getSelectedTicketIds:function(){var a=this.selectionBar.getCheckedValues();
return a},createPropertyForTicket:function(e,f){var b=null;var a=/^(.*?)\[(.*?)\]$/.exec(e);if(a!==null){e=a[1];b=a[2]}var c=this._getPropClass(e,b);
if(!c){return false}var d=new c[0](this.page,f,c[1]);return d},_applyButtonClicked:function(){if(this._applyButtonCallback){this._applyButtonCallback()
}},_loadActions:function(e){console.debug("loading indicator TicketActionsBar._loadActions");var b=$(".loading-off").hide();
var a=$(".loading-on").show().empty();var d=new Spinner(a,{radii:[4,8],padding:0}).play();var c=$(":input, select, textarea",$(".actions-terms",this.actionsWrap)).serializeArray();
c.combine($(":input, select, textarea",$(".ticket-reply",this.actionsWrap)).serializeArray());Array.each(e,function(f){c.push({name:"ticket_ids[]",value:f})
});$.ajax({cache:false,type:"GET",data:c,url:BASE_URL+"agent/ticket-search/ajax-preview-actions",context:this,dataType:"json",success:function(f){this.actionsOverlay.closeOverlay();
d.remove();a.empty().hide();b.show();this.applyActions(f,e)}})},applyActions:function(b,c){var a=this.page.changeManager;
a.begin(c);Object.each(b.ticket_actions,function(e,d){Object.each(e,function(h,g){var f=this.createPropertyForTicket(g,d);
if(!f){return}if(h.value_display){h=h.value_display}a.addChange(f,h)},this)},this);a.applyChanges();this.toggleMacroApplyBtn("on");
this._applyButtonCallback=(function(){this.saveActions();this.toggleMacroApplyBtn("off")}).bind(this)},_getPropClass:function(c,a){var d=null;
var b=null;switch(c){case"department":case"category":case"product":case"priority":case"workflow":case"status":case"agent":case"agent_team":d=DeskPRO.Agent.TicketList.Property.StandardOption;
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
DeskPRO.Agent.PageHelper.TicketDisplay=new Orb.Class({Implements:[Orb.Util.Options,Orb.Util.Events],initialize:function(b,a){this.options={wrapper:null,holders:".page-display-holders:first",inputHolders:".page-display-input:first",fieldWrapSelector:".display-item",fieldTabSelector:"li.field-tab",fieldTabContentSelector:".field-tab-content"};
this.setOptions(a);this.wrapper=$(this.options.wrapper||b.wrapper);this.holders=$(this.options.holders,this.wrapper);this.inputHolders=$(this.options.inputHolders,this.wrapper);
this.sectionProperties=b.getEl("fields_display_main_wrap");this.sectionPropertiesContent=b.getEl("fields_display_main");this.sectionPropertiesWrapTpl=b.getEl("fields_display_main_wrap_tpl").get(0).innerHTML;
this.sectionPropertiesEditTpl=b.getEl("fields_display_main_edit_tpl").get(0).innerHTML;this.sectionPropertiesEditRowTpl=b.getEl("fields_display_main_edit_row_tpl").get(0).innerHTML;
this.sectionBodyTabs=b.getEl("ticket_tabs");this.sectionBodyTabContents=b.getEl("ticket_tabs_content");this.sectionBodyTabsTabTpl=b.getEl("fields_display_tabs_tab_tpl").get(0).innerHTML;
this.sectionBodyTabsTabContentTpl=b.getEl("fields_display_tabs_content_tpl").get(0).innerHTML;this.sectionBodyTabsWrapTpl=b.getEl("fields_display_tabs_wrap_tpl").get(0).innerHTML;
this.departmentId=null;this.page=b;this.page.changeManager.addEvent("updateResult",this.handleChangeUpdateResult.bind(this));
this.setDepartment(parseInt($("input.department_id",this.wrapper).val()||0));$(".edit-fields-trigger",this.sectionProperties).click((function(){this.enableEditMode("default")
}).bind(this))},handleChangeUpdateResult:function(a){if(a.holders){this.replaceHolders(a.holders);this.setDepartment(parseInt($("input.department_id",this.wrapper).val()||0),true)
}},replaceHolders:function(b){var a=$(b);this.holders.remove();this.holders=a.filter(".page-display-holders");this.inputHolders.remove();
this.inputHolders=$(a).filter(".page-display-input");this.wrapper.append(this.holders);this.wrapper.append(this.inputHolders)
},clearAll:function(){$(this.options.fieldTabSelector,this.sectionBodyTabs).remove();$(this.options.fieldTabContentSelector,this.sectionBodyTabContents).remove();
$(this.options.fieldWrapSelector,this.sectionPropertiesContent).remove();this.sectionProperties.hide();$(".fields-show",this.sectionProperties).show();
var a=$(this.sectionPropertiesEditTpl);$(".close-trigger",a).click((function(b){b.preventDefault();this.closeEditMode("default")
}).bind(this));$(".save-trigger",a).click((function(b){b.preventDefault();this.saveEditMode("default")}).bind(this));$(".fields-edit",this.sectionProperties).empty().hide().append(a)
},setDepartment:function(c,b){c=parseInt(c);console.log("Setting %i",c);this.clearAll();if(c==this.departmentId&&!b){return
}this.departmentId=c;if(!window.DESKPRO_TICKET_DISPLAY||!window.DESKPRO_TICKET_DISPLAY[c]){return}var a=window.DESKPRO_TICKET_DISPLAY[c];
console.log("depItems %o",a);Array.each(a,function(n){switch(n.section){case"default":var g=this.getItemHolderEls(n);if(!g){return
}this.sectionProperties.show();var j=this.getItemId(n);if(1||!g.itemHolder.is(".no-value")){if(g.itemHolder.data("custom-field-handler")){n.custom_field_handler=g.itemHolder.data("custom-field-handler")
}var k=$(this.sectionPropertiesWrapTpl);g.itemTitle.detach().appendTo($(".display-title",k));g.itemContent.detach().appendTo($(".display-content",k));
k.appendTo(this.sectionPropertiesContent);n.sectionEl=this.sectionPropertiesContent;g.itemHolder.remove()}var i=$(".fields-edit-container",this.sectionProperties);
var h=$("> ."+j,this.inputHolders);var o=$("> .title",h);var m=$("> .content",h);var f=$(this.sectionPropertiesEditRowTpl);
o.detach().appendTo($(".display-title",f));m.detach().appendTo($(".display-content",f));f.appendTo($(".fields-edit-rows",i));
break;case"bodytabs":if(!n.items||!n.items.length){n.items=[]}var d="bodytabfieldtab_"+$(this.options.fieldTabSelector,this.sectionBodyTabs).length+1;
var e=$(this.sectionBodyTabsTabTpl.replace(/\{title\}/g,n.title).replace(/\{id\}/g,d));var l=$(this.sectionBodyTabsTabContentTpl.replace(/\{id\}/g,d));
e.appendTo(this.sectionBodyTabs);l.appendTo(this.sectionBodyTabContents);Array.each(n.items,function(q){q.section="bodytabs";
var r=this.getItemHolderEls(q);if(!r){return}if(r.itemHolder.data("custom-field-handler")){n.custom_field_handler=r.itemHolder.data("custom-field-handler")
}var p=$(this.sectionBodyTabsWrapTpl);r.itemTitle.detach().appendTo($(".display-title",p));r.itemContent.detach().appendTo($(".display-content",p));
p.appendTo(l);q.sectionEl=l;r.itemHolder.remove();$(".edit-fields-trigger",p).click((function(){this.enableEditMode("default")
}).bind(this))},this);break}},this);this.updateSectionDisplay()},enableEditMode:function(c){var b=$(".fields-show",this.sectionProperties);
var a=$(".fields-edit",this.sectionProperties);b.hide();a.show()},closeEditMode:function(c){var b=$(".fields-show",this.sectionProperties);
var a=$(".fields-edit",this.sectionProperties);a.hide();b.show()},saveEditMode:function(e){var b=$(".fields-edit",this.sectionProperties);
b.addClass("loading");var a=this.page.changeManager;$("[data-prop-id]",b).each(function(){var f=a.getPropertyManager($(this).data("prop-id"));
f.setValue($(this).val());a.addChange(f)});var c=$(".custom-field input, .custom-field textarea, .custom-field select",b).serializeArray();
a.saveChanges(c,(function(){this.closeEditMode()}).bind(this));var d=$("input, textarea, select",b).serializeArray()},runRules:function(){var c=window.DESKPRO_TICKET_DISPLAY[department_id];
if(!c){return}var b={getCategoryId:function(){if(this.categoryId){return this.categoryId}this.categoryId=parseInt($("input.category_id",this.wrapper).val()||0);
return this.categoryId},getProductId:function(){if(this.productId){return this.productId}this.productId=parseInt($("input.product_id",this.wrapper).val()||0);
return this.productId},getPriorityId:function(){if(this.priorityId){return this.priorityId}this.priorityId=parseInt($("input.priority_id",this.wrapper).val()||0);
return this.priorityId},getWorkflowId:function(){if(this.workflowId){return this.workflowId}this.workflowId=parseInt($("input.workflow_id",this.wrapper).val()||0);
return this.workflowId}};var a=[];Array.each(items,function(d){switch(d.section){case"default":var e=this.runCheckForItem(d);
if(e){a.push(e)}break;case"bodytabs":Array.each(d.items,function(f){var g=this.runCheckForItem(f);if(g){a.push(g)}},this);
break}},this);Array.each(a,function(d){if(d[2]=="visible"){d[1].show()}else{d[1].hide()}});this.updateSectionDisplay()},updateSectionDisplay:function(){if($(this.options.fieldWrapSelector+":first",this.sectionPropertiesContent).length){this.sectionProperties.show()
}else{this.sectionProperties.hide()}var a=this.sectionBodyTabContents;var b=this.options.fieldWrapSelector+":first";$("li.field-tab",this.sectionBodyTabs).each(function(){var d=$(this).data("field-tab-id");
var c=$("> ."+d,a);if($(b,c).length){$(this).show()}else{$(this).hide()}})},runCheckForItem:function(a){var c=this.getItemId(a);
if(a.initial_display=="visible"){var b=true}else{var b=false}if(a.check&&a.check(ticketReader)){b=!b}return[c,$("> ."+c,a.sectionEl),b]
},getItemHolderEls:function(c){var e=this.getItemId(c);var b=$("> ."+e+":first",this.holders);if(!b||!b.length){return}var a=$("> .title:first",b);
var d=$("> .content:first",b);return{itemHolder:b,itemTitle:a,itemContent:d}},getItemId:function(a){var b=a.item_type;if(a.item_id){b+="_"+a.item_id
}return b}});Orb.createNamespace("DeskPRO.Agent.PageHelper");DeskPRO.Agent.PageHelper.ListSearchForm=new Orb.Class({Implements:[Orb.Util.Events,Orb.Util.Options],initialize:function(b,a){this.page=b;
this.options={form:null,context:null,searchData:null};this.setOptions(a);this.form=this.options.form;this.topSection=this.options.context;
this._initSearchOptions()},_initSearchOptions:function(){var c=$(".summary .edit",this.topSection);c.click(this.showSearchForm.bind(this));
var b=this.form;var a=this;b.submit(function(e){e.preventDefault();var d=b.attr("action");var f=b.serializeArray();a.fireEvent("searchSubmit",[d,f])
})},showSearchForm:function(){var d=$(".search-form",this.topSection);var e=$(".search-builder-tpl",this.topSection);var b=new DeskPRO.Form.RuleBuilder(e);
$(".add-term",d).data("add-count",0).click(function(){var f=parseInt($(this).data("add-count"));var g="terms["+f+"]";$(this).data("add-count",f+1);
b.addNewRow($(".search-terms",d),g)});var c=this.searchData;if(c&&c.length){var a=c.get(0).innerHTML;a=$.parseJSON(a);if(a.terms){Array.each(a.terms,function(h,f){var g="terms[initial_"+f+"]";
b.addNewRow($(".search-terms",d),g,{type:h.type,op:h.op,options:h.options})})}c.remove()}$(".summary",this.topSection).slideUp();
$(".form-panel",this.topSection).slideDown()}});Orb.createNamespace("DeskPRO.Agent.PageHelper");DeskPRO.Agent.PageHelper.CategoryEdit=new Orb.Class({Implements:[Orb.Util.Events,Orb.Util.Options],initialize:function(a){this.options={wrapper:null};
this.setOptions(a||{});this.wrapper=$(this.options.wrapper)},_init:function(){if(this.hasInit){return}this.hasInit=true;this.wrapper.detach().appendTo("body");
this.wrapper.click(function(b){b.stopPropagation()});$(".close-trigger",this.wrapper).click((function(b){this.close()}).bind(this));
$(".new-close-trigger",this.wrapper).click((function(b){this.closeNewDlg()}).bind(this));$("ul",this.wrapper).sortable({axis:"y",items:"> li"});
this.backdrop=$(".backdrop:first",this.wrapper);this.newDlg=$(".new-dlg:first",this.wrapper);this.newParent=$("select:first",this.newDlg);
this.newTitle=$("input:first",this.newDlg);$(".add-save-trigger",this.newDlg).click((function(){this.addNewToList()}).bind(this));
$(".add-trigger:first",this.wrapper).click((function(){this.openNewDlg()}).bind(this));$(".save-all-trigger",this.wrapper).click((function(){this.fireEvent("save",[this]);
this.close()}).bind(this));var a=this;this.wrapper.delegate(".title","dblclick",function(){a.enableEditTitle($(this))})},open:function(){this._init();
if(this.wrapper.is(".open")){return}this.wrapper.css({position:"absolute",left:40,top:150});this.wrapper.addClass("open").fadeIn()
},close:function(){this.closeNewDlg();this.wrapper.removeClass("open").fadeOut()},openNewDlg:function(){var a=[];$("li > .title",this.wrapper).each(function(){var f=$(this);
var h=parseInt(f.data("depth"));var e=f.data("cat-id");var g=f.text().trim();if(h){g=Orb.strRepeat("--",h)+" "+g}a.push('<option value="'+e+'">'+Orb.escapeHtml(g)+"</option>")
});a=a.join("");$("option:not(.none)",this.newParent).remove();$("option.none",this.newParent).after($(a));var c=50;var b=65;
var d=this.newDlg;d.css({display:"absolute",top:c,left:b});this.backdrop.fadeIn("fast");d.fadeIn()},closeNewDlg:function(){var a=this.newDlg;
a.fadeOut("fast");this.backdrop.fadeOut("fast")},addNewToList:function(){var h=this.newParent.val();var f=this.newTitle.val().trim();
if(!f.length){return}this.newTitle.val("");var b,e=0,d=false;if(h&&h!="0"){d=$("li.cat-"+h,this.wrapper)}if(d&&d.length){e=parseInt($("> .title",d).data("depth"))+1;
b=$("> ul",d);if(!b.length){var c=$("<ul></ul>");d.append(c);c.sortable({axis:"y",items:"> li"})}b=$("> ul",d)}else{b=$("ul.top:first",this.wrapper)
}var g="new_"+Orb.uuid();var a='<li class="cat-'+g+'"><div class="title new" data-cat-id="'+g+'" data-depth="'+e+'">'+Orb.escapeHtml(f)+"</div></li>";
b.append(a);this.closeNewDlg()},enableEditTitle:function(c){var b=$("<input />");b.val(c.text().trim());c.empty().append(b);
var d=$('<button class="dp-button x-small">Apply</button>');c.append(d);var a=this;b.keypress(function(e){if(e.keyCode==13){e.preventDefault();
a._applyEditTitle(c,b)}});d.click(function(){a._applyEditTitle(c,b)})},_applyEditTitle:function(b,a){b.empty().text(a.val())
},encode:function(){var a=[];this._encodeSet(a,$("ul.top",this.wrapper),0);return a},_encodeSet:function(c,b,d){var a=this;
$("li",b).each(function(){var f=$(this);var j=$("> .title",f);var h=j.data("cat-id");var i=c.length+1;var e=j.is(".new");
c.push({id:h,parentId:d,isNew:e,displayOrder:i,title:j.text().trim()});var g=$("> ul",f);if(g.length){a._encodeSet(c,g,h)
}})},encodeForm:function(a){if(!a){a="cats"}var d=this.encode();var c=[];var b=0;Array.each(d,function(e){Object.each(e,function(g,f){if(f=="isNew"){if(g){g=1
}else{g=0}}c.push({name:a+"["+b+"]["+f+"]",value:g})});b++});return c},destroy:function(){if(this.hasInit){this.wrapper.remove()
}}});Orb.createNamespace("DeskPRO.Agent.PageHelper");DeskPRO.Agent.PageHelper.DisplayOptions=new Orb.Class({Implements:[Orb.Util.Events,Orb.Util.Options],initialize:function(e,c){var b=this;
this.page=e;this.options={triggerElement:null,resultId:0,prefId:"",refreshUrl:""};this.setOptions(c);if(!this.options.triggerElement){this.options.triggerElement=$(".display-options-trigger:first",this.page.wrapper)
}$(this.options.triggerElement).click((function(){this.open()}).bind(this));var a=$("button.order-by-trigger",this.page.wrapper);
var d=$("ul.order-by-menu",this.page.wrapper);if(a.length&&d.length){this.orderByMenu=new DeskPRO.UI.Menu({triggerElement:a,menuElement:d,onItemClicked:(function(j){var g=$(j.itemEl);
var k=g.data("field");var f=g.text().trim();$(".label",a).text(f);var i=b.getWrapperElement();var h=$("select.sel-order-by",i);
$("option",h).prop("selected",false);$("option."+k.replace(".","_"),h).prop("selected",true);b.saveAndRefresh()}).bind(this)})
}this.page.addEvent("destroy",(function(){this.destroy()}).bind(this))},_initOverlay:function(){if(this._hasInit){return}this._hasInit=true;
var a=$(".display-options:first",this.page.wrapper);var b=$("ul.sortable-list",a);this.overlay=new DeskPRO.UI.Overlay({contentElement:a,onContentSet:function(c){b.sortable({axis:"y"})
}});$(".save-trigger",a).click((function(){this.saveDisplayOptions()}).bind(this))},saveDisplayOptions:function(){$(".loading-off",this.overlay.elements.wrapper).hide();
$(".loading-on",this.overlay.elements.wrapper).show();this.saveAndRefresh()},saveAndRefresh:function(){var c=this.getWrapperElement();
var d=[];var a="prefs[agent.ui."+this.options.prefId+"-display-fields."+this.options.resultId+"][]";$('input[type="checkbox"]:checked',c).each(function(){d.push({name:a,value:$(this).attr("name")})
});d.push({name:"prefs[agent.ui."+this.options.prefId+"-order-by."+this.options.resultId+"]",value:$('select[name="order_by"]',c).val()});
var b=this.options.refreshUrl;$.ajax({timeout:20000,type:"POST",url:BASE_URL+"agent/misc/ajax-save-prefs",data:d,context:this,success:function(){DeskPRO_Window.loadListPane(b)
}})},open:function(){this._initOverlay();this.overlay.open()},close:function(){if(this.overlay){this.overlay.close()}},getWrapperElement:function(){if(this.overlay){return $(this.overlay.elements.wrapper)
}else{return $(".display-options:first",this.page.wrapper)}},destroy:function(){if(this.overlay){this.overlay.destroy()}}});
Orb.createNamespace("DeskPRO.Agent.PageHelper");DeskPRO.Agent.PageHelper.SelectionBar=new Orb.Class({Implements:[Orb.Util.Events,Orb.Util.Options],initialize:function(c,b){var a=this;
this.page=c;this.options={selectionBar:null,selectedCount:null,button:null};this.setOptions(b);if(!this.options.selectionBar){this.options.selectionBar=$(".selection-bar",this.page.wrapper).first()
}this.selectionBar=$(this.options.selectionBar);if(!this.options.selectedCount){this.options.selectedCount=$(".selected-count:first",this.selectionBar)
}this.selectedCount=$(this.options.selectedCount);if(!this.options.button){this.options.button=$(".perform-actions-trigger:first",this.selectionBar)
}this.button=$(this.options.button);this.button.addClass("disabled");this.button.click(this.buttonClicked.bind(this));this.controlCheck=$(".selection-control",this.page.wrapper).click(function(){if($(this).is(":checked")){a.checkAll()
}else{a.checkNone()}});this.page.wrapper.delegate("input.item-select","click",function(){var d=$(this);a.handleCheckChange(d,d.is(":checked"))
})},buttonClicked:function(){if(this.button.is(".disabled")){return}this.fireEvent("buttonClick")},getCheckedValues:function(){var a=[];
$("input.item-select:checked",this.page.wrapper).each(function(){a.push($(this).val())});return a},getCheckedFormValues:function(b,a){a=a||[];
$("input.item-select:checked",this.page.wrapper).each(function(){a.push({name:b,value:$(this).val()})});return a},getChecked:function(){return $("input.item-select:checked",this.page.wrapper)
},getCount:function(){return $("input.item-select:checked",this.page.wrapper).length},checkAll:function(){$("input.item-select",this.page.wrapper).attr("checked",true);
var a=this.getCount();this.selectedCount.text(a);if(a>0){this.button.removeClass("disabled");this.controlCheck.attr("checked",true)
}else{this.controlCheck.attr("checked",false)}},checkNone:function(){$("input.item-select:checked",this.page.wrapper).attr("checked",false);
var a=this.getCount();this.selectedCount.text(a);this.button.addClass("disabled");this.controlCheck.attr("checked",false)
},handleCheckChange:function(b,a){var c=this.getCount();this.selectedCount.text(c);if(c>0){this.button.removeClass("disabled")
}else{this.button.addClass("disabled")}if($("input.item-select:not(:checked):first",this.page.wrapper).length){this.controlCheck.attr("checked",false)
}else{this.controlCheck.attr("checked",true)}this.fireEvent("checkChange",[b,a,c])}});Orb.createNamespace("DeskPRO.Agent.PageHelper");
DeskPRO.Agent.PageHelper.Popover_Instances={};DeskPRO.Agent.PageHelper.Popover=new Orb.Class({Implements:[Orb.Util.Events,Orb.Util.Options],initialize:function(a){this.options={loadTimeout:0,pageUrl:"",pageCallback:null,tabRoute:false,destroyOnClose:false,overFrom:"#dp_content"};
this.id=Orb.uuid();DeskPRO.Agent.PageHelper.Popover_Instances[this.id]=this;this.setOptions(a);this.pageSource=null;this.page=null;
this.isWaiting=false;this.popover=null;this.popoverOuter=null;if(this.options.loadTimeout){this.autoloadTimeout=window.setTimeout(this._loadPage.bind(this),this.options.loadTimeout)
}},_loadPage:function(){if(this.options.pageCallback){return this.options.pageCallback(this.setHtml.bind(this))}if(!this.options.pageUrl){return
}if(this._isLoading){return}this._isLoading=true;if(this.autoloadTimeout){window.clearTimeout(this.autoloadTimeout);this.autoloadTimeout=null
}$.ajax({dataType:"text",url:this.options.pageUrl,type:"GET",context:this,success:function(a){this._isLoading=false;this.setHtml(a)
}})},setHtml:function(a){this.pageSource=a;if(this.isWaiting){this.isWaiting=false;this._initFragment();this.open()}},_initPopover:function(){if(this._hasInit){return
}this._hasInit=true;this.popoverOuter=$($("#popover_tpl").get(0).innerHTML);this.popover=$(".popover-inner",this.popoverOuter).first();
this.popoverOuter.detach().appendTo("body");this.popoverOuter.click(function(d){d.stopPropagation()});var c=$(this.options.overFrom).offset();
var b=c.top;var a=c.left-30;this.popoverOuter.css({position:"absolute",display:"none","z-index":999997,width:a+2+6,overflow:"auto",top:b-3,left:9,bottom:20});
$(".close",this.popoverOuter).first().click((function(d){d.preventDefault();d.stopPropagation();this.isWaiting=false;var d={pop:this,cancel:false};
this.fireEvent("closeTabClick",d);if(d.cancel){return}this.close()}).bind(this));if(this.options.tabRoute){$(".move-to-tab:first",this.popoverTabs).click((function(d){d.preventDefault();
d.stopPropagation();this.isWaiting=false;DeskPRO_Window.runPageRoute(this.options.tabRoute);this.close()}).bind(this))}else{$(".move-to-tab:first",this.popoverTabs).remove()
}},_initFragment:function(){if(this.page){return}if(!this.pageSource){return}this.page=DeskPRO_Window.createPageFragment(this.pageSource);
this.popover.html(this.pageSource);this.page.initPage(this.popover);this.pageSource=null;this.fireEvent("pageInit",[this,this.page])
},isOpen:function(){if(this.popover&&this.popover.is(":visible")){return true}return false},open:function(){this._initPopover();
this._initFragment();if(!this.page&&!this.pageSource){this.isWaiting=true;this._loadPage()}if(this.isOpen()){return}Object.each(DeskPRO.Agent.PageHelper.Popover_Instances,function(a){if(a.isOpen()){a.close()
}});this.popoverOuter.show()},toggle:function(){if(this.isOpen()){this.close()}else{this.open()}},close:function(){var a={pop:this,cancel:false};
this.fireEvent("close",a);if(a.cancel){return}this.popoverOuter.hide();if(this.options.destroyOnClose){this.destroy()}},destroy:function(){if(this.page){this.page.fireEvent("destroy")
}if(this.popover){this.popoverOuter.remove()}delete DeskPRO.Agent.PageHelper.Popover_Instances[this.id]}});Orb.createNamespace("DeskPRO.Agent.PageHelper");
DeskPRO.Agent.PageHelper.ValidatingEdit=new Orb.Class({Implements:[Orb.Util.Events,Orb.Util.Options],initialize:function(c,b){var a=this;
this.page=c;this.options={typename:"",contentId:0,singleType:""};this.setOptions(b);$("button.approve-trigger",this.page.wrapper).click(this.approveEdit.bind(this));
$("button.disapprove-trigger",this.page.wrapper).click(this.showDisapproveForm.bind(this));$("button.disapprove2-trigger",this.page.wrapper).click(this.disapproveEdit.bind(this));
$("button.skip-trigger",this.page.wrapper).click(this.skipValidateEdit.bind(this))},showDisapproveForm:function(){$(".validating-bar:first .options",this.page.wrapper).hide();
$(".validating-bar:first .disapprove-form",this.page.wrapper).show()},approveEdit:function(){$.ajax({url:BASE_URL+"agent/publish/content/approve/"+this.options.typename+"/"+this.options.contentId+".json?specific_type="+this.options.singleType,type:"POST",context:this,dataType:"json",success:function(a){if(a.next_url){DeskPRO_Window.runPageRoute("page:"+a.next_url)
}DeskPRO_Window.getMessageBroker().sendMessage("publish.validating.list-remove",{typename:this.options.typename,contentId:this.options.contentId});
DeskPRO_Window.removePage(this.page)}})},disapproveEdit:function(){var a=$(".validating-bar .disapprove-reason",this.page.wrapper).val().trim();
$.ajax({url:BASE_URL+"agent/publish/content/disapprove/"+this.options.typename+"/"+this.options.contentId+".json?specific_type="+this.options.singleType,type:"POST",context:this,data:{reason:a},dataType:"json",success:function(b){if(b.next_url){DeskPRO_Window.runPageRoute("page:"+b.next_url)
}DeskPRO_Window.getMessageBroker().sendMessage("publish.validating.list-remove",{typename:this.options.typename,contentId:this.options.contentId});
DeskPRO_Window.removePage(this.page)}})},skipValidateEdit:function(){$.ajax({url:BASE_URL+"agent/publish/content/get-next-validating/"+this.options.typename+"/"+this.options.contentId+".json?specific_type="+this.options.singleType,type:"POST",context:this,dataType:"json",success:function(a){if(a.next_url){DeskPRO_Window.runPageRoute("page:"+a.next_url)
}DeskPRO_Window.getMessageBroker().sendMessage("publish.validating.list-remove",{typename:this.options.typename,contentId:this.options.contentId});
DeskPRO_Window.removePage(this.page)}})}});Orb.createNamespace("DeskPRO.Agent.PageHelper");DeskPRO.Agent.PageHelper.RelatedContent=new Orb.Class({Implements:[Orb.Util.Events,Orb.Util.Options],initialize:function(c,b){var a=this;
this.page=c;this.options={typename:"",content_id:0,listEl:null,newItemTpl:"#related_content_newitem"};this.setOptions(b);
this.listEl=$(this.options.listEl);this.listEl.delegate(".remove","click",function(f){f.stopPropagation();f.preventDefault();
var e=$(this);var d=0;while(!e.is(".related-content")){if(d++>15){return}e=e.parent()}a.removeLinkByElement(e)})},_refreshInstructionEl:function(){if($(".related-content:not(.removing):first",this.listEl).length){$(".no-related-content",this.listEl).hide()
}else{$(".related-section",this.listEl).hide();$(".no-related-content",this.listEl).show()}},setActiveRelatedListController:function(a){this.relatedContentList=a
},addLink:function(d,c,f,b){if(this.isLinked(d,c)){return}var a=$(DeskPRO_Window.util.getPlainTpl(this.options.newItemTpl));
$(".link-title",a).text(f);$(".link-route",a).data("route",b);a.addClass("related-content").addClass(d+"-"+c);a.data("content-type",d);
a.data("content-id",c);a.hide();var g=$("."+d+".related-section",this.listEl);if(g.length){var e=$(".related-list:first",g);
e.append(a);if(!g.is(":visible")){a.show();g.slideDown("fast")}else{a.slideDown("fast")}}else{if(!this.listEl.is(":visible")){a.show();
this.listEl.append(a).slideDown("fast")}else{this.listEl.append(a);a.slideDown("fast")}}if(this.relatedContentList){this.relatedContentList.elementIsLinked(d,c)
}this._refreshInstructionEl();this.fireEvent("contentLinked",[d,c,f,b,this])},addLinkByElement:function(e){var d=e.data("content-type");
var b=e.data("content-id");if(e.data("route")){var a=e.data("route");var f=e.text().trim()}else{var c=$("[data-route]:first",e);
var a=c.data("route");var f=c.text().trim()}return this.addLink(d,b,f,a)},removeLinkByElement:function(c){var b=c.data("content-type");
var a=c.data("content-id");if(!b||!a){console.warn("No content linked on element: %o",c);return false}this.removeLink(b,a);
return true},removeLink:function(b,a){var c=this.getLinkElementInList(b,a);if(!c){return}c.addClass("removing").slideUp("fast",function(){c.remove();
var d=$("."+b+".related-section",this.listEl);if(d.length){if(!$(".related-content:first",d).length){d.slideUp("fast")}}});
if(this.relatedContentList){this.relatedContentList.elementIsUnlinked(b,a)}this._refreshInstructionEl();this.fireEvent("contentUnlinked",[b,a,this])
},getLinkElementInList:function(c,a){var b=$("."+c+"-"+a+".related-content:first",this.listEl);if(!b.length){return null}return b
},isLinked:function(b,a){return $("."+b+"-"+a+".related-content:first",this.listEl).length},isLinkable:function(b,a){if(b==this.options.typename&&a==this.options.content_id){return false
}var c={linkable:true};this.fireEvent("checkLinkable",[c,b,a,this]);if(c.linkable){return true}else{return false}},elementIsLinkable:function(a){return this.isLinkable(a.data("content-type"),a.data("content-id"))
},elementIsLinked:function(a){return this.isLinked(a.data("content-type"),a.data("content-id"))}});Orb.createNamespace("DeskPRO.Agent.PageHelper");
DeskPRO.Agent.PageHelper.RelatedContentList=new Orb.Class({Implements:[Orb.Util.Events,Orb.Util.Options],initialize:function(g,d){var c=this;
this.page=g;this.options={contentListEl:null};this.setOptions(d);this.contentListEl=$(this.options.contentListEl);this.addEvent("watchedTabActivated",this.enableControls.bind(this));
this.addEvent("watchedTabDeactivated",this.disableControls.bind(this));var f=DeskPRO_Window.getTabWatcher().getActiveTabType();
var b=false;var e=["article","download","news","idea"];Array.each(e,function(h){DeskPRO_Window.getTabWatcher().addTabTypeWatcher(h,this);
if(f==h){b=true}},this);if(b){this.enableControls(DeskPRO_Window.getTabWatcher().getActiveTab())}var a=function(i){var h=0;
while(!i.is(".related-is-linkable")){if(h++>15){return null}i=i.parent()}return i};this.contentListEl.delegate(".related-link","click",function(j){j.stopPropagation();
j.preventDefault();var i=a($(this));if(!i){return}c.fireEvent("relatedLinkClick",[i,$(this),this]);var h=DeskPRO_Window.getTabWatcher().getActiveTab();
if(!h||!h.page||!h.page.relatedContent){return}h.page.relatedContent.addLinkByElement(i)});this.contentListEl.delegate(".related-unlink","click",function(j){j.stopPropagation();
j.preventDefault();var i=a($(this));if(!i){return}c.fireEvent("relatedUnlinkClick",[i,$(this),this]);var h=DeskPRO_Window.getTabWatcher().getActiveTab();
if(!h||!h.page||!h.page.relatedContent){return}h.page.relatedContent.removeLinkByElement(i)})},enableControls:function(b){var c=b.page;
if(!c.relatedContent){return}this.activePage=c;c.relatedContent.setActiveRelatedListController(this);var a=this;$(".related-is-linkable",this.contentListEl).each(function(){var d=$(this);
d.removeClass("related-not-linkable").removeClass("related-is-linked");if(c.relatedContent.elementIsLinkable(d)){if(c.relatedContent.elementIsLinked(d)){d.addClass("related-is-linked")
}}else{d.addClass("related-not-linkable")}});this.contentListEl.addClass("with-related-content-controls");this.fireEvent("relatedControlsActivated",[this.contentListEl,this])
},disableControls:function(){this.activePage=null;this.contentListEl.removeClass("with-related-content-controls");this.fireEvent("relatedControlsDeactivated",[this.contentListEl,this])
},elementIsLinked:function(b,a){$("."+b+"-"+a+".related-is-linkable",this.contentListEl).addClass("related-is-linked")},elementIsUnlinked:function(b,a){$("."+b+"-"+a+".related-is-linkable",this.contentListEl).removeClass("related-is-linked")
},destroy:function(){if(this.activePage){this.activePage.relatedContent.setActiveRelatedListController(null)}}});Orb.createNamespace("DeskPRO.Agent.PageHelper");
DeskPRO.Agent.PageHelper.Comments=new Orb.Class({Implements:[Orb.Util.Events,Orb.Util.Options],initialize:function(c,b){var a=this;
this.page=c;this.options={commentsWrapper:null};this.setOptions(b);this.commentsWrapper=$(this.options.commentsWrapper);this.commentsWrapper.delegate(".comment-edit-btn","click",function(d){d.stopPropagation();
d.preventDefault();a.getCommentMenu().open(d)});this.commentsWrapper.delegate(".comment-validate-btn","click",function(d){d.stopPropagation();
d.preventDefault();a.getCommentValidationMenu().open(d)})},getCommentMenu:function(){if(this._commentMenu){return this._commentMenu
}var a=this;this._commentMenu=new DeskPRO.UI.Menu({menuElement:$("#comment_tools_menu"),onItemClicked:function(c){var d=$(c.menu.getOpenTriggerElement()).parent().parent().parent();
var b=$(c.itemEl).data("action");switch(b){case"edit":a.editComment(d,d.data("content-type"),d.data("comment-id"));break;
case"delete":a.deleteComment(d,d.data("content-type"),d.data("comment-id"));break;case"create-ticket":$.ajax({url:BASE_URL+"agent/publish/comments/new-ticket-info/"+d.data("content-type")+"/"+d.data("comment-id")+".json",type:"GET",dataType:"json",success:function(e){DeskPRO_Window.newTicketLoader.open(function(f){f.setNewByComment(e)
})}});break}}});return this._commentMenu},getCommentValidationMenu:function(){if(this._commentValidationMenu){return this._commentValidationMenu
}var a=this;this._commentValidationMenu=new DeskPRO.UI.Menu({menuElement:$("#comment_validation_menu"),onItemClicked:function(c){var d=$(c.menu.getOpenTriggerElement()).parent().parent().parent();
var b=$(c.itemEl).data("action");switch(b){case"approve":a.approveComment(d,d.data("content-type"),d.data("comment-id"));
break;case"delete":a.deleteComment(d,d.data("content-type"),d.data("comment-id"));break}}});return this._commentValidationMenu
},editComment:function(d,c,b){var a=this;$.ajax({url:BASE_URL+"agent/publish/comments/info/"+c+"/"+b,type:"GET",dataType:"json",context:this,success:function(f){var e=$(DeskPRO_Window.util.getPlainTpl("#comment_edit_tpl"));
$(".save-trigger",e).click(function(h){h.preventDefault();a._saveEditComment(d,e,c,b)});$(".cancel-trigger",e).click(function(h){h.preventDefault();
a._closeEditComment(d,e)});e.hide();$("textarea.comment",e).val(f.comment_text);var g=$(".rendered-message",d);e.insertBefore(g);
g.slideUp("fast",function(){e.slideDown("fast")})}})},_saveEditComment:function(d,b,c,a){$.ajax({url:BASE_URL+"agent/publish/comments/save-comment/"+c+"/"+a,type:"POST",data:{comment:$("textarea.comment",b).val()},dataType:"json",context:this,error:function(){},success:function(e){var f=$(".rendered-message",d);
f.html(e.comment_html);this._closeEditComment(d,b)}})},_closeEditComment:function(c,a){var b=$(".rendered-message",c);a.slideUp("fast",function(){b.slideDown();
a.remove()})},deleteComment:function(c,b,a){c.fadeOut();$.ajax({url:BASE_URL+"agent/publish/comments/delete/"+b+"/"+a,type:"POST",context:this,dataType:"json",error:function(){c.show()
},success:function(d){el.remove()}})},approveComment:function(c,b,a){c.removeClass("validating");$.ajax({url:BASE_URL+"agent/publish/comments/approve/"+b+"/"+a,type:"POST",context:this,dataType:"json",error:function(){c.addClass("validating")
}})}});Orb.createNamespace("DeskPRO.Agent.PageHelper");DeskPRO.Agent.PageHelper.MiscContent=new Orb.Class({Implements:[Orb.Util.Events,Orb.Util.Options],initialize:function(c,b){var a=this;
this.page=c;this.options={revisionCompareUrl:""};this.setOptions(b);this.wrapper=this.page.wrapper;this.getEl=this.page.getEl;
this._initCompareRevs()},_initCompareRevs:function(){$(".compare-trigger",this.wrapper).click(this.showCompareRev.bind(this));
var b=$("input.rev-compare-check",this.wrapper);var a=0;$(".revision-compare-table",this.wrapper).delegate("input.rev-compare-check","click",function(){if($(this).is(":checked")){var c=b.filter(":checked");
if(c.length>2){c.each(function(){if($(this).data("check-count")==a){$(this).prop("checked",false)}})}$(this).data("check-count",++a)
}})},showCompareRev:function(){var d=$(".revision-compare-table input.rev-compare-check:checked",this.wrapper);var a=d.first().val();
var b=d.last().val();if(!a||!b||a==b){return}var c=new DeskPRO.UI.Overlay({triggerElement:$("button.compare-trigger",this.wrapper),contentMethod:"ajax",contentAjax:{url:this.options.revisionCompareUrl.replace("{OLD}",a).replace("{NEW}",b)},destroyOnClose:true});
c.openOverlay()}});Orb.createNamespace("DeskPRO.Agent.PageHelper");DeskPRO.Agent.PageHelper.StateSaver=new Orb.Class({Implements:[Orb.Util.Events,Orb.Util.Options],initialize:function(b){var a=this;
this.options={stateId:"",expireTime:"+7 days",time:1000,alwaysChanged:false,listenOn:null,callback:null};this.setOptions(b);
if(this.options.listenOn){var c=$(this.options.listenOn);$(":input, textarea, select",c).change(function(){a.triggerChange()
});$("input[type=text], textarea",c).keypress(function(){a.triggerChange()});if(!this.options.callback){this.options.callback=function(){if(c.is("form")){return c.serializeArray()
}else{return $(":input, textarea, select",c).serializeArray()}}}}if(!this.options.callback){this.options.callback=function(){};
console.warn("No callback for state save")}this.doRestartTimer=false;this.hasChanged=false;if(this.alwaysChanged){this.restartTimer()
}},triggerChange:function(){this.hasChanged=true;this.restartTimer()},restartTimer:function(){if(this.ajax){this.doRestartTimer=true
}if(this.timer){window.clearTimeout(this.timer);this.timer=null}this.doRestartTimer=false;this.timer=window.setTimeout(this.saveState.bind(this),this.options.time)
},saveState:function(){var a=this.options.callback();this.hasChanged=false;this.fireEvent("beforeSaveState",[a]);var b=function(d){if(d.indexOf("[")===-1){d="["+d+"]"
}else{d=d.replace(/^([\w\d]+)\[(.*?)$/,"[$1][$2")}return d};var c=[];c.push({name:"prefs_expire[agent.ui.state."+this.options.stateId+"]",value:this.options.expireTime});
if(typeOf(a)=="array"){Array.each(a,function(d){c.push({name:"prefs[agent.ui.state."+this.options.stateId+"]"+b(d.name),value:d.value})
},this)}else{c.push({name:"prefs[agent.ui.state."+this.options.stateId+"]",value:text})}$.ajax({url:BASE_URL+"agent/misc/ajax-save-prefs",type:"POST",data:c,context:this,complete:function(){this.ajax=null;
if(this.doRestartTimer||this.alwaysChanged){this.restartTimer()}}})},destroy:function(){if(this.ajax){this.ajax.abort()}if(this.timer){window.clearTimeout(this.timer);
this.timer=null}}});Orb.createNamespace("DeskPRO.Agent.PageFragment.Page");DeskPRO.Agent.PageFragment.Page.SnippetViewer=new Class({Extends:DeskPRO.Agent.PageFragment.Basic,allowDupe:true,TYPENAME:"ticket_snippets",initPage:function(b){var a=this;
this.wrapper=b;this.catTabs=new DeskPRO.UI.SimpleTabs({triggerElements:$("nav:first > ul > li:not(.new-category)",this.wrapper),context:this.wrapper});
this.overlay=new DeskPRO.UI.Overlay({contentElement:this.wrapper,destroyOnClose:false});this.wrapper.delegate(".snippet-trigger","click",function(f){f.preventDefault();
f.stopPropagation();var e=$(this).data("snippet-id");var c=$(".snippet-"+e,a.wrapper);var h=$("textarea.value.formatted",c);
if(!h.length){h=$("textarea.value.raw",c)}var d=h.val().trim();var g={event:f,snippetId:e,snippetEl:c,snippet:d};a.fireEvent("snippetClick",[g]);
a.closeSelf()});this._initEditing()},closeSelf:function(){var a={cancel:false};this.fireEvent("closeSelf",a);if(!a.cancel){this.parent()
}},destroy:function(){if(this.newCatOverlay){this.newCatOverlay.remove()}if(this.newCatBackdrop){this.newCatBackdrop.remove()
}},_initEditing:function(){var a=this;this.newCategoryBtn=$("nav li.new-category",this.wrapper);this.newCategoryBtn.click(function(b){b.preventDefault();
b.stopPropagation();a.newCategory()});this.newCatOverlay=$(".new-snippet-category",this.wrapper);this.newCatOverlay.detach().appendTo("body");
$(".perm-type-opt",this.newCatOverlay).click(function(){console.log("click");if($(this).val()=="team"){$(".perm-teams",a.newCatOverlay).slideDown()
}else{$(".perm-teams",a.newCatOverlay).slideUp()}});$(".new-cat-trigger",this.newCatOverlay).click(function(){a.saveNewCat()
});this.newCatBackdrop=$('<div class="backdrop" />').hide().appendTo("body");this.newCatBackdrop.click(function(){a.newCatOverlay.slideUp();
a.newCatBackdrop.hide()});this.wrapper.delegate(".save-snippet-trigger","click",function(b){b.preventDefault();b.stopPropagation();
var c=$(this).parent().parent().parent();a.saveSnippet($(c))});this.wrapper.delegate(".snippet .show","dblclick",function(b){var c=$(this).parent();
$(".show",c).slideUp("fast",function(){$(".edit",c).slideDown()})});this.wrapper.delegate(".delete-snippet-trigger","click",function(b){var c=$(this).data("snippet-id");
$.ajax({url:BASE_URL+"agent/tickets/snippet-viewer/delete-snippet",type:"POST",data:{snippet_id:c},dataType:"json",context:this,success:function(e){var d=$(".snippet-"+e.snippet_id,this.wrapper);
d.slideUp(function(){d.remove()})}})});this.wrapper.delegate("nav li","dblclick",function(b){a.editCategory($(this))})},editCategory:function(c){var a=this;
var b=c.data("category");$.ajax({url:BASE_URL+"agent/tickets/snippet-viewer/edit-category",type:"GET",data:{category_id:b},dataType:"html",context:this,success:function(f){var e=$(f).hide().appendTo("body");
var d=$('<div class="backdrop" />').hide().appendTo("body");var h=c.offset();e.css({left:h.left,top:h.top});e.slideDown();
d.show();function g(){d.remove();e.slideUp(function(){e.remove()})}d.click(g);$(".save-trigger",e).click(function(i){i.preventDefault();
i.stopPropagation();var j=$("input",c).serializeArray();$.ajax({url:BASE_URL+"agent/tickets/snippet-viewer/save-category",type:"POST",data:j,dataType:"json",context:this,success:function(k){$(".cat-title-"+k.category_id,a.wrapper).text(k.title);
g()}})});$(".delete-trigger",e).click(function(i){i.preventDefault();i.stopPropagation();$.ajax({url:BASE_URL+"agent/tickets/snippet-viewer/delete-category",type:"POST",data:{category_id:b},dataType:"json",context:this,success:function(l){var j=$(".cat-title-"+l.category_id,a.wrapper);
var k=j.prev();j.remove();a.catTabs.activateTab(k);g()}})})}})},newCategory:function(){var a=this.newCategoryBtn.offset();
this.newCatOverlay.css({left:a.left,top:a.top});this.newCatOverlay.slideDown();this.newCatBackdrop.show()},saveNewCat:function(){var a=$("input",this.newCatOverlay).serializeArray();
$.ajax({url:BASE_URL+"agent/tickets/snippet-viewer/new-cat",type:"POST",data:a,dataType:"json",context:this,success:function(c){var b=$(c.cat_row_html);
this.newCatOverlay.slideUp();this.newCatBackdrop.hide();b.insertBefore(this.newCategoryBtn);var d=$(c.cat_section_html);d.appendTo($(".snippet-sections",this.wrapper));
this.catTabs.addTriggerElement(b);this.catTabs.activateTab(b)}})},saveSnippet:function(b){var a=$("input, textarea",b).serializeArray();
if(this.wrapper.data("ticket-id")){a.push({name:"ticket_id",value:this.wrapper.data("ticket-id")})}$.ajax({url:BASE_URL+"agent/tickets/snippet-viewer/save-snippet",type:"POST",data:a,dataType:"json",context:this,success:function(d){var c=$(d.snippet_row_html);
c.hide();if(b.is(".new-snippet")){$(".cat-"+d.category_id+" .new-snippet",this.wrapper).before(c);$('input[name="title"], textarea[name="snippet"]',b).val("")
}else{$(".snippet-"+d.snippet_id,this.wrapper).replaceWith(c)}c.slideDown()}})}});Orb.createNamespace("DeskPRO.Agent.PageFragment.Page");
DeskPRO.Agent.PageFragment.Page.Ticket=new Class({Extends:DeskPRO.Agent.PageFragment.Basic,TYPENAME:"ticket",wrapper:null,destroyEls:[],destroyMenus:[],destroyOverlays:[],changeManager:null,valueForm:null,layout:null,popout:null,popout_overview:null,isMouseOverPopout:false,hasInitPopout:false,popoutPage:null,lastActiveDate:null,initPage:function(g){this.wrapper=g;
this.contentWrapper=$(".layout-content",this.wrapper).attr("id",Orb.getUniqueId());this.barWrapper=$(".bar-wrapper",this.wrapper);
var d=this.contentWrapper;d.tinyscrollbar();$("div.scroll-content:first, div.scroll-viewport:first",this.contentWrapper).resize(function(){d.tinyscrollbar_update()
});this.valueForm=$("form.value-form:first",this.contentWrapper);this.valueForm.submit(function(i){i.preventDefault()});this.changeManager=new DeskPRO.Agent.Ticket.ChangeManager(this);
window.TICKET=this;if(!this.meta.isDeleted){this._initCustomFieldsEditor()}var f=this;this._initMessage($("div.messages-wrap"));
$("input.date-field",this.contentWrapper).datepicker({dateFormat:"M d, yy"});this.ticketDisplay=new DeskPRO.Agent.PageHelper.TicketDisplay(this,{wrapper:g});
this.initFeaturesOnCollection(this.wrapper,{routes:[],times:[".timeago"]});if(!this.meta.isDeleted){this._initTicketActionsMenu();
this._initMessageActionsMenu();this._initFlagMenu();this._initLabels()}else{$("button.undelete-trigger",this.wrapper).click(this.doTicketUndelete.bind(this))
}this._initPopout();this._initTicketTabs();DeskPRO_Window.getMessageBroker().sendMessage("ui.ticket.opened",{ticketId:this.getMetaData("ticket_id")});
DeskPRO_Window.getMessageBroker().sendMessage("ui.tab.opened",{type:"tickets",id:this.getMetaData("ticket_id")});DeskPRO_Window.getMessageBroker().addMessageListener("tickets.deleted",(function(i){if(i.indexOf(this.getMetaData("ticket_id"))!==-1){DeskPRO_Window.removePage(this)
}}).bind(this),this.pageUid);DeskPRO_Window.getMessageBroker().addMessageListener("tickets.new-messages."+this.getMetaData("ticket_id"),this.getNewTicketMessages.bind(this),this.pageUid);
Array.each(this.getMetaData("fieldHandlers",[]),function(i){if(!i){return}var j=i.classname;var k=i.wrap_id;var i=new i($("#"+k),this);
i.initPage()},this);this.addEvent("shortcutFocusReply",(function(){$("div.scroll-content:first, div.scroll-viewport:first",this.contentWrapper).scrollTop(100000);
$('textarea[name="message"]',this.ticketReply).focus()}).bind(this));$(".ticket-urgency",this.contentWrapper).mouseover(function(){Tipped.show(this)
});var c=$("input.show-messages",this.wrapper);var e=$("input.show-notes",this.wrapper);var h=$("input.show-logs",this.wrapper);
var b=$(".messages-wrap",this.wrapper);function a(){var k=c.is(":checked");var j=e.is(":checked");var i=h.is(":checked");
if(!k&&!j&&!i){k=true;c.attr("checked",true)}if(k){$("div.message:not(.note-message)",b).show()}else{$("div.message:not(.note-message)",b).hide()
}if(j){$("div.note-message",b).show()}else{$("div.note-message",b).hide()}if(i){$("div.log-row",b).show();$("div.log-batch",b).show()
}else{$("div.log-row",b).hide();$("div.log-batch",b).hide()}}$(".message-controls input",this.wrapper).click(function(){a()
});$("button.goto-reply",this.wrapper).click(function(){d.tinyscrollbar_scrolltop(1000000)});this.moreActionsMenu=new DeskPRO.UI.Menu({triggerElement:$(".more",this.getEl("action_buttons")),menuElement:this.getEl("more_actions_menu"),onItemClicked:(function(k){var j=$(k.itemEl);
if(j.is(".merge-trigger")){var i=new DeskPRO.Agent.Widget.MergeTicket({ticketId:this.getMetaData("ticket_id"),destroyOnClose:true,onMergeSuccess:function(l){Array.each(DeskPRO_Window.getTabWatcher().findTabType("ticket"),function(m){var n=m.page.getMetaData("ticket_id");
if(n==l.old_ticket_id||n==l.ticket_id){DeskPRO_Window.pageTabStrip.removeTabById(m.id)}});DeskPRO_Window.runPageRoute("ticket:"+BASE_URL+"agent/tickets/"+l.ticket_id);
i.close()}});i.open()}}).bind(this)});this.replyBox=new DeskPRO.Agent.PageFragment.Page.Ticket.ReplyBox(this,{replyBox:this.getEl("replybox"),onBeforeSaveReply:(function(i){i.formData.push({name:"client_messages_since",value:DeskPRO_Window.getLastClientMessageId()});
i.formData.push({name:"last_message_id",value:this.ticketChecker.getLastMessageId()});i.formData.push({name:"last_log_id",value:this.ticketChecker.getLastLogId()});
this.ticketChecker.pause(true)}).bind(this),onSaveReplySuccess:(function(j){this.handleTicketUpdate(j.result);this.ticketChecker.unpause();
if(j.result.close_tab){window.setTimeout((function(){console.log("ere");this.closeSelf()}).bind(this),400)}else{var i=this.changeManager.getPropertyManager("agent_id");
i.setIncomingValue(j.result.agent_id);var l=this.changeManager.getPropertyManager("agent_team_id");l.setIncomingValue(j.result.agent_team_id);
var k=this.changeManager.getPropertyManager("status");k.setIncomingValue(j.result.status)}}).bind(this)});this.ticketActions=new DeskPRO.Agent.PageFragment.Page.Ticket.TicketActions(this);
this.ticketParticipants=new DeskPRO.Agent.PageFragment.Page.Ticket.Participants(this);this.ticketChecker=new DeskPRO.Agent.PageFragment.Page.Ticket.TicketChecker(this,{lastMessageId:this.meta.lastMessageId,lastLogId:this.meta.lastLogId,checkUrl:BASE_URL+"agent/tickets/"+this.getMetaData("ticket_id")+"/ajax-update-check"});
if(this.meta.isLocked){this.ticketLocked=new DeskPRO.Agent.PageFragment.Page.Ticket.TicketLocked(this)}},destroyPage:function(){this.ticketChecker.destroy();
if(this.updateCheckTimeout){this.updateCheckTimeout=window.clearTimeout(this.updateCheckTimeout)}for(var a=0;a<this.destroyEls.length;
a++){$(this.destroyEls[a]).remove()}for(var a=0;a<this.destroyMenus.length;a++){this.destroyMenus[a].destroy()}for(var a=0;
a<this.destroyOverlays.length;a++){this.destroyOverlays[a].destroy()}if(this.personPopover){this.personPopover.destroy()}if(this.orgPopover){this.orgPopover.destroy()
}DeskPRO_Window.getMessageBroker().sendMessage("ui.ticket.closed",{ticketId:this.getMetaData("ticket_id")});DeskPRO_Window.getMessageBroker().removeTaggedListeners(this.pageUid)
},handleTicketUpdate:function(e){if(e.client_messages){DeskPRO_Window.getMessageChanneler().handleMessageAjax(e.client_messages)
}if(e.ticket_messages_block){var d=$(e.ticket_messages_block).hide();d.appendTo($(this.getEl("messages_wrap"))).slideDown("fast");
var b=$("input.show-messages",this.wrapper).is(":checked");var c=$("input.show-notes",this.wrapper).is(":checked");var f=$("input.show-logs",this.wrapper).is(":checked");
console.log($("input.show-logs",this.wrapper));var a=$(".messages-wrap",this.wrapper);if(!b){d.find("div.message:not(.note-message)").hide()
}if(!c){d.find("div.note-message").hide();if(!f){d.find("div.log-row").hide();d.find("div.log-batch").hide()}}this._initMessage(d)
}if(e.updated_agent_parts_html){this.getEL("agent_part_list").html(e.updated_agent_parts_html);$(".agent-part-count",this.wrapper).text(e.updated_agent_parts_count)
}},displayNewMessage:function(a,c){var b=$(a).hide();c=c||function(){};b.appendTo($(this.getEl("messages_wrap"))).slideDown("fast",c);
this._initMessage(b);this.incCount("ticket-messages")},activate:function(){this.ticketChecker.unpause()},deactivate:function(){this.ticketChecker.pause()
},_initMessage:function(b){var a=$("ul.attachment-list li.is-image a",b);a.colorbox({title:function(){var c=$(this).attr("href");
return'<a href="'+c+'" target="_blank">Open In New Window</a>'},width:"50%",height:"50%",initialWidth:"200",initialHeight:"150",scalePhotos:true,photo:true,opacity:0.5,transition:"none"});
$(".log-row",b).each(function(){var d=$(".expand",this);var c=$(this);d.click(function(){var f=".expand-set";if($(this).data("set")){f=$(this).data("set")
}var e=$(f,b);if(e.is(":visible")){e.slideUp();d.removeClass("open")}else{e.slideDown();d.addClass("open")}})})},incCount:function(c){var b=$("."+c+"-count",this.wrapper);
var a=b.data("count")+1;b.data("count",a).html("("+a+")")},setCount:function(c,a){var b=$("."+c+"-count",this.wrapper);b.data("count",a).html("("+a+")")
},appendToMessage:function(a){this.getEl("replybox_txt").insertAtCaret(a)},addAttachToList:function(a){var b=$(".template-download",this.getEl("replybox")).tmpl(a);
$(".file-list",this.getEl("replybox")).append(b)},getPropertyManager:function(a,b){console.warn("Depreciated");return this.changeManager.getPropertyManager(a,b)
},custom_fields_display:null,custom_fields_edit:null,_initCustomFieldsEditor:function(){$(".ticket-custom-fields-edit-btn",this.wrapper).click((function(){this.showCustomFieldEditor()
}).bind(this));this.custom_fields_display=$(".ticket-custom-fields:not(.edit)",this.wrapper);this.custom_fields_edit=$(".ticket-custom-fields.edit",this.wrapper);
this.custom_fields_edit.detach().appendTo(this.custom_fields_display.parent().parent().parent().parent());$(".close-trigger",this.custom_fields_edit).click((function(){this.closeCustomFieldEditor()
}).bind(this));var a=this;$(".save-trigger",this.custom_fields_edit).click((function(){var b=$(":input",a.custom_fields_edit);
this._saveCustomFields(b)}).bind(this))},showCustomFieldEditor:function(){var b=this.custom_fields_display.position();var a=this.custom_fields_display.width();
if(a>690){b.left+=a-690;a=690}this.custom_fields_edit.css({position:"absolute",top:b.top,left:b.left,width:a});this.custom_fields_edit.slideDown()
},closeCustomFieldEditor:function(){this.custom_fields_edit.slideUp()},_saveCustomFields:function(a){console.warn("This method shold be overriden in a subclass!")
},labelsList:null,_initLabels:function(){this.labelsList=$(".ticket-tags ul",this.contentWrapper);this.labelsInput=new DeskPRO.UI.LabelsInput({type:"tickets",list:this.labelsList,onChange:this.saveLabels.bind(this)})
},_saveLabelsTimeout:null,saveLabels:function(){if(this.changeManager.hasChanges()){return}if(this._saveLabelsTimeout){window.clearTimeout(this._saveLabelsTimeout)
}this._saveLabelsTimeout=this._doSaveLabels.delay(2000,this)},_doSaveLabels:function(){var a=this.labelsInput.getFormData();
$.ajax({url:this.getMetaData("labelsSaveUrl"),type:"POST",context:this,data:a,dataType:"json",success:function(b){this._handleSaveLabelsSuccess(b)
}})},_handleSaveLabelsSuccess:function(a){},getNewTicketMessages:function(){var a=$("li.message-item:last",this.contentWrapper).data("message-id");
$.ajax({url:this.getMetaData("getMessagesUrl"),type:"POST",context:this,data:{since:a},dataType:"json",success:function(b){Array.each(b.messages,function(c){this.displayNewMessage(c)
},this)}})},_saveCustomFields:function(a){$(".buttons .loading-off",this.custom_fields_edit).hide();$(".buttons .loading-on",this.custom_fields_edit).show();
var b=a.serializeArray();$.ajax({url:BASE_URL+"agent/tickets/"+this.getMetaData("ticket_id")+"/ajax-save-custom-fields",type:"POST",context:this,data:b,dataType:"html",success:function(c){this._handleSaveCustomFieldsSuccess(c)
}})},_handleSaveCustomFieldsSuccess:function(a){$(".buttons .loading-on",this.custom_fields_edit).hide();$(".buttons .loading-off",this.custom_fields_edit).show();
this.closeCustomFieldEditor();$(".wrap",this.custom_fields_display).html(a)},flagMenu:null,_initFlagMenu:function(){var a=this;
this.flagMenu=new DeskPRO.UI.Menu({triggerElement:$(".ticket-flag:first",this.wrapper),menuElement:$(".ticket-flag-menu:first",this.wrapper),onItemClicked:function(b){a._handleFlagMenuClick(b)
}});this.destroyMenus.push(this.flagMenu)},_handleFlagMenuClick:function(b){var a="flag";var c=$(b.itemEl).data("flag");var d=this.getPropertyManager(a);
this.changeManager.setInstantChange(d,c)},_handleFlagMenuClickSuccess:function(a,b){DeskPRO_Window.getMessageBroker().sendMessage("filter-flagged.flag-changed",{old_flag:a,new_flag:b})
},_initTicketTabs:function(){var a=this;var b=new DeskPRO.UI.SimpleTabs({context:$(".full-container-tabbed-contents-wrap",this.contentWrapper),triggerElements:$(".full-container-tabbed-tabs li",this.contentWrapper),onTabSwitch:function(c){if(c.tabEl.is(".ticket-log")){a._loadTicketTab_Log()
}else{if(c.tabEl.is(".ticket-attach")){a._loadTicketTab_Attach()}else{if(c.tabEl.is(".ticket-related-content")){a._loadTicketTab_RelatedContent()
}}}}})},_loadTicketTab_RelatedContent:function(){var a=$(".tab-content.ticket-realted-content",this.wrapper);if(!a.is(".unloaded")){return
}$.ajax({url:this.getMetaData("tabRelatedContentUrl"),type:"GET",dataType:"html",success:function(b){a.html(b);a.removeClass("unloaded")
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
a.openOverlay();break;case"quote":console.debug("todo loading indicator when loading _doMessageAction quote");$.ajax({url:this.getMetaData("getMessageQuoteUrl").replace("{message_id}",b),type:"GET",context:this,dataType:"json",success:function(f){var e=$(".reply-form-fields:first textarea:first",this.ticketBar);
console.log(e);e.val(f.message_quote+"\n\n"+e.val());e.focus()}});break;case"split":var d="Are you sure you want to split this ticket into two?";
DeskPRO_Window.showConfirm(d,function(){$.ajax({url:BASE_URL+"agent/tickets/split/"+b,type:"POST",context:this,dataType:"json",success:function(e){console.log("Ticket split return %o",e);
if(e.success){DeskPRO_Window.loadPage(BASE_URL+"agent/tickets/"+e.ticket_id)}}})});break}},_initPopout:function(){this.personPopover=new DeskPRO.Agent.PageHelper.Popover({pageUrl:this.getMetaData("viewPersonUrl"),tabRoute:$(".person-overview",this.wrapper).data("route"),loadTimeout:500});
$(".person-overview",this.wrapper).css({cursor:"pointer"}).click((function(b){this.personPopover.toggle()}).bind(this));this.orgPopover=null;
var a=$(".org-overview",this.wrapper);if(a.length){this.orgPopover=new DeskPRO.Agent.PageHelper.Popover({pageUrl:this.getMetaData("viewOrgUrl"),tabRoute:a.data("route"),loadTimeout:1200});
a.css({cursor:"pointer"}).click((function(b){this.orgPopover.toggle()}).bind(this))}},updateCounts:function(){var a=$(".full-container-tabbed-tabs",this.wrapper);
$.ajax({url:this.getMetaData("getUpdatedCountsUrl"),type:"GET",context:this,dataType:"json",success:function(b){Object.each(b,function(d,c){var e=".ticket-"+c+"-count";
$(e,a).html("("+d+")")})}})}});Orb.createNamespace("DeskPRO.Agent.PageFragment.Page.Ticket");DeskPRO.Agent.PageFragment.Page.Ticket.ReplyBox=new Orb.Class({Implements:[Orb.Util.Events,Orb.Util.Options],initialize:function(e,i){var h=this;
this.page=e;this.options={replyBox:null};this.setOptions(i);var a;this.replyBox=a=this.options.replyBox;this.replyForm=this.getEl("reply_form");
this.replyForm.submit(function(j){j.preventDefault()});$(".save-reply-trigger",this.replyBox).click(function(j){h.saveReply()
});this.getEl("replybox_txt").keypress(this.updateDraftWait.bind(this));this.page.addEvent("closeTab",this.updateDraft.bind(this));
var g=this.getEl("replybox_replytab_btn");var b=this.getEl("replybox_notetab_btn");g.click(function(){b.removeClass("on");
g.addClass("on");$(".reply-hide",a).hide();$(".note-hide",a).show()});b.click(function(){b.addClass("on");g.removeClass("on");
$(".reply-hide",a).show();$(".note-hide",a).hide()});this.tabReply=g;this.tabNote=b;var f=this.getEl("newnote_followerslist");
this.agentFollowersSelector=new DeskPRO.Agent.Widget.AgentSelector({agentList:$("#agent_selector_list"),multipleChoice:true,onSelectionClick:function(m){if(m.checked){$(".agent-"+m.agentId,f).remove()
}else{var l=DeskPRO_Window.getAgentInfo(m.agentId);if(!l){return}var k='<li class="agent-'+l.id+'"><input type="hidden" name="add_agent_part[]" value="'+l.id+'" /><span style="background: url(\''+l.pictureUrlSizable.replace("{SIZE}",30)+"')\">"+Orb.escapeHtml(l.name)+"</li>";
var j=$(k);j.appendTo(f)}}});$(".add-followers-trigger",a).click(function(j){h.agentFollowersSelector.open(j)});var d=this.getEl("text_snippets_btn");
d.click(function(){h.showTextSnippets()});var c=$(".file-list",this.replyBox);$("input",c[0]).live("click",function(){var k=$(this);
var j=k.parent();if(k.is(":checked")){j.removeClass("unchecked")}else{j.addClass("unchecked")}});this.replyBox.fileupload({url:this.page.getMetaData("uploadAttachUrl"),dropZone:this.barWrapper,autoUpload:true,uploadTemplate:$(".template-upload",this.replyBox),downloadTemplate:$(".template-download",this.replyBox)});
$(".add-cc-trigger",this.replyBox).click(function(){var j=h.getEl("add_cc_txt");var l=j.val();var k=$("<li>"+l+'<input type="hidden" name="new_parts[]" value="'+l+'" />&nbsp;&nbsp;<span class="remove-trigger" style="cursor: pointer;">x</span></li>');
$(".remove-trigger",k).click(function(m){m.preventDefault();m.stopPropagation();k.remove()});k.appendTo(h.getEl("cc_list"));
j.val("")});this.assignAgentSelector=new DeskPRO.Agent.Widget.AgentSelector({triggerElement:$(".reply-agent-menu-trigger",this.replyBox),agentList:$("#agent_selector_list"),multipleChoice:false,onSelectionClick:function(k){var j=DeskPRO_Window.getAgentInfo(k.agentId);
if(!j){return}h.getEl("agent_id").val(j.id);h.getEl("agent_id_name").text(j.name);h.getEl("note_agent_id_name").text(j.name)
}});this.assignTeamMenu=new DeskPRO.UI.Menu({triggerElement:$(".reply-agent-team-menu-trigger",this.replyBox),menuElement:$("#agent_teams_menu"),onItemClicked:function(l){var j=$(l.itemEl);
if(j.data("team-id")){var k=j.data("team-id");var m=j.text().trim();h.getEl("agent_team_id").val(k);h.getEl("agent_team_id_name").text(m);
h.getEl("note_agent_team_id_name").text(m)}}});this.getEl("note_assign_opt").click(function(j){if(h.getEl("note_agent_id_name").text().trim()==""){h.assignAgentSelector.open(j)
}});this.getEl("note_assign_team_opt").click(function(j){if(h.getEl("note_agent_team_id_name").text().trim()==""){h.assignTeamMenu.open(j)
}});this.snippetsViewer=new DeskPRO.Agent.Widget.SnippetViewer({viewUrl:this.page.getUrl("snippetviewer"),triggerElement:$(".ticket-snippets-trigger",this.replyBox),onSnippetClick:this._onSnippetClick.bind(this)});
if(!this.getEl("replybox_txt").val().trim().length){this.resetReplyBox()}},_onSnippetClick:function(a){this.getEl("replybox_txt").val(this.getEl("replybox_txt").val()+"\n\n"+a.snippet)
},saveReply:function(){var a=this.serializeFormData();var b={formData:a,replyBox:this,cancel:false};this.fireEvent("beforeSaveReply",[b]);
if(b.cancel){return false}this.replyBox.addClass("loading");$.ajax({url:this.replyForm.attr("action"),type:"POST",dataType:"json",data:a,context:this,success:function(c){this.replyBox.removeClass("loading");
b.result=c;b.success=true;this.resetReplyBox();this.fireEvent("saveReply",[b]);this.fireEvent("saveReplySuccess",[b])},error:function(c,d){this.replyBox.removeClass("loading");
b.xhr=c;b.result=null;b.textStatus=d;b.success=false;this.fireEvent("saveReply",[b]);this.fireEvent("saveReplyError",[b])
}})},serializeFormData:function(){var b=$(".fields-both",this.replyBox);if(this.getReplyType()=="reply"){b=b.add($(".reply-options",this.replyBox))
}else{b=b.add($(".note-options",this.replyBox))}var a=b.find("input, select, textarea");return a.serializeArray()},showTextSnippets:function(){if(!this.textsnippetsOverlay){this.textsnippetsOverlay=new DeskPRO.UI.Overlay({contentElement:this.getEl("text_snippets_overlay")})
}this.textsnippetsOverlay.open()},resetReplyBox:function(){if(this._draftTimer){window.clearTimeout(this._draftTimer);this._draftTimer=null
}$("textarea.reply",this.replyBox).val("");$(".attachments ul.file-list",this.replyBox).html("");if(this.page.getMetaData("agentSignature",false)){$("textarea.reply",this.replyBox).val("\n\n--\n"+this.page.getMetaData("agentSignature",""))
}this.tabReply.click()},updateDraftWait:function(){if(this._draftTimer){return}this._draftTimer=window.setTimeout(this.updateDraft.bind(this),1000)
},updateDraft:function(){if(this._draftTimer){window.clearTimeout(this._draftTimer);this._draftTimer=null}var c=this.getEl("replybox_txt").val().trim();
if(!c){return}var a="ticket_draft."+this.page.getMetaData("ticket_id");var b=[];b.push({name:"prefs_expire["+a+"]",value:"+30 days"});
b.push({name:"prefs["+a+"]",value:c});$.ajax({url:BASE_URL+"agent/misc/ajax-save-prefs",type:"POST",data:b})},getReplyType:function(){if(this.tabReply.is(".on")){return"reply"
}else{return"note"}},getEl:function(a){return this.page.getEl(a)}});Orb.createNamespace("DeskPRO.Agent.PageFragment.Page.Ticket");
DeskPRO.Agent.PageFragment.Page.Ticket.TicketLocked=new Orb.Class({Implements:[Orb.Util.Events,Orb.Util.Options],initialize:function(b,a){this.page=b;
this.options={};this.setOptions(a);this.lockedBar=this.page.getEl("locked_bar");this.lockedOverlay=this.page.getEl("locked_overlay");
this.dismissBtn=$("button.dismiss",this.lockedBar);this.dismissBtn.click(this.dismiss.bind(this))},dismiss:function(){var b=$(".page-ticket:first",this.page.wrapper);
var a=this;this.lockedOverlay.fadeOut("fast");this.lockedBar.fadeOut("fast",function(){b.removeClass("locked")})},unlock:function(){this.dismiss()
},destroy:function(){}});Orb.createNamespace("DeskPRO.Agent.PageFragment.Page.Ticket");DeskPRO.Agent.PageFragment.Page.Ticket.TicketChecker=new Orb.Class({Implements:[Orb.Util.Events,Orb.Util.Options],initialize:function(b,a){this.options={interval:8000,autoAfterPauseTime:8000,lastMessageId:0,lastLogId:0,autostart:true};
this.setOptions(a);this.page=b;this.paused=false;this.lastDate=null;this.timeout=null;this.activeAjax=null;this.lastMessageId=0;
this.lastLogId=0;if(this.options.lastMessageId){this.lastMessageId=this.options.lastMessageId}if(this.options.lastLogId){this.lastLogId=this.options.lastLogId
}this.sendData={};if(this.autostart){this.startTimer()}},pause:function(a){this.paused=true;if(this.timeout){window.clearTimeout(this.timeout);
this.timeout=null}if(a&&this.activeAjax){this.activeAjax.abort();this.activeAjax=null}},unpause:function(){this.paused=false;
if(this.lastDate){var a=new Date();if(a.getTime()-this.lastDate.getTime()){this.runCheck()}}this.startTimer()},isPaused:function(){return this.paused
},startTimer:function(){if(this.timeout){window.clearTimeout(this.timeout)}this.timeout=window.setTimeout(this.runCheck.bind(this),this.options.interval)
},setSendData:function(b,a){this.sendData[b]=a},getLastMessageId:function(){return this.lastMessageId},getLastLogId:function(){return this.lastLogId
},runCheck:function(){if(this.timeout){window.clearTimeout(this.timeout)}if(this.activeAjax){return}var a=$.extend({},this.sendData);
a.last_message_id=this.getLastMessageId();a.last_log_id=this.getLastLogId();this.fireEvent("sendData",[a]);this.activeAjax=$.ajax({url:this.options.checkUrl,type:"GET",context:this,data:a,dataType:"json",complete:function(){this.lastDate=new Date();
this.startTimer();this.activeAjax=null;this.fireEvent("checkComplete")},success:function(b){if(b.last_message_id&&b.last_message_id>this.lastMessageId){this.lastMessageId=b.last_message_id
}if(b.last_log_id&&b.last_log_id>this.lastLogId){this.lastLogId=b.last_log_id}this.fireEvent("sendSuccess",[b])}})},destroy:function(){if(this.timeout){window.clearTimeout(this.timeout)
}if(this.activeAjax){this.activeAjax.abort();this.activeAjax=null}}});Orb.createNamespace("DeskPRO.Agent.PageFragment.Page.Ticket");
DeskPRO.Agent.PageFragment.Page.Ticket.TicketActions=new Orb.Class({Implements:[Orb.Util.Events,Orb.Util.Options],initialize:function(e,b){var a=this;
this.page=e;this.options={};this.setOptions(b);this.changeManager=this.page.changeManager;var f=this.getEl("ticket_header");
var c=this.getEl("action_buttons");var d=this.changeManager.getPropertyManager("agent_id");$(".assign-me button",c).click((function(){this.changeManager.setInstantChange(d,DESKPRO_PERSON_ID)
}).bind(this));$(".assign-none button",c).click((function(){this.changeManager.setInstantChange(d,0)}).bind(this));this.assignAgentSelector=new DeskPRO.Agent.Widget.AgentSelector({agentList:$("#agent_selector_list"),showNone:true,multipleChoice:false,triggerElement:$("nav.actions .assign-to button, .prop-agent-id",f),startWith:[d.getValue()],onSelectionChanged:(function(g){this.changeManager.setInstantChange(d,g.selection)
}).bind(this)});this.assignTeamMenu=new DeskPRO.UI.Menu({triggerElement:$("nav.actions .assign-to-team button, .prop-agent-team-id",f),menuElement:$("#agent_teams_menu"),onItemClicked:(function(i){var g=$(i.itemEl);
var j=this.changeManager.getPropertyManager("agent_team_id");var h=parseInt(g.data("team-id"));this.changeManager.setInstantChange(j,h)
}).bind(this)});this.statusMenu=new DeskPRO.UI.Menu({triggerElement:$(".prop-status-icon",f),menuElement:$("#ticket_status_menu"),onItemClicked:(function(i){var h=$(i.itemEl);
var j=this.changeManager.getPropertyManager("status");var g=h.data("status");this.changeManager.setInstantChange(j,g)}).bind(this)});
$(".set-resolved button",c).click((function(){var g=this.changeManager.getPropertyManager("status");this.changeManager.setInstantChange(g,"resolved")
}).bind(this));$(".set-closed button",c).click((function(){var g=this.changeManager.getPropertyManager("status");this.changeManager.setInstantChange(g,"closed")
}).bind(this));$(".set-opem button",c).click((function(){var g=this.changeManager.getPropertyManager("status");this.changeManager.setInstantChange(g,"open")
}).bind(this));$(".set-open button",c).click((function(){var g=this.changeManager.getPropertyManager("status");this.changeManager.setInstantChange(g,"open")
}).bind(this));$(".marks-spam button",c).click((function(){var g=this.changeManager.getPropertyManager("status");this.changeManager.setInstantChange(g,"hidden.spam")
}).bind(this));this.statusMenu=new DeskPRO.UI.Menu({triggerElement:$(".prop-department-id",f),menuElement:$("#department_menu"),onItemClicked:(function(i){var h=$(i.itemEl);
var j=this.changeManager.getPropertyManager("department_id");var g=parseInt(h.data("department-id"));this.changeManager.setInstantChange(j,g)
}).bind(this)});this.macroControls=$(".macro-controls");this.macroApplyBtn=$(".save",this.macroControls);this.macroCancelBtn=$(".cancel",this.macroControls);
this.macrosMenu=new DeskPRO.UI.Menu({triggerElement:$(".macros button",c),menuElement:this.getEl("macros_menu"),onItemClicked:(function(h){if($(h.itemEl).data("no-macro")){var g=new DeskPRO.UI.Overlay({contentMethod:"iframe",iframeUrl:BASE_URL+"agent/settings/ticket-macros/new"});
g.openOverlay();return}this.activateMacro($(h.itemEl).data("macro-id"))}).bind(this)});this.macroCancelBtn.click((function(){this.revertMacro()
}).bind(this));this.macroApplyBtn.click((function(){this.saveMacro()}).bind(this))},activateMacro:function(a){this.currentMacroId=a;
$.ajax({url:this.page.getMetaData("getMacroUrl").replace("$macro_id",this.currentMacroId),type:"GET",context:this,dataType:"json",success:function(b){this.previewMacroActions(b);
this.macroOpacityHighlight=$("section.ticket-header, div.messages-wrap").css("opacity","0.4")}})},saveMacro:function(){if(this.changeManager.hasChangedProperty("reply")){this.page.replyBox.saveReply()
}this.changeManager.saveChanges();if(this.macroOpacityHighlight){this.macroOpacityHighlight.css("opacity",1);this.macroOpacityHighlight=null
}this.toggleMacroApplyBtn("off")},revertMacro:function(){this.changeManager.revertChanges();if(this.macroOpacityHighlight){this.macroOpacityHighlight.css("opacity",1);
this.macroOpacityHighlight=null}this.toggleMacroApplyBtn("off")},previewMacroActions:function(a){Array.each(a,function(c){var e=c.action;
var f;delete c.action;var d=Object.values(c);if(d.length==1){f=d[0]}else{f=c}var h=null;var b=/^(.*?)\[(.*?)\]$/.exec(e);
if(b!==null){e=b[1];h=b[2]}var g=this.changeManager.getPropertyManager(e,h);if(g){if(typeOf(f)=="object"&&f.value_display){f=f.value_display
}this.changeManager.addChange(g,f)}else{console.warn("Unknown property `%s`. Actions: %o",e,a)}},this);this.changeManager.applyChanges();
this.toggleMacroApplyBtn("on")},toggleMacroApplyBtn:function(a){if(!a){if(this.macroControls.is(":visible")){a="off"}else{a="on"
}}if(a=="on"){this.macroControls.show()}else{this.macroControls.hide()}},getEl:function(a){return this.page.getEl(a)}});Orb.createNamespace("DeskPRO.Agent.PageFragment.Page.Ticket");
DeskPRO.Agent.PageFragment.Page.Ticket.Participants=new Orb.Class({Implements:[Orb.Util.Events,Orb.Util.Options],initialize:function(c,b){var a=this;
this.page=c;this.options={};this.setOptions(b);this.wrapper=this.page.getEl("ticket_header");$(".agent-participants-edit",this.wrapper).click(this.showAgentParticipants.bind(this));
$(".user-participants-edit",this.wrapper).click(this.showUserParticipants.bind(this))},showAgentParticipants:function(b){if(!this.agentPartsSelector){var a=this;
var c=[];$("li.person",this.page.getEl("agent_part_list")).each(function(){c.push($(this).data("person-id"))});this.agentPartsSelector=new DeskPRO.Agent.Widget.AgentSelector({agentList:$("#agent_selector_list"),multipleChoice:true,startWith:c,onSelectionChanged:function(){a.updateAgentParticipants()
}})}this.agentPartsSelector.open(b)},updateAgentParticipants:function(){var a=this.agentPartsSelector.getSelection();var b=[];
Array.each(a,function(c){b.push({name:"person_ids[]",value:c})});$.ajax({url:BASE_URL+"agent/ticket/"+this.page.meta.ticket_id+"/save-agent-parts",data:b,dataType:"html",type:"POST",context:this,success:function(c){var d=this.page.getEl("agent_part_list");
d.empty().html(c);var e=$("li.person",d).length;$(".agent-part-count",this.wrapper).text(e)}})},showUserParticipants:function(b){if(!this.userFind){var a=this;
this.userFind=new DeskPRO.Agent.Widget.FindPerson({onChoosePerson:function(c){a.addUserPart(c.personId)}})}this.userFind.open(b)
},addUserPart:function(b){var a=[b];$("ul.user-participants-list > li",this.wrapper).each(function(){a.push($(this).data("person-id"))
});var c=[];Array.each(a,function(d){c.push({name:"person_ids[]",value:d})});$.ajax({url:BASE_URL+"agent/ticket/"+this.page.meta.ticket_id+"/save-user-parts",data:c,dataType:"html",type:"POST",context:this,success:function(d){var e=this.page.getEl("user_part_list");
e.empty().html(d);var f=$("li.person",e).length;$(".user-part-count",this.wrapper).text(f)}})}});Orb.createNamespace("DeskPRO.Agent.PageFragment.Page.Ticket");
DeskPRO.Agent.PageFragment.Page.Ticket.TicketFields=new Orb.Class({Implements:[Orb.Util.Events,Orb.Util.Options],initialize:function(b,a){this.options={};
this.page=b;$(".properties-edit-trigger",this.page.wrapper).click(this.showEditor.bind(this))},getEl:function(a){return this.page.getEl(a)
}});Orb.createNamespace("DeskPRO.Agent.PageFragment.Page.PersonHelper");DeskPRO.Agent.PageFragment.Page.PersonHelper.ChangePic=new Orb.Class({Implements:[Orb.Util.Events,Orb.Util.Options],initialize:function(c,b){var a=this;
this.options={loadUrl:"",saveUrl:""};this.setOptions(b);this.page=c;this.page.getEl("change_user_picture").click(this.open.bind(this));
this.page.addEvent("destroy",this.destroy.bind(this))},_initOverlay:function(){if(this.overlay){return}this.wrapperEl=$('<div class="change-person-picture" />');
this.wrapperEl.append("<div>Loading...</div>");this.overlay=new DeskPRO.UI.Overlay({contentElement:this.wrapperEl});$.ajax({url:this.options.loadUrl,type:"GET",dataType:"html",context:this,success:function(a){this.wrapperEl.empty().append(a);
this._initControls()}})},_initControls:function(){this.wrapperEl.fileupload({url:BASE_URL+"agent/misc/accept-upload",dropZone:this.wrapperEl,autoUpload:true,uploadTemplate:$(".template-upload",this.wrapperEl),downloadTemplate:$(".template-download",this.wrapperEl)});
$(".save-trigger",this.wrapperEl).click(this._doSave.bind(this))},_doSave:function(){var b=$(".set_pic_opt:checked",this.wrapperEl).val();
var c=null;var d=null;var e=[];switch(b){case"nochange":this.close();return;case"gravatar":e.push({name:"action",value:"delete-picture"});
c=$("img.pic-gravatar",this.wrapperEl).attr("src");break;case"newpic":e.push({name:"action",value:"set-picture"});var a=$("input.new_blob_id",this.wrapperEl).val();
if(!a){return}e.push({name:"blob_id",value:a});c=$("img.pic-new",this.wrapperEl).attr("src");break;default:return}$.ajax({url:this.options.saveUrl,type:"POST",dataType:"json",data:e});
this.page.getEl("picture_display").attr("src",c);this.close()},open:function(){this._initOverlay();this.overlay.open()},close:function(){if(this.overlay){this.overlay.close()
}},destroy:function(){if(this.overlay){this.overlay.destroy()}}});Orb.createNamespace("DeskPRO.Agent.PageFragment.Page.PersonHelper");
DeskPRO.Agent.PageFragment.Page.PersonHelper.ContactEditor=new Orb.Class({Implements:[Orb.Util.Events,Orb.Util.Options],initialize:function(c,b){var a=this;
this.options={saveUrl:""};this.setOptions(b);this.page=c;this.wrapper=this.page.wrapper;this.page.addEvent("destroy",this.destroy.bind(this));
this.initEditorOverlay()},replaceEditorOverlay:function(a){var b=$(".profile-contact-editor",this.wrapper);b.remove();b=null;
$(a).appendTo(this.wrapper);this.initEditorOverlay()},initEditorOverlay:function(){var a=this;if(this.contactOverlay){this.contactOverlay.destroy();
this.contactOverlay=null}if(this.contactNewMenu){this.contactNewMenu.destroy();this.contactNewMenu=null}var b=$(".profile-contact-editor",this.wrapper);
this.contactOverlay=new DeskPRO.UI.Overlay({triggerElement:$(".contact-edit:first",this.wrapper),contentElement:b});$(".save-trigger",b).click(function(c){var d=$(":input, select, textarea",b).serializeArray();
$.ajax({url:a.options.saveUrl,type:"POST",dataType:"json",data:d,success:function(e){a.contactOverlay.close();$(".contact-list-wrapper",a.wrapper).empty().html(e.display_html);
a.replaceEditorOverlay(e.editor_overlay_html)}})});b.delegate(".remove","click",function(f){var d=$(this);var h=d;while(!h.is("li")){h=h.parent()
}var e=h.data("remove-name");var g=h.data("remove-value");if(e&&g){var c=$('<input type="hidden" />');c.attr("name",e);c.val(g);
c.appendTo($(".contact-edit-list",b))}h.fadeOut("fast",function(){h.remove()})});this.contactNewMenu=new DeskPRO.UI.Menu({triggerElement:$(".add-new-type-trigger",this.wrapper),menuElement:$(".add-new-type-menu:first",this.wrapper),initMenuNow:true,onItemClicked:(function(g){var e=this.contactOverlay.elements.wrapper;
var f=$(g.itemEl);var c=$("."+f.data("tpl"),e).get(0).innerHTML;c=c.replace(/%id%/g,Orb.uuid());var d=$(c);d.appendTo($(".contact-edit-list ul",e))
}).bind(this)})},destroy:function(){if(this.contactOverlay){this.contactOverlay.destroy()}if(this.contactNewMenu){this.contactNewMenu.destroy()
}}});Orb.createNamespace("DeskPRO.Agent.PageFragment.Page.Content");DeskPRO.Agent.PageFragment.Page.Content.DeleteControl=new Orb.Class({Implements:[Orb.Util.Events,Orb.Util.Options],initialize:function(c,b){var a=this;
this.options={ajaxSaveUrl:"",statusMenu:null,type:"delete"};this.setOptions(b);this.page=c;this.deleteBtn=$("."+this.options.type,this.page.getEl("action_buttons"));
this.deletedNotice=$("."+this.options.type+"-notice:first",this.page.wrapper);this.statusBtn=$(".the-status:first",this.page.wrapper);
this.undeleteBtn=$(".un"+this.options.type,this.deletedNotice);this.otherDeleteBtns=$(".delete-type:not(."+this.options.type+")",this.page.getEl("action_buttons"));
this.undeleteBtn.click(function(d){d.customEvents=new Orb.Util.EventObj({onItemClicked:function(){a.handleUndelete()}});a.options.statusMenu.open(d)
});this.deleteBtn.click(function(){a.handleDeleted();$.ajax({url:a.options.ajaxSaveUrl,data:{action:a.options.type},type:"GET",dataType:"json",error:function(){a.handleUndelete()
},success:function(d){}})})},undelete:function(){},handleDeleted:function(){this.deleteBtn.hide();this.statusBtn.hide();this.deletedNotice.show();
this.otherDeleteBtns.hide()},handleUndelete:function(){this.deleteBtn.show();this.statusBtn.show();this.deletedNotice.hide();
this.otherDeleteBtns.show()}});Orb.createNamespace("DeskPRO.Agent.PageFragment.Page.Content");DeskPRO.Agent.PageFragment.Page.Content.StickyWords=new Orb.Class({Implements:[Orb.Util.Events,Orb.Util.Options],initialize:function(d,c){var b=this;
this.page=d;this.options={saveUrl:null,contentType:"",contentId:0,element:null};this.setOptions(c);if(!this.options.saveUrl){this.options.saveUrl=BASE_URL+"agent/publish/save-sticky-search-words/"+this.options.contentType+"/"+this.options.contentId
}var a={enableBackspace:false,fieldName:"sticky_search_words",onchange:(function(){this._updated()}).bind(this)};this.tagit=$(this.options.element).addClass("tagit").tagit(a)
},_updated:function(){var c=this.tagit.getLabels();var a={labels:c,cancel:false};this.fireEvent("change",[a]);if(a.cancel){return
}var b=[];Array.each(c,function(d){b.push({name:"words[]",value:d})});$.ajax({url:this.options.saveUrl,data:b,type:"POST"})
}});Orb.createNamespace("DeskPRO.Agent.PageFragment.Page");DeskPRO.Agent.PageFragment.Page.NewArticle=new Class({Extends:DeskPRO.Agent.PageFragment.Basic,allowDupe:true,TYPENAME:"newarticle",initPage:function(c){this.wrapper=c;
this.contentWrapper=this.wrapper.children(".layout-content").attr("id",Orb.getUniqueId());this.parent(c);var a=this.contentWrapper;
a.tinyscrollbar();var b=this;$("div.scroll-content:first, div.scroll-viewport:first",this.contentWrapper).resize(function(){a.tinyscrollbar_update();
b.fireEvent("resized")});this.form=$("form",this.wrapper).submit(function(d){d.preventDefault()});$("button.submit-trigger",this.wrapper).click(this.submit.bind(this));
this._initCategorySection();this._initTitleSection();this._initContentSection();this._initOtherSection();this.stateSaver=new DeskPRO.Agent.PageHelper.StateSaver({stateId:"newarticle",listenOn:this.getEl("newarticle")})
},closeSelf:function(){var a={cancel:false};this.fireEvent("closeSelf",a);if(!a.cancel){this.parent()}},submit:function(){var a=this.form.serializeArray();
$.ajax({url:BASE_URL+"agent/kb/article/new/save",type:"POST",data:a,dataType:"json",context:this,success:function(c){var b=this.getEl("pending_article_id").val();
if(b){DeskPRO_Window.getMessageBroker().sendMessage("kb.pending_article_removed",{pending_article_id:b})}if(c.success){DeskPRO_Window.runPageRoute("page:"+BASE_URL+"agent/kb/article/"+c.article_id);
this.closeSelf()}else{alert("There was an error with the form")}}})},setTitle:function(a){this.getEl("title").val(a).change()
},setContent:function(a,b){if(!b){a=Orb.escapeHtml(a)}this.getEl("content").html(a)},setPendingArticle:function(b){this.getEl("pending_article_id").val(b.id);
if(b.ticket_subject){this.setTitle(b.ticket_subject)}if(b.message_content_html){this.setContent(b.message_content_html,true)
}var a=$(".pending-info:first",this.wrapper);if(b.ticket_url){$(".pending-ticket a",a).text(b.ticket_subject);$(".pending-ticket a",a).data("route","page:"+b.ticket_url);
$(".pending-ticket",a).show()}else{$(".pending-reason",a).text(b.comment).show()}$(".person-name",a).text(b.person_name);
a.show()},_initCategorySection:function(){var a=this;this.getEl("cat").change(function(){if(parseInt($(this).val())){a.getEl("cat_section").addClass("done")
}else{a.getEl("cat_section").removeClass("done")}})},_initTitleSection:function(){var a=this;var b=function(){if($(this).val().trim()==""){a.getEl("title_section").removeClass("done")
}else{a.getEl("title_section").addClass("done")}};this.getEl("title").change(b).keypress(b).change(function(){var c=$(this).val().trim().toLowerCase();
c=c.replace(/[^a-z0-9\-_]/g,"-");c=c.replace(/-{2,}/g,"-");a.getEl("slug").val(c)})},_initContentSection:function(){var b=this;
var d=this.getEl("content").offset().top;var a=this.wrapper.offset().top+this.wrapper.height();var c=a-d-100;this.getEl("content").css({width:this.wrapper.width()-80,height:c});
this.getEl("content").tinymce({script_url:ASSETS_BASE_URL+"/vendor/tiny_mce/tiny_mce.js",theme:"advanced",plugins:"fullscreen",fullscreen_new_window:true,theme_advanced_buttons1:"bold,italic,underline,|,justifyleft,justifycenter,justifyright,|,fontselect,fontsizeselect,formatselect",theme_advanced_buttons2:",bullist,numlist,|,outdent,indent,|,link,unlink,anchor,image,|,code,removeformat,fullscreen",theme_advanced_buttons3:"",theme_advanced_toolbar_location:"top",theme_advanced_toolbar_align:"left",theme_advanced_resizing:true,theme_advanced_statusbar_location:"bottom",setup:function(e){e.onClick.add(function(){b.getEl("content_section").addClass("done")
});e.onKeyPress.add(function(){b.stateSaver.triggerChange()})}})},_initOtherSection:function(){this.otherTabs=new DeskPRO.UI.SimpleTabs({triggerElements:$("li",this.getEl("other_props_tabs")),context:this.getEl("other_props_tabs_content"),autoSelectFirst:false,onTabClick:(function(e){var f=this.getEl("other_props_tabs_content");
var c=this.getEl("other_props_tabs_wrap");var d=e.tabEl;if(!$(".on",c).length||d.is(".on")){if(f.is(":visible")){f.slideUp();
c.removeClass("on")}else{window.setTimeout(function(){f.slideDown()},20);c.addClass("on")}}}).bind(this)});var a=this;this.labelsInput=new DeskPRO.UI.LabelsInput({type:"articles",fieldName:"newarticle[labels]",list:$(".tags-wrap ul",this.wrapper),onChange:function(){a.stateSaver.triggerChange()
}});this.getEl("slug").focus(function(){this.addClass("had-focus")});var b=$(".file-list",this.wrapper);$("input",b[0]).live("click",function(){var d=$(this);
var c=d.parent();if(d.is(":checked")){c.removeClass("unchecked")}else{c.addClass("unchecked")}});this.wrapper.fileupload({url:BASE_URL+"agent/misc/accept-upload",dropZone:this.wrapper,autoUpload:true,uploadTemplate:$(".template-upload",this.wrapper),downloadTemplate:$(".template-download",this.wrapper)})
}});Orb.createNamespace("DeskPRO.Agent.PageFragment.Page");DeskPRO.Agent.PageFragment.Page.NewPerson=new Class({Extends:DeskPRO.Agent.PageFragment.Basic,allowDupe:true,TYPENAME:"newperson",initPage:function(f){this.wrapper=f;
this.contentWrapper=this.wrapper.children(".layout-content").attr("id",Orb.getUniqueId());this.parent(f);var b=this.contentWrapper;
b.tinyscrollbar();var c=this;$("div.scroll-content:first, div.scroll-viewport:first",this.contentWrapper).resize(function(){b.tinyscrollbar_update();
c.fireEvent("resized")});this.form=$("form",this.wrapper).submit(function(g){g.preventDefault()});$("button.submit-trigger",this.wrapper).click(this.submit.bind(this));
this._initNameSection();this._initOtherSection();this.stateSaver=new DeskPRO.Agent.PageHelper.StateSaver({stateId:"newperson",listenOn:this.getEl("newperson")});
var e=$("#organizations_select").clone().appendTo(this.getEl("org_container"));e.data("placeholder","Select an existing organization");
e.attr("name","newperson[organization_id]");e.css("width",200);e.prepend("<option selected />");e.chosen();var a=$("#usergroups_select").clone().appendTo(this.getEl("ug_container"));
a.data("placeholder","Choose usergroups");a.attr("name","newperson[usergroup_ids][]");a.attr("multiple","multiple");a.css("width","400");
a.prepend("<option selected />");a.chosen();var d=this.getEl("timezone");d.chosen()},closeSelf:function(){var a={cancel:false};
this.fireEvent("closeSelf",a);if(!a.cancel){this.parent()}},submit:function(){var a=this.form.serializeArray();$.ajax({url:BASE_URL+"agent/people/new/save",type:"POST",data:a,dataType:"json",context:this,success:function(b){if(b.success){DeskPRO_Window.runPageRoute("person:"+BASE_URL+"agent/people/"+b.person_id);
this.closeSelf()}else{alert("There was an error with the form")}}})},_initNameSection:function(){},_initOtherSection:function(){this.otherTabs=new DeskPRO.UI.SimpleTabs({triggerElements:$("li",this.getEl("other_props_tabs")),context:this.getEl("other_props_tabs_content"),autoSelectFirst:false,onTabClick:(function(d){var e=this.getEl("other_props_tabs_content");
var b=this.getEl("other_props_tabs_wrap");var c=d.tabEl;if(!$(".on",b).length||c.is(".on")){if(e.is(":visible")){e.slideUp();
b.removeClass("on")}else{window.setTimeout(function(){e.slideDown()},20);b.addClass("on")}}}).bind(this)});var a=this;this.labelsInput=new DeskPRO.UI.LabelsInput({type:"people",fieldName:"newperson[labels]",list:$(".tags-wrap ul",this.wrapper),onChange:function(){a.stateSaver.triggerChange()
}})}});Orb.createNamespace("DeskPRO.Agent.PageFragment.Page");DeskPRO.Agent.PageFragment.Page.NewOrganization=new Class({Extends:DeskPRO.Agent.PageFragment.Basic,allowDupe:true,TYPENAME:"neworganization",initPage:function(d){this.wrapper=d;
this.contentWrapper=this.wrapper.children(".layout-content").attr("id",Orb.getUniqueId());this.parent(d);var b=this.contentWrapper;
b.tinyscrollbar();var c=this;$("div.scroll-content:first, div.scroll-viewport:first",this.contentWrapper).resize(function(){b.tinyscrollbar_update();
c.fireEvent("resized")});this.form=$("form",this.wrapper).submit(function(e){e.preventDefault()});$("button.submit-trigger",this.wrapper).click(this.submit.bind(this));
this._initNameSection();this._initOtherSection();this.stateSaver=new DeskPRO.Agent.PageHelper.StateSaver({stateId:"neworg",listenOn:this.getEl("neworg")});
var a=$("#usergroups_select").clone().appendTo(this.getEl("ug_container"));a.data("placeholder","Choose usergroups");a.attr("name","newperson[usergroup_ids][]");
a.attr("multiple","multiple");a.css("width","400");a.prepend("<option selected />");a.chosen()},closeSelf:function(){var a={cancel:false};
this.fireEvent("closeSelf",a);if(!a.cancel){this.parent()}},submit:function(){var a=this.form.serializeArray();$.ajax({url:BASE_URL+"agent/organizations/new/save",type:"POST",data:a,dataType:"json",context:this,success:function(b){if(b.success){DeskPRO_Window.runPageRoute("person:"+BASE_URL+"agent/organizations/"+b.org_id);
this.closeSelf()}else{alert("There was an error with the form")}}})},_initNameSection:function(){},_initOtherSection:function(){this.otherTabs=new DeskPRO.UI.SimpleTabs({triggerElements:$("li",this.getEl("other_props_tabs")),context:this.getEl("other_props_tabs_content"),autoSelectFirst:false,onTabClick:(function(d){var e=this.getEl("other_props_tabs_content");
var b=this.getEl("other_props_tabs_wrap");var c=d.tabEl;if(!$(".on",b).length||c.is(".on")){if(e.is(":visible")){e.slideUp();
b.removeClass("on")}else{window.setTimeout(function(){e.slideDown()},20);b.addClass("on")}}}).bind(this)});var a=this;this.labelsInput=new DeskPRO.UI.LabelsInput({type:"people",fieldName:"neworg[labels]",list:$(".tags-wrap ul",this.wrapper),onChange:function(){a.stateSaver.triggerChange()
}})}});Orb.createNamespace("DeskPRO.Agent.PageFragment.Page");DeskPRO.Agent.PageFragment.Page.NewDownload=new Class({Extends:DeskPRO.Agent.PageFragment.Basic,allowDupe:true,TYPENAME:"newdownload",initPage:function(c){this.wrapper=c;
this.contentWrapper=this.wrapper.children(".layout-content").attr("id",Orb.getUniqueId());this.parent(c);var a=this.contentWrapper;
a.tinyscrollbar();var b=this;$("div.scroll-content:first, div.scroll-viewport:first",this.contentWrapper).resize(function(){a.tinyscrollbar_update();
b.fireEvent("resized")});this.form=$("form",this.wrapper).submit(function(d){d.preventDefault()});$("button.submit-trigger",this.wrapper).click(this.submit.bind(this));
this._initCategorySection();this._initTitleSection();this._initFileSection();this._initContentSection();this._initOtherSection();
this.stateSaver=new DeskPRO.Agent.PageHelper.StateSaver({stateId:"newdownload",listenOn:this.getEl("newdownload")})},closeSelf:function(){var a={cancel:false};
this.fireEvent("closeSelf",a);if(!a.cancel){this.parent()}},submit:function(){var a=this.form.serializeArray();$.ajax({url:BASE_URL+"agent/downloads/new/save",type:"POST",data:a,dataType:"json",context:this,success:function(b){if(b.success){DeskPRO_Window.runPageRoute("page:"+BASE_URL+"agent/downloads/file/"+b.download_id);
this.closeSelf()}else{alert("There was an error with the form")}}})},_initCategorySection:function(){var a=this;this.getEl("cat").change(function(){if(parseInt($(this).val())){a.getEl("cat_section").addClass("done")
}else{a.getEl("cat_section").removeClass("done")}})},_initTitleSection:function(){var a=this;var b=function(){if($(this).val().trim()==""){a.getEl("title_section").removeClass("done")
}else{a.getEl("title_section").addClass("done")}};this.getEl("title").change(b).keypress(b).change(function(){var c=$(this).val().trim().toLowerCase();
c=c.replace(/[^a-z0-9\-_]/g,"-");c=c.replace(/-{2,}/g,"-");a.getEl("slug").val(c)})},_initFileSection:function(){var a=this;
var b=$(".file-list",this.wrapper);$("input",b[0]).live("click",function(){var d=$(this);var c=d.parent();if(d.is(":checked")){c.removeClass("unchecked")
}else{c.addClass("unchecked")}});this.wrapper.fileupload({url:BASE_URL+"agent/misc/accept-upload",dropZone:this.wrapper,autoUpload:true,uploadTemplate:$(".template-upload",this.wrapper),downloadTemplate:$(".template-download",this.wrapper)});
this.wrapper.bind("fileuploaddone",function(){a.getEl("file_section").addClass("done")});this.wrapper.bind("fileuploadadd",function(){$("ul.file-list",a.wrapper).empty()
})},_initContentSection:function(){var b=this;var d=this.getEl("content").offset().top;var a=this.wrapper.offset().top+this.wrapper.height();
var c=a-d-100;this.getEl("content").css({width:this.wrapper.width()-80,height:c});this.getEl("content").tinymce({script_url:ASSETS_BASE_URL+"/vendor/tiny_mce/tiny_mce.js",theme:"advanced",plugins:"fullscreen",fullscreen_new_window:true,theme_advanced_buttons1:"bold,italic,underline,|,justifyleft,justifycenter,justifyright,|,fontselect,fontsizeselect,formatselect",theme_advanced_buttons2:",bullist,numlist,|,outdent,indent,|,link,unlink,anchor,image,|,code,removeformat,fullscreen",theme_advanced_buttons3:"",theme_advanced_toolbar_location:"top",theme_advanced_toolbar_align:"left",theme_advanced_resizing:true,theme_advanced_statusbar_location:"bottom",setup:function(e){e.onClick.add(function(){b.getEl("content_section").addClass("done")
});e.onKeyPress.add(function(){b.stateSaver.triggerChange()})}})},_initOtherSection:function(){this.otherTabs=new DeskPRO.UI.SimpleTabs({triggerElements:$("li",this.getEl("other_props_tabs")),context:this.getEl("other_props_tabs_content"),autoSelectFirst:false,onTabClick:(function(d){var e=this.getEl("other_props_tabs_content");
var b=this.getEl("other_props_tabs_wrap");var c=d.tabEl;if(!$(".on",b).length||c.is(".on")){if(e.is(":visible")){e.slideUp();
b.removeClass("on")}else{window.setTimeout(function(){e.slideDown()},20);b.addClass("on")}}}).bind(this)});var a=this;this.labelsInput=new DeskPRO.UI.LabelsInput({type:"downloads",fieldName:"newdownload[labels]",list:$(".tags-wrap ul",this.wrapper),onChange:function(){a.stateSaver.triggerChange()
}});this.getEl("slug").focus(function(){this.addClass("had-focus")})}});Orb.createNamespace("DeskPRO.Agent.PageFragment.Page");
DeskPRO.Agent.PageFragment.Page.NewNews=new Class({Extends:DeskPRO.Agent.PageFragment.Basic,allowDupe:true,TYPENAME:"newnews",initPage:function(c){this.wrapper=c;
this.contentWrapper=this.wrapper.children(".layout-content").attr("id",Orb.getUniqueId());this.parent(c);var a=this.contentWrapper;
a.tinyscrollbar();var b=this;$("div.scroll-content:first, div.scroll-viewport:first",this.contentWrapper).resize(function(){a.tinyscrollbar_update();
b.fireEvent("resized")});this.form=$("form",this.wrapper).submit(function(d){d.preventDefault()});$("button.submit-trigger",this.wrapper).click(this.submit.bind(this));
this._initCategorySection();this._initTitleSection();this._initContentSection();this._initOtherSection();this.stateSaver=new DeskPRO.Agent.PageHelper.StateSaver({stateId:"c",listenOn:this.getEl("newnews")})
},closeSelf:function(){var a={cancel:false};this.fireEvent("closeSelf",a);if(!a.cancel){this.parent()}},submit:function(){var a=this.form.serializeArray();
$.ajax({url:BASE_URL+"agent/news/new/save",type:"POST",data:a,dataType:"json",context:this,success:function(b){if(b.success){DeskPRO_Window.runPageRoute("page:"+BASE_URL+"agent/news/"+b.news_id);
this.closeSelf()}else{alert("There was an error with the form")}}})},_initCategorySection:function(){var a=this;this.getEl("cat").change(function(){if(parseInt($(this).val())){a.getEl("cat_section").addClass("done")
}else{a.getEl("cat_section").removeClass("done")}})},_initTitleSection:function(){var a=this;var b=function(){if($(this).val().trim()==""){a.getEl("title_section").removeClass("done")
}else{a.getEl("title_section").addClass("done")}};this.getEl("title").change(b).keypress(b).change(function(){var c=$(this).val().trim().toLowerCase();
c=c.replace(/[^a-z0-9\-_]/g,"-");c=c.replace(/-{2,}/g,"-");a.getEl("slug").val(c)})},_initContentSection:function(){var b=this;
var d=this.getEl("content").offset().top;var a=this.wrapper.offset().top+this.wrapper.height();var c=a-d-100;this.getEl("content").css({width:this.wrapper.width()-80,height:c});
this.getEl("content").tinymce({script_url:ASSETS_BASE_URL+"/vendor/tiny_mce/tiny_mce.js",theme:"advanced",plugins:"fullscreen",fullscreen_new_window:true,theme_advanced_buttons1:"bold,italic,underline,|,justifyleft,justifycenter,justifyright,|,fontselect,fontsizeselect,formatselect",theme_advanced_buttons2:",bullist,numlist,|,outdent,indent,|,link,unlink,anchor,image,|,code,removeformat,fullscreen",theme_advanced_buttons3:"",theme_advanced_toolbar_location:"top",theme_advanced_toolbar_align:"left",theme_advanced_resizing:true,theme_advanced_statusbar_location:"bottom",setup:function(e){e.onClick.add(function(){b.getEl("content_section").addClass("done")
});e.onKeyPress.add(function(){b.stateSaver.triggerChange()})}})},_initOtherSection:function(){this.otherTabs=new DeskPRO.UI.SimpleTabs({triggerElements:$("li",this.getEl("other_props_tabs")),context:this.getEl("other_props_tabs_content"),autoSelectFirst:false,onTabClick:(function(e){var f=this.getEl("other_props_tabs_content");
var c=this.getEl("other_props_tabs_wrap");var d=e.tabEl;if(!$(".on",c).length||d.is(".on")){if(f.is(":visible")){f.slideUp();
c.removeClass("on")}else{window.setTimeout(function(){f.slideDown()},20);c.addClass("on")}}}).bind(this)});var a=this;this.labelsInput=new DeskPRO.UI.LabelsInput({type:"news",fieldName:"newnews[labels]",list:$(".tags-wrap ul",this.wrapper),onChange:function(){a.stateSaver.triggerChange()
}});this.getEl("slug").focus(function(){this.addClass("had-focus")});var b=$(".file-list",this.wrapper);$("input",b[0]).live("click",function(){var d=$(this);
var c=d.parent();if(d.is(":checked")){c.removeClass("unchecked")}else{c.addClass("unchecked")}});this.wrapper.fileupload({url:BASE_URL+"agent/misc/accept-upload",dropZone:this.wrapper,autoUpload:true,uploadTemplate:$(".template-upload",this.wrapper),downloadTemplate:$(".template-download",this.wrapper)})
}});Orb.createNamespace("DeskPRO.Agent.PageFragment.Page");DeskPRO.Agent.PageFragment.Page.NewTicket=new Class({Extends:DeskPRO.Agent.PageFragment.Basic,allowDupe:true,TYPENAME:"newticket",initPage:function(b){this.wrapper=b;
this.contentWrapper=this.wrapper.children(".layout-content").attr("id",Orb.getUniqueId());this.parent(b);var a=this.contentWrapper;
a.tinyscrollbar();$("div.scroll-content:first, div.scroll-viewport:first",this.contentWrapper).resize(function(){a.tinyscrollbar_update()
});this.form=$("form",this.wrapper).submit(function(c){c.preventDefault()});this._initUserSection();this._initDepartmentSection();
this._initSubjectSection();this._initMessageSection();this._initOtherSection();$("button.submit-trigger",this.wrapper).click(this.submit.bind(this))
},closeSelf:function(){var a={cancel:false};this.fireEvent("closeSelf",a);if(!a.cancel){this.parent()}},submit:function(){var a=this.form.serializeArray();
$.ajax({url:BASE_URL+"agent/tickets/new/save",type:"POST",data:a,dataType:"json",context:this,success:function(b){if(b.success){if(b.comment_id){DeskPRO_Window.getMessageBroker().sendMessage("agent-ui.comment-remove",{comment_id:b.comment_id,comment_type:b.comment_type})
}DeskPRO_Window.runPageRoute("ticket:"+BASE_URL+"agent/tickets/"+b.ticket_id);this.closeSelf()}else{alert("There was an error with the form")
}}})},setNewByComment:function(a){this.getEl("message").val(a.message);this.getEl("for_comment_type").val(a.content_type);
this.getEl("for_comment_id").val(a.comment_id);$(".pending-info",this.wrapper).show();this.getEl("comment_object_link").data("route","page:"+a.object_url).text(a.object_title);
this.getEl("usersearch").val(a.email_address);this.setUser(a.person_id);if(a.status=="validating"){$('option[value="approve"]',this.getEl("comment_action")).hide()
}else{$('option[value="approve"]',this.getEl("comment_action")).show()}},_initUserSection:function(){var a=this;this.getEl("me_btn").click((function(c){c.preventDefault();
var b=DeskPRO_Window.getAgentInfo(DESKPRO_PERSON_ID);this.getEl("usersearch").val(b.email);this.setUser(b.id)}).bind(this));
this.getEl("usersearch").autocomplete({focus:true,delay:300,minLength:2,source:function(b,c){$.ajax({timeout:8000,type:"POST",url:BASE_URL+"agent/people-search/search-quick",data:{term:b.term,format:"json",limit:20},dataType:"json",context:this,success:function(d){console.log(d);
c(d)},complete:function(){this.usersearchAjaxQuitCount=0;this.usersearchAjax=null}})},select:(function(b,c){this.setUser(c.item.value);
b.preventDefault();this.getEl("usersearch").val(c.item.email)}).bind(this)});this.getEl("usersearch").blur((function(){if(!$("input.person_id",this.wrapper).length){this.setUser(0)
}}).bind(this));this.getEl("usersearch").keypress((function(b){if(b.keyCode==13&&!b.metaKey){if(!parseInt(this.val("person_id").val())){this.setUser(0)
}}}).bind(this))},clearUser:function(){this.getEl("userinfo").hide().empty();this.getEl("new_userinfo").hide()},setUser:function(a){this.getEl("user_section").removeClass("done");
var b=[];a=parseInt(a)||0;if(!a){b.push({name:"email_address",value:this.getEl("usersearch").val()});this.getEl("person_id").val(0)
}else{this.getEl("person_id").val(a)}$.ajax({type:"GET",url:BASE_URL+"agent/tickets/new/get-person-row/"+a,data:b,dataType:"html",context:this,success:function(d){this.getEl("new_userinfo").hide();
this.getEl("userinfo").empty().html(d).show();var e=parseInt($(".person_id",this.getEl("userinfo")).val());this.getEl("person_id").val(e);
if(e){this.getEl("user_section").addClass("done")}var c=this;$("button.more-fields",this.getEl("userinfo")).click(function(){c.getEl("user_section").addClass("more-on");
$(this).remove()})}})},setGuestUser:function(){this.getEl("userinfo").hide();this.getEl("new_userinfo").show()},_initDepartmentSection:function(){var a=this;
this.getEl("dep").change(function(){if(parseInt($(this).val())){a.getEl("dep_section").addClass("done")}else{a.getEl("dep_section").removeClass("done")
}})},_initSubjectSection:function(){var a=this;var b=function(){if($(this).val().trim()==""){a.getEl("subject_section").removeClass("done")
}else{a.getEl("subject_section").addClass("done")}};this.getEl("subject").change(b).blur(b).keypress(b)},_initMessageSection:function(){var a=this;
var b=function(){if($(this).val().trim()==""){a.getEl("message_section").removeClass("done")}else{a.getEl("message_section").addClass("done")
}};this.getEl("message").change(b).blur(b).keypress(b)},_initOtherSection:function(){this.otherTabs=new DeskPRO.UI.SimpleTabs({triggerElements:$("li",this.getEl("other_props_tabs")),context:this.getEl("other_props_tabs_content"),autoSelectFirst:false,onTabClick:(function(e){var f=this.getEl("other_props_tabs_content");
var c=this.getEl("other_props_tabs_wrap");var d=e.tabEl;if(!$(".on",c).length||d.is(".on")){if(f.is(":visible")){f.slideUp();
c.removeClass("on")}else{window.setTimeout(function(){f.slideDown()},20);c.addClass("on")}}}).bind(this)});this.assignAgentSelector=new DeskPRO.Agent.Widget.AgentSelector({agentList:$("#agent_selector_list"),showNone:true,multipleChoice:false,triggerElement:this.getEl("assign_agent_choose"),onSelectionChanged:(function(e){var d=parseInt(e.selection);
if(!e.selection){this.getEl("assigned_agent").text("Unassigned");this.getEl("agent_id").val("0")}else{var c=DeskPRO_Window.getAgentInfo(d);
this.getEl("assigned_agent").text(c.name);this.getEl("agent_id").val(d)}}).bind(this)});var a=this;$(".add-cc-trigger",this.wrapper).click(function(){var c=a.getEl("add_cc_txt");
var e=c.val();var d=$("<li>"+e+'<input type="hidden" name="newticket[new_parts][]" value="'+e+'" />&nbsp;&nbsp;<span class="remove-trigger" style="cursor: pointer;">x</span></li>');
$(".remove-trigger",d).click(function(f){f.preventDefault();f.stopPropagation();d.remove()});d.appendTo(a.getEl("cc_list"));
c.val("")});var b=$(".file-list",this.wrapper);$("input",b[0]).live("click",function(){var d=$(this);var c=d.parent();if(d.is(":checked")){c.removeClass("unchecked")
}else{c.addClass("unchecked")}});this.wrapper.fileupload({url:BASE_URL+"agent/misc/accept-upload",dropZone:this.wrapper,autoUpload:true,uploadTemplate:$(".template-upload",this.wrapper),downloadTemplate:$(".template-download",this.wrapper)})
}});Orb.createNamespace("DeskPRO.Agent.PageFragment.Page");DeskPRO.Agent.PageFragment.Page.NewIdea=new Class({Extends:DeskPRO.Agent.PageFragment.Basic,allowDupe:true,TYPENAME:"newidea",initPage:function(c){this.wrapper=c;
this.contentWrapper=this.wrapper.children(".layout-content").attr("id",Orb.getUniqueId());this.parent(c);var a=this.contentWrapper;
a.tinyscrollbar();var b=this;$("div.scroll-content:first, div.scroll-viewport:first",this.contentWrapper).resize(function(){a.tinyscrollbar_update();
b.fireEvent("resized")});this.form=$("form",this.wrapper).submit(function(d){d.preventDefault()});$("button.submit-trigger",this.wrapper).click(this.submit.bind(this));
this._initCategorySection();this._initTitleSection();this._initContentSection();this._initOtherSection();this.stateSaver=new DeskPRO.Agent.PageHelper.StateSaver({stateId:"newnews",listenOn:this.getEl("newidea")})
},closeSelf:function(){var a={cancel:false};this.fireEvent("closeSelf",a);if(!a.cancel){this.parent()}},submit:function(){var a=this.form.serializeArray();
$.ajax({url:BASE_URL+"agent/ideas/new/save",type:"POST",data:a,dataType:"json",context:this,success:function(b){if(b.success){DeskPRO_Window.runPageRoute("page:"+BASE_URL+"agent/ideas/view/"+b.idea_id);
this.closeSelf()}else{alert("There was an error with the form")}}})},_initCategorySection:function(){var a=this;this.getEl("cat").change(function(){if(parseInt($(this).val())){a.getEl("cat_section").addClass("done")
}else{a.getEl("cat_section").removeClass("done")}})},_initTitleSection:function(){var a=this;var b=function(){if($(this).val().trim()==""){a.getEl("title_section").removeClass("done")
}else{a.getEl("title_section").addClass("done")}};this.getEl("title").change(b).keypress(b).change(function(){var c=$(this).val().trim().toLowerCase();
c=c.replace(/[^a-z0-9\-_]/g,"-");c=c.replace(/-{2,}/g,"-");a.getEl("slug").val(c)})},_initContentSection:function(){var b=this;
var d=this.getEl("content").offset().top;var a=this.wrapper.offset().top+this.wrapper.height();var c=a-d-100;this.getEl("content").css({width:this.wrapper.width()-80,height:c});
this.getEl("content").tinymce({script_url:ASSETS_BASE_URL+"/vendor/tiny_mce/tiny_mce.js",theme:"advanced",plugins:"fullscreen",fullscreen_new_window:true,theme_advanced_buttons1:"bold,italic,underline,|,justifyleft,justifycenter,justifyright,|,fontselect,fontsizeselect,formatselect",theme_advanced_buttons2:",bullist,numlist,|,outdent,indent,|,link,unlink,anchor,image,|,code,removeformat,fullscreen",theme_advanced_buttons3:"",theme_advanced_toolbar_location:"top",theme_advanced_toolbar_align:"left",theme_advanced_resizing:true,theme_advanced_statusbar_location:"bottom",setup:function(e){e.onClick.add(function(){b.getEl("content_section").addClass("done")
});e.onKeyPress.add(function(){b.stateSaver.triggerChange()})}})},_initOtherSection:function(){this.otherTabs=new DeskPRO.UI.SimpleTabs({triggerElements:$("li",this.getEl("other_props_tabs")),context:this.getEl("other_props_tabs_content"),autoSelectFirst:false,onTabClick:(function(d){var e=this.getEl("other_props_tabs_content");
var b=this.getEl("other_props_tabs_wrap");var c=d.tabEl;if(!$(".on",b).length||c.is(".on")){if(e.is(":visible")){e.slideUp();
b.removeClass("on")}else{window.setTimeout(function(){e.slideDown()},20);b.addClass("on")}}}).bind(this)});var a=this;this.labelsInput=new DeskPRO.UI.LabelsInput({type:"news",fieldName:"newnews[labels]",list:$(".tags-wrap ul",this.wrapper),onChange:function(){a.stateSaver.triggerChange()
}});this.getEl("slug").focus(function(){this.addClass("had-focus")})}});Orb.createNamespace("DeskPRO.Agent.PageFragment.Page");
DeskPRO.Agent.PageFragment.Page.Organization=new Class({Extends:DeskPRO.Agent.PageFragment.Basic,TYPENAME:"organization",wrapper:null,initPage:function(e){this.wrapper=e;
this.contentWrapper=$("div.layout-content:first",e);var b=this;var a=this.contentWrapper;a.tinyscrollbar();$("div.scroll-content:first, div.scroll-viewport:first",this.contentWrapper).resize(function(){a.tinyscrollbar_update()
});this.contactEditor=new DeskPRO.Agent.PageFragment.Page.PersonHelper.ContactEditor(this,{saveUrl:BASE_URL+"agent/organizations/"+this.meta.org_id+"/save-contact-data.json"});
this.initNoteFormEditable();this.initTimesOnCollection($("time.timeago",this.wrapper));var c=$("h3.name.editable:first",e);
if(!c.attr("id")){c.attr("id",Orb.getUniqueId())}var d=new DeskPRO.Form.InlineEdit({baseElement:this.wrapper,ajax:{url:BASE_URL+"agent/organizations/"+this.meta.org_id+"/ajax-save"}});
$(this.wrapper).click(function(f){d.handleDocumentClick(f)});this.morectionsMenu=new DeskPRO.UI.Menu({triggerElement:$(".more",this.getEl("action_buttons")),menuElement:this.getEl("more_actions_menu"),onItemClicked:function(g){var f=$(g.itemEl).data("action")
}});this.changePic=new DeskPRO.Agent.PageFragment.Page.PersonHelper.ChangePic(this,{loadUrl:BASE_URL+"agent/organizations/"+this.meta.org_id+"/change-picture-overlay",saveUrl:BASE_URL+"agent/organizations/"+this.meta.org_id+"/ajax-save"});
this._initLabels();this._initCustomFieldsEditor();this.getEl("members_list").delegate(".remove","click",function(){var g=$(this).closest(".member-row");
var f=g.data("person-id");if(!f){return}g.fadeOut("fast");$.ajax({url:BASE_URL+"agent/organizations/"+b.meta.org_id+"/ajax-save",data:{action:"remove-person",person_id:f},type:"POST",error:function(){g.show()
},success:function(){g.remove()}})});this.getEl("newmember_person_input").autocomplete({focus:true,delay:300,minLength:2,source:function(f,g){$.ajax({timeout:8000,type:"POST",url:BASE_URL+"agent/people-search/search-quick",data:{term:f.term,format:"json",limit:20},dataType:"json",context:this,success:function(h){g(h)
}})},select:(function(f,g){f.preventDefault();b.getEl("newmember_person_input").val(g.item.email);b.getEl("newmember_person_id").val(g.item.value)
}).bind(this)});this.getEl("newmember_btn").click(function(){var f=b.getEl("newmember_person_id").val();var g=b.getEl("newmember_position").val();
$.ajax({url:BASE_URL+"agent/organizations/"+b.meta.org_id+"/ajax-save",data:{action:"add-person",person_id:f,position:g},type:"POST",success:function(h){b.getEl("newmember_person_input").val("");
b.getEl("newmember_position").val("");b.getEl("newmember_person_id").val("0");var i=$(h.row_html);i.insertAfter(b.getEl("newmember_row"));
DeskPRO_Window.util.showSavePuff(i)}})})},_initCustomFieldsEditor:function(){var c,b;c=this.fieldsRenderedWrap=this.getEl("custom_fields_rendered");
b=this.fieldsEditWrap=this.getEl("custom_fields_editable");var a=(function(){if(c.is(":visible")){c.hide();b.show()}else{b.hide();
c.show()}}).bind(this);$(".show-edit-custom-fields",this.wrapper).click(function(){a()});$(".save-custom-fields",this.wrapper).click((function(){var d=$("input, select, textarea",b).serializeArray();
$.ajax({url:BASE_URL+"agent/organizations/"+this.meta.org_id+"/ajax-save-custom-fields",type:"POST",data:d,dataType:"html",success:function(e){c.empty().html(e);
a()}})}).bind(this))},labelsList:null,_initLabels:function(){this.labelsList=$(".org-tags ul",this.wrapper).tagit({availableTags:this.getMetaData("labelsAutocompleteUrl"),enableBackspace:false,fieldName:"labels",onchange:this.saveLabels.bind(this)})
},_saveLabelsTimeout:null,saveLabels:function(){if(this._saveLabelsTimeout){window.clearTimeout(this._saveLabelsTimeout)}this._saveLabelsTimeout=this._doSaveLabels.delay(2000,this)
},_doSaveLabels:function(){var a=$(":input",this.labelsList).serializeArray();$.ajax({url:this.getMetaData("labelsSaveUrl"),type:"POST",context:this,data:a,dataType:"json",success:function(b){}})
},initNoteFormEditable:function(){this.notesSection=$(".notes-wrap:first",this.wrapper);$(".new-note-form .trigger.save",this.notesSection).click((function(){this.saveNote()
}).bind(this))},saveNote:function(){$(".new-note-form",this.notesSection).addClass("saving");var a=$(".new-note-form textarea",this.notesSection).val();
$.ajax({timeout:20000,type:"POST",url:BASE_URL+"agent/organizations/"+this.meta.org_id+"/ajax-save-note",data:{note:a},success:this.handleNoteSave.bind(this)})
},handleNoteSave:function(b){$(".new-note-form textarea",this.notesSection).val("");var a=$(".note-list",this.notesSection);
a.append(b.note_li_html);$(".new-note-form",this.notesSection).removeClass("saving");this.updateCounts()}});Orb.createNamespace("DeskPRO.Agent.PageFragment.Page");
DeskPRO.Agent.PageFragment.Page.Person=new Class({Extends:DeskPRO.Agent.PageFragment.Basic,TYPENAME:"person",wrapper:null,initPage:function(f){this.wrapper=f;
this.contentWrapper=$("div.layout-content:first",f);this.zIndex=999999;var b=this;var a=this.contentWrapper;a.tinyscrollbar();
$("div.scroll-content:first, div.scroll-viewport:first",this.contentWrapper).resize(function(){a.tinyscrollbar_update()});
this.contactEditor=new DeskPRO.Agent.PageFragment.Page.PersonHelper.ContactEditor(this,{saveUrl:BASE_URL+"agent/people/"+this.meta.person_id+"/save-contact-data.json"});
this.initNoteFormEditable();this.initTimesOnCollection($("time.timeago",this.wrapper));var g=new DeskPRO.UI.Menu({menuElement:this.getEl("timezone")});
var d=new DeskPRO.UI.Menu({menuElement:this.getEl("is_autoresponder")});this.getEl("timezone").change(function(){var h=$(this).val();
$(".timezone-info",this.wrapper).empty();$.ajax({url:BASE_URL+"agent/people/"+b.meta.person_id+"/ajax-save",type:"POST",dataType:"json",data:{action:"timezone",timezone:h},context:this,success:function(i){$(".timezone-info",this.wrapper).empty().html(i.bit_html)
}})});this.getEl("is_autoresponder").change(function(){var h=$(this).val();$.ajax({url:BASE_URL+"agent/people/"+b.meta.person_id+"/ajax-save",type:"POST",dataType:"json",data:{action:"is_autoresponder",is_autoresponder:h}})
});var c=$("h3.name.editable:first",f);if(!c.attr("id")){c.attr("id",Orb.getUniqueId())}var e=new DeskPRO.Form.InlineEdit({baseElement:this.wrapper,ajax:{url:BASE_URL+"agent/people/"+this.meta.person_id+"/ajax-save"}});
$(this.wrapper).click(function(h){e.handleDocumentClick(h)});$(".create-ticket",this.getEl("action_buttons")).click(function(){DeskPRO_Window.newTicketLoader.open(function(h){h.setUser(b.meta.person_id)
})});$(".contact-list-wrapper",this.wrapper).first().delegate(".set-primary","click",function(){var h=$(this).data("email-id");
$(".contact-list-wrapper .email.is-primary",b.wrapper).removeClass("is-primary");$(".contact-list-wrapper .email-"+h,b.wrapper).addClass("is-primary");
var i=$(this).val();$.ajax({url:BASE_URL+"agent/people/"+b.meta.person_id+"/ajax-save",type:"POST",dataType:"json",data:{action:"set-primary-email",email_id:h}})
});this.morectionsMenu=new DeskPRO.UI.Menu({triggerElement:$(".more",this.getEl("action_buttons")),menuElement:this.getEl("more_actions_menu"),onItemClicked:function(i){var h=$(i.itemEl).data("action");
if(h=="reset-password"){DeskPRO_Window.showPrompt('<div>Enter a new password:<br /><br /><label style="font-size: 11px;"><input type="checkbox" class="send_email" value="1" /> Send the user an email with their new password</label></div>',function(l,k){var j=[];
j.push({name:"password",value:l});j.push({name:"send_email",value:$(".send_email",k).is(":checked")});j.push({name:"action",value:"password"});
$.ajax({url:BASE_URL+"agent/people/"+b.meta.person_id+"/ajax-save",type:"POST",dataType:"json",data:j})})}}});this.changePic=new DeskPRO.Agent.PageFragment.Page.PersonHelper.ChangePic(this,{loadUrl:BASE_URL+"agent/people/"+this.meta.person_id+"/change-picture-overlay",saveUrl:BASE_URL+"agent/people/"+this.meta.person_id+"/ajax-save"});
this._initLabels();this._initCustomFieldsEditor()},_initCustomFieldsEditor:function(){var c,b;c=this.fieldsRenderedWrap=this.getEl("custom_fields_rendered");
b=this.fieldsEditWrap=this.getEl("custom_fields_editable");var a=(function(){if(c.is(":visible")){c.hide();b.show()}else{b.hide();
c.show()}}).bind(this);$(".show-edit-custom-fields",this.wrapper).click(function(){a()});$(".save-custom-fields",this.wrapper).click((function(){var d=$("input, select, textarea",b).serializeArray();
$.ajax({url:BASE_URL+"agent/person/"+this.meta.person_id+"/ajax-save-custom-fields",type:"POST",data:d,dataType:"html",success:function(e){c.empty().html(e);
a()}})}).bind(this))},labelsList:null,_initLabels:function(){this.labelsList=$(".people-tags ul",this.wrapper).tagit({availableTags:this.getMetaData("labelsAutocompleteUrl"),enableBackspace:false,fieldName:"labels",onchange:this.saveLabels.bind(this)})
},_saveLabelsTimeout:null,saveLabels:function(){if(this._saveLabelsTimeout){window.clearTimeout(this._saveLabelsTimeout)}this._saveLabelsTimeout=this._doSaveLabels.delay(2000,this)
},_doSaveLabels:function(){var a=$(":input",this.labelsList).serializeArray();$.ajax({url:this.getMetaData("labelsSaveUrl"),type:"POST",context:this,data:a,dataType:"json",success:function(b){}})
},initNoteFormEditable:function(){this.notesSection=$(".notes-wrap:first",this.wrapper);$(".new-note-form .trigger.save",this.notesSection).click((function(){this.saveNote()
}).bind(this))},saveNote:function(){$(".new-note-form",this.notesSection).addClass("saving");var a=$(".new-note-form textarea",this.notesSection).val();
$.ajax({timeout:20000,type:"POST",url:BASE_URL+"agent/people/"+this.meta.person_id+"/ajax-save-note",data:{note:a},success:this.handleNoteSave.bind(this)})
},handleNoteSave:function(b){$(".new-note-form textarea",this.notesSection).val("");var a=$(".note-list",this.notesSection);
a.append(b.note_li_html);$(".new-note-form",this.notesSection).removeClass("saving");this.updateCounts()}});Orb.createNamespace("DeskPRO.Agent.PageFragment.Page");
DeskPRO.Agent.PageFragment.Page.PersonPopout=new Class({Extends:DeskPRO.Agent.PageFragment.Page.Person,TYPENAME:"person",initPage:function(a){this.parent(a)
}});Orb.createNamespace("DeskPRO.Agent.PageFragment.Page");DeskPRO.Agent.PageFragment.Page.TwitterUser=new Class({Extends:DeskPRO.Agent.PageFragment.Basic,el:null,tabs:null,initPage:function(a){this.el=$(a);
this._initTimeago();this._initTabs()},_initTimeago:function(){this.initTimesOnCollection($(".timeago",this.el))},_initTabs:function(){this.tabs=new DeskPRO.UI.SimpleTabs({context:$(".full-container-tabbed",this.el),triggerElements:$(".full-container-tabbed-tabs li",this.el)})
}});Orb.createNamespace("DeskPRO.Agent.PageFragment.Page");DeskPRO.Agent.PageFragment.Page.KbViewArticle=new Class({Extends:DeskPRO.Agent.PageFragment.Basic,TYPENAME:"article",wrapper:null,article_id:null,initPage:function(d){var b=this;
this.wrapper=d;this.article_id=this.getMetaData("article_id");this._initBasic();this._initMenus();this._initLabels();this._initCommentForm();
this._initPostArea();this._initAutoUnpublishOptions();this._initAutoPublishOptions();var c=$(".kb-editor-edit",this.wrap);
c.click(this.showEditor.bind(this));if(this.meta.isValidating){this.validatingEdit=new DeskPRO.Agent.PageHelper.ValidatingEdit(this,{typename:"articles",contentId:this.meta.article_id})
}var a=this.wrapper;a.tinyscrollbar();$("div.scroll-content:first, div.scroll-viewport:first",this.wrapper).resize(function(){a.tinyscrollbar_update()
});$("time.timeago",this.wrapper).timeago();this.relatedContent=new DeskPRO.Agent.PageHelper.RelatedContent(this,{typename:"articles",content_id:this.meta.article_id,listEl:$("section.linked-content:first",this.wrapper),onContentLinked:function(f,e){$.ajax({url:BASE_URL+"agent/kb/article/"+b.meta.article_id+"/ajax-save",type:"POST",data:{content_type:f,content_id:e,action:"add-related"},context:this,dataType:"json"})
},onContentUnlinked:function(f,e){$.ajax({url:BASE_URL+"agent/kb/article/"+b.meta.article_id+"/ajax-save",type:"POST",data:{content_type:f,content_id:e,action:"add-related"},context:this,dataType:"json"})
}});this.miscContent=new DeskPRO.Agent.PageHelper.MiscContent(this,{});this.whoVotedOverlay=new DeskPRO.UI.Overlay({triggerElement:".who-voted-trigger",contentMethod:"ajax",contentAjax:{url:BASE_URL+"agent/publish/rating-who-voted/article/"+this.meta.article_id}})
},handleUnloadRevisions:function(a){if(!a){return}if($(".rev-"+a,this.getEl("revs")).length){return}this.getEl("revs").empty().removeClass("loaded")
},_initBasic:function(){var a=this;$(".edit-trigger",this.wrapper).click(function(){DeskPRO_Window.runPageRoute("kb_article_edit:"+BASE_URL+"agent/kb/article/"+a.article_id);
DeskPRO_Window.removePage(a)});$(".validate-trigger",this.wrapper).click(function(){DeskPRO_Window.runPageRoute("kb_article_edit:"+BASE_URL+"agent/kb/article/"+a.article_id+"?do_validate=1");
DeskPRO_Window.removePage(a)});var b=$("h3.title.editable:first",this.wrapper);if(!b.attr("id")){b.attr("id",Orb.getUniqueId())
}var c=new DeskPRO.Form.InlineEdit({baseElement:this.wrapper,ajax:{url:BASE_URL+"agent/kb/article/"+this.meta.article_id+"/ajax-save",success:function(f){a.handleUnloadRevisions(f.revision_id)
}}});this.bodyTabs=new DeskPRO.UI.SimpleTabs({triggerElements:$("li.tab-trigger",this.getEl("bodytabs")),context:this.getEl("bodytabs"),onTabSwitch:(function(f){if($(f.tabContent).is(".revisions")&&!$(f.tabContent).is(".loaded")){$.ajax({url:BASE_URL+"agent/kb/article/"+this.meta.article_id+"/view-revisions",type:"GET",dataType:"html",context:this,success:function(g){this.getEl("revs").html(g);
this.miscCont()}})}}).bind(this)});var e=this.getEl("action_buttons");$(".permalink",e).click(function(){var f=[];f.push("<div>");
f.push("The permalink to this download on the website is:<br />");f.push('<input type="text" style="width:450px;" />');f.push("</div>");
var g=$(f.join(""));$("input",g).val(a.meta.permalink);DeskPRO_Window.showAlert(g)});$(".view-user-interface",e).click(function(){window.open(a.meta.permalink)
});var d=$(".file-list",this.getEl("attachtab"));this.wrapper.fileupload({url:BASE_URL+"agent/misc/accept-upload?attach_to_object=article&object_id="+this.meta.article_id,dropZone:this.wrapper,autoUpload:true,uploadTemplate:$(".template-upload",a.getEl("attachtab")),downloadTemplate:$(".template-download",a.getEl("attachtab"))});
d.delegate(".delete","click",function(){var f=$(this).data("blob-id");$.ajax({url:BASE_URL+"agent/kb/article/"+a.meta.article_id+"/ajax-save",type:"POST",data:{action:"remove-blob",blob_id:f},context:a,dataType:"json"});
$(this).parent().fadeOut()})},_initMenus:function(){var a=this;var b=$(".the-status:first",this.wrapper);this.statusMenu=new DeskPRO.UI.Menu({triggerElement:b,menuElement:$(".status-menu:first",this.wrapper),onItemClicked:function(d){var c=$(d.itemEl).data("option-value");
$(".article-status",b).attr("title",c);$(".article-status span",b).attr("class","").addClass("ticket-"+c.replace(/\./,"_"));
a.getEl("auto_unpub").hide();a.getEl("auto_pub").hide();if(c=="published"){a.getEl("auto_unpub").show()}else{if(c=="hidden.unpublished"){a.getEl("auto_pub").show()
}}$.ajax({url:BASE_URL+"agent/kb/article/"+a.meta.article_id+"/ajax-save",type:"POST",data:{action:"status",status:c},context:a,dataType:"json"})
}});this.deleteHelper=new DeskPRO.Agent.PageFragment.Page.Content.DeleteControl(this,{ajaxSaveUrl:BASE_URL+"agent/kb/article/"+a.meta.article_id+"/ajax-save",statusMenu:this.statusMenu});
this.catMenu=new DeskPRO.UI.Menu({triggerElement:$("li.add",this.getEl("categories")),menuElement:$("#article_category_menu"),onItemClicked:function(h){var f=$(h.itemEl).data("category-id");
var j=$(h.itemEl).data("parent-id");var g=[];g.push($("#article_category_menu .cat-"+f).text().trim());if(j){g.push($("#article_category_menu .cat-"+j).text().trim())
}var i=g.reverse().join(" > ");var c=$("<li />");c.append('<span class="remove">remove</span>');var e=$("<span />");e.text(i);
c.append(e);c.append('<input type="hidden" name="category_ids[]" value="'+f+'" />');c.insertBefore($("li.add",a.getEl("categories")));
var d=$("li:not(.add)",a.getEl("categories"));if(d.length>1){$(".remove",d).show()}a.sendUpdateCats()}});this.getEl("categories").delegate(".remove","click",function(e){var c=$(this).parent();
c.remove();var d=$("li:not(.add)",a.getEl("categories"));if(d.length==1){$(".remove",d).hide()}a.sendUpdateCats()});this.prodMenu=new DeskPRO.UI.Menu({triggerElement:$("li.add",this.getEl("products")),menuElement:$("#products_menu"),onItemClicked:function(g){var e=$(g.itemEl).data("product-id");
var i=$(g.itemEl).data("parent-id");var f=[];f.push($("#products_menu .prod-"+e).text().trim());if(i){f.push($("#products_menu .prod-"+i).text().trim())
}var h=f.reverse().join(" > ");var c=$("<li />");c.append('<span class="remove">remove</span>');var d=$("<span />");d.text(h);
c.append(d);c.append('<input type="hidden" name="product_ids[]" value="'+e+'" />');c.insertBefore($("li.add",a.getEl("products")));
a.sendUpdateProds()}});this.getEl("products").delegate(".remove","click",function(d){var c=$(this).parent();c.remove();a.sendUpdateProds()
})},sendUpdateCats:function(){var a=$("input",this.getEl("categories")).serializeArray();a.push({name:"action",value:"categories"});
$.ajax({url:BASE_URL+"agent/kb/article/"+this.meta.article_id+"/ajax-save",type:"POST",data:a,context:this,dataType:"json"})
},sendUpdateProds:function(){var a=$("input",this.getEl("products")).serializeArray();a.push({name:"action",value:"products"});
$.ajax({url:BASE_URL+"agent/kb/article/"+this.meta.article_id+"/ajax-save",type:"POST",data:a,context:this,dataType:"json"})
},labelsList:null,_initLabels:function(){this.labelsList=$(".kb-tags ul",this.wrapper);this.labelsInput=new DeskPRO.UI.LabelsInput({type:"articles",list:this.labelsList,onChange:this.saveLabels.bind(this)});
this.stickyWords=new DeskPRO.Agent.PageFragment.Page.Content.StickyWords(this,{contentType:"articles",contentId:this.meta.article_id,element:$(".sticky-search-words ul",this.wrapper)})
},_saveLabelsTimeout:null,saveLabels:function(){if(this._saveLabelsTimeout){window.clearTimeout(this._saveLabelsTimeout)}this._saveLabelsTimeout=this._doSaveLabels.delay(2000,this)
},_doSaveLabels:function(){var a=$(":input",this.labelsList).serializeArray();$.ajax({url:this.getMetaData("labelsSaveUrl"),type:"POST",context:this,data:a,dataType:"json",success:function(b){this._handleSaveLabelsSuccess(b)
}})},_handleSaveLabelsSuccess:function(a){},_initAutoUnpublishOptions:function(){var c=this;var b=this.getEl("auto_unpub");
$(".auto-unpublish-set",b).click(function(){c.updateAutoUnPubOptions();$(".auto-unpublish",b).show();$(this).hide()});$(".remove-auto-unpublish",b).click(function(){c.removeAutoUnPubOptions();
$(".auto-unpublish-set",b).show();$(".auto-unpublish",b).hide()});var e=$(".auto-unpublish .end-action.opt",b);var a=new DeskPRO.UI.Menu({triggerElement:e,menuElement:$(".end-action-menu",b),onItemClicked:function(h){var i=$(h.itemEl).data("action");
var g=$(h.itemEl).text().trim();e.data("val",i);e.text(g);c.updateAutoUnPubOptions()}});var d=$(".auto-unpublish .end-date.opt",b);
var f=$(".auto-unpublish .end-date-input",b);f.datepicker({dateFormat:"M d, yy",onSelect:function(i,h){var g=f.datepicker("getDate").getTime()/1000;
d.data("val",g);d.text(i);c.updateAutoUnPubOptions()}});d.click(function(){$(".auto-unpublish .end-date-input",b).datepicker("show")
})},removeAutoUnPubOptions:function(){$.ajax({url:BASE_URL+"agent/kb/article/"+this.meta.article_id+"/ajax-save",type:"POST",data:{action:"remove-auto-unpub"},context:this,dataType:"json"})
},updateAutoUnPubOptions:function(){var b=this.getEl("auto_unpub");var a=$(".auto-unpublish .end-date.opt",b).data("val");
var c=$(".auto-unpublish .end-action.opt",b).data("val");if(!a||!c){return}var d=[];d.push({name:"action",value:"auto-unpub"});
d.push({name:"end_action",value:c});d.push({name:"end_timestamp",value:a});$.ajax({url:BASE_URL+"agent/kb/article/"+this.meta.article_id+"/ajax-save",type:"POST",data:d,context:this,dataType:"json"})
},_initAutoPublishOptions:function(){var b=this;var a=this.getEl("auto_pub");$(".auto-publish-set",a).click(function(){b.updateAutoPubOptions();
$(".auto-publish",a).show();$(this).hide()});$(".remove-auto-publish",a).click(function(){b.removeAutoPubOptions();$(".auto-publish-set",a).show();
$(".auto-publish",a).hide()});var c=$(".auto-publish .pub-date.opt",a);var d=$(".auto-publish .pub-date-input",a);d.datepicker({dateFormat:"M d, yy",onSelect:function(g,f){var e=d.datepicker("getDate").getTime()/1000;
c.data("val",e);c.text(g);b.updateAutoPubOptions()}});c.click(function(){$(".auto-publish .pub-date-input",a).datepicker("show")
})},removeAutoPubOptions:function(){$.ajax({url:BASE_URL+"agent/kb/article/"+this.meta.article_id+"/ajax-save",type:"POST",data:{action:"remove-auto-pub"},context:this,dataType:"json"})
},updateAutoPubOptions:function(){var a=this.getEl("auto_unpub");var c=$(".auto-unpublish .end-date.opt",a).data("val");if(!c){return
}var b=[];b.push({name:"action",value:"auto-pub"});b.push({name:"pub_timestamp",value:c});$.ajax({url:BASE_URL+"agent/kb/article/"+this.meta.article_id+"/ajax-save",type:"POST",data:b,context:this,dataType:"json"})
},_initPostArea:function(){this._hasInitEd=false;$(".editor-cancel-trigger",this.getEl("content_ed")).click((function(){this.hideEditor()
}).bind(this));var a=$("ul.attachment-list:first",this.wrapper);if(a.length){this.getEl("attachtab").empty().append(a);var b=$("li.is-image a",a);
b.colorbox({title:function(){var d=$(this).attr("href");return'<a href="'+d+'" target="_blank">Open In New Window</a>'},width:"50%",height:"50%",initialWidth:"200",initialHeight:"150",scalePhotos:true,photo:true,opacity:0.5,transition:"none"})
}if(this.editStateSaver){this.editStateSaver.destroy()}this.editStateSaver=new DeskPRO.Agent.PageHelper.StateSaver({stateId:"editarticle",listenOn:$(".article-editor-wrap:first",c)});
var c=this.wrapper;$(".editor-save-trigger",this.getEl("content_ed")).click((function(d){d.preventDefault();var e=[];e.push({name:"action",value:"content"});
e.push({name:"content",value:$(".article-editor-wrap textarea:first",c).val()});$("input.edit-content-attach:checked",c).each(function(){e.push({name:"attach[]",value:$(this).val()})
});$.ajax({url:BASE_URL+"agent/kb/article/"+this.meta.article_id+"/ajax-save",type:"POST",context:this,data:e,dataType:"json",success:function(f){this.getEl("content_ed").html(f.content_html);
this._initPostArea();this.handleUnloadRevisions(f.revision_id)}})}).bind(this));this.hideEditor()},showEditor:function(){var a=this;
$(".article-content-wrap",this.getEl("content_ed")).hide();var b=$(".article-editor-wrap",this.getEl("content_ed")).show();
$(".revert-default",b).click(function(){var c=$("textarea.edit-content-field-default").val();$("textarea.edit-content-field").val(c);
$(".revert-message-notice",b).remove()});if(!this._hasInitEd){this._hasInitEd=true;$(".edit-content-field",this.getEl("content_ed")).tinymce({script_url:ASSETS_BASE_URL+"/vendor/tiny_mce/tiny_mce.js",theme:"advanced",plugins:"fullscreen",fullscreen_new_window:true,theme_advanced_buttons1:"bold,italic,underline,|,justifyleft,justifycenter,justifyright,|,fontselect,fontsizeselect,formatselect",theme_advanced_buttons2:",bullist,numlist,|,outdent,indent,|,link,unlink,anchor,image,|,code,removeformat,fullscreen",theme_advanced_buttons3:"",theme_advanced_toolbar_location:"top",theme_advanced_toolbar_align:"left",theme_advanced_resizing:true,theme_advanced_statusbar_location:"bottom",setup:function(c){c.onKeyPress.add(function(){a.editStateSaver.triggerChange()
})}});this._hasInitEdBefore=true}},hideEditor:function(){$(".article-editor-wrap",this.getEl("content_ed")).hide();$(".article-content-wrap",this.getEl("content_ed")).show()
},_initMediaBrowser:function(){if(this.mediabrowser_has_init){return}this.mediabrowser_has_init=true;this.mediaBrowserEl=$(".media-browser",this.wrapper);
this.mediaBrowserOverlay=new DeskPRO.UI.Overlay({contentElement:this.mediaBrowserEl});this.mediaBrowser=new DeskPRO.Agent.MediaBrowser({wrapper:this.mediaBrowserEl,additionalDropZone:$(".kb-editor > textarea",this.wrapper)})
},showMediaBrowser:function(){this._initMediaBrowser();this.mediaBrowserOverlay.openOverlay()},_initCommentForm:function(){this.commentsController=new DeskPRO.Agent.PageHelper.Comments(this,{commentsWrapper:this.getEl("comments_wrap")});
this.newCommentWrapper=$(".new-note:first",this.wrapper);$("button",this.newCommentWrapper).click(this.saveNewComment.bind(this))
},saveNewComment:function(){var b=$(".loading-on",this.newCommentWrapper).show();var a=$(".loading-off",this.newCommentWrapper).hide();
var c=[];c.push({name:"content",value:$("textarea",this.newCommentWrapper).val()});$.ajax({url:BASE_URL+"agent/kb/article/"+this.getMetaData("article_id")+"/ajax-save-comment",type:"POST",context:this,data:c,dataType:"html",success:function(d){b.hide();
a.show();$("textarea",this.newCommentWrapper).val("");var e=$(d);this.newCommentWrapper.before(e)}})}});Orb.createNamespace("DeskPRO.Agent.PageFragment.Page");
DeskPRO.Agent.PageFragment.Page.AgentChatTranscript=new Class({Extends:DeskPRO.Agent.PageFragment.Basic});Orb.createNamespace("DeskPRO.Agent.PageFragment.Page");
DeskPRO.Agent.PageFragment.Page.UserChat=new Class({Extends:DeskPRO.Agent.PageFragment.Basic,initPage:function(c){this.destroyEls=[];
this.wrapper=c;this.contentWrapper=this.wrapper.children(".layout-content").attr("id",Orb.getUniqueId());this.barWrapper=this.wrapper.children(".layout-footer").attr("id",Orb.getUniqueId());
DeskPRO_Window.getMessageBroker().addMessageListener("chat.new-message-"+this.meta.conversation_id,this.handleNewMessage.bind(this));
DeskPRO_Window.getMessageBroker().addMessageListener("chat.chat-ended-"+this.meta.conversation_id,this.chatHasEnded.bind(this));
DeskPRO_Window.getMessageBroker().addMessageListener("chat_user_agent.chat-parts-updated-"+this.meta.conversation_id,this.handleUpdateParts.bind(this));
this._initLayout();var a=this;var b=$(".new-message",this.barWrapper);b.keypress(function(d){if(d.keyCode==13&&!d.metaKey){d.preventDefault();
var e=b.val().trim();b.val("");if(!e.length){return}a.sendMessage(e);a.addMessageRow(a.meta.youName,e)}});this._initMenus();
if(this.meta.viewPersonUrl){this._initPopout()}$(".bar-actions .attach",this.wrapper).click(function(d){d.preventDefault();
d.stopPropagation();a.showUploadOverlay()})},_initLayout:function(){var a=this.contentWrapper;a.tinyscrollbar();$("div.scroll-content:first, div.scroll-viewport:first",this.contentWrapper).resize(function(){a.tinyscrollbar_update()
});this.layout=new DeskPRO.Agent.Layout.FooterLayout(this.wrapper);var b=this;var c=new DeskPRO.UI.SimpleTabs({context:this.contentWrapper,triggerElements:$(".full-container-tabbed-tabs li",this.contentWrapper),onTabSwitch:function(d){}})
},handleUpdateParts:function(a){this.updateActiveAgentList(a.agent_id,a.participant_ids)},updateActiveAgentList:function(a,c){var d=DeskPRO_Window.getDisplayName("agent",agent_id)||"Unassigned";
$("span.agent_id.val",this.wrapper).html(d);var b=$(".convo_participants ul",this.wrapper);b.empty();if(!c.lenght){b.append('<li class="agent-0">None</li>')
}else{Array.each(c,function(f){var e=DeskPRO_Window.getDisplayName("agent",f);b.append('<li class="agent-'+f+'">'+e+"</li>")
})}},_initMenus:function(){var a=this;this.qrMenu=new DeskPRO.UI.Menu({triggerElement:$("li.macros:first",this.barWrapper),menuElement:$("ul.quick-replies:first",this.wrapper),onItemClicked:function(c){var d=$(c.itemEl).data("qr-id");
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
var a=$(".chat-status:first",this.wrapper);$(".open",a).hide();$(".ended",a).show();this.barWrapper.hide()},addPart:function(a){$.ajax({url:BASE_URL+"agent/chat/add-part/"+this.meta.conversation_id+"/"+a,context:this,contentType:"json"});
this.addMessageRow("*",(DeskPRO_Window.getDisplayName("agent",a))+" joined","sys")},reassignConvo:function(a){$.ajax({url:BASE_URL+"agent/chat/assign/"+this.meta.conversation_id+"/"+a,context:this,contentType:"json"});
this.addMessageRow("*","Chat assigned to "+(DeskPRO_Window.getDisplayName("agent",a)||"Unassigned"),"sys")},handleNewMessage:function(a){DeskPRO_Window.pageTabStrip.alertTab(this.meta.tabIdClass);
if(a.message_html){this.addMessageRow(a.author_name,a.message_html,a.author_type,true)}else{this.addMessageRow(a.author_name,a.message,a.author_type)
}var b=$.tmpl("user_chat_newmsg_sound");b.appendTo(this.wrapper);DeskPRO_Window.handleSoundElements(b)},addMessageRow:function(a,g,c,f){if(c=="sys"){a="* "
}else{a="&lt;"+a+"&gt; "}var d="";if(c=="user"){d=" person-overview"}var b=['<div class="message '+c+'">'];b.push('<span class="author'+d+'">'+a+"</span>");
b.push('<span class="message"></span>');b.push("</div>");var e=$(b.join(""));if(f){$(".message",e).html(g)}else{g=Orb.escapeHtml(g);
$(".message",e).html(g)}e.appendTo($(".chat-messages .messages-wrapper",this.wrapper));$(".scroll-viewport",this.wrapper).scrollTop(10000)
},sendMessage:function(a){$.ajax({url:BASE_URL+"agent/chat/send-message/"+this.meta.conversation_id,data:{content:a},context:this,contentType:"json"})
},showUploadOverlay:function(){this._initUploadOverlay();this.uploadOverlay.open()},_initUploadOverlay:function(){if(this.uploadOverlay){return
}var a=this;var d;var b=this.getEl("upfile_overlay");this.uploadOverlay=d=new DeskPRO.UI.Overlay({contentElement:b});this.addEvent("destroy",function(){d.destroy()
});var c=$(".file-list",b);b.fileupload({url:BASE_URL+"agent/misc/accept-upload",dropZone:b,autoUpload:true,uploadTemplate:$(".template-upload",b),downloadTemplate:$(".template-download",b)});
b.bind("fileuploadadd",function(){$("ul.file-list",b).empty()});$("button.send-trigger",b).click(function(){var e=$("input.send_blob_id",b).val();
console.log(e);if(!e){return}$.ajax({url:BASE_URL+"agent/chat/send-file-message/"+a.meta.conversation_id,data:{send_blob_id:e},context:a,contentType:"json",success:function(g){var f=g.chat_data;
this.addMessageRow(f.author_name,f.message_html,f.author_type,true)}});a.uploadOverlay.close();$("ul.file-list",b).empty()
})},personPopoutHtml:null,personPopoutWaiting:false,_initPopout:function(){var a=this;var c=this.wrapper;var b=this.getMetaData("viewPersonUrl");
$.ajax({dataType:"text",url:b,type:"GET",success:function(d){a.personPopoutHtml=d;if(a.personPopoutWaiting){a.personPopoutWaiting=false;
a._initPopoutPageFragment()}}});$(".person-overview",c).css({cursor:"pointer"}).click(function(d){a.isMouseOverPopout=true;
a.openPopOut(d)})},_initPopoutEls_done:false,_initPopoutEls:function(){if(this._initPopoutEls_done){return}this._initPopoutEls_done=true;
var b=this.contentWrapper;var a=this;this.popout=$(".person-popout:first",b);this.popout.click(function(c){c.stopPropagation()
});this.popout.detach().appendTo("body");this.destroyEls.push(this.popout);this.popoutOuter=$(".person-popout-outer:first",b);
this.popoutOuter.detach().appendTo("body");this.destroyEls.push(this.popoutOuter);this.popoutTabs=$(".person-popout-tabs:first",b);
this.popoutTabs.detach().appendTo("body");this.destroyEls.push(this.popoutTabs);var a=this;$(".close:first",this.popoutTabs).click(function(){a.closePopout()
});$(".move-to-tab:first",this.popoutTabs).click(function(c){c.stopPropagation();DeskPRO_Window.runPageRouteFromElement($(".person-overview",a.wrapper));
a.closePopout()})},openPopOut:function(d){this._initPopoutEls();if(this.popout.is(":visible")){return}var g=$(".person-overview:first",this.wrapper);
var f=g.offset();var c=this.wrapper.offset();var b=f.left-35;if(b>780){b=780}var a=true;if(b<400){a=false}if(a){this.popout.css({position:"absolute",display:"block","z-index":999998,width:b,overflow:"auto"});
this.popout.css({top:(c.top-8),left:(f.left-this.popout.outerWidth()-20),bottom:30});var e=this.popout.offset();this.popoutOuter.css({position:"absolute",display:"block","z-index":999997,width:b+2+6,overflow:"auto",top:e.top-1,left:e.left-1,bottom:29});
this.popoutTabs.css({"z-index":999996,display:"block",top:(c.top-30),left:(f.left-260)})}if(!this.hasInitPopout&&a){if(this.personPopoutHtml){this._initPopoutPageFragment()
}else{this.personPopoutWaiting=true}}},closePopout:function(){this.popout.hide();this.popoutOuter.hide();this.popoutTabs.hide()
},_initPopoutPageFragment:function(){this.popoutPage=DeskPRO_Window.createPageFragment(this.personPopoutHtml);this.popout.html(this.personPopoutHtml);
this.personPopoutHtml=null;this.popoutPage.initPage(this.popout);this.hasInitPopout=true}});Orb.createNamespace("DeskPRO.Agent.PageFragment.Page");
DeskPRO.Agent.PageFragment.Page.IdeaView=new Class({Extends:DeskPRO.Agent.PageFragment.Basic,TYPENAME:"idea",wrapper:null,idea_id:null,initPage:function(d){var b=this;
this.wrapper=d;this.initFeaturesOnCollection(this.wrapper);this.idea_id=this.getMetaData("idea_id");this._initBasic();this._initMenus();
this._initActions();this._initLabels();this._initPostArea();this._initCommentForm();if(this.meta.isValidating){this.validatingEdit=new DeskPRO.Agent.PageHelper.ValidatingEdit(this,{typename:"ideas",contentId:this.idea_id,singleTyle:"idea"})
}var a=this.wrapper;a.tinyscrollbar();$("div.scroll-content:first, div.scroll-viewport:first",this.wrapper).resize(function(){a.tinyscrollbar_update()
});$("time.timeago",this.wrapper).timeago();var c=$(".idea-editor-edit",this.wrapper);c.click(this.showEditor.bind(this));
this.relatedContent=new DeskPRO.Agent.PageHelper.RelatedContent(this,{typename:"ideas",content_id:this.idea_id,listEl:$("section.linked-content:first",this.wrapper),onContentLinked:function(f,e){$.ajax({url:BASE_URL+"agent/ideas/view/"+b.idea_id+"/ajax-save",type:"POST",data:{content_type:f,content_id:e,action:"add-related"},context:this,dataType:"json"})
},onContentUnlinked:function(f,e){$.ajax({url:BASE_URL+"agent/ideas/view/"+b.idea_id+"/ajax-save",type:"POST",data:{content_type:f,content_id:e,action:"add-related"},context:this,dataType:"json"})
}});DeskPRO_Window.getMessageBroker().addMessageListener("publish.validating.list-remove",function(e){$("article."+e.typename+"-"+e.contentId).slideUp()
});this.miscContent=new DeskPRO.Agent.PageHelper.MiscContent(this,{revisionCompareUrl:BASE_URL+"agent/ideas/compare-revs/{OLD}/{NEW}"});
this.whoVotedOverlay=new DeskPRO.UI.Overlay({triggerElement:".who-voted-trigger",contentMethod:"ajax",contentAjax:{url:BASE_URL+"agent/publish/rating-who-voted/idea/"+this.idea_id}});
this.getEl("my_vote").click(function(){b.toggleMyVote()})},handleUnloadRevisions:function(a){if(!a){return}if($(".rev-"+a,this.getEl("revs")).length){return
}this.getEl("revs").empty().removeClass("loaded")},_initBasic:function(){var a=this;this.bodyTabs=new DeskPRO.UI.SimpleTabs({triggerElements:$("li.tab-trigger",this.getEl("bodytabs")),context:this.getEl("bodytabs"),onTabSwitch:(function(d){if($(d.tabContent).is(".idea-revs")&&!$(d.tabContent).is(".loaded")){$.ajax({url:BASE_URL+"agent/ideas/view/"+this.idea_id+"/view-revisions",type:"GET",dataType:"html",context:this,success:function(e){this.getEl("revs").html(e);
this.miscContent._initCompareRevs();$(d.tabContent).addClass("loaded")}})}}).bind(this)});var b=$("h3.title.editable:first",this.wrapper);
if(!b.attr("id")){b.attr("id",Orb.getUniqueId())}var c=new DeskPRO.Form.InlineEdit({baseElement:this.wrapper,ajax:{url:BASE_URL+"agent/ideas/view/"+this.idea_id+"/ajax-save",success:function(d){a.handleUnloadRevisions(d.revision_id)
}}})},toggleMyVote:function(){var a;if(this.getEl("my_vote").is(".radio-on")){a="clear-vote";DeskPRO_Window.util.modCountEl(this.getEl("num_votes"),"-");
this.getEl("my_vote").removeClass("radio-on")}else{a="vote";DeskPRO_Window.util.modCountEl(this.getEl("num_votes"),"+");this.getEl("my_vote").addClass("radio-on")
}$.ajax({url:BASE_URL+"agent/ideas/view/"+this.idea_id+"/ajax-save",type:"POST",data:{action:a},context:this,dataType:"json"})
},_initMenus:function(){var a=this;var b=$(".the-status:first",this.wrapper);this.statusMenu=new DeskPRO.UI.Menu({triggerElement:b,menuElement:$(".status-menu:first",this.wrapper),onItemClicked:function(e){var d=$(e.itemEl).data("option-value");
$(".idea-status",b).attr("title",d);$(".idea-status span",b).attr("class","").addClass("ticket-"+d.replace(/\./,"_"));$.ajax({url:BASE_URL+"agent/ideas/view/"+a.idea_id+"/ajax-save",type:"POST",data:{action:"status",status:d},context:a,dataType:"json"})
}});this.deleteHelper=new DeskPRO.Agent.PageFragment.Page.Content.DeleteControl(this,{ajaxSaveUrl:BASE_URL+"agent/ideas/view/"+a.idea_id+"/ajax-save",statusMenu:this.statusMenu});
this.deleteHelper=new DeskPRO.Agent.PageFragment.Page.Content.DeleteControl(this,{ajaxSaveUrl:BASE_URL+"agent/ideas/view/"+a.idea_id+"/ajax-save",statusMenu:this.statusMenu,type:"spam"});
var c=new DeskPRO.UI.Menu({menuElement:$("#idea_category_menu"),triggerElement:this.getEl("category"),onItemClicked:function(f){var d=$(f.itemEl).data("category-id");
var h=$(f.itemEl).data("parent-id");var e=$("#idea_category_menu .cat-"+d).text().trim();var g="";if(h){g=$("#idea_category_menu .cat-"+h).text().trim()
}if(h){$(".parent",a.getEl("category")).text(g);$(".sub",a.getEl("category")).text(e).show()}else{$(".parent",a.getEl("category")).text(e);
$(".sub",a.getEl("category")).text("").hide()}$.ajax({url:BASE_URL+"agent/ideas/view/"+a.idea_id+"/ajax-save",type:"POST",data:{action:"category",category_id:d},dataType:"json",success:function(){}})
}})},_initActions:function(){var a=this;var b=this.getEl("action_buttons");$(".delete",b).click(function(){});$(".permalink",b).click(function(){var c=[];
c.push("<div>");c.push("The permalink to this idea on the website is:<br />");c.push('<input type="text" style="width:450px;" />');
c.push("</div>");var d=$(c.join(""));$("input",d).val(a.meta.permalink);DeskPRO_Window.showAlert(d)});$(".view-user-interface",b).click(function(){window.open(a.meta.permalink)
});$(".merge",b).click((function(d){var c=new DeskPRO.Agent.Widget.MergeIdea({ideaId:this.getMetaData("idea_id"),destroyOnClose:true,onMergeSuccess:function(e){Array.each(DeskPRO_Window.getTabWatcher().findTabType("idea"),function(f){var g=f.page.getMetaData("idea_id");
if(g==e.old_idea_id||g==e.idea_id){DeskPRO_Window.pageTabStrip.removeTabById(f.id)}});DeskPRO_Window.runPageRoute("page:"+BASE_URL+"agent/ideas/view/"+e.idea_id);
c.close()}});c.open()}).bind(this))},labelsList:null,_initLabels:function(){this.labelsList=$(".idea-tags ul",this.wrapper);
this.labelsInput=new DeskPRO.UI.LabelsInput({type:"ideas",list:this.labelsList,onChange:this.saveLabels.bind(this)});this.stickyWords=new DeskPRO.Agent.PageFragment.Page.Content.StickyWords(this,{contentType:"ideas",contentId:this.idea_id,element:$(".sticky-search-words ul",this.wrapper)})
},_saveLabelsTimeout:null,saveLabels:function(){if(this._saveLabelsTimeout){window.clearTimeout(this._saveLabelsTimeout)}this._saveLabelsTimeout=this._doSaveLabels.delay(2000,this)
},_doSaveLabels:function(){var a=$(":input",this.labelsList).serializeArray();$.ajax({url:this.getMetaData("labelsSaveUrl"),type:"POST",context:this,data:a,dataType:"json",success:function(b){this._handleSaveLabelsSuccess(b)
}})},_handleSaveLabelsSuccess:function(a){},_initCommentForm:function(){this.commentsController=new DeskPRO.Agent.PageHelper.Comments(this,{commentsWrapper:this.getEl("comments_wrap")});
this.newCommentWrapper=$(".new-note:first",this.wrapper);$("button",this.newCommentWrapper).click(this.saveNewComment.bind(this))
},saveNewComment:function(){var b=$(".loading-on",this.newCommentWrapper).show();var a=$(".loading-off",this.newCommentWrapper).hide();
var c=[];c.push({name:"content",value:$("textarea",this.newCommentWrapper).val()});if(this.getEl("agent_comment_ck").is(":checked")){c.push({name:"agent_only",value:1})
}$.ajax({url:BASE_URL+"agent/ideas/view/"+this.getMetaData("idea_id")+"/ajax-save-comment",type:"POST",context:this,data:c,dataType:"html",success:function(d){b.hide();
a.show();$("textarea",this.newCommentWrapper).val("");var e=$(d);this.newCommentWrapper.before(e);this.incCount("idea-comments")
}})},_initPostArea:function(){this._hasInitEd=false;$(".editor-cancel-trigger",this.getEl("content_ed")).click((function(){this.hideEditor()
}).bind(this));var a=this.wrapper;if(this.editStateSaver){this.editStateSaver.destroy()}this.editStateSaver=new DeskPRO.Agent.PageHelper.StateSaver({stateId:"editidea",listenOn:$(".idea-editor-wrap:first",a)});
$(".editor-save-trigger",this.getEl("content_ed")).click((function(b){b.preventDefault();var c={action:"content",content:$(".idea-editor-wrap textarea:first",a).val(),attach:$(".idea-editor-wrap .edit-content-attach:first",a).val()};
$.ajax({url:BASE_URL+"agent/ideas/view/"+this.idea_id+"/ajax-save",type:"POST",context:this,data:c,dataType:"json",success:function(d){this.getEl("content_ed").html(d.content_html);
this.handleUnloadRevisions(d.revision_id);this._initPostArea()}})}).bind(this));this.hideEditor()},showEditor:function(){var a=this;
var b=$(".idea-editor-wrap",this.getEl("content_ed")).show();$(".revert-default",b).click(function(){var c=$("textarea.edit-content-field-default").val();
$("textarea.edit-content-field").val(c);$(".revert-message-notice",b).remove()});$(".idea-content-wrap",this.getEl("content_ed")).hide();
$(".idea-editor-wrap",this.getEl("content_ed")).show();if(!this._hasInitEd){this._hasInitEd=true;$(".edit-content-field",this.getEl("content_ed")).tinymce({script_url:ASSETS_BASE_URL+"/vendor/tiny_mce/tiny_mce.js",theme:"advanced",plugins:"fullscreen",fullscreen_new_window:true,theme_advanced_buttons1:"bold,italic,underline,|,justifyleft,justifycenter,justifyright,|,fontselect,fontsizeselect,formatselect",theme_advanced_buttons2:",bullist,numlist,|,outdent,indent,|,link,unlink,anchor,image,|,code,removeformat,fullscreen",theme_advanced_buttons3:"",theme_advanced_toolbar_location:"top",theme_advanced_toolbar_align:"left",theme_advanced_resizing:true,theme_advanced_statusbar_location:"bottom",setup:function(c){c.onKeyPress.add(function(){a.editStateSaver.triggerChange()
})}})}},hideEditor:function(){$(".idea-editor-wrap",this.getEl("content_ed")).hide();$(".idea-content-wrap",this.getEl("content_ed")).show()
},_initMediaBrowser:function(){if(this.mediabrowser_has_init){return}this.mediabrowser_has_init=true;this.mediaBrowserEl=$(".media-browser",this.wrapper);
this.mediaBrowserOverlay=new DeskPRO.UI.Overlay({contentElement:this.mediaBrowserEl});this.mediaBrowser=new DeskPRO.Agent.MediaBrowser({wrapper:this.mediaBrowserEl,additionalDropZone:$(".kb-editor > textarea",this.wrapper)})
},showMediaBrowser:function(){this._initMediaBrowser();this.mediaBrowserOverlay.openOverlay()},_initCompareRevs:function(){$(".compare-trigger",this.wrapper).click(this.showCompareRev.bind(this))
},showCompareRev:function(){var a=$(".idea-revs input.old:checked",this.wrapper).val();var b=$(".idea-revs input.new:checked",this.wrapper).val();
if(!a||!b){return}var c=new DeskPRO.UI.Overlay({triggerElement:$("button.compare-trigger",this.wrapper),contentMethod:"ajax",contentAjax:{url:BASE_URL+"agent/ideas/compare-revs/"+a+"/"+b},destroyOnClose:true});
c.openOverlay()}});Orb.createNamespace("DeskPRO.Agent.PageFragment.Page");DeskPRO.Agent.PageFragment.Page.NewsView=new Class({Extends:DeskPRO.Agent.PageFragment.Basic,TYPENAME:"news",wrapper:null,article_id:null,initPage:function(d){var b=this;
this.wrapper=d;this.news_id=this.getMetaData("news_id");this._initBasic();this._initMenus();this._initActions();this._initLabels();
this._initPostArea();this._initCommentForm();if(this.meta.isValidating){this.validatingEdit=new DeskPRO.Agent.PageHelper.ValidatingEdit(this,{typename:"news",contentId:this.meta.news_id})
}var a=this.wrapper;a.tinyscrollbar();$("div.scroll-content:first, div.scroll-viewport:first",this.wrapper).resize(function(){a.tinyscrollbar_update()
});$("time.timeago",this.wrapper).timeago();var c=$(".news-editor-edit",this.wrapper);c.click(this.showEditor.bind(this));
this.relatedContent=new DeskPRO.Agent.PageHelper.RelatedContent(this,{typename:"news",content_id:this.meta.article_id,listEl:$("section.linked-content:first",this.wrapper),onContentLinked:function(f,e){$.ajax({url:BASE_URL+"agent/news/post/"+b.meta.news_id+"/ajax-save",type:"POST",data:{content_type:f,content_id:e,action:"add-related"},context:this,dataType:"json"})
},onContentUnlinked:function(f,e){$.ajax({url:BASE_URL+"agent/news/post/"+b.meta.news_id+"/ajax-save",type:"POST",data:{content_type:f,content_id:e,action:"add-related"},context:this,dataType:"json"})
}});this.miscContent=new DeskPRO.Agent.PageHelper.MiscContent(this,{revisionCompareUrl:BASE_URL+"agent/downloads/compare-revs/{OLD}/{NEW}"});
this.whoVotedOverlay=new DeskPRO.UI.Overlay({triggerElement:".who-voted-trigger",contentMethod:"ajax",contentAjax:{url:BASE_URL+"agent/publish/rating-who-voted/news/"+this.meta.news_id}})
},handleUnloadRevisions:function(a){if(!a){return}if($(".rev-"+a,this.getEl("revs")).length){return}this.getEl("revs").empty().removeClass("loaded")
},_initBasic:function(){var a=this;this.bodyTabs=new DeskPRO.UI.SimpleTabs({triggerElements:$("li.tab-trigger",this.getEl("bodytabs")),context:this.getEl("bodytabs"),onTabSwitch:(function(d){if($(d.tabContent).is(".news-revs")&&!$(d.tabContent).is(".loaded")){$.ajax({url:BASE_URL+"agent/news/post/"+this.meta.news_id+"/view-revisions",type:"GET",dataType:"html",context:this,success:function(e){this.getEl("revs").html(e);
this.miscContent._initCompareRevs();$(d.tabContent).addClass("loaded")}})}}).bind(this)});var b=$("h3.title.editable:first",this.wrapper);
if(!b.attr("id")){b.attr("id",Orb.getUniqueId())}var c=new DeskPRO.Form.InlineEdit({baseElement:this.wrapper,ajax:{url:BASE_URL+"agent/news/"+this.meta.news_id+"/ajax-save",success:function(d){a.handleUnloadRevisions(d.revision_id)
}}})},_initMenus:function(){var a=this;var b=$(".the-status:first",this.wrapper);this.statusMenu=new DeskPRO.UI.Menu({triggerElement:b,menuElement:$(".status-menu:first",this.wrapper),onItemClicked:function(e){var d=$(e.itemEl).data("option-value");
$(".news-status",b).attr("title",d);$(".news-status span",b).attr("class","").addClass("ticket-"+d.replace(/\./,"_"));$.ajax({url:BASE_URL+"agent/news/post/"+a.meta.news_id+"/ajax-save",type:"POST",data:{action:"status",status:d},context:a,dataType:"json"})
}});this.deleteHelper=new DeskPRO.Agent.PageFragment.Page.Content.DeleteControl(this,{ajaxSaveUrl:BASE_URL+"agent/news/post/"+a.meta.news_id+"/ajax-save",statusMenu:this.statusMenu});
var c=new DeskPRO.UI.Menu({menuElement:$("#news_category_menu"),triggerElement:this.getEl("category"),onItemClicked:function(f){var d=$(f.itemEl).data("category-id");
var h=$(f.itemEl).data("parent-id");var e=$("#news_category_menu .cat-"+d).text().trim();var g="";if(h){g=$("#news_category_menu .cat-"+h).text().trim()
}if(h){$(".parent",a.getEl("category")).text(g);$(".sub",a.getEl("category")).text(e).show()}else{$(".parent",a.getEl("category")).text(e);
$(".sub",a.getEl("category")).text("").hide()}$.ajax({url:BASE_URL+"agent/news/post/"+a.meta.news_id+"/ajax-save",type:"POST",data:{action:"category",category_id:d},dataType:"json",success:function(){}})
}})},_initActions:function(){var a=this;var b=this.getEl("action_buttons");$(".delete",b).click(function(){});$(".permalink",b).click(function(){var c=[];
c.push("<div>");c.push("The permalink to this post on the website is:<br />");c.push('<input type="text" style="width:450px;" />');
c.push("</div>");var d=$(c.join(""));$("input",d).val(a.meta.permalink);DeskPRO_Window.showAlert(d)});$(".view-user-interface",b).click(function(){window.open(a.meta.permalink)
})},labelsList:null,_initLabels:function(){this.labelsList=$(".news-tags ul",this.wrapper);this.labelsInput=new DeskPRO.UI.LabelsInput({type:"news",list:this.labelsList,onChange:this.saveLabels.bind(this)});
this.stickyWords=new DeskPRO.Agent.PageFragment.Page.Content.StickyWords(this,{contentType:"news",contentId:this.meta.news_id,element:$(".sticky-search-words ul",this.wrapper)})
},_saveLabelsTimeout:null,saveLabels:function(){if(this._saveLabelsTimeout){window.clearTimeout(this._saveLabelsTimeout)}this._saveLabelsTimeout=this._doSaveLabels.delay(2000,this)
},_doSaveLabels:function(){var a=$(":input",this.labelsList).serializeArray();$.ajax({url:this.getMetaData("labelsSaveUrl"),type:"POST",context:this,data:a,dataType:"json",success:function(b){this._handleSaveLabelsSuccess(b)
}})},_handleSaveLabelsSuccess:function(a){},_initCommentForm:function(){this.commentsController=new DeskPRO.Agent.PageHelper.Comments(this,{commentsWrapper:this.getEl("comments_wrap")});
this.newCommentWrapper=$(".new-note:first",this.wrapper);$("button",this.newCommentWrapper).click(this.saveNewComment.bind(this))
},saveNewComment:function(){var b=$(".loading-on",this.newCommentWrapper).show();var a=$(".loading-off",this.newCommentWrapper).hide();
var c=[];c.push({name:"content",value:$("textarea",this.newCommentWrapper).val()});$.ajax({url:BASE_URL+"agent/news/post/"+this.getMetaData("news_id")+"/ajax-save-comment",type:"POST",context:this,data:c,dataType:"html",success:function(d){b.hide();
a.show();$("textarea",this.newCommentWrapper).val("");var e=$(d);this.newCommentWrapper.before(e);this.incCount("news-comments")
}})},_initPostArea:function(){this._hasInitEd=false;$(".editor-cancel-trigger",this.getEl("content_ed")).click((function(){this.hideEditor()
}).bind(this));var a=this.wrapper;if(this.editStateSaver){this.editStateSaver.destroy()}this.editStateSaver=new DeskPRO.Agent.PageHelper.StateSaver({stateId:"editarticle",listenOn:$(".news-editor-wrap:first",a)});
$(".editor-save-trigger",this.getEl("content_ed")).click((function(b){b.preventDefault();var c={action:"content",content:$(".news-editor-wrap textarea:first",a).val(),attach:$(".news-editor-wrap .edit-content-attach:first",a).val()};
$.ajax({url:BASE_URL+"agent/news/post/"+this.meta.news_id+"/ajax-save",type:"POST",context:this,data:c,dataType:"json",success:function(d){this.getEl("content_ed").html(d.content_html);
this.handleUnloadRevisions(d.revision_id);this._initPostArea()}})}).bind(this));this.hideEditor()},showEditor:function(){var a=this;
var b=$(".news-editor-wrap",this.getEl("content_ed")).show();$(".revert-default",b).click(function(){var c=$("textarea.edit-content-field-default").val();
$("textarea.edit-content-field").val(c);$(".revert-message-notice",b).remove()});$(".news-content-wrap",this.getEl("content_ed")).hide();
$(".news-editor-wrap",this.getEl("content_ed")).show();if(!this._hasInitEd){this._hasInitEd=true;$(".edit-content-field",this.getEl("content_ed")).tinymce({script_url:ASSETS_BASE_URL+"/vendor/tiny_mce/tiny_mce.js",theme:"advanced",plugins:"fullscreen",fullscreen_new_window:true,theme_advanced_buttons1:"bold,italic,underline,|,justifyleft,justifycenter,justifyright,|,fontselect,fontsizeselect,formatselect",theme_advanced_buttons2:",bullist,numlist,|,outdent,indent,|,link,unlink,anchor,image,|,code,removeformat,fullscreen",theme_advanced_buttons3:"",theme_advanced_toolbar_location:"top",theme_advanced_toolbar_align:"left",theme_advanced_resizing:true,theme_advanced_statusbar_location:"bottom",setup:function(c){c.onKeyPress.add(function(){a.editStateSaver.triggerChange()
})}})}},hideEditor:function(){$(".news-editor-wrap",this.getEl("content_ed")).hide();$(".news-content-wrap",this.getEl("content_ed")).show()
},_initMediaBrowser:function(){if(this.mediabrowser_has_init){return}this.mediabrowser_has_init=true;this.mediaBrowserEl=$(".media-browser",this.wrapper);
this.mediaBrowserOverlay=new DeskPRO.UI.Overlay({contentElement:this.mediaBrowserEl});this.mediaBrowser=new DeskPRO.Agent.MediaBrowser({wrapper:this.mediaBrowserEl,additionalDropZone:$(".kb-editor > textarea",this.wrapper)})
},showMediaBrowser:function(){this._initMediaBrowser();this.mediaBrowserOverlay.openOverlay()},_initCompareRevs:function(){$(".compare-trigger",this.wrapper).click(this.showCompareRev.bind(this))
},showCompareRev:function(){var a=$(".news-revs input.old:checked",this.wrapper).val();var b=$(".news-revs input.new:checked",this.wrapper).val();
if(!a||!b){return}var c=new DeskPRO.UI.Overlay({triggerElement:$("button.compare-trigger",this.wrapper),contentMethod:"ajax",contentAjax:{url:BASE_URL+"agent/news/compare-revs/"+a+"/"+b},destroyOnClose:true});
c.openOverlay()}});Orb.createNamespace("DeskPRO.Agent.PageFragment.Page");DeskPRO.Agent.PageFragment.Page.DownloadsView=new Class({Extends:DeskPRO.Agent.PageFragment.Basic,TYPENAME:"download",wrapper:null,article_id:null,initPage:function(d){var b=this;
this.wrapper=d;this.download_id=this.getMetaData("download_id");this._initBasic();this._initLabels();this._initCommentForm();
this._initPostArea();this._initActions();if(this.meta.isValidating){this.validatingEdit=new DeskPRO.Agent.PageHelper.ValidatingEdit(this,{typename:"downloads",contentId:this.meta.download_id})
}var c=$(".download-editor-edit",this.wrap);c.click(this.showEditor.bind(this));var a=this.wrapper;a.tinyscrollbar();$("div.scroll-content:first, div.scroll-viewport:first",this.wrapper).resize(function(){a.tinyscrollbar_update()
});$("time.timeago",this.wrapper).timeago();this.relatedContent=new DeskPRO.Agent.PageHelper.RelatedContent(this,{typename:"downloads",content_id:this.meta.download_id,listEl:$("section.linked-content:first",this.wrapper),onContentLinked:function(f,e){$.ajax({url:BASE_URL+"agent/downloads/file/"+b.meta.download_id+"/ajax-save",type:"POST",data:{content_type:f,content_id:e,action:"add-related"},context:this,dataType:"json"})
},onContentUnlinked:function(f,e){$.ajax({url:BASE_URL+"agent/downloads/file/"+b.meta.download_id+"/ajax-save",type:"POST",data:{content_type:f,content_id:e,action:"add-related"},context:this,dataType:"json"})
}});this.miscContent=new DeskPRO.Agent.PageHelper.MiscContent(this,{revisionCompareUrl:BASE_URL+"agent/downloads/compare-revs/{OLD}/{NEW}"});
this.whoVotedOverlay=new DeskPRO.UI.Overlay({triggerElement:".who-voted-trigger",contentMethod:"ajax",contentAjax:{url:BASE_URL+"agent/publish/rating-who-voted/download/"+this.meta.download_id}})
},handleUnloadRevisions:function(a){if(!a){return}if($(".rev-"+a,this.getEl("revs")).length){return}this.getEl("revs").empty().removeClass("loaded")
},_initBasic:function(){var a=this;this.bodyTabs=new DeskPRO.UI.SimpleTabs({triggerElements:$("li.tab-trigger",this.getEl("bodytabs")),context:this.getEl("bodytabs"),onTabSwitch:(function(f){if($(f.tabContent).is(".dl-revs")&&!$(f.tabContent).is(".loaded")){$.ajax({url:BASE_URL+"agent/downloads/file/"+this.meta.download_id+"/view-revisions",type:"GET",dataType:"html",context:this,success:function(g){this.getEl("revs").html(g);
this.miscContent._initCompareRevs();$(f.tabContent).addClass("loaded")}})}}).bind(this)});var c=$("h3.title.editable:first",this.wrapper);
if(!c.attr("id")){c.attr("id",Orb.getUniqueId())}var d=new DeskPRO.Form.InlineEdit({baseElement:this.wrapper,ajax:{url:BASE_URL+"agent/downloads/file/"+this.meta.download_id+"/ajax-save",success:function(f){a.handleUnloadRevisions(f.revision_id)
}}});var e=new DeskPRO.UI.Menu({menuElement:$("#download_category_menu"),triggerElement:this.getEl("category"),onItemClicked:function(h){var f=$(h.itemEl).data("category-id");
var j=$(h.itemEl).data("parent-id");var g=$("#download_category_menu .cat-"+f).text().trim();var i="";if(j){i=$("#download_category_menu .cat-"+j).text().trim()
}if(j){$(".parent",a.getEl("category")).text(i);$(".sub",a.getEl("category")).text(g).show()}else{$(".parent",a.getEl("category")).text(g);
$(".sub",a.getEl("category")).text("").hide()}$.ajax({url:BASE_URL+"agent/downloads/file/"+a.meta.download_id+"/ajax-save",type:"POST",data:{action:"category",category_id:f},dataType:"json",success:function(){}})
}});var b=$(".the-status:first",this.wrapper);this.statusMenu=new DeskPRO.UI.Menu({triggerElement:b,menuElement:$(".status-menu:first",this.wrapper),onItemClicked:function(g){var f=$(g.itemEl).data("option-value");
$(".download-status",b).attr("title",f);$(".download-status span",b).attr("class","").addClass("ticket-"+f.replace(/\./,"_"));
$.ajax({url:BASE_URL+"agent/downloads/file/"+a.meta.download_id+"/ajax-save",type:"POST",data:{action:"status",status:f},context:a,dataType:"json"})
}});this.deleteHelper=new DeskPRO.Agent.PageFragment.Page.Content.DeleteControl(this,{ajaxSaveUrl:BASE_URL+"agent/downloads/file/"+a.meta.download_id+"/ajax-save",statusMenu:this.statusMenu})
},_initActions:function(){var a=this;var b=this.getEl("action_buttons");$(".delete",b).click(function(){});$(".permalink",b).click(function(){var c=[];
c.push("<div>");c.push("The permalink to this download on the website is:<br />");c.push('<input type="text" style="width:450px;" />');
c.push("</div>");var d=$(c.join(""));$("input",d).val(a.meta.permalink);DeskPRO_Window.showAlert(d)});$(".view-user-interface",b).click(function(){window.open(a.meta.permalink)
})},labelsList:null,_initLabels:function(){this.labelsList=$(".download-tags ul",this.wrapper);this.labelsInput=new DeskPRO.UI.LabelsInput({type:"downloads",list:this.labelsList,onChange:this.saveLabels.bind(this)});
this.stickyWords=new DeskPRO.Agent.PageFragment.Page.Content.StickyWords(this,{contentType:"downloads",contentId:this.meta.download_id,element:$(".sticky-search-words ul",this.wrapper)})
},_saveLabelsTimeout:null,saveLabels:function(){if(this._saveLabelsTimeout){window.clearTimeout(this._saveLabelsTimeout)}this._saveLabelsTimeout=this._doSaveLabels.delay(2000,this)
},_doSaveLabels:function(){var a=$(":input",this.labelsList).serializeArray();$.ajax({url:this.getMetaData("labelsSaveUrl"),type:"POST",context:this,data:a,dataType:"json",success:function(b){this._handleSaveLabelsSuccess(b)
}})},_handleSaveLabelsSuccess:function(a){},_initCommentForm:function(){this.commentsController=new DeskPRO.Agent.PageHelper.Comments(this,{commentsWrapper:this.getEl("comments_wrap")});
this.newCommentWrapper=$(".new-note:first",this.wrapper);$("button",this.newCommentWrapper).click(this.saveNewComment.bind(this))
},saveNewComment:function(){var b=$(".loading-on",this.newCommentWrapper).show();var a=$(".loading-off",this.newCommentWrapper).hide();
var c=[];c.push({name:"content",value:$("textarea",this.newCommentWrapper).val()});$.ajax({url:BASE_URL+"agent/downloads/file/"+this.getMetaData("download_id")+"/ajax-save-comment",type:"POST",context:this,data:c,dataType:"html",success:function(d){b.hide();
a.show();$("textarea",this.newCommentWrapper).val("");var e=$(d);this.newCommentWrapper.before(e)}})},_initPostArea:function(){this._hasInitEd=false;
$(".editor-cancel-trigger",this.getEl("content_ed")).click((function(){this.hideEditor()}).bind(this));var a=this.wrapper;
if(this.editStateSaver){this.editStateSaver.destroy()}this.editStateSaver=new DeskPRO.Agent.PageHelper.StateSaver({stateId:"editdownload",listenOn:$(".download-editor-wrap:first",a)});
$(".editor-save-trigger",this.getEl("content_ed")).click((function(b){b.preventDefault();var c={action:"content",content:$(".download-editor-wrap textarea:first",a).val(),attach:$(".download-editor-wrap .edit-content-attach:first",a).val()};
$.ajax({url:BASE_URL+"agent/downloads/file/"+this.meta.download_id+"/ajax-save",type:"POST",context:this,data:c,dataType:"json",success:function(d){this.getEl("content_ed").html(d.content_html);
this.handleUnloadRevisions(d.revision_id);this._initPostArea()}})}).bind(this));this.hideEditor()},showEditor:function(){var a=this;
var c=$(".download-editor-wrap",this.getEl("content_ed")).show();$(".revert-default",c).click(function(){var d=$("textarea.edit-content-field-default").val();
$("textarea.edit-content-field").val(d);$(".revert-message-notice",c).remove()});$(".download-content-wrap",this.getEl("content_ed")).hide();
$(".download-editor-wrap",this.getEl("content_ed")).show();if(!this._hasInitEd){this._hasInitEd=true;$(".edit-content-field",this.getEl("content_ed")).tinymce({script_url:ASSETS_BASE_URL+"/vendor/tiny_mce/tiny_mce.js",theme:"advanced",plugins:"fullscreen",fullscreen_new_window:true,theme_advanced_buttons1:"bold,italic,underline,|,justifyleft,justifycenter,justifyright,|,fontselect,fontsizeselect,formatselect",theme_advanced_buttons2:",bullist,numlist,|,outdent,indent,|,link,unlink,anchor,image,|,code,removeformat,fullscreen",theme_advanced_buttons3:"",theme_advanced_toolbar_location:"top",theme_advanced_toolbar_align:"left",theme_advanced_resizing:true,theme_advanced_statusbar_location:"bottom",setup:function(d){d.onKeyPress.add(function(){a.editStateSaver.triggerChange()
})}});var b=$(".file-list",this.getEl("content_ed"));if(this._hasInitEdBefore){this.wrapper.fileupload("destroy")}this.wrapper.fileupload({url:BASE_URL+"agent/misc/accept-upload",dropZone:this.wrapper,autoUpload:true,uploadTemplate:$(".template-upload",a.getEl("content_ed")),downloadTemplate:$(".template-download",a.getEl("content_ed"))});
this.wrapper.bind("fileuploadadd",function(){$("ul.file-list",a.getEl("content_ed")).empty()});this._hasInitEdBefore=true
}},hideEditor:function(){$(".download-editor-wrap",this.getEl("content_ed")).hide();$(".download-content-wrap",this.getEl("content_ed")).show()
}});Orb.createNamespace("DeskPRO.Agent.PageFragment.Page");DeskPRO.Agent.PageFragment.Page.Test=new Class({Extends:DeskPRO.Agent.PageFragment.Basic,TYPENAME:"test",initPage:function(a){var b=new DeskPRO.Agent.PageHelper.Popover({pageUrl:BASE_URL+"agent/people/20001"});
b.open();window.popover=b}});