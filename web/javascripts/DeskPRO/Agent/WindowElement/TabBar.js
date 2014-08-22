Orb.createNamespace('DeskPRO.Agent.WindowElement');

/**
 * The tabbar handles adding and removing tabs in the right pane of the window.
 */
DeskPRO.Agent.WindowElement.TabBar = new Orb.Class({

	Implements: [Orb.Util.Events, Orb.Util.Options],

	initialize: function(options) {
		this.options = {
			/**
			 * The element that contains the tabs. This is the pane or wrapper element that
			 * contains the actual list. It must include a list with class '.dp-tab-list'
			 */
			tabPane: null,

			/**
			 * The element the append body pages to
			 */
			bodyPane: null
		};

		this.setOptions(options);

		this.tabPane = $(this.options.tabPane);
		this.tabList = this.tabPane.find('ul.dp-tab-list').first();
		this.bodyPane = $(this.options.bodyPane);
		this.menuBtn = $(this.options.menuBtn);
		this.active = null;

		this.tabCount = 0;

		this.tabs = {};
		this._tabs = [];
		this.currentTabId = null;
		this.initScope();

		this.tabBarOverflow = new DeskPRO.Agent.WindowElement.TabBarOverflow();
	},

	initScope: function() {
		var self = this;
		this.$scope = DeskPRO_Window.$scope;
		this.$timeout = DeskPRO_Window.$timeout;
		this.$scope.tabs = this._tabs;
		this.$scope.tabClick = function($event, tab){ self._tabStripClick($event, tab); };

		this.$scope.$watch('listItems', function(){
			self._checkOpenedItems();
		}, true);
	},

	_checkOpenedItems: function(){
		var self = this;

		this.$scope.listItems.each(function(item){
			item.open = false;
			for (var i = 0; i < self._tabs.length; i++) {
				var tab = self._tabs[i];
				if (tab.page && tab.page.meta.pageIdentity === item.identity) {
					item.open = true;
					return;
				}
			}
		});
	},

	rescanTitles: function() {
		var i, tab;
		for (i = 0; i < this._tabs.length; i++) {
			tab = this._tabs[i];
			if (tab.page) {
				tab.title = tab.page.getMetaData('title', 'Untitled');
				console.log(tab)
			}
		}
		this.$scope.$safeApply();
	},

	//##################################################################################################################
	// Methods to fetch tabs
	//##################################################################################################################

	/**
	 * Get the active tab ID
	 *
	 * @return {String}
	 */
	getActiveTabId: function() {
		return this.currentTabId;
	},


	/**
	 * Get the active tab object
	 *
	 * @return {Object}
	 */
	getActiveTab: function() {
		return this.getTab(this.currentTabId);
	},


	/**
	 * Get a tab by its id
	 *
	 * @param {String} id
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
	 * @return {Object}
	 */
	getTabs: function() {
		return this.tabs;
	},


	/**
	 * Get an array of all tab ids
	 *
	 * @return {Array}
	 */
	getTabIds: function() {
		return Object.keys(this.tabs);
	},


	/**
	 * Find a tab by pages fragment.
	 *
	 * @param {String} fragment
	 * @return {Object}
	 */
	findTabByFragment: function(fragment) {
		var retTab = null;

		Object.each(this.tabs, function(tab) {
			if (tab.page && tab.page.getMetaData('url_fragment') == fragment) {
				retTab = tab;
				return false;
			}
		});

		return retTab;
	},


	/**
	 * Find a tab by its pages route url
	 *
	 * @param {String} routeUrl
	 * @return {String}
	 */
	findTabByRouteUrl: function(routeUrl) {
		var retTab = null;

		Object.each(this.tabs, function(tab) {
			if (tab.page.getMetaData('routeUrl') == routeUrl) {
				retTab = tab;
				return false;
			}
		});

		return retTab;
	},


	//##################################################################################################################
	// Adding a removing tabs
	//##################################################################################################################

	/**
	 * Add a page to the tabbar
	 *
	 * @param {Object} page
	 */
	addTab: function(page) {

		var self = this;

		this.isAdding = true;

		var id = Orb.uuid();
		page.meta.tabId = id;

		var data = {};
		data.id = id;
		data.page = page;
		data.title = page.getMetaData('title', 'Untitled');
		data.callback_render = function(container) {
			container = $(container);
			page.fireEvent('render', [container.first(), id]);
		};
		data.callback_remove_content = function(data, container) {
			if (data.isInited) {
				page.fireEvent('destroy');
			}
		};
		data.callback_activate = function() {
			page.fireEvent('activate');
		};
		data.callback_deactivate = function() {
			page.fireEvent('deactivate');
		};

		data.isInited = false;

		if (!this.tabs[id]) {
			this.tabCount++;
			$('body').removeClass('without-tabs').addClass('with-tabs');
		}
		this.tabs[id] = data;

		//----------
		// Render content to dom
		//----------

		// The tab content
		data.wrapperId = 'tabcontent_' + id;

		if (page.meta.existingWrapper) {
			data.wrapper = page.meta.existingWrapper;
			data.wrapper.attr('id', data.wrapperId);
			data.wrapper.attr('class', 'tabViewDetailContent test');
			data.wrapper.css('display', 'none');
			data.wrapper.appendTo(this.bodyPane);
		} else {
			var preparedOutput = DeskPRO_Window.prepareWidgetedHtml(page.getHtml());

			data.wrapper = $('<div id="'+data.wrapperId+'" class="tabViewDetailContent" style="display: none">' + preparedOutput.html + '</div>').appendTo(this.bodyPane);

			DeskPRO_Window.runWidgetedJs(data.page, preparedOutput.jsSource, preparedOutput.jsInline);
		}

		//----------
		// Render tab button
		//----------

		data.tabBtnId = 'tabbtn_' + id;
		data.tabType = null;

		if (page) {
			if (page.TYPENAME_FOR) {
				data.tabType = page.TYPENAME_FOR;
			} else if (page.TYPENAME) {
				data.tabType = page.TYPENAME;
			}
		}

		var wasActive = false;
		var otherTab = null;

		if (data.page && data.page.meta.tabPlaceholderId) {
			otherTab = this.getTab(data.page.meta.tabPlaceholderId);
		}

			// We may have had a placeholder, in which case we want to place
			// the new tab where the old one was while also removing the placeholder
			// content in the body pane

		// insert new tab after active
		if (otherTab && this._tabs.indexOf(otherTab) < this._tabs.length - 1) {
			this._tabs.splice(this._tabs.indexOf(otherTab) + 1, 0, data);
		} else {
			this._tabs.push(data);
		}

		if (otherTab) {
			if (this.currentTabId == otherTab.id) {
				wasActive = true;
				this.currentTabId = null;
			}

			this.removeTab(otherTab, true);
		}

		this._checkOpenedItems();

		//----------
		// Just about done
		//----------

		this.fireEvent('addTab', [data, this]);

		if (!this.currentTabId || wasActive) {
			this.activateTabById(id);
		} else {
			DeskPRO_Window.updateWindowUrlFragment();
		}

		this.isAdding = false;

		this.$scope.$safeApply();
		this.$timeout(function() {
			self.tabBarOverflow.update();
		});
		return id;
	},

	/**
	 * Like addTab except the tab is marked as "loading"
	 *
	 * @param url
	 * @param routeData
	 */
	addTabPlaceholder: function(url, routeData) {
		var html = DeskPRO_Window.util.getPlainTpl($('#tab_loading_template'));

		var page = DeskPRO_Window.createPageFragment(html, 'DeskPRO.Agent.PageFragment.Page.Loading');
		page.meta.routeUrl = url;
		page.meta.routeData = routeData;
		page.TYPENAME_FOR = routeData.master;
		page.TAB_FOR_ID = routeData.masterTag;

		if (routeData.url_fragment) {
			page.meta.url_fragment = routeData.url_fragment;
		}

		if (routeData.title) {
			page.meta.title = routeData.title;
		}
		if (routeData.forTypename) {
			page.LOADING_TYPENAME = routeData.forTypename;
		}

		var id = this.addTab(page);
		this.activateTabById(id);

		if (routeData.tabLoad) {
			routeData.tabLoad();
		}

		return id;
	},


	/**
	 * Activate a tab in the tabbar
	 *
	 * @param {Object} tab
	 */
	activateTab: function(tab) {

		if (!tab) {
			return;
		}

		var id = tab.id;

		// Already the current tab
		if (id == this.currentTabId) {
			return;
		}

		this.isActivating = true;

		if (this.currentTabId) {
			this.deactivateCurrentTab();
		}

		var data = this.tabs[id];
		if (!data || !data.wrapper) {
			this.removeTab(tab, true);
		}
		var wrapper = data.wrapper.show();

		if (!data.isInited) {
			data.isInited = true;

			if (data.callback_render !== undefined) {
				data.callback_render(wrapper);
			}

			this.fireEvent('activateTabRender', [data, $('#' + data.wrapperId), this]);
		}

		if (data.callback_activate !== undefined) {
			data.callback_activate(data, wrapper, this);
		}

		this.clearAlertTab(data);

		this.currentTabId = id;

		this.fireEvent('activateTab', [data, wrapper, this]);

		this.isActivating = false;
		data.isActive = true;
		DeskPRO_Window.updateWindowUrlFragment();
	},


	/**
	 * Deactivate the currently selected tab
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
	 * Determines if a tab is visible in relation to scrolling.
	 *
	 * @param tab
	 */
	isTabVisible: function(tab) {
		var $el = $(tab.tabBtnId);
		var left = $el.position().left;

		// Attempt to ignore margin and border. Lets hope they're the same on both sides.
		var guess_slack = Math.round(($el.outerWidth() - $el.innerWidth()) / 2);
		var right = left + $el.innerWidth() + guess_slack;

		var bounds = this.tabBarOverflow.getBounds();

		return !(right < bounds.left || left > bounds.right);
	},

	/**
	 * Remove a tab
	 *
	 * @param {Object} tab
	 * @param {Boolean} [silent]
	 */
	removeTab: function(tab, silent) {

		var self = this;
		var id = tab.id;
		var wasActive = false;

		if (this.currentTabId == id) {
			wasActive = true;
			if (!silent) {
				this.deactivateCurrentTab();
			}

			this.currentTabId = null;
		}

		var data = this.tabs[id];
		delete this.tabs[id];
		this.tabCount--;
		if (this.tabCount <= 0) {
			$('body').addClass('without-tabs').removeClass('with-tabs');
		}
		this._tabs.splice(this._tabs.indexOf(tab), 1);
		this._checkOpenedItems();

		if (data.callback_remove_content !== undefined) {
			data.callback_remove_content(data, $('#' + data.wrapperId), this);
		}

		if (data.wrapper) {
			data.wrapper.empty();
			data.wrapper.remove();
		}

		if (data.page) {
			if (data.page.meta.routeData && data.page.meta.routeData.xhr) {
				data.page.meta.routeData.xhr.abort();
			}

			if (data.page.meta.routeData && data.page.meta.routeData.dataUnload) {
				data.page.meta.routeData.dataUnload();
			}
		}

		if (!silent) {

			this.fireEvent('removeTab', [data, this]);

			if (wasActive) {
				var last_tab_id = Object.keys(this.tabs).getLast();
				if (last_tab_id) {
					this.activateTabById(last_tab_id);
				} else {
					// If list view isnt active, then after a small timeout
					// make it visiable.
					// The timeout is in case we have other routines that auto-open
					// a new tab (e.g., after ticket reply)
					this.$timeout(function(){
						var last_tab_id = Object.keys(self.tabs).getLast();
						if (!last_tab_id) {
							self.$scope.showList();
						}
					}, 100);
				}
			}

			if (data.tabBtn)  data.tabBtn.remove();
			if (data.tabBtn2) data.tabBtn2.remove();
		}

		DeskPRO_Window.updateWindowUrlFragment();
		this.$scope.$safeApply();
		this.$timeout(function() {
			self.tabBarOverflow.update();
		})
	},


	/**
	 * Remove a tab via id
	 *
	 * @param {String} id
	 */
	removeTabById: function(id) {
		var tab = this.getTab(id);
		if (!tab) {
			DP.console.log("Cannot remove, unknown tab %s", id);
			DP.console.trace();
			return null;
		}
		this.removeTab(tab);
	},


	/**
	 * Activate a tab by id
	 *
	 * @param {String} id
	 */
	activateTabById: function(id) {
		var tab = this.getTab(id);
		if (!tab) {
			DP.console.log("Cannot activate, unknown tab %s", id);
		}
		this.activateTab(tab);
	},

	/**
	 * Activate a tab by id
	 *
	 * @param {String} id
	 */
	tabToFrontTabById: function(id, noalert) {
		var tab = this.getTab(id);

		if (!tab) {
			DP.console.log("Cannot activate, unknown tab %s", id);
		}

		var otherTab = null;
		if (tab.page && tab.page.meta.tabPlaceholderId) {
			otherTab = this.getTab(tab.page.meta.tabPlaceholderId);
		}

		this._tabs.splice(this._tabs.indexOf(tab), 1);

		if (otherTab && otherTab != tab) {
			if (this.currentTabId == otherTab.id) {
				this.currentTabId = null;
			}

			this._tabs.indexOf(otherTab) < this._tabs.length - 1
				? this._tabs.splice(this._tabs.indexOf(otherTab) + 1, 0, tab)
				: this._tabs.push(tab);

			this.removeTab(otherTab, true);
		} else {
			this._tabs.unshift(tab);
		}

		this.tabBarOverflow.resetScroll();

		if(!noalert) {
			$(tab.tabBtnId).effect("pulsate", { times:4 }, 500);
		}
	},

	//##################################################################################################################
	// Tab functionality
	//##################################################################################################################

	alertTab: function(tab) {
		var el = $(tab.tabBtnId);
		if (tab.isActive || el.is('.is-alerting')) return;

		if(!this.isTabVisible(tab)) {
			this.tabToFrontTabById(tab.id, true);
		}

		el.addClass('is-alerting');
		var timeout = this._alertTabDoHighlight.periodical(700, this, [el]);
		el.data('alerting-timeout', timeout);
	},

	clearAlertTab: function(tab) {
		var el = $(tab.tabBtnId);

		if (!el.length) return;

		el.removeClass('alert-highlight').removeClass('is-alerting');

		var timeout = el.data('alerting-timeout');
		if (timeout) {
			window.clearTimeout(timeout);
		}

		el.data('alerting-timeout', null);
	},

	_alertTabDoHighlight: function(el) {
		el.toggleClass('alert-highlight');
	},


	//##################################################################################################################
	// Handling events
	//##################################################################################################################

	_tabStripClick: function(event, tab) {

		if (this.cancelClickActivate) {
			this.cancelClickActivate = false;
			return;
		}

		this.cancelClickActivate = true;

		var el_click = $(event.target);

		// If the clicked thing was the close button, or if its a middle-click...
		if (el_click.is('.close') || event.which == 2 || event.isDbl) {
			if (!tab) {
				return;
			}

			if (tab.page && tab.page.fireEvent) {
				event.deskpro = {cancelClose: false};
				tab.page.fireEvent('closeTab', [event, tab]);

				if (event.deskpro.cancelClose) {
					this.cancelClickActivate = false;
					return;
				}
			}

			tab.isCloseClick = true;
			this.removeTabById(tab.id);
			tab.isCloseClick = false;

			this.cancelClickActivate = false;

			return;
		}

		// Otherwise activate the tab
		this.activateTabById(tab.id);

		DeskPRO_Window.$scope.showTabs();

		this.cancelClickActivate = false;
	}
});