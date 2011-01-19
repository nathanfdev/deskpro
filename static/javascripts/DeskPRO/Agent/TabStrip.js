Orb.createNamespace('DeskPRO.Agent');

DeskPRO.Agent.TabStrip = new Class({
	Implements: [Events, Options],
	
	tabStrip: null,
	tabManager: null,
	
	initialize: function(tabStrip, tabManager) {
		this.tabStrip = tabStrip;
		this.tabManager = tabManager;
		
		var self = this;
		
		this.tabStrip.sortable({
			'axis': 'x',
			'items': '> li',
			'tolerance': 'intersect',
			'containment': 'parent',
			'deactivate': function() {
				self.cancelClickActivate = true;
			}
		});
		
		// Mouseup because firefox doesnt respond to click
		// for middle clicks
		this.tabStrip.mouseup(this._tabStripClick.bind(this));
		//this.tabStrip.click(this._tabStripClick.bind(this));

		this.tabManager.addEvents({
			addTab: this._onTabAdd.bind(this),
			activateTab: this._onTabActivate.bind(this),
			deactivateTab: this._onTabDeactivate.bind(this),
			removeTab: this._onTabRemove.bind(this)
		});
		
		// Clicking the scrollers scroll left and right
		var w = (self.tabStrip.parent().width() / 2);
		w = w - (w / 4);

		scroll_el = this.tabStrip.parent();
		$('.tabs_scroll_left').click(function() {
			scroll_el.animate({scrollLeft: '-=' + w}, 200);
		});
		$('.tabs_scroll_right').click(function() {
			scroll_el.animate({scrollLeft: '+=' + w}, 200);
		});
		
		// Scroll wheel should scroll this baby horizontally
		this.tabStrip.parent().mousewheel(function(ev, delta) {
			if (delta > 0) {
				scroll_el.animate({scrollLeft: '+=' + w}, 100);
			} else {
				scroll_el.animate({scrollLeft: '-=' + w}, 100);
			}
		});
	},
	
	
	getTabs: function() {
		return this.tabManager.getTabs();
	},
	
	activateTabById: function(tabId) {
		this.tabManager.activateTab(tabId);
	},
	
	removeTabById: function(tabId) {
		this.tabManager.removeTab(tabId);
	},
	
	findTabByPage: function(page) {
		var tabId = false;
		Object.each(this.tabManager.getTabs(), function(v, k) {
			if (v.page == page) {
				tabId = k;
				return false;
			}
		});
		
		return tabId;
	},
	
	findTabByRouteUrl: function(routeUrl) {
		var tabId = false;
		Object.each(this.tabManager.getTabs(), function(tab, tab_id) {
			if (tab.page.getMetaData('routeUrl') == routeUrl) {
				tabId = tab_id;
				return false;
			}
		});
		
		return tabId;
	},
	
	addTab: function(page) {
		this.tabManager.addTab(Orb.uuid(), {
			html: page.getHtml(),
			page: page,
			title: page.getMetaData('title', 'Untitled'),
			callback_render: function(data, container, tabManager) {
				container = $(container);
				page.fireEvent('render', [container]);
			},
			callback_remove_content: function(data, container, tabManager) {
				page.fireEvent('destroy');
			},
			callback_activate: function() {
				page.fireEvent('activate');
			},
			callback_deactivate: function() {
				page.fireEvent('deactivate');
			}
		});
	},
	
	
	
	resizeTabListWidth: function() {
		var w = 0;
		$('> li', this.tabStrip).each(function() {
			w += $(this).outerWidth();	
		});
		
		if (w < this.tabStrip.parent().width()) {
			w = this.tabStrip.parent().width();
		}
		
		this.tabStrip.css({width: w});
		
		// See if we need to be showing the navigator
		if (this.tabStrip.width() > this.tabStrip.parent().width()) {
			this.tabStrip.parent().addClass('with-scroller');
			w += 30; // for scroll indicators :)
			this.tabStrip.css({width: w});
		} else {
			this.tabStrip.parent().removeClass('with-scroller');
		}
	},
	
	
	cancelClickActivate: false,
	_tabStripClick: function(event) {
		
		if (this.cancelClickActivate) {
			this.cancelClickActivate = false;
			return;
		}
		
		var el_click = $(event.target);

		if (el_click.parent().is('li.tab')) {
			var el = el_click.parent();
		} else {
			var el = el_click.parentsUntil('li.tab');
			if (el.length) {
				el = el.parent(); // jquery doesnt include the actual parent in parentsUntil
			}
		}
		
		// If its not a tab, we can just ignore the event
		if (!el.is('li.tab')) {
			return;
		}
		
		// If the clicked thing was the close button, or if its a middle-click...
		if (el_click.parent().is('.close') || event.which == 2 || event.isDbl) {
			this.tabManager.removeTab(el.data('tab-id'));
			return;
		}
		
		// Otherwise activate the tab
		this.tabManager.activateTab(el.data('tab-id'));
	},
	
	_onTabAdd: function(tabData) {
		tabData.btnId = Orb.getUniqueId('tab_');
		
		var html = '<li id="'+tabData.btnId+'" data-tab-id="'+tabData.id+'" class="tab';
			if (tabData.page.TYPENAME != 'basic') {
				html += ' icon icon-' + tabData.page.TYPENAME;
			}
			html += '" title="' + tabData.title + '">';
		
			html += '<div class="title"><span>'+tabData.title+'</span></div>';
			html += '<div class="close"><span /></div>';
		html += '</li>';
		
		var li = $(html);
		
		li.appendTo(this.tabStrip);
		this.resizeTabListWidth();
		
		// Add tooltip
		//$(li).tipTip({defaultPosition: 'bottom'});
	},
	
	_onTabDeactivate: function(tabData, container, isActivating) {
		// If a tab was removed, the onmouseout was never fired
		// so the tooltip saying its title might still appear
		$('#tiptip_holder').clearQueue().hide();
	},
	
	_onTabActivate: function(tabData) {
		$('li', this.tabStrip).removeClass('tab-active');
		$('#' + tabData.btnId, this.tabStrip).addClass('tab-active');
	},
	
	_onTabRemove: function(tabData) {
		$('#' + tabData.btnId).remove();
		
		$('#tiptip_holder').clearQueue().hide();
		
		this.resizeTabListWidth();
	}
});