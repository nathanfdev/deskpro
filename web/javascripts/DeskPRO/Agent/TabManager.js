Orb.createNamespace('DeskPRO.Agent');

/**
 * This tab manager helps storing and showing tabbed data. It works by
 * actually removing nodes from the dom instead of actually hiding them,
 * which is better performing but requires careful handling to set some
 * things like event handling. Essentially each tab is actually re-rendered
 * each time.
 */
DeskPRO.Agent.TabManager = new Orb.Class({

	Implements: [Orb.Util.Events, Orb.Util.Options],

	initialize: function(containerEl, options) {

		this.options = {
			defaultHideMode: 'hide',
			activateNew: true
		};

		this.tabs = {};
		this.currentTabId = null;

		this.isActivating = false;

		this.containerEl = $(containerEl);
		this.setOptions(options);

		this.tabCount = 0;
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
	 * Get the currently selected tab object.
	 *
	 * @return {Object}
	 */
	getActiveTab: function() {
		return this.getTab(this.getActiveTabId());
	},

	/**
	 * Get the data for a tab.
	 *
	 * @return {Object}
	 */
	getTab: function(id) {
		if (this.tabs[id] == undefined) {
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
		data.html = '<div id="'+data.wrapperId+'" class="tabViewDetailContent" style="display: none">' + data.html + '</div>';

		this.tabs[id] = data;
		this.tabCount++;

		var el = $(data.html).appendTo(this.containerEl);
		data.isInserted = true;
		data.isInited = false;

		this.fireEvent('addTab', [data, this]);

		var noactivate = false;
		if (data.page && data.page.meta && data.page.meta.tabPlaceholderId) {
			noactivate = true;
		}

		if (!this.currentTabId || (this.options.activateNew && !noactivate)) {
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
			DP.console.error('Unknown tab: %s', id);
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
		var wrapper = $('#' + data.wrapperId).show();

		if (!data.isInited) {
			data.isInited = true;

			if (data.callback_render !== undefined) {
				data.callback_render(data, wrapper, this);
			}

			this.fireEvent('activateTabRender', [data, $('#' + data.wrapperId), this]);
		}

		if (data.callback_reinsert !== undefined) {
			data.callback_reinsert(data, wrapper, this);
		}

		this.fireEvent('activateTabReinsert', [data, wrapper, this]);


		if (data.callback_activate !== undefined) {
			data.callback_activate(data, wrapper, this);
		}

		this.currentTabId = id;

		this.fireEvent('activateTab', [data, wrapper, this]);

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
			data.callback_deactivate(data, $('#' + data.wrapperId), this);
		}

		DP.console.log('Hiding tab content: %o, id: %s', this.currentTabId, data.wrapperId);
		$('#' + data.wrapperId).hide();

		if (data.callback_hide_content !== undefined) {
			data.callback_hide_content(data, $('#' + data.wrapperId), this);
		}

		this.fireEvent('deactivateTab', [data, $('#' + data.wrapperId), this.isActivating, this]);

		this.currentTabId = null;
	},



	/**
	 * Removes a tab from this tab manager.
	 *
	 * @param {String} id The tab ID
	 */
	removeTab: function(id, silent) {
		if (this.tabs[id] == undefined) {
			return false;
		}

		if (this.currentTabId == id) {
			this.deactivateCurrentTab();
		}

		var data = this.tabs[id];
		delete this.tabs[id];
		this.tabCount--;

		if (data.callback_remove_content !== undefined) {
			data.callback_remove_content(data, $('#' + data.wrapperId), this);
		}

		$('#' + data.wrapperId).remove();

		if (!silent) {
			this.fireEvent('removeTab', [data, this]);

			var last_tab_id = Object.keys(this.tabs).getLast();
			if (last_tab_id) {
				this.activateTab(last_tab_id);
			}
		}
	}
});
