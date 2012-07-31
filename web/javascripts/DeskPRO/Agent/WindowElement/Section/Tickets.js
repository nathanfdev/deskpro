Orb.createNamespace('DeskPRO.Agent.WindowElement.Section');

DeskPRO.Agent.WindowElement.Section.Tickets = new Orb.Class({
	Extends: DeskPRO.Agent.WindowElement.Section.AbstractSection,

	init: function() {
		this.archiveFilterIds = [];
		this.buttonEl = $('#tickets_section');
		this.filterTicketIds = {};

		this.urlFragmentName = 'tickets';

		this.setSectionElement($('<section id="tickets_outline"></section>'));

		DeskPRO_Window.getSectionData('tickets_section', this._initSection.bind(this));
		DeskPRO_Window.getMessageBroker().addMessageListener('agent.filter-update', this.filterUpdated, this);
		DeskPRO_Window.getMessageBroker().addMessageListener('ticket-section.list-activated', function (info) {
			this.highlightNavItem($('.filter-' + info.id, this.getSectionElement()), info.topGroupingOption || null);
		}, this);

		this.lastArchiveUpdate = new Date();
	},

	_initSection: function(data) {

		this.setHasInitialLoaded();

		this.contentEl.html(data.section_html);

		var self = this;
		this.tabs = new DeskPRO.UI.SimpleTabs({
			context: this.sectionEl,
			triggerElements: $('#tickets_outline_tabstrip li'),
			onTabSwitch: function(info) {
			}
		});

		this.filterTicketIds = data.filter_id_matches;
		var archiveFilterIds = [];
		$('#tickets_outline_archive').find('li.is-archive-filter').each(function() {
			archiveFilterIds.push($(this).data('filter-id'));
		});
		this.archiveFilterIds = archiveFilterIds;

		this._initFilters();
		this._initFlagged();

		$('#user_settings_filters_link').on('click', function() {
			var overlay = new DeskPRO.UI.Overlay({
				contentMethod: 'iframe',
				iframeUrl: BASE_URL + 'agent/settings/ticket-filters'
			});

			overlay.openOverlay();
		});

		this.activeNavClass = null;

		$('.hold-ticket-count', this.sectionEl).on('click', function() {
			self.toggleHoldDisplay();
		});

		this.filterGroupEditor = new DeskPRO.Agent.Widget.FilterGroupEditor({
			containerElement: '#tickets_outline .scroll-content',
			listElement: '#tickets_outline_sys_filters',
			boundListElement: '#tickets_outline_sys_hold_filters',
			triggerElement: '#ticket_filter_launch_editor',
			controlElement: '#ticket_filter_group_editor',
			onGroupingChanged: function(filterId) {
				self.refreshFilterGrouping([filterId], true);
			}
		});

		var initialGrouped = $('#tickets_outline_sys_filters .filter[data-initial-grouping]');
		if (initialGrouped.length) {
			var fids = [];
			initialGrouped.each(function() {
				fids.push($(this).data('filter-id'));
			});

			self.filterGroupEditor._initControl();
			self.refreshFilterGrouping(fids);
		}

		if ($('#tickets_outline_custom_filters .filter').not('.filter-hidden').length) {
			$('#tickets_outline_custom_filters .no-data').hide();
		} else {
			$('#tickets_outline_custom_filters .no-data').show();
		}

		this.customFilterGroupEditor = new DeskPRO.Agent.Widget.FilterOptionsPop({
			containerElement: '#tickets_outline .scroll-content',
			listElement: '#tickets_outline_custom_filters',
			triggerElement: $('.launch-customfilters-editor', this.contentEl),
			onInit: function(ed) {
				ed.controlRealEl.on('click', ':checkbox', function() {
					var row = $(this).closest('.filter-row');
					var filter_id = parseInt(row.data('filter-id'));

					var filter_row = $('#tickets_outline_custom_filters .filter-' + filter_id);
					if ($(this).is(':checked')) {
						filter_row.removeClass('filter-hidden');
					} else {
						filter_row.addClass('filter-hidden');
					}
				});
			},
			onInitRow: function(row, filter_id, ed) {
				var filter_row = $('#tickets_outline_custom_filters .filter-' + filter_id);
				if (filter_row.is('.filter-hidden')) {
					$(':checkbox', row).attr('checked', false);
				}
			},
			onPreOpen: function(ed) {
				$('#tickets_outline_custom_filters li.filter-hidden').show();
				$('#tickets_outline_custom_filters').addClass('ed-open');

				$('#tickets_outline_custom_filters .no-data').hide();
			},
			onClose: function(ed) {
				$('#tickets_outline_custom_filters li.filter-hidden').slideUp(300);
				window.setTimeout(function() {
					$('#tickets_outline_custom_filters').removeClass('ed-open');

					if ($('#tickets_outline_custom_filters .filter').not('.filter-hidden').length) {
						$('#tickets_outline_custom_filters .no-data').hide();
					} else {
						$('#tickets_outline_custom_filters .no-data').show();
					}

				}, 310);

				var postData = [];
				$('#tickets_outline_custom_filters li.filter').each(function() {
					var id = parseInt($(this).data('filter-id'));
					var v;

					if ($(this).is('.filter-hidden')) {
						v = 'hidden';
					} else {
						v = '';
					}

					postData.push({
						name: 'prefs[agent.ui.filter-visibility.' + id + ']',
						value: v
					});
				});

				$.ajax({
					type: 'POST',
					url: BASE_URL + 'agent/misc/ajax-save-prefs',
					data: postData
				});
			}
		});

		$('.launch-customfilters-settings', this.contentEl).on('click', function() {
			$('#settingswin').trigger('dp_open', 'filters');
		});

		DeskPRO.ElementHandler_Exec(this.wrapper);

		this.fireEvent('sectionInit');
	},

	onShow: function() {
		if (!this.hasLoaded) {
			DeskPRO_Window.getSectionData('tickets_section', this._initSection.bind(this));
		}
		this.activeNavClass = null;
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

	highlightNavItem: function(el, topGroupingOption) {

		if (!el.is('li')) {
			el = el.closest('li');
		}

		if (topGroupingOption) {
			el = $('.grouping-' + topGroupingOption, el);
		}

		$('.nav-selected', this.getSectionElement()).removeClass('nav-selected');
		el.addClass('nav-selected');
	},

	//#########################################################################
	// Filters/Inbox
	//#########################################################################

	_initFilters: function() {
		var self = this;
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

		DeskPRO_Window.getMessageBroker().addMessageListener('filters.counts', this.updateFilterCounts, this);

		DeskPRO_Window.getMessageBroker().addMessageListener('agent.ticket-updated', function (data) {
			var ticketId = data.ticket_id;

			if (!data.via_person || data.via_person != DESKPRO_PERSON_ID) {
				var tab = DeskPRO_Window.getTabWatcher().findTab('ticket', function(tab) {
					if (tab && tab.page && tab.page && tab.page.meta.ticket_id == ticketId) {
						return true;
					}

					return false;
				});

				if (tab) {
					tab.page.doTicketUpdate();
				}
			}

			// And if we're viewing any groups affected by the changed field, then we need to reload the group
			if ($('.show-hold-check', self.getSectionElement()).hasClass('checked')) {
				var filterIds = self.archiveFilterIds;
			} else {
				var filterIds = self.filterTicketIds;
			}

			var refreshFilterIds = [];

			$.each(filterIds, function(filterId) {
				var grouping = self.getGroupingVar(filterId);
				if (!grouping) {
					return;
				}

				if (data.changed_fields.indexOf(grouping) !== -1) {
					refreshFilterIds.push(filterId);
				}
			});

			if (refreshFilterIds.length) {
				self.refreshFilterGrouping(refreshFilterIds);
			}
		});

		$('#tickets_outline_inbox_list .sub-toggle').on('click', function(ev) {
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
		$('li.filter', this.sectionEl).not('.is-archive-filter').each((function(i, el) {
			el = $(el);

			var filterId = parseInt(el.data('filter-id'));
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

	modFilterCount: function(filter_id, op) {
		filter_id = parseInt(filter_id);
		var count = parseInt($('#ticket_filter_' + filter_id + '_count').text().trim());

		if (op == 'add') {
			count++;
		} else {
			count--;
		}

		if (count < 0) {
			count = 0;
		}

		var el = $('#ticket_filter_' + filter_id + '_count').html(count).data('count', count);
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

			var filterId = parseInt(el.data('filter-id'));
			if (this.filterTicketIds[filterId]) {
				if (el.hasClass('filter-all_w_hold')) {
					total += this.filterTicketIds[filterId].length;
				}
				this.setFilterCount(filterId, this.filterTicketIds[filterId].length);
			} else {
				this.setFilterCount(filterId, 0);
			}
		}).bind(this));

		$('#tickets_outline_sys_filters li.filter', this.sectionEl).each((function(i, el) {
			el = $(el);

			var filterId = parseInt(el.data('filter-id'));
			if (this.filterTicketIds[filterId]) {
				this.setFilterCount(filterId, this.filterTicketIds[filterId].length);
			} else {
				this.setFilterCount(filterId, 0);
			}
		}).bind(this));

		var hold = $('.hold-ticket-count', this.sectionEl);
		if (total < 1) {
			hold.hide();
			if ($('.show-hold-check', this.getSectionElement()).hasClass('checked')) {
				this.toggleHoldDisplay();
			}
		} else {
			$('.count', hold).text(total);
			hold.show();
		}
	},

	updateFilterCounts: function(counts) {
		Object.each(counts, function (count, filter_id) {
			if (this.archiveFilterIds.indexOf(filter_id) != -1) {
				return;
			}
			this.setFilterCount(filter_id, count);
		}, this);

		this._recountHold();
	},

	filterUpdated: function(data) {

		var filterId = parseInt(data.filter_id);
		var ticketId = parseInt(data.ticket_id);

		if (!this.filterTicketIds[filterId]) {
			this.filterTicketIds[filterId] = [];
		}

		var page = null;
		if (this.listPage && parseInt(this.listPage.meta.filter_id) == filterId) {
			page = this.listPage;
		}

		if (data.op == 'add') {
			if (this.archiveFilterIds.indexOf(filterId) != -1) {
				this.modFilterCount(filterId, 'add');
			} else {
				this.filterTicketIds[filterId].include(ticketId);

				var count = this.filterTicketIds[filterId].length;
				this.setFilterCount(filterId, count);
			}

			if (page && ticketId) {
				page.addTicket(ticketId);
			}

		} else if (data.op == 'del') {
			if (this.archiveFilterIds.indexOf(filterId) != -1) {
				this.modFilterCount(filterId, 'del');
			} else {
				this.filterTicketIds[filterId].erase(ticketId);

				var count = this.filterTicketIds[filterId].length;
				this.setFilterCount(filterId, count);
			}

			if (page && ticketId) {
				page.delTicket(ticketId);
			}
		}

		this.refreshFilterGrouping([filterId]);
		this._recountHold();
	},

	refreshFilterGrouping: function(filterIds, doSave) {
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
				boundFilterId = parseInt(boundFilterEl.data('filter-id'));
			}

			if (!this.filterTicketIds[filterId] && (!boundFilterId || !this.filterTicketIds[boundFilterId])) {
				return;
			}

			var grouping = this.getGroupingVar(filterId);

			if (!grouping || !grouping.length) {
				if (!doSave) {
					this.setFilterGroupingContent(filterId, '', grouping);

					if (boundFilterId) {
						this.setFilterGroupingContent(boundFilterId, '', grouping);
					}
					return;
				}
			}

			postData.push({
				name: 'batches['+filterId+'][grouping]',
				value: grouping
			});

			if (grouping) {
				Array.each(this.filterTicketIds[filterId], function(tid) {
					postData.push({
						name: 'batches['+filterId+'][ticket_ids][]',
						value: tid
					});
				});
			}

			if (boundFilterId) {
				if (this.filterTicketIds[boundFilterId]) {
					postData.push({
						name: 'batches['+boundFilterId+'][grouping]',
						value: grouping
					});

					if (grouping) {
						Array.each(this.filterTicketIds[boundFilterId], function(tid) {
							postData.push({
								name: 'batches['+boundFilterId+'][ticket_ids][]',
								value: tid
							});
						});
					}
				} else {
					this.setFilterGroupingContent(boundFilterId, '', grouping);
				}
			}
		}, this);

		if (doSave) {
			postData.push({
				name: 'save_pref',
				value: 1
			});
		}

		// Nothing to do
		if (!postData.length) {
			return;
		}

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

					var filterEl = $('.filter-' + filterId, this.sectionEl);
					var name = filterEl.data('filter-name');
					if (name.indexOf('_w_hold') !== -1) {
						name = name.replace(/_w_hold$/, '');
						parentFilterEl = $('.filter-' + name, this.sectionEl);
						parentFilterId = parseInt(parentFilterEl.data('filter-id'));
						var grouping = this.getGroupingVar(parentFilterId);
					} else {
						var grouping = this.getGroupingVar(filterId);
					}

					var selectedGrouping = filterEl.find('ul.sub-group li.nav-selected').data('grouping-option');
					this.setFilterGroupingContent(filterId, html, grouping);

					if (selectedGrouping != 'undefined') {
						filterEl = $('.filter-' + filterId, this.sectionEl);
						var li = filterEl.find('li.grouping-' + selectedGrouping);
						if (li[0]) {
							DeskPRO_Window.runPageRouteFromElement(li.find('.is-nav-item'));
							li.addClass('nav-selected');
						}
					}
				}, this);
			}
		});
	},

	getGroupingVar: function(filterId) {
		return $('#ticket_filter_group_editor .filter-' + filterId + ' .field-option').val();
	},

	setFilterGroupingContent: function(filterId, html, grouping) {
		var filterEl = $('.filter-' + filterId, this.sectionEl);
		var subgroupEl = $('ul.sub-group', filterEl);

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
				var setRoute = Orb.appendQueryData(baseRoute, 'set_group_term', grouping);
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

		var selectedIndex = null;

		counts1.each(function(i) {

			var other = counts2.eq(i);

			if (selectedIndex === null && ($(this).parent().parent().is('.nav-selected') || other.parent().parent().is('.nav-selected'))) {
				selectedIndex = i;
			}

			var val1 = parseInt($(this).text().trim());
			var val2 = parseInt(other.text().trim());

			if (val1 != val2) {
				$('.list-counter', $(this).parent().parent()).each(function() {
					els.push(this);
				});
				$('.list-counter', other.parent().parent()).each(function() {
					els.push(this);
				});
			}
		});

		els = $(els);

		// Fake "loading" indicator gives impression
		// of change
		els.addClass('loading');

		if (selectedIndex !== null) {
			if (check.is('.checked')) {
				var runEl = $('#tickets_outline_sys_hold_filters li').eq(selectedIndex).addClass('nav-selected');
			} else {
				var runEl = $('#tickets_outline_sys_filters li').eq(selectedIndex).addClass('nav-selected');
			}

			DeskPRO_Window.runPageRouteFromElement($('h3', runEl).first());
		}

		window.setTimeout(function() {

			if (check.is('.checked')) {
				$('#tickets_outline_sys_filters').hide();
				$('#tickets_outline_sys_hold_filters').show();

				$('#tickets_outline_sys_filters li.nav-selected').removeClass('nav-selected');
			} else {
				$('#tickets_outline_sys_hold_filters').hide();
				$('#tickets_outline_sys_filters').show();

				$('#tickets_outline_sys_hold_filters li.nav-selected').removeClass('nav-selected');
			}

			els.removeClass('loading');
		}, 310);
	},

	//#########################################################################
	// Flagged
	//#########################################################################

	_initFlagged: function() {
		DeskPRO_Window.getMessageBroker().addMessageListener('filter-flagged.counts', this.updateFlagCounts, this);
		DeskPRO_Window.getMessageBroker().addMessageListener('filter-flagged.flag-changed', this.changeFlagCountsForSwitch, this);

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

		$('#tickets_outline_flagged li').on('dblclick', function(ev) {
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
			backdrop.on('click', closeFn);

			var wrapper = $('<div class="field-overlay"><div class="close-trigger"></div></div>');
			inputEl.appendTo(wrapper);
			wrapper.css({
				left: li.offset().left,
				top: li.offset().top
			});
			wrapper.appendTo('body').show();
			inputEl.on('keypress', enterCloseFn).focus();

			$('.close-trigger', wrapper).on('click', closeFn);
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

		var el = $('#ticket_flag_' + flag + '_count').text(count_str);
	},

	changeFlagCountsForSwitch: function(info) {
		if (info.old_flag) {
			var old_flag_count = parseInt($('#ticket_flag_' + info.old_flag + '_count').text());
			this.updateFlagCountFor(info.old_flag, old_flag_count-1);
		}

		var new_flag_count = parseInt($('#ticket_flag_' + info.new_flag + '_count').text());
		this.updateFlagCountFor(info.new_flag, new_flag_count+1);
	},

	//#########################################################################
	// Misc
	//#########################################################################

	/**
	 * Remove a filter row. This remove doesnt need to be perfect, its used from the settings window
	 * when you update filters. After the settings overlay is closed, the page is refreshed, this is just
	 * instant feedback.
	 */
	removeCustomFilter: function(id) {
		$('#tickets_outline_custom_filters').find('li.filter-' + id).remove();
		if (!$('#tickets_outline_custom_filters').find('li.filter')[0]) {
			$('#tickets_outline_custom_filters').find('li.no-data').show();
		}
	},

	updateCustomFilterTitle: function(id, title) {
		$('#tickets_outline_custom_filters').find('li.filter-' + id + ' label').text(title);
	},

	addCustomFilter: function(id, title) {
		var html = [];
		html.push('<li class="filter filter-'+id+'">');
			html.push('<h3 class="is-nav-item title"><label></label></h3>');
		html.push('</li>');
		html = html.join('');

		var row = $(html);
		row.find('label').text(title);

		$('#tickets_outline_custom_filters').append(row);
		$('#tickets_outline_custom_filters').find('li.no-data').hide();
	}
});
