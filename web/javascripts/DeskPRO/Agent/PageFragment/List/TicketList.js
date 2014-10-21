Orb.createNamespace('DeskPRO.Agent.PageFragment.List');

DeskPRO.Agent.PageFragment.List.TicketList = new Orb.Class({
	Extends: DeskPRO.Agent.PageFragment.ListPane.Basic,

	initializeProperties: function() {
		this.parent();
	},

	initPage: function(el) {
		var self = this;
		var attachPoint = this.getEl('ng_attach');

		this.wrapper = el;
		this.perPage = 50;
		this.filterId = parseInt(this.meta.filter_id) || 0;
		this.fixed_fields = ['subject'];

		self.$scope = DeskPRO_Window.$scope.$new();
		self.$q = DeskPRO_Window.$q;
		self.$timeout = DeskPRO_Window.$timeout;

		DeskPRO_Window.ngModule.dpInjector.invoke(['$compile', function($compile) {
			attachPoint.data('$ngControllerController', self);
			$compile(attachPoint.contents())(self.$scope);
			self.initScope();
		}]);

		this.addEvent('destroy', function() {
			if (self.queuedChangeEvents_timeout) {
				self.$timeout.cancel(self.queuedChangeEvents_timeout);
				self.queuedChangeEvents_timeout = null;
			}
			if (self.$scope) {
				self.$scope.$destroy();
				self.$scope = null;
			}
		});

		if (this.meta.filter_id) {
			DeskPRO_Window.sections.tickets_section.highlightFilterNav(
				this.meta.filter_id,
				this.meta.topGroupingOption || this.meta.topGroupingOption === 0 ? this.meta.topGroupingOption : null
			);
		}

		this.addEvent('activate', this.fillListItems, this);
	},

	updateSlaListForTicket: function(info) {
		if (!info.ticket_id || !info.sla_id || !this.meta.sla_id || info.sla_id != this.meta.sla_id) {
			return;
		}

		var self = this;

		if (this.isRefreshing) {
			return;
		}
		this.isRefreshing = true;

		setTimeout(function() {
			self.isRefreshing = false;
			DeskPRO_Window.loadListPane(self.meta.refreshUrl);
		}, 0);
	},

	initScope: function() {
		var $scope = this.$scope,
			$timeout = this.$timeout,
			self = this,
			startTickets,
			startTicketsBatch;

		startTickets = eval(this.getEl('ticket_json').html());

		if (DP_DEBUG) {
			console.log(startTickets);
		}

		startTicketsBatch = [[], [], []];
		for (var i = 0; i < startTickets.length; i++) {
			if (i <= 15) startTicketsBatch[0].push(startTickets[i]);
			else if (i <= 30) startTicketsBatch[1].push(startTickets[i]);
			else startTicketsBatch[2].push(startTickets[i]);
		}

		$scope.tickets              = startTickets;
		$scope.checkedTickets       = {};
		$scope.checkedTicketsCount  = 0;
		$scope.display_fields       = this.meta.display_fields || [];
		$scope.openTickets          = {};
		$scope.listType             = 'list';
		$scope.DESKPRO_PERSON_ID    = DESKPRO_PERSON_ID;

		if (Modernizr.localstorage && window.localStorage['dp_ticket_listtype']) {
			$scope.listType = window.localStorage['dp_ticket_listtype'];
		}

		this.listTicketIds = eval(this.getEl('ticket_ids_json').html());

		this.updatePageCursorWithTicketId();
		this.realCursorStart = this.$scope.pageCursorStart;

		// Wait til after updatePageCursor since it needs full list to know proper cursor
		$scope.tickets = startTicketsBatch[0];
		this.getEl('ticket_json').remove();
		this.getEl('ticket_ids_json').remove();

		this._initDisplayOptions();
		this._initListChangeEvents();
		this._initMassActions();
		this._initNavControls();

		$timeout(function() {
			if (startTicketsBatch[1].length) {
				startTicketsBatch[1].forEach(function(t) {
					$scope.tickets.push(t);
				});

				$timeout(function() {
					if (startTicketsBatch[2].length) {
						startTicketsBatch[2].forEach(function(t) {
							$scope.tickets.push(t);
						});
						self.updatePageCursorWithTicketId();
						$timeout(function() {
							$scope.isLoaded = true;
						}, 0);
					} else {
						$scope.isLoaded = true;
					}
				}, 10);
			} else {
				$scope.isLoaded = true;
			}
		}, 10);


		$scope.$watch('tickets', this.fillListItems.bind(this));
	},

	fillListItems: function() {
		var self = this,
			$scope = this.$scope,
			routeTemplate = $scope.routes.ticket;

		if (!self.IS_ACTIVE) return;
		$scope.listItems.length = 0;

		$scope.tickets.each(function(ticket){
			$scope.addListItem('ticket', 'ticket:'+ticket.id, ticket.subject, routeTemplate.replace('0000', ticket.id));
		});
	},

	//#########################################################################
	//# Paging and cursor
	//#########################################################################

	/**
	 * When tickets are added or removed then we should update the counter/cursor vars.
	 * This calculates the page cursor based on the first displayed ticket and where it appears
	 * in the list of all ticket IDs.
	 */
	updatePageCursorWithTicketId: function() {
		var $scope = this.$scope,
			displayTicketId,
			startIdx;

		$scope.ticketCount = this.listTicketIds.length;

		if ($scope.ticketCount) {
			displayTicketId = $scope.tickets[0].id;
			startIdx = this.listTicketIds.indexOf(displayTicketId);

			$scope.pageCursorStart = startIdx+1;
			$scope.pageCursorEnd = startIdx + $scope.tickets.length;

			if ($scope.pageCursorEnd > $scope.ticketCount) {
				$scope.pageCursorEnd = $scope.ticketCount;
			}
		} else {
			$scope.pageCursorStart = 0;
			$scope.pageCursorEnd = 0;
		}

		if ($scope.pageCursorStart != 1) {
			$scope.hasPrevPage = true;
		} else {
			$scope.hasPrevPage = false;
		}

		if ($scope.pageCursorEnd < $scope.ticketCount) {
			$scope.hasNextPage = true;
		} else {
			$scope.hasNextPage = false;
		}
	},

	//#########################################################################
	//# Ticket List Change Events
	//#########################################################################

	_initListChangeEvents: function() {
		var self = this, $scope = this.$scope, wrapperEl = this.wrapper;

		this.queuedChangeEvents_timeout = null;
		this.queuedChangeEvents = {'addTicketResults': [], 'removeTicketResults': [], 'refreshTicketResults': [], 'postRun': []};

		this.fieldUtil  = DeskPRO.Agent.PageFragment.List.TicketList.FieldUtil;
		this.orderBy    = this.meta.orderBy.replace(/^ticket\./, '');
		this.orderByDir = this.meta.orderByDir.toUpperCase();

		$scope.realtime = true;
		$scope.halfrealtime = false;
		$scope.massActionsOpen = false;

		this.groupingTerms = [];
		if (this.meta.topGroupingTerm) {
			if (this.meta.topGroupingOption.match(/^\d+/)) {
				this.meta.topGroupingOption = parseInt(this.meta.topGroupingOption);
			}
			this.groupingTerms.push({ field: this.meta.topGroupingTerm, value: this.meta.topGroupingOption || 0 });
		}
		if (this.meta.groupBy) {
			$scope.$watch('ticketCount', function(newCount, oldCount) {
				var diff = newCount - oldCount;
				var groupCountEl = self.getEl('total_grouped_count').find('span');
				var currentCount = parseInt(groupCountEl.text()) + diff;
				groupCountEl.text(currentCount);
			});
		}
		if (this.meta.groupBy && this.meta.groupByOption && this.meta.groupByOption != 'DP_NOT_SET') {
			if (this.meta.groupByOption.match(/^\d+/)) {
				this.meta.groupByOption = parseInt(this.meta.groupByOption);
			}
			this.groupingTerms.push({ field: this.meta.groupBy, value: this.meta.groupByOption || 0 });
		}

		if (this.groupingTerms[0]) {
			this.isClientSideGroupingLogic = this.fieldUtil.isSupportedField(this.groupingTerms[0].field);
		}
		if (this.isClientSideGroupingLogic && this.groupingTerms[1]) {
			this.isClientSideGroupingLogic = this.fieldUtil.isSupportedField(this.groupingTerms[1].field);
		}

		// Events about updates
		// More updates happen in the Section.Tickets controller where
		// we are told about specific additions/removals of tickets to the list
		// Those events are called directly on this controller as addTicketResults/removeTicketResults
		DeskPRO_Window.getMessageBroker().addMessageListener('tickets.deleted', function(ticket_ids) {
			self.queueChangeEvent('removeTicketResults', ticket_ids);
		}, null, [this.OBJ_ID]);

		DeskPRO_Window.getMessageBroker().addMessageListener('agent-notification.tickets.unlocked', (function(info) {
			var ticketId = parseInt(info.ticket_id);
			self.listTicketIds.indexOf(ticketId) !== -1 && self.queueChangeEvent('refreshTicketResults', [ticketId]);
		}).bind(this), null, [this.OBJ_ID]);
		DeskPRO_Window.getMessageBroker().addMessageListener('agent-notification.tickets.locked', (function(info) {
			var ticketId = parseInt(info.ticket_id);
			self.listTicketIds.indexOf(ticketId) !== -1 && self.queueChangeEvent('refreshTicketResults', [ticketId]);
		}).bind(this));

		DeskPRO_Window.getMessageBroker().addMessageListener('agent.ticket-updated', function(info) {
			var ticketId = parseInt(info.ticket_id),
				currentlyInView,
				isInFilter = false;

			if (self.listTicketIds.indexOf(ticketId) !== -1) {
				isInFilter = true;
			}
			if (!isInFilter && self.filterId && DeskPRO_Window.sections.tickets_section && DeskPRO_Window.sections.tickets_section.filterTicketIds[self.filterId]) {
				if (DeskPRO_Window.sections.tickets_section.filterTicketIds[self.filterId].indexOf(ticketId) !== -1) {
					isInFilter = true;
				}
			}

			if (!isInFilter) {
				return;
			}

			currentlyInView = $scope.tickets.filter(function(x) { return x.id === ticketId; }).length === 1;
			if (currentlyInView) {
				self.queueChangeEvent('refreshTicketResults', [ticketId]);
			} else {
				// Need to put this in a post run because currentlyInView needs to know latest state
				// and a removeTicketResults might be queued
				self.queuePostChangeEvent(function() {
					currentlyInView = $scope.tickets.filter(function(x) { return x.id === ticketId; }).length === 1;
					if (currentlyInView) {
						self.queueChangeEvent('refreshTicketResults', [ticketId]);
					} else {
						self.queueChangeEvent('addTicketResults', [ticketId]);
					}
				});
			}
		}, null, [this.OBJ_ID]);

		if (this.meta.groupBy && this.filterId) {
			DeskPRO_Window.getMessageBroker().addMessageListener('agent.filter-update', function(data) {
				var filterId = parseInt(data.filter_id);
				var ticketId = parseInt(data.ticket_id || 0);
				if (filterId == self.filterId) {
					if (ticketId && data.op) {
						if (data.op == 'add') {
							if (!self.isClientSideGroupingLogic) {
								self.refreshCursor(null, true);
								self.updateSubgroupingBubbles('refresh');
							} else {
								self.updateSubgroupingBubbles('refresh');
								if ($scope.tickets.filter(function (x) {
									return x.id === ticketId;
								}).length === 1) {
									self.queueChangeEvent('refreshTicketResults', [ticketId]);
								} else {
									self.queueChangeEvent('addTicketResults', [ticketId]);
								}
							}
						} else if (data.op == 'del') {
							self.queueChangeEvent('removeTicketResults', [ticketId]);
							self.updateSubgroupingBubbles('refresh');
						}
					}
				}
			}, null, [this.OBJ_ID]);
		}

		// Tab indicator
		this.addEvent('watchedTabAdded', function(tab) {
			var ticketId = parseInt(tab.page.meta.ticket_id);
			if (ticketId) {
				$scope.openTickets[ticketId] = true;
				wrapperEl.find('.ticket-row-' + ticketId).addClass('open');
			}
		});
		this.addEvent('watchedTabRemoved', function(tab) {
			var ticketId = parseInt(tab.page.meta.ticket_id);
			if (ticketId) {
				$scope.openTickets[ticketId] = false;
				wrapperEl.find('.ticket-row-' + ticketId).removeClass('open');
			}
		});
		DeskPRO_Window.getTabWatcher().addTabTypeWatcher('ticket', this, true);
	},


	/**
	 * Queues a change event. This is mainly to de-bounce incoming events from client messages.
	 *
	 * @param {String} type
	 * @param {Array} ticketIds
	 */
	queueChangeEvent: function(type, ticketIds) {
		if (!this.$scope) return;

		var self = this;

		ticketIds.forEach(function(tid) {
			if (self.queuedChangeEvents[type].indexOf(tid) === -1) {
				self.queuedChangeEvents[type].push(parseInt(tid));
			}
		});

		this._ensureQueuedChangeEventsTimeout();
	},

	_ensureQueuedChangeEventsTimeout: function() {
		var self = this, $timeout = this.$timeout;
		if (!this.queuedChangeEvents_timeout) {
			this.queuedChangeEvents_timeout = $timeout(function() { self._runQueuedChangeEvents(); }, 500);
		}
	},

	_runQueuedChangeEvents: function() {
		if (!this.$scope) return;

		var events = this.queuedChangeEvents;
		this.queuedChangeEvents = {'addTicketResults': [], 'removeTicketResults': [], 'refreshTicketResults': [], 'postRun': []};
		this.queuedChangeEvents_timeout = null;

		if (events.removeTicketResults.length) {
			this.removeTicketResults(events.removeTicketResults);
		}
		if (events.refreshTicketResults.length) {
			this.refreshTicketResults(events.refreshTicketResults);
		}
		if (events.addTicketResults.length) {
			this.addTicketResults(events.addTicketResults);
		}
		if (events.postRun.length) {
			for (var x = 0; x < events.postRun.length; x++) {
				events.postRun[x]();
			}
		}
	},


	/**
	 * Queues an event post run callback
	 * @param {Function} fn
	 */
	queuePostChangeEvent: function(fn) {
		this.queuedChangeEvents.postRun.push(fn);
		this._ensureQueuedChangeEventsTimeout();
	},


	/**
	 * Called when a new ticket is added to the main result set. Note that just because it was
	 * added to the *main* result set doesn't mean it should be added to the actual list
	 * we're viewing.
	 *
	 * E.g.:
	 * - We are on page1 and the result is added to the end of the list on page3
	 * - We are viewing a sub-group of the set (e.g, 'All Tickets > By agent > Me'), so the ticket
	 * might not apply at all.
	 *
	 * So just because you add a ticket result here doesn't neccessarily mean the list is updated
	 * at all. This method does proper logic for detecting which tickets should be displayed.
	 *
	 * Note: This should still only be called when the tickets affect the current list. Eg.,
	 * we are viewing filter#4 then only new tickets added to filter#4 should be added here.
	 * The logic only knows about pages/grouping, not about full filter criteria--that is still
	 * done server-side.
	 *
	 * @param {Array<Integer>} ticketIds
	 * @return {promise}
	 */
	addTicketResults: function(ticketIds) {
		var self = this,
			$scope = this.$scope,
			currentTicketIdsMap = {},
			didAdd = [],
			appendIds = [],
			lastId = null,
			promise,
			tmp;

		console.log("[TicketList.addTicketResult] %o", ticketIds);

		if (!$scope.realtime) {
			$scope.hasUnloadedUpdates = true;
			return;
		}

		$scope.tickets.forEach(function(t) { currentTicketIdsMap[t.id] = true });
		ticketIds = ticketIds.filter(function(tid) { return !currentTicketIdsMap[tid]; });

		if (!ticketIds.length) {
			return;
		}

		promise = this.getTicketRows(ticketIds);
		promise.then(function(tickets) {
			if (!self.$scope) return;
			var firstId = $scope.tickets[0] ? $scope.tickets[0].id : null,
				newFirstTicketIdx = null,
				listTicketIdsMap;

			// Re-gen the map because it could change if another request
			// was made while this one was still processing
			currentTicketIdsMap = {};
			$scope.tickets.forEach(function(t) { currentTicketIdsMap[t.id] = true });

			listTicketIdsMap = {};
			self.listTicketIds.forEach(function(tid) { listTicketIdsMap[tid] = true; });

			tickets.forEach(function(ticket) {
				if (!currentTicketIdsMap[ticket.id] && self.isTicketGroupMatch(ticket)) {
					$scope.tickets.push(ticket);
					didAdd.push(ticket.id);

					if (!listTicketIdsMap[ticket.id]) {
						self.updateSubgroupingBubbles('add', ticket);
					}
				}
			});

			if (didAdd.length) {

				$scope.tickets.sort(function(ticketA, ticketB) {
					return self.fieldUtil.getOrder(ticketA, ticketB, self.orderBy, self.orderByDir);
				});

				// Add to IDs array
				$scope.tickets.forEach(function(ticket, idx) {
					if (didAdd.indexOf(ticket.id) !== -1) {
						if (!listTicketIdsMap[ticket.id]) {
							if (!lastId) {
								if (idx === 0) {
									self.listTicketIds.unshift(ticket.id);
								} else {
									self.listTicketIds.push(ticket);
								}
							} else {
								tmp = self.listTicketIds.indexOf(lastId);
								if (tmp !== -1) {
									self.listTicketIds.splice(tmp, 0, ticket.id);
								} else {
									appendIds.push(ticket.id);
								}
							}
						}
					}
					lastId = ticket.id;
				});
				if (appendIds.length) {
					appendIds.forEach(function(tid) {
						if (!listTicketIdsMap[tid]) {
							self.listTicketIds.push(tid);
						}
					});
				}

				// Truncate list to max perPage
				if ($scope.tickets.length >= self.perPage) {

					if (self.realCursorStart !== 1 && firstId) {
						for (var i = 0; i < $scope.tickets.length; i++) {
							if ($scope.tickets[i].id === firstId) {
								newFirstTicketIdx = i;
								break;
							}
						}
					}

					if (!newFirstTicketIdx) {
						newFirstTicketIdx = 0;
					}

					$scope.tickets = $scope.tickets.slice(newFirstTicketIdx, newFirstTicketIdx+self.perPage);
				}

				self.updatePageCursorWithTicketId();
			}
		});

		return promise;
	},


	/**
	 * Called when a new ticket is removed from the current result set.
	 *
	 * @param {Array<Integer>} ticketIds
	 * @return void
	 */
	removeTicketResults: function(ticketIds) {
		var $scope = this.$scope,
			self = this,
			removeTicketIdsMap,
			didRemoveList,
			loadExtra,
			loadExtraStartIdx,
			loadExtraEndIdx;

		console.log("[TicketList.removeTicketResult] %o", ticketIds);

		if (!$scope.realtime && !$scope.halfrealtime) {
			$scope.hasUnloadedUpdates = true;
			return;
		}

		removeTicketIdsMap = {};
		didRemoveList = {};
		ticketIds.forEach(function(x) { removeTicketIdsMap[x] = true; });

		$scope.tickets = $scope.tickets.filter(function(t) {
			if (removeTicketIdsMap[t.id]) {
				self.updateSubgroupingBubbles('remove', t);
				didRemoveList[t.id] = true;
				return false;
			} else {
				return true;
			}
		});

		this.listTicketIds = this.listTicketIds.filter(function(tid) {
			if (removeTicketIdsMap[tid]) {
				if (!didRemoveList[tid]) {
					self.updateSubgroupingBubbles('refresh');
				}
				return false;
			} else {
				return true;
			}
		});

		// If we have less than the per page, then get the next page results and bring them in here
		if ($scope.realtime && $scope.tickets.length < this.perPage && this.listTicketIds.length > $scope.tickets.length) {
			if ($scope.tickets.length) {
				loadExtraStartIdx = this.listTicketIds.indexOf($scope.tickets[$scope.tickets.length-1].id) + 1;
			}
			if (!loadExtraStartIdx || loadExtraStartIdx === -1) {
				loadExtraStartIdx = 0;
			}

			loadExtraEndIdx = loadExtraStartIdx + (this.perPage - $scope.tickets.length);
			loadExtra = this.listTicketIds.slice(loadExtraStartIdx, loadExtraEndIdx);
			$scope.$safeApply(function() {
				self.getTicketRows(loadExtra).then(function(tickets) {
					var currentTicketIdsMap = {}, didAdd;
					$scope.tickets.forEach(function(t) { currentTicketIdsMap[t.id] = true });

					tickets.forEach(function(ticket) {
						if (self.listTicketIds.indexOf(ticket.id) !== -1 && !currentTicketIdsMap[ticket.id]) {
							$scope.tickets.push(ticket);
							didAdd = true;
						}
					});
					if (didAdd) {
						$scope.tickets.sort(function(ticketA, ticketB) {
							return self.fieldUtil.getOrder(ticketA, ticketB, self.orderBy, self.orderByDir);
						});
						self.updatePageCursorWithTicketId();
					}
				});
			});
		} else {
			this.updatePageCursorWithTicketId();
			$scope.$safeApply();
		}
	},


	/**
	 * Called to refresh display of specific tickets in the current view.
	 *
	 * Note: This only refreshes tickets in the current view (e.g, within current 50 ticket page).
	 *
	 * @param {Array<Integer>} ticketIds
	 * @return {promise}
	 */
	refreshTicketResults: function(ticketIds) {
		var $scope = this.$scope,
			self = this,
			validTicketIdsMap = {},
			promise;

		console.log("[TicketList.refreshTicketResult] %o", ticketIds);

		if (!$scope.realtime && !$scope.halfrealtime) {
			$scope.hasUnloadedUpdates = true;
			return;
		}

		if (!$scope.tickets || !$scope.tickets.length) {
			return;
		}

		$scope.tickets.forEach(function(t) { validTicketIdsMap[t.id] = true });
		ticketIds = ticketIds.filter(function(tid) { return !!validTicketIdsMap[tid]; });

		if (!ticketIds.length) {
			return;
		}

		promise = this.getTicketRows(ticketIds);
		promise.then(function (tickets) {
			if (!self.$scope) return;
			self.applyTicketData(tickets);
		});

		return promise;
	},


	/**
	 * Applies an array of ticket data to the current view.
	 * This updates existing ticket data models, does not do anything to load/add/remove
	 * (use other methods for that).
	 *
	 * @param {Array} tickets
	 */
	applyTicketData: function(tickets) {
		var $scope = this.$scope,
			self = this,
			didChange = false,
			hasOutofviewChange = false,
			removeIds = [];

		if (!tickets || !tickets.length) {
			return;
		}

		tickets.forEach(function(newTicket) {
			var found = false;
			if (!self.isTicketGroupMatch(newTicket)) {
				found = true;
				removeIds.push(newTicket.id);
			} else {
				for (var i = 0; i < $scope.tickets.length; i++) {
					if ($scope.tickets[i].id == newTicket.id) {
						found = true;
						self.updateSubgroupingBubbles('remove', $scope.tickets[i]);
						self.updateSubgroupingBubbles('add', newTicket);
						$scope.tickets[i] = newTicket;
						didChange = true;
					}
				}
			}
			if (!found) {
				hasOutofviewChange = true;
			}
		});

		if (didChange) {
			$scope.tickets.sort(function(ticketA, ticketB) {
				return self.fieldUtil.getOrder(ticketA, ticketB, self.orderBy, self.orderByDir);
			});
		}

		if (hasOutofviewChange) {
			self.updateSubgroupingBubbles('refresh');
		}
		if (removeIds.length) {
			this.removeTicketResults(removeIds);
		}
	},


	/**
	 * Applies some arbitrary data to a ticket in the current list view.
	 * Use this to apply specific known changes to the view.
	 *
	 * @param {Integer} ticketId
	 * @param {Object} data Data to apply
	 */
	mergeTicketData: function(ticketId, data) {
		var $scope = this.$scope,
			idx = null,
			newTicket;

		$scope.tickets.forEach(function(ticket, i) {
			if (ticket.id != ticketId) return;
			newTicket = _.extend({}, ticket, data || {});
			idx = i;
		});

		if (idx !== null) {
			$scope.$safeApply(function() {
				$scope.tickets.splice(idx, 1, newTicket);
			});
		}
	},


	/**
	 * Given a ticket model object, check if it belongs in the list based on grouping vals.
	 * For example, if viewing grouped by agent and I have selected 'me', then only tickets with ticket.agent.id == me
	 * would return true.
	 *
	 * @param {Object} ticket
	 * @returns {boolean}
	 */
	isTicketGroupMatch: function(ticket) {
		var groupMatchCount = 0,
			self = this;

		if (!this.groupingTerms.length) {
			return true;
		}

		this.groupingTerms.forEach(function(groupInfo) {
			if (self.fieldUtil.checkEquality(ticket, groupInfo.field, groupInfo.value)) {
				groupMatchCount++;
			}
		});

		if (groupMatchCount < this.groupingTerms.length) {
			return false;
		}

		return true;
	},


	/**
	 * Loads raw JSON data for specified ticket IDs
	 *
	 * @param ticketIds
	 * @returns {promise}
	 */
	getTicketRows: function(ticketIds) {
		var def = this.$q.defer(),
			formData = [];

		if (!ticketIds || !ticketIds.length) {
			def.resolve([]);
			return def.promise;
		}

		ticketIds.forEach(function(tid) {
			formData.push({ name: 'ticket_ids[]', value: tid });;
		});

		$.ajax({
			url: BASE_URL + "agent/ticket-search/ticket-rows.json",
			type: 'GET',
			dataType: 'json',
			data: formData,
			noErrorOverride: true,
			success: function(data) {
				def.resolve(data);
			},
			error: function() {
				def.reject();
			}
		});

		return def.promise;
	},


	/**
	 * Loads ticket rows with change data to preview changes.
	 *
	 * @param ticketIds
	 * @param changes
	 * @returns {promise}
	 */
	getTicketChangePreviewRows: function(ticketIds, changes, runSingle) {
		var def = this.$q.defer(),
			formData = [];

		if (runSingle && this.runningChangePreviewAjax) {
			this.runningChangePreviewAjax.abort();
			this.runningChangePreviewAjax = null;
		}

		if (!ticketIds || !ticketIds.length || !changes || !changes.length) {
			def.resolve([]);
			return def.promise;
		}

		ticketIds.forEach(function(tid) {
			formData.push({ name: 'ticket_ids[]', value: tid });;
		});

		changes.forEach(function(c) {
			formData.push(c);
		});

		this.runningChangePreviewAjax = $.ajax({
			url: BASE_URL + "agent/ticket-search/ticket-rows.json",
			type: 'POST',
			dataType: 'json',
			data: formData,
			noErrorOverride: true,
			complete: function() {
				this.runningChangePreviewAjax = null;
			},
			success: function(data) {
				def.resolve(data);
			},
			error: function() {
				def.reject();
			}
		});

		return def.promise;
	},


	/**
	 * Applies data from getTicketChangePreviewRows to the current ticket array so the changes
	 * are visible.
	 *
	 * @param {Array} tickets
	 */
	applyTicketChangePreviews: function(tickets) {
		var ticketMap = {};
		tickets.forEach(function(t) { ticketMap[t.id] = t; });

		this.$scope.tickets.forEach(function(ticket) {
			if (ticketMap[ticket.id]) {
				ticket.preview_changes = ticketMap[ticket.id];

				if (!ticket.version_id) ticket.version_id = 0;
				ticket.version_id++;
			}
		});
	},


	/**
	 * Clear preview data from all tickets
	 */
	clearAllTicketChangePreviews: function() {
		this.$scope.tickets.forEach(function(ticket) {
			if (ticket.preview_changes) {
				ticket.preview_changes = null;
				delete ticket.preview_changes;

				if (!ticket.version_id) ticket.version_id = 0;
				ticket.version_id++;
			}
		});
	},


	/**
	 * Clear preview data from specific tickets
	 *
	 * @param {Array} ticketIds      Clear these ticket IDs
	 * @param {Array} notTicketIds   Clear tickets that are not these IDs
	 */
	clearTicketChangePreviews: function(ticketIds, notTicketIds) {
		var map = null, notMap = null;

		if (ticketIds) {
			map = {};
			ticketIds.forEach(function(t) { map[t] = true; });
		}
		if (notTicketIds) {
			notMap = {};
			notTicketIds.forEach(function(t) { notMap[t] = true; });
		}

		this.$scope.tickets.forEach(function(ticket) {
			if ((map && map[ticket.id]) || (notMap && !notMap[ticket.id])) {
				if (ticket.preview_changes) {
					ticket.preview_changes = null;
					delete ticket.preview_changes;

					if (!ticket.version_id) ticket.version_id = 0;
					ticket.version_id++;
				}
			}
		});
	},


	/**
	 * Updates grouping bubble with some info about what happened.
	 * This is called automatically when a ticket is updated. If we know how to handle
	 * a grouping field, we can update the grouping counts now. Otherwise, we need to
	 * refresh on the server side.
	 *
	 * @param op
	 * @param ticket
	 */
	updateSubgroupingBubbles: function(op, ticket) {
		var self = this,
			groupingBar,
			ticketValue,
			foundBubble,
			$timeout = this.$timeout;

		if (!this.meta.groupBy) {
			return;
		}

		groupingBar = this.getEl('grouping_bar');

		if (!ticket) {
			op = 'refresh';
		}
		if (op != 'refresh') {
			ticketValue = this.fieldUtil.getFieldValue(this.meta.groupBy, ticket);
			if (ticketValue === '__UNKNOWN__') {
				op = 'refresh';
			}
		}
		if (op != 'refresh') {
			if (ticketValue === null) {
				ticketValue = 0;
			}
			groupingBar.find('li').each(function() {
				var el = $(this), num;
				if (el.data('grouping-option') == ticketValue) {
					foundBubble = el;
					num = parseInt(el.find('span').text().trim() || 0);
					if (op == 'add') {
						num++;
					} else {
						num--;
					}
					el.find('span').text(num);
					if (num == 0) {
						el.hide();
					} else {
						el.show();
					}
				}
			});
			if (!foundBubble) {
				op = 'refresh';
			}
		}

		if (op == 'refresh') {
			$timeout(function() {
				self.refreshSubgroupNumbers();
			}, 100);
		}
	},


	/**
	 * Refreshes the subgroup changes from the server.
	 */
	refreshSubgroupNumbers: function() {
		var self = this;
		if (!this.meta.refreshSubgroupCounts || !this.$scope) {
			return;
		}

		$.ajax({
			url: this.meta.refreshSubgroupCounts,
			success: function(data) {
				var groupingBar = self.getEl('grouping_bar');
				if (!data.group_display || !data.group_display.counts) {
					return;
				}

				var touched = [];

				for (var k in data.group_display.counts) {
					if (!data.group_display.counts.hasOwnProperty(k)) continue;
					groupingBar.find('li').each(function() {
						var el = $(this), num = data.group_display.counts[k].total || 0;
						if (el.data('grouping-option') == k) {
							touched.push(this);
							el.find('span').text(num);
							if (num == 0) {
								el.hide();
							} else {
								el.show();
							}
						}
					});
				}

				// Ones with no numbers need to be hidden
				groupingBar.find('li').each(function() {
					if (touched.indexOf(this) === -1) {
						$(this).hide().find('span').text('0');
					}
				});
			}
		});
	},

	//#########################################################################
	//# Mass Actions
	//#########################################################################

	_initMassActions: function() {
		var self = this,
			$scope = this.$scope;

		//------------------------------
		// Checkbox management
		//------------------------------

		$scope.uncheckTicketId = function(ticketId) {
			ticketId = parseInt(ticketId);
			if ($scope.checkedTickets[ticketId]) {
				$scope.checkedTickets[ticketId] = false;
				delete $scope.checkedTickets[ticketId];
				$scope.checkedTicketsCount--;
			}
		};

		$scope.$watch('checkedTickets', function(x) {
			$scope.checkedTicketsCount = 0;
			for (var key in x) {
				if (x[key] === true) {
					$scope.checkedTicketsCount++;
				}
			}

			if ($scope.checkedTicketsCount == $scope.tickets.length) {
				$scope.checkedTicketsToggle = true;
			} else {
				$scope.checkedTicketsToggle = false;
			}

			if ($scope.checkedTicketsCount && !self.massActions && DeskPRO_Window.paneVis.tabs) {
				$scope.openMassActions();
			} else if (!$scope.checkedTicketsCount && self.massActions) {
				self.massActions.close();
			}

			self.updateMassActionPreviewsStatus();
		}, true);

		$scope.checkAllTickets = function(isChecked) {
			if (isChecked) {
				$scope.checkedTickets = {};
				$scope.tickets.forEach(function(x) { $scope.checkedTickets[parseInt(x.id)] = true; });
			} else {
				$scope.checkedTickets = {};
			}
		};

		//------------------------------
		// Mass actions overlay
		//------------------------------

		$scope.openMassActions = function() {
			if (!self.massActions) {
				self.massActions = new DeskPRO.Agent.PageFragment.List.TicketList.MassActions({
					templateElement: self.wrapper.find('.mass-actions-overlay-tpl'),
					"$scope": self.$scope,
					onPostApply: function(inst, data, info) {
						$scope.$safeApply(function() {
							$scope.checkedTickets = {};
							self.clearAllTicketChangePreviews();

							if (data.ticket_data) {
								self.applyTicketData(data.ticket_data);
							}
						});
					},
					getCheckedIds: function() {
						var ids = [];
						for (var tid in $scope.checkedTickets) {
							if ($scope.checkedTickets[tid] === true) {
								ids.push(tid);
							}
						}

						return ids;
					},
					onClosed: function() {
						if (self.massActions) {
							self.massActions.destroy();
						}

						$scope.$safeApply(function() {
							self.clearAllTicketChangePreviews();
							$scope.checkedTickets = {};
						});

						self.massActions = null;
					},
					onFormUpdated: function(changes) {
						self.updateMassActionPreviews(changes);
					}
				});
			}

			self.massActions.open();
		};

		this.updateMassActionPreviewsDebounced = _.debounce(this.updateMassActionPreviews, 500);
	},

	updateMassActionPreviewsStatus: function() {
		var $scope = this.$scope,
			ids = [],
			self = this;

		for (var i in $scope.checkedTickets) {
			if ($scope.checkedTickets.hasOwnProperty(i) && $scope.checkedTickets[i] === true) {
				ids.push(parseInt(i));
			}
		}

		$scope.$safeApply(function() {
			if (!ids.length) {
				self.clearAllTicketChangePreviews();
			} else {
				self.clearTicketChangePreviews(null, ids);
			}
		});
	},

	updateMassActionPreviews: function(changes) {
		var $scope = this.$scope,
			ids = [],
			self = this;

		for (var i in $scope.checkedTickets) {
			if ($scope.checkedTickets.hasOwnProperty(i) && $scope.checkedTickets[i] === true) {
				ids.push(parseInt(i));
			}
		}

		if (!ids.length || !changes.length) {
			$scope.$safeApply(function() {
				self.clearAllTicketChangePreviews();
			});
			return;
		}

		// Clear all previews except for ones we want
		this.clearTicketChangePreviews(null, ids);

		// Load preview data for selected tickets
		this.getTicketChangePreviewRows(ids, changes, true).then(function(data) {
			$scope.$safeApply(function() {
				self.applyTicketChangePreviews(data);
			});
		});
	},

	//#########################################################################
	//# Display options
	//#########################################################################

	_initDisplayOptions: function() {
		var $scope = this.$scope,
			$timeout = this.$timeout,
			wrapperEl = this.wrapper,
			self = this,
			displayOptions,
			sortMenuBtn,
			sortingMenu,
			groupMenuBtn,
			groupingMenu;

		$scope.previewMaxWidthCalc = function(element, targetElement, attrs, scope) {
			var pane = $('#dp_list'),
				maxW;

			maxW = pane.width();

			// Minus the indent of the element to the row (its aligned to the link)
			maxW -= (element.offset().left - pane.offset().left);

			// Some tolerance
			maxW -= 25;

			if (maxW < 300) {
				maxW = 300;
			}

			return maxW;
		};

		$scope.isFieldDisplayable = function(ticket, field) {
			var fieldM;
			switch (field) {
				case 'ref':
				case 'agent':
				case 'agent_team':
				case 'date_created':
					return true;
				case 'date_user_waiting':
					return !!ticket.date_user_waiting;
				case 'date_resolved':
					return !!ticket.date_resolved;
				case 'total_user_waiting':
					return (ticket.total_user_waiting || ticket.date_user_waiting);
				case 'date_last_user_reply':
					return !!ticket.date_last_user_reply;
				case 'date_last_agent_reply':
					return !!ticket.date_last_agent_reply;
				case 'date_last_reply':
					return (ticket.date_last_user_reply || ticket.date_last_agent_reply);
				case 'department':
					return !!ticket.department;
				case 'language':
					return !!ticket.language;
				case 'product':
					return !!ticket.product;
				case 'category':
					return !!ticket.category;
				case 'priority':
					return !!ticket.priority;
				case 'workflow':
					return !!ticket.workflow;
				case 'organization':
					return !!ticket.organization;
				case 'labels':
					return ticket.labels && ticket.labels.length > 0;
				case 'slas':
					return ticket.ticket_slas && ticket.ticket_slas.length > 0;
				default:
					fieldM = field.match(/^ticket_fields\[(\d+)\]$/);
					if (fieldM) {
						if (ticket['field' + fieldM[1]]) {
							return true;
						}
					} else {
						fieldM = field.match(/^person_fields\[(\d+)\]$/);
						if (fieldM) {
							if (ticket.person['field' + fieldM[1]]) {
								return true;
							}
						}
					}
					return false;
			}
		};

		$scope.getDisplayableFields = function() {
			var fields = [];
			self.fixed_fields.each(function(v){
				fields.push(v);
			});
			$scope.display_fields.each(function(v){
				if (fields.indexOf(v) > -1 || v == 'id') return;
				fields.push(v);
			});
			return fields;
		};

		$scope.getFieldDisplayName = function(field){
			return (field.charAt(0).toUpperCase() + field.slice(1)).replace('_', ' ');
		};

		displayOptions = new DeskPRO.Agent.PageHelper.DisplayOptions(this, {
			prefId: 'ticket-' + this.meta.resultTypeName,
			resultId: this.meta.resultTypeId,
			refreshUrl: this.meta.refreshUrl,
			isListView: false,
			refreshCallback: function(info) {
				// Updates to sort order must always refresh
				if (info.context.isSortUpdate) {
					$scope.$safeApply(function() {
						$scope.refreshCursorLoading = true;
					});
					DeskPRO_Window.loadListPane(self.meta.refreshUrl);

				// Otherwise its a display field update, we can just
				// update the display fields and angular will update the view
				} else {
					$scope.display_fields = info.displayFields;
					$scope.$apply();
				}
			}
		});
		this.ownObject(displayOptions);

		// Sorting options
		sortMenuBtn = wrapperEl.find('.order-by-menu-trigger');
		sortingMenu = new DeskPRO.UI.Menu({
			triggerElement: sortMenuBtn,
			menuElement: wrapperEl.find('.order-by-menu'),
			onItemClicked: function(info) {
				var item = $(info.itemEl);

				var prop = item.data('order-by');
				var label = item.find('.label').text().trim();

				// Change the displayed label for some visual feedback
				sortMenuBtn.find('.label label').text(label);
				sortMenuBtn.find('.order-dir').hide();
				sortMenuBtn.find('.order-dir.' + prop.split('_').pop()).show();


				var disOptWrap = displayOptions.getWrapperElement();
				var sel = disOptWrap.find('select.sel-order-by');
				sel.find('option').prop('selected', false);
				sel.find('option.' + prop).prop('selected', true);

				if(wrapperEl.find('header.list-grouping-bar').css('display') == 'block') {
					wrapperEl.find('header.list-grouping-bar').hide();
					self.getEl('grouping_loading').show();
				}

				displayOptions.saveAndRefresh({ isSortUpdate: true });
			}
		});
		this.ownObject(sortingMenu);

		groupMenuBtn = wrapperEl.find('.group-by-menu-trigger');
		groupingMenu = new DeskPRO.UI.Menu({
			triggerElement: groupMenuBtn,
			menuElement: wrapperEl.find('.group-by-menu'),
			onItemClicked: function(info) {
				var item = $(info.itemEl);

				var prop = item.data('group-by')
				var label = item.text().trim();

				// Change the displayed label for some visual feedback
				groupMenuBtn.find('.label').text(label);

				var url = self.meta.refreshUrl;
				url = Orb.appendQueryData(url, 'group_by', prop);

				if (self.meta.viewType == 'list') {
					self.loadNewListviewUrl(url +'&view_type=list');
				} else {
					self.wrapper.find('header.list-grouping-bar').hide();
					self.getEl('grouping_loading').show();
					self.getEl('grouping_bar').hide();
					DeskPRO_Window.loadListPane(url);
				}
			}
		});
		this.ownObject(groupingMenu);

		$scope.openAgentChat = function(agentId, $event) {
			if (!agentId) return;

			if ($event) {
				$event.stopPropagation();
				$event.preventDefault();
			}

			DeskPRO_Window.sections.agent_chat_section.newChatWindow([agentId]);
		}

		//------------------------------
		// Export
		//------------------------------

		$scope.openDisplayOptions = function() { displayOptions.open(); };
		$scope.openTableView = function() {
			$scope.pauseListAnim = true;
			$scope.listType = 'table' === $scope.listType ? 'list' : 'table';
			$timeout(function() {
				$scope.pauseListAnim = false;
			}, 500);

			if (Modernizr.localstorage) {
				window.localStorage['dp_ticket_listtype'] = $scope.listType;
			}
		};
	},


	//#########################################################################
	//# Paging and refreshing
	//#########################################################################

	_initNavControls: function() {
		var self = this,
			$scope = this.$scope;

		$scope.realtime              = true;
		$scope.refreshCursor         = function() { self.refreshCursor(); };
		$scope.hasUnloadedUpdates    = false;
		$scope.toggleRealtimeUpdates = function() {
			$scope.realtime = !$scope.realtime;
			if ($scope.realtime && $scope.hasUnloadedUpdates) {
				self.refreshCursor();
			}
		};

		$scope.loadPrevCursorPage = function() { if ($scope.hasPrevPage) self.loadPrevCursorPage(); };
		$scope.loadNextCursorPage = function() { if ($scope.hasNextPage) self.loadNextCursorPage(); };
	},

	refreshCursor: function(cursor, invisibleLoad) {
		var self = this,
			$scope = this.$scope,
			$q = this.$q,
			$timeout = this.$timeout,
			def,
			time1 = new Date(),
			time2;

		if (typeof cursor == 'undefined' || cursor === null) {
			cursor = this.realCursorStart - 1;
		}

		console.log('[TicketList] refreshCursor(%d)', cursor);

		if (this.refreshCursorAjax) {
			this.refreshCursorAjax.abort();
			this.refreshCursorAjax = null;
			console.log('[TicketList] refreshCursor :: abort existing request');
		}

		def = new $q.defer();
		if (!invisibleLoad) {
			$scope.refreshCursorLoading = true;
		}

		this.refreshCursorAjax = $.ajax({
			url: this.meta.refreshCursorUrl.replace(/\$cursor/g, cursor),
			dataType: 'json',
			success: function(data) {
				if (!self.$scope) return;
				this.refreshCursorAjax = null;
				time2 = new Date();
				console.log('[TicketList] refreshCursor :: done load (%dms) :: %o', time2.getTime() - time1.getTime(), data);

				$scope.hasUnloadedUpdates = false;
				self._handleRefreshCursor(data);
				def.resolve(data);

				$timeout(function() {
					console.log('[TicketList] refreshCursor :: done render (%dms)', (new Date()).getTime() - time2.getTime());
					$scope.refreshCursorLoading = false;
				}, 10);
			},
			error: function() {
				this.refreshCursorAjax = null;
				console.log('[TicketList] refreshCursor :: error :: %o', arguments);
				$scope.refreshCursorLoading = false;
				def.reject();
			}
		});

		return def.promise;
	},

	loadNextCursorPage: function() {
		var $scope = this.$scope;

		var nextCursor = ($scope.pageCursorStart + this.perPage) - 1;

		return this.refreshCursor(nextCursor);
	},

	loadPrevCursorPage: function() {
		var $scope = this.$scope;

		var prevCursor = ($scope.pageCursorStart - this.perPage) - 1;
		if (prevCursor < 0) {
			prevCursor = 0;
		}

		return this.refreshCursor(prevCursor);
	},

	_handleRefreshCursor: function(data) {
		var self = this,
			$scope = this.$scope,
			$timeout = this.$timeout;

		$scope.pauseListAnim = true;
		this.listTicketIds = data.all_ticket_ids;
		$scope.tickets = data.tickets;
		this.updatePageCursorWithTicketId();
		this.realCursorStart = this.$scope.pageCursorStart;

		$timeout(function() {
			$scope.pauseListAnim = false;
		}, 1200);
	}
});


//######################################################################################################################
//######################################################################################################################
//######################################################################################################################

DeskPRO.Agent.PageFragment.List.TicketList.FieldUtil = {

	isSupportedField: function(f) {
		return this.getSupportedFields().indexOf(f) !== -1;
	},

	getSupportedFields: function() {
		return [
			'department', 'category', 'product', 'organization', 'person',
			'language', 'agent', 'agent_team', 'agent_team', 'urgency'
		];
	},

	getFieldValue: function(field, ticket) {
		switch (field) {
			case 'department':
				return ticket.department ? ticket.department.id : null;
			case 'category':
				return ticket.category ? ticket.category.id : null;
			case 'product':
				return ticket.product ? ticket.product.id : null;
			case 'organization':
				return ticket.organization ? ticket.organization.id : null;
			case 'person':
				return ticket.person ? ticket.person.id : null;
			case 'language':
				return ticket.language ? ticket.language.id : null;
			case 'agent':
				return ticket.agent ? ticket.agent.id : null;
			case 'agent_team':
				return ticket.agent_team ? ticket.agent_team.id : null;
			case 'agent_team':
				return ticket.agent_team ? ticket.agent_team.id : null;
			case 'urgency':
				return ticket.urgency ? ticket.urgency : null;
			default:
				console.log("[getFieldValue] Unknown field: %s", field);
				return '__UNKNOWN__';
		}
	},

	checkEquality: function(ticket, field, value) {
		var intValue = parseInt(value) || 0;

		switch (field) {
			case 'department':
				return (ticket.department && ticket.department.id === intValue) || (!ticket.department && intValue === 0);
			case 'category':
				return (ticket.category && ticket.category.id === intValue) || (!ticket.category && intValue === 0);
			case 'product':
				return (ticket.product && ticket.product.id === intValue) || (!ticket.product && intValue === 0);
			case 'organization':
				return (ticket.organization && ticket.organization.id === intValue) || (!ticket.organization && intValue === 0);
			case 'person':
				return (ticket.person && ticket.person.id === intValue) || (!ticket.person && intValue === 0);
			case 'language':
				return (ticket.language && ticket.language.id === intValue) || (!ticket.language && intValue === 0);
			case 'agent':
				return (ticket.agent && ticket.agent.id === intValue) || (!ticket.agent && intValue === 0);
			case 'agent_team':
				return (ticket.agent_team && ticket.agent_team.id === intValue) || (!ticket.agent_team && intValue === 0);
			case 'agent_team':
				return (ticket.agent_team && ticket.agent_team.id === intValue) || (!ticket.agent_team && intValue === 0);
			case 'urgency':
				return ticket.urgency === intValue;
			default:
				console.log("[checkEquality] Unknown field: %s", field);
				return false;
		}
	},

	getOrder: function(ticketA, ticketB, field, dir) {

		dir = dir.toUpperCase();

		var valA = 0,
			valB = 0,
			idDir = dir,
			rDir = dir == 'ASC' ? 'DESC' : 'ASC';

		switch (field) {
			case 'urgency':
				valA = ticketA.urgency || 0;
				valB = ticketB.urgency || 0;
				idDir = rDir;
				break;
			case 'status':
				switch (ticketA.status) {
					case 'awaiting_agent': valA = 1; break;
					case 'awaiting_user':  valA = 2; break;
					case 'resolved':       valA = 3; break;
					case 'closed':         valA = 4; break;
					default:               valA = 5; break;
				}
				switch (ticketB.status) {
					case 'awaiting_agent': valB = 1; break;
					case 'awaiting_user':  valB = 2; break;
					case 'resolved':       valB = 3; break;
					case 'closed':         valB = 4; break;
					default:               valB = 5; break;
				}
			case 'date_created':
				idDir = dir;
				// Will fallback to id
				break;
			case 'date_resolved':
				if (ticketA.date_resolved) {
					valA = ticketA.date_resolved_ts;
				}
				if (ticketB.date_resolved) {
					valB = ticketB.date_resolved_ts;
				}
				break;
			case 'date_archived':
				if (ticketA.date_archived) {
					valA = ticketA.date_archived_ts;
				}
				if (ticketB.date_archived) {
					valB = ticketB.date_archived_ts;
				}
				break;
			case 'total_user_waiting':
				valA = ticketA.total_user_waiting;
				valB = ticketB.total_user_waiting;
				break;
			case 'date_user_waiting':
				if (ticketA.date_user_waiting) {
					valA = ticketA.date_user_waiting_ts;
				}
				if (ticketB.date_user_waiting) {
					valB = ticketB.date_user_waiting_ts;
				}
				break;
			case 'date_last_user_reply':
				if (ticketA.date_last_user_reply) {
					valA = ticketA.date_last_user_reply_ts;
				}
				if (ticketB.date_last_user_reply) {
					valB = ticketB.date_last_user_reply_ts;
				}
				break;
			case 'date_last_agent_reply':
				if (ticketA.date_last_agent_reply) {
					valA = ticketA.date_last_agent_reply_ts;
				}
				if (ticketB.date_last_agent_reply) {
					valB = ticketB.date_last_agent_reply_ts;
				}
				break;
			case 'date_last_reply':
				valA = Math.max(ticketA.date_last_user_reply_ts || 0, ticketA.date_last_agent_reply_ts || 0, ticketA.date_created_ts || 0);
				valB = Math.max(ticketB.date_last_user_reply_ts || 0, ticketB.date_last_agent_reply_ts || 0, ticketB.date_created_ts || 0);
				break;
			case 'priority':
				valA = ticketA.priority ? ticketA.priority.priority : 0;
				valB = ticketB.priority ? ticketB.priority.priority : 0;
				idDir = rDir;
				break;
		}

		if (valA === valB) {
			valA = ticketA.id;
			valB = ticketB.id;
			dir = idDir;
		}

		if (dir == 'ASC') {
			return valA < valB ? -1 : 1;
		} else {
			return valA < valB ? 1 : -1;
		}
	}
};


//######################################################################################################################
//######################################################################################################################
//######################################################################################################################

DeskPRO.Agent.PageFragment.List.TicketList.MassActions = new Orb.Class({
	Implements: [Orb.Util.Events, Orb.Util.Options],

	initialize: function(options)  {
		this.options = {
			/**
			 * The HTML element with the actual controls etc we'll use for this
			 * Defaults to 'wrapper .mass-actions-overlay'
			 */
			templateElement: null,

			/**
			 * Scope of the list
			 */
			$scope: null
		};

		this.setOptions(options);
		this.$scope = this.options.$scope;

		this.realtimeStatus = this.$scope.realtime;
		this.$scope.realtime = false;

		if (this.realtimeStatus) {
			this.$scope.halfrealtime = true;
		}

		this.$scope.massActionsOpen = true;

		this.wrapperEl = this.options.templateElement;
		if(!this.wrapperEl.length) {
			return;
		}

		this._formUpdatedDebounce = _.debounce(this._formUpdated, 500);

		this._resetWrapper();

		this.backdropEls = null;
	},

	updateUi: function() {
		if (this.scrollerHandler) {
			this.scrollerHandler.updateSize();
		}
	},

	_resetWrapper: function() {
		if (this.wrapper) {
			this.wrapper.remove();
		}

		if (!this.wrapperEl.is('script')) {
			this.wrapper = $('<div/>').addClass('mass-actions-overlay-container mass-actions').data('base-id', this.wrapperEl.data('base-id')).data('upload-url', this.wrapperEl.data('upload-url'));
			var wrapperHtml = this.wrapperEl.html();
			this.wrapper.html(wrapperHtml);
		} else {
			var wrapperHtml = DeskPRO_Window.util.getPlainTpl(this.wrapperEl);
			this.wrapper = $(wrapperHtml);
			this.wrapper.detach().appendTo('body');
		}

		this.wrapper.find('.with-scroll-handler, .scroll-setup, .scroll-draw').removeClass('with-scroll-handler scroll-setup scroll-draw');

		this.countEl = $('.selected-tickets-count', this.wrapper);
		DP.select($('select.macro', this.wrapper));

		DeskPRO_Window.initInterfaceLayerEvents(this.wrapper);
		var scrollEl = $('.with-scrollbar', this.wrapper).first();
		if (scrollEl.length) {
			this.scrollerHandler = new DeskPRO.Agent.ScrollerHandler(null, scrollEl, {
				showEvent: 'show',
				hideEvent: 'hide'
			});
		}
	},

	/**
	 * Resets the wrapper back to the original, and then runs all of the init again.
	 */
	reset: function() {
		var wasopen = this.isOpen();
		this.close();

		this._resetWrapper();

		this._hasInit = false;

		if (wasopen) {
			this.open();
		}
	},


	/**
	 * Get the main wrapper element around the mass actions UI controls.
	 *
	 * @return {jQuery}
	 */
	getElement: function() {
		return this.wrapper;
	},


	/**
	 * Form updated, fire the updated callback
	 */
	_formUpdated: function() {
		var info = {};
		var changes = [];

		this.getActionFormValues(changes, false, info);
		this.fireEvent('formUpdated', [changes]);
	},


	/**
	 * Inits the overlay controls lazily on first open
	 */
	_initOverlay: function() {
		var self = this;
		if (this._hasInit) return;
		this._hasInit = true;

		this.wrapper.detach().appendTo('body');
		this.wrapper.css('z-index', '21001');

		this.baseId = this.wrapper.data('base-id');

		this.wrapper.on('click', function(ev) {
			ev.stopPropagation();
		});

		// Three backdrops to surround each side of the list pane: left, right, top
		var back1 = $('<div class="backdrop mass-actions" />');
		var back2 = $('<div class="backdrop mass-actions" />');
		var back3 = $('<div class="backdrop mass-actions" />');
		this.backdropEls = $([back1.get(0), back2.get(0), back3.get(0)]);
		this.backdropEls.css('z-index', '21000').hide().appendTo('body');

		this.backdropEls.on('click', (function(ev) {
			ev.stopPropagation();
			this.close();
		}).bind(this));

		$('header .close-trigger', this.wrapper).first().on('click', (function(ev) {
			ev.stopPropagation();
			ev.preventDefault();
			this.close();
		}).bind(this));

		//------------------------------
		// Convert radios
		//------------------------------

		var tpl = DeskPRO_Window.util.getPlainTpl($('.radio-tpl', this.wrapper));

		var groupedRadios = {};
		$(':radio.button-toggle', this.wrapper).each(function() {
			var name = $(this).attr('name');
			if (!groupedRadios[name]) {
				groupedRadios[name] = [];
			}

			groupedRadios[name].push(this);
		});

		Object.each(groupedRadios, function(els) {
			var newEls = [];
			els = $(els);

			var clickFn = function() {
				var boundId = $(this).data('bound-id');
				var radio = $('#' + boundId);

				// Toggle off already checked (ie none selected now)
				if (radio.is(':checked')) {
					radio.attr('checked', false);
					newEls.removeClass('radio-on');

					// Normal radio behavior
				} else {
					radio.attr('checked', true);
					newEls.removeClass('radio-on');
					$(this).addClass('radio-on');
				}

				self._formUpdatedDebounce();
			};

			els.each(function() {

				var wrapper = $(this).parent();
				var title = $('.radio-title', wrapper).text().trim();

				var newEl = $(tpl)
				newEl.addClass($(this).data('attach-class'));
				$('.radio-title', newEl).text(title);

				if (!$(this).attr('id')) {
					$(this).attr('id', Orb.getUniqueId());
				}

				newEl.data('bound-id', $(this).attr('id'));

				newEl.on('click', clickFn);

				wrapper.hide();
				newEl.insertAfter(wrapper);

				newEls.push(newEl.get(0));
			});

			newEls = $(newEls);
		});

		//------------------------------
		// Attach change listeners
		//------------------------------


		$('select.macro', this.wrapper).on('change', function() {
			self.loadMacro($(this).val());
			self._formUpdatedDebounce();
		});

		// todo should be redo to $scope models
		var $assignMe = this.getElById('assign_me'), $unassignAgent = this.getElById('unassign_agent');
		$assignMe.on('click', function(){
			$('select[name="actions[agent]"]', this.wrapper).val($(this).data('me')).trigger('change');
		});
		$unassignAgent.on('click', function(){
			$('select[name="actions[agent]"]', this.wrapper).val(0).trigger('change');
		});
		$('select[name="actions[agent]"]').on('change', function(){
			$(this).val() == $assignMe.data('me') ? $assignMe.hide() : $assignMe.show();
			parseInt($(this).val()) ? $unassignAgent.show() : $unassignAgent.hide();
		});
		var $assignTeam = this.getElById('assign_team'),
			$unassignTeam = this.getElById('unassign_team');
		$assignTeam.on('click', function(){
			$('select[name="actions[agent_team]"]', this.wrapper).val($(this).data('team')).trigger('change');
		});
		$unassignTeam.on('click', function(){
			$('select[name="actions[agent_team]"]', this.wrapper).val(0).trigger('change');
		});
		$('select[name="actions[agent_team]"]').on('change', function(){
			$(this).val() == $assignTeam.data('team') ? $assignTeam.hide() : $assignTeam.show();
			parseInt($(this).val()) ? $unassignTeam.show() : $unassignTeam.hide();
		});
		var $assignFollow = this.getElById('follower_me');
		$assignFollow.on('click', function(){
			var $sel = $('select[name="actions[add_participants][add_participants][]"]', this.wrapper),
				me = $(this).data('me').toString(),
				val = $sel.val();
			val ? val.push(me) : val = [me];
			$sel.val(val);
			$sel.trigger('change');
		});
		$('select[name="actions[add_participants][add_participants][]"]').on('change', function(){
			var val = $(this).val();
			val && val.length && val.indexOf($assignFollow.data('me').toString()) > -1 ? $assignFollow.hide() : $assignFollow.show();
		});

		$('.apply-actions', this.wrapper).on('click', (function(ev) {
			this.apply();
		}).bind(this));

		//------------------------------
		// Reply Box
		//------------------------------

		var textarea = this.getElById('replybox_txt'), isWysiwyg = false;
		this.textarea = textarea;

		if (DeskPRO_Window.canUseAgentReplyRte()) {

			var sig = this.wrapper.find('textarea.signature-value-html').val() || "";
			sig = sig.replace(/<div class="dp-signature-start">([\w\W]*)<\/div>/, '<p class="dp-signature-start">$1</p>');

			if (sig) {
				textarea.val(($.browser.msie ? '<p></p><p></p>' : '<p><br></p><p><br></p>') + '\n\n' + sig);
			}

			isWysiwyg = true;
			self.getElById('is_html_reply').val('1');

			DeskPRO_Window.initRteAgentReply(textarea, {
				defaultIsHtml: true,
				inlineHiddenPosition: this.getElById('is_html_reply'),
				minHeight: 120,
				callback: function(obj) {
					obj.addBtnFirst('dp_attach', 'Click here to attach a file. You may also drag a file from your computer desktop into this reply area to upload attachments faster.', function(){});
					obj.addBtnAfter('dp_attach', 'dp_snippets', 'Open snippets', function(){});
					obj.addBtnSeparatorAfter('dp_attach');

					var snippetBtn = obj.$toolbar.find('.redactor_btn_dp_snippets').closest('li');
					snippetBtn.addClass('snippets').find('a').html('<span class="show-key-shortcut">S</span>nippets');
					snippetBtn.on('click', function(ev) {
						Orb.cancelEvent(ev);
						self.snippetsViewer.open();
					});

					var attachBtn = obj.$toolbar.find('.redactor_btn_dp_attach').closest('li');
					attachBtn.addClass('attach');
					attachBtn.find('a').text('Attach').append('<input type="file" class="file" name="file-upload" />');

					obj.addBtnSeparatorAfter('dp_snippets');
				}
			});
			this.getElById('is_html_reply').val(1);
		}

		//------------------------------
		// Snippets Viewer
		//------------------------------

		this.snippetsViewer = new DeskPRO.Agent.Widget.SnippetViewer({
			driver: DeskPRO_Window.ticketSnippetDriver,
			onBeforeOpen: function() {
				if (isWysiwyg && textarea.data('redactor')) {
					try {
						textarea.data('redactor').saveSelection();
					} catch (e) {}
				}
			},
			onSnippetClick: function(info) {

				var snippetId    = info.snippetId;
				var snippetCode  = info.snippetCode;

				var agentText;
				var defaultText;
				var useText;
				var result;

				Array.each(snippetCode, function(info) {
					if (info.value) {
						if (info.language_id == DESKPRO_PERSON_LANG_ID) {
							agentText = info.value;
						}
						if (info.language_id == DESKPRO_DEFAULT_LANG_ID) {
							defaultText = info.value;
						}
						useText = info.value;
					}
				});

				if (agentText) {
					useText = agentText;
				} else if (defaultText) {
					useText = defaultText;
				}

				result = useText || '';

				if (isWysiwyg && textarea.data('redactor')) {
					try {
						textarea.data('redactor').restoreSelection();
						textarea.data('redactor').setBuffer();
					} catch (e) {}

					var html = result;
					html = html.replace(/<\/p>\s*<p>/g, '<br/>');
					html = html.replace(/^<p>/, '');
					html = html.replace(/<\/p>$/, '');
					textarea.data('redactor').insertHtml(html);
				} else {
					self.page.insertTextInReply(result);
				}

				self.snippetsViewer.close();
			}
		});

		//------------------------------
		// Upload handling
		//------------------------------

		DeskPRO_Window.util.fileupload(this.wrapper, {
			url: this.wrapper.data('upload-url'),
			uploadTemplate: $('.template-upload', this.replyBox),
			downloadTemplate: $('.template-download', this.replyBox)
		});

		var sels = this.wrapper.find('select.dpe_select');

		window.setTimeout(function() {
			sels.each(function() {
				DP.select($(this));
			});
		}, 150);

		this.wrapper.bind('fileuploaddone', function() {
			self.getElById('attach_row').fadeIn();
			self.wrapper.find('[name="attach\\[\\]"]').each(function() {
				$(this).name('actions[reply][attach_ids][]');
			});
		});
		this.wrapper.bind('fileuploadstart', function() {
			self.getElById('attach_row').fadeIn();
			self.updatePositions();
		});

		this.wrapper.on('click', '.remove-attach-trigger', function() {

			var row = $(this).closest('li');
			row.remove();

			var rows = $('ul.files li', self.getElById('attach_row'));
			if (!rows.length) {
				self.getElById('attach_row').hide().addClass('is-hidden');
			}

			self.updatePositions();
		});

		if (this.assignOptionBox) {
			this.assignOptionBox.destroy();
		}

		var add = $('.other-properties-wrapper', this.wrapper);

		// Remove all the stuff we have layed out in a different way
		// on this popup
		$('div.type', add).each(function() {
			var type = $(this).data('rule-type');
			if (!type) return;

			if (type == 'add_labels' || type == 'remove_labels' || type.indexOf('ticket_field[') !== -1 || type.indexOf('people_field[') !== -1) {

			} else {
				$(this).remove();
			}
			self.updatePositions();
		});

		this.actionsEditor = new DeskPRO.Form.RuleBuilder($('.actions-builder-tpl', add));

		var actList = $('.other-properties-wrapper', this.wrapper);
		$('.add-term-row', add).show().on('click', function() {
			var x = Orb.getUniqueId();
			var basename = 'actions_set['+x+']';
			self.actionsEditor.addNewRow($('.search-terms', actList), basename);
			self.updatePositions();
		});

		this.wrapper.find('input, select, textarea').on('click change blur focus', function() {
			self._formUpdatedDebounce();
		});
	},

	getElById: function(id) {
		return $('#' + this.baseId + '_' + id);
	},

	getActionFormValues: function(appendArray, isApply, info) {
		var self = this;
		appendArray = appendArray || [];

		if (!info) info = {};
		info.actionsCount = 0;

		if (this.wrapper.find('select.macro_id')[0] && this.wrapper.find('select.macro_id').val() != '0') {
			appendArray.push({
				name: 'run_macro_id',
				value: this.wrapper.find('select.macro_id').val()
			});
			info.actionsCount = 1;
			return appendArray;
		}

		$('input, select, textarea', this.wrapper).filter('[name^="actions["], [name^="actions_set["]').each(function() {

			var val = $(this).val(), name = $(this).attr('name');

			if (!val) {
				val = '';
			}

			if (val == '-1') {
				val = '';
			}

			if ($(this).is(':radio, :checkbox')) {
				if (!$(this).is(':checked')) {
					return;
				}
			}

			if (val === '') {
				return;
			}

			// Dont send reply type when we're just fetching previews
			if (!isApply && name == 'actions[reply][reply_text]') {
				return;
			}
			if (!isApply && name == 'actions[reply][is_html]') {
				return;
			}

			// Empty reply, dont add it
			if (name == 'actions[reply][reply_text]') {
				var copy = $.trim(self.wrapper.find('.ticketreply').find('.redactor_editor').text()).replace(/\s/g, ' ');
				var tmp = $('<div/>').html(self.wrapper.find('textarea.signature-value-html').val());
				var sig = $.trim(tmp.text()).replace(/\s/g, ' ');

				if (!copy || copy == sig) {
					return;
				}
			}

			appendArray.push({
				name: name,
				value: val
			});

			info.actionsCount++;
		});

		return appendArray;
	},

	/**
	 * Apply the changes
	 */
	apply: function() {
		var self = this;
		var formData = [];

		var formDataInfo = {
			checkedCount: 0,
			actionsCount: 0
		};

		var ticketIds = this.options.getCheckedIds();
		formDataInfo.checkedCount = ticketIds.length;
		if (!formDataInfo.checkedCount) {
			return;
		}

		ticketIds.forEach(function(tid) {
			formData.push({ name: 'result_ids[]', value: tid });
		});

		formData.push({ name:'return_data', value:'1'});

		this.getActionFormValues(formData, true, formDataInfo);

		// If we dont have any tickets or actions then theres nothing to do
		if (!formDataInfo.checkedCount || !formDataInfo.actionsCount) {
			return;
		}

		this.wrapper.addClass('loading');

		var statusUpdate = this.wrapper.find('input[name="actions[status]"]:checked').val();

		DeskPRO_Window.util.ajaxWithClientMessages({
			url: BASE_URL + 'agent/ticket-search/ajax-save-actions',
			type: 'POST',
			data: formData,
			dataType: 'json',
			context: this,
			success: function(data) {
				self.wrapper.removeClass('loading');

				this.close();

				this.fireEvent('postApply', [this, data, formDataInfo]);

				if (data && data.failed_tickets && data.failed_tickets.length) {
					DeskPRO_Window.showAlert('Note: ' + data.failed_tickets.length + ' tickets were not updated because you do not have permission to make the requested changed.');
				}

				if (statusUpdate === 'hidden.deleted' || statusUpdate === 'hidden.spam') {
					// hide any open tickets
					$.each(data.success_tickets, function(k, ticketId) {
						var tab = DeskPRO_Window.getTabWatcher().findTab('ticket', function(tab) {
							return (tab && tab.page && tab.page && tab.page.meta.ticket_id == ticketId);
						});
						if (tab) {
							DeskPRO_Window.removePage(tab.page);
						}
					});
				}
			}
		});

	},


	/**
	 * Update the positions of the elements
	 */
	updatePositions: function() {

		//------------------------------
		// The wrapper overlaps the content pane section
		//------------------------------

		var pos = $('#dp_content').offset();
		var top = pos.top - 4;

		var bottom = 10;
		var height = '';

		var scrollContent = $('.dp-page-content', this.wrapper).first();
		var contentH = false;
		var hasHeader = !!($('> section > header', this.wrapper).length);
		var hasFooter = !!($('> section > footer', this.wrapper).length);

		if (scrollContent.length) {
			contentH = scrollContent.height();
			if (hasHeader) {
				contentH += 36;
			}
			if (hasFooter) {
				contentH += 45;
			}

			contentH += 50;
		}

		if (hasHeader) $('> section > article', this.wrapper).removeClass('no-header');
		else $('> section > article', this.wrapper).addClass('no-header');

		if (hasFooter) $('> section > article', this.wrapper).removeClass('no-footer');
		else $('> section > article', this.wrapper).addClass('no-footer');

		if (contentH < 350) {
			contentH = 350;
		}

		var maxH = $(window).height() - top - 10;

		if (contentH && contentH < maxH) {
			bottom = '';
			height = contentH;
		}

		this.wrapper.css({
			top: pos.top - 4,
			left: pos.left + 8,
			right: 3,
			bottom: bottom,
			height: height
		});

		//------------------------------
		// The backdrops surround each side of the list pane
		//------------------------------

		var leftEnd = 269; // Where the left ends (aka where listpane starts)

		if (!DeskPRO_Window.paneVis.source) {
			leftEnd = 78;
		}

		var topEnd = 50; // Where the top ends (aka header height)
		var contentStart = pos.left;

		if (!this.options.isListView) {
			this.backdropEls.eq(0).css({
				top: 0,
				width: leftEnd,
				bottom: 0,
				left: 0
			});

			this.backdropEls.eq(1).css({
				top: 0,
				height: topEnd,
				width: contentStart - leftEnd,
				left: leftEnd
			});

			this.backdropEls.eq(2).css({
				top: 0,
				right: 0,
				bottom: 0,
				left: contentStart
			});
		}

		this.updateUi();
	},


	/**
	 * Load a macro into the form
	 */
	loadMacro: function(macro_id) {

		var macroEl = $('.macro-options', this.wrapper);
		var inputActionsEl = $('.actions-input', this.wrapper);

		macro_id = parseInt(macro_id);
		if (!macro_id) {
			macroEl.hide();
			macroEl.find('ul.actions-list').empty();
			macroEl.find('input.macro_id').remove();
			inputActionsEl.show();
			this.updateUi();
			this.updatePositions();
			return;
		}

		var macroBtnEl = $('div.macro-load', this.wrapper).addClass('loading');

		$.ajax({
			url: BASE_URL + 'agent/ticket-search/ajax-get-macro-actions',
			data: { macro_id: macro_id },
			type: 'GET',
			dataType: 'json',
			context: this,
			success: function(data) {

				inputActionsEl.hide();
				macroEl.show();

				var input = $('<input type="hidden" class="macro_id" name="run_macro_id" />');
				input.val(macro_id);
				input.appendTo(macroEl);

				var ul = macroEl.find('ul.actions-list');
				ul.empty();

				Array.each(data.descriptions, function(desc) {
					var li = $('<li />');
					li.html(desc);

					ul.append(li);
				});

				macroBtnEl.removeClass('loading');

				this.updateUi();
				this.updatePositions();
			}
		});

		this.updatePositions();
	},


	/**
	 * Is the overlay currently open?
	 *
	 * @return {Boolean}
	 */
	isOpen: function() {
		if (!this._hasInit || !this.wrapper.is('.open')) {
			return false;
		}

		return true;
	},


	/**
	 * Open this overlay
	 */
	open: function() {
		if (!this.wrapper || !this.wrapper[0]) {
			this._resetWrapper();
		}

		this._initOverlay();

		this.updatePositions();
		DeskPRO_Window.layout.addEvent('resized', this.updatePositions, this);
		this.wrapper.addClass('open');
		this.backdropEls.show();

		this.wrapper.addClass('open').show();

		this.updatePositions();
	},


	/**
	 * Close the overlay
	 */
	close: function() {
		if (!this.isOpen()) {
			return false;
		}

		DeskPRO_Window.layout.removeEvent('resized', this.updatePositions, this);
		this.wrapper.removeClass('open');
		this.backdropEls.hide();
		this.fireEvent('closed', [this]);

		if (this.options.resetOnClose) {
			this.reset();
		}

		this.$scope.halfrealtime = false;
		this.$scope.massActionsOpen = false;
		if (this.realtimeStatus) {
			this.$scope.toggleRealtimeUpdates();
		}
		this.$scope.$safeApply();
	},


	destroy: function() {
		if (this._hasInit) {
			this.wrapper.remove();
			this.backdropEls.remove();
		}

		if (this.textarea && this.textarea.data('redactor')) {
			this.textarea.redactor('destroy');
		}
	}
});
