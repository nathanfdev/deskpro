Orb.createNamespace("DeskPRO.Form");DeskPRO.Form.InlineEdit=new Class({Implements:Options,options:{baseElement:window.document,editableClass:"editable",triggers:null,ajax:{timeout:20000,type:"POST",url:""},saveFinishCallback:function(){}},activeEdits:[],sendingEdits:{},documentClickSubmitOn:false,initialize:function(b){this.setOptions(b);
var c="."+this.options.editableClass;var a=this;$(c,this.options.baseElement).each(function(){a.initEditable(this)});this.options.baseElement.click(function(d){a.handleDocumentClick(d)
});$(document).keydown(function(d){if(d.keyCode==27){a.closeEditables()}})},initEditable:function(d){var b=this;var a=$(d);
if(a.is(".parent-trigger")){var c=a.parent();c.dblclick(function(){b.startEditable(a)})}else{a.dblclick(function(){b.startEditable(this)
})}if(this.options.triggers){$(this.options.triggers,a.parent()).click(function(e){e.stopPropagation();b.startEditable(a)
})}},handleDocumentClick:function(a){if(!this.documentClickSubmitOn){return}if($(a.target).parents().is(".editable")){return
}this.submitOpen();this.documentClickSubmitOn=false},startEditable:function(c){c=$(c);var a=$("div.rendered-value",c);if(!a.size()){c.wrapInner('<div class="rendered-value" />');
a=$("div.rendered-value",c)}var b=$($(c).data("editable-for"),this.options.baseElement);var e=b.parent();a.fadeOut("fast",function(){a.detach();
b.addClass("editable-fields-on").hide().appendTo(c).fadeIn("fast");$("input, textarea, select",b).addClass("unchanged").change(function(){$(this).removeClass("unchanged")
}).keypress(function(){$(this).removeClass("unchanged")}).filter(":visible").first().focus()});var d={editable:c,rendered_els:a,form_elements:b,form_elements_container:e};
c.addClass("editing").parent().addClass("editing");this.documentClickSubmitOn=true;this.activeEdits.push(d)},submitOpen:function(){if(!this.activeEdits.length){return
}var f=$(".editable-fields-on :input, .editable-ajax-data :input",this.options.baseElement).filter(":not(.unchanged)").serializeArray();
var d=this.activeEdits.length;var e=[];var g=null;while(g=this.activeEdits.pop()){this.setEditinfoLoading(g);e.push(g)}var a=Orb.uuid();
this.sendingEdits[a]=e;if(f.length){var c=this;var b=Object.merge({success:function(i,j,h){console.log("ajax-save data: %o",i);
c.handleAjaxSuccess(a,i)},error:function(h,j,i){console.log("ajax-save error: %s",j);c.handleAjaxFailure(a)},context:this,dataType:"json",data:f},this.options.ajax);
console.log("ajax-save: %s",b.url);console.log("ajax-save data: %o",b.data);$.ajax(b)}else{this.handleAjaxSuccess(a,{})}},handleAjaxSuccess:function(b,d){var i=this.sendingEdits[b];
delete this.sendingEdits[b];var g=null;var c=null;while(c=i.pop()){var f=this._findDataFromEditinfo(c,d);var e=null;if(f){if(f.errors){continue
}else{if(f.html){e=f.html}}}if(!e){var h=$('input[type="text"], textarea, select',c.form_elements).serializeArray();var a=[];
h.each(function(j){a.push(j.value)});e=a.join(", ")}c.rendered_els.remove();c.rendered_els=$('<div class="rendered-value" />').html(e);
this.closeEditinfo(c)}this.options.saveFinishCallback(d)},_findDataFromEditinfo:function(d,c){var e=$(":input",d.form_elements).eq(0).attr("id");
if(!e){return c}var a=e.split("_");do{var b=a.join("_");if(c[b]!=undefined){return c[b]}}while(a.pop());return c},handleAjaxFailure:function(a){},setEditinfoLoading:function(b,a){},closeEditables:function(){var a=null;
while(a=this.activeEdits.pop()){this.closeEditinfo(a)}},closeEditinfo:function(d){var c=d.editable;var a=d.rendered_els;var b=d.form_elements;
var e=d.form_elements_container;b.fadeOut("fast",function(){b.removeClass("editable-fields-on").appendTo(e);if(a.parent().get(0)!=c.get(0)){a.hide().appendTo(c).fadeIn("fast");
c.removeClass("editing").parent().removeClass("editing")}})}});Orb.createNamespace("DeskPRO.Form");DeskPRO.Form.RuleBuilder=new Class({Implements:Events,ruleTpl:null,typeSelectHtml:null,types:{},rowDestroy:{},initialize:function(b){this.ruleTpl=b;
var c=this;var a={};var d=['<ul class="menu" style="display:none">'];$("> .type",this.ruleTpl).each(function(g,j){var h=$(j).data("rule-type");
var k=$(j).attr("title");var f=$(j).data("rule-group");c.types[h]=k;if(f){var l=Orb.uuid();if(!a[f]){a[f]={id:l,types:[]};
d.push("<li>"+f+'<ul class="submenu '+l+'"></ul></li>')}a[f]["types"].push([h,k])}else{d.push('<li data-value="'+h+'">'+k+"</li>")
}});d.push("</ul>");d=d.join("");var e=$(d);Object.each(a,function(i,h){var g=$("ul."+i.id,e);var f=[];Array.each(i.types,function(j){f.push('<li data-prefix="'+h+': " data-value="'+j[0]+'">'+j[1]+"</li>")
});var f=$(f.join(""));g.append(f)});this.menu=new DeskPRO.UI.Menu({menuElement:e,onItemClicked:function(j){var h=$(j.menu.getOpenTriggerElement());
var i=$(j.itemEl).data("value");var f=$("input",h.parent());f.val(i);f.change();var g=$(".current-value",h.parent());g.text(c.types[i])
}})},destroy:function(){Object.each(this.rowDestroy,function(a){Array.each(rowDesotry,function(b){if(b.destroy){b.destroy()
}else{if(b.remove){b.remove()}}})});this.menu.destroy()},addNewRow:function(b,k,c){var d=Orb.uuid();var f=$("> .row",this.ruleTpl).children().clone();
f.data("row-id",d);$(".type:first",f).html('<span class="current-value menu-trigger">Choose criteria...</span><input type="hidden" class="type" name="type" value="" />');
var i=$("input.type:first",f);var j=this;var e=$(".type .current-value",f).click(function(l){j.menu.openMenu(l)});$(".remove",f).click(function(){j.removeRow(f)
});if(k){f.data("form-base-name",k);this.updateFormName(f,k)}var a=false;if(c){i.val(c.type).change();var h=$(".type:first .current-value",f);
h.text(this.types[c.type]);this.handleSelectChange(f);$(".op:first select",f).val(c.op).addClass("op").change();if(typeof c.options=="string"||typeof c.options=="number"||typeOf(c.options)!="object"){$(":input, textarea, select",f).filter(":not(.op, .type)").first().val(c.options)
}else{Object.each(c.options,function(o,m){if(!m||!m.length){return}var l=m.replace(/\[/,"\\[").replace(/\]/,"\\]");if(typeof o=="string"||typeof o=="number"){var n=$('[name="'+l+'"], [name$="'+this.makeArrayName(m,true)+'"]',f).first().val(o).change()
}else{if(typeOf(o)=="object"){Object.each(o,function(s,t){var r=l+"["+t+"]";var p=l+"\\["+t+"\\]";var q=$('[name="'+p+'"], [name$="'+this.makeArrayName(r,true)+'"]',f).first().val(s).change()
},this)}else{if(typeOf(o)=="array"){Array.each(o,function(q){var p=$('option[value="'+q+'"]',f).first().get(0);p.selected=true
},this)}else{var n=$('[name="'+l+'"], [name$="'+this.makeArrayName(m,true)+'"]',f).first().val(o).change()}}}},this)}var g=f.data("rule-handler-inst");
if(g){g.initValues()}}i.change((function(){this.handleSelectChange(f)}).bind(this));$(b).append(f);this.fireEvent("newRow",[f,b,c]);
return f},handleSelectChange:function(o){this.destroyRow(o);var d=o.data("row-id");var b=[];var j=$(".type:first > input.type",o).val();
var c=$('> .type[data-rule-type="'+j+'"]',this.ruleTpl);var i=$("> .op:first",c).children().clone();var e=$("> .options:first",c).clone();
e.css("display","inline");$(".op:first",o).empty().append(i);$(".options:first",o).empty().append(e);var k=null;if(i.is("select")){var k=new DeskPRO.UI.Menu({menuElement:i});
b.push(k)}var a=c.data("rule-handler");var g=null;if(a){ruleHandlerObj=Orb.getNamespacedObject(a);g=new ruleHandlerObj({ruleBuilder:this,rowEl:o,rowId:d,opMenu:k});
b.push(g)}var h=e.children().length;if(h==1){var n=$("select:not(.no-auto):not([multiple])",e);if(n.length){var m=new DeskPRO.UI.Menu({menuElement:n});
b.push(m)}var l=$('input[type="text"]:not(.no-auto), textarea:not(.no-auto)',e);if(l.length){var f=$('<span class="menu-trigger">(click to set value)</span>');
f.appendTo(e);f.click(function(){var q=function(t){if(t.keyCode==13&&!t.metaKey){l.blur();s()}};var s=function(){p.remove();
l.detach().unbind("keypress",q).css("display","none").appendTo(e);r.remove()};var p=$('<div class="backdrop"></div>');p.appendTo("body");
p.click(s);var r=$('<div class="field-overlay"><div class="close-trigger"></div></div>');l.detach().css("display","block").appendTo(r);
r.css({left:f.offset().left,top:f.offset().top});r.appendTo("body").show();l.keypress(q).focus();$(".close-trigger",r).click(s)
});l.css("display","none");l.change(function(){var p=l.val().trim();if(!p){p="(click to set value)"}f.text(p)})}}if(o.data("form-base-name")){this.updateFormName($(".op:first",o),o.data("form-base-name"));
this.updateFormName($(".options:first",o),o.data("form-base-name"))}if(g){g.initRow();o.data("rule-handler-inst",g)}if(b.length){this.rowDestroy[d]=b
}this.fireEvent("selectChange",[o,j])},destroyRow:function(b){var a=b.data("row-id");if(this.rowDestroy[a]){Array.each(this.rowDestroy[a],function(c){if(c.destroy){c.destroy()
}else{if(c.remove){c.remove()}}});delete this.rowDestroy[a]}},removeRow:function(a){this.destroyRow(a);a.remove()},updateFormName:function(b,a){$("[name]",b).each(function(){var c=$(this).attr("name");
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
Orb.createNamespace("DeskPRO.Agent.Widget");DeskPRO.Agent.Widget.FindPerson=new Orb.Class({Implements:[Orb.Util.Options,Orb.Util.Events],initialize:function(a){this.options={};
this.setOptions(a)},_initOverlay:function(){if(this.overlay){return this.overlay}this.overlay=new DeskPRO.UI.Overlay({contentMethod:"ajax",contentAjax:{url:BASE_URL+"agent/people-search/quick-find"}});
this.overlay.addEvent("ajaxDone",this._initElements,this);var a={findPerson:this,overlay:this.overlay};this.fireEvent("initOverlay",[a])
},_initElements:function(){this.wrapper=this.overlay.getWrapper();this.headerNav=$("header > nav",this.wrapper);this.simpleBtn=$(".simple",this.headerNav).click(this.switchSimple.bind(this));
this.advancedBtn=$(".advanced",this.headerNav).click(this.switchAdvanced.bind(this));this.results=$("section.deskpro-results-list",this.wrapper);
this.loading=$("section.results-loading",this.wrapper);this.info=$("section.no-results-info",this.wrapper);this.searchArea=$("section.search-area:first",this.wrapper);
this.simpleForm=$("form.simple:first",this.searchArea);this.advancedForm=$("form.advanced:first",this.searchArea);var a=this;
this.simpleForm.submit(function(b){b.preventDefault();a.submitSearch($(this))});this.advancedForm.submit(function(b){b.preventDefault();
a.submitSearch($(this))})},_initRuleBuilder:function(){if(this.ruleBuilder){return}var b=$(".search-form",this.advancedForm);
var c=$(".search-builder-tpl",this.wrapper);var a=new DeskPRO.Form.RuleBuilder(c);$(".add-term",b).data("add-count",0).click(function(){var d=parseInt($(this).data("add-count"));
var e="terms["+d+"]";$(this).data("add-count",d+1);a.addNewRow($(".search-terms",b),e)});this.ruleBuilder=a},open:function(){this._initOverlay();
this.overlay.open()},close:function(){this.overlay.close()},switchDisplayElement:function(a,b){if(a=="results"){this.results.show();
this.loading.hide();this.info.hide()}else{if(a=="info"){this.info.show();$("> *",this.info).hide();$(b,this.info).show();
this.results.hide();this.loading.hide()}else{if(a=="loading"){this.loading.show();this.results.hide();this.info.hide()}}}},switchSimple:function(){if(this.simpleBtn.is(".on")){return
}this.switchDisplayElement("info","no-search");$(".on",this.headerNav).removeClass("on");this.simpleBtn.addClass("on");this.advancedForm.slideUp();
this.simpleForm.slideDown()},switchAdvanced:function(){if(this.advancedBtn.is(".on")){return}this._initRuleBuilder();this.switchDisplayElement("info","no-search");
$(".on",this.headerNav).removeClass("on");this.advancedBtn.addClass("on");this.simpleForm.slideUp();this.advancedForm.slideDown()
},submitSearch:function(a){var b=a.serializeArray();this.switchDisplayElement("loading");$.ajax({url:BASE_URL+"agent/people-search/quick-find-search.json",data:b,dataType:"json",type:"POST",context:this,success:this.handleSearchSuccess})
},handleSearchSuccess:function(a){if(a.no_results){this.switchDisplayElement("info","no-results");return}this.results.empty().html(a.html);
this._initNewResults();this.switchDisplayElement("results")},_initNewResults:function(){var a=this;$(".choose-trigger",this.results).click(function(e){e.preventDefault();
var b=$(this);var d=b.data("person-id");var c={el:b,personId:d,event:e,doCloseOverlay:true};a.fireEvent("choosePerson",[c]);
if(c.doCloseOverlay){a.close()}})},destroy:function(){if(this.overlay){this.overlay.destroy()}if(this.ruleBuilder){this.ruleBuilder.destroy()
}}});Orb.createNamespace("DeskPRO.Agent.Widget");DeskPRO.Agent.Widget.AgentSelector=new Orb.Class({Implements:[Orb.Util.Options,Orb.Util.Events],initialize:function(b){this.options={triggerElement:null,agentList:null,multipleChoice:false,showNone:false,noneLabel:"Unassigned",zIndex:1000001,startWith:[]};
if(b){this.setOptions(b)}this.previousSelection="";if(this.options.triggerElement){var a=this;$(this.options.triggerElement).click(function(c){c.preventDefault();
a.open(c)})}},_initWrapper:function(){if(this.wrapper){return}var a=$("li",this.options.agentList);this.backdrop=$('<div class="backdrop"></div>').appendTo("body");
this.backdrop.click(this.close.bind(this));this.wrapper=$('<div class="field-overlay agent-selector" style="display:none;"><div class="close-trigger"></div></div>');
$(".close-trigger",this.wrapper).click(this.close.bind(this));var e=$('<div class="with-scrollbar"><div class="scrollbar"><div class="track"><div class="thumb"><div class="end"></div></div></div></div><div class="scroll-viewport"><div class="scroll-content"></div></div></div>');
if(a.length>=10){this.filter=$('<div class="filter"><div class="input-wrap"><input type="text" value="" placeholder="Find an agent" /></div></div>').appendTo(this.wrapper);
$("input",this.filter).keyup(this.updateFilter.bind(this))}else{this.filter=null}var d=this.options.multipleChoice;var g=this.options.startWith;
var l=$("<ul />");if(this.options.showNone){var k=$('<li class="agent-0" data-agent-id="0"><div class="name"><a>'+this.options.noneLabel+"</a></div></li>");
var b=$('<div class="choice" />');var h="";if(!g.length){h='checked="checked"'}if(d){var c=$('<input type="checkbox" name="agents[]" '+h+' value="0" class="agent-choice-0" />')
}else{var c=$('<input type="radio" name="agents[]" '+h+' value="0" class="agent-choice-0" />')}c.appendTo(b);k.append(b);
k.append($('<br style="clear:left;height: 1px;overflow: hidden;"/>'));l.append(k)}a.each(function(){var t=$(this);var n=t.data("agent-id");
var r=$("a:first",t).text();var p=$("img:first",t);var u=$('<li class="agent-'+n+'" data-agent-id="'+n+'" />');if(p.length){var m=$('<div class="avatar" />');
p.clone().appendTo(m);m.appendTo(u)}var v=$('<div class="name" />');v.append("<a>"+Orb.escapeHtml(r)+"</a>");v.appendTo(u);
var s="";if(g.indexOf(n+"")!==-1||g.indexOf(parseInt(n))!==-1){s='checked="checked"'}var o=$('<div class="choice" />');if(d){var q=$('<input type="checkbox" name="agents[]" '+s+' value="'+n+'" class="agent-choice-'+n+'" />')
}else{var q=$('<input type="radio" name="agents[]" '+s+' value="'+n+'" class="agent-choice-'+n+'" />')}q.appendTo(o);o.appendTo(u);
u.append($('<br style="clear:left;height: 1px;overflow: hidden;"/>'));u.appendTo(l);u.click(function(w){w.stopPropagation();
if(!$(w.target).is("input")){q.click()}})});delete a;this.agentList=l;var j=this;$('input[type="checkbox"], input[type="radio"]',l).click(function(o){var p=$(this).val();
var n=$(this).is(":checked");var m={agentSelector:j,element:$(this),agentId:p,checked:n,event:o};j.fireEvent("selectionClick",[m])
});l.appendTo($("div.scroll-content",e));e.appendTo(this.wrapper);this.listWrapper=e;this.wrapper.appendTo("body");var f=this.getSelection();
if(this.options.multipleChoice){f=f.join(",")}this.previousSelection=f;var i={agentSelector:this,wrapper:this.wrapper};this.fireEvent("initWrapper",[i])
},updateFilter:function(){var a=$("input",this.filter);var c=a.val().trim().toLowerCase();var b=$("> li",this.agentList);
if(!c){b.show();return}b.each(function(){var d=$("a:first",this).text().toLowerCase();if(d.indexOf(c)!==-1){$(this).show()
}else{$(this).hide()}})},open:function(a){this._initWrapper();var g=$(a.target);var b=this.wrapper.outerWidth();var k=this.wrapper.outerHeight();
var i=$(document).width();var d=$(document).height();var f=g.offset().left;var e=g.offset().top;if(f+b<i){var c=f+6}else{var c=f-b-4
}if(e+k<d){var h=e-6}else{var h=e-k+4}if(h<0){h=5}this.backdrop.show();this.wrapper.addClass("open");this.wrapper.css({"z-index":this.options.zIndex,position:"absolute",top:h,left:c,display:"block"});
this.listWrapper.tinyscrollbar();var j={agentSelector:this,event:a};this.fireEvent("open",[j])},close:function(){if(!this.wrapper.is(".open")){return
}var a={agentSelector:this,cancelClose:false};this.fireEvent("beforeClose",[a]);if(a.cancelClose){return}delete a.cancelClose;
this.backdrop.hide();this.wrapper.hide().removeClass("open");this.fireEvent("close",[a]);var b=this.getSelection();if(this.options.multipleChoice){b=b.join(",")
}if(this.previousSelection!=b){a.selection=this.getSelection();this.fireEvent("selectionChanged",[a]);this.previousSelection=b
}},isOpen:function(){return this.wrapper.is(".open")},getWrapper:function(){return this.wrapper},getSelection:function(){if(this.options.multipleChoice){var a=[];
$('input[type="checkbox"]:checked',this.agentList).each(function(){a.push($(this).val())});return a}else{var b=$('input[type="radio"]:checked:first',this.agentList).val();
return b}}});Orb.createNamespace("DeskPRO.Agent.Widget");DeskPRO.Agent.Widget.SnippetViewer=new Orb.Class({Implements:[Orb.Util.Options,Orb.Util.Events],initialize:function(b){this.options={viewUrl:null,triggerElement:null};
this.setOptions(b);if(this.options.triggerElement){var a=this;$(this.options.triggerElement).click(function(c){c.preventDefault();
c.stopPropagation();a.open()})}this.pop=new DeskPRO.Agent.PageHelper.Popover({pageUrl:this.options.viewUrl,onPageInit:function(c,d){d.addEvent("closeSelf",function(e){e.cancel=true;
a.destroyPop()});d.addEvent("snippetClick",function(e){a.fireEvent("snippetClick",[e])})}})},open:function(){this.pop.open()
},close:function(){if(this.pop){this.pop.close()}},destroy:function(){this.pop.destroy()}});Orb.createNamespace("DeskPRO.Agent.Widget");
DeskPRO.Agent.Widget.MergeTicket=new Orb.Class({Implements:[Orb.Util.Options,Orb.Util.Events],initialize:function(a){this.options={ticketId:0,destroyOnClose:false};
this.setOptions(a);this.ticketId=this.options.ticketId;this.overlay=null},_initOverlay:function(){if(this.overlay){return this.overlay
}var a=[];Array.each(DeskPRO_Window.getTabWatcher().findTabType("ticket"),function(b){var c=b.page.getMetaData("ticket_id");
if(c&&c!=this.ticketId){a.push({name:"open_ticket_ids[]",value:c})}});this.overlay=new DeskPRO.UI.Overlay({contentMethod:"ajax",contentAjax:{url:BASE_URL+"agent/tickets/merge-overlay/"+this.ticketId,data:a}});
this.overlay.addEvent("ajaxDone",this._initElements.bind(this))},_initElements:function(){this.wrapper=this.overlay.getWrapper();
var a=this;$(".merge-trigger",this.wrapper).click(function(){$(this).text("...").attr("disabled",true);$(".merge-trigger",this.wrapper).attr("disabled",true);
var b=$(this).data("ticket-id");var c=a.ticketId;$.ajax({url:BASE_URL+"agent/tickets/merge/"+c+"/"+b,type:"POST",dataType:"json",success:function(d){if(d.success){a.fireEvent("mergeSuccess",[d])
}else{a.fireEvent("mergeError",[d])}},error:function(d){a.fireEvent("mergeError",[d])}})})},open:function(){this._initOverlay();
this.overlay.open()},close:function(){this.overlay.close();if(this.options.destroyOnClose){this.desotry}},destroy:function(){if(this.overlay){this.overlay.destroy()
}}});Orb.createNamespace("DeskPRO.Agent.Widget");DeskPRO.Agent.Widget.MergeIdea=new Orb.Class({Implements:[Orb.Util.Options,Orb.Util.Events],initialize:function(a){this.options={ideaId:0,destroyOnClose:false};
this.setOptions(a);this.ideaId=this.options.ideaId;this.overlay=null},_initOverlay:function(){if(this.overlay){return this.overlay
}var a=[];Array.each(DeskPRO_Window.getTabWatcher().findTabType("idea"),function(b){var c=b.page.getMetaData("idea_id");if(c&&c!=this.ideaId){a.push({name:"open_idea_ids[]",value:c})
}});this.overlay=new DeskPRO.UI.Overlay({contentMethod:"ajax",contentAjax:{url:BASE_URL+"agent/ideas/merge-overlay/"+this.ideaId,data:a}});
this.overlay.addEvent("ajaxDone",this._initElements.bind(this))},_initElements:function(){this.wrapper=this.overlay.getWrapper();
var a=this;$(".merge-trigger",this.wrapper).click(function(){$(this).text("...").attr("disabled",true);$(".merge-trigger",this.wrapper).attr("disabled",true);
var c=$(this).data("idea-id");var b=a.ideaId;$.ajax({url:BASE_URL+"agent/ideas/merge/"+b+"/"+c,type:"POST",dataType:"json",success:function(d){if(d.success){a.fireEvent("mergeSuccess",[d])
}else{a.fireEvent("mergeError",[d])}},error:function(d){a.fireEvent("mergeError",[d])}})})},open:function(){this._initOverlay();
this.overlay.open()},close:function(){this.overlay.close();if(this.options.destroyOnClose){this.desotry}},destroy:function(){if(this.overlay){this.overlay.destroy()
}}});Orb.createNamespace("DeskPRO.Agent.Widget");DeskPRO.Agent.Widget.AgentChatWin_Registry={};DeskPRO.Agent.Widget.AgentChatWin_Find=function(a){var b=null;
Object.each(DeskPRO.Agent.Widget.AgentChatWin_Registry,function(c){if(!b&&c.getConvoId()==a){b=c}});return b};DeskPRO.Agent.Widget.AgentChatWin_FindAgents=function(a){var b=null;
a=a.sort(function(d,c){return parseInt(d)-parseInt(c)});agent_ids_str=a.join(",");Object.each(DeskPRO.Agent.Widget.AgentChatWin_Registry,function(c){if(!b&&c.agentIdsStr==agent_ids_str){b=c
}});return b};DeskPRO.Agent.Widget.AgentChatWin=new Orb.Class({Implements:[Orb.Util.Events,Orb.Util.Options],initialize:function(a){this.options={convoId:0,agentIds:[]};
this.setOptions(a);this.uuid=Orb.uuid();this.convoId=this.options.convoId;this.chatsWrapper=$("#agent_chats_wrapper");DeskPRO.Agent.Widget.AgentChatWin_Registry[this.uuid]=this;
this.agentIds=[];Array.each(this.options.agentIds,function(b){this.agentIds.push(parseInt(b))},this);this.agentIds=this.agentIds.sort(function(d,c){return d-c
});this.agentIdsStr=this.agentIds.join(",");this.wrapper=null;this._initWindow()},_initWindow:function(){if(this._hasInitWin){return
}this._hasInitWin=true;var b=this;if(this.convoId){var e=$("#agent_chat_conversation_"+this.convoId);if(e.length){return}}if(this.agentIds.length==1||(this.agentIds.length==2&&this.agentIds.indexOf(parseInt(DESKPRO_PERSON_ID))!=-1)){var d=DeskPRO_Window.getAgentInfo(this.agentIds[0]);
var f=$.tmpl("agent_chat_conversation",{local_id:this.uuid,to_agent_name:d.name,to_agent_id:d.id,to_agent_picture:d.pictureUrlSizable.replace("{SIZE}",30)})
}else{var f=$.tmpl("agent_groupchat_conversation",{local_id:this.uuid})}this.wrapper=f;var a=$("> section.agent-chat:last",this.chatsWrapper);
if(a.length){var c=a.position().left+$("> nav",a).outerWidth()+8;f.css("left",c)}this.chatsWrapper.append(f);f.addClass("new-message");
$("textarea",f).keypress((function(h){if(h.keyCode==13&&!h.metaKey){h.preventDefault();this._fireSendMessage()}}).bind(this));
var g=$("> nav",f);g.click(function(h){h.stopPropagation();if(f.is(".open")){f.removeClass("open")}else{f.addClass("open")
}});$(".close-trigger",g).click(function(h){h.stopPropagation();b.destroy()})},_fireSendMessage:function(){var a=$("textarea",this.wrapper);
var b=a.val().trim();a.val("");if(!b.length){return}this.sendMessage(b);this.showMyMessage(b)},getConvoId:function(){return this.convoId
},getConvoLocalId:function(){return this.uuid},sendMessage:function(b){var c=[];c.push({name:"content",value:b});c.push({name:"local_id",value:this.uuid});
Array.each(this.agentIds,function(e){c.push({name:"agent_ids[]",value:e})});var a=Orb.uuid();var d={message:b,localMessageId:a,convoId:this.convoId,convoLocalId:this.uuid};
this.fireEvent("sendMessage",[this,d]);$.ajax({url:BASE_URL+"agent/agent-chat/send-agent-message/"+this.convoId,data:c,contentType:"json",context:this,success:function(e){this.convoId=e.conversation_id;
d.messageId=e.message_id;d.convoId=this.convoId;this.fireEvent("sendMessageDone",[this,d])}})},showMessage:function(d,c){var b=DeskPRO_Window.getAgentInfo(d);
var a=$.tmpl("agent_chat_message",{author_id:d,author_name:b.name,author_picture:b.pictureUrlSizable.replace("{SIZE}",20),message:c});
$(".messages-container:first",this.wrapper).append(a).scrollTop(100000)},showMyMessage:function(b){var a=$.tmpl("agent_chat_message_me",{message:b});
$(".messages-container:first",this.wrapper).append(a).scrollTop(100000)},open:function(){this.wrapper.addClass("open")},close:function(){this.wrapper.removeClass("open")
},destroy:function(){if(this.wrapper){this.wrapper.remove();this.wrapper=null}this.fireEvent("destroy",[this]);delete DeskPRO.Agent.Widget.AgentChatWin_Registry[this.uuid]
}});Orb.createNamespace("DeskPRO.Agent.Widget");DeskPRO.Agent.Widget.FilterGroupEditor=new Orb.Class({Implements:[Orb.Util.Options,Orb.Util.Events],initialize:function(a){this.options={containerElement:null,listElement:null,boundListElement:null,elements:".filter",controlEl:"#ticket_filter_group_editor",triggerElement:null,scrollWatchTimeout:100};
this.setOptions(a);if(this.options.triggerElement){this.enableTriggerElement(this.options.triggerElement)}},_initControl:function(){if(this._hasInit){return
}this._hasInit=true;this.fireEvent("preInit",[this]);this.containerElement=$(this.options.containerElement);this.listElement=$(this.options.listElement);
this.boundListElement=$(this.options.boundListElement);this.elements=this.options.elements;if(typeOf(this.elements)=="string"){this.elements=$(this.elements,this.listElement)
}this.controlEl=$(this.options.controlEl);this.controlEl.detach().hide().appendTo("body");this.controlEl.click(this.close.bind(this));
this.controlRealEl=$(".filter-group-editor",this.controlEl);this.controlRealEl.click(function(a){a.stopPropagation()});this.editorRowTpl=$(".editor-row-tpl",this.controlEl).first().get(0).innerHTML.trim();
this.editorFieldsTpl=$(".editor-fields-tpl",this.controlEl).first().get(0).innerHTML.trim();this.elements.each((function(b,c){c=$(c);
var g=c.data("filter-id");var e=$(this.editorRowTpl);var d=$(this.editorFieldsTpl);d.addClass("field-option");$(".field-wrap",e).append(d);
var f=c.data("grouping-ignore");if(f){f=f.split(",");Array.each(f,function(h){$('[value="'+h+'"]',d).remove()})}var a=this;
d.change(function(){a.fireEvent("groupingChanged",[parseInt(g),d.val(),d,a])});e.addClass("filter-"+g);e.appendTo(this.controlRealEl)
}).bind(this));this.backdrop=$('<div class="backdrop" />').hide().appendTo("body");this.backdrop.click(this.close.bind(this));
this.backdrop2=$('<div class="backdrop" />').hide().appendTo("body");this.backdrop2.click(this.close.bind(this));$(".close",this.controlRealEl).first().click((function(a){a.preventDefault();
a.stopPropagation();this.close()}).bind(this));this.fireEvent("init",[this])},enableTriggerElement:function(a){$(a).click((function(b){b.stopPropagation();
b.preventDefault();this.toggle()}).bind(this))},getElement:function(){return this.controlEl},isOpen:function(){if(!this._hasInit){return false
}return this.controlEl.is(".open")},open:function(){if(this.isOpen()){return}this._initControl();this.controlEl.addClass("open");
var c=this.containerElement.offset();var b=this.containerElement.outerHeight();var a=this.containerElement.outerWidth();this.controlEl.css({top:c.top,bottom:0,left:c.left+a+1,right:0});
this.updatePositions();this.controlEl.addClass("open");this.controlEl.fadeIn();this.backdrop.css({left:c.left+a});this.backdrop2.css({left:0,width:c.left});
this.backdrop.show();this.backdrop2.show();this._startScrollWatch()},updatePositions:function(){var d=this.listElement;var b=false;
if(!d.is(":visible")){d=this.boundListElement;b=true}var a=d.outerHeight();this.controlRealEl.css({height:a,top:0,left:0});
var c=25;this.controlRealEl.css({"margin-top":c});$(this.options.elements,d).each((function(f,g){g=$(g);var k=g.data("filter-id");
var j=g.position();if(b){var h=$(".filter-"+g.data("filter-name").replace("_w_hold",""),this.listElement).data("filter-id");
var e=$(".filter-"+h,this.controlEl)}else{var e=$(".filter-"+k,this.controlEl)}e.css({top:j.top-25})}).bind(this))},syncScroll:function(){var b=this.listElement.height();
if(!this.lastListElHeight||b!=this.lastListElHeight){this.updatePositions()}var c=this.containerElement.position().top;var d=this.listElement.position().top;
var a=c;this.controlRealEl.css("top",a+"px")},_startScrollWatch:function(){if(this.scrollWatchTimer){window.clearTimeout(this.scrollWatchTimer)
}this.scrollWatchTimer=window.setInterval(this.syncScroll.bind(this),this.options.scrollWatchTimeout)},_stopScrollWatch:function(){if(this.scrollWatchTimer){window.clearTimeout(this.scrollWatchTimer);
this.scrollWatchTimer=null}},close:function(){if(!this.isOpen()){return}this._stopScrollWatch();this.controlEl.removeClass("open");
this.controlEl.fadeOut();this.backdrop.hide();this.backdrop2.hide()},toggle:function(){if(this.isOpen()){this.close()}else{this.open()
}},destroy:function(){if(this._hasInit){this.controlEl.remove();this.backdrop.remove();this.backdrop2.remove()}}});Orb.createNamespace("DeskPRO.Agent.Widget");
DeskPRO.Agent.Widget.FilterOptionsPop=new Orb.Class({Implements:[Orb.Util.Options,Orb.Util.Events],initialize:function(a){this.options={containerElement:null,listElement:null,elements:".filter",controlEl:"#ticket_customfilter_group_editor",triggerElement:null,scrollWatchTimeout:100};
this.setOptions(a);if(this.options.triggerElement){this.enableTriggerElement(this.options.triggerElement)}},_initControl:function(){if(this._hasInit){return
}this._hasInit=true;this.fireEvent("preInit",[this]);this.containerElement=$(this.options.containerElement);this.listElement=$(this.options.listElement);
this.elements=this.options.elements;if(typeOf(this.elements)=="string"){this.elements=$(this.elements,this.listElement)}this.controlEl=$(this.options.controlEl);
this.controlEl.detach().hide().appendTo("body");this.controlEl.click(this.close.bind(this));this.controlRealEl=$(".filter-group-editor",this.controlEl);
this.controlRealEl.click(function(a){a.stopPropagation()});this.editorRowTpl=$(".editor-row-tpl",this.controlEl).first().get(0).innerHTML.trim();
this.editorFieldsTpl=$(".editor-fields-tpl",this.controlEl).first().get(0).innerHTML.trim();this.elements.each((function(a,b){b=$(b);
var e=b.data("filter-id");var d=$(this.editorRowTpl);var c=$(this.editorFieldsTpl);c.addClass("field-option");$(".field-wrap",d).append(c);
d.addClass("filter-"+e);d.addClass("filter-row");d.data("filter-id",e);this.fireEvent("initRow",[d,e,this]);d.appendTo(this.controlRealEl)
}).bind(this));this.backdrop=$('<div class="backdrop" />').hide().appendTo("body");this.backdrop.click(this.close.bind(this));
this.backdrop2=$('<div class="backdrop" />').hide().appendTo("body");this.backdrop2.click(this.close.bind(this));$(".close",this.controlRealEl).first().click((function(a){a.preventDefault();
a.stopPropagation();this.close()}).bind(this));this.fireEvent("init",[this])},enableTriggerElement:function(a){$(a).click((function(b){b.stopPropagation();
b.preventDefault();this.toggle()}).bind(this))},getElement:function(){return this.controlEl},isOpen:function(){if(!this._hasInit){return false
}return this.controlEl.is(".open")},open:function(){if(this.isOpen()){return}this._initControl();this.fireEvent("preOpen",[this]);
this.controlEl.addClass("open");var c=this.containerElement.offset();var b=this.containerElement.outerHeight();var a=this.containerElement.outerWidth();
this.controlEl.css({top:c.top,bottom:0,left:c.left+a+1,right:0});this.updatePositions();this.controlEl.addClass("open");this.controlEl.fadeIn();
this.backdrop.css({left:c.left+a});this.backdrop2.css({left:0,width:c.left});this.backdrop.show();this.backdrop2.show();this._startScrollWatch();
this.fireEvent("open",[this])},updatePositions:function(){var c=this.listElement;var a=c.outerHeight();this.controlRealEl.css({height:a,top:0,left:0});
var b=this.listElement.offset().top-41;this.controlRealEl.css({"margin-top":b});$(this.options.elements,c).each((function(e,f){f=$(f);
var h=f.data("filter-id");var g=f.position();var d=$(".filter-"+h,this.controlEl);d.css({top:g.top-b})}).bind(this))},syncScroll:function(){var b=this.listElement.height();
if(!this.lastListElHeight||b!=this.lastListElHeight){this.updatePositions()}var c=this.containerElement.position().top;var d=this.listElement.position().top;
var a=c;this.controlRealEl.css("top",a+"px")},_startScrollWatch:function(){if(this.scrollWatchTimer){window.clearTimeout(this.scrollWatchTimer)
}this.scrollWatchTimer=window.setInterval(this.syncScroll.bind(this),this.options.scrollWatchTimeout)},_stopScrollWatch:function(){if(this.scrollWatchTimer){window.clearTimeout(this.scrollWatchTimer);
this.scrollWatchTimer=null}},close:function(){if(!this.isOpen()){return}this.fireEvent("preClose",[this]);this._stopScrollWatch();
this.controlEl.removeClass("open");this.controlEl.fadeOut();this.backdrop.hide();this.backdrop2.hide();this.fireEvent("close",[this])
},toggle:function(){if(this.isOpen()){this.close()}else{this.open()}},destroy:function(){if(this._hasInit){this.controlEl.remove();
this.backdrop.remove();this.backdrop2.remove()}}});Orb.createNamespace("DeskPRO.Agent.Widget");DeskPRO.Agent.Widget.BackgroundPopout=new Orb.Class({Implements:[Orb.Util.Options,Orb.Util.Events],initialize:function(a){this.options={loadUrl:null,tabRoute:null,initialTimeout:12000,periodicalTimeout:600000,autostart:true};
this.setOptions(a);this.template=null;this.xhr=null;this.timeout=null;this.pop=null;if(this.autostart){this.startTimeout()
}},startTimeout:function(){var a;if(this.timeout){return}if(this.template){a=this.options.periodicalTimeout}else{a=this.options.initialTimeout
}this.timeout=window.setTimeout(this.loadTemplate.bind(this),a)},loadTemplate:function(a){if(this.timeout){window.clearTimeout(this.timeout);
this.timeout=null}if(this.xhr){return}this.xhr=$.ajax({url:this.options.loadUrl,type:"GET",dataType:"html",context:this,success:function(b){this.template=b;
if(a){a.call(this,b)}},complete:function(){this.startTimeout();this.xhr=null}})},invalidateTemplate:function(){this.loadTemplate()
},getTemplate:function(){return this.template},open:function(d){if(this.pop){this.pop.open();if(d){d(this.pop.page)}return
}var b=this;var a=new DeskPRO.Agent.PageHelper.Popover({tabRoute:this.options.tabRoute,onPageInit:function(e,f){f.addEvent("closeSelf",function(g){g.cancel=true;
b.clear()});if(d){d(f)}}});var c=this.getTemplate();if(c){a.setHtml(c)}else{this.loadTemplate(function(e){a.setHtml(e)})}this.pop=a;
a.open()},toggle:function(){if(!this.pop){this.open();return}this.pop.toggle()},close:function(){if(!this.pop){return}this.pop.close()
},clear:function(){if(this.pop){this.pop.destroy();this.pop=null}},destroyPop:function(){if(!this.pop){return}this.pop.destroy();
this.pop=null}});Orb.createNamespace("DeskPRO.Agent.RuleBuilder");DeskPRO.Agent.RuleBuilder.TermAbstract=new Orb.Class({Implements:[Orb.Util.Events,Orb.Util.Options],initialize:function(a){this.options={ruleBuilder:null,rowEl:null,rowId:null,opMenu:null};
if(a){this.setOptions(a)}this.ruleBuilder=this.options.ruleBuilder;this.rowEl=$(this.options.rowEl);this.rowId=this.options.rowId;
this.opMenu=this.options.opMenu;this.init()},init:function(){},initRow:function(){},initValues:function(){}});Orb.createNamespace("DeskPRO.Agent.RuleBuilder");
DeskPRO.Agent.RuleBuilder.DateTerm=new Orb.Class({Extends:DeskPRO.Agent.RuleBuilder.TermAbstract,initRow:function(){this._initUi()
},initValues:function(){var b=null,a=null;b=this.date1Input.val();if(b){a=new Date(b*1000);this.date1Widget.datepicker("setDate",a)
}b=this.date2Input.val();if(b){a=new Date(b*1000);this.date2Widget.datepicker("setDate",a)}this.updateStatus()},_initUi:function(){this.opInput=$("select.op",this.rowEl);
this.date1Input=$("input.date1-input",this.rowEl);this.date2Input=$("input.date2-input",this.rowEl);this.date1Display=$("input.date1-display",this.rowEl);
this.date2Display=$("input.date2-display",this.rowEl);this.currentValue=$(".status-value",this.rowEl);this.currentValue.text("(click to set)");
this.currentValue.click(this.show.bind(this));this.dateWrap=$(".date-wrap",this.rowEl);this.backdrop=$('<div class="backdrop" style="display: none"></div>');
this.backdrop.appendTo("body");this.backdrop.click(this.hide.bind(this));this.wrapper=$('<div class="field-overlay" style="display:none"><div class="close-trigger"></div></div>');
$(".close-trigger",this.wrapper).click(this.hide.bind(this));this.dateWrap.detach().appendTo(this.wrapper).css("display","block");
this.wrapper.appendTo("body");this.date1=$(".date1",this.dateWrap);this.date2=$(".date2",this.dateWrap);var a=this;this.date1Widget=$(".widget",this.date1).datepicker({dateFormat:"M d, yy",onSelect:function(d,c){a.date1Input.val(a.date1Widget.datepicker("getDate").getTime()/1000);
a.date1Display.val(d);a.updateStatus()}});this.date2Widget=$(".widget",this.date2).datepicker({dateFormat:"M d, yy",onSelect:function(d,c){a.date2Input.val(a.date2Widget.datepicker("getDate").getTime()/1000);
a.date2Display.val(d);a.updateStatus()}});var b=function(d){var e=strtotime(d.val());if(!e){return null}var c=new Date(e*1000);
return c};this.date1Display.change(function(){var c=b($(this));if(!c){$(this).val("");return}a.date1Widget.datepicker("setDate",c)
});this.date2Display.change(function(){var c=b($(this));if(!c){$(this).val("");return}a.date2Widget.datepicker("setDate",c)
});$(".switcher",this.date1).click((function(){var d=$(".date",this.date1);var c=$(".relative",this.date1);if(d.is(":visible")){d.hide();
c.show()}else{c.hide();d.show()}}).bind(this));$(".switcher",this.date2).click((function(){var d=$(".date",this.date2);var c=$(".relative",this.date2);
if(d.is(":visible")){d.hide();c.show()}else{c.hide();d.show()}}).bind(this))},show:function(){if(this.opInput.val()=="between"){this.dateWrap.addClass("two")
}else{this.dateWrap.removeClass("two")}this.wrapper.css({left:this.currentValue.offset().left,top:this.currentValue.offset().top});
this.backdrop.show();this.wrapper.show()},updateStatus:function(){var e="",c="",b="";var a=$(".relative1",this.date1);var g=$(".relative2",this.date2);
if(a.is(":visible")){$(".date1-relative-input",this.rowEl).val($(".relative1-input",this.date1).val());$(".date1-relative-type",this.rowEl).val($(".relative1-type",this.date1).val());
this.date1Input.val("");if($(".relative1-input",this.date1).val().trim().length){e=$(".relative1-input",this.date1).val()+" "+$(".relative1-type",this.date1).val()+" ago"
}}else{var f=this.date1Widget.datepicker("getDate");if(f){e=$.datepicker.formatDate("M d, yy",f)}}if(g.is(":visible")){$(".date2-relative-input",this.rowEl).val($(".relative2-input",this.date2).val());
$(".date2-relative-type",this.rowEl).val($(".relative2-type",this.date2).val());this.date2Input.val("");if($(".relative2-input",this.date2).val().trim().length){c=$(".relative2-input",this.date2).val()+" "+$(".relative2-type",this.date2).val()+" ago"
}}else{var d=this.date2Widget.datepicker("getDate");if(d){c=$.datepicker.formatDate("M d, yy",d)}}if(!e.length){e="(click to set)"
}if(!c.length){e="(click to set)"}if(this.opInput.val()=="between"){b=e+" and "+c}else{b=e}this.currentValue.text(b)},hide:function(){this.updateStatus();
this.backdrop.hide();this.wrapper.hide()},destroy:function(){this.wrapper.remove();this.backdrop.remove()}});Orb.createNamespace("DeskPRO.Agent.RuleBuilder");
DeskPRO.Agent.RuleBuilder.LabelsTerm=new Orb.Class({Extends:DeskPRO.Agent.RuleBuilder.TermAbstract,initRow:function(){this.inner=$(".label-chooser-wrap",this.rowEl);
this.labelType=this.rowEl.data("label-type");this.labelsList=$("ul:first",this.rowEl);this.labelsInput=new DeskPRO.UI.LabelsInput({type:"tickets",list:this.labelsList,onChange:this.updateLabels.bind(this)});
this.currentValue=$(".status-value",this.rowEl);this.currentValue.text("(click to set)");this.currentValue.click(this.show.bind(this));
this.values=$(".label-values",this.rowEl);this.backdrop=$('<div class="backdrop" style="display: none"></div>');this.backdrop.appendTo("body");
this.backdrop.click(this.hide.bind(this));this.wrapper=$('<div class="field-overlay labels-chooser" style="display:none"><div class="close-trigger"></div></div>');
$(".close-trigger",this.wrapper).click(this.hide.bind(this));this.inner.detach().appendTo(this.wrapper).css("display","block");
this.wrapper.appendTo("body")},updateLabels:function(){var b=this.labelsInput.getLabels();var a="(click to set)";if(b.length){a=b.join(", ")
}this.currentValue.text(a);this.values.empty();if(b.length){Array.each(b,function(d){var c=$('<option value="" selected="selected" />');
c.val(d);c.appendTo(this.values)},this)}},show:function(){this.wrapper.css({left:this.currentValue.offset().left,top:this.currentValue.offset().top});
this.backdrop.show();this.wrapper.show()},hide:function(){this.backdrop.hide();this.wrapper.hide()},destroy:function(){this.wrapper.remove();
this.backdrop.remove();this.labelsInput.destroy()}});Orb.createNamespace("DeskPRO.Agent.Ticket");DeskPRO.Agent.Ticket.ChangeManager=new Class({Implements:[Events],ticketPage:null,ticketId:null,updateUrl:null,mode:"single",oldValues:{},changes:{},initialize:function(a){this.ticketPage=a;
this.ticketId=a.getMetaData("ticket_id");this.updateUrl=a.getMetaData("saveActionsUrl")},propertyManagers:{},getPropertyManager:function(b,c){if(this.propertyManagers[b]){return this.propertyManagers[b]
}var a=null;switch(b){case"category_id":case"product_id":case"workflow_id":case"priority_id":a=new DeskPRO.Agent.Ticket.Property.StandardOption(this.ticketPage,{optionName:b});
break;case"agent_id":a=new DeskPRO.Agent.Ticket.Property.Agent(this.ticketPage);break;case"agent_team_id":a=new DeskPRO.Agent.Ticket.Property.AgentTeam(this.ticketPage);
break;case"department_id":a=new DeskPRO.Agent.Ticket.Property.Department(this.ticketPage);break;case"status":a=new DeskPRO.Agent.Ticket.Property.Status(this.ticketPage);
break;case"add_labels":a=new DeskPRO.Agent.Ticket.Property.Labels(this.ticketPage,{mode:"add"});break;case"remove_labels":a=new DeskPRO.Agent.Ticket.Property.Labels(this.ticketPage,{mode:"remove"});
break;case"flag":a=new DeskPRO.Agent.Ticket.Property.Flag(this.ticketPage);break;case"reply":a=new DeskPRO.Agent.Ticket.Property.Reply(this.ticketPage);
break;case"ticket_field":a=new DeskPRO.Agent.Ticket.Property.TicketField(this.ticketPage,{fieldId:c});break;case"is_hold":a=new DeskPRO.Agent.Ticket.Property.Hold(this.ticketPage);
break}if(a===null&&b.indexOf("_id")==-1){b+="_id";return this.getPropertyManager(b,c)}if(a===null){return null}this.propertyManagers[b]=a;
return a},hasChanges:function(){if(Object.getLength(this.changes)){return true}return false},addChange:function(b,c,a){if(b.isSameValue(c)){return
}this.mode="multi";this.changes[b.getName()]=[b,c];if(a){this.applyChangeForProperty(b,c)}},applyChangeForProperty:function(a,b){this.oldValues[a.getName()]=a.getValue();
a.setValue(b);if(this.mode=="multi"){a.highlightInterfaceElement()}},applyChanges:function(){Object.each(this.changes,function(c){var a=c[0];
var b=c[1];this.applyChangeForProperty(a,b)},this);this.fireEvent("changesApplied",{changes:this.changes})},hasChangedProperty:function(a){if(this.changes[a]){return true
}return false},revertChanges:function(){Object.each(this.changes,function(c){var b=c[0];var a=b.getName();if(this.oldValues[a]!==undefined){b.setValue(this.oldValues[a]);
b.unhighlightInterfaceElement()}b.changeReverted()},this);this.oldValues={};$(".change-on, .highlight-change-on",this.ticketPage.wrapper).removeClass("change-on").removeClass("highlight-change-on");
this.mode="single"},setInstantChange:function(b,d,e){if(this.mode=="multi"){this.addChange(b,d,true);return}b.setValue(d);
b.changePersisted();var a=[];this._addPropertyValueToData(a,b.getName(),b.getValue());this.fireEvent("changesApplied",{changes:[b,d]});
var c="saving-"+b.getName().replace(".","_");this.ticketPage.wrapper.addClass(c);(function(){this.ticketPage.wrapper.removeClass(c)
}).delay(650,this);if(this.updateUrl){$.ajax({type:"POST",url:this.updateUrl,data:a,dataType:"json",context:this,success:function(f){if(e){e(f)
}this.fireEvent("updateResult",[f])}})}},saveChanges:function(b,c){b=b||[];var a=[];Object.each(this.changes,function(g){var e=g[0];
var d=e.getName();if(d=="reply"){e.unhighlightInterfaceElement();return}var f="saving-"+d.replace(".","_");a.push(f);this.ticketPage.wrapper.addClass(f);
if(!e.isDisplayOnly()){this._addPropertyValueToData(b,e.getName(),e.getValue())}e.unhighlightInterfaceElement();e.changePersisted()
},this);this.mode="single";(function(){var d="";while(d=a.pop()){this.ticketPage.wrapper.removeClass(d)}}).delay(650,this);
if(this.updateUrl){$.ajax({type:"POST",url:this.updateUrl,data:b,dataType:"json",context:this,success:function(d){this.changes={};
this.oldValues={};if(d&&d.properties){Object.each(d,function(f,e){var g=this.getPropertyManager(e);g.setIncomingValue(f)},this)
}if(c){}this.fireEvent("updateResult",[d])}})}},_addPropertyValueToData:function(d,c,b){if(typeOf(b)=="array"){for(var a=0;
a<b.length;a++){var e=b[a];if(typeOf(e)=="object"&&e.full_name!==undefined){d.push({name:e.full_name,value:e.value})}else{if(typeOf(e)=="object"&&e.name!==undefined){d.push({name:"actions["+c+"]["+e.name+"]",value:e.value})
}else{d.push({name:"actions["+c+"][]",value:e})}}}}else{if(typeOf(b)=="object"){Object.each(b,function(g,f){d.push({name:"actions["+c+"]["+f+"]",value:g})
},this)}else{d.push({name:"actions["+c+"]",value:b})}}},setPropertyUpdated:function(a,b){if(typeOf(a)=="string"){a=this.getPropertyManager(a)
}a.setIncomingValue(b);a.pulseInterfaceElement()}});Orb.createNamespace("DeskPRO.Agent.Ticket.Property");DeskPRO.Agent.Ticket.Property.Abstract=new Class({Implements:[Events,Options],options:{},ticketPage:null,initialize:function(b,a){if(a){this.setOptions(a)
}this.ticketPage=b;this.init()},init:function(){},getName:function(){},isSameValue:function(a){if(this.getValue()==a){return true
}return false},getValue:function(){},setValue:function(a){},setIncomingValue:function(a){this.setValue(a)},getInterfaceElement:function(){return this._getInterfaceElement()
},_interfaceEl:null,_getInterfaceElement:function(){},pulseInterfaceElement:function(){this.getInterfaceElement().effect("highlight",1200)
},highlightInterfaceElement:function(){var b=this.getInterfaceElement();if(!b||!b.length){return}this.getInterfaceElement().addClass("change-on");
var a=b.parentsUntil(null,".display-item");if(a.length){a.addClass("highlight-change-on")}else{b.addClass("change-on")}},changePersisted:function(){},changeReverted:function(){},unhighlightInterfaceElement:function(){this.getInterfaceElement().removeClass("change-on")
},isDisplayOnly:function(){return false},isAdditionOnly:function(){return false}});Orb.createNamespace("DeskPRO.Agent.Ticket.Property");
DeskPRO.Agent.Ticket.Property.Agent=new Class({Extends:DeskPRO.Agent.Ticket.Property.Abstract,optionName:"agent_id",init:function(){},getName:function(){return this.optionName
},getValue:function(){return this.getFormEl().val()},setValue:function(c){this.getFormEl().val(c);if(c=="0"){c=0}var b=this.getInterfaceElement();
if(c==0){b.text("Unassigned");b.css("background-image","")}else{var a=DeskPRO_Window.getAgentInfo(c);b.text(a.name);b.css("background-image",a.pictureUrlSizable.replace("{SIZE}",20))
}if(c==DESKPRO_PERSON_ID){$(".assign-me",this.ticketPage.wrapper)}else{if(c==0){$(".assign-none",this.ticketPage.wrapper)
}}},_getInterfaceElement:function(){return $(".prop-agent-id:first",this.ticketPage.wrapper)},_formEl:null,getFormEl:function(){if(this._formEl!==null){return this._formEl
}this._formEl=$("input.agent_id:first",this.ticketPage.valueForm);return this._formEl}});Orb.createNamespace("DeskPRO.Agent.Ticket.Property");
DeskPRO.Agent.Ticket.Property.Department=new Class({Extends:DeskPRO.Agent.Ticket.Property.Abstract,optionName:"department_id",init:function(){},getName:function(){return"department_id"
},getValue:function(){return this.getFormEl().val()},setValue:function(c){this.getFormEl().val(c);if(c=="0"){c=0}var b=this.getInterfaceElement();
var a=DeskPRO_Window.getDisplayName("department_full",c);console.log(a);this.getInterfaceElement().text(a)},_getInterfaceElement:function(){return $(".label-department-id",this.ticketPage.wrapper)
},_formEl:null,getFormEl:function(){if(this._formEl!==null){return this._formEl}this._formEl=$("input.department_id:first",this.ticketPage.valueForm);
return this._formEl}});Orb.createNamespace("DeskPRO.Agent.Ticket.Property");DeskPRO.Agent.Ticket.Property.AgentTeam=new Class({Extends:DeskPRO.Agent.Ticket.Property.Abstract,optionName:"agent_team_id",init:function(){},getName:function(){return this.optionName
},getValue:function(){return this.getFormEl().val()},setValue:function(c){this.getFormEl().val(c);if(c=="0"){c=0}var b=this.getInterfaceElement();
if(c==0){b.text("Unassigned");b.css("background-image","")}else{var a=DeskPRO_Window.getTeamInfo(c);b.text(a.name);b.css("background-image",a.pictureUrlSizable.replace("{SIZE}",20))
}},_getInterfaceElement:function(){return $(".prop-agent-team-id:first",this.ticketPage.wrapper)},_formEl:null,getFormEl:function(){if(this._formEl!==null){return this._formEl
}this._formEl=$("input.agent_team_id:first",this.ticketPage.valueForm);return this._formEl}});Orb.createNamespace("DeskPRO.Agent.Ticket.Property");
DeskPRO.Agent.Ticket.Property.StandardOption=new Class({Extends:DeskPRO.Agent.Ticket.Property.Abstract,optionName:null,displayNameType:"standardOption",init:function(){var a=["category_id","product_id","priority_id","workflow_id"];
if(a.indexOf(this.options.optionName)==-1){throw"invalidOptionName:"+this.options.optionName}this.optionName=this.options.optionName;
switch(this.optionName){case"category_id":this.displayNameType="ticket_category_full";this.displayCaption="Category";break;
case"product_id":this.displayNameType="product";this.displayCaption="Product";break;case"priority_id":this.displayNameType="ticket_priority";
this.displayCaption="Priority";break;case"workflow_id":this.displayNameType="ticket_workflow";this.displayCaption="Workflow";
break}},getName:function(){return this.optionName},getValue:function(){return this.getFormEl().val()},setValue:function(d){this.getFormEl().val(d);
if(d=="0"){d=0}var c=this.getInterfaceElement();var b=null;if(c.data("picture-element")){b=$(c.data("picture-element"),c.parent());
if(!b.length){b=$(c.data("picture-element"),c.parent.parent());if(!b.length){b=null}}}if(d){var a=d;if(this.displayNameType){a=DeskPRO_Window.getDisplayName(this.displayNameType,d);
if(!a){a=d}}this.getInterfaceElement().removeClass("no-value").html(a);if(b){b.removeClass("no-value").show().attr("src",b.data("picture-url").replace("{value}",d))
}}else{this.getInterfaceElement().addClass("no-value").html(this.getInterfaceElement().data("no-value-label")||"None");if(b){b.addClass("no-value").hide()
}}var e=$(".prop-input-"+this.optionName,this.ticketPage.wrapper);e.val(d)},_getInterfaceElement:function(){return $(".prop-val."+this.optionName+":first",this.ticketPage.contentWrapper)
},_formEl:null,getFormEl:function(){if(this._formEl!==null){return this._formEl}this._formEl=$("input."+this.optionName+":first",this.ticketPage.valueForm);
return this._formEl}});Orb.createNamespace("DeskPRO.Agent.Ticket.Property");DeskPRO.Agent.Ticket.Property.Status=new Class({Extends:DeskPRO.Agent.Ticket.Property.Abstract,setValue:function(b){var a=false;
var d=b;if(b&&b.constructor.toString().indexOf("Array")!=-1){a=b[1].value;b=b[0].value;d=b}else{if(b.indexOf(".")!=-1){var c=b.split(".");
var b=c[0];var a=c[1];d=b+"_"+a;this.ticketPage.fireEvent("ticketHidden",[a])}}$(".page-header .set-status",this.ticketPage.wrapper).hide();
$(".page-header .set-status."+d,this.ticketPage.wrapper).show();console.log(d);$("input.status:first",this.ticketPage.valueForm).val(b);
$("input.hidden_status:first",this.ticketPage.valueForm).val(a)},getValue:function(){var a=[];a.push({full_name:"actions[status]",value:$("input.status:first",this.ticketPage.valueForm).val()});
a.push({full_name:"actions[hidden_status]",value:$("input.hidden_status:first",this.ticketPage.valueForm).val()});return a
},getName:function(){return"status"}});Orb.createNamespace("DeskPRO.Agent.Ticket.Property");DeskPRO.Agent.Ticket.Property.Reply=new Class({Extends:DeskPRO.Agent.Ticket.Property.Abstract,optionName:null,menuRepository:null,getName:function(){return"reply"
},getValue:function(){return this.ticketPage.getEl("replybox_txt").val()},highlightInterfaceElement:function(){return this.ticketPage.getEl("replybox").addClass("highlight-change-on")
},unhighlightInterfaceElement:function(){return this.ticketPage.getEl("replybox").removeClass("highlight-change-on")},setValue:function(a){this.getInterfaceElement().val(a)
},setIncomingValue:function(a){},_getInterfaceElement:function(){return this.ticketPage.getEl("replybox_txt")}});Orb.createNamespace("DeskPRO.Agent.Ticket.Property");
DeskPRO.Agent.Ticket.Property.TicketField=new Class({Extends:DeskPRO.Agent.Ticket.Property.Abstract,optionName:"",init:function(){this.optionName="ticket_field."+this.options.fieldId
},getName:function(){return this.optionName},getValue:function(){return this.getInterfaceElement().html()},setValue:function(a){this.getInterfaceElement().html(a)
},_getInterfaceElement:function(){return $(".show-fields .custom-field-"+this.options.fieldId+" .field-input",this.ticketPage.contentWrapper)
},isDisplayOnly:function(){return true}});Orb.createNamespace("DeskPRO.Agent.Ticket.Property");DeskPRO.Agent.Ticket.Property.Flag=new Class({Extends:DeskPRO.Agent.Ticket.Property.Abstract,optionName:"flag",init:function(){},getName:function(){return this.optionName
},getValue:function(){return $("li.on",this.ticketPage.getEl("flag_opt")).data("value")},setValue:function(a){console.log("set %o",a);
$("li",this.ticketPage.getEl("flag_opt")).removeClass("on");$("li.flag-"+a,this.ticketPage.getEl("flag_opt")).addClass("on")
}});Orb.createNamespace("DeskPRO.Agent.Ticket.Property");DeskPRO.Agent.Ticket.Property.Labels=new Class({Extends:DeskPRO.Agent.Ticket.Property.Abstract,optionName:"labels",mode:"add",init:function(){this.mode=this.options.mode
},getName:function(){return this.mode+"_"+this.optionName},getValue:function(){return this._values},_values:null,setValue:function(a){this._values=a;
if(this.mode=="add"){Array.each(a,function(b){this.ticketPage.labelsTagit.add(b,'<span class="new">'+b+"</span>")},this)}else{Array.each(a,function(c){var b=$('input[value="'+c+'"]',this.getInterfaceElement());
if(b.length){b=b.parent();li.hide()}},this)}},changePersisted:function(){if(!this._values){return}if(this.mode=="add"){$("li span.new",this.getInterfaceElement()).removeClass("new")
}else{$("li.pending-remove",this.getInterfaceElement()).remove()}this._values=null},changeReverted:function(){if(!this._values){return
}if(this.mode=="add"){$("li:has(span.new)",this.getInterfaceElement()).remove()}else{Array.each(values,function(b){var a=$('input[value="'+b+'"]',this.getInterfaceElement());
if(a.length){a=a.parent();li.show().addClass("pending-remove")}},this)}this._values=null},_getInterfaceElement:function(){return $("ul.tagit.ticket:first",this.ticketPage.contentWrapper)
}});Orb.createNamespace("DeskPRO.Agent.Ticket.Property");DeskPRO.Agent.Ticket.Property.Hold=new Class({Extends:DeskPRO.Agent.Ticket.Property.Abstract,optionName:"is_hold",init:function(){},getName:function(){return this.optionName
},getValue:function(){return this.getFormEl().val()},setValue:function(a){a=parseInt(a);$(".set-hold",this.ticketPage.wrapper).hide();
if(a){$(".set-hold.unhold",this.ticketPage.wrapper).show()}else{$(".set-hold.hold",this.ticketPage.wrapper).show()}},_formEl:null,getFormEl:function(){if(this._formEl!==null){return this._formEl
}this._formEl=$("input.is_hold:first",this.ticketPage.valueForm);return this._formEl}});Orb.createNamespace("DeskPRO.Agent.TicketList.MassActions");
DeskPRO.Agent.TicketList.MassActions.Widget=new Orb.Class({Implements:[Orb.Util.Events,Orb.Util.Options],initialize:function(c,b){this.page=c;
this.options={viewHandler:null,selectionBar:null,listWrapper:null,fetchPreviewUrl:null,triggerElement:null,templateElement:null,isListView:false};
this.setOptions(b);this.viewHandler=this.options.viewHandler;this.selectionBar=this.options.selectionBar||c.selectionBar;
this.fetchPreviewUrl=this.options.fetchPreviewUrl||c.meta.fetchResultsUrl;this.listWrapper=this.options.listWrapper||$(".list-listing",c.wrapper);
this.wrapper=this.options.templateElement||$("div.mass-actions-overlay-container",c.wrapper);this.backdropEls=null;this.countEl=$(".selected-tickets-count",this.getElement());
var a=null;if(this.options.triggerElement===null){a=$(".perform-actions-trigger",c.wrapper)}else{if(this.options.triggerElement){a=this.options.triggerElement
}}if(a){a.click((function(d){d.preventDefault();d.stopPropagation();this.open()}).bind(this))}},getElement:function(){return this.wrapper
},_initOverlay:function(){if(this._hasInit){return}this._hasInit=true;this.wrapper.detach().appendTo("body");this.wrapper.css("z-index","1000100");
this.wrapper.click(function(g){g.stopPropagation()});if(this.options.isListView){this.backdropEls=$('<div class="backdrop fade" />')
}else{var f=$('<div class="backdrop mass-actions" />');var e=$('<div class="backdrop mass-actions" />');var d=$('<div class="backdrop mass-actions" />');
this.backdropEls=$([f.get(0),e.get(0),d.get(0)])}this.backdropEls.css("z-index","1000010").hide().appendTo("body");this.backdropEls.click((function(g){g.stopPropagation();
this.close()}).bind(this));$("header .close-trigger",this.wrapper).click((function(g){g.stopPropagation();g.preventDefault();
this.close()}).bind(this));var c=DeskPRO_Window.util.getPlainTpl($(".radio-tpl",this.wrapper));var a={};$(":radio",this.wrapper).each(function(){var g=$(this).attr("name");
if(!a[g]){a[g]=[]}a[g].push(this)});var b=this;Object.each(a,function(g){var i=[];g=$(g);var h=function(){var k=$(this).data("bound-id");
var j=$("#"+k);if(j.is(":checked")){j.attr("checked",false);i.removeClass("radio-on")}else{j.attr("checked",true);i.removeClass("radio-on");
$(this).addClass("radio-on")}b.updatePreview()};g.each(function(){var l=$(this).parent();var k=$(".radio-title",l).text().trim();
var j=$(c);j.addClass($(this).data("attach-class"));$(".radio-title",j).text(k);if(!$(this).attr("id")){$(this).attr("id",Orb.getUniqueId())
}j.data("bound-id",$(this).attr("id"));j.click(h);l.hide();j.insertAfter(l);i.push(j.get(0))});i=$(i)});$("input, select, textarea",this.wrapper).change((function(){this.updatePreview()
}).bind(this));this.selectionBar.addEvent("checkChange",function(h,g,i){if(!this.isOpen()){return}this.updateCount(i);this.handleCheckChange(h,g)
},this);this.selectionBar.addEvent("checkAll",function(g){if(!this.isOpen()){return}this.updateCount(g);this.updatePreview()
},this);this.selectionBar.addEvent("checkNone",function(){if(!this.isOpen()){return}this.updateCount(0);this.clearPreview()
},this);$(".apply-macro-trigger",this.wrapper).click((function(g){g.preventDefault();g.stopPropagation();this.loadMacro($("select.macro",this.wrapper).val())
}).bind(this));$(".apply-actions",this.wrapper).click((function(g){this.apply();if(this.options.isListView){this.close()}}).bind(this))
},updateCount:function(a){if(a===undefined||a===null){a=this.selectionBar.getCount()}this.countEl.text(a)},getActionFormValues:function(a,b,c){a=a||[];
if(!c){c={}}c.actionsCount=0;$("input, select, textarea",this.wrapper).filter('[name^="actions["]').each(function(){var e=$(this).val().trim(),d=$(this).attr("name");
if($(this).is(":radio, :checkbox")){if(!$(this).is(":checked")){return}}if(e===""){return}if(!b&&d=="actions[reply]"){return
}a.push({name:d,value:e});c.actionsCount++});return a},apply:function(){var c,b=[];var a={checkedCount:0,actionsCount:0};
c=this.selectionBar.getCheckedFormValues("result_ids[]",null,a);this.selectionBar.getChecked().each(function(){b.push($(this).closest(".row-item").get(0))
});this.getActionFormValues(c,true,a);if(!a.checkedCount||!a.actionsCount){return}b=$(b);b.addClass("loading");$.ajax({url:BASE_URL+"agent/ticket-search/ajax-save-actions",type:"POST",data:c,dataType:"json",context:this,complete:function(){b.removeClass("loading")
},success:function(d){$(".preview-edit",this.listWrapper).removeClass("preview-edit");$(".preview-edit-hide",this.listWrapper).remove();
$(".row-item.changed",this.listWrapper).removeClass("changed");this.resetForm()}})},clearPreview:function(){$(".preview-edit",this.listWrapper).remove();
$(".preview-edit-hide",this.listWrapper).show().removeClass("preview-edit-hide")},updatePreview:function(e){if(this.runningAjax){this.runningAjax.abort();
this.runningAjax=null}var d,c=[];var a={checkedCount:0,actionsCount:0};if(!e){d=this.selectionBar.getCheckedFormValues("result_ids[]",null,a);
this.selectionBar.getChecked().each(function(){c.push($(this).closest(".row-item").get(0))})}else{d=[{name:"result_ids[]",value:e}];
a.checkedCount=1;c=[$(".ticket-"+e+".row-item").get(0)]}this.getActionFormValues(d,false,a);if(!a.checkedCount){return}c=$(c);
c.addClass("loading");if(this.options.isListView){d.push({view_type:"list"})}var b=$.ajax({url:BASE_URL+"agent/ticket-search/get-page",type:"POST",data:d,dataType:"html",context:this,complete:function(){c.removeClass("loading");
this.runningAjax=null},success:function(f){this.updatePreviewDisplay(f)}});if(!e){this.runningAjax=b}},updatePreviewDisplay:function(c){var b=$(c);
var a=this.listWrapper;$(".row-item",b).each(function(){var i=$(this).data("ticket-id");var h=$(".ticket-"+i+".row-item",a);
var j=$(".preview-edit",h);j.remove();var g=$(".top-row-right",this).addClass("preview-edit");var e=$(".extra-fields",this).addClass("preview-edit");
var f=$(".top-row-right",h).addClass("preview-edit-hide");var d=$(".extra-fields",h).addClass("preview-edit-hide");g.insertAfter(f);
e.insertAfter(d);f.hide();d.hide()})},handleCheckChange:function(b,a){var c=$(b).closest(".row-item");if(a){this.updatePreview(c.data("ticket-id"))
}else{$(".preview-edit",c).remove();$(".preview-edit-hide",c).show().removeClass("preview-edit-hide")}},resetForm:function(){$("input, select, textarea",this.wrapper).filter('[name^="actions["]').each(function(){if($(this).is(":radio, :checkbox")){$(this).attr("checked",false)
}else{if($(this).is("select")){$("option",this).attr("selected",false).first().selected("true")}else{if($(this).is("input, textarea")){$(this).val("")
}}}});$("button.radio.on",this.wrapper).removeClass("on")},updatePositions:function(){var g=$("#dp_content").offset();if(this.options.isListView){var e=$(window).width();
var f=$(window).height();var a=this.wrapper.outerWidth();this.wrapper.css({top:g.top+3,left:(e/2)-a,right:3,bottom:3})}else{this.wrapper.css({top:g.top+3,left:g.left+3,right:3,bottom:3})
}var d=269;var b=41;var c=g.left;if(!this.options.isListView){this.backdropEls.eq(0).css({top:0,width:d,bottom:0,left:0});
this.backdropEls.eq(1).css({top:0,height:b,width:c-d,left:d});this.backdropEls.eq(2).css({top:0,right:0,bottom:0,left:c})
}},loadMacro:function(a){a=parseInt(a);if(!a){return}$.ajax({url:BASE_URL+"agent/ticket-search/ajax-get-macro-actions",data:{macro_id:a},type:"GET",dataType:"json",context:this,success:function(b){console.log(b);
if(!b.macro_actions){return}Array.each(b.macro_actions,function(c){switch(c.type){case"agent":$('[name="actions[agent]"]',this.wrapper).val(c.options.agent);
break;case"agent_team":$('[name="actions[agent_team]"]',this.wrapper).val(c.options.agent_team);break;case"category":$('[name="actions[category]"]',this.wrapper).val(c.options.category);
break;case"department":$('[name="actions[department]"]',this.wrapper).val(c.options.department);break;case"product":$('[name="actions[product]"]',this.wrapper).val(c.options.product);
break;case"flag":$('[name="actions[flag]"]',this.wrapper).val(c.options.flag);break;case"priority":$('[name="actions[priority]"]',this.wrapper).val(c.options.priority);
break;case"urgency":break;case"urgency_set":break;case"workflow":$('[name="actions[workflow]"]',this.wrapper).val(c.options.workflow);
break;case"status":$("button.status.status-"+c.options.status,this.wrapper).click();break;case"reply":$('[name="actions[reply]"]',this.wrapper).val(c.options.reply_text);
break}},this)}})},isOpen:function(){if(!this._hasInit||!this.wrapper.is(".open")){return false}return true},open:function(){this._initOverlay();
this.updatePositions();this.wrapper.addClass("open");this.backdropEls.show();this.updateCount(null);this.wrapper.addClass("open");
this.updatePreview()},close:function(){if(!this.isOpen()){return false}this.wrapper.removeClass("open");this.backdropEls.hide();
this.fireEvent("closed",[this]);this.clearPreview()},destroy:function(){if(this._hasInit){this.wrapper.remove();this.backdropEls.remove()
}}});Orb.createNamespace("DeskPRO.Agent.TicketList");DeskPRO.Agent.TicketList.ChangeManager=new Class({Implements:[Events],ticketPage:null,hasChanges:false,changes:{},ticketIdsBatch:null,initialize:function(a){this.ticketPage=a
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
}});Orb.createNamespace("DeskPRO.Agent.TicketList.Property");DeskPRO.Agent.TicketList.Property.StandardOption=new Class({Extends:DeskPRO.Agent.TicketList.Property.Abstract,optionName:null,init:function(){var a=["department","category","product","priority","workflow","status","agent","agent_team"];
if(a.indexOf(this.options.optionName)==-1){throw"invalidOptionName:"+this.options.optionName}this.optionName=this.options.optionName;
switch(this.optionName){case"department":this.displayNameType="department_full";this.displayCaption="Department";break;case"category":this.displayNameType="ticket_category_full";
this.displayCaption="Category";break;case"product":this.displayNameType="product";this.displayCaption="Product";break;case"priority":this.displayNameType="ticket_priority";
this.displayCaption="Priority";break;case"workflow":this.displayNameType="ticket_workflow";this.displayCaption="Workflow";
break;case"status":this.displayNameType="status";this.displayCaption="Status";break;case"agent":this.displayNameType="agent";
this.displayCaption="Agent";break;case"agent_team":this.displayNameType="agent_team";this.displayCaption="Agent Team";break
}},getValue:function(){return this.getInterfaceElement().data("prop-value")},getName:function(){return this.optionName},setValue:function(b){if(b=="0"){b=0
}this.getInterfaceElement().data("prop-value",b);if(b){var a=b;if(this.displayNameType){a=DeskPRO_Window.getDisplayName(this.displayNameType,b);
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