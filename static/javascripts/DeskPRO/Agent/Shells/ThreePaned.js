Orb.createNamespace('DeskPRO.Agent.Shells');

/**
 * The main three-paned interface has a nav pane on the left,
 * a list pane in the center, and then the content area on the right.
 *
 * The content area has a tabbed interface.
 */
DeskPRO.Agent.Shells.ThreePaned = new Class({
	el: null,
	htmlEl: null,
	
	tabManager: null,
	tabStrip: null,
	
	navPanePage: null,
	listPanePage: null,
	
	outerLayout: null,
	innerLayout: null,
	
	paneNavEl: null,
	paneListEl: null,
	paneContentEl: null,
	
	initialize: function() {
		this.el = $('#pane_shell');
		this.htmlEl = $(this.el).get(0);
		this.paneNavEl = $('#pane_nav');
		this.paneListEl = $('#pane_list');
		this.paneContentEl = $('#pane_content');
		
		//------------------------------
		// Set up the layout
		//------------------------------
		
		this.outerLayout = this.el.layout({
			west: {
				paneSelector: '#pane_nav',
				size: 165,
				spacing_open: 1,
				slidable: false
			},
			center: {
				paneSelector: '#pane_shell_inner'
			}
		});
		
		var west_is_closed = false;
		if (screen.width && screen.width < 1000) {
			west_is_closed = true;
		}
		
		this.innerLayout = $('#pane_shell_inner').layout({
			west: {
				paneSelector: '#pane_list',
				size: '45%',
				spacing_open: 1,
				initClosed: west_is_closed,
				slidable: false
			},
			center: {
				paneSelector: '#pane_content'
			}
		});
		
		//------------------------------
		// Set up the tab strip
		//------------------------------
		
		var self = this;
		$('#pane_tabs').sortable({
			'axis': 'x',
			'items': '> li',
			'tolerance': 'intersect',
			'containment': 'parent',
			'deactivate': function() {
				self.cancelClickActivate = true;
			}
		});
		
		this.tabStrip = $('#pane_tabs');
		// Mouseup because firefox doesnt respond to click
		// for middle clicks
		this.tabStrip.mouseup(this._tabStripClick.bind(this));
		//this.tabStrip.click(this._tabStripClick.bind(this));
		
		this.tabManager = new DeskPRO.Agent.TabManager('#page');
		var self = this;
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
		$('#pane_tabs_scroll_left').click(function() {
			scroll_el.animate({scrollLeft: '-=' + w}, 200);
		});
		$('#pane_tabs_scroll_right').click(function() {
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
	
	setNavPanePage: function(page) {
		
		if (this.navPanePage) {
			this.navPanePage.fireEvent('deactivate');
			this.navPanePage.fireEvent('destroy');
		}
		
		this.navPanePage = page;
		this.paneNavEl.empty().html(page.getHtml()).scrollTop(0);
		page.fireEvent('render', [this.paneNavEl]);
		page.fireEvent('activate');
	},
	
	setListPanePage: function(page) {
		
		if (this.listPanePage) {
			this.listPanePage.fireEvent('deactivate');
			this.listPanePage.fireEvent('destroy');
		}
		
		this.listPanePage = page;
		this.paneListEl.empty().html(page.getHtml()).scrollTop(0);
		page.fireEvent('render', [this.paneListEl]);
		page.fireEvent('activate');
	},
	
	addTabPage: function(page) {
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
	
	/**
	 * Add a new tab to the tab strip and manager.
	 *
	 * @param {String} id The unique ID we can use to identify the tab and its content.
	 * @param {String} title The title to put on the tab strip button
	 * @param {String} html The HTML for the page. It'll be lazy-rendered
	 */
	addTabHtml: function(id, title, html) {
		var data = {title: title, html: html};
		this.tabManager.addTab(id, data);
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