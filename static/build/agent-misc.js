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
}})}});Orb.createNamespace("DeskPRO.Form");DeskPRO.Form.RuleBuilder=new Class({Implements:Events,ruleTpl:null,typeSelectHtml:null,types:{},rowDestroy:{},initialize:function(b){this.ruleTpl=b;
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
Orb.createNamespace("DeskPRO.Agent");DeskPRO.Agent.Widgetable=new Class({widgets:[],initWidgets:function(a,b){b=b||{};Array.each(a,function(e){if(!e||typeOf(e)!="object"){return
}var f=Orb.getNamespacedObject(e.classname);var c=Object.merge(b,{__wrapperSelector:e.wrapperSelector},e.options||{});var d=new f(c);
if(typeOf(e.prefs)=="object"){d.userPrefs=e.prefs}this.widgets.push(d)},this)},initWidgetsDom:function(a){var a=a||document.body;
a=$(a);Array.each(this.widgets,function(c){var b=$(c.options.__wrapperSelector,a);if(!b.length){console.warn("Widget has no element wrapper: %o",c);
return}c.setWidgetElement(b)});if(this.fireEvent){this.fireEvent("allWidgetsReady")}},getWidgets:function(){return this.widgets
}});Orb.createNamespace("DeskPRO.Agent.Widget");DeskPRO.Agent.Widget.FindPerson=new Orb.Class({Implements:[Orb.Util.Options,Orb.Util.Events],initialize:function(a){this.options={};
this.setOptions(a)},_initOverlay:function(){if(this.overlay){return this.overlay}this.overlay=new DeskPRO.UI.Overlay({contentMethod:"ajax",contentAjax:{url:BASE_URL+"agent/people-search/quick-find"}});
this.overlay.addEvent("ajaxDone",this._initElements.bind(this));var a={findPerson:this,overlay:this.overlay};this.fireEvent("initOverlay",[a])
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
}}});Orb.createNamespace("DeskPRO.Agent.Widget");DeskPRO.Agent.Widget.AgentSelector=new Orb.Class({Implements:[Orb.Util.Options,Orb.Util.Events],initialize:function(a){this.options={agentList:null,multipleChoice:false,zIndex:1000001,startWith:[]};
if(a){this.setOptions(a)}this.previousSelection=""},_initWrapper:function(){if(this.wrapper){return}var b=$("li",this.options.agentList);
this.backdrop=$('<div class="backdrop"></div>').appendTo("body");this.backdrop.click(this.close.bind(this));this.wrapper=$('<div class="field-overlay agent-selector" style="display:none;"><div class="close-trigger"></div></div>');
$(".close-trigger",this.wrapper).click(this.close.bind(this));var a=$('<div class="with-scrollbar"><div class="scrollbar"><div class="track"><div class="thumb"><div class="end"></div></div></div></div><div class="scroll-viewport"><div class="scroll-content"></div></div></div>');
if(b.length>=10){this.filter=$('<div class="filter"><div class="input-wrap"><input type="text" value="" placeholder="Find an agent" /></div></div>').appendTo(this.wrapper);
$("input",this.filter).keyup(this.updateFilter.bind(this))}else{this.filter=null}var h=this.options.multipleChoice;var g=this.options.startWith;
var d=$("<ul />");b.each(function(){var p=$(this);var j=p.data("agent-id");var n=$("a:first",p).text();var l=$("img:first",p);
var q=$('<li class="agent-'+j+'" data-agent-id="'+j+'" />');if(l.length){var i=$('<div class="avatar" />');l.clone().appendTo(i);
i.appendTo(q)}var r=$('<div class="name" />');r.append("<a>"+Orb.escapeHtml(n)+"</a>");r.appendTo(q);var o="";if(g.indexOf(j+"")!==-1||g.indexOf(parseInt(j))!==-1){o='checked="checked"'
}var k=$('<div class="choice" />');if(h){var m=$('<input type="checkbox" name="agents[]" '+o+' value="'+j+'" class="agent-choice-'+j+'" />')
}else{var m=$('<input type="radio" name="agents[]" '+o+' value="'+j+'" class="agent-choice-'+j+'" />')}m.appendTo(k);k.appendTo(q);
q.append($('<br style="clear:left;height: 1px;overflow: hidden;"/>'));q.appendTo(d);q.click(function(s){s.stopPropagation();
if(!$(s.target).is("input")){m.click()}})});delete b;this.agentList=d;var c=this;$('input[type="checkbox"], input[type="radio"]').click(function(k){k.stopPropagation();
var l=$(this).val();var j=$(this).is(":checked");var i={agentSelector:c,agentId:l,checked:j,event:event};c.fireEvent("selectionClick",[i])
});d.appendTo($("div.scroll-content",a));a.appendTo(this.wrapper);this.listWrapper=a;this.wrapper.appendTo("body");var f=this.getSelection();
if(this.options.multipleChoice){f=f.join(",")}this.previousSelection=f;var e={agentSelector:this,wrapper:this.wrapper};this.fireEvent("initWrapper",[e])
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
$('input[type="checkbox"]:checked',this.agentList).each(function(){a.push($(this).val())});return a}else{var b=$('input[type="checkbox"]:checked:first',this.agentList).val();
return b}}});Orb.createNamespace("DeskPRO.Agent.RuleBuilder");DeskPRO.Agent.RuleBuilder.TermAbstract=new Orb.Class({Implements:[Orb.Util.Events,Orb.Util.Options],initialize:function(a){this.options={ruleBuilder:null,rowEl:null,rowId:null,opMenu:null};
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
})},show:function(){if(this.opInput.val()=="between"){this.dateWrap.addClass("two")}else{this.dateWrap.removeClass("two")
}this.wrapper.css({left:this.currentValue.offset().left,top:this.currentValue.offset().top});this.backdrop.show();this.wrapper.show()
},updateStatus:function(){if(this.opInput.val()=="between"){var b=this.date1Widget.datepicker("getDate");var a=this.date2Widget.datepicker("getDate");
var c="";if(b){c=$.datepicker.formatDate("M d, yy",b)}else{c="(click to set)"}c+=" and ";if(a){c+=$.datepicker.formatDate("M d, yy",a)
}else{c+="(click to set)"}}else{var b=this.date1Widget.datepicker("getDate");var c="";if(b){c=$.datepicker.formatDate("M d, yy",b)
}else{c="(click to set)"}}this.currentValue.text(c)},hide:function(){this.backdrop.hide();this.wrapper.hide()},destroy:function(){this.wrapper.remove();
this.backdrop.remove()}});Orb.createNamespace("DeskPRO.Agent.Ticket");DeskPRO.Agent.Ticket.ChangeManager=new Class({Implements:[Events],ticketPage:null,ticketId:null,updateUrl:null,mode:"single",oldValues:{},changes:{},initialize:function(a){this.ticketPage=a;
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