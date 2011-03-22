Orb.createNamespace('DeskPRO.Agent.WindowElement.MainMenu');

DeskPRO.Agent.WindowElement.MainMenu.Tickets = new Class({
	Extends: DeskPRO.Agent.WindowElement.MainMenu.Abstract,

	init: function () {

		// Sends ajax to fetch initial data
		this._initInitialData();
		this._initBeforeData();

		var self = this;
		this.addEvent('clickRoute', function(event, evData) {
			if (self.cancelClickActivateFilter || self.cancelClickActivateFlag) {
				evData.cancelClose = true;
			}
		});

		this.addEvent('menuFirstOpen', this.resetFilterScroller.bind(this));
		this.addEvent('menuOpen', this.resetGroupScroller.bind(this));
	},

	_initBeforeData: function() {
		this.slideHandler = new DeskPRO.Agent.WindowElement.MainMenuSlider({
			menuLi: this.buttonEl
		});
		this._initLabelsSwitcher();
	},

	// we use a counter to make sure initAfterInitialData is only fired once, after all panes are loaded
	_initerCount: 0,
	_initInitialData: function() {
		this._initerCount++;
		$.ajax({
			url: BASE_URL + 'agent/ticket-search/filters-pane',
			dataType: 'html',
			context: this,
			success: function(html) {
				$('ol#filters_list').html(html);
				this._initerCount--;
				this.resetFilterScroller();
				this._initAfterInitialData();
			}
		});

		this._initerCount++;
		$.ajax({
			url: BASE_URL + 'agent/ticket-search/flagged-pane',
			dataType: 'html',
			context: this,
			success: function(html) {
				$('ol#flagged_list').html(html);
				this._initerCount--;
				this._initAfterInitialData();
			}
		});

		this._initerCount++;
		$.ajax({
			url: BASE_URL + 'agent/ticket-search/labels-pane',
			dataType: 'html',
			context: this,
			success: function(html) {
				$('ol#labels_cloud_list').html(html);
				this._initerCount--;
				this._initAfterInitialData();
			}
		});
	},


	/**
	 * After all ajax calls from initInitialData is done, we
	 * can initiate the actual sections.
	 */
	_initAfterInitialData: function() {
		if (this._initerCount > 0) return; //notyet

		this._initSearchSwitcher();

		this._initFilters();
		this._initFlagged();
		this._initOverview();

		// Send poller now
		(function() {
			DeskPRO_Window.getPoller().send();
		}).delay(500);
	},



	//#########################################################################
	// Filter functionality
	//#########################################################################

	/**
	 * When true, clicking on a route is cancelled because
	 * it was fired by a drag+drop, not an actual click.
	 */
	cancelClickActivateFilter: false,

	_initFilters: function() {
		DeskPRO_Window.getPoller().addData(
			[{name: 'do[]', value: 'get-filter-counts'}],
			'filters.counts',
			{recurring: true, minDelay: 15000, minDelayAfterOne: true}
		);

		DeskPRO_Window.getMessageBroker().addMessageListener('filters.counts', this.updateFilterCounts.bind(this));

		// Drag+drop to reorder
		var self = this;
		$('ol#filters_list').sortable({
			'axis': 'y',
			'distance': 8,
			'deactivate': function() {
				self.cancelClickActivateFilter = true;
			},
			'update': function() {
				self.cancelClickActivateFilter = false;
				self.saveFilterOrder();
			}
		});
	},

	resetFilterScroller: function() {
		var wrap = $('#filters_list_wrap');

		var viewport = $('#filters_list_wrap > .viewport');
		var list = $('#filters_list');

		if (list.outerHeight() < 200) {
			wrap.addClass('scrollbar-disabled');
			viewport.height(list.outerHeight() + 12);
		} else {
			wrap.removeClass('scrollbar-disabled');
			viewport.height(200);
		}

		wrap.tinyscrollbar();
	},

	saveFilterOrder: function() {
		var data = [];

		$('ol#filters_list > li').each(function() {
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
	},

	/**
	 * When the poller comes back with new counts, we should update the UI with them.
	 *
	 * @param {Object} counts
	 */
	updateFilterCounts: function(counts) {
		var badgeCount = 0;

		Object.each(counts, function (count, filter_id) {
			var count_str = count;
			filter_id = parseInt(filter_id);
			if (count >= 1000) count_str = '1000+';

			var system_name = DeskPRO_Window.getData('systemFilters')[filter_id];
			if (system_name) {

				if (system_name == 'all') {
					badgeCount = count;
				}

				var el = $('#filter_' + system_name + '_count').html(count_str);
			} else {
				var el = $('#filter_' + filter_id + '_count').html(count_str);
			}
		});

		this.updateBadge(badgeCount);
	},

	//#########################################################################
	// Flags functionality
	//#########################################################################

	/**
	 * When true, clicking on a route is cancelled because
	 * it was fired by a drag+drop, not an actual click.
	 */
	cancelClickActivateFlag: false,

	_initFlagged: function() {

		DeskPRO_Window.getPoller().addData(
			[{name: 'do[]', value: 'get-flagged-counts'}],
			'filter-flagged.counts',
			{recurring: true, minDelay: 60000, minDelayAfterOne: true}
		);

		DeskPRO_Window.getMessageBroker().addMessageListener('filter-flagged.counts', this.updateFlagCounts.bind(this));
		DeskPRO_Window.getMessageBroker().addMessageListener('filter-flagged.flag-changed', this.changeFlagCountsForSwitch.bind(this));

		// Drag+drop to reorder
		var self = this;
		$('ol#flagged_list').sortable({
			'axis': 'y',
			'distance': 8,
			'deactivate': function() {
				self.cancelClickActivateFlag = true;
			},
			'update': function() {
				self.cancelClickActivateFlag = false;
				self.saveFlagOrder();
			}
		});

		// Editable
		$('#flagged_edit_btn').click(this.startFlagEdit.bind(this));
		$('#flagged_save_btn').click(this.saveFlagEdit.bind(this));
	},

	saveFlagOrder: function() {
		var data = [];

		$('ol#flagged_list > li').each(function() {
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
	},

	updateFlagCounts: function(counts) {

		$('ol#flagged_list span.list-counter').html('0');

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

		var el = $('#flag_' + flag + '_count').html(count_str);
	},

	changeFlagCountsForSwitch: function(info) {

		var old_flag_count = parseInt($('#flag_' + info.old_flag + '_count').html());
		var new_flag_count = parseInt($('#flag_' + info.new_flag + '_count').html());

		this.updateFlagCountFor(info.old_flag, old_flag_count-1);
		this.updateFlagCountFor(info.new_flag, new_flag_count+1);
	},

	startFlagEdit: function() {
		$('#flagged_list > li').each(function() {
			var li = $(this);
			li.children().hide();

			var name = $('> a', li).text().trim();
			var flag = li.data('flag');

			var input = $('<input type="input" name="'+flag+'" />');
			input.val(name);
			input.click(function(ev) { ev.stopPropagation(); });

			li.append(input);
		});

		$('#flagged_edit_btn').hide();
		$('#flagged_save_btn').show();
	},

	saveFlagEdit: function() {
		var data = [];

		$('#flagged_list > li').each(function() {
			var li = $(this);
			var input = $('> input', li);
			if (!input.val().trim().length) {
				input.val(input.attr('name'));
			}

			$('> a', li).text(input.val());
			data.push({ name: 'prefs[agent.ui.flag.'+input.attr('name')+']', value: input.val()});

			input.remove();
			li.children().show();
		});

		$('#flagged_edit_btn').css({ 'display': ''});
		$('#flagged_save_btn').hide();

		$.ajax({
			timeout: 20000,
			type: 'POST',
			url: BASE_URL + 'agent/misc/ajax-save-prefs',
			data: data
		});
	},

	//#########################################################################
	// Overview functionality
	//#########################################################################

	_initOverview: function() {
		this._initoverviewGroupingMenu();
		this.overviewLoadList();
	},

	overviewGroupMenuEl: null,
	overviewGroupEl1: null,
	overviewGroupEl2: null,
	overviewGroupEl2_yes: null,

	overviewGroupingMenu: null,
	overviewModeMenu: null,
	_initoverviewGroupingMenu: function() {

		this.overviewGroupMenuEl   = $('#overview_grouping_menu');
		this.overviewModeMenuEl    = $('#overview_mode_menu');
		this.overviewModeEl        = $('#grouping_options .mode');
		this.overviewGroupEl1      = $('#grouping_options .grouping1');
		this.overviewGroupEl2      = $('#grouping_options .grouping2');
		this.overviewGroupEl2_no   = $('#grouping_options .no-subgroup');
		this.overviewGroupEl2_yes  = $('#grouping_options .with-subgroup');

		var self = this;
		this.overviewGroupingMenu = new DeskPRO.UI.Menu({
			triggerElement: $('#grouping_options .grouping-menu-trigger'),
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
			triggerElement: $('#grouping_options .mode-menu-trigger'),
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

		DeskPRO_Window.startLoadingIndicator();

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

		DeskPRO_Window.stopLoadingIndicator();

		var list = $('#overview_list').html(html);

		this.resetGroupScroller();
	},

	resetGroupScroller: function() {
		var wrap = $('#overview_list_wrap');

		var viewport = $('#overview_list_wrap > .viewport');
		var list = $('#overview_list');

		if (list.outerHeight() < 200) {
			wrap.addClass('scrollbar-disabled');
			viewport.height(list.outerHeight() + 12);
		} else {
			wrap.removeClass('scrollbar-disabled');
			viewport.height(200);
		}

		wrap.tinyscrollbar();
	},

	//#########################################################################
	// Labels
	//#########################################################################

	_initLabelsSwitcher: function() {

		var self = this;
		this.slideHandler.addEvent('view', function(slide) {
			if (slide.attr('id') != 'tickets_labels_list_section') return;
			self.showLabelsList();
		});
	},

	showLabelsList: function() {

		this.resetLablesIndexScroller();

		$.ajax({
			timeout: 20000,
			type: 'POST',
			url: BASE_URL + 'agent/ticket-search/labels-index-pane',
			dataType: 'html',
			context: this,
			success: function(html) {
				$('#ticket_labels_index_content').html(html)
				this.resetLablesIndexScroller();
			}
		});
	},

	resetLablesIndexScroller: function() {
		var wrap = $('#ticket_labels_index_content_wrap');

		var viewport = $('#ticket_labels_index_content_wrap > .viewport');
		var list = $('#ticket_labels_index_content');

		var height_thresh = $('#tickets_labels_list_section').height() - 42;
		viewport.height(height_thresh);

		wrap.tinyscrollbar();

		if ($('> .scrollbar', wrap).is('.disable')) {
			wrap.addClass('scrollbar-disabled');
		} else {
			wrap.removeClass('scrollbar-disabled');
		}
	},

	//#########################################################################
	// Search
	//#########################################################################

    _initSearchSwitcher: function() {

		var self = this;
		this.slideHandler.addEvent('beforeView', function(slide) {
			if (slide.attr('id') != 'tickets_search_section') return;
			$('#tickets_search_section .search-form .search-terms').empty();
		});
		this.slideHandler.addEvent('view', function(slide) {
			if (slide.attr('id') != 'tickets_search_section') return;
			self.resetSearchScroller();
		});

        // The terms build
        // Set up search builder
		var editor = new DeskPRO.Form.RuleBuilder($('#tickets_search_section_content .search-builder-tpl'));
		editor.addEvent('newRow', function(new_row) {
			$('.remove', new_row).click(function() {
				new_row.remove();
				self.resetSearchScroller();
			});
		});
		$('#tickets_search_section .add-term').data('add-count', 0).click(function() {
			var count = parseInt($(this).data('add-count'));
			var basename = 'terms['+count+']';

			$(this).data('add-count', count+1);

			editor.addNewRow($('#tickets_search_section_content .search-terms'), basename);
			self.resetSearchScroller();
		});

		$('#tickets_search_form').submit((function(ev) {
			ev.preventDefault();

			var form = $('#tickets_search_form');
			var url = form.attr('action');
			console.log(form);
			console.log(url);

			var data = form.serializeArray();

			DeskPRO_Window.loadListPane(url, { postData: data });

			this.closeMenu();
		}).bind(this));
	},

    resetSearchScroller: function(type) {

		var wrap = $('#tickets_search_section_wrap');

		var viewport = $('#tickets_search_section_wrap > .viewport');
		var list = $('#tickets_search_section_content');

		var height_thresh = $('#tickets_search_section').height() - 42;
		viewport.height(height_thresh);

		wrap.tinyscrollbar();

		if ($('> .scrollbar', wrap).is('.disable')) {
			wrap.addClass('scrollbar-disabled');
		} else {
			wrap.removeClass('scrollbar-disabled');
		}
	}
});