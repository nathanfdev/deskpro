Orb.createNamespace("DeskPRO.Form");DeskPRO.Form.InlineEdit=new Class({Implements:Options,options:{baseElement:window.document,editableClass:"editable",ajax:{timeout:20000,type:"POST",url:""},saveFinishCallback:function(){}},activeEdits:[],sendingEdits:{},documentClickSubmitOn:false,initialize:function(b){this.setOptions(b);
var c="."+this.options.editableClass;var a=this;$(c,this.options.baseElement).each(function(){a.initEditable(this)});$(document).click(function(d){a.handleDocumentClick(d)
});$(document).keydown(function(d){if(d.keyCode==27){a.closeEditables()}})},initEditable:function(d){var b=this;var a=$(d);
if(a.is(".parent-trigger")){var c=a.parent();c.dblclick(function(){b.startEditable(a)})}else{a.dblclick(function(){b.startEditable(this)
})}},handleDocumentClick:function(a){if(!this.documentClickSubmitOn){return}if($(a.target).parents().is(".editable")){return
}this.submitOpen();this.documentClickSubmitOn=false},startEditable:function(c){c=$(c);var a=$("div.rendered-value",c);if(!a.size()){c.wrapInner('<div class="rendered-value" />');
a=$("div.rendered-value",c)}var b=$($(c).data("editable-for"),this.options.baseElement);var e=b.parent();a.fadeOut("fast",function(){a.detach();
b.addClass("editable-fields-on").hide().appendTo(c).fadeIn("fast")});var d={editable:c,rendered_els:a,form_elements:b,form_elements_container:e};
this.documentClickSubmitOn=true;this.activeEdits.push(d)},submitOpen:function(){if(!this.activeEdits.length){return}var f=$(".editable-fields-on :input, .editable-ajax-data :input",this.options.baseElement).serializeArray();
var d=this.activeEdits.length;var e=[];var g=null;while(g=this.activeEdits.pop()){this.setEditinfoLoading(g);e.push(g)}var a=Orb.uuid();
this.sendingEdits[a]=e;var c=this;var b=Object.merge({success:function(i,j,h){console.log("ajax-save data: %o",i);c.handleAjaxSuccess(a,i)
},error:function(h,j,i){console.log("ajax-save error: %s",j);c.handleAjaxFailure(a)},dataType:"json",data:f},this.options.ajax);
console.log("ajax-save: %s",b.url);console.log("ajax-save data: %o",b.data);$.ajax(b)},handleAjaxSuccess:function(b,d){var i=this.sendingEdits[b];
delete this.sendingEdits[b];var g=null;var c=null;while(c=i.pop()){var f=this._findDataFromEditinfo(c,d);var e=null;if(f){if(f.errors){continue
}else{if(f.html){e=f.html}}}if(!e){var h=$(":input",c.form_elements).serializeArray();var a=[];h.each(function(j){a.push(j.value)
});e=a.join(", ")}c.rendered_els.remove();c.rendered_els=$('<div class="rendered-value" />').html(e);this.closeEditinfo(c)
}this.options.saveFinishCallback(d)},_findDataFromEditinfo:function(d,c){var e=$(":input",d.form_elements).eq(0).attr("id");
var a=e.split("_");do{var b=a.join("_");if(c[b]!=undefined){return c[b]}}while(a.pop());return c},handleAjaxFailure:function(a){},setEditinfoLoading:function(b,a){},closeEditables:function(){var a=null;
while(a=this.activeEdits.pop()){this.closeEditinfo(a)}},closeEditinfo:function(d){var c=d.editable;var a=d.rendered_els;var b=d.form_elements;
var e=d.form_elements_container;b.fadeOut("fast",function(){b.removeClass("editable-fields-on").appendTo(e);if(a.parent().get(0)!=c.get(0)){a.hide().appendTo(c).fadeIn("fast")
}})}});Orb.createNamespace("DeskPRO.Form");DeskPRO.Form.RuleBuilder=new Class({Implements:Events,ruleTpl:null,typeSelectHtml:null,initialize:function(a){this.ruleTpl=a;
this.typeSelectHtml=['<select name="type" class="type">'];$("> .type",this.ruleTpl).each((function(b,c){this.typeSelectHtml.push('<option value="'+$(c).data("rule-type")+'">'+$(c).attr("title")+"</option>")
}).bind(this));this.typeSelectHtml.push("</select>");this.typeSelectHtml=this.typeSelectHtml.join("")},addNewRow:function(g,c,e){var b=$("> .row",this.ruleTpl).children().clone();
$(".type:first",b).html(this.typeSelectHtml);var a=$("select.type:first",b);if(c){b.data("form-base-name",c);this.updateFormName(b,c)
}var d=false;if(e){d=$('option[value="'+e.type+'"]:first',a);d.attr("selected",true);this.handleSelectChange(b);$(".op:first select",b).val(e.op).addClass("op");
if(typeof e.options=="string"||typeof e.options=="number"||typeOf(e.options)!="object"){$(":input, textarea, select",b).filter(":not(.op, .type)").first().val(e.options)
}else{Object.each(e.options,function(k,i){if(!i||!i.length){return}var h=i.replace(/\[/,"\\[").replace(/\]/,"\\]");if(typeof k=="string"||typeof k=="number"){var j=$('[name="'+h+'"], [name$="'+this.makeArrayName(i,true)+'"]',b).first().val(k)
}else{if(typeOf(k)=="object"){Object.each(k,function(o,p){var n=h+"["+p+"]";var l=h+"\\["+p+"\\]";var m=$('[name="'+l+'"], [name$="'+this.makeArrayName(n,true)+'"]',b).first().val(o)
},this)}else{if(typeOf(k)=="array"){Array.each(k,function(m){var l=$('option[value="'+m+'"]',b).first().get(0);l.selected=true
},this)}else{var j=$('[name="'+h+'"], [name$="'+this.makeArrayName(i,true)+'"]',b).first().val(k)}}}},this)}}var f=new DeskPRO.UI.Menu({menuElement:a});
a.change((function(){this.handleSelectChange(b)}).bind(this));$(g).append(b);if(d){d.attr("selected",true)}this.fireEvent("newRow",[b,g,e]);
return b},handleSelectChange:function(k){var f=$(".type:first > select",k).val();var a=$('> .type[data-rule-type="'+f+'"]',this.ruleTpl);
var d=$("> .op:first",a).children().clone();var b=$("> .options:first",a).clone();b.css("display","inline");$(".op:first",k).empty().append(d);
$(".options:first",k).empty().append(b);if(d.is("select")){var g=new DeskPRO.UI.Menu({menuElement:d})}var e=b.children().length;
if(e==1){var j=$("select",b);if(j.length){var i=new DeskPRO.UI.Menu({menuElement:j})}var h=$('input[type="text"], textarea',b);
if(h.length){var c=$('<span class="menu-trigger">(click to set value)</span>');c.appendTo(b);c.click(function(){var m=function(p){if(p.keyCode==13&&!p.metaKey){h.blur();
o()}};var o=function(){l.remove();h.detach().unbind("keypress",m).css("display","none").appendTo(b);n.remove()};var l=$('<div class="backdrop"></div>');
l.appendTo("body");l.click(o);var n=$('<div class="field-overlay"><div class="close-trigger"></div></div>');h.detach().css("display","block").appendTo(n);
n.css({left:c.offset().left,top:c.offset().top});n.appendTo("body").show();h.keypress(m).focus();$(".close-trigger",n).click(o)
});h.css("display","none");h.change(function(){var l=h.val().trim();if(!l){l="(click to set value)"}c.text(l)})}}if(k.data("form-base-name")){this.updateFormName($(".op:first",k),k.data("form-base-name"));
this.updateFormName($(".options:first",k),k.data("form-base-name"))}this.fireEvent("selectChange",[k,f])},updateFormName:function(b,a){$("[name]",b).each(function(){var c=$(this).attr("name");
c=c.replace(/^([\w\d]*)/,"[$1]");c=a+c;$(this).attr("name",c)})},makeArrayName:function(a,b){if(a.indexOf("[")===-1){a="["+a+"]"
}else{a=a.replace(/^([\w\d]+)\[(.*?)$/,"[$1][$2")}if(b){a=a.replace(/\[/g,"\\[").replace(/\]/g,"\\]")}return a}});Orb.createNamespace("DeskPRO");
DeskPRO.FaviconBadge=new Orb.Class({Implements:[Orb.Util.Options],initialize:function(a){this.supported=false;if(typeof HTMLCanvasElement!=undefined){this.supported=true
}this.faviconEl=$(a.favicon);this.badgeEl=null},updateBadge:function(c,d){if(!this.supported){return}var b=document.createElement("img");
var a=this;if(c>99){c=99}if(a.badgeEl&&a.badgeEl.data("num")==c){return}if(a.currentCancel){a.currentCancel()}if(c==0){d=false;
a.badgeEl.remove()}b.src=this.faviconEl.attr("href");b.onload=function(){function g(){if(a.badgeEl){a.badgeEl.remove()}var k=a.drawCanvus(b,c);
a.badgeEl=a.faviconEl.clone();a.badgeEl.data("num",c);a.badgeEl.get(0).href=k.toDataURL("image/png");$("body").append(a.badgeEl)
}function f(){if(a.badgeEl){a.badgeEl.remove()}var k=a.drawCanvus(b,c,true);a.badgeEl=a.faviconEl.clone();a.badgeEl.data("num",c).addClass("alt");
a.badgeEl.get(0).href=k.toDataURL("image/png");$("body").append(a.badgeEl)}function j(){if(i++>10){e();return}if(!a.badgeEl){g()
}else{if(a.badgeEl.is(".alt")){g()}else{f()}}h=window.setTimeout(function(){j()},1000)}function e(){if(h){window.clearTimeout(h)
}g();$(window).unbind("focus",e);$(window).unbind("mousemove",e);a.currentCancel=null}a.currentCancel=e;if(d){var h=null;
var i=0;j();$(window).bind("focus",e);$(window).bind("mousemove",e)}else{g()}}},drawCanvus:function(b,d,e){var c=document.createElement("canvas");
c.height=c.width=16;var a=c.getContext("2d");a.drawImage(b,0,0);a.font='11px "helvetica", sans-serif';if(e){a.fillStyle="rgba(255, 255, 255, 1)"
}else{a.fillStyle="rgba(255, 255, 255, 0.75)"}a.fillRect(3,6,12,10);a.fillStyle="#000";a.fillText(d,4,16);return c}});Orb.createNamespace("DeskPRO.Agent");
DeskPRO.Agent.MediaBrowser=new Orb.Class({Implements:[Orb.Util.Events,Orb.Util.Options],initialize:function(b){this.options={wrapper:null,additionalDropZone:null};
if(b){this.setOptions(b)}var d=this.wrapper=this.options.wrapper;this.tabs=new DeskPRO.UI.SimpleTabs({context:this.wrapper});
var c=$();c=c.add(this.wrapper);if(this.options.additionalDropZone){c=c.add(this.options.additionalDropZone)}this.uploadForm=$("form.upload-form",this.wrapper);
var a=this;this.uploadForm.fileUploadUI({singleFileUploads:false,cancelSelector:"button.cancel-trigger",uploadTable:$(".files-list",this.wrapper),downloadTable:$(".files-list",this.wrapper),dropZone:c,buildUploadRow:function(g,e,f){return $('<div class="uploading">'+g[e].name+' <button class="dp-button x-small cancel-trigger">Cancel</button></div>')
},buildDownloadRow:function(h,g){var f=[];Array.each(h,function(i){f.push(i.row_html)});f=f.join("");var e=a.createDownloadRows(f);
a.fireEvent("filesUploaded",[e]);return e}}).bind("fileuploaddragover",function(f){console.log(f);d.addClass("file-drag-over")
})},createDownloadRows:function(c){var b=$(c);var d=b;var a=this;d.each(function(){var e=this;$(".blob-tags ul",this).tagit({enableBackspace:false,fieldName:"labels",onchange:function(){a.saveFileChanges(e)
}});$("input.file-title").change(function(){a.saveFileChanges(e)});$(".remove-trigger",e).click(function(){$(e).remove()});
$(".link-trigger",e).click(function(){a.fireEvent("addLinkCode",[$(this).data("code"),e])});$(".image-trigger",e).click(function(){a.fireEvent("addImageCode",[$(this).data("code"),e])
});$(".image-edit-trigger",e).click(function(){a.openImageEditor(e)})});return b},saveFileChanges:function(a){a=$(a);var c=$(":input",a).serializeArray();
var b=a.data("blob-id");$.ajax({url:BASE_URL+"agent/media-browser/update-blob/"+b,type:"POST",context:this,data:c,dataType:"json",success:function(d){}})
},openImageEditor:function(c){var a=c;var d=$(c).data("blob-id");var b=this;$.ajax({url:BASE_URL+"agent/media-browser/image-editor/"+d,type:"GET",context:this,dataType:"html",success:function(g){var i=$(g);
var e=$("img",i);var j=function(k){$('input[name="x"]',i).val(k.x);$('input[name="y"]',i).val(k.y);$('input[name="x2"]',i).val(k.x2);
$('input[name="y2"]',i).val(k.y2);$('input[name="w"]',i).val(k.w);$('input[name="h"]',i).val(k.h)};var h=e.Jcrop({onChange:j,onSelect:j,minSize:[5,5],setSelect:[0,0,45,45]});
$(".scale-slider",i).slider({range:"min",value:100,min:1,max:300,step:1,slide:function(n,o){$("input.scale",i).val(o.value);
$(".scale-slider-value",i).html(o.value+"%");var l=$(".jcrop-holder img",i);if(!e.data("real-w")){e.data("real-w",e.width());
e.data("real-h",e.height())}var k=e.data("real-w")*(o.value/100);var m=e.data("real-h")*(o.value/100);e.width(k);e.height(m);
l.width(k);l.height(m)}});var f=new DeskPRO.UI.Overlay({contentElement:i,destroyOnClose:true});$("button.save-trigger",i).click(function(){var k=$(":input",i).serializeArray();
$.ajax({url:BASE_URL+"agent/media-browser/save-image-editor/"+d,type:"POST",context:this,data:k,dataType:"json",success:function(l){f.closeOverlay();
b.fireEvent("addImageEditedCode",["[attach:"+l.blob_id+"]",a])}})});f.openOverlay()}})}});Orb.createNamespace("DeskPRO.Agent");
DeskPRO.Agent.InterfaceEffects=new Orb.Class({Implements:[Orb.Util.Events],initialize:function(){},initPage:function(){}});
Orb.createNamespace("DeskPRO.Agent");DeskPRO.Agent.Widgetable=new Class({widgets:[],initWidgets:function(a,b){b=b||{};Array.each(a,function(e){if(!e||typeOf(e)!="object"){return
}var f=Orb.getNamespacedObject(e.classname);var c=Object.merge(b,{__wrapperSelector:e.wrapperSelector},e.options||{});var d=new f(c);
if(typeOf(e.prefs)=="object"){d.userPrefs=e.prefs}this.widgets.push(d)},this)},initWidgetsDom:function(a){var a=a||document.body;
a=$(a);Array.each(this.widgets,function(c){var b=$(c.options.__wrapperSelector,a);if(!b.length){console.warn("Widget has no element wrapper: %o",c);
return}c.setWidgetElement(b)});if(this.fireEvent){this.fireEvent("allWidgetsReady")}},getWidgets:function(){return this.widgets
}});Orb.createNamespace("DeskPRO.Agent.Ticket");DeskPRO.Agent.Ticket.ChangeManager=new Class({Implements:[Events],ticketPage:null,ticketId:null,updateUrl:null,mode:"single",oldValues:{},changes:{},initialize:function(a){this.ticketPage=a;
this.ticketId=a.getMetaData("ticket_id");this.updateUrl=a.getMetaData("saveActionsUrl")},hasChanges:function(){if(Object.getLength(this.changes)){return true
}return false},addChange:function(b,c,a){if(b.isSameValue(c)){return}this.mode="multi";this.changes[b.getName()]=[b,c];if(a){this.applyChangeForProperty(b,c)
}},applyChangeForProperty:function(a,b){this.oldValues[a.getName()]=a.getValue();a.setValue(b);if(this.mode=="multi"){a.highlightInterfaceElement()
}},applyChanges:function(){Object.each(this.changes,function(c){var a=c[0];var b=c[1];this.applyChangeForProperty(a,b)},this);
this.fireEvent("changesApplied",{changes:this.changes})},revertChanges:function(){Object.each(this.changes,function(c){var b=c[0];
var a=b.getName();if(this.oldValues[a]!==undefined){b.setValue(this.oldValues[a]);b.unhighlightInterfaceElement()}b.changeReverted()
},this);this.oldValues={};this.mode="single"},setInstantChange:function(b,d){if(this.mode=="multi"){this.addChange(b,d,true);
return}b.setValue(d);b.changePersisted();var a=[];this._addPropertyValueToData(a,b.getName(),b.getValue());this.fireEvent("changesApplied",{changes:[b,d]});
var c="saving-"+b.getName().replace(".","_");this.ticketPage.contentWrapper.addClass(c);(function(){this.ticketPage.contentWrapper.removeClass(c)
}).delay(650,this);if(this.updateUrl){$.ajax({type:"POST",url:this.updateUrl,data:a,dataType:"json",context:this,success:function(e){this.ticketPage.unloadTicketTab("ticket-log");
this.fireEvent("updateResult",[e])}})}},saveChanges:function(b){b=b||[];var a=[];Object.each(this.changes,function(f){var d=f[0];
var c=d.getName();var e="saving-"+c.replace(".","_");a.push(e);this.ticketPage.contentWrapper.addClass(e);if(!d.isDisplayOnly()){this._addPropertyValueToData(b,d.getName(),d.getValue())
}d.unhighlightInterfaceElement();d.changePersisted()},this);this.mode="single";(function(){var c="";while(c=a.pop()){this.ticketPage.contentWrapper.removeClass(c)
}}).delay(650,this);if(this.updateUrl){$.ajax({type:"POST",url:this.updateUrl,data:b,dataType:"json",context:this,success:function(c){this.ticketPage.unloadTicketTab("ticket-log");
this.changes={};this.oldValues={};if(c&&c.properties){Object.each(c,function(e,d){var f=this.ticketPage.getPropertyManager(d);
f.setIncomingValue(e)},this)}this.fireEvent("updateResult",[c])}})}},_addPropertyValueToData:function(d,c,b){if(typeOf(b)=="array"){for(var a=0;
a<b.length;a++){var e=b[a];if(typeOf(e)=="object"&&e.full_name!==undefined){d.push({name:e.full_name,value:e.value})}else{if(typeOf(e)=="object"&&e.name!==undefined){d.push({name:"actions["+c+"]["+e.name+"]",value:e.value})
}else{d.push({name:"actions["+c+"][]",value:e})}}}}else{if(typeOf(b)=="object"){Object.each(b,function(g,f){d.push({name:"actions["+c+"]["+f+"]",value:g})
},this)}else{d.push({name:"actions["+c+"]",value:b})}}},setPropertyUpdated:function(a,b){if(typeOf(a)=="string"){a=this.ticketPage.getPropertyManager(a)
}a.setIncomingValue(b);a.pulseInterfaceElement()}});Orb.createNamespace("DeskPRO.Agent.Ticket.Property");DeskPRO.Agent.Ticket.Property.Abstract=new Class({Implements:[Events,Options],options:{},ticketPage:null,initialize:function(b,a){if(a){this.setOptions(a)
}this.ticketPage=b;this.init()},init:function(){},getName:function(){},isSameValue:function(a){if(this.getValue()==a){return true
}return false},getValue:function(){},setValue:function(a){},setIncomingValue:function(a){this.setValue(a)},getInterfaceElement:function(){if(this._interfaceEl!==null){return this._interfaceEl
}this._interfaceEl=this._getInterfaceElement();return this._interfaceEl},_interfaceEl:null,_getInterfaceElement:function(){},pulseInterfaceElement:function(){this.getInterfaceElement().effect("highlight",1200)
},highlightInterfaceElement:function(){this.getInterfaceElement().addClass("change-on")},changePersisted:function(){},changeReverted:function(){},unhighlightInterfaceElement:function(){this.getInterfaceElement().removeClass("change-on")
},isDisplayOnly:function(){return false},isAdditionOnly:function(){return false}});Orb.createNamespace("DeskPRO.Agent.Ticket.Property");
DeskPRO.Agent.Ticket.Property.StandardOption=new Class({Extends:DeskPRO.Agent.Ticket.Property.Abstract,optionName:null,displayNameType:"standardOption",init:function(){var a=["department_id","category_id","product_id","priority_id","workflow_id","status","agent_id","agent_team_id"];
if(a.indexOf(this.options.optionName)==-1){throw"invalidOptionName:"+this.options.optionName}this.optionName=this.options.optionName;
switch(this.optionName){case"department_id":this.displayNameType="department_full";this.displayCaption="Department";break;
case"category_id":this.displayNameType="ticket_category_full";this.displayCaption="Category";break;case"product_id":this.displayNameType="product";
this.displayCaption="Product";break;case"priority_id":this.displayNameType="ticket_priority";this.displayCaption="Priority";
break;case"workflow_id":this.displayNameType="ticket_workflow";this.displayCaption="Workflow";break;case"status":this.displayNameType="status";
this.displayCaption="Status";break;case"agent_id":this.displayNameType="agent";this.displayCaption="Agent";break;case"agent_team_id":this.displayNameType="agent_team";
this.displayCaption="Agent Team";break}},getName:function(){return this.optionName},getValue:function(){return this.getFormEl().val()
},setValue:function(d){this.getFormEl().val(d);if(d=="0"){d=0}var c=this.getInterfaceElement();var b=null;if(c.data("picture-element")){b=$(c.data("picture-element"),c.parent());
if(!b.length){b=$(c.data("picture-element"),c.parent.parent());if(!b.length){b=null}}}if(d){var a=d;if(this.displayNameType){a=DeskPRO_Window.getDisplayName(this.displayNameType,d);
if(!a){a=d}}this.getInterfaceElement().removeClass("no-value").html(a);if(b){b.removeClass("no-value").show().attr("src",b.data("picture-url").replace("{value}",d))
}}else{this.getInterfaceElement().addClass("no-value").html(this.getInterfaceElement().data("no-value-label")||"None");if(b){b.addClass("no-value").hide()
}}},_getInterfaceElement:function(){return $(".prop-val."+this.optionName+":first",this.ticketPage.contentWrapper)},_formEl:null,getFormEl:function(){if(this._formEl!==null){return this._formEl
}this._formEl=$("input."+this.optionName+":first",this.ticketPage.valueForm);return this._formEl}});Orb.createNamespace("DeskPRO.Agent.Ticket.Property");
DeskPRO.Agent.Ticket.Property.Status=new Class({Extends:DeskPRO.Agent.Ticket.Property.StandardOption,setValue:function(e){var a=false;
if(e&&e.constructor.toString().indexOf("Array")!=-1){a=e[1].value;e=e[0].value}else{if(e.indexOf(".")!=-1){var f=e.split(".");
var e=f[0];var a=f[1]}}var c=this.getInterfaceElement().parent();var b=$(".ticket-hidden-bar",this.ticketPage.contentWrapper);
var d=$("span",b);var g=$("input.hidden_status:first",this.ticketPage.valueForm);b.hide();g.val("");if(a){d.text(DeskPRO_Window.getDisplayName("hidden_status",a));
b.show();g.val(a)}this.parent(e);c.removeClass("ticket-open ticket-closed ticket-pending ticket-resolved ticket-hidden").addClass("ticket-"+e)
},getValue:function(){var a=[];a.push({full_name:"actions[status]",value:this.parent()});a.push({full_name:"actions[hidden_status]",value:$("input.hidden_status:first",this.ticketPage.valueForm).val()});
return a}});Orb.createNamespace("DeskPRO.Agent.Ticket.Property");DeskPRO.Agent.Ticket.Property.NewReply=new Class({Extends:DeskPRO.Agent.Ticket.Property.Abstract,optionName:null,menuRepository:null,getName:function(){return"new_reply"
},getValue:function(){return $("form.reply-form",this.ticketPage.ticketReply).serializeArray()},setValue:function(a){this.ticketPage.toggleReplyBar("on");
this.getInterfaceElement().val(a)},setIncomingValue:function(a){this.ticketPage.toggleReplyBar("off");this.ticketPage.displayNewMessage(a);
this.tocketPage.afterNewReply()},_getInterfaceElement:function(){return $('form.reply-form textarea[name="message"]:first',this.ticketPage.ticketReply)
}});Orb.createNamespace("DeskPRO.Agent.Ticket.Property");DeskPRO.Agent.Ticket.Property.TicketField=new Class({Extends:DeskPRO.Agent.Ticket.Property.Abstract,optionName:"",init:function(){this.optionName="ticket_field."+this.options.fieldId
},getName:function(){return this.optionName},getValue:function(){return this.getInterfaceElement().html()},setValue:function(a){this.getInterfaceElement().html(a)
},_getInterfaceElement:function(){return $(".show-fields .custom-field-"+this.options.fieldId+" .field-input",this.ticketPage.contentWrapper)
},isDisplayOnly:function(){return true}});Orb.createNamespace("DeskPRO.Agent.Ticket.Property");DeskPRO.Agent.Ticket.Property.Flag=new Class({Extends:DeskPRO.Agent.Ticket.Property.Abstract,optionName:"flag",init:function(){},getName:function(){return this.optionName
},getValue:function(){return this.getInterfaceElement().data("flag")},setValue:function(b){var a=this.getInterfaceElement().data("flag");
this.getInterfaceElement().data("flag",b);this.getInterfaceElement().removeClass("icon-flag-"+a).addClass("icon-flag-"+b)
},_getInterfaceElement:function(){return $(".ticket-flag:first",this.ticketPage.contentWrapper)}});Orb.createNamespace("DeskPRO.Agent.Ticket.Property");
DeskPRO.Agent.Ticket.Property.Labels=new Class({Extends:DeskPRO.Agent.Ticket.Property.Abstract,optionName:"labels",mode:"add",init:function(){this.mode=this.options.mode
},getName:function(){return this.mode+"_"+this.optionName},getValue:function(){return this._values},_values:null,setValue:function(a){this._values=a;
if(this.mode=="add"){Array.each(a,function(b){this.ticketPage.labelsTagit.add(b,'<span class="new">'+b+"</span>")},this)}else{Array.each(a,function(c){var b=$('input[value="'+c+'"]',this.getInterfaceElement());
if(b.length){b=b.parent();li.hide()}},this)}},changePersisted:function(){if(!this._values){return}if(this.mode=="add"){$("li span.new",this.getInterfaceElement()).removeClass("new")
}else{$("li.pending-remove",this.getInterfaceElement()).remove()}this._values=null},changeReverted:function(){if(!this._values){return
}if(this.mode=="add"){$("li:has(span.new)",this.getInterfaceElement()).remove()}else{Array.each(values,function(b){var a=$('input[value="'+b+'"]',this.getInterfaceElement());
if(a.length){a=a.parent();li.show().addClass("pending-remove")}},this)}this._values=null},_getInterfaceElement:function(){return $("ul.tagit.ticket:first",this.ticketPage.contentWrapper)
}});Orb.createNamespace("DeskPRO.Agent.TicketList");DeskPRO.Agent.TicketList.ChangeManager=new Class({Implements:[Events],ticketPage:null,hasChanges:false,changes:{},ticketIdsBatch:null,initialize:function(a){this.ticketPage=a
},begin:function(a){this.ticketIdsBatch=a},addChange:function(b,d){var a=b.getName();var e=b.getTicketId();if(b.isSameValue(d)){return
}if(!this.changes[e]){this.changes[e]=[]}var c={property:b,newValue:d,hasApplied:false};this.changes[e].push(c);this.hasChanges=true
},applyChangeForEntry:function(c){var b=c.property;var a=b.getName();var d=b.getTicketId();c.oldValue=b.getValue();b.setValue(c.newValue);
b.highlightInterfaceElement();c.hasApplied=true},applyChanges:function(){if(!this.ticketIdsBatch){return}$("tr:not(.on, .line-3)",this.ticketPage.contentWrapper).addClass("faded");
$("tr.on").removeClass("faded");$("table:first",this.ticketPage.contentWrapper).addClass("preview-mode");Array.each(this.ticketIdsBatch,function(a){Array.each(this.changes[a],function(b){this.applyChangeForEntry(b)
},this)},this);this.ticketIdsBatch=null},revertChangeForEntry:function(b){var a=b.property;if(b.hasApplied&&b.oldValue!==undefined){a.setValue(b.oldValue);
a.unhighlightInterfaceElement()}},revertChanges:function(){Object.each(this.changes,function(a,b){Array.each(a,function(c){this.revertChangeForEntry(c)
},this)},this);this.ticketIdsBatch=null;this.changes={};this.hasChanges=false;this.fireEvent("changesCleared")},revertChangesForTicketId:function(b){if(!this.changes[b]){return
}Array.each(this.changes[b],function(c){this.revertChangeForEntry(c)},this);var a=$("tr.ticket-"+b,this.ticketPage.contentWrapper);
a.filter("tr.line-3").hide().find("td > ul").html("");a.filter(":not(.line-3)").addClass("faded").removeClass("with-line-3");
delete this.changes[b];if(Object.getLength(this.changes)==0){this.revertChanges()}},commitChanges:function(){Array.each(Object.values(this.changes),function(a){Object.each(a,function(c){var b=c.property;
b.unhighlightInterfaceElement()},this)},this);this.ticketIdsBatch=null;this.changes={};this.hasChanges=false;this.fireEvent("changesCleared")
},setPropertyUpdated:function(a,b){if(typeOf(a)=="string"){a=this.ticketPage.getPropertyManager(a)}a.setIncomingValue(b);
a.pulseInterfaceElement()}});Orb.createNamespace("DeskPRO.Agent.TicketList.Property");DeskPRO.Agent.TicketList.Property.Abstract=new Class({Implements:[Events,Options],displayNameType:null,displayCaption:null,options:{},ticketPage:null,ticketId:null,initialize:function(b,c,a){if(a){this.setOptions(a)
}this.ticketPage=b;this.ticketId=c;this.init()},init:function(){},getName:function(){},getTicketId:function(){return this.ticketId
},isSameValue:function(a){if(this.getValue()==a){return true}return false},getValue:function(){},setValue:function(a){},setIncomingValue:function(a){this.setValue(a)
},getInterfaceElement:function(){if(this._interfaceEl!==null){return this._interfaceEl}this._interfaceEl=this._getInterfaceElement();
return this._interfaceEl},_interfaceEl:null,_getInterfaceElement:function(){},_buildSelector:function(a){sel="tr.ticket-"+this.ticketId+" "+a;
return sel},getSublineElement:function(){var b=$("tr.ticket-"+this.ticketId+".line-2",this.ticketPage.actionsBarHelper.tableEl);
b.addClass("with-line-3");var d=$("tr.ticket-"+this.ticketId+".line-3",this.ticketPage.actionsBarHelper.tableEl);d.show();
var c=$("ul",d);var a=$('<li class="generated prop-value '+this.getName()+'"></li>');c.append(a);return a},pulseInterfaceElement:function(){this.getInterfaceElement().effect("highlight",1200)
},highlightInterfaceElement:function(){this.getInterfaceElement().addClass("change-on")},unhighlightInterfaceElement:function(){this.getInterfaceElement().removeClass("change-on")
}});Orb.createNamespace("DeskPRO.Agent.TicketList.Property");DeskPRO.Agent.TicketList.Property.StandardOption=new Class({Extends:DeskPRO.Agent.TicketList.Property.Abstract,optionName:null,init:function(){var a=["department_id","category_id","product_id","priority_id","workflow_id","status","agent_id","agent_team_id"];
if(a.indexOf(this.options.optionName)==-1){throw"invalidOptionName:"+this.options.optionName}this.optionName=this.options.optionName;
switch(this.optionName){case"department_id":this.displayNameType="department_full";this.displayCaption="Department";break;
case"category_id":this.displayNameType="ticket_category_full";this.displayCaption="Category";break;case"product_id":this.displayNameType="product";
this.displayCaption="Product";break;case"priority_id":this.displayNameType="ticket_priority";this.displayCaption="Priority";
break;case"workflow_id":this.displayNameType="ticket_workflow";this.displayCaption="Workflow";break;case"status":this.displayNameType="status";
this.displayCaption="Status";break;case"agent_id":this.displayNameType="agent";this.displayCaption="Agent";break;case"agent_team_id":this.displayNameType="agent_team";
this.displayCaption="Agent Team";break}},getValue:function(){return this.getInterfaceElement().data("prop-value")},getName:function(){return this.optionName
},setValue:function(b){if(b=="0"){b=0}this.getInterfaceElement().data("prop-value",b);if(b){var a=b;if(this.displayNameType){a=DeskPRO_Window.getDisplayName(this.displayNameType,b);
if(!a){a=b}}var c=a;if(this.getInterfaceElement().is(".generated")){var c=this.displayCaption+": "+a}this.getInterfaceElement().removeClass("no-value").text(c)
}else{this.getInterfaceElement().addClass("no-value").text("none")}},_getInterfaceElement:function(){var a=$(this._buildSelector(".prop-val."+this.optionName+":first"),this.ticketPage.actionsBarHelper.tableEl);
if(!a.length){a=this.getSublineElement()}return a}});Orb.createNamespace("DeskPRO.Agent.TicketList.Property");DeskPRO.Agent.TicketList.Property.NewReply=new Class({Extends:DeskPRO.Agent.TicketList.Property.Abstract,displayCaption:"Reply",getName:function(){return"new_reply"
},isSameValue:function(a){return false},getValue:function(){return null},setValue:function(a){if(a){var b=a;this.getInterfaceElement().removeClass("no-value").text(b)
}},setIncomingValue:function(a){},_getInterfaceElement:function(){el=this.getSublineElement();return el}});Orb.createNamespace("DeskPRO.Agent.TicketList.Property");
DeskPRO.Agent.TicketList.Property.TicketField=new Class({Extends:DeskPRO.Agent.TicketList.Property.Abstract,optionName:null,init:function(){this.optionName="ticket_field."+this.options.fieldId
},getName:function(){return this.optionName},getValue:function(){return this.getInterfaceElement().html()},setValue:function(a){this.getInterfaceElement().html(a)
},_getInterfaceElement:function(){var a=$(".prop-val .custom-field-"+this.options.fieldId,this.ticketPage.actionsBarHelper.tableEl);
if(!a.length){a=this.getSublineElement()}return a}});Orb.createNamespace("DeskPRO.Agent.TicketList.Property");DeskPRO.Agent.TicketList.Property.Flag=new Class({Extends:DeskPRO.Agent.TicketList.Property.Abstract,optionName:"flag",displayCaption:"Flag",init:function(){},getValue:function(){return this.getInterfaceElement().data("flag")
},getName:function(){return this.optionName},setValue:function(b){var a=this.getInterfaceElement().data("flag");this.getInterfaceElement().data("flag",b);
this.getInterfaceElement().removeClass("icon-flag-"+a).addClass("icon-flag-"+b)},_getInterfaceElement:function(){var a=$(this._buildSelector(".ticket-flag:first"),this.ticketPage.actionsBarHelper.tableEl);
return a}});Orb.createNamespace("DeskPRO.Agent.TicketList.Property");DeskPRO.Agent.TicketList.Property.Labels=new Class({Extends:DeskPRO.Agent.TicketList.Property.Abstract,optionName:"labels",displayCaption:"Flag",mode:"add",init:function(){this.mode=this.options.mode;
if(this.mode=="add"){this.displayCaption="Add Labels"}else{this.displayCaption="Remove Labels"}},isSameValue:function(a){return false
},getName:function(){return this.mode+"_"+this.optionName},getValue:function(){return this._values},_values:null,setValue:function(a){this._values=a;
var b=this.displayCaption+": ";Array.each(a,function(c){b+=" "+c},this);this.getInterfaceElement().text(b)},_getInterfaceElement:function(){var a=this.getSublineElement();
return a}});