Orb.createNamespace('DeskPRO.Agent.WindowElement.Section');

DeskPRO.Agent.WindowElement.Section.Tickets = new Orb.Class({
	Extends: DeskPRO.Agent.WindowElement.Section.AbstractSection,

	init: function() {
		this.buttonEl = $('#tickets_section');

		this.setSectionElement($('<section id="tickets_outline"></section>'));

		DeskPRO_Window.getMessageChanneler().subscribeChannel('agent.filter-update', this.filterUpdated.bind(this));
		DeskPRO_Window.getMessageChanneler().subscribeChannel('agent.new-recent-search', this.refreshRecentSearches.bind(this));

		DeskPRO_Window.getMessageChanneler().subscribeChannel('list-page-fragment.activated', this.highlightActiveSection.bind(this));

		// Simulate instant switching when clicking nav items
		var self = this;
		this.getSectionElement().delegate('li[data-route]', 'click', function() {
			self.highlightNavItem($(this));
		});

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
				} else if (info.tabEl.is('.flagged')) {
					self.loadFlagCounts();
				}
			}
		});

		this.inboxViewTabs = new DeskPRO.UI.SimpleTabs({
			triggerElements: $('#tickets_outline_viewtypetabs li')
		});

		this._initFilters();
		//this._initOverview();
		this._initFlagged();

		$('#user_settings_filters_link').click(function() {
			var overlay = new DeskPRO.UI.Overlay({
				contentMethod: 'iframe',
				iframeUrl: BASE_URL + 'agent/settings/ticket-filters'
			});

			overlay.openOverlay();
		});

		if (this.isVisible() && !DeskPRO_Window.loadingListFragment) {
			this._loadAutoLoadRoutes();
		}

		this.activeNavClass = null;
	},

	onShow: function() {
		this.activeNavClass = null;
	},

	highlightActiveSection: function(info) {

		if (!this.isVisible()) return;

		var page = info.page;

		if (page.TYPENAME == 'ticket-filter') {
			this.activeNavClass = '.nav-filter-' + page.getMetaData('filter_id');
		} else if (page.TYPENAME == 'ticket-custom-filter') {
			if (page.getMetaData('recent_search_id')) {
				this.activeNavClass = '.nav-recent-search-' + page.getMetaData('recent_search_id')
			}
		}

		this.highlightNav();
	},

	highlightNav: function() {

		$('.active-nav', this.getSectionElement()).removeClass('active-nav');
		if (this.activeNavClass) {
			$(this.activeNavClass, this.getSectionElement()).addClass('active-nav');
		}
	},

	highlightNavItem: function(el) {
		$('.active-nav', this.getSectionElement()).removeClass('active-nav');
		el.addClass('active-nav');
	},

	//#########################################################################
	// Filters/Inbox
	//#########################################################################

	_initFilters: function() {

		DeskPRO_Window.getPoller().addData(
			[{name: 'do[]', value: 'get-sys-filter-counts'}],
			'filters.counts',
			{recurring: true, minDelay: 120000/*2minutes*/ }
		);
		DeskPRO_Window.getPoller().addData(
			[{name: 'do[]', value: 'get-custom-filter-counts'}],
			'filters.counts',
			{recurring: true, minDelay: 600000/*10 mintues*/, minDelayAfterOne:true }
		);

		DeskPRO_Window.getMessageBroker().addMessageListener('filters.counts', this.updateFilterCounts.bind(this));

		$('ul#tickets_outline_filters_list').sortable({
			'axis': 'y',
			'distance': 8,
			'update': function() {
				var data = [];

				$('ul#tickets_outline_filters_list > li').each(function() {
					var id = $(this).data('filter-id');
					if (id) {
						data.push({ name: 'prefs[agent.ui.ticket-filters-order][]', value: id });
					}
				});

				$.ajax({
					timeout: 20000,
					type: 'POST',
					url: BASE_URL + 'agent/misc/ajax-save-prefs',
					data: data
				});
			}
		});

		$('#tickets_outline_inbox_list .sub-toggle').click(function(ev) {
			ev.stopPropagation();
			var li = $(this).parent();
			var sub = $('ul.sub-group', li);

			if (sub.is(':visible')) {
				sub.slideUp();
				$(this).removeClass('open');
			} else {
				sub.slideDown();
				$(this).addClass('open');
			}
		});
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

	refreshRecentSearches: function() {
		$.ajax({
			url: BASE_URL + 'agent/ticket-search/get-recent-search-list',
			type: 'GET',
			context: this,
			dataType: 'html',
			success: function(html) {
				$('#tickets_outline_searches_list').empty().html(html);
				this.highlightNav(); //the list was replaced, so have to re-highlight it
			}
		});
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

		} else {
			this.overviewGroupEl2.html('none').data('groupby', '');
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
		DeskPRO_Window.getMessageBroker().addMessageListener('filter-flagged.counts', this.updateFlagCounts.bind(this));
		DeskPRO_Window.getMessageBroker().addMessageListener('filter-flagged.flag-changed', this.changeFlagCountsForSwitch.bind(this));

		//------------------------------
		// Reorder flags
		//------------------------------

		var self = this;
		$('#tickets_outline_flagged ul').sortable({
			'axis': 'y',
			'distance': 8,
			'update': function() {
				var data = [];

				$('#tickets_outline_flagged ul > li').each(function() {
					var flag = $(this).data('flag');
					if (flag) {
						data.push({ name: 'prefs[agent.ui.ticket-flag-order][]', value: flag });
					}
				});

				$.ajax({
					timeout: 20000,
					type: 'POST',
					url: BASE_URL + 'agent/misc/ajax-save-prefs',
					data: data
				});
			}
		});

		//------------------------------
		// Renaming flags
		//------------------------------

		$('#tickets_outline_flagged li').dblclick(function(ev) {
			ev.preventDefault();
			ev.stopPropagation();

			var li = $(this);

			var inputEl = $('<input type="text" />');
			inputEl.val($('a', li).text().trim());

			var enterCloseFn = function(ev) {
				if (ev.keyCode == 13 && !ev.metaKey) {
					closeFn();
				}
			}
			var closeFn = function() {

				var newTitle = inputEl.val().trim();
				if (newTitle.length) {
					$.ajax({
						type: 'POST',
						url: BASE_URL + 'agent/misc/ajax-save-prefs',
						data: [{
							name: 'prefs[agent.ui.flag.' + li.data('flag') + ']',
							value: newTitle
						}]
					});

					$('a', li).text(newTitle);
				}

				backdrop.remove();
				wrapper.remove();
			};
			
			var backdrop = $('<div class="backdrop"></div>');
			backdrop.appendTo('body');
			backdrop.click(closeFn);

			var wrapper = $('<div class="field-overlay"><div class="close-trigger"></div></div>');
			inputEl.appendTo(wrapper);
			wrapper.css({
				left: li.offset().left,
				top: li.offset().top
			});
			wrapper.appendTo('body').show();
			inputEl.keypress(enterCloseFn).focus();

			$('.close-trigger', wrapper).click(closeFn);
		});
	},

	loadFlagCounts: function() {
		$.ajax({
			url: BASE_URL + 'agent/tickets/get-flagged-section-data.json',
			context: this,
			success: function(data) {
				this.updateFlagCounts(data.flag_counts);
			}
		});
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
				this._setLabelsList(html);
			}
		});
	},

	_setLabelsList: function(html) {
		$('#tickets_outline_labels').html(html);

		this.labelsTabs = new DeskPRO.UI.SimpleTabs({
			context: $('#tickets_outline_labels'),
			triggerElements: $('#tickets_outline_labels .deskpro-sub-tabstrip li')
		});
	}
});