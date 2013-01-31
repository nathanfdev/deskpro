Orb.createNamespace('DeskPRO.Agent.WindowElement.Section');

DeskPRO.Agent.WindowElement.Section.Tickets = new Orb.Class({
	Extends: DeskPRO.Agent.WindowElement.Section.AbstractSection,

	init: function() {
		var self = this;
		this.archiveFilterIds = [];
		this.buttonEl = $('#tickets_section');
		this.filterTicketIds = {};

		this.urlFragmentName = 'tickets';

		this.runningRefreshFilterGrouping = [];
		this.rerunRefreshFilterGrouping = [];

		this.collectedFilterUpdates = [];
		this.collectedFilterUpdateOps = {};
		this.queueRefreshFilterGrouping = [];

		this.lastArchiveUpdate = new Date();
		this.loadHighlightNavEl = null;

		this.setSectionElement($('<section id="tickets_outline"></section>'));

		DeskPRO_Window.getSectionData('tickets_section', this._initSection.bind(this));
		DeskPRO_Window.getMessageBroker().addMessageListener('agent.filter-update', this.filterUpdated, this);

		DeskPRO_Window.getMessageChanneler().addEvent('postMessageSend', function() {

			if (!self.collectedFilterUpdates.length && !self.queueRefreshFilterGrouping.length) {
				return;
			}

			var filterIds = self.collectedFilterUpdates;
			if (self.queueRefreshFilterGrouping.length) {
				filterIds.append(self.queueRefreshFilterGrouping);
			}

			var filterOps = self.collectedFilterUpdateOps;

			self.collectedFilterUpdates = [];
			self.collectedFilterUpdateOps = {};
			self.queueRefreshFilterGrouping = [];

			if (filterIds.length) {
				self.refreshFilterGrouping(filterIds, false, filterOps);
				self._recountHold();
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
			onPostTabClick: function(info) {
				self.updateUi();
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
			onPreOpen: function(ed) {
				if (self.customFilterGroupEditor) {
					self.customFilterGroupEditor.close();
				}
				if (self.slaGroupEditor) {
					self.slaGroupEditor.close();
				}
			},
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
				if (self.slaGroupEditor) {
					self.slaGroupEditor.close();
				}
				if (self.filterGroupEditor) {
					self.filterGroupEditor.close();
				}

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

		if ($('#ticket_slas_header').length) {
			var header = $('#ticket_slas_header');

			DeskPRO_Window.getMessageBroker().addMessageListener('agent.ticket-sla-updated', function(info) {
				self.getUpdatedSlaCounts();
				if (self.listPage && self.listPage.updateSlaListForTicket) {
					self.listPage.updateSlaListForTicket(info);
				} else {
					DeskPRO_Window.getMessageBroker().sendMessage('agent.ui.ticket_updated', { ticket_id: info.ticket_id });
				}
			});

			DeskPRO_Window.getMessageBroker().addMessageListener('agent.ticket-updated', function(info) {
				if (!info.sla_ids || !info.sla_ids.length) {
					return;
				}

				var refresh = false;

				for (var i = 0; i < info.changed_fields.length; i++) {
					switch (info.changed_fields[i]) {
						case 'status':
							refresh = true;
							break;

						case 'agent':
							if (header.data('sla-filter') == 'agent') {
								refresh = true;
							}
							break;

						case 'agent_team':
							if (header.data('sla-filter') == 'team') {
								refresh = true;
							}
					}
				}

				if (refresh) {
					self.getUpdatedSlaCounts();
					if (self.listPage && self.listPage.refreshSlaTicketList && self.listPage.meta.sla_id && $.inArray(self.listPage.meta.sla_id, info.sla_ids) != -1) {
						self.listPage.refreshSlaTicketList();
					}
				}
			});

			this.updateSlaDescription();

			var slaVal;

			this.slaGroupEditor = new DeskPRO.Agent.Widget.SlaOptionsPop({
				containerElement: '#tickets_outline .scroll-content',
				listElement: '#ticket_slas_header',
				triggerElement: $('.launch-sla-editor', this.contentEl),

				onPreOpen: function(ed) {
					if (self.customFilterGroupEditor) {
						self.customFilterGroupEditor.close();
					}
					if (self.filterGroupEditor) {
						self.filterGroupEditor.close();
					}

					var row = ed.controlRealEl;
					slaVal = row.find('.ticket-filter').val();
				},

				onClose: function(ed) {
					var postData = [];
					var row = ed.controlRealEl;
					var val = row.find('.ticket-filter').val();

					if (val == slaVal) {
						return;
					}

					postData.push({
						name: 'prefs[agent.ui.sla.ticket-filter]',
						value: row.find('.ticket-filter').val()
					});

					var gear = $('#ticket_slas_header .settings');

					gear.addClass('loading');

					$.ajax({
						type: 'POST',
						url: BASE_URL + 'agent/misc/ajax-save-prefs',
						data: postData
					}).done(function() {
						self.getUpdatedSlaCounts(function() {
							gear.removeClass('loading');
						});
					}).fail(function() {
						gear.removeClass('loading');
					});

				}
			});
		}

		DeskPRO_Window.getMessageBroker().addMessageListener('agent.ticket-draft-updated', function (data) {
			var ticketId = data.ticket_id;

			if (!data.via_person || data.via_person != DESKPRO_PERSON_ID) {
				var tab = DeskPRO_Window.getTabWatcher().findTab('ticket', function(tab) {
					if (tab && tab.page && tab.page.wrapper && tab.page.meta.ticket_id == ticketId) {
						return true;
					}

					return false;
				});

				if (tab) {
					var wrapper = tab.page.wrapper;
					if (data.via_person) {
						wrapper.find('.agent-draft-message.agent-' + data.via_person).remove();
					}
					if (data.draft_html) {
						wrapper.find('.ticket-messages .messages-wrap').append(data.draft_html);
					}
				}
			}
		});

		DeskPRO.ElementHandler_Exec(this.wrapper);


		if (this.loadHighlightNavEl) {
			this.highlightFilterNav(this.loadHighlightNavEl[0], this.loadHighlightNavEl[1]);
		}

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

	highlightFilterNav: function(filterId, groupingOption) {
		this.loadHighlightNavEl = [filterId, groupingOption];
		var navLi = $('#system_filters_wrap').find('li.filter-'+filterId);
		if (navLi[0]) {
			$('#system_filters_wrap').find('.nav-selected').removeClass('nav-selected');
			if (groupingOption !== null) {
				navLi.find('li.grouping-' + groupingOption).addClass('nav-selected');
			} else {
				navLi.find('h3.is-nav-item').addClass('nav-selected');
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
			{recurring: true, minDelay: 57000/*57sec*/ }
		);
		DeskPRO_Window.getPoller().addData(
			[{name: 'do[]', value: 'get-custom-filter-counts'}],
			'filters.counts',
			{recurring: true, minDelay: 57000/*57sec*/, minDelayAfterOne:true }
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

				if (tab && data.changed_fields) {
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
				self.queueRefreshFilterGrouping.append(refreshFilterIds);
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
		$('#ticket_filter_' + filter_id + '_count2').html(count);
	},

	setFilterCount: function(filter_id, count) {

		var count_str = count;
		filter_id = parseInt(filter_id);

		var count_str_real = count_str;
		if (count >= 10000) count_str = '10000+';

		var system_name = DeskPRO_Window.getData('systemFilters')[filter_id];
		if (system_name) {

			if (system_name == 'all') {
				this.updateBadge(count);
			}

			var el = $('#ticket_filter_' + filter_id + '_count').html(count_str).data('count', count);
			$('#ticket_filter_' + filter_id + '_count2').html(count_str_real);

			if (el.is('.is-hold-filter')) {
				this._recountHold();
			}
		} else {
			var el = $('#ticket_filter_' + filter_id + '_count').html(count_str).data('count', count);
			$('#ticket_filter_' + filter_id + '_count2').html(count_str_real);
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

	getUpdatedFilterCounts: function() {
		$.ajax({
			url: BASE_URL + 'agent/ticket-search/get-filter-counts.json',
			dataType: 'json',
			context: this,
			success: function(data) {
				this.updateFilterCounts(data);
			}
		});
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
		var filterOps = {};

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

			filterOps = {ticketId: ticketId, op: 'add'};

			if (page && ticketId) {
				page.handleAutoAdd(ticketId);
			}

		} else if (data.op == 'del') {
			if (this.archiveFilterIds.indexOf(filterId) != -1) {
				this.modFilterCount(filterId, 'del');
			} else {
				this.filterTicketIds[filterId].erase(ticketId);

				var count = this.filterTicketIds[filterId].length;
				this.setFilterCount(filterId, count);
			}

			filterOps = {ticketId: ticketId, op: 'del'};

			if (page && ticketId) {
				page.delTicket(ticketId);
			}
		}

		this.collectedFilterUpdates.push(filterId);
		this.collectedFilterUpdateOps[filterId] = filterOps;
	},

	refreshFilterGrouping: function(filterIds, doSave, filterOps) {

		if (this.queueRefreshFilterGrouping.length) {
			filterIds.append(this.queueRefreshFilterGrouping);
			this.queueRefreshFilterGrouping = [];
		}

		var postData = [];

		var els = [];

		if (this.runningRefreshFilterGrouping && this.runningRefreshFilterGrouping.length) {
			var setFilterIds = [];
			Array.each(filterIds, function(filterId) {
				if (this.runningRefreshFilterGrouping.indexOf(filterId) !== -1) {
					this.rerunRefreshFilterGrouping.include(filterId);
				} else {
					setFilterIds.push(filterId);
				}
			}, this);

			filterIds = setFilterIds;
			if (!filterIds || !filterIds.length) {
				return;
			}
		}

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

		this.runningRefreshFilterGrouping = filterIds;

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
					var li = filterEl.find('li.grouping-' + selectedGrouping);

					var count = parseInt(li.find('span.list-counter').text());

					this.setFilterGroupingContent(filterId, html, grouping);

					// Update currently viewed list if we're viewing a
					// subgrouping and its a non-delete update.
					// - If its a delete, then the ticket is simply removed,
					// any other update would require a server call to see
					// if its visible in this group at all
					if (selectedGrouping != 'undefined') {
						filterEl = $('.filter-' + filterId, this.sectionEl);
						li = filterEl.find('li.grouping-' + selectedGrouping);
						if (li[0]) {
							var count2 = parseInt(li.find('span.list-counter').text());
							if (count != count2) {
								// See DeskPRO/Agent/PageFragment/ListPane/BasicTicketResults.js
								// Used to signify that the counts were updated, so the list might need refreshing
								li.addClass('is-stale');

								// Try to find the list
								var listPage = DeskPRO_Window.getListPage();
								if (
										listPage.meta.filter_id
										&& (
											(listPage.meta.filter_id == parseInt(filterId) && listPage.reloadIfStale)
											|| (listPage.meta.filter_id == '5')
											|| (listPage.meta.topGroupingOption) // We are dumb to any grouping, so only way to know if view should be updated is by refreshing
										)
								) {
									if (filterOps && filterOps[filterId] && filterOps[filterId].ticketId && filterOps[filterId].op == 'del') {
										listPage.delTicket(filterOps[filterId].ticketId);
									} else {
										if (li.data('route')) {
											DeskPRO_Window.runPageRouteFromElement(li);
										} else {
											DeskPRO_Window.runPageRouteFromElement(li.find('[data-route]'));
										}
									}
								}
							}
							li.addClass('nav-selected');
						}
					}
				}, this);

				this.runningRefreshFilterGrouping = [];
				if (this.rerunRefreshFilterGrouping && this.rerunRefreshFilterGrouping.length) {
					var refreshIds = this.rerunRefreshFilterGrouping;
					this.rerunRefreshFilterGrouping = [];
					this.refreshFilterGrouping(refreshIds);
				}
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

		this.updateUi();
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
			inputEl.val($('.title .flag', li).text().trim());

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

					$('.title .flag', li).text(newTitle);
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
		if (count >= 10000) {
			count_str = '10000+';
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

		$('#customfilter_group_editor').find('.filter-' + id).remove();
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
	},

	updateSlaDescription: function() {
		var row = $('#ticket_slas_header');
		var filter = row.data('sla-filter');

		row.find('h1 span.sla-filter-type').hide();
		$('#ticket_sla_filter_' + filter).show();
	},

	getUpdatedSlaCounts: function(callback) {
		$.ajax({
			url: BASE_URL + 'agent/ticket-search/get-sla-counts.json',
			dataType: 'json',
			context: this,
			success: function(data) {
				this.updateSlaCounts(data);
				if ($.isFunction(callback)) {
					callback(data);
				}
			}
		});
	},

	updateSlaCounts: function(data) {
		if (!data.counts) {
			return;
		}

		var header = $('#ticket_slas_header');
		header.data('sla-filter', data.sla_filter);
		this.updateSlaDescription();

		Object.each(data.counts, function (counts, sla_id) {
			this.setSlaCounts(sla_id, counts.ok, counts.warning, counts.fail);
		}, this);
	},

	setSlaCounts: function(sla_id, ok, warning, fail) {
		sla_id = parseInt(sla_id);

		var list = $('#tickets_outline_slas');
		var row = list.find('.sla-' + sla_id);

		if (row.length) {
			var okCount = row.find('.list-counter.ok');

			okCount.text(ok || 0);
			if (ok > 0) {
				okCount.addClass('not-empty');
			} else {
				okCount.removeClass('not-empty');
			}

			var warningCount = row.find('.list-counter.warning');

			warningCount.text(warning || 0);
			if (warning > 0) {
				warningCount.addClass('not-empty');
			} else {
				warningCount.removeClass('not-empty');
			}

			var failCount = row.find('.list-counter.fail');

			failCount.text(fail || 0);
			if (fail > 0) {
				failCount.addClass('not-empty');
			} else {
				failCount.removeClass('not-empty');
			}
		}
	}
});
