Orb.createNamespace('DeskPRO.Agent.WindowElement.Section');

DeskPRO.Agent.WindowElement.Section.Tickets = new Orb.Class({
	Extends: DeskPRO.Agent.WindowElement.Section.AbstractSection,

	init: function() {
		this.buttonEl = $('#tickets_section');

		this.setSectionElement($('<section id="tickets_outline"></section>'));

		DeskPRO_Window.getMessageChanneler().subscribeChannel('agent.filter-update', this.filterUpdated.bind(this));

		$.ajax({
			url: BASE_URL + 'agent/tickets/get-section-data.json',
			context: this,
			success: function(data) {
				this._initSection(data);
			}
		});
	},

	_initSection: function(data) {

		this.setHasInitialLoaded();

		this.sectionEl.html(data.section_html);

		var self = this;
		this.tabs = new DeskPRO.UI.SimpleTabs({
			context: this.sectionEl,
			triggerElements: $('#tickets_outline_tabstrip li'),
			onTabSwitch: function(info) {
				if (info.tabEl.is('.labels')) {
					self.showLabelsList();
				}
			}
		});

		this._initFilters();
		this._initOverview();
		this._initFlagged();

		if (this.isVisible() && !DeskPRO_Window.loadingListFragment) {
			this._loadAutoLoadRoutes();
		}
	},

	//#########################################################################
	// Filters/Inbox
	//#########################################################################

	_initFilters: function() {
		DeskPRO_Window.getPoller().addData(
			[{name: 'do[]', value: 'get-filter-counts'}],
			'filters.counts',
			{recurring: true, minDelay: 60000, minDelayAfterOne: true}
		);
		DeskPRO_Window.getMessageBroker().addMessageListener('filters.counts', this.updateFilterCounts.bind(this));
	},

	getFilterCount: function(filter_id) {
		return parseInt($('#ticket_filter_' + filter_id + '_count').data('count') || 0);
	},

	setFilterCount: function(filter_id, count) {

		var count_str = count;
		filter_id = parseInt(filter_id);

		if (count > 1000) count_str = '1000+';

		var system_name = DeskPRO_Window.getData('systemFilters')[filter_id];
		if (system_name) {

			if (system_name == 'all') {
				this.updateBadge(count);
			}

			var el = $('#ticket_filter_' + filter_id + '_count').html(count_str).data('count', count);
		} else {
			var el = $('#ticket_filter_' + filter_id + '_count').html(count_str).data('count', count);
		}
	},

	updateFilterCounts: function(counts) {
		Object.each(counts, function (count, filter_id) {
			this.setFilterCount(filter_id, count);
		}, this);
	},

	filterUpdated: function(data) {
		var count = this.getFilterCount(data.filter_id);

		var page = null;
		if (this.listPage && this.listPage.meta.filter_id == data.filter_id) {
			page = this.listPage;
		}

		if (data.op == 'add') {
			count++;
			this.setFilterCount(data.filter_id, count);

			if (page && data.ticket_id) {
				page.addTicket(data.ticket_id);
			}

		} else if (data.op == 'del') {
			count--;
			if (count < 1) count = 0;

			this.setFilterCount(data.filter_id, count);

			if (page && data.ticket_id) {
				page.delTicket(data.ticket_id);
			}
		}
	},

	//#########################################################################
	// Grouping
	//#########################################################################

	_initOverview: function() {

		this.overviewGroupMenuEl = null;
		this.overviewGroupEl1 = null;
		this.overviewGroupEl2 = null;
		this.overviewGroupEl2_yes = null;

		this.overviewGroupingMenu = null;
		this.overviewModeMenu = null;

		this._initoverviewGroupingMenu();
		this.overviewLoadList();
	},

	_initoverviewGroupingMenu: function() {

		this.overviewGroupMenuEl   = $('#overview_grouping_menu');
		this.overviewModeMenuEl    = $('#overview_mode_menu');
		this.overviewModeEl        = $('#ticket_grouping_options .mode');
		this.overviewGroupEl1      = $('#ticket_grouping_options .grouping1');
		this.overviewGroupEl2      = $('#ticket_grouping_options .grouping2');
		this.overviewGroupEl2_no   = $('#ticket_grouping_options .no-subgroup');
		this.overviewGroupEl2_yes  = $('#ticket_grouping_options .with-subgroup');

		var self = this;
		this.overviewGroupingMenu = new DeskPRO.UI.Menu({
			triggerElement: $('#ticket_grouping_options .grouping-menu-trigger'),
			menuElement: this.overviewGroupMenuEl,
			onItemClicked: function(info) {
				info.event.stopPropagation();
				self._handleGroupingChanged(info);
			},
			onBeforeMenuOpened: function(info) {
				$('li[data-groupby]', self.overviewGroupMenuEl).show();

				var event = info.menu.getOpenTriggerEvent();
				var triggerEl = $(event.target);

				if (triggerEl.is('.grouping1')) {
					$('li[data-groupby="none"]', self.overviewGroupMenuEl).hide();
				} else {
					var grouping1 = self.overviewGroupEl1.data('groupby');

					// Hide primary grouping form sub-grouping menu
					$('li[data-groupby="'+grouping1+'"]', self.overviewGroupMenuEl).hide();
				}
			}
		});

		this.overviewModeMenu = new DeskPRO.UI.Menu({
			triggerElement: $('#ticket_grouping_options .mode-menu-trigger'),
			menuElement: this.overviewModeMenuEl,
			onItemClicked: function(info) {
				self._handleModeChanged(info);
			}
		});
	},

	_handleModeChanged: function (info) {
		var itemEl = $(info.itemEl);
		var mode = itemEl.data('mode');
		var modeTitle = itemEl.text();

		this.overviewModeEl.text(modeTitle).data('mode', mode);
		this.overviewLoadList();
	},

	_handleGroupingChanged: function(info) {
		var grouping1 = this.overviewGroupEl1.data('groupby');
		var grouping2 = this.overviewGroupEl2.data('groupby');

		var event = info.menu.getOpenTriggerEvent();
		var triggerEl = $(event.target);
		var itemEl = $(info.itemEl);

		if (triggerEl.is('.grouping1')) {
			grouping1 = itemEl.data('groupby');
			if (grouping1 == grouping2) {
				grouping2 = '';
			}
		} else {
			grouping2 = itemEl.data('groupby');
		}

		if (!grouping1 || grouping1 == 'none') grouping1 = 'department';
		if (!grouping2 || grouping2 == 'none') grouping2 = '';

		this.overviewUpdateGrouping(grouping1, grouping2);
		this.overviewLoadList();
	},

	/**
	 * This just updates the page to show proper texts etc for the particular groups
	 */
	overviewUpdateGrouping: function(grouping1, grouping2) {
		var grouping1_menuItemEl = $('[data-groupby="'+grouping1+'"]', this.overviewGroupMenuEl);

		if (grouping2 && grouping2.length) {
			var grouping2_menuItemEl = $('[data-groupby="'+grouping2+'"]', this.overviewGroupMenuEl);;
		} else {
			grouping2 = false;
			var grouping2_menuItemEl = $();
		}

		// Update
		this.overviewGroupEl1.html(grouping1_menuItemEl.html()).data('groupby', grouping1);

		if (grouping2) {
			this.overviewGroupEl2.html(grouping2_menuItemEl.html()).data('groupby', grouping2);
			this.overviewGroupEl2_no.hide();
			this.overviewGroupEl2_yes.show();
		} else {
			this.overviewGroupEl2.html('').data('groupby', '');
			this.overviewGroupEl2_no.show();
			this.overviewGroupEl2_yes.hide();
		}
	},

	/**
	 * This loads the lists for the currently selected group and mode
	 */
	overviewLoadList: function() {

		var grouping1 = this.overviewGroupEl1.data('groupby');
		var grouping2 = this.overviewGroupEl2.data('groupby') || '';
		var mode = this.overviewModeEl.data('mode');

		var data = {
			group1: grouping1,
			group2: grouping2,
			mode: mode
		};

		// Send AJAX
		$.ajax({
			url: BASE_URL + 'agent/ticket-search/overview-nav',
			type: 'GET',
			data: data,
			context: this,
			dataType: 'html',
			success: function(html) {
				this._overviewGroupListLoaded(html);
			}
		});
	},

	_overviewGroupListLoaded: function(html) {
		var list = $('#ticket_grouping_list > ul').html(html);
	},

	//#########################################################################
	// Flagged
	//#########################################################################

	_initFlagged: function() {
		DeskPRO_Window.getPoller().addData(
			[{name: 'do[]', value: 'get-flagged-counts'}],
			'filter-flagged.counts',
			{recurring: true, minDelay: 60000, minDelayAfterOne: true}
		);

		DeskPRO_Window.getMessageBroker().addMessageListener('filter-flagged.counts', this.updateFlagCounts.bind(this));
		DeskPRO_Window.getMessageBroker().addMessageListener('filter-flagged.flag-changed', this.changeFlagCountsForSwitch.bind(this));
	},

	updateFlagCounts: function(counts) {

		$('ol#ticket_flagged_list span.list-counter').html('0');

		Object.each(counts, (function (count, flag) {
			this.updateFlagCountFor(flag, count);
		}).bind(this))
	},

	updateFlagCountFor: function(flag, count) {
		var count_str = count;
		if (count >= 1000) {
			count_str = '1000+';
		} else if (count < 0) {
			count = 0;
			count_str = '0';
		}

		var el = $('#ticket_flag_' + flag + '_count').html(count_str);
	},

	changeFlagCountsForSwitch: function(info) {

		var old_flag_count = parseInt($('#ticket_flag_' + info.old_flag + '_count').html());
		var new_flag_count = parseInt($('#ticket_flag_' + info.new_flag + '_count').html());

		this.updateFlagCountFor(info.old_flag, old_flag_count-1);
		this.updateFlagCountFor(info.new_flag, new_flag_count+1);
	},

	//#########################################################################
	// Labels
	//#########################################################################

	showLabelsList: function() {

		if (this.hasLoadedLabels) return;

		this.hasLoadedLabels = true;

		$.ajax({
			timeout: 20000,
			type: 'POST',
			url: BASE_URL + 'agent/ticket-search/labels-index-pane',
			dataType: 'html',
			context: this,
			success: function(html) {
				$('#tickets_outline_labels').html(html)
			}
		});
	}
});