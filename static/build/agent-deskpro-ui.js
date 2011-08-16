Orb.createNamespace("DeskPRO.UI");DeskPRO.UI.LabelsInput_Grouped={};DeskPRO.UI.LabelsInput=new Orb.Class({Implements:[Orb.Util.Options,Orb.Util.Events],initialize:function(c){this.options={list:null,fieldName:"labels",type:"",showMax:50};
this.setOptions(c);var b={enableBackspace:false,fieldName:this.options.fieldName,onchange:(function(e){this.fireEvent("change",[this.getLabels()])
}).bind(this)};var d=false;if(DeskPRO.UI.LabelsInput_Grouped[this.options.type]){d=DeskPRO.UI.LabelsInput_Grouped[this.options.type]
}else{if(window.DESKPRO_DATA_REGISTRY.labels){d=[];Object.each(window.DESKPRO_DATA_REGISTRY.labels,function(f,e){if(f.indexOf(this.options.type)!=-1){d.push(e)
}},this);DeskPRO.UI.LabelsInput_Grouped[this.options.type]=d}}if(d&&d.length){b.autocompleteOptions={source:d,minLength:0,delay:20};
b.focusShowAutocomplete=true}else{b.autocompleteOptions={source:BASE_URL+"/misc/ajax-labels/"+this.options.type}}var a=this.options.showMax;
b.autocompleteOptions.open=(function(f,h){var e=this.tagit.getInput();var g=$(e.autocomplete("widget"));var i=$("> li",g).slice(a);
i.remove()}).bind(this);this.tagit=$(this.options.list).tagit(b)},getLabels:function(){return this.tagit.getLabels()},getFormData:function(){return this.tagit.getFormData()
}});Orb.createNamespace("DeskPRO.UI");DeskPRO.UI.Overlay=new Orb.Class({Implements:[Orb.Util.Options,Orb.Util.Events],initialize:function(a){this.objectId=null,this.options={triggerElement:null,contentMethod:"element",contentElement:null,contentAjax:{url:"",type:"GET",dataType:"html"},iframeUrl:null,iframeId:false,maxHeight:700,maxWidth:900,destroyOnClose:false,customClassname:"",isModal:true,zIndex:100000,escapeClose:true,modalClickClose:true,objectGroup:"default",addClose:true};
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
}});Orb.createNamespace("DeskPRO.UI");DeskPRO.UI.Menu_Instances={};DeskPRO.UI.Menu=new Orb.Class({DisableParentCall:true,Implements:[Orb.Util.Options,Orb.Util.Events],initialize:function(a){this.options={triggerElement:null,customClassname:"",zIndex:1000000,menuElement:null,objectGroup:"default",subMenuConfig:null,initSubMenusNow:false,initMenuNow:false,parentMenu:null};
this.hasInit=false;this.elements={};this.openTriggerEvent=null;this.openedTime=null;this.cachePosInfo=null;this.subMenus=[];
this.openSubMenuId=null;this.parentMenu=null;this.objectId=Orb.uuid();if(a){this.setOptions(a)}if(this.options.parentMenu){this.parentMenu=this.options.parentMenu;
delete this.options.parentMenu}if(DeskPRO.UI.Menu_Instances[this.options.objectGroup]===undefined){DeskPRO.UI.Menu_Instances[this.options.objectGroup]={}
}DeskPRO.UI.Menu_Instances[this.options.objectGroup][this.objectId]=this;this._setupMenuElement();if(this.options.triggerElement){this.setupTriggerElement($(this.options.triggerElement))
}if(this.options.initMenuNow){this._initMenu()}},_setupMenuElement:function(){var e=$(this.options.menuElement);if(e.is("select")){var c=[];
c.push('<ul class="menu" style="display:none">');var g=null;var b=$("option",e);var h=false;b.each(function(j,l){l=$(l);var i=(l.next().text().indexOf("--")!==-1);
var k=(l.text().indexOf("--")!==-1);if(!g||l.is(":selected")){g=l.text()}if(j&&!k&&(i||h)){c.push('<li class="sep">')}if(i){h=true;
c.push('<li class="section-title">'+Orb.escapeHtml(l.text())+"</li>")}else{if(!k){h=false}c.push('<li data-value="'+l.val()+'">'+Orb.escapeHtml(l.text())+"</li>")
}});c.push("</ul>");var d=$(c.join("")).appendTo("body");this.options.menuElement=d;e.css({display:"none"});if(!this.options.triggerElement){var f=g;
if(!f.length){f="Choose..."}var a=this.options.triggerElement=$('<span class="menu-trigger">'+Orb.escapeHtml(f)+"</span>").insertAfter(e);
this.addEvent("itemClicked",function(j){var i=$(j.itemEl);var l=i.text().trim();if(!l.length){l="Choose..."}else{var k=$(j.itemEl).data("prefix");
if(k){l=k+l}}a.text(l)})}this.addEvent("itemClicked",function(j){var i=$(j.itemEl);var k=i.data("value");if(k!=e.val()){e.val(k);
e.change()}});if(a){e.change(function(){var i=$("option:selected",this);var k=i.text().trim();if(!k.length){k="Choose..."
}else{var j=$(this).data("prefix");if(j){k=j+k}}a.text(k)})}}},isOpen:function(){return this.isMenuOpen()},isMenuOpen:function(){if(!this.hasInit){return false
}return this.elements.wrapper.is(":visible")},getOpenTriggerEvent:function(){return this.openTriggerEvent},getOpenTriggerElement:function(){if(!this.openTriggerEvent){return null
}return this.openTriggerEvent.target},open:function(a){return this.openMenu(a)},openMenu:function(a){if(!this._initMenu()){return
}if(this.isMenuOpen()){return}if(!this.parentMenu){Object.each(DeskPRO.UI.Menu_Instances[this.options.objectGroup],function(m,l){if(m.isMenuOpen()){m.closeMenu()
}})}this.openTriggerEvent=a;if(a.stopPropagation){a.stopPropagation()}var i={menu:this,cancelOpen:false};if(a&&a.customEvents){a.customEvents.fireEvent("beforeMenuOpened",i)
}else{this.fireEvent("beforeMenuOpened",i)}if(i.cancelOpen){this.openTriggerEvent=null;return}if(!this.options.zIndex){this.options.zIndex=Orb.findHighestZindex()+1
}if(this.cachePosInfo&&this.openedTime&&this.parentMenu&&this.parentMenu.openedTime&&this.parentMenu.openedTime<=this.openedTime){var c=this.cachePosInfo.left;
var g=this.cachePosInfo.top}else{var b=this.elements.wrapperOuter.outerWidth();var j=this.elements.wrapperOuter.outerHeight();
var h=$(document).width();var e=$(document).height();if(this.parentMenu!==null&&this.parentMenu.isMenuOpen()){var f=this.options.parentMenuItem.offset().left+this.options.parentMenuItem.outerWidth()-4;
var d=this.options.parentMenuItem.offset().top;if(f+b>h){f=this.options.parentMenuItem.offset().left-b}}else{if(a.pageX){var f=a.pageX;
var d=a.pageY}else{var f=$(a.target).offset().top;var d=$(a.target).offset().left}}if(f+b<h){var c=f+4}else{var c=f-b-4}if(d+j<e){var g=d
}else{var g=d-j+4}if(g<0){g=5}this.cachePosInfo={left:c,top:g}}if(this.elements.shim){this.elements.shim.css({"z-index":this.options.zIndex+1,position:"absolute",top:0,right:0,bottom:0,left:0,background:"transparent"}).show()
}this.elements.wrapperOuter.css({"z-index":this.options.zIndex+2,position:"absolute",top:g,left:c});this.elements.wrapperOuter.show();
this.openedTime=new Date();if(a&&a.customEvents){a.customEvents.fireEvent("menuOpened",{menu:this})}else{this.fireEvent("menuOpened",{menu:this})
}},close:function(){return this.closeMenu(event)},closeMenu:function(){if(!this.isMenuOpen()){return false}var a={menu:this,cancelClose:false};
if(this.openTriggerEvent&&this.openTriggerEvent.customEvents){this.openTriggerEvent.customEvents.fireEvent("beforeMenuClosed",a)
}else{this.fireEvent("beforeMenuClosed",a)}if(a.cancelClose){return false}this._closeSubMenu();if(this.elements.shim){this.elements.shim.hide()
}if(this.parentMenu){this.elements.wrapperOuter.hide()}else{this.elements.wrapperOuter.fadeOut(200)}if(this.openTriggerEvent&&this.openTriggerEvent.customEvents){this.openTriggerEvent.customEvents.fireEvent("menuClosed",{menu:this})
}else{this.fireEvent("menuClosed",{menu:this})}this.openTriggerEvent=null;return true},_menuItemClicked:function(b){var a={menu:this,event:b,itemEl:b.currentTarget,cancelClose:false};
if($(a.itemEl).is(".sep, .disabled, .section-title")){return false}if($(a.itemEl).is(".elm, .sep, .section-title")){a.cancelClose=true
}if(this.openTriggerEvent&&this.openTriggerEvent.customEvents){this.openTriggerEvent.customEvents.fireEvent("itemClicked",a)
}else{this.fireEvent("itemClicked",a)}b.stopPropagation();if(this.parentMenu&&this.parentMenu.isMenuOpen()){this.parentMenu._menuItemClicked(b)
}if(a.cancelClose){return}this.closeMenu()},_menuItemMouseover:function(d){var c={menu:this,event:d,itemEl:d.currentTarget};
if(this.openTriggerEvent&&this.openTriggerEvent.customEvents){this.openTriggerEvent.customEvents.fireEvent("itemMouseover",c)
}else{this.fireEvent("itemMouseover",c)}d.stopPropagation();var b=$(c.itemEl);var e=b.data("submenu-id");if(this.openSubMenuId==e){return
}this._closeSubMenu();if(e===undefined){return}var a=this.subMenus[e];a._initMenu();a.openMenu(this.openTriggerEvent);this.openSubMenuId=e;
b.addClass("hover")},_closeSubMenu:function(){if(this.openSubMenuId!==null){this.subMenus[this.openSubMenuId].closeMenu();
this.subMenus[this.openSubMenuId].options.parentMenuItem.removeClass("hover");this.openSubMenuId=null}},_initMenu:function(){if(this.hasInit){return true
}this.hasInit=true;if(!this.parentMenu){this.elements.shim=$("<div />").hide().appendTo("body");this.elements.shim.click((function(c){if(this.closeMenu()){c.stopPropagation()
}}).bind(this))}this._initWrapperElements();this.elements.list=$(this.options.menuElement);this.elements.list.detach().show().appendTo(this.elements.wrapper);
if(this.options.subMenuConfig){var b=this.options.subMenuConfig}else{var b={}}$("li",this.elements.list[0]).live("click",this._menuItemClicked.bind(this));
var a=$("> li > ul.submenu",this.elements.list[0]);if(a.length){a.each((function(e,f){var d=$(f);f=$(d.parent());f.mouseover(this._menuItemMouseover.bind(this));
d.hide();var g=this.subMenus.length;f.addClass("with-submenu");f.data("submenu-id",g);b.parentMenu=this;b.subMenuId=g;b.parentMenuItem=f;
b.menuElement=d;b.zIndex=this.options.zIndex+1;var c=new DeskPRO.UI.Menu(b);this.subMenus.push(c);f.prepend($('<span class="arrow">&#x25B8;</span>'));
if(this.options.initSubMenusNow){c._initMenu()}}).bind(this))}this.fireEvent("menuInit",{menu:this});return true},_initWrapperElements:function(){this.elements.wrapperOuter=$('<div class="deskpro-menu-outer '+this.options.customClassname+'" style="display:none" />');
this.elements.wrapperOuter.appendTo("body");this.elements.wrapperInner=$('<div class="deskpro-menu-inner '+this.options.customClassname+'" />');
this.elements.wrapperInner.appendTo(this.elements.wrapperOuter);this.elements.wrapper=$('<div class="deskpro-menu '+this.options.customClassname+'">');
this.elements.wrapper.appendTo(this.elements.wrapperInner)},getListElement:function(){if(this.elements.list){return this.elements.list
}else{return this.options.menuElement}},setupTriggerElement:function(a){a=$(a);a.click((function(b){b.preventDefault();this.openMenu(b)
}).bind(this))},getWrapper:function(){return $(this.elements.wrapperOuter)},destroy:function(){if(this.elements&&this.elements.shim){this.elements.shim.remove()
}if(this.elements&&this.elements.wrapperOuter){this.elements.wrapperOuter.remove()}if(this.options.menuEl){this.options.menuEl.remove()
}delete DeskPRO.UI.Menu_Instances[this.options.objectGroup][this.objectId];Array.each(this.subMenus,function(a){a.menu.destroy()
});this.subMenus=[]}});Orb.createNamespace("DeskPRO.UI");DeskPRO.UI.SimpleTabs=new Orb.Class({Implements:[Orb.Util.Options,Orb.Util.Events],initialize:function(b){this.options={triggerElements:".tab-trigger",activeClassname:"on",context:document};
this.lastActiveTab=null;this.triggerEls=null;if(b){this.setOptions(b)}this.triggerEls=this.options.triggerElements;if(typeOf(this.triggerEls)=="string"){this.triggerEls=$(this.triggerEls,this.options.context)
}var a=this;this.triggerEls.click(function(d){a._handleTabClick(this,d)});var c=this.triggerEls.filter(".on:first");if(!c.length){c=this.triggerEls.first()
}this.activateTab(c)},addTriggerElement:function(b){var a=this;this.triggerEls.add(b);b.click(function(c){a._handleTabClick(this,c)
})},_handleTabClick:function(b,c){var a=$(b);this.activateTab(a,c)},activateTab:function(c,b){var a={event:b||null,tabEl:c,lastTabEl:this.lastActiveTab,manager:this,cancel:false};
this.fireEvent("beforeTabSwitch",a);if(a.cancel){return}delete a.cancel;if(this.lastActiveTab){this.lastActiveTab.removeClass(this.options.activeClassname);
this.getContentElFromTab(this.lastActiveTab).removeClass(this.options.activeClassname).hide();this.lastActiveTab=null}this.lastActiveTab=c;
this.lastActiveTab.addClass(this.options.activeClassname);a.tabContent=this.getContentElFromTab(this.lastActiveTab).addClass(this.options.activeClassname).show();
this.fireEvent("tabSwitch",a)},getContentElFromTab:function(b){if(!b.data("tab-for")){console.error("tab has no tab-for: %o",b);
console.trace();return $()}var a=$(b.data("tab-for"),this.options.context);if(a.length<1){console.error("no tab content exists for tab: %o",b);
console.trace()}return a}});