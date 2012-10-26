Orb.createNamespace('DeskPRO.Agent');

DeskPRO.Agent.KeyboardShortcuts = new Orb.Class({
	Implements: [Orb.Util.Events, Orb.Util.Options],

	initialize: function() {
		var self = this;

		$(document).bind('keydown', 'ctrl+shift+left', this.tabLeft.bind(this));
		$(document).bind('keydown', 'ctrl+shift+right', this.tabRight.bind(this));
		$(document).bind('keydown', 'ctrl+shift+c', this.closeTab.bind(this));
		$(document).bind('keydown', 'alt+c', this.saveContent.bind(this));

		function getActiveListNav() {
			var listNav = null;
			if (DeskPRO_Window.activeListNav) {
				listNav = DeskPRO_Window.activeListNav;
			} else {
				var p = DeskPRO_Window.getListPage();
				if (p && p.listNav) {
					listNav = p.listNav;
				}
			}

			return listNav;
		};

		var navigateListPane = function(action) {
			return function(ev) {
				var listNav = getActiveListNav();
				if (listNav) {
					listNav[action]();
				}
			};
		};

		// Navigating list pane
		$(document).bind('keydown', 'down', navigateListPane('down'));
		$(document).bind('keydown', 'up', navigateListPane('up'));
		$(document).bind('keydown', 'return', navigateListPane('enter'));
		$(document).bind('keydown', 'space', navigateListPane('check'));

		// Create-type
		if (DeskPRO_Window.newTicketLoader)        $(document).bind('keydown', 't', this.showNewTicket.bind(this));
		if (DeskPRO_Window.newArticleLoader)       $(document).bind('keydown', 'a', this.showNewArticle.bind(this));
		if (DeskPRO_Window.newNewsLoader)          $(document).bind('keydown', 'n', this.showNewNews.bind(this));
		if (DeskPRO_Window.newDownloadLoader)      $(document).bind('keydown', 'd', this.showNewDownload.bind(this));
		if (DeskPRO_Window.newFeedbackLoader)      $(document).bind('keydown', 'i', this.showNewFeedback.bind(this));
		if (DeskPRO_Window.newPersonLoader)        $(document).bind('keydown', 'p', this.showNewPerson.bind(this));
		if (DeskPRO_Window.newOrganizationLoader)  $(document).bind('keydown', 'o', this.showNewOrganization.bind(this));
		if (DeskPRO_Window.newArticleLoader)       $(document).bind('keydown', 'k', this.showNewTask.bind(this));

		this.boundShortkuts = {};

		this.addContextShortcut('ticket', 'shift+r', 'shortcutFocusReply');
		this.addContextShortcut('ticket', 'shift+p', 'openUserProfile');
		this.addContextShortcut('ticket', 'shift+o', 'openOrgProfile');
		this.addContextShortcut('person', 'shift+o', 'openOrgProfile');
	},


	/**
	 * Adds a shortcut that only applies to specific tab types. When the shortcut is run,
	 * an event (`eventName`) is fired on the active tab of its type for the tab to handle.
	 *
	 * @param pageTypeName
	 * @param key
	 * @param eventName
	 */
	addContextShortcut: function(pageTypeName, key, eventName) {
		if (!this.boundShortkuts[key]) {
			this.boundShortkuts[key] = {};
			$(document).bind('keydown', key, (function(ev) {
				this.dispatchShortcutEvent(ev, key);
			}).bind(this));
		}

		this.boundShortkuts[key][pageTypeName] = eventName;
	},


	/**
	 * Called when a registered context shortcut is fired. We need to decide which, if any,
	 * event to dispatch to the tab.
	 *
	 * @param ev
	 * @param key
	 */
	dispatchShortcutEvent: function(ev, key) {
		if (!this.boundShortkuts[key]) {
			return;
		}

		var page = DeskPRO_Window.getCurrentTabPage();
		if (!page || !page.TYPENAME || !this.boundShortkuts[key][page.TYPENAME]) {
			return;
		}

		page.fireEvent(this.boundShortkuts[key][page.TYPENAME], [ev, key]);
	},

	//#########################################################################
	//# Global Shortcuts
	//#########################################################################

	showNewTicket: function(ev) {
		DeskPRO_Window.newTicketLoader.toggle();
	},
	showNewArticle: function(ev) {
		DeskPRO_Window.newArticleLoader.toggle();
	},
	showNewNews: function(ev) {
		DeskPRO_Window.newNewsLoader.toggle();
	},
	showNewDownload: function(ev) {
		DeskPRO_Window.newDownloadLoader.toggle();
	},
	showNewFeedback: function(ev) {
		DeskPRO_Window.newFeedbackLoader.toggle();
	},
	showNewPerson: function(ev) {
		DeskPRO_Window.newPersonLoader.toggle();
	},
	showNewOrganization: function(ev) {
		DeskPRO_Window.newOrganizationLoader.toggle();
	},
	showNewTask: function(ev) {
		$('form#newTaskForm input, form#newTaskForm select').val('');
		DeskPRO_Window.newTaskLoader.toggle();
	},
	showNewDeal: function(ev) {
		DeskPRO_Window.newDealLoader.toggle();
	},

	/**
	 * Saves content by looking for the 'submit-trigger' in the open fragment. Popovers
	 * are checked first, and then tabs.
	 */
	saveContent: function() {
		var page = null;
		Object.each(DeskPRO.Agent.PageHelper.Popover_Instances, function(inst) {
			if (inst.isOpen()) {
				page = inst.page;
			}
		});

		if (!page) {
			var tab = DeskPRO_Window.getTabWatcher().getActiveTab();
			if (tab) {
				page = tab.page;
			}
		}

		if (!page) {
			return false;
		}

		var wrapper = page.wrapper || page.el || page.wrap || false;
		if (!wrapper) {
			return false;
		}

		if (page.submit) {
			page.submit();
		} else {
			var form = $('form.keybound-submit', wrapper);
			if (!form.length) {
				return false;
			}

			form.submit();
		}
	},

	tabLeft: function(ev) {
		var activeTab = $('li.activeTabList', DeskPRO_Window.TabBar.tabList);
		var next = activeTab.prev();

		if (!next.length) {
			next = $('li:last', DeskPRO_Window.TabBar.tabList);
		}

		if (!next.is('.activeTabList')) {
			DeskPRO_Window.TabBar.activateTabById(next.data('tab-id'));
		}
	},

	tabRight: function(ev) {
		var activeTab = $('li.activeTabList', DeskPRO_Window.TabBar.tabList);
		var next = activeTab.next();

		if (!next.length) {
			next = $('li:first', DeskPRO_Window.TabBar.tabList);
		}

		if (!next.is('.activeTabList')) {
			DeskPRO_Window.TabBar.activateTabById(next.data('tab-id'));
		}
	},

	closeTab: function(ev) {
		var activeTab = DeskPRO_Window.TabBar.getActiveTab();
		if (activeTab) {
			DeskPRO_Window.TabBar.removeTabById(activeTab.id);
		}
	}
});
