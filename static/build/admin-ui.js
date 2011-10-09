Orb.createNamespace("DeskPRO.Admin");DeskPRO.Admin.Window=new Orb.Class({Extends:DeskPRO.BasicWindow,init:function(){this.util={showSavePuff:function(a){var b=$('<div class="load-puff" style="display: none; opacity: 0" />');
b.appendTo("body");var e=a.offset();b.css({top:e.top+15,left:e.left+a.width()-4});var d=e.top-5;var c=e.top-15;b.show();b.animate({top:d,opacity:1},200,"swing",function(){window.setTimeout(function(){b.animate({top:c,opacity:0},200,"swing",function(){b.remove()
})},225)})}}},initPage:function(){var a=this;this.menuEls=$("#menus_container > div").addClass("header-menu").each(function(){$(this).detach().appendTo("body")
});$("#menus_container").remove();this.menuTriggerEls=$("#dp_admin_nav li[data-menu]");$("#dp_admin_nav").delegate("li[data-menu]","click",function(b){b.preventDefault();
a.openHeaderMenu($(this))});$('input[type="checkbox"].onoff-slider').checkbox({empty:ASSETS_BASE_URL+"/vendor/jquery/jquery-checkbox/empty.png"});
$("table.with-reorderable").each(function(){var b=$(this);b.data("table-reorder",new DeskPRO.Admin.TableReorder(b))});$("#DP-InterfaceSwitcher > .DP-adminSwitch > .adminSwitcher").click(function(e){e.preventDefault();
e.stopPropagation();var g=$(this).offset();var b=$(this).outerWidth();var d=$(this).outerHeight();var f=$("#interfacesToggle");
f.hide().detach().appendTo("body");f.css({top:g.top,left:g.left});f.show();var c=$('<div class="backdrop" />').appendTo("body");
c.click(function(){f.hide();c.remove()})});this.initFeatures();if(typeof window.DeskPRO_Window_Init=="function"){window.DeskPRO_Window_Init()
}},openHeaderMenu:function(c){if(!this.headerMenuBackdrop){this.headerMenuBackdrop=$('<div class="backdrop" />').appendTo("body").hide();
this.headerMenuBackdrop.click(this.closeHeaderMenu.bind(this))}this.menuTriggerEls.removeClass("open");this.menuEls.hide();
var d=c.offset();var b=c.outerHeight();var a=$(c.data("menu"));c.addClass("open");a.css({top:d.top+b,left:d.left});a.show();
c.show();this.headerMenuBackdrop.show()},closeHeaderMenu:function(){this.menuTriggerEls.removeClass("open");var a=this.menuEls.filter(":visible");
a.fadeOut("fast");this.headerMenuBackdrop.hide()},dismissHelpMessage:function(b){b=$(b);var a=b.data("message-id");b.remove();
if(!a){return}$.ajax({dataType:"json",url:BASE_URL+"agent/misc/dismiss-help-message/"+escape(a),type:"GET"})},initFeatures:function(b){var a=this;
DeskPRO.ElementHandler_Exec();$(".timeago").timeago()}});Orb.createNamespace("DeskPRO.Admin");DeskPRO.Admin.PopoutWindow=new Class({Extends:DeskPRO.Admin.Window,initialize:function(){$("body").layout({applyDefaultStyles:false})
}});Orb.createNamespace("DeskPRO.Admin.PageHandler");DeskPRO.Admin.PageHandler.Basic=new Class({Implements:[Events],meta:{},contextEl:null,options:{},messageBroker:null,initialize:function(b,a){if(b){this.contextEl=$(b)
}else{this.contextEl=$(document.body)}a=a||{};this.options=a;var c=this.getOpenerDeskPRO();if(c){this.messageBroker=c.getMessageBroker()
}else{if(window.DeskPRO_Window){this.messageBroker=window.DeskPRO_Window.getMessageBroker()}else{if(window.DeskPRO_Page){this.messageBroker=window.DeskPRO_Page.getMessageBroker()
}else{this.messageBroker=new DeskPRO.MessageBroker()}}}},getMessageBroker:function(){return this.messageBroker},initPage:function(){},initPopoutTriggers:function(b){if(!b){b=this.contextEl
}var a=this;$(".popout-trigger",b).click(function(h){var e=$(this);h.preventDefault();var d=e.attr("href");if(!d){d=e.data("href")
}var g=700;var f=900;var i=false;if(e.data("width")){f=e.data("width")}if(e.data("height")){g=e.data("height")}if(e.data("opener-id")){i=e.data("opener-id")
}var c=new DeskPRO.UI.Overlay({contentMethod:"iframe",iframeUrl:d,iframeId:i,destroyOnClose:true,maxWidth:f,maxHeight:g});
c.openOverlay();c.addEvent("destroyed",function(){delete c;a._openedOverlay=null});a._openedOverlay=c})},_openedOverlay:null,setMetaData:function(a,b){if(b===undefined&&typeOf(a)=="object"){this.meta=Object.merge(this.meta,a)
}else{this.meta[a]=b}},getAllMetaData:function(){return this.meta},getMetaData:function(b,a){if(a===undefined){a=null}if(this.meta[b]===undefined){return a
}return this.meta[b]},handleListChange:function(c){var b=$("ul.item-list:first");var a=$("li."+c.typename+"-"+c[c.typename+"_id"]);
var d=$(c.row_html);this.initPopoutTriggers(d);if(a.length){a.replaceWith(d)}else{b.prepend(d)}},getOpenerDeskPRO:function(b){var a="DeskPRO_Page";
var d="DeskPRO_Window";var c=null;if(window.parent&&window.parent[a]){c=window.parent[a]}else{if(window.opener&&window.opener[a]){c=window.opener[a]
}else{if(window.parent&&window.parent[d]){c=window.parent[d]}else{if(window.opener&&window.opener[d]){c=window.opener[d]}}}}return c
},closeThisPopout:function(){if(this.getThisOverlay()){this.getThisOverlay().closeOverlay()}},getThisOverlay:function(){if(window.parent&&window.parent.DeskPRO_Page&&window.parent.DeskPRO_Page._openedOverlay){return window.parent.DeskPRO_Page._openedOverlay
}return null}});Orb.createNamespace("DeskPRO.Admin");DeskPRO.Admin.TableReorder=new Orb.Class({initialize:function(b){var a=this;
this.table=b;this.updateUrl=this.table.data("reorder-save-url");this.table.sortable({items:"tbody",handle:"tr.depth-0",placeholder:{element:function(){return $('<tbody class="placeholder"><tr><td colspan="100">&nbsp;</td></tr></tbody>')
},update:function(){return}},helper:function(e,d){var c=a.table.clone(false);c.empty();c.append(d.clone());c.addClass("dragging");
$("tr td:not(.title)",c).remove();c.css("width",300);return c}});$("tbody",this.table).each(function(){var c=$(this);c.sortable({items:"tr.depth-1",placeholder:{element:function(){return $('<tr class="placeholder"><td colspan="100">&nbsp;</td></tr>')
},update:function(){return}},helper:function(f,e){var d=a.table.clone(false);d.empty();d.append(e.clone());d.addClass("dragging");
$("tr td:not(.title)",d).remove();d.css("width",300);return d}})})}});