Orb.createNamespace('DeskPRO.Agent.WindowElement.Section');

DeskPRO.Agent.WindowElement.Section.Tickets = new Orb.Class({
	Extends: DeskPRO.Agent.WindowElement.Section.AbstractSection,

	init: function() {
		this.buttonEl = $('#tickets_section');

		this.setSectionElement($('<section id="tickets_outline"></section>'));

		DeskPRO_Window.getMessageChanneler().subscribeChannel('agent.filter-update', this.filterUpdated.bind(this));

		DeskPRO_Window.getMessageChanneler().subscribeChannel('list-page-fragment.activated', this.highlightActiveSection.bind(this));

		// Simulate instant switching when clicking nav items
		var self = this;
		this.getSectionElement().delegate('[data-route]', 'click', function(ev) {
			self.highlightNavItem($(this));
		});

		this.filterTicketIds = {};

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

		this.contentEl.html(data.section_html);

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

		this.filterTicketIds = data.filter_id_matches;

		this._initFilters();
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

		$('.show-hold-check', this.sectionEl).click(function() {
			self.toggleHoldDisplay();
		});

		this.filterGroupEditor = new DeskPRO.Agent.Widget.FilterGroupEditor({
			containerElement: '#tickets_outline .scroll-content',
			listElement: '#tickets_outline_sys_filters',
			boundListElement: '#tickets_outline_sys_hold_filters',
			triggerElement: '#ticket_filter_launch_editor',
			onGroupingChanged: function(filterId) {
				self.refreshFilterGrouping([filterId]);
			}
		});
	},

	onShow: function() {
		this.activeNavClass = null;
	},

	highlightActiveSection: function(info) {

		if (!this.isVisible()) return;

		var page = info.page;

		if (page.TYPENAME == 'ticket-filter') {
			this.activeNavClass = '.nav-filter-' + page.getMetaData('filter_id');
		} else if (page.TYPENAME == 'recyclebin') {
			this.activeNavClass = '.archive-recycle-bin';
		} else if (page.TYPENAME == 'ticket-custom-filter' || page.TYPENAME == 'ticket-flagged') {
			var view_extra = page.getMetaData('view_extra');
			var view_name = page.getMetaData('view_name');
			switch (view_name) {
				case 'flag':
					if (view_extra) {
						this.activeNavClass = '.nav-flag-' + view_extra;
					}
					break;

				case 'label':
					if (view_extra) {
						this.activeNavClass = '.nav-label-' + DeskPRO_Window.util.slugify(view_extra);
					}
					break;

				case 'spam': this.activeNavClass = '.nav-archive-spam'; break;
				case 'validating': this.activeNavClass = '.nav-archive-validating'; break;
				case 'pending': this.activeNavClass = '.nav-archive-pending'; break;
				case 'resolved': this.activeNavClass = '.nav-archive-resolved'; break;
				case 'closed': this.activeNavClass = '.nav-archive-closed'; break;
			}
		}

		this.highlightNav();
	},

	highlightNav: function() {

		if (this.activeNavClass) {
			var el = $(this.activeNavClass, this.getSectionElement());
			var childSel = $('.nav-selected', el);

			if (!childSel.length) {
				$('.nav-selected', this.getSectionElement()).removeClass('nav-selected');
				el.addClass('nav-selected');
			}
		}
	},

	highlightNavItem: function(el) {

		if (!el.is('li')) {
			el = el.closest('li');
		}

		$('.nav-selected', this.getSectionElement()).removeClass('nav-selected');
		el.addClass('nav-selected');
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

		// Init counts based on IDs we have cached
		$('li.filter', this.sectionEl).each((function(i, el) {
			el = $(el);

			var filterId = el.data('filter-id');
			if (this.filterTicketIds[filterId]) {
				this.setFilterCount(filterId, this.filterTicketIds[filterId].length);
			} else {
				this.setFilterCount(filterId, 0);
			}
		}).bind(this));

		this._recountHold();
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

			if (el.is('.is-hold-filter')) {
				this._recountHold();
			}
		} else {
			var el = $('#ticket_filter_' + filter_id + '_count').html(count_str).data('count', count);
		}
	},

	_recountHold: function() {
		var total = 0;
		$('#tickets_outline_sys_hold_filters li.filter', this.sectionEl).each((function(i, el) {
			el = $(el);

			var filterId = el.data('filter-id');
			if (this.filterTicketIds[filterId]) {
				total += this.filterTicketIds[filterId].length;
				this.setFilterCount(filterId, this.filterTicketIds[filterId].length);
			} else {
				this.setFilterCount(filterId, 0);
			}
		}).bind(this));

		$('#tickets_outline_sys_filters li.filter', this.sectionEl).each((function(i, el) {
			el = $(el);

			var filterId = el.data('filter-id');
			if (this.filterTicketIds[filterId]) {
				total -= this.filterTicketIds[filterId].length;
				this.setFilterCount(filterId, this.filterTicketIds[filterId].length);
			} else {
				this.setFilterCount(filterId, 0);
			}
		}).bind(this));

		var hold = $('.hold-ticket-count', this.sectionEl);
		if (total < 1) {
			hold.hide();
		} else {
			$('.count', hold).text(total);
			hold.show();
		}
	},

	updateFilterCounts: function(counts) {
		Object.each(counts, function (count, filter_id) {
			this.setFilterCount(filter_id, count);
		}, this);
	},

	filterUpdated: function(data) {

		var filterId = parseInt(data.filter_id);
		var ticketId = parseInt(data.ticket_id);

		if (!this.filterTicketIds[filterId]) {
			this.filterTicketIds[filterId] = [];
		}

		var page = null;
		if (this.listPage && this.listPage.meta.filter_id == data.filter_id) {
			page = this.listPage;
		}

		if (data.op == 'add') {
			this.filterTicketIds[filterId].include(ticketId);

			var count = this.filterTicketIds[filterId].length;
			this.setFilterCount(data.filter_id, count);

			if (page && data.ticket_id) {
				page.addTicket(data.ticket_id);
			}

		} else if (data.op == 'del') {
			this.filterTicketIds[filterId].erase(ticketId);

			var count = this.filterTicketIds[filterId].length;
			this.setFilterCount(data.filter_id, count);

			if (page && data.ticket_id) {
				page.delTicket(data.ticket_id);
			}
		}

		this.refreshFilterGrouping([filterId]);
	},

	refreshFilterGrouping: function(filterIds) {
		var postData = [];

		var els = [];

		Array.each(filterIds, function(filterId) {
			filterId = parseInt(filterId);
			var filterEl = $('li.filter-' + filterId, this.sectionEl);
			els.push(filterEl.get(0));

			var boundFilterEl = null;
			var boundFilterId = null;
			if (filterEl.data('filter-name')) {
				boundFilterEl = $('.filter-' + filterEl.data('filter-name') + '_w_hold', this.sectionEl);
				boundFilterId = boundFilterEl.data('filter-id');
			}

			if (!this.filterTicketIds[filterId] && (!boundFilterId || !this.filterTicketIds[boundFilterId])) {
				return;
			}

			var grouping = this.getGroupingVar(filterId);

			if (!grouping || !grouping.length) {
				this.setFilterGroupingContent(filterId, '');

				if (boundFilterId) {
					this.setFilterGroupingContent(boundFilterId, '');
				}
				return;
			}

			postData.push({
				name: 'batches['+filterId+'][grouping]',
				value: grouping
			});
			Array.each(this.filterTicketIds[filterId], function(tid) {
				postData.push({
					name: 'batches['+filterId+'][ticket_ids][]',
					value: tid
				});
			});

			if (boundFilterId) {
				if (this.filterTicketIds[boundFilterId]) {
					postData.push({
						name: 'batches['+boundFilterId+'][grouping]',
						value: grouping
					});
					Array.each(this.filterTicketIds[boundFilterId], function(tid) {
						postData.push({
							name: 'batches['+boundFilterId+'][ticket_ids][]',
							value: tid
						});
					});
				} else {
					this.setFilterGroupingContent(boundFilterId, '');
				}
			}
		}, this);

		var countEls = $('.list-counter', $(els)).first();
		countEls.addClass('loading');

		$.ajax({
			url: BASE_URL + 'agent/ticket-search/group-tickets.json',
			type: 'POST',
			dataType: 'json',
			data: postData,
			context: this,
			complete: function() {
				countEls.removeClass('loading');
			},
			success: function(batches) {
				Object.each(batches, function(html,filterId) {
					this.setFilterGroupingContent(filterId, html);
				}, this);
			}
		});
	},

	getGroupingVar: function(filterId) {
		return $('#ticket_filter_group_editor .filter-' + filterId + ' .field-option').val();
	},

	setFilterGroupingContent: function(filterId, html) {
		var filterEl = $('.filter-' + filterId, this.sectionEl);
		var subgroupEl = $('ul.sub-group', filterEl);

		var groupingVar = this.getGroupingVar(filterId);
		var baseRoute = $('.title', filterEl).first().data('route');

		subgroupEl.empty();
		if (html.length) {
			subgroupEl.html(html);
		}

		var lis = $('> li', subgroupEl);
		if (lis.length) {
			subgroupEl.show();

			// Add the proper route to each row
			lis.each(function() {
				var setRoute = Orb.appendQueryData(baseRoute, 'set_group_term', groupingVar);
				setRoute = Orb.appendQueryData(setRoute, 'set_group_option', $(this).data('grouping-option'));
				$('.title', this).first().data('route', setRoute);
				$('.title', this).first().attr('data-route', setRoute);
			});
		} else {
			subgroupEl.hide();
		}
	},

	toggleHoldDisplay: function() {
		var check = $('.show-hold-check', this.getSectionElement());
		check.toggleClass('checked');

		// Go through each one and figure out which ones change
		// We dont need ot do any filter matching, they are always in the same order

		var counts1 = $('#tickets_outline_sys_filters > li > .title > .list-counter');
		var counts2 = $('#tickets_outline_sys_hold_filters > li > .title > .list-counter');

		var els = [];

		counts1.each(function(i) {
			var other = counts2.eq(i);

			var val1 = parseInt($(this).text().trim());
			var val2 = parseInt(other.text().trim());

			if (val1 != val2) {
				els.push(this);
				els.push(other.get(0));
			}
		});

		els = $(els);

		// Fake "loading" indicator gives impression
		// of change
		els.addClass('loading');

		window.setTimeout(function() {
			if (check.is('.checked')) {
				$('#tickets_outline_sys_filters').hide();
				$('#tickets_outline_sys_hold_filters').show();
			} else {
				$('#tickets_outline_sys_hold_filters').hide();
				$('#tickets_outline_sys_filters').show();
			}

			els.removeClass('loading');
		}, 310);
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
			context: this.sectionEl,
			triggerElements: $('#tickets_outline_labels_switcher li')
		});
	}
});
