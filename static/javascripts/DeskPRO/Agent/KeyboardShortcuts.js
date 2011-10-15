Orb.createNamespace('DeskPRO.Agent');

DeskPRO.Agent.KeyboardShortcuts = new Orb.Class({
	Implements: [Orb.Util.Events, Orb.Util.Options],

	initialize: function() {
		$(document).bind('keydown', 'ctrl+left', this.tabLeft.bind(this));
		$(document).bind('keydown', 'ctrl+right', this.tabRight.bind(this));
		$(document).bind('keydown', 'ctrl+shift+c', this.closeTab.bind(this));

		// Create-type
		$(document).bind('keydown', 't', this.showNewTicket.bind(this));
		$(document).bind('keydown', 'k', this.showNewArticle.bind(this));
		$(document).bind('keydown', 'n', this.showNewNews.bind(this));
		$(document).bind('keydown', 'd', this.showNewDownload.bind(this));
		$(document).bind('keydown', 'i', this.showNewIdea.bind(this));
		$(document).bind('keydown', 'p', this.showNewPerson.bind(this));
		$(document).bind('keydown', 'o', this.showNewOrganization.bind(this));
		$(document).bind('keydown', 'j', this.showNewTask.bind(this));


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
	showNewIdea: function() {
		DeskPRO_Window.newIdeaLoader.toggle();
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
