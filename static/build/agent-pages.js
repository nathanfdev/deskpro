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
}})}});Orb.createNamespace("DeskPRO.Agent.PageHelper");DeskPRO.Agent.PageHelper.TicketMassActions=new Orb.Class({Implements:[Orb.Util.Options],initialize:function(a){console.log(this);
this.options={ticketsWrapper:null,selectionBar:null,changeManager:null};this.setOptions(a);this.ticketsWrapper=this.options.ticketsWrapper;
this.selectionBar=this.options.selectionBar;this.actionsWrap=$(".mass-actions:first",this.selectionBar);this.changeManager=this.options.changeManager;
this._applyButtonCallback=null;this.initOverlay()},initOverlay:function(){var a=this;var b=this.triggerBtn=$(".perform-actions-trigger",this.selectionBar);
this.actionsOverlay=new DeskPRO.UI.Overlay({contentElement:this.actionsWrap,triggerElement:b,onBeforeOverlayOpened:function(d){if(b.is(".disabled")){d.cancel=true;
return}var c=$("input.ticket-select:checked",a.ticketsWrapper).length;$(".check-count-overlay",a.actionsWrap).html(c)}});
var a=this;$(".radio-option",this.actionsWrap).click(function(){var c=$(this);if(c.is(".radio-on")){c.removeClass("radio-on")
}else{var d=c.data("radio-group");if(d){$("."+d+".radio-option",this.actionsWrap).removeClass("radio-on")}c.addClass("radio-on")
}});$(".reply-check",this.actionsWrap).click(function(){if($(this).is(":checked")){$(".reply-area",a.actionsWrap).slideDown()
}else{$(".reply-area",a.actionsWrap).slideUp()}})},loadMacroActions:function(){var b=parseInt($("select.apply-macro-select",this.actionsWrap).val());
if(!b){return}var a=$(".macro-selector .spinner",this.actionsWrap).show().empty();var c=new Spinner(a,{radii:[4,8],padding:0}).play();
$.ajax({cache:false,type:"POST",data:{macro_id:b},url:BASE_URL+"agent/ticket-search/ajax-get-macro-actions",context:this,dataType:"json",success:function(d){console.log(d);
var e=false;Object.each(d.macro_actions,function(j,f){var h=$(".actions-form .add-term",this.actionsWrap);var g=parseInt(h.data("add-count"));
var i="actions["+g+"]";h.data("add-count",g+1);if(j.type=="reply"){e=j.options.reply}else{this.actionsEditor.addNewRow($(".actions-terms",this.actionsWrap),i,{type:j.type,options:j.options})
}},this);if(e){$("textarea",this.actionsWrap).val(e)}},complete:function(){c.remove();a.empty()}})},getSelectedTicketIds:function(){var a=[];
$("input.ticket-select:checked",this.ticketsWrapper).each(function(){a.push(parseInt($(this).val()))});return a},createPropertyForTicket:function(e,f){var b=null;
var a=/^(.*?)\[(.*?)\]$/.exec(e);if(a!==null){e=a[1];b=a[2]}var c=this._getPropClass(e,b);if(!c){return false}var d=new c[0](this.page,f,c[1]);
return d},_applyButtonClicked:function(){if(this._applyButtonCallback){this._applyButtonCallback()}},_loadActions:function(e){console.debug("loading indicator TicketActionsBar._loadActions");
var b=$(".loading-off").hide();var a=$(".loading-on").show().empty();var d=new Spinner(a,{radii:[4,8],padding:0}).play();
var c=$(":input, select, textarea",$(".actions-terms",this.actionsWrap)).serializeArray();c.combine($(":input, select, textarea",$(".ticket-reply",this.actionsWrap)).serializeArray());
Array.each(e,function(f){c.push({name:"ticket_ids[]",value:f})});$.ajax({cache:false,type:"GET",data:c,url:BASE_URL+"agent/ticket-search/ajax-preview-actions",context:this,dataType:"json",success:function(f){this.actionsOverlay.closeOverlay();
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
}}});Orb.createNamespace("DeskPRO.Agent.PageHelper");DeskPRO.Agent.PageHelper.DisplayOptions=new Orb.Class({Implements:[Orb.Util.Events,Orb.Util.Options],initialize:function(b,a){this.page=b;
this.options={triggerElement:null,resultId:0,prefId:"",refreshUrl:""};this.setOptions(a);if(!this.options.triggerElement){this.options.triggerElement=$(".display-options-trigger:first",this.page.wrapper)
}$(this.options.triggerElement).click((function(){this.open()}).bind(this));this.page.addEvent("destroy",(function(){this.destroy()
}).bind(this))},_initOverlay:function(){if(this._hasInit){return}this._hasInit=true;var a=$(".display-options:first",this.page.wrapper);
var b=$("ul.sortable-list",a);this.overlay=new DeskPRO.UI.Overlay({contentElement:a,onContentSet:function(c){b.sortable({axis:"y"})
}});$(".save-trigger",a).click((function(){this.saveDisplayOptions()}).bind(this))},saveDisplayOptions:function(){$(".loading-off",this.overlay.elements.wrapper).hide();
$(".loading-on",this.overlay.elements.wrapper).show();var c=[];var a="prefs[agent.ui."+this.options.prefId+"-display-fields."+this.options.resultId+"][]";
$('input[type="checkbox"]:checked',this.overlay.elements.wrapper).each(function(){c.push({name:a,value:$(this).attr("name")})
});c.push({name:"prefs[agent.ui."+this.options.prefId+"-order-by."+this.options.resultId+"]",value:$('select[name="order_by"]',this.overlay.elements.wrapper).val()});
var b=this.options.refreshUrl;$.ajax({timeout:20000,type:"POST",url:BASE_URL+"agent/misc/ajax-save-prefs",data:c,context:this,success:function(){DeskPRO_Window.loadListPane(b)
}})},open:function(){this._initOverlay();this.overlay.open()},close:function(){if(this.overlay){this.overlay.close()}},destroy:function(){if(this.overlay){this.overlay.destroy()
}}});Orb.createNamespace("DeskPRO.Agent.PageHelper");DeskPRO.Agent.PageHelper.SelectionBar=new Orb.Class({Implements:[Orb.Util.Events,Orb.Util.Options],initialize:function(c,b){var a=this;
this.page=c;this.options={selectionBar:null,selectedCount:null,button:null};this.setOptions(b);if(!this.options.selectionBar){this.options.selectionBar=$(".selection-bar:first",this.page.wrapper)
}this.selectionBar=$(this.options.selectionBar);if(!this.options.selectedCount){this.options.selectedCount=$(".selected-count:first",this.selectionBar)
}this.selectedCount=$(this.options.selectedCount);if(!this.options.button){this.options.button=$("button.perform-actions-trigger:first",this.selectionBar)
}this.button=$(this.options.button);this.button.addClass("disabled");this.button.click(this.buttonClicked.bind(this));$(".selection-control",this.page.wrapper).click(function(){if($(this).is(":checked")){a.checkAll()
}else{a.checkNone()}});this.page.wrapper.delegate("input.item-select","click",function(){var d=$(this);a.handleCheckChange(d,d.is(":checked"))
})},buttonClicked:function(){if(this.button.is(".disabled")){return}this.fireEvent("buttonClick")},getCheckedValues:function(){var a=[];
$("input.item-select:checked",this.page.wrapper).each(function(){a.push($(this).val())});return a},getChecked:function(){return $("input.item-select:checked",this.page.wrapper)
},getCount:function(){return $("input.item-select:checked",this.page.wrapper).length},checkAll:function(){$("input.item-select",this.page.wrapper).attr("checked",true);
var a=this.getCount();this.selectedCount.text(a);if(a>0){this.button.removeClass("disabled")}},checkNone:function(){$("input.item-select:checked",this.page.wrapper).attr("checked",false);
var a=this.getCount();this.selectedCount.text(a);this.button.addClass("disabled")},handleCheckChange:function(b,a){var c=this.getCount();
this.selectedCount.text(c);if(c>0){this.button.removeClass("disabled")}else{this.button.addClass("disabled")}this.fireEvent("checkChange",[b,a,c])
}});Orb.createNamespace("DeskPRO.Agent.PageHelper");DeskPRO.Agent.PageHelper.Popover_Instances={};DeskPRO.Agent.PageHelper.Popover=new Orb.Class({Implements:[Orb.Util.Events,Orb.Util.Options],initialize:function(a){this.options={loadTimeout:0,pageUrl:"",pageCallback:null,tabRoute:false,destroyOnClose:false,overFrom:"#deskpro_content"};
this.id=Orb.uuid();DeskPRO.Agent.PageHelper.Popover_Instances[this.id]=this;this.setOptions(a);this.pageSource=null;this.page=null;
this.isWaiting=false;this.popover=null;this.popoverOuter=null;this.popoverTabs=null;if(this.options.loadTimeout){this.autoloadTimeout=window.setTimeout(this._loadPage.bind(this),this.options.loadTimeout)
}},_loadPage:function(){if(this.options.pageCallback){return this.options.pageCallback(this.setHtml.bind(this))}if(!this.options.pageUrl){return
}if(this._isLoading){return}this._isLoading=true;if(this.autoloadTimeout){window.clearTimeout(this.autoloadTimeout);this.autoloadTimeout=null
}$.ajax({dataType:"text",url:this.options.pageUrl,type:"GET",context:this,success:function(a){this._isLoading=false;this.setHtml(a)
}})},setHtml:function(a){this.pageSource=a;if(this.isWaiting){this.isWaiting=false;this._initFragment();this.open()}},_initPopover:function(){if(this._hasInit){return
}this._hasInit=true;var b=$($("#popover_tpl").get(0).innerHTML);this.popover=b.filter(".popover-inner");this.popoverOuter=b.filter(".popover-outer");
this.popoverTabs=b.filter(".popover-tabs");this.popover.detach().appendTo("body");this.popoverOuter.detach().appendTo("body");
this.popoverTabs.detach().appendTo("body");this.popoverOuter.click(function(f){f.stopPropagation()});var e=$(this.options.overFrom).offset();
var d=e.top;var c=e.left-30;this.popover.css({position:"absolute",display:"none","z-index":999998,overflow:"auto",top:d,left:10,width:c,bottom:30});
this.popoverOuter.css({position:"absolute",display:"none","z-index":999997,width:c+2+6,overflow:"auto",top:d-1,left:9,bottom:29});
if(this.options.tabRoute){var a=c-210}else{var a=c-100}this.popoverTabs.css({"z-index":999996,display:"none",top:d-24,left:a});
$(".close:first",this.popoverTabs).click((function(f){f.preventDefault();f.stopPropagation();this.isWaiting=false;var f={pop:this,cancel:false};
this.fireEvent("closeTabClick",f);if(f.cancel){return}this.close()}).bind(this));if(this.options.tabRoute){$(".move-to-tab:first",this.popoverTabs).click((function(f){f.preventDefault();
f.stopPropagation();this.isWaiting=false;DeskPRO_Window.runPageRoute(this.options.tabRoute);this.close()}).bind(this))}else{$(".move-to-tab:first",this.popoverTabs).remove()
}},_initFragment:function(){if(this.page){return}if(!this.pageSource){return}this.page=DeskPRO_Window.createPageFragment(this.pageSource);
this.popover.html(this.pageSource);this.page.initPage(this.popover);this.pageSource=null;this.fireEvent("pageInit",[this,this.page])
},isOpen:function(){if(this.popover&&this.popover.is(":visible")){return true}return false},open:function(){this._initPopover();
this._initFragment();if(!this.page&&!this.pageSource){this.isWaiting=true;this._loadPage()}if(this.isOpen()){return}Object.each(DeskPRO.Agent.PageHelper.Popover_Instances,function(a){if(a.isOpen()){a.close()
}});this.popover.show();this.popoverOuter.show();this.popoverTabs.show()},toggle:function(){if(this.isOpen()){this.close()
}else{this.open()}},close:function(){var a={pop:this,cancel:false};this.fireEvent("close",a);if(a.cancel){return}this.popover.hide();
this.popoverOuter.hide();this.popoverTabs.hide();if(this.options.destroyOnClose){this.destroy()}},destroy:function(){if(this.page){this.page.fireEvent("destroy")
}if(this.popover){this.popover.remove();this.popoverOuter.remove();this.popoverTabs.remove()}delete DeskPRO.Agent.PageHelper.Popover_Instances[this.id]
}});Orb.createNamespace("DeskPRO.Agent.PageHelper");DeskPRO.Agent.PageHelper.ValidatingEdit=new Orb.Class({Implements:[Orb.Util.Events,Orb.Util.Options],initialize:function(c,b){var a=this;
this.page=c;this.options={typename:"",contentId:0};this.setOptions(b);$("button.approve-trigger",this.page.wrapper).click(this.approveEdit.bind(this));
$("button.disapprove-trigger",this.page.wrapper).click(this.showDisapproveForm.bind(this));$("button.disapprove2-trigger",this.page.wrapper).click(this.disapproveEdit.bind(this));
$("button.skip-trigger",this.page.wrapper).click(this.skipValidateEdit.bind(this))},showDisapproveForm:function(){$(".validating-bar:first .options",this.page.wrapper).hide();
$(".validating-bar:first .disapprove-form",this.page.wrapper).show()},approveEdit:function(){$.ajax({url:BASE_URL+"agent/publish/content/approve/"+this.options.typename+"/"+this.options.contentId+".json",type:"POST",context:this,dataType:"json",success:function(a){if(a.next_url){DeskPRO_Window.runPageRoute("page:"+a.next_url)
}DeskPRO_Window.getMessageBroker().sendMessage("publish.validating.list-remove",{typename:this.options.typename,contentId:this.options.contentId});
DeskPRO_Window.removePage(this.page)}})},disapproveEdit:function(){var a=$(".validating-bar .disapprove-reason",this.page.wrapper).val().trim();
$.ajax({url:BASE_URL+"agent/publish/content/disapprove/"+this.options.typename+"/"+this.options.contentId+".json",type:"POST",context:this,data:{reason:a},dataType:"json",success:function(b){if(b.next_url){DeskPRO_Window.runPageRoute("page:"+b.next_url)
}DeskPRO_Window.getMessageBroker().sendMessage("publish.validating.list-remove",{typename:this.options.typename,contentId:this.options.contentId});
DeskPRO_Window.removePage(this.page)}})},skipValidateEdit:function(){$.ajax({url:BASE_URL+"agent/publish/content/get-next-validating/"+this.options.typename+"/"+this.options.contentId+".json",type:"POST",context:this,dataType:"json",success:function(a){if(a.next_url){DeskPRO_Window.runPageRoute("page:"+a.next_url)
}DeskPRO_Window.getMessageBroker().sendMessage("publish.validating.list-remove",{typename:this.options.typename,contentId:this.options.contentId});
DeskPRO_Window.removePage(this.page)}})}});Orb.createNamespace("DeskPRO.Agent.PageFragment.Page");DeskPRO.Agent.PageFragment.Page.SnippetViewer=new Class({Extends:DeskPRO.Agent.PageFragment.Basic,allowDupe:true,TYPENAME:"ticket_snippets",initPage:function(b){var a=this;
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
this.contentWrapper=this.wrapper.children(".layout-content").attr("id",Orb.getUniqueId());this.barWrapper=$(".bar-wrapper",this.wrapper);
this.valueForm=$("form.value-form:first",this.contentWrapper);this.valueForm.submit(function(i){i.preventDefault()});this.changeManager=new DeskPRO.Agent.Ticket.ChangeManager(this);
window.TICKET=this;if(!this.meta.isDeleted){this._initCustomFieldsEditor()}var f=this;this._initMessage($("div.messages-wrap"));
$("input.date-field",this.contentWrapper).datepicker({dateFormat:"M d, yy"});this.ticketDisplay=new DeskPRO.Agent.PageHelper.TicketDisplay(this,{wrapper:g});
this.initFeaturesOnCollection(this.wrapper,{routes:[],times:[".timeago"]});var d=this.contentWrapper;d.tinyscrollbar();$("div.scroll-content:first, div.scroll-viewport:first",this.contentWrapper).resize(function(){d.tinyscrollbar_update()
});this.initRoutesOnCollection($(".with-route",this.wrapper));if(!this.meta.isDeleted){this._initTicketActionsMenu();this._initMessageActionsMenu();
this._initFlagMenu();this._initLabels()}else{$("button.undelete-trigger",this.wrapper).click(this.doTicketUndelete.bind(this))
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
},appendToMessage:function(a){this.getEl("replybox_txt").insertAtCaret(a)},getPropertyManager:function(a,b){console.warn("Depreciated");
return this.changeManager.getPropertyManager(a,b)},custom_fields_display:null,custom_fields_edit:null,_initCustomFieldsEditor:function(){$(".ticket-custom-fields-edit-btn",this.wrapper).click((function(){this.showCustomFieldEditor()
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
}});Orb.createNamespace("DeskPRO.Agent.PageFragment.Page.Content");DeskPRO.Agent.PageFragment.Page.Content.DeleteControl=new Orb.Class({Implements:[Orb.Util.Events,Orb.Util.Options],initialize:function(c,b){var a=this;
this.options={ajaxSaveUrl:"",statusMenu:null};this.setOptions(b);this.page=c;this.deleteBtn=$(".delete",this.page.getEl("action_buttons"));
this.deletedNotice=$(".deleted-notice:first",this.page.wrapper);this.statusBtn=$(".the-status:first",this.page.wrapper);this.undeleteBtn=$(".undelete",this.deletedNotice);
this.undeleteBtn.click(function(d){d.customEvents=new Orb.Util.EventObj({onItemClicked:function(){a.handleUndelete()}});a.options.statusMenu.open(d)
});this.deleteBtn.click(function(){a.handleDeleted();$.ajax({url:a.options.ajaxSaveUrl,data:{action:"delete"},type:"GET",dataType:"json",error:function(){a.handleUndelete()
},success:function(d){}})})},undelete:function(){},handleDeleted:function(){this.deleteBtn.hide();this.statusBtn.hide();this.deletedNotice.show()
},handleUndelete:function(){this.deleteBtn.show();this.statusBtn.show();this.deletedNotice.hide()}});Orb.createNamespace("DeskPRO.Agent.PageFragment.Page");
DeskPRO.Agent.PageFragment.Page.NewArticle=new Class({Extends:DeskPRO.Agent.PageFragment.Basic,allowDupe:true,TYPENAME:"newarticle",initPage:function(c){this.wrapper=c;
this.contentWrapper=this.wrapper.children(".layout-content").attr("id",Orb.getUniqueId());this.parent(c);var a=this.contentWrapper;
a.tinyscrollbar();var b=this;$("div.scroll-content:first, div.scroll-viewport:first",this.contentWrapper).resize(function(){a.tinyscrollbar_update();
b.fireEvent("resized")});this.form=$("form",this.wrapper).submit(function(d){d.preventDefault()});$("button.submit-trigger",this.wrapper).click(this.submit.bind(this));
this._initCategorySection();this._initTitleSection();this._initContentSection();this._initOtherSection()},closeSelf:function(){var a={cancel:false};
this.fireEvent("closeSelf",a);if(!a.cancel){this.parent()}},submit:function(){var a=this.form.serializeArray();$.ajax({url:BASE_URL+"agent/kb/article/new/save",type:"POST",data:a,dataType:"json",context:this,success:function(c){var b=this.getEl("pending_article_id").val();
if(b){DeskPRO_Window.getMessageBroker().sendMessage("kb.pending_article_removed",{pending_article_id:b})}if(c.success){DeskPRO_Window.runPageRoute("page:"+BASE_URL+"agent/kb/article/"+c.article_id);
this.closeSelf()}else{alert("There was an error with the form")}}})},setTitle:function(a){this.getEl("title").val(a).change()
},setContent:function(a,b){if(!b){a=Orb.escapeHtml(a)}this.getEl("content").html(a)},setPendingArticleId:function(a){this.getEl("pending_article_id").val(a)
},_initCategorySection:function(){var a=this;this.getEl("cat").change(function(){if(parseInt($(this).val())){a.getEl("cat_section").addClass("done")
}else{a.getEl("cat_section").removeClass("done")}})},_initTitleSection:function(){var a=this;var b=function(){if($(this).val().trim()==""){a.getEl("title_section").removeClass("done")
}else{a.getEl("title_section").addClass("done")}};this.getEl("title").change(b).keypress(b).change(function(){var c=$(this).val().trim().toLowerCase();
c=c.replace(/[^a-z0-9\-_]/g,"-");c=c.replace(/-{2,}/g,"-");a.getEl("slug").val(c)})},_initContentSection:function(){var b=this;
var d=this.getEl("content").offset().top;var a=this.wrapper.offset().top+this.wrapper.height();var c=a-d-100;this.getEl("content").css({width:this.wrapper.width()-80,height:c});
this.getEl("content").tinymce({script_url:ASSETS_BASE_URL+"/vendor/tiny_mce/tiny_mce.js",theme:"advanced",plugins:"fullscreen",fullscreen_new_window:true,theme_advanced_buttons1:"bold,italic,underline,|,justifyleft,justifycenter,justifyright,|,fontselect,fontsizeselect,formatselect",theme_advanced_buttons2:",bullist,numlist,|,outdent,indent,|,link,unlink,anchor,image,|,code,removeformat,fullscreen",theme_advanced_buttons3:"",theme_advanced_toolbar_location:"top",theme_advanced_toolbar_align:"left",theme_advanced_resizing:true,theme_advanced_statusbar_location:"bottom",setup:function(e){e.onClick.add(function(){b.getEl("content_section").addClass("done")
})}})},_initOtherSection:function(){this.otherTabs=new DeskPRO.UI.SimpleTabs({triggerElements:$("li",this.getEl("other_props_tabs")),context:this.getEl("other_props_tabs_content"),autoSelectFirst:false,onTabClick:(function(d){var e=this.getEl("other_props_tabs_content");
var b=this.getEl("other_props_tabs_wrap");var c=d.tabEl;if(!$(".on",b).length||c.is(".on")){if(e.is(":visible")){e.slideUp();
b.removeClass("on")}else{window.setTimeout(function(){e.slideDown()},20);b.addClass("on")}}}).bind(this)});this.labelsInput=new DeskPRO.UI.LabelsInput({type:"articles",fieldName:"newarticle[labels][]",list:$(".tags-wrap ul",this.wrapper)});
this.getEl("slug").focus(function(){this.addClass("had-focus")});var a=$(".file-list",this.wrapper);$("input",a[0]).live("click",function(){var c=$(this);
var b=c.parent();if(c.is(":checked")){b.removeClass("unchecked")}else{b.addClass("unchecked")}});this.wrapper.fileupload({url:BASE_URL+"agent/misc/accept-upload",dropZone:this.wrapper,autoUpload:true,uploadTemplate:$(".template-upload",this.wrapper),downloadTemplate:$(".template-download",this.wrapper)})
}});Orb.createNamespace("DeskPRO.Agent.PageFragment.Page");DeskPRO.Agent.PageFragment.Page.NewDownload=new Class({Extends:DeskPRO.Agent.PageFragment.Basic,allowDupe:true,TYPENAME:"newdownload",initPage:function(c){this.wrapper=c;
this.contentWrapper=this.wrapper.children(".layout-content").attr("id",Orb.getUniqueId());this.parent(c);var a=this.contentWrapper;
a.tinyscrollbar();var b=this;$("div.scroll-content:first, div.scroll-viewport:first",this.contentWrapper).resize(function(){a.tinyscrollbar_update();
b.fireEvent("resized")});this.form=$("form",this.wrapper).submit(function(d){d.preventDefault()});$("button.submit-trigger",this.wrapper).click(this.submit.bind(this));
this._initCategorySection();this._initTitleSection();this._initFileSection();this._initContentSection();this._initOtherSection()
},closeSelf:function(){var a={cancel:false};this.fireEvent("closeSelf",a);if(!a.cancel){this.parent()}},submit:function(){var a=this.form.serializeArray();
$.ajax({url:BASE_URL+"agent/downloads/new/save",type:"POST",data:a,dataType:"json",context:this,success:function(b){if(b.success){DeskPRO_Window.runPageRoute("page:"+BASE_URL+"agent/downloads/file/"+b.download_id);
this.closeSelf()}else{alert("There was an error with the form")}}})},_initCategorySection:function(){var a=this;this.getEl("cat").change(function(){if(parseInt($(this).val())){a.getEl("cat_section").addClass("done")
}else{a.getEl("cat_section").removeClass("done")}})},_initTitleSection:function(){var a=this;var b=function(){if($(this).val().trim()==""){a.getEl("title_section").removeClass("done")
}else{a.getEl("title_section").addClass("done")}};this.getEl("title").change(b).keypress(b).change(function(){var c=$(this).val().trim().toLowerCase();
c=c.replace(/[^a-z0-9\-_]/g,"-");c=c.replace(/-{2,}/g,"-");a.getEl("slug").val(c)})},_initFileSection:function(){var a=this;
var b=$(".file-list",this.wrapper);$("input",b[0]).live("click",function(){var d=$(this);var c=d.parent();if(d.is(":checked")){c.removeClass("unchecked")
}else{c.addClass("unchecked")}});this.wrapper.fileupload({url:BASE_URL+"agent/misc/accept-upload",dropZone:this.wrapper,autoUpload:true,uploadTemplate:$(".template-upload",this.wrapper),downloadTemplate:$(".template-download",this.wrapper)});
this.wrapper.bind("fileuploaddone",function(){a.getEl("file_section").addClass("done")});this.wrapper.bind("fileuploadadd",function(){$("ul.file-list",a.wrapper).empty()
})},_initContentSection:function(){var b=this;var d=this.getEl("content").offset().top;var a=this.wrapper.offset().top+this.wrapper.height();
var c=a-d-100;this.getEl("content").css({width:this.wrapper.width()-80,height:c});this.getEl("content").tinymce({script_url:ASSETS_BASE_URL+"/vendor/tiny_mce/tiny_mce.js",theme:"advanced",plugins:"fullscreen",fullscreen_new_window:true,theme_advanced_buttons1:"bold,italic,underline,|,justifyleft,justifycenter,justifyright,|,fontselect,fontsizeselect,formatselect",theme_advanced_buttons2:",bullist,numlist,|,outdent,indent,|,link,unlink,anchor,image,|,code,removeformat,fullscreen",theme_advanced_buttons3:"",theme_advanced_toolbar_location:"top",theme_advanced_toolbar_align:"left",theme_advanced_resizing:true,theme_advanced_statusbar_location:"bottom",setup:function(e){e.onClick.add(function(){b.getEl("content_section").addClass("done")
})}})},_initOtherSection:function(){this.otherTabs=new DeskPRO.UI.SimpleTabs({triggerElements:$("li",this.getEl("other_props_tabs")),context:this.getEl("other_props_tabs_content"),autoSelectFirst:false,onTabClick:(function(c){var d=this.getEl("other_props_tabs_content");
var a=this.getEl("other_props_tabs_wrap");var b=c.tabEl;if(!$(".on",a).length||b.is(".on")){if(d.is(":visible")){d.slideUp();
a.removeClass("on")}else{window.setTimeout(function(){d.slideDown()},20);a.addClass("on")}}}).bind(this)});this.labelsInput=new DeskPRO.UI.LabelsInput({type:"downloads",fieldName:"newdownload[labels][]",list:$(".tags-wrap ul",this.wrapper)});
this.getEl("slug").focus(function(){this.addClass("had-focus")})}});Orb.createNamespace("DeskPRO.Agent.PageFragment.Page");
DeskPRO.Agent.PageFragment.Page.NewNews=new Class({Extends:DeskPRO.Agent.PageFragment.Basic,allowDupe:true,TYPENAME:"newnews",initPage:function(c){this.wrapper=c;
this.contentWrapper=this.wrapper.children(".layout-content").attr("id",Orb.getUniqueId());this.parent(c);var a=this.contentWrapper;
a.tinyscrollbar();var b=this;$("div.scroll-content:first, div.scroll-viewport:first",this.contentWrapper).resize(function(){a.tinyscrollbar_update();
b.fireEvent("resized")});this.form=$("form",this.wrapper).submit(function(d){d.preventDefault()});$("button.submit-trigger",this.wrapper).click(this.submit.bind(this));
this._initCategorySection();this._initTitleSection();this._initContentSection();this._initOtherSection()},closeSelf:function(){var a={cancel:false};
this.fireEvent("closeSelf",a);if(!a.cancel){this.parent()}},submit:function(){var a=this.form.serializeArray();$.ajax({url:BASE_URL+"agent/news/new/save",type:"POST",data:a,dataType:"json",context:this,success:function(b){if(b.success){DeskPRO_Window.runPageRoute("page:"+BASE_URL+"agent/news/"+b.news_id);
this.closeSelf()}else{alert("There was an error with the form")}}})},_initCategorySection:function(){var a=this;this.getEl("cat").change(function(){if(parseInt($(this).val())){a.getEl("cat_section").addClass("done")
}else{a.getEl("cat_section").removeClass("done")}})},_initTitleSection:function(){var a=this;var b=function(){if($(this).val().trim()==""){a.getEl("title_section").removeClass("done")
}else{a.getEl("title_section").addClass("done")}};this.getEl("title").change(b).keypress(b).change(function(){var c=$(this).val().trim().toLowerCase();
c=c.replace(/[^a-z0-9\-_]/g,"-");c=c.replace(/-{2,}/g,"-");a.getEl("slug").val(c)})},_initContentSection:function(){var b=this;
var d=this.getEl("content").offset().top;var a=this.wrapper.offset().top+this.wrapper.height();var c=a-d-100;this.getEl("content").css({width:this.wrapper.width()-80,height:c});
this.getEl("content").tinymce({script_url:ASSETS_BASE_URL+"/vendor/tiny_mce/tiny_mce.js",theme:"advanced",plugins:"fullscreen",fullscreen_new_window:true,theme_advanced_buttons1:"bold,italic,underline,|,justifyleft,justifycenter,justifyright,|,fontselect,fontsizeselect,formatselect",theme_advanced_buttons2:",bullist,numlist,|,outdent,indent,|,link,unlink,anchor,image,|,code,removeformat,fullscreen",theme_advanced_buttons3:"",theme_advanced_toolbar_location:"top",theme_advanced_toolbar_align:"left",theme_advanced_resizing:true,theme_advanced_statusbar_location:"bottom",setup:function(e){e.onClick.add(function(){b.getEl("content_section").addClass("done")
})}})},_initOtherSection:function(){this.otherTabs=new DeskPRO.UI.SimpleTabs({triggerElements:$("li",this.getEl("other_props_tabs")),context:this.getEl("other_props_tabs_content"),autoSelectFirst:false,onTabClick:(function(d){var e=this.getEl("other_props_tabs_content");
var b=this.getEl("other_props_tabs_wrap");var c=d.tabEl;if(!$(".on",b).length||c.is(".on")){if(e.is(":visible")){e.slideUp();
b.removeClass("on")}else{window.setTimeout(function(){e.slideDown()},20);b.addClass("on")}}}).bind(this)});this.labelsInput=new DeskPRO.UI.LabelsInput({type:"news",fieldName:"newnews[labels][]",list:$(".tags-wrap ul",this.wrapper)});
this.getEl("slug").focus(function(){this.addClass("had-focus")});var a=$(".file-list",this.wrapper);$("input",a[0]).live("click",function(){var c=$(this);
var b=c.parent();if(c.is(":checked")){b.removeClass("unchecked")}else{b.addClass("unchecked")}});this.wrapper.fileupload({url:BASE_URL+"agent/misc/accept-upload",dropZone:this.wrapper,autoUpload:true,uploadTemplate:$(".template-upload",this.wrapper),downloadTemplate:$(".template-download",this.wrapper)})
}});Orb.createNamespace("DeskPRO.Agent.PageFragment.Page");DeskPRO.Agent.PageFragment.Page.NewTicket=new Class({Extends:DeskPRO.Agent.PageFragment.Basic,allowDupe:true,TYPENAME:"newticket",initPage:function(b){this.wrapper=b;
this.contentWrapper=this.wrapper.children(".layout-content").attr("id",Orb.getUniqueId());this.parent(b);var a=this.contentWrapper;
a.tinyscrollbar();$("div.scroll-content:first, div.scroll-viewport:first",this.contentWrapper).resize(function(){a.tinyscrollbar_update()
});this.form=$("form",this.wrapper).submit(function(c){c.preventDefault()});this._initUserSection();this._initDepartmentSection();
this._initSubjectSection();this._initMessageSection();this._initOtherSection();$("button.submit-trigger",this.wrapper).click(this.submit.bind(this))
},closeSelf:function(){var a={cancel:false};this.fireEvent("closeSelf",a);if(!a.cancel){this.parent()}},submit:function(){var a=this.form.serializeArray();
$.ajax({url:BASE_URL+"agent/tickets/new/save",type:"POST",data:a,dataType:"json",context:this,success:function(b){if(b.success){DeskPRO_Window.runPageRoute("ticket:"+BASE_URL+"agent/tickets/"+b.ticket_id);
this.closeSelf()}else{alert("There was an error with the form")}}})},_initUserSection:function(){var a=this;this.getEl("me_btn").click((function(c){c.preventDefault();
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
}});Orb.createNamespace("DeskPRO.Agent.PageFragment.Page");DeskPRO.Agent.PageFragment.Page.KbViewArticle=new Class({Extends:DeskPRO.Agent.PageFragment.Basic,TYPENAME:"kb_article_view",wrapper:null,article_id:null,initPage:function(c){this.wrapper=c;
this.article_id=this.getMetaData("article_id");this._initBasic();this._initMenus();this._initLabels();this._initCommentForm();
this._initPostArea();this._initCompareRevs();this._initAutoUnpublishOptions();this._initAutoPublishOptions();var b=$(".kb-editor-edit",this.wrap);
b.click(this.showEditor.bind(this));if(this.meta.isValidating){this.validatingEdit=new DeskPRO.Agent.PageHelper.ValidatingEdit(this,{typename:"articles",contentId:this.meta.article_id})
}var a=this.wrapper;a.tinyscrollbar();$("div.scroll-content:first, div.scroll-viewport:first",this.wrapper).resize(function(){a.tinyscrollbar_update()
});$("time.timeago",this.wrapper).timeago()},handleUnloadRevisions:function(a){if(!a){return}if($(".rev-"+a,this.getEl("revs")).length){return
}this.getEl("revs").empty().removeClass("loaded")},_initBasic:function(){var a=this;$(".edit-trigger",this.wrapper).click(function(){DeskPRO_Window.runPageRoute("kb_article_edit:"+BASE_URL+"agent/kb/article/"+a.article_id);
DeskPRO_Window.removePage(a)});$(".validate-trigger",this.wrapper).click(function(){DeskPRO_Window.runPageRoute("kb_article_edit:"+BASE_URL+"agent/kb/article/"+a.article_id+"?do_validate=1");
DeskPRO_Window.removePage(a)});var b=$("h3.title.editable:first",this.wrapper);if(!b.attr("id")){b.attr("id",Orb.getUniqueId())
}var c=new DeskPRO.Form.InlineEdit({baseElement:this.wrapper,ajax:{url:BASE_URL+"agent/kb/article/"+this.meta.article_id+"/ajax-save",success:function(e){a.handleUnloadRevisions(e.revision_id)
}}});this.bodyTabs=new DeskPRO.UI.SimpleTabs({triggerElements:$("li.tab-trigger",this.getEl("bodytabs")),context:this.getEl("bodytabs"),onTabSwitch:(function(e){if($(e.tabContent).is(".kb-revs")){$.ajax({url:BASE_URL+"agent/kb/article/"+this.meta.article_id+"/view-revisions",type:"GET",dataType:"html",context:this,success:function(f){this.getEl("revs").html(f);
this._initCompareRevs()}})}}).bind(this)});var d=this.getEl("action_buttons");$(".permalink",d).click(function(){var e=[];
e.push("<div>");e.push("The permalink to this download on the website is:<br />");e.push('<input type="text" style="width:450px;" />');
e.push("</div>");var f=$(e.join(""));$("input",f).val(a.meta.permalink);DeskPRO_Window.showAlert(f)});$(".view-user-interface",d).click(function(){window.open(a.meta.permalink)
})},_initMenus:function(){var a=this;var b=$(".the-status:first",this.wrapper);this.statusMenu=new DeskPRO.UI.Menu({triggerElement:b,menuElement:$(".status-menu:first",this.wrapper),onItemClicked:function(d){var c=$(d.itemEl).data("option-value");
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
},labelsList:null,_initLabels:function(){this.labelsList=$(".kb-tags ul",this.wrapper);this.labelsInput=new DeskPRO.UI.LabelsInput({type:"articles",list:this.labelsList,onChange:this.saveLabels.bind(this)})
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
}).bind(this));var a=this.wrapper;$(".editor-save-trigger",this.getEl("content_ed")).click((function(b){b.preventDefault();
var c={action:"content",content:$(".article-editor-wrap textarea:first",a).val()};$.ajax({url:BASE_URL+"agent/kb/article/"+this.meta.article_id+"/ajax-save",type:"POST",context:this,data:c,dataType:"json",success:function(d){this.getEl("content_ed").html(d.content_html);
this._initPostArea();this.handleUnloadRevisions(d.revision_id)}})}).bind(this));this.hideEditor()},showEditor:function(){var a=this;
$(".article-content-wrap",this.getEl("content_ed")).hide();$(".article-editor-wrap",this.getEl("content_ed")).show();if(!this._hasInitEd){this._hasInitEd=true;
$(".edit-content-field",this.getEl("content_ed")).tinymce({script_url:ASSETS_BASE_URL+"/vendor/tiny_mce/tiny_mce.js",theme:"advanced",plugins:"fullscreen",fullscreen_new_window:true,theme_advanced_buttons1:"bold,italic,underline,|,justifyleft,justifycenter,justifyright,|,fontselect,fontsizeselect,formatselect",theme_advanced_buttons2:",bullist,numlist,|,outdent,indent,|,link,unlink,anchor,image,|,code,removeformat,fullscreen",theme_advanced_buttons3:"",theme_advanced_toolbar_location:"top",theme_advanced_toolbar_align:"left",theme_advanced_resizing:true,theme_advanced_statusbar_location:"bottom"})
}},hideEditor:function(){$(".article-editor-wrap",this.getEl("content_ed")).hide();$(".article-content-wrap",this.getEl("content_ed")).show()
},_initMediaBrowser:function(){if(this.mediabrowser_has_init){return}this.mediabrowser_has_init=true;this.mediaBrowserEl=$(".media-browser",this.wrapper);
this.mediaBrowserOverlay=new DeskPRO.UI.Overlay({contentElement:this.mediaBrowserEl});this.mediaBrowser=new DeskPRO.Agent.MediaBrowser({wrapper:this.mediaBrowserEl,additionalDropZone:$(".kb-editor > textarea",this.wrapper)})
},showMediaBrowser:function(){this._initMediaBrowser();this.mediaBrowserOverlay.openOverlay()},_initCompareRevs:function(){$(".compare-trigger",this.wrapper).click(this.showCompareRev.bind(this))
},showCompareRev:function(){var a=$(".kb-revs input.old:checked",this.wrapper).val();var b=$(".kb-revs input.new:checked",this.wrapper).val();
if(!a||!b){return}var c=new DeskPRO.UI.Overlay({triggerElement:$("button.compare-trigger",this.wrapper),contentMethod:"ajax",contentAjax:{url:BASE_URL+"agent/kb/compare-revs/"+a+"/"+b},destroyOnClose:true});
c.openOverlay()},_initCommentForm:function(){this.newCommentWrapper=$(".new-note:first",this.wrapper);$("button",this.newCommentWrapper).click(this.saveNewComment.bind(this))
},saveNewComment:function(){var b=$(".loading-on",this.newCommentWrapper).show();var a=$(".loading-off",this.newCommentWrapper).hide();
var c=[];c.push({name:"content",value:$("textarea",this.newCommentWrapper).val()});$.ajax({url:BASE_URL+"agent/kb/article/"+this.getMetaData("article_id")+"/ajax-save-comment",type:"POST",context:this,data:c,dataType:"html",success:function(d){b.hide();
a.show();$("textarea",this.newCommentWrapper).val("");var e=$(d);this.newCommentWrapper.before(e)}})},});Orb.createNamespace("DeskPRO.Agent.PageFragment.Page");
DeskPRO.Agent.PageFragment.Page.AgentChatTranscript=new Class({Extends:DeskPRO.Agent.PageFragment.Basic});Orb.createNamespace("DeskPRO.Agent.PageFragment.Page");
DeskPRO.Agent.PageFragment.Page.UserChat=new Class({Extends:DeskPRO.Agent.PageFragment.Basic,initPage:function(c){this.destroyEls=[];
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
DeskPRO.Agent.PageFragment.Page.IdeaView=new Class({Extends:DeskPRO.Agent.PageFragment.Basic,TYPENAME:"ticket",popout:null,popout_overview:null,isMouseOverPopout:false,hasInitPopout:false,popoutPage:null,initPage:function(b){this.wrapper=$(b);
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
}})},});Orb.createNamespace("DeskPRO.Agent.PageFragment.Page");DeskPRO.Agent.PageFragment.Page.NewsView=new Class({Extends:DeskPRO.Agent.PageFragment.Basic,TYPENAME:"news_view",wrapper:null,article_id:null,initPage:function(c){this.wrapper=c;
this.news_id=this.getMetaData("news_id");this._initBasic();this._initMenus();this._initActions();this._initLabels();this._initPostArea();
this._initCommentForm();this._initCompareRevs();if(this.meta.isValidating){this.validatingEdit=new DeskPRO.Agent.PageHelper.ValidatingEdit(this,{typename:"news",contentId:this.meta.news_id})
}var a=this.wrapper;a.tinyscrollbar();$("div.scroll-content:first, div.scroll-viewport:first",this.wrapper).resize(function(){a.tinyscrollbar_update()
});$("time.timeago",this.wrapper).timeago();var b=$(".news-editor-edit",this.wrapper);b.click(this.showEditor.bind(this))
},handleUnloadRevisions:function(a){if(!a){return}if($(".rev-"+a,this.getEl("revs")).length){return}this.getEl("revs").empty().removeClass("loaded")
},_initBasic:function(){var a=this;this.bodyTabs=new DeskPRO.UI.SimpleTabs({triggerElements:$("li.tab-trigger",this.getEl("bodytabs")),context:this.getEl("bodytabs"),onTabSwitch:(function(d){if($(d.tabContent).is(".news-revs")){$.ajax({url:BASE_URL+"agent/news/post/"+this.meta.news_id+"/view-revisions",type:"GET",dataType:"html",context:this,success:function(e){this.getEl("revs").html(e);
this._initCompareRevs()}})}}).bind(this)});var b=$("h3.title.editable:first",this.wrapper);if(!b.attr("id")){b.attr("id",Orb.getUniqueId())
}var c=new DeskPRO.Form.InlineEdit({baseElement:this.wrapper,ajax:{url:BASE_URL+"agent/news/"+this.meta.news_id+"/ajax-save",success:function(d){a.handleUnloadRevisions(d.revision_id)
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
})},labelsList:null,_initLabels:function(){this.labelsList=$(".news-tags ul",this.wrapper);this.labelsInput=new DeskPRO.UI.LabelsInput({type:"news",list:this.labelsList,onChange:this.saveLabels.bind(this)})
},_saveLabelsTimeout:null,saveLabels:function(){if(this._saveLabelsTimeout){window.clearTimeout(this._saveLabelsTimeout)}this._saveLabelsTimeout=this._doSaveLabels.delay(2000,this)
},_doSaveLabels:function(){var a=$(":input",this.labelsList).serializeArray();$.ajax({url:this.getMetaData("labelsSaveUrl"),type:"POST",context:this,data:a,dataType:"json",success:function(b){this._handleSaveLabelsSuccess(b)
}})},_handleSaveLabelsSuccess:function(a){},_initCommentForm:function(){this.newCommentWrapper=$(".new-note:first",this.wrapper);
$("button",this.newCommentWrapper).click(this.saveNewComment.bind(this))},saveNewComment:function(){var b=$(".loading-on",this.newCommentWrapper).show();
var a=$(".loading-off",this.newCommentWrapper).hide();var c=[];c.push({name:"content",value:$("textarea",this.newCommentWrapper).val()});
$.ajax({url:BASE_URL+"agent/news/post/"+this.getMetaData("news_id")+"/ajax-save-comment",type:"POST",context:this,data:c,dataType:"html",success:function(d){b.hide();
a.show();$("textarea",this.newCommentWrapper).val("");var e=$(d);this.newCommentWrapper.before(e);this.incCount("news-comments")
}})},_initPostArea:function(){this._hasInitEd=false;$(".editor-cancel-trigger",this.getEl("content_ed")).click((function(){this.hideEditor()
}).bind(this));var a=this.wrapper;$(".editor-save-trigger",this.getEl("content_ed")).click((function(b){b.preventDefault();
var c={action:"content",content:$(".news-editor-wrap textarea:first",a).val(),attach:$(".news-editor-wrap .edit-content-attach:first",a).val()};
$.ajax({url:BASE_URL+"agent/news/post/"+this.meta.news_id+"/ajax-save",type:"POST",context:this,data:c,dataType:"json",success:function(d){this.getEl("content_ed").html(d.content_html);
this.handleUnloadRevisions(d.revision_id);this._initPostArea()}})}).bind(this));this.hideEditor()},showEditor:function(){var a=this;
$(".news-content-wrap",this.getEl("content_ed")).hide();$(".news-editor-wrap",this.getEl("content_ed")).show();if(!this._hasInitEd){this._hasInitEd=true;
$(".edit-content-field",this.getEl("content_ed")).tinymce({script_url:ASSETS_BASE_URL+"/vendor/tiny_mce/tiny_mce.js",theme:"advanced",plugins:"fullscreen",fullscreen_new_window:true,theme_advanced_buttons1:"bold,italic,underline,|,justifyleft,justifycenter,justifyright,|,fontselect,fontsizeselect,formatselect",theme_advanced_buttons2:",bullist,numlist,|,outdent,indent,|,link,unlink,anchor,image,|,code,removeformat,fullscreen",theme_advanced_buttons3:"",theme_advanced_toolbar_location:"top",theme_advanced_toolbar_align:"left",theme_advanced_resizing:true,theme_advanced_statusbar_location:"bottom"})
}},hideEditor:function(){$(".news-editor-wrap",this.getEl("content_ed")).hide();$(".news-content-wrap",this.getEl("content_ed")).show()
},_initMediaBrowser:function(){if(this.mediabrowser_has_init){return}this.mediabrowser_has_init=true;this.mediaBrowserEl=$(".media-browser",this.wrapper);
this.mediaBrowserOverlay=new DeskPRO.UI.Overlay({contentElement:this.mediaBrowserEl});this.mediaBrowser=new DeskPRO.Agent.MediaBrowser({wrapper:this.mediaBrowserEl,additionalDropZone:$(".kb-editor > textarea",this.wrapper)})
},showMediaBrowser:function(){this._initMediaBrowser();this.mediaBrowserOverlay.openOverlay()},_initCompareRevs:function(){$(".compare-trigger",this.wrapper).click(this.showCompareRev.bind(this))
},showCompareRev:function(){var a=$(".news-revs input.old:checked",this.wrapper).val();var b=$(".news-revs input.new:checked",this.wrapper).val();
if(!a||!b){return}var c=new DeskPRO.UI.Overlay({triggerElement:$("button.compare-trigger",this.wrapper),contentMethod:"ajax",contentAjax:{url:BASE_URL+"agent/news/compare-revs/"+a+"/"+b},destroyOnClose:true});
c.openOverlay()}});Orb.createNamespace("DeskPRO.Agent.PageFragment.Page");DeskPRO.Agent.PageFragment.Page.DownloadsView=new Class({Extends:DeskPRO.Agent.PageFragment.Basic,TYPENAME:"download_view",wrapper:null,article_id:null,initPage:function(c){this.wrapper=c;
this.download_id=this.getMetaData("download_id");this._initBasic();this._initLabels();this._initCommentForm();this._initPostArea();
this._initActions();this._initCompareRevs();if(this.meta.isValidating){this.validatingEdit=new DeskPRO.Agent.PageHelper.ValidatingEdit(this,{typename:"downloads",contentId:this.meta.download_id})
}var b=$(".download-editor-edit",this.wrap);b.click(this.showEditor.bind(this));var a=this.wrapper;a.tinyscrollbar();$("div.scroll-content:first, div.scroll-viewport:first",this.wrapper).resize(function(){a.tinyscrollbar_update()
});$("time.timeago",this.wrapper).timeago()},handleUnloadRevisions:function(a){if(!a){return}if($(".rev-"+a,this.getEl("revs")).length){return
}this.getEl("revs").empty().removeClass("loaded")},_initBasic:function(){var a=this;this.bodyTabs=new DeskPRO.UI.SimpleTabs({triggerElements:$("li.tab-trigger",this.getEl("bodytabs")),context:this.getEl("bodytabs"),onTabSwitch:(function(f){if($(f.tabContent).is(".dl-revs")){$.ajax({url:BASE_URL+"agent/downloads/file/"+this.meta.download_id+"/view-revisions",type:"GET",dataType:"html",context:this,success:function(g){this.getEl("revs").html(g);
this._initCompareRevs()}})}}).bind(this)});var c=$("h3.title.editable:first",this.wrapper);if(!c.attr("id")){c.attr("id",Orb.getUniqueId())
}var d=new DeskPRO.Form.InlineEdit({baseElement:this.wrapper,ajax:{url:BASE_URL+"agent/downloads/file/"+this.meta.download_id+"/ajax-save",success:function(f){a.handleUnloadRevisions(f.revision_id)
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
})},labelsList:null,_initLabels:function(){this.labelsList=$(".download-tags ul",this.wrapper);this.labelsInput=new DeskPRO.UI.LabelsInput({type:"downloads",list:this.labelsList,onChange:this.saveLabels.bind(this)})
},_saveLabelsTimeout:null,saveLabels:function(){if(this._saveLabelsTimeout){window.clearTimeout(this._saveLabelsTimeout)}this._saveLabelsTimeout=this._doSaveLabels.delay(2000,this)
},_doSaveLabels:function(){var a=$(":input",this.labelsList).serializeArray();$.ajax({url:this.getMetaData("labelsSaveUrl"),type:"POST",context:this,data:a,dataType:"json",success:function(b){this._handleSaveLabelsSuccess(b)
}})},_handleSaveLabelsSuccess:function(a){},_initCommentForm:function(){this.newCommentWrapper=$(".new-note:first",this.wrapper);
$("button",this.newCommentWrapper).click(this.saveNewComment.bind(this))},saveNewComment:function(){var b=$(".loading-on",this.newCommentWrapper).show();
var a=$(".loading-off",this.newCommentWrapper).hide();var c=[];c.push({name:"content",value:$("textarea",this.newCommentWrapper).val()});
$.ajax({url:BASE_URL+"agent/downloads/file/"+this.getMetaData("download_id")+"/ajax-save-comment",type:"POST",context:this,data:c,dataType:"html",success:function(d){b.hide();
a.show();$("textarea",this.newCommentWrapper).val("");var e=$(d);this.newCommentWrapper.before(e)}})},_initPostArea:function(){this._hasInitEd=false;
$(".editor-cancel-trigger",this.getEl("content_ed")).click((function(){this.hideEditor()}).bind(this));var a=this.wrapper;
$(".editor-save-trigger",this.getEl("content_ed")).click((function(b){b.preventDefault();var c={action:"content",content:$(".download-editor-wrap textarea:first",a).val(),attach:$(".download-editor-wrap .edit-content-attach:first",a).val()};
$.ajax({url:BASE_URL+"agent/downloads/file/"+this.meta.download_id+"/ajax-save",type:"POST",context:this,data:c,dataType:"json",success:function(d){this.getEl("content_ed").html(d.content_html);
this.handleUnloadRevisions(d.revision_id);this._initPostArea()}})}).bind(this));this.hideEditor()},showEditor:function(){var a=this;
$(".download-content-wrap",this.getEl("content_ed")).hide();$(".download-editor-wrap",this.getEl("content_ed")).show();if(!this._hasInitEd){this._hasInitEd=true;
$(".edit-content-field",this.getEl("content_ed")).tinymce({script_url:ASSETS_BASE_URL+"/vendor/tiny_mce/tiny_mce.js",theme:"advanced",plugins:"fullscreen",fullscreen_new_window:true,theme_advanced_buttons1:"bold,italic,underline,|,justifyleft,justifycenter,justifyright,|,fontselect,fontsizeselect,formatselect",theme_advanced_buttons2:",bullist,numlist,|,outdent,indent,|,link,unlink,anchor,image,|,code,removeformat,fullscreen",theme_advanced_buttons3:"",theme_advanced_toolbar_location:"top",theme_advanced_toolbar_align:"left",theme_advanced_resizing:true,theme_advanced_statusbar_location:"bottom"});
var b=$(".file-list",this.getEl("content_ed"));if(this._hasInitEdBefore){this.wrapper.fileupload("destroy")}this.wrapper.fileupload({url:BASE_URL+"agent/misc/accept-upload",dropZone:this.wrapper,autoUpload:true,uploadTemplate:$(".template-upload",a.getEl("content_ed")),downloadTemplate:$(".template-download",a.getEl("content_ed"))});
this.wrapper.bind("fileuploadadd",function(){$("ul.file-list",a.getEl("content_ed")).empty()});this._hasInitEdBefore=true
}},hideEditor:function(){$(".download-editor-wrap",this.getEl("content_ed")).hide();$(".download-content-wrap",this.getEl("content_ed")).show()
},_initCompareRevs:function(){$(".compare-trigger",this.wrapper).click(this.showCompareRev.bind(this))},showCompareRev:function(){var a=$(".dl-revs input.old:checked",this.wrapper).val();
var b=$(".dl-revs input.new:checked",this.wrapper).val();if(!a||!b){return}var c=new DeskPRO.UI.Overlay({triggerElement:$("button.compare-trigger",this.wrapper),contentMethod:"ajax",contentAjax:{url:BASE_URL+"agent/downloads/compare-revs/"+a+"/"+b},destroyOnClose:true});
c.openOverlay()}});Orb.createNamespace("DeskPRO.Agent.PageFragment.Page");DeskPRO.Agent.PageFragment.Page.Test=new Class({Extends:DeskPRO.Agent.PageFragment.Basic,TYPENAME:"test",initPage:function(a){var b=new DeskPRO.Agent.PageHelper.Popover({pageUrl:BASE_URL+"agent/people/20001"});
b.open();window.popover=b}});