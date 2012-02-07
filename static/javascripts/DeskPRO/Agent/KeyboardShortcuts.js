Orb.createNamespace('DeskPRO.Agent');

DeskPRO.Agent.KeyboardShortcuts = new Orb.Class({
	Implements: [Orb.Util.Events, Orb.Util.Options],

	initialize: function() {
		$(document).bind('keydown', 'ctrl+left', this.tabLeft.bind(this));
		$(document).bind('keydown', 'ctrl+right', this.tabRight.bind(this));
		$(document).bind('keydown', 'ctrl+shift+c', this.closeTab.bind(this));
		$(document).bind('keydown', 'alt+c', this.saveContent.bind(this));

		// Create-type
		$(document).bind('keydown', 't', this.showNewTicket.bind(this));
		$(document).bind('keydown', 'a', this.showNewArticle.bind(this));
		$(document).bind('keydown', 'n', this.showNewNews.bind(this));
		$(document).bind('keydown', 'd', this.showNewDownload.bind(this));
		$(document).bind('keydown', 'i', this.showNewFeedback.bind(this));
		$(document).bind('keydown', 'p', this.showNewPerson.bind(this));
		$(document).bind('keydown', 'o', this.showNewOrganization.bind(this));
		$(document).bind('keydown', 'k', this.showNewTask.bind(this));
        //$(document).bind('keydown', 'l', this.showNewDeal.bind(this));

		this.boundShortkuts = {};

		this.addContextShortcut('ticket', 'ctrl+shift+r', 'shortcutFocusReply');
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

	showNewTicket: function() {
		DeskPRO_Window.newTicketLoader.toggle();
	},
	showNewArticle: function() {
		DeskPRO_Window.newArticleLoader.toggle();
	},
	showNewNews: function() {
		DeskPRO_Window.newNewsLoader.toggle();
	},
	showNewDownload: function() {
		DeskPRO_Window.newDownloadLoader.toggle();
	},
	showNewFeedback: function() {
		DeskPRO_Window.newFeedbackLoader.toggle();
	},
	showNewPerson: function() {
		DeskPRO_Window.newPersonLoader.toggle();
	},
	showNewOrganization: function() {
		DeskPRO_Window.newOrganizationLoader.toggle();
	},
        showNewTask: function() {
		$('form#newTaskForm input, form#newTaskForm select').val('');
                DeskPRO_Window.newTaskLoader.toggle();
	},
	showNewDeal: function() {
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

		var form = $('form.keybound-submit', wrapper);
		if (!form.length) {
			return false;
		}

		form.submit();
	},

	tabLeft: function() {
		var activeTab = $('li.active-tab', DeskPRO_Window.pageTabStrip.tabStrip);
		var next = activeTab.prev();

		if (!next.length) {
			next = $('li:last', DeskPRO_Window.pageTabStrip.tabStrip);
		}

		if (!next.is('.active-tab')) {
			DeskPRO_Window.pageTabStrip.activateTabById(next.data('tab-id'));
		}
	},

	tabRight: function() {
		var activeTab = $('li.active-tab', DeskPRO_Window.pageTabStrip.tabStrip);
		var next = activeTab.next();

		if (!next.length) {
			next = $('li:first', DeskPRO_Window.pageTabStrip.tabStrip);
		}

		if (!next.is('.active-tab')) {
			DeskPRO_Window.pageTabStrip.activateTabById(next.data('tab-id'));
		}
	},

	closeTab: function() {
		var activeTab = DeskPRO_Window.pageTabStrip.getActiveTab();
		if (activeTab) {
			DeskPRO_Window.pageTabStrip.removeTabById(activeTab.id);
		}
	}
});
