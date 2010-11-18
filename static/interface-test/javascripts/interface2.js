Orb.createNamespace('DeskPRO.Agent.Interface');

/**
 * The super duper Window that connects controls from all over the interface.
 * So right now this is just mainly interacting with the 3-paned shell,
 * but we'll add connecting-logic for sounds and notifications and such here as well.
 */
DeskPRO.Agent.Interface.Window = new Class({
	
	shells: {},

	initialize: function() {
		
		$('body').layout({
			north: {
				paneSelector: '#window_head',
				spacing_open: 0,
				spacing_closed: 0,
				size: 50
			},
			center: {
				paneSelector: '#pane_shell'
			}
		});
		
		var pane = new DeskPRO.Agent.Interface.Shells.Pane();
		pane.addTabHtml('tab1',  'Tab 1', '<b>Tab 1</b>');
		pane.addTabHtml('tab2',  'Tab 2', '<b>Tab 2</b>');
		pane.addTabHtml('tab3',  'Tab 3', '<b>Tab 3</b>');
		pane.addTabHtml('tab4',  'Tab 4', '<b>Tab 4</b>');
	},
	
	addShell: function(name, shell) {
		this.shells[name] = shell;
	},
	
	getShell: function(name) {
		return this.shells[name];
	},
	
	getPaneShell: function() {
		return this.getShell('pane');
	}
});





//#####################################################################
//# DeskPRO.Agent.Interface.Shells.Pane
//#####################################################################

Orb.createNamespace('DeskPRO.Agent.Interface.Shells');

/**
 * The main three-paned interface has a nav pane on the left,
 * a list pane in the center, and then the content area on the right.
 *
 * The content area has a tabbed interface.
 */
DeskPRO.Agent.Interface.Shells.Pane = new Class({
	el: null,
	htmlEl: null,
	
	tabManager: null,
	tabStrip: null,
	
	initialize: function() {
		this.el = $('#pane_shell');
		this.htmlEl = $(this.el).get(0);
		
		this.el.layout({
			west: {
				paneSelector: '#pane_nav'
			},
			center: {
				paneSelector: '#pane_shell_inner'
			}
		});
		
		$('#pane_shell_inner').layout({
			west: {
				paneSelector: '#pane_list'
			},
			center: {
				paneSelector: '#pane_content'
			}
		});
		
		this.tabStrip = $('#pane_tabs');
		this.tabStrip.click(this._tabStripClick.bind(this));
		
		this.tabManager = new DeskPRO.Interface.TabManager('#page');
		var self = this;
		this.tabManager.addEvents({
			addTab: this._onTabAdd.bind(this),
			activateTab: this._onTabActivate.bind(this),
			deactivateTab: this._onTabDeactivate.bind(this)
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
		var el = $(event.target);
		
		// If its not a tab, we can just ignore the event
		if (!el.is('li.tab')) {
			return;
		}
		
		// Otherwise activate the tab
		this.tabManager.activateTab(el.data('tab-id'));
	},
	
	_onTabAdd: function(tabData) {
		tabData.btnId = Orb.getUniqueId('tab_');
		this.tabStrip.append('<li id="'+tabData.btnId+'" data-tab-id="'+tabData.id+'" class="tab">'+tabData.title+'</li>');
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
	},
	
	_onTabActivate: function(tabData) {
		$('li', this.tabStrip).removeClass('tab-active');
		$('#' + tabData.btnId, this.tabStrip).addClass('tab-active');
	}
});





//#####################################################################
//# DeskPRO.Interface.PageFragment
//#####################################################################

/**
 * A generic page fragment is any kind of page we'll spit into the content
 * area of the currently loaded page. For example, tabs, lightbox content etc.
 *
 * Each page can have it's own resources that should be loaded before the HTML
 * for it is rendered (though that responsibility is up to whatever uses the PageFragment).
 */
DeskPRO.Interface.PageFragment = new Class({

	scripts: [],
	stylesheets: [],
	html: '',
	
	initialize: function(html, scripts, stylesheets) {
		if (html) {
			this.html = html;
		}
		
		if (scripts) {
			if (typeOf(scripts) == 'string') {
				scripts = [scripts];
			}
			
			this.scripts = scripts;
		}
		
		if (stylesheets) {
			if (typeOf(stylesheets) == 'string') {
				stylesheets = [stylesheets];
			}
			
			this.stylesheets = stylesheets;
		}
	},
	
	getScripts: function() {
		return this.scripts;
	},
	
	getStylesheets: function() {
		return this.stylesheets;
	},
	
	getHtml: function() {
		return this.html;
	},
	
	loadResources: function(callback) {
		
		var batch = [];
		
		for (var i = 0; i < this.scripts.length; i++) {
			batch.push({ type: 'script', url: this.scripts[i] });
		}
		
		for (var i = 0; i < this.stylesheets.length; i++) {
			batch.push({ type: 'stylesheet', url: this.stylesheets[i] });
		}
		
		Orb.resourceLoader.loadBatch(batch, callback);
	}
});




//#####################################################################
//# DeskPRO.Interface.TabManager
//#####################################################################

Orb.createNamespace('DeskPRO.Interface');

/**
 * This tab manager helps storing and showing tabbed data. It works by
 * actually removing nodes from the dom instead of actually hiding them,
 * which is better performing but requires careful handling to set some
 * things like event handling. Essentially each tab is actually re-rendered
 * each time.
 */
DeskPRO.Interface.TabManager = new Class({
	
	Implements: [Events, Options],
	
	options: {
		defaultHideMode: 'remove'
	},
	
	tabs: {},
	currentTabId: null,
	containerEl: null,
	
	isActivating: false,
	
	initialize: function(containerEl, options) {
		this.containerEl = $(containerEl);
		this.setOptions(options);
	},
	
	
	
	/**
	 * Get the currently selected tab ID.
	 *
	 * @return {String}
	 */
	getActiveTabId: function() {
		return this.currentTabId();
	},
	
	
	
	/**
	 * Get the data for a tab.
	 *
	 * @return {Object}
	 */
	getTab: function(id) {
		if (this.tabs[id] != undefined) {
			return null;
		}
		
		return this.tabs[id];
	},
	
	
	
	/**
	 * Adds a tab to be managed.
	 *
	 * Data can be any arbitrary data used by callback methods, but these
	 * are special:
	 * - html: The HTML for the tab that will be rendered
	 * - id: Reserverd, is the id you pass
	 * - hideMode: 'remove' or 'detach' depending on mode. Defaults to options.defaultHideMode
	 *
	 * @param {String} id A unique ID (unique to this manager)
	 * @param {Object} data Data
	 */
	addTab: function(id, data) {
		if (typeOf(data) == 'string') {
			data = {html: data};
		}
		
		data.id = id;
		
		if (data['hideMode'] != undefined) {
			data.hideMode = this.options.defaultHideMode;
		}

		this.tabs[id] = data;
		
		this.fireEvent('addTab', [data, this]);
		
		if (!this.currentTabId) {
			this.activateTab(id);
		}
	},
	
	
	
	/**
	 * Activate a specific tab by inserting it back into the dom.
	 *
	 * @param {String} id The tab ID
	 */
	activateTab: function(id) {
		
		if (this.tabs[id] == undefined) {
			console.log('Unknown tab: %s', id);
			return false;
		}
		
		this.isActivating = true;
		
		var data = this.tabs[id];
		
		//----------------------------------------
		// If we kept data nodes, we can just reinsert them
		//----------------------------------------
		
		if (data.domNodes != undefined) {
			
			console.log('Reinserting tab nodes: %s', id);
			
			this.containerEl.append(data.domNodes);
			
			if (Object.contains(data, 'callback_reinsert')) {
				data.callback_reinsert(data, this.containerEl, this);
			}
			
			delete data.domNodes;

			this.fireEvent('activateTabReinsert', [data, this.containerEl, this]);


		//----------------------------------------
		// Otherwise we're re-rendering
		//----------------------------------------
		
		} else {
			
			console.log('Rendering tab content: %s', id);
			
			this.containerEl.html(data.html);
			
			if (Object.contains(data, 'callback_render')) {
				data.callback_render(data, this.containerEl, this);
			}
			
			this.fireEvent('activateTabRender', [data, this.containerEl, this]);
		}
		
		if (Object.contains(data, 'callback_activate')) {
			data.callback_activate(data, this.containerEl, this);
		}

		this.currentTabId = id;

		this.fireEvent('activateTab', [data, this.containerEl, this]);
		
		this.isActivating = false;
	},
	
	
	
	/**
	 * Deactivates the currently selected tab by removing it from the dom.
	 */
	deactivateCurrentTab: function() {
		
		if (!this.currentTabId) {
			return;
		}
		
		var data = this.tabs[this.currentTabId];
		
		// Chance to hook in before the nodes are actually removed
		this.fireEvent('deactivateTabBefore', [data, this.containerEl, this.isActivating, this]);
		
		// Removing
		if (data.hideMode == 'remove') {
			this.containerEl.empty();
			delete data.domNodes;

		// detach
		} else {
			data.domNodes = this.containerEl.children();
			this.containerEl.children().detach();
		}
		
		if (Object.contains(data, 'callback_deactivate')) {
			data.callback_deactivate(data, this.containerEl, this);
		}
		
		this.fireEvent('deactivateTab', [data, this.containerEl, this.isActivating, this]);
		
		this.currentTabId = null;
	},
	
	
	
	/**
	 * Removes a tab from this tab manager.
	 *
	 * @param {String} id The tab ID
	 */
	removeTab: function(id) {
		if (this.tabs[id] != undefined) {
			return false;
		}
		
		if (this.currentTabId == id) {
			this.deactivateTab();
		}
		
		var data = this.tabs[id];
		delete this.tabs[id];
		
		if (Object.contains(data, 'callback_remove')) {
			data.callback_delete();
		}
		
		this.fireEvent('removeTab', [data, this]);
	}
});

