Orb.createNamespace('DeskPRO.Agent');

/**
 * This tab manager helps storing and showing tabbed data. It works by
 * actually removing nodes from the dom instead of actually hiding them,
 * which is better performing but requires careful handling to set some
 * things like event handling. Essentially each tab is actually re-rendered
 * each time.
 */
DeskPRO.Agent.TabManager = new Class({
	
	Implements: [Events, Options],
	
	options: {
		defaultHideMode: 'hide',
		activateNew: true
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
		return this.currentTabId;
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
	 * Get all tabs
	 *
	 * @return {Array}
	 */
	getTabs: function() {
		return this.tabs;
	},
	
	
	
	/**
	 * Adds a tab to be managed.
	 *
	 * Data can be any arbitrary data used by callback methods, but these
	 * are special:
	 * - html: The HTML for the tab that will be rendered
	 * - id: Reserverd, is the id you pass
	 * - hideMode: 'remove' or 'hide' depending on mode. Defaults to options.defaultHideMode
	 *
	 * @param {String} id A unique ID (unique to this manager)
	 * @param {Object} data Data
	 */
	addTab: function(id, data) {
		if (typeOf(data) == 'string') {
			data = {html: data};
		}
		
		data.id = id;
		
		if (data['hideMode'] == undefined) {
			data.hideMode = this.options.defaultHideMode;
		}
		
		data.wrapperId = Orb.getUniqueId('tab_');
		data.isInserted = false;
		data.html = '<div id="'+data.wrapperId+'" style="display:none; position: absolute; top: 0; bottom: 0; left: 0; right: 0; overflow: auto;">' + data.html + '</div>';

		this.tabs[id] = data;
		
		this.fireEvent('addTab', [data, this]);
		
		if (!this.currentTabId || this.options.activateNew) {
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
			console.warn('Unknown tab: %s', id);
			return false;
		}
		
		// Already the current tab
		if (id == this.currentTabId) {
			return;
		}
		
		this.isActivating = true;
		
		if (this.currentTabId) {
			this.deactivateCurrentTab();
		}
		
		var data = this.tabs[id];
		
		//----------------------------------------
		// If we kept data nodes, we can just reinsert them
		//----------------------------------------
		
		if (data.isInserted && data.hideMode == 'hide') {
			
			console.log('Re-showing tab node: %s', id);
			
			var wrapper = $('#' + data.wrapperId, this.containerEl).show();
			
			if (data.callback_reinsert !== undefined) {
				data.callback_reinsert(data, $('#' + data.wrapperId, this.containerEl), this);
			}

			this.fireEvent('activateTabReinsert', [data, wrapper, this]);


		//----------------------------------------
		// Otherwise we're re-rendering or inserting for the first time
		//----------------------------------------
		
		} else {
			console.log('Rendering tab content: %s', id);

			var el = $(data.html).appendTo(this.containerEl);
			data.isInserted = true;
	
			el.show();

			if (data.callback_render !== undefined) {
				data.callback_render(data, $('#' + data.wrapperId, this.containerEl), this);
			}
			
			this.fireEvent('activateTabRender', [data, $('#' + data.wrapperId, this.containerEl), this]);
		}
		
		if (data.callback_activate !== undefined) {
			data.callback_activate(data, this.containerEl, this);
		}

		this.currentTabId = id;

		this.fireEvent('activateTab', [data, this.containerEl, this]);
		
		this.isActivating = false;
		
		data.isActive = true;
	},
	
	
	
	/**
	 * Deactivates the currently selected tab by removing it from the dom.
	 */
	deactivateCurrentTab: function() {
		
		if (!this.currentTabId) {
			return;
		}
		
		var data = this.tabs[this.currentTabId];
		data.isActive = false;
		
		// Chance to hook in before the nodes are actually removed
		this.fireEvent('deactivateTabBefore', [data, this.containerEl, this.isActivating, this]);
		
		if (data.callback_deactivate !== undefined) {
			data.callback_deactivate(data, $('#' + data.wrapperId, this.containerEl), this);
		}
		
		// Removing
		if (data.hideMode == 'remove') {
			
			console.log('Removing tab content: %o', this.currentTabId);
			
			$('#' + data.wrapperId, this.containerEl).remove();
			
			data.isInserted = false;
			
			if (data.callback_remove_content !== undefined) {
				data.callback_remove_content(data, $('#' + data.wrapperId, this.containerEl), this);
			}

		// hide
		} else {
			console.log('Hiding tab content: %o, id: %s', this.currentTabId, data.wrapperId);
			$('#' + data.wrapperId, this.containerEl).hide();
			
			if (data.callback_hide_content !== undefined) {
				data.callback_hide_content(data, $('#' + data.wrapperId, this.containerEl), this);
			}
		}
		
		this.fireEvent('deactivateTab', [data, $('#' + data.wrapperId, this.containerEl), this.isActivating, this]);
		
		this.currentTabId = null;
	},
	
	
	
	/**
	 * Removes a tab from this tab manager.
	 *
	 * @param {String} id The tab ID
	 */
	removeTab: function(id) {
		if (this.tabs[id] == undefined) {
			return false;
		}
		
		if (this.currentTabId == id) {
			this.deactivateCurrentTab();
		}
		
		var data = this.tabs[id];
		delete this.tabs[id];
		
		if (data.callback_remove_content !== undefined) {
			data.callback_remove_content(data, $('#' + data.wrapperId, this.containerEl), this);
		}
		
		$('#' + data.wrapperId, this.containerEl).remove();
		
		this.fireEvent('removeTab', [data, this]);
		
		var last_tab_id = Object.keys(this.tabs).getLast();
		if (last_tab_id) {
			this.activateTab(last_tab_id);
		}
	}
});