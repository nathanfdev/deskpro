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
	
	initialize: function() {
		this.el = $('#pane_shell');
		this.htmlEl = $(this.el).get(0);
		
		//------------------------------
		// Set up the layout
		//------------------------------
		
		this.el.layout({
			west: {
				paneSelector: '#pane_nav',
				size: 185,
				spacing_open: 2
			},
			center: {
				paneSelector: '#pane_shell_inner'
			}
		});
		
		$('#pane_shell_inner').layout({
			west: {
				paneSelector: '#pane_list',
				size: '45%',
				spacing_open: 2
			},
			center: {
				paneSelector: '#pane_content'
			}
		});
		
		//------------------------------
		// Set up the tab strip
		//------------------------------
		
		this.tabStrip = $('#pane_tabs');
		this.tabStrip.click(this._tabStripClick.bind(this));
		
		this.tabManager = new DeskPRO.Agent.TabManager('#page');
		var self = this;
		this.tabManager.addEvents({
			addTab: this._onTabAdd.bind(this),
			activateTab: this._onTabActivate.bind(this),
			deactivateTab: this._onTabDeactivate.bind(this),
			removeTab: this._onTabRemove.bind(this)
		});
	},
	
	setNavPanePage: function(page) {
		this.navPanePage = page;
		$('#pane_nav').html(page.getHtml());
		page.initPage($('#pane_nav'));
	},
	
	setListPanePage: function(page) {
		this.listPanePage = page;
		$('#pane_list').html(page.getHtml());
		page.initPage($('#pane_list'));
	},
	
	addTabPage: function(page) {
		this.tabManager.addTab(Orb.uuid(), {
			html: page.getHtml(),
			page: page,
			title: page.getMetaData('title', 'Untitled'),
			callback_render: function(data, container, tabManager) {
				page.initPage(container);
			},
			callback_remove_content: function(data, container, tabManager) {
				page.destroyPage(container);
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
	
	_tabStripClick: function(event) {
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
		
		// If the clicked thing was the close button...
		if (el_click.parent().is('.close')) {
			this.tabManager.removeTab(el.data('tab-id'));
			return;
		}
		
		// Otherwise activate the tab
		this.tabManager.activateTab(el.data('tab-id'));
	},
	
	_onTabAdd: function(tabData) {
		tabData.btnId = Orb.getUniqueId('tab_');
		
		var li = $('<li id="'+tabData.btnId+'" data-tab-id="'+tabData.id+'" class="tab" title="' + tabData.title + '"><div class="title"><span>'+tabData.title+'</span></div><div class="close"><span /></div></li>');
		
		li.appendTo(this.tabStrip);
		
		// Add tooltip
		$(li).tipTip({defaultPosition: 'bottom'});
	},
	
	_onTabDeactivate: function(tabData, container, isActivating) {
		// Activate the last tab now if we're not already in the process
		// of activating another
		if (!isActivating) {
			var last_tab = $(':last', this.tabStrip);
			if (last_tab.length) {
				this.tabManager.activateTab(last_tab.data('tab-id'));
			}
		}
		
		// If a tab was removed, the onmouseout was never fired
		// so the tooltip saying its title might still appear
		$('#tiptip_holder').hide();
	},
	
	_onTabActivate: function(tabData) {
		$('li', this.tabStrip).removeClass('tab-active');
		$('#' + tabData.btnId, this.tabStrip).addClass('tab-active');
	},
	
	_onTabRemove: function(tabData) {
		$('#' + tabData.btnId).remove();
		
		$('#tiptip_holder').hide();
	}
});