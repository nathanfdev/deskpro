Orb.createNamespace("DeskPRO.UI");DeskPRO.UI.LabelsInput_Grouped={};DeskPRO.UI.LabelsInput=new Orb.Class({Implements:[Orb.Util.Options,Orb.Util.Events],initialize:function(d){this.options={list:null,fieldName:"labels",type:"",showMax:50};
this.setOptions(d);var b={enableBackspace:false,fieldName:this.options.fieldName,onchange:(function(f){this.fireEvent("change",[this.getLabels()])
}).bind(this)};var e=false;if(DeskPRO.UI.LabelsInput_Grouped[this.options.type]){e=DeskPRO.UI.LabelsInput_Grouped[this.options.type]
}else{if(window.DESKPRO_DATA_REGISTRY.labels){e=[];Object.each(window.DESKPRO_DATA_REGISTRY.labels,function(g,f){if(g.indexOf(this.options.type)!=-1){e.push(f)
}},this);DeskPRO.UI.LabelsInput_Grouped[this.options.type]=e}}if(e&&e.length){b.autocompleteOptions={source:e,minLength:0,delay:20};
b.focusShowAutocomplete=true}else{b.autocompleteOptions={source:BASE_URL+"/misc/ajax-labels/"+this.options.type}}var a=this.options.showMax;
b.autocompleteOptions.open=(function(g,i){var f=this.tagit.getInput();var h=$(f.autocomplete("widget"));var j=$("> li",h).slice(a);
j.remove()}).bind(this);this.tagit=$(this.options.list).tagit(b);var c=$(this.options.list).data("label-route");if(c){$(this.options.list).click(function(i){if($(i.target).is(".close")){return
}if($(i.target).is("li")){var g=$(i.target)}else{var g=$(i.target).closest("li")}if(g.is(".tagit-new")){return}var f=$("input",g).val();
var h=c.replace(/\{LABEL\}/g,f,c);DeskPRO_Window.runPageRoute(h)})}},getLabels:function(){return this.tagit.getLabels()},getFormData:function(){return this.tagit.getFormData()
}});Orb.createNamespace("DeskPRO.UI");DeskPRO.UI.Overlay=new Orb.Class({Implements:[Orb.Util.Options,Orb.Util.Events],initialize:function(a){this.objectId=null,this.options={triggerElement:null,contentMethod:"element",contentElement:null,contentAjax:{url:"",type:"GET",dataType:"html"},iframeUrl:null,iframeId:false,maxHeight:700,maxWidth:900,destroyOnClose:false,customClassname:"",isModal:true,zIndex:1000000,escapeClose:true,modalClickClose:true,objectGroup:"default",addClose:true};
this.isThisDestroyed=false;this.hasInit=false;this.hasSentAjax=false;this.elements={};if(a){this.setOptions(a)}if(this.options.triggerElement){this.setupTriggerElement($(this.options.triggerElement))
}if(this.options.escapeClose){$(document).keydown((function(b){if(b.which==27){this.closeOverlay()}}).bind(this))}},isOpen:function(){return this.isOverlayOpen()
},isOverlayOpen:function(){if(!this.hasInit){return false}return this.elements.wrapper.is(":visible")},open:function(){return this.openOverlay()
},openOverlay:function(){if(!this.initOverlay()){return}if(this.isOverlayOpen()){return}var e={overlay:this,cancel:false};
this.fireEvent("beforeOverlayOpened",e);if(e.cancel){return}if(!this.options.zIndex){this.options.zIndex=Orb.findHighestZindex()+1
}this.elements.modal.css({"z-index":this.options.zIndex,position:"absolute",top:0,right:0,bottom:0,left:0});this.elements.modal.fadeIn(200);
if(this.options.contentMethod=="iframe"){var j=$(window).width()-250;var d=$(window).height()-150;if(j>this.options.maxWidth){j=this.options.maxWidth
}if(d>this.options.maxHeight){d=this.options.maxHeight}$("iframe:first",this.elements.wrapper).css({width:j,height:d});var i=($(window).width()-this.elements.wrapperOuter.outerWidth())/2;
var g=($(window).height()-this.elements.wrapperOuter.outerHeight())/2;this.elements.wrapperOuter.css({left:i,top:g})}else{var j=this.elements.wrapperOuter.outerWidth();
var c=$(window).width();var f=(c/2)-(j/2);var d=this.elements.wrapperOuter.outerHeight();var a=$(window).height();var b=(a/2)-(d/2);
this.elements.wrapperOuter.css({top:b,left:f})}this.elements.wrapperOuter.css({"z-index":this.options.zIndex+1,position:"absolute",left:f});
this.elements.wrapperOuter.fadeIn(450,(function(){this.fireEvent("overlayOpened",{overlay:this})}).bind(this))},close:function(){return this.closeOverlay()
},closeOverlay:function(){if(!this.isOverlayOpen()){return}var a={overlay:this,cancelClose:false};this.fireEvent("beforeOverlayClosed",a);
if(a.cancelClose){return}this.elements.modal.fadeOut(450);this.elements.wrapperOuter.fadeOut(200);this.fireEvent("overlayClosed",{overlay:this});
if(this.options.destroyOnClose){this.destroy()}},initOverlay:function(){if(this.hasInit){return true}if(this.options.isModal){this.elements.modal=$('<div class="deskpro-overlay-overlay '+this.options.customClassname+'" style="display:none" />');
this.elements.modal.appendTo("body");if(this.options.modalClickClose){this.elements.modal.click((function(){this.closeOverlay()
}).bind(this))}}this.elements.wrapperOuter=$('<div class="deskpro-overlay-outer '+this.options.customClassname+'" style="display:none" />');
this.elements.wrapperOuter.appendTo("body");this.elements.wrapper=$('<div class="deskpro-overlay '+this.options.customClassname+'">');
this.elements.wrapper.appendTo(this.elements.wrapperOuter);switch(this.options.contentMethod){case"element":var c=$(this.options.contentElement);
this._setContent(c);this.hasInit=true;return true;break;case"ajax":if(this.hasSentAjax){return false}this.hasSentAjax=true;
var b=Object.merge(this.options.contentAjax,{success:this._handleAjaxSuccess.bind(this)});$.ajax(b);this.fireEvent("ajaxStart",{overlay:this});
return false;break;case"iframe":var a="iframe_"+Orb.uuid();var c=$('<iframe name="'+a+'" src="'+this.options.iframeUrl+'"></iframe>');
if(this.options.iframeId){c.attr("id",this.options.iframeId)}this._setContent(c);this.hasInit=true;this.elements.wrapper.addClass("no-pad").addClass("iframe");
return true;break}console.error("Unknown content method: %s",this.options.contentMethod);return false},_handleAjaxSuccess:function(c){var d=$(c);
if(d.length!=1){var a=$("<div />");a.append(d)}else{var a=d}a.show();this._setContent(a);this.hasInit=true;var b={overlay:this,ajaxData:c};
this.fireEvent("ajaxDone",b);this.openOverlay()},_setContent:function(b){this.elements.wrapper.empty();b.detach().appendTo(this.elements.wrapper);
b.show();if(!$("div.overlay-content:first",b).length){var a=b;var b=$('<div class="overlay-content" />');a.wrap(b)}if(this.options.addClose){$("div.overlay-content:first",this.elements.wrapper).prepend('<a class="close-overlay close-trigger">Close</a>')
}$(".overlay-close-trigger, .close-trigger",this.elements.wrapper).click((function(c){c.preventDefault();this.closeOverlay()
}).bind(this));if(!$(".overlay-footer:first",b).length){$("div.overlay-content:first",b).addClass("no-footer")}this.fireEvent("contentSet",{overlay:this,contentEl:b,wrapperEl:this.elements.wrapper})
},setContent:function(a){this._setContent(a)},setupTriggerElement:function(b){b=$(b);var a=(function(c){this.openOverlay();
c.preventDefault()}).bind(this);if(b.is(".dbl-click-trigger")){b.dblclick(a)}else{b.click(a)}},getWrapper:function(){return $(this.elements.wrapperOuter)
},destroy:function(){this.fireEvent("beforeDestroy",[this]);if(this.elements.wrapperOuter){this.elements.wrapperOuter.remove()
}if(this.elements.modal){this.elements.modal.remove()}this.isThisDestroyed=true;this.fireEvent("destroyed",[this])},isDestroyed:function(){return this.isThisDestroyed
}});Orb.createNamespace("DeskPRO.UI");DeskPRO.UI.OptionBox=new Orb.Class({DisableParentCall:true,Implements:[Orb.Util.Options,Orb.Util.Events],initialize:function(a){this.options={element:null,trigger:null};
this.setOptions(a);this.el=this.options.element;if(this.options.trigger){$(this.options.trigger).click(this.open.bind(this))
}},getElement:function(a){if(!a||a=="element"){this.el}else{if(a=="backdrop"){return this.backdrop}}},_init:function(){var a=this;
if(this._hasInit){return}this._hasInit=true;this.backdrop=$('<div class="backdrop" />').hide().appendTo("body");if(!this.el.parent().is("body")){this.el.detach().appendTo("body")
}this.el.click(function(b){b.stopPropagation()});this.backdrop.click(function(b){b.stopPropagation();a.close()});$(":checkbox",this.el).change(function(){a.clickCheckbox($(this))
});$("section",this.el).each(function(){var b=$("ul :checkbox",this).length;$(this).data("total-count",b);if($(this).data("section-name")){$(this).addClass($(this).data("section-name"))
}});$("header .all-check",this.el).click(function(){var b=a._findSection($(this));if($(this).is(":checked")){$("ul :checkbox",b).attr("checked",true)
}else{$("ul :checkbox",b).attr("checked",false)}a.updateCountEls(b)});$("header input.filter-box",this.el).keyup(function(){a.updateFilter($(this))
}).change(function(){a.updateFilter($(this))});this.fireEvent("init",[this])},clickCheckbox:function(a){var b=this._findSection(a);
this.updateCountEls(b);this.fireEvent("checked",[this])},updateCountEls:function(b){var a=$("ul :checkbox:checked",b).length;
var c=$(".selected-count",b);if(a){$(".num",c).text(a);c.show()}else{c.hide()}if(a==b.data("total-count")){$("header .all-check",b).attr("checked",true)
}else{$("header .all-check",b).attr("checked",false)}},getCount:function(a){if(typeof a=="string"){a=$("section."+a,this.el)
}return parseInt($(".selected-count .num",a).text()||0)},getSelected:function(a){if(typeof a=="string"){a=$("section."+a,this.el)
}var b=[];$("li input:checked",a).each(function(){b.push($(this).val())});return b},getAllSelected:function(){var a={};$("section",this.el).each(function(){var b=$(this).data("section-name");
a[b]=self.getSelected($(this))});return a},_findSection:function(a){return a.closest("section")},updateFilter:function(d){var b=d.val().trim().toLowerCase();
var c=this._findSection(d);var a=$("li",c);if(!b){a.show();return}a.each(function(){var e=$("label",this).text().toLowerCase();
if(e.indexOf(b)!==-1){$(this).show()}else{$(this).hide()}})},open:function(f){this._init();var g=$(window).width();var b=$(window).height();
var e=$(f.target).offset().top;var c=$(f.target).offset().left;var a=this.el.width()+5;var d=this.el.height()+5;this.el.show();
this.backdrop.show();if(c+a>g){c=c-a}if(e+d>b){e=e-d}this.el.css({top:e,left:c});this.el.addClass("open");this.fireEvent("open",[this])
},close:function(){if(!this.isOpen()){return}this.el.hide().removeClass("open");this.backdrop.hide();this.fireEvent("close",[this])
},isOpen:function(){if(this._hasInit&&this.el.is(".open")){return true}return false},destroy:function(){if(this._hasInit){this.el.remove();
this.backdrop.remove()}}});Orb.createNamespace("DeskPRO.UI");DeskPRO.UI.Menu_Instances={};DeskPRO.UI.Menu=new Orb.Class({DisableParentCall:true,Implements:[Orb.Util.Options,Orb.Util.Events],initialize:function(a){this.options={triggerElement:null,customClassname:"",zIndex:1000000,menuElement:null,objectGroup:"default",subMenuConfig:null,initSubMenusNow:false,initMenuNow:false,parentMenu:null};
this.hasInit=false;this.elements={};this.openTriggerEvent=null;this.openedTime=null;this.cachePosInfo=null;this.subMenus=[];
this.openSubMenuId=null;this.parentMenu=null;this.objectId=Orb.uuid();if(a){this.setOptions(a)}if(this.options.parentMenu){this.parentMenu=this.options.parentMenu;
delete this.options.parentMenu}if(DeskPRO.UI.Menu_Instances[this.options.objectGroup]===undefined){DeskPRO.UI.Menu_Instances[this.options.objectGroup]={}
}DeskPRO.UI.Menu_Instances[this.options.objectGroup][this.objectId]=this;this._setupMenuElement();if(this.options.triggerElement){this.setupTriggerElement($(this.options.triggerElement))
}if(this.options.initMenuNow){this._initMenu()}},_setupMenuElement:function(){var e=$(this.options.menuElement);if(e.data("menu-flag")&&e.data("menu-flag").indexOf("copy-menu")!==-1){e=e.clone();
if(e.attr("id")){e.attr("id",e.attr("id")+"_"+Orb.uuid())}}if(e.is("select")){var c=[];c.push('<ul class="menu" style="display:none">');
var g=null;var b=$("option",e);var h=false;b.each(function(j,l){l=$(l);var i=(l.next().text().indexOf("--")!==-1);var k=(l.text().indexOf("--")!==-1);
if(!g||l.is(":selected")){g=l.text()}if(j&&!k&&(i||h)){c.push('<li class="sep">')}if(i){h=true;c.push('<li class="section-title">'+Orb.escapeHtml(l.text())+"</li>")
}else{if(!k){h=false}c.push('<li data-value="'+l.val()+'">'+Orb.escapeHtml(l.text())+"</li>")}});c.push("</ul>");var d=$(c.join("")).appendTo("body");
this.options.menuElement=d;e.css({display:"none"});if(!this.options.triggerElement){var f=g;if(!f.length){f="Choose..."}var a=this.options.triggerElement=$('<span class="menu-trigger">'+Orb.escapeHtml(f)+"</span>").insertAfter(e);
this.addEvent("itemClicked",function(j){var i=$(j.itemEl);var l=i.text().trim();if(!l.length){l="Choose..."}else{var k=$(j.itemEl).data("prefix");
if(k){l=k+l}}a.text(l)})}this.addEvent("itemClicked",function(j){var i=$(j.itemEl);var k=i.data("value");if(k!=e.val()){e.val(k);
e.change()}});if(a){e.change(function(){var i=$("option:selected",this);var k=i.text().trim();if(!k.length){k="Choose..."
}else{var j=$(this).data("prefix");if(j){k=j+k}}a.text(k)})}}},isOpen:function(){return this.isMenuOpen()},isMenuOpen:function(){if(!this.hasInit){return false
}return this.elements.wrapper.is(":visible")},getOpenTriggerEvent:function(){return this.openTriggerEvent},getOpenTriggerElement:function(){if(!this.openTriggerEvent){return null
}return this.openTriggerEvent.target},open:function(a){return this.openMenu(a)},openMenu:function(a){if(!this._initMenu()){return
}if(this.isMenuOpen()){return}if(!this.parentMenu){Object.each(DeskPRO.UI.Menu_Instances[this.options.objectGroup],function(m,l){if(m.isMenuOpen()){m.closeMenu()
}})}this.openTriggerEvent=a;if(a&&a.stopPropagation){a.stopPropagation()}var j={menu:this,cancelOpen:false};if(a&&a.customEvents){a.customEvents.fireEvent("beforeMenuOpened",j)
}if(!j.noFireEvent){this.fireEvent("beforeMenuOpened",j)}if(j.cancelOpen){this.openTriggerEvent=null;return}if(!this.options.zIndex){this.options.zIndex=Orb.findHighestZindex()+1
}if(this.cachePosInfo&&this.openedTime&&this.parentMenu&&this.parentMenu.openedTime&&this.parentMenu.openedTime<=this.openedTime){var c=this.cachePosInfo.left;
var g=this.cachePosInfo.top;var i=this.cachePosInfo.point}else{var b=this.elements.wrapperOuter.outerWidth();var k=this.elements.wrapperOuter.outerHeight();
var h=$(document).width();var e=$(document).height();if(this.parentMenu!==null&&this.parentMenu.isMenuOpen()){var f=this.options.parentMenuItem.offset().left+this.options.parentMenuItem.outerWidth()-4;
var d=this.options.parentMenuItem.offset().top;if(f+b>h){f=this.options.parentMenuItem.offset().left-b}}else{if(a.target&&!$(a.target).is(".with-menu-click-position")){var f=$(a.target).offset().left+($(a.target).width()/2);
var d=$(a.target).offset().top+($(a.target).outerHeight())+2}else{if(a.pageX){var f=a.pageX;var d=a.pageY}else{var f=$(a.target).offset().left;
var d=$(a.target).offset().top}}}var i=true;if(f+b<h){var c=f}else{var c=h-b-4;i=false}if(d+k<e){var g=d}else{var g=e-k-4;
i=false}if(g<0){g=5}this.cachePosInfo={left:c,top:g,point:i}}if(this.elements.shim){this.elements.shim.css({"z-index":this.options.zIndex+1,position:"absolute",top:0,right:0,bottom:0,left:0,background:"transparent"}).show()
}if(i){this.elements.wrapperOuter.addClass("with-point")}else{this.elements.wrapperOuter.removeClass("with-point")}this.elements.wrapperOuter.css({"z-index":this.options.zIndex+2,position:"absolute",top:g,left:c});
this.elements.wrapperOuter.show();this.openedTime=new Date();if(a&&a.customEvents){a.customEvents.fireEvent("menuOpened",{menu:this})
}if(!j.noFireEvent){this.fireEvent("menuOpened",{menu:this})}},close:function(){return this.closeMenu()},closeMenu:function(){if(!this.isMenuOpen()){return false
}var a={menu:this,cancelClose:false};if(this.openTriggerEvent&&this.openTriggerEvent.customEvents){this.openTriggerEvent.customEvents.fireEvent("beforeMenuClosed",a)
}if(!a.noFireEvent){this.fireEvent("beforeMenuClosed",a)}if(a.cancelClose){return false}this._closeSubMenu();if(this.elements.shim){this.elements.shim.hide()
}if(this.parentMenu){this.elements.wrapperOuter.hide()}else{this.elements.wrapperOuter.fadeOut(200)}if(this.openTriggerEvent&&this.openTriggerEvent.customEvents){this.openTriggerEvent.customEvents.fireEvent("menuClosed",{menu:this})
}if(!a.noFireEvent){this.fireEvent("menuClosed",{menu:this})}this.openTriggerEvent=null;return true},_menuItemClicked:function(b){var a={menu:this,event:b,itemEl:b.currentTarget,cancelClose:false};
if($(a.itemEl).is(".sep, .disabled, .section-title")){return false}if($(a.itemEl).is(".elm, .sep, .section-title")){a.cancelClose=true
}if(this.openTriggerEvent&&this.openTriggerEvent.customEvents){this.openTriggerEvent.customEvents.fireEvent("itemClicked",a)
}if(!a.noFireEvent){this.fireEvent("itemClicked",a)}b.stopPropagation();if(this.parentMenu&&this.parentMenu.isMenuOpen()){this.parentMenu._menuItemClicked(b)
}if(a.cancelClose){return}this.closeMenu()},_menuItemMouseover:function(d){var c={menu:this,event:d,itemEl:d.currentTarget};
if(this.openTriggerEvent&&this.openTriggerEvent.customEvents){this.openTriggerEvent.customEvents.fireEvent("itemMouseover",c)
}if(!c.noFireEvent){this.fireEvent("itemMouseover",c)}d.stopPropagation();var b=$(c.itemEl);var e=b.data("submenu-id");if(this.openSubMenuId==e){return
}this._closeSubMenu();if(e===undefined){return}var a=this.subMenus[e];a._initMenu();a.openMenu(this.openTriggerEvent);this.openSubMenuId=e;
b.addClass("hover")},_closeSubMenu:function(){if(this.openSubMenuId!==null){this.subMenus[this.openSubMenuId].closeMenu();
this.subMenus[this.openSubMenuId].options.parentMenuItem.removeClass("hover");this.openSubMenuId=null}},_initMenu:function(){if(this.hasInit){return true
}this.hasInit=true;if(!this.parentMenu){this.elements.shim=$("<div />").hide().appendTo("body");this.elements.shim.click((function(c){if(this.closeMenu()){c.stopPropagation()
}}).bind(this))}this._initWrapperElements();this.elements.list=$(this.options.menuElement);this.elements.list.detach().show().appendTo(this.elements.wrapper);
if(this.options.subMenuConfig){var b=this.options.subMenuConfig}else{var b={}}$("li",this.elements.list[0]).live("click",this._menuItemClicked.bind(this));
$("> li[data-submenu-selector]",this.elements.list[0]).each((function(d,e){var c=$($(e).data("submenu-selector")).first();
if(c.length){c=c.clone();c.data("menu-flag","");c.attr("menu-flag","");c.attr("id","");c.addClass("submenu");if($(e).data("submenu-add-action")){c.data("action",$(e).data("submenu-add-action")).attr("data-action",$(e).data("submenu-add-action"))
}c.appendTo(e);$(e).data("submenu-selector","").attr("submenu-selector","")}}).bind(this));var a=$("> li > ul.submenu",this.elements.list[0]);
if(a.length){a.each((function(e,f){var d=$(f);f=$(d.parent());f.mouseover(this._menuItemMouseover.bind(this));d.hide();var g=this.subMenus.length;
f.addClass("with-submenu");f.data("submenu-id",g);b.parentMenu=this;b.subMenuId=g;b.parentMenuItem=f;b.menuElement=d;b.zIndex=this.options.zIndex+1;
var c=new DeskPRO.UI.Menu(b);this.subMenus.push(c);f.prepend($('<span class="arrow">&#x25B8;</span>'));if(this.options.initSubMenusNow){c._initMenu()
}}).bind(this))}this.fireEvent("menuInit",{menu:this});return true},_initWrapperElements:function(){this.elements.wrapperOuter=$('<div class="deskpro-menu-outer '+this.options.customClassname+'" style="display:none" />');
this.elements.wrapperOuter.appendTo("body");this.elements.wrapperInner=$('<div class="deskpro-menu-inner '+this.options.customClassname+'" />');
this.elements.wrapperInner.appendTo(this.elements.wrapperOuter);this.elements.wrapper=$('<div class="deskpro-menu '+this.options.customClassname+'">');
this.elements.wrapper.appendTo(this.elements.wrapperInner)},getListElement:function(){if(this.elements.list){return this.elements.list
}else{return this.options.menuElement}},setupTriggerElement:function(a){a=$(a);a.click((function(b){b.preventDefault();this.openMenu(b)
}).bind(this))},getWrapper:function(){return $(this.elements.wrapperOuter)},destroy:function(){if(this.elements&&this.elements.shim){this.elements.shim.remove()
}if(this.elements&&this.elements.wrapperOuter){this.elements.wrapperOuter.remove()}if(this.options.menuEl){this.options.menuEl.remove()
}delete DeskPRO.UI.Menu_Instances[this.options.objectGroup][this.objectId];Array.each(this.subMenus,function(a){a.menu.destroy()
});this.subMenus=[]}});Orb.createNamespace("DeskPRO.UI");DeskPRO.UI.SimpleTabs=new Orb.Class({Implements:[Orb.Util.Options,Orb.Util.Events],initialize:function(b){this.options={triggerElements:".tab-trigger",activeClassname:"on",context:document,autoSelectFirst:true};
this.lastActiveTab=null;this.triggerEls=null;if(b){this.setOptions(b)}this.triggerEls=this.options.triggerElements;if(typeOf(this.triggerEls)=="string"){this.triggerEls=$(this.triggerEls,this.options.context)
}var a=this;this.triggerEls.click(function(d){d.cancel=false;d.tabEl=$(this);a.fireEvent("tabClick",[d]);if(!d.cancel){a._handleTabClick(this,d)
}});if(this.options.autoSelectFirst){var c=this.triggerEls.filter(".on:first");if(!c.length){c=this.triggerEls.first()}this.activateTab(c)
}},addTriggerElement:function(b){var a=this;this.triggerEls.add(b);b.click(function(c){c.cancel=false;c.tabEl=$(this);a.fireEvent("tabClick",[c]);
if(!c.cancel){a._handleTabClick(this,c)}})},_handleTabClick:function(b,c){var a=$(b);this.activateTab(a,c)},activateTab:function(c,b){var a={event:b||null,tabEl:c,lastTabEl:this.lastActiveTab,manager:this,cancel:false};
this.fireEvent("beforeTabSwitch",a);if(a.cancel){return}delete a.cancel;if(this.lastActiveTab){this.lastActiveTab.removeClass(this.options.activeClassname);
this.getContentElFromTab(this.lastActiveTab).removeClass(this.options.activeClassname).hide();this.lastActiveTab=null}this.lastActiveTab=c;
this.lastActiveTab.addClass(this.options.activeClassname);a.tabContent=this.getContentElFromTab(this.lastActiveTab).addClass(this.options.activeClassname).show();
this.fireEvent("tabSwitch",a)},getContentElFromTab:function(b){if(!b.data("tab-for")){console.error("tab has no tab-for: %o",b);
console.trace();return $()}var a=$(b.data("tab-for"),this.options.context);if(a.length<1){console.error("no tab content exists for tab: %o",b);
console.trace()}return a}});Orb.createNamespace("DeskPRO.Agent.UI");DeskPRO.UI.DateChooser=new Orb.Class({Implements:[Orb.Util.Events,Orb.Util.Options],initialize:function(a){this.options={rowEl:null};
if(a){this.setOptions(a)}this.rowEl=$(this.options.rowEl)},initValues:function(){var b=null,a=null;b=this.date1Input.val();
if(b){a=new Date(b*1000);this.date1Widget.datepicker("setDate",a)}b=this.date2Input.val();if(b){a=new Date(b*1000);this.date2Widget.datepicker("setDate",a)
}this.updateStatus()},_initUi:function(){if(this._hasInit){return}this._hasInit=true;this.opInput=$(".op",this.rowEl);this.date1Input=$("input.date1-input",this.rowEl);
this.date2Input=$("input.date2-input",this.rowEl);this.date1Display=$("input.date1-display",this.rowEl);this.date2Display=$("input.date2-display",this.rowEl);
this.currentValue=$(".display-value",this.rowEl);this.dateWrap=$(".date-wrap",this.rowEl);this.backdrop=$('<div class="backdrop" style="display: none"></div>');
this.backdrop.appendTo("body");this.backdrop.click(this.hide.bind(this));this.wrapper=$('<div class="field-overlay" style="display:none"><div class="close-trigger"></div></div>');
$(".close-trigger",this.wrapper).click(this.hide.bind(this));this.dateWrap.detach().appendTo(this.wrapper).css("display","block");
this.wrapper.appendTo("body");this.date1=$(".date1",this.dateWrap);this.date2=$(".date2",this.dateWrap);var a=this;this.date1Widget=$(".widget",this.date1).datepicker({dateFormat:"M d, yy",onSelect:function(d,c){a.date1Input.val(a.date1Widget.datepicker("getDate").getTime()/1000);
a.date1Display.val(d);a.updateStatus()}});this.date2Widget=$(".widget",this.date2).datepicker({dateFormat:"M d, yy",onSelect:function(d,c){a.date2Input.val(a.date2Widget.datepicker("getDate").getTime()/1000);
a.date2Display.val(d);a.updateStatus()}});var b=function(d){var e=strtotime(d.val());if(!e){return null}var c=new Date(e*1000);
return c};this.date1Display.change(function(){var c=b($(this));if(!c){$(this).val("");return}a.date1Widget.datepicker("setDate",c)
});this.date2Display.change(function(){var c=b($(this));if(!c){$(this).val("");return}a.date2Widget.datepicker("setDate",c)
});$(".switcher",this.date1).click((function(){var d=$(".date",this.date1);var c=$(".relative",this.date1);if(d.is(":visible")){d.hide();
c.show()}else{c.hide();d.show()}}).bind(this));$(".switcher",this.date2).click((function(){var d=$(".date",this.date2);var c=$(".relative",this.date2);
if(d.is(":visible")){d.hide();c.show()}else{c.hide();d.show()}}).bind(this))},open:function(){this._initUi();if(this.opInput.val()=="between"){this.dateWrap.addClass("two")
}else{this.dateWrap.removeClass("two")}this.wrapper.css({left:this.currentValue.offset().left,top:this.currentValue.offset().top});
this.backdrop.show();this.wrapper.show()},updateStatus:function(){var e="",c="",b="";var a=$(".relative1",this.date1);var g=$(".relative2",this.date2);
if(a.is(":visible")){$(".date1-relative-input",this.rowEl).val($(".relative1-input",this.date1).val());$(".date1-relative-type",this.rowEl).val($(".relative1-type",this.date1).val());
this.date1Input.val("");if($(".relative1-input",this.date1).val().trim().length){e=$(".relative1-input",this.date1).val()+" "+$(".relative1-type",this.date1).val()+" ago"
}}else{var f=this.date1Widget.datepicker("getDate");if(f){e=$.datepicker.formatDate("M d, yy",f)}}if(g.is(":visible")){$(".date2-relative-input",this.rowEl).val($(".relative2-input",this.date2).val());
$(".date2-relative-type",this.rowEl).val($(".relative2-type",this.date2).val());this.date2Input.val("");if($(".relative2-input",this.date2).val().trim().length){c=$(".relative2-input",this.date2).val()+" "+$(".relative2-type",this.date2).val()+" ago"
}}else{var d=this.date2Widget.datepicker("getDate");if(d){c=$.datepicker.formatDate("M d, yy",d)}}if(!e.length){e="(click to set)"
}if(!c.length){e="(click to set)"}if(this.opInput.val()=="between"){b=e+" and "+c}else{b=e}this.currentValue.text(b)},hide:function(){this.updateStatus();
this.backdrop.hide();this.wrapper.hide()},destroy:function(){this.wrapper.remove();this.backdrop.remove()}});Orb.createNamespace("DeskPRO.Agent.UI");
DeskPRO.UI.CatListEditor=new Orb.Class({Implements:[Orb.Util.Events,Orb.Util.Options],initialize:function(c){this.options={listEl:null,itemSelector:"li",subListSelector:"> ul",titleSelector:".title-edit:first",dataId:"category-id",newItemTplSelector:null,editorBaseId:""};
this.setOptions(c);var a=this;var d=$(this.options.listEl);this.list=d;var b=$(this.options.itemSelector,this.list);this._initLisCollection(b);
d.delegate(".sub-toggle","click",function(){$(this).parent().parent().toggleClass("sub-expanded")})},_initLisCollection:function(b){var a=this;
var c=this.list;b.addClass("dp-cat-li").wrapInner('<div class="item-wrap dp-cat-item" />').prepend('<div class="dp-cat-dropzone" />');
$(".dp-cat-item "+this.options.subListSelector,b).each(function(){var d=$(this).parent().parent();$(this).detach().appendTo(d)
});$(".dp-cat-item, .dp-cat-dropzone",b).droppable({accept:"li.dp-cat-li",tolerance:"pointer",drop:function(h,g){var f=false;
var d=$(this).parent();var i=!$(this).is(".dp-cat-dropzone");if(i&&d.children("ul").length==0){d.append("<ul/>")}if(i){d.addClass("sub-expanded").addClass("has-children").children("ul").append(g.draggable)
}else{d.before(g.draggable)}f=true;$("li.dp-li-open").not(":has(li:not(.ui-draggable-dragging))").removeClass("sub-expanded");
d.find(".dp-cat-item, .dp-cat-dropzone").removeClass("dp-cat-over");$("li.has-children",c).each(function(){var e=$(this);
if(!$("> ul > li:not(.ui-draggable-dragging):first",e).length){e.removeClass("has-children")}});a.fireEvent("reordered",[g.draggable,this]);
if(f){console.log("structed");window.setTimeout(function(){a.fireEvent("restructured",[g.draggable,this])},200)}},over:function(){$(this).addClass("dp-cat-over");
if($(this).is(".dp-cat-dropzone")){$(".dp-cat-item:first",$(this).parent()).removeClass("dp-cat-over")}},out:function(){$(this).filter(".dp-cat-item, .dp-cat-dropzone").removeClass("dp-cat-over")
}});b.draggable({handle:"> .dp-cat-item",opacity:0.5,addClasses:false,helper:"clone",zIndex:100})},getOrder:function(){var a=this.options.dataId;
var b=[];$("li.dp-cat-li",this.list).each(function(){var c=$(this).data(a);if(c){b.push(c)}});return b},getStructure:function(a){a=a||this.list;
var b={};this._getStructure(a,b);return b},_getStructure:function(c,d){var a=this;var b=this.options.dataId;var f=this.options.subListSelector;
var e=0;if(c.parent().is("li.dp-cat-li")){e=c.parent().data(b)}$("> li.dp-cat-li",c).each(function(){var h=$(this).data(b);
d[h]=e;var g=$(f,this);if(g.length){a._getStructure(g,d)}})},isTitleEditing:function(){return this.list.is(".title-editing")
},showEditTitles:function(){if(this.isTitleEditing()){return}this.list.addClass("title-editing");var a=this;$(".dp-cat-item",this.list).each(function(){a._enableEditable($(this))
});this.fireEvent("titlesActivated",[this])},endEditTitles:function(){if(!this.isTitleEditing()){return}var a=this;var b={};
$("input.dp-cat-input",this.list).each(function(){var d=$(this).parent();var c=d.parent().data(a.options.dataId);var e=a._disableEditable(d);
if(c){b[c]=e}});this.list.removeClass("title-editing");this.fireEvent("titlesUpdated",[b,this])},_enableEditable:function(b){var a=$(this.options.titleSelector,b);
var d=a.text().trim();var c=$('<input type="text" class="dp-cat-input" />');c.val(d);a.hide();c.insertAfter(a)},_disableEditable:function(c){var b=$(this.options.titleSelector,c);
var a=$("input.dp-cat-input",c).first();var d=a.val().trim();a.remove();b.text(d).show();return d},addNew:function(a){var c=this;
var d=$(this.options.newItemTplSelector).get(0).innerHTML;var a=$(d);var e=$("li.dp-cat-li:first",this.list);if(e.length){a.insertBefore(e)
}else{a.appendTo(this.list)}this._initLisCollection(a);this._enableEditable($(".dp-cat-item",a));this.fireEvent("newAddEditable",[a,b,this]);
var b=$("input.dp-cat-input",a);var f=function(){c.fireEvent("newAdded",[a,b,c]);c._disableEditable($(".dp-cat-item",a))};
b.blur(f).keypress(function(g){if(g.which==13){f()}});var b=$("input:first",a);b.focus().select()},showEditor:function(a){this._initEditor();
var b=a.offset();var c=a.width()-10;var d=$(".title-edit:first",a).text().trim();$("input.title",this.editTab).val(d);b.top-=5;
this.editTab.css({left:b.left,top:b.top,width:c});this.editTabBk.css({left:b.left+c-4,top:b.top+1});this.edit.css({left:b.left+c,top:b.top-10});
this.editBack.show();this.editTab.show();this.edit.show();this.editTabBk.show();this.edit.data("editing-li",a)},closeOpenEditor:function(){var a=this.edit.data("editing-li");
if(a){var f=$("input.title",this.editTab).val().trim();var c=$(".title-edit:first",a);var d=c.text().trim();var b=a.data(this.options.dataId);
if(f!=d){c.text(f);var e={};e[b]=f;this.fireEvent("titlesUpdated",[e,this])}}this.editBack.hide();this.editTab.hide();this.edit.hide().data("editing-li",0);
this.editTabBk.hide()},_initEditor:function(){if(this._editorHasInit){return}this._editorHasInit=true;this.editBack=$("#"+this.options.editorBaseId+"cat_editor_backdrop").detach().appendTo("body");
this.editTab=$("#"+this.options.editorBaseId+"cat_editor_tab").detach().appendTo("body");this.edit=$("#"+this.options.editorBaseId+"cat_editor").detach().appendTo("body");
this.editTabBk=$("#"+this.options.editorBaseId+"cat_editor_shadowbreak").detach().appendTo("body");$(".close-trigger",this.edit).click(this.closeOpenEditor.bind(this));
this.edit.click(function(a){a.stopPropagation()});this.editTab.click(function(a){a.stopPropagation()});this.editBack.click(this.closeOpenEditor.bind(this))
},destroy:function(){if(this.editBack){this.editBack.remove();this.editTab.remove();this.edit.remove();this.editTabBk.remove()
}}});