'use strict';
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

    this.orderBy = this.meta.orderBy.replace(/^ticket\./, '');
    this.orderByDir = this.meta.orderByDir.toUpperCase();

    self.$scope = DeskPRO_Window.$scope.$new();
    self.$q = DeskPRO_Window.$q;
    self.$timeout = DeskPRO_Window.$timeout;

    DeskPRO_Window.ngModule.dpInjector.invoke(['$compile', function($compile) {
      attachPoint.data('$ngControllerController', self);
      $compile(attachPoint.contents())(self.$scope);
      self.initScope();
    }]);

    this.addEvent('immediateDestroy', function() {
      if (self.queuedChangeEvents_timeout) {
        self.$timeout.cancel(self.queuedChangeEvents_timeout);
        self.queuedChangeEvents_timeout = null;
      }
      if (self.$scope) {
        self.$scope.$destroy();
        self.$scope = null;
      }
    });

    this.addEvent('destroy', function() {
      if (self.massActions) {
        self.massActions.destroy();
      }
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

    this.listNav = (function() {
      var $list = self.wrapper.find('section.list-listing:first');
      return {
        scrollTo: function($current) {
          var $children = $list.children('.ng-scope'), totalHeight = 0;

          $children.each(function() {
            totalHeight += $(this).height();
          });
          if ($list.height() > totalHeight) {
            return;
          }

          if ($current.position().top > 0 && $current.position().top < $list.height() - $current.height()) {
            return;
          }

          var scrollTo = $current.position().top < 0
            ? $list.scrollTop() + $current.position().top
            : $list.scrollTop() + $current.position().top - $list.height() + $current.height();
          $list.scrollTop(scrollTo);
        },
        up:       function() {
          var $current = $list.children('.ng-scope.selection-on:first'),
            $next = $current.length ? $current.prev('.ng-scope') : $list.children('.ng-scope').first();

          if ($current.length) {
            $current.removeClass('selection-on');
          }
          if (!$next.length) {
            $next = $current;
          }
          $next.addClass('selection-on');
          this.scrollTo($next);
        },
        down:     function() {
          var $current = $list.children('.ng-scope.selection-on:first'),
            $next = $current.length ? $current.next('.ng-scope') : $list.children('.ng-scope').first();

          if ($current.length) {
            $current.removeClass('selection-on');
          }
          if (!$next.length) {
            $next = $current;
          }
          $next.addClass('selection-on');
          this.scrollTo($next);
        },
        check:    function() {
          var $current = $list.children('.ng-scope.selection-on:first'),
            $check = $current.find('.dp-tpl article input[type="checkbox"]:first');

          $check.length && $check.prop('checked', !$check.prop('checked'));
        },
        enter:    function() {
          var $current = $list.children('.ng-scope.selection-on:first');
          $current.length && DeskPRO_Window.runPageRouteFromElement($current.find('article.row-item:first'));
        }
      };
    })();
    this.addEvent('activate', this.fillListItems, this);
    this.addEvent('activate', function() {
      this.$scope.$safeApply();
    }, this);
  },

  updateSlaListForTicket: function(info) {
    if (!info.ticket_id || !info.sla_id || !this.meta.sla_id || info.sla_id !== this.meta.sla_id) {
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

    $scope.ticketsMap = {};
    $scope.$watch('tickets', function(tickets) {
      $scope.ticketsMap = {};
      tickets.forEach(function(ticket) {
        $scope.ticketsMap[ticket.id] = ticket;
      });
    });

    if (DP_DEBUG) {
      console.log(startTickets);
    }

    startTicketsBatch = [[], [], []];
    for (var i = 0; i < startTickets.length; i++) {
      if (i <= 15) {
        startTicketsBatch[0].push(startTickets[i]);
      } else if (i <= 30) {
        startTicketsBatch[1].push(startTickets[i]);
      } else {
        startTicketsBatch[2].push(startTickets[i]);
      }
    }

    $scope.tickets = startTickets;
    $scope.checkedTickets = {};
    $scope.checkedTicketsCount = 0;
    $scope.checkedTicketsToggle = false;
    $scope.display_fields = this.meta.display_fields || [];
    $scope.openTickets = {};
    $scope.listType = 'list';
    $scope.DESKPRO_PERSON_ID = DESKPRO_PERSON_ID;

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

    // sync cached sla count
    if (self.meta.sla_id) {
      if (self.meta.sla_status) {
        var cachedSlaCount = $('#ticket_sla_'+self.meta.sla_id+'_count_'+self.meta.sla_status);
        var oldSlaCount = parseInt(cachedSlaCount.html(), 10);
        var newSlaCount = self.meta.ticketResultIds.length;

        if (oldSlaCount !== newSlaCount) {
          cachedSlaCount.html(newSlaCount);
          if (newSlaCount > 0) {
            cachedSlaCount.addClass('not-empty');
          } else {
            cachedSlaCount.removeClass('not-empty');
          }
        }
      } else {
        ['ok', 'warning', 'fail'].forEach(function(slaStatus) {
          var cachedSlaCount = $('#ticket_sla_'+self.meta.sla_id+'_count_'+slaStatus);
          var oldSlaCount = parseInt(cachedSlaCount.html(), 10);
          var newSlaCount = parseInt(self.meta.slaGroupCounts[slaStatus], 10);

          if (oldSlaCount !== newSlaCount) {
            cachedSlaCount.html(newSlaCount);
            if (newSlaCount > 0) {
              cachedSlaCount.addClass('not-empty');
            } else {
              cachedSlaCount.removeClass('not-empty');
            }
          }
        });
      }
    }
  },

  fillListItems: function() {
    var self = this,
      $scope = this.$scope,
      routeTemplate = $scope.routes.ticket;

    if (!self.IS_ACTIVE) {
      return;
    }
    $scope.listItems.length = 0;

    $scope.tickets.each(function(ticket) {
      $scope.addListItem('ticket', 'ticket:' + ticket.id, ticket.subject, routeTemplate.replace('0000', ticket.id));
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

    if (!$scope) {
      return;
    }

    $scope.ticketCount = this.listTicketIds.length;

    if ($scope.ticketCount && $scope.tickets[0]) {
      displayTicketId = $scope.tickets[0].id;
      startIdx = this.listTicketIds.indexOf(displayTicketId);

      $scope.pageCursorStart = startIdx + 1;
      $scope.pageCursorEnd = startIdx + $scope.tickets.length;

      if ($scope.pageCursorEnd > $scope.ticketCount) {
        $scope.pageCursorEnd = $scope.ticketCount;
      }
    } else {
      $scope.pageCursorStart = 0;
      $scope.pageCursorEnd = 0;
    }

    $scope.hasPrevPage = ($scope.pageCursorStart !== 1);

    $scope.hasNextPage = ($scope.pageCursorEnd < $scope.ticketCount);
  },

  //#########################################################################
  //# Ticket List Change Events
  //#########################################################################

  _initListChangeEvents: function() {
    var self = this, $scope = this.$scope, wrapperEl = this.wrapper;

    this.queuedChangeEvents_timeout = null;
    this.queuedChangeEvents = {
      'addTicketResults':     [],
      'removeTicketResults':  [],
      'refreshTicketResults': [],
      'postRun':              []
    };

    this.fieldUtil = DeskPRO.Agent.PageFragment.List.TicketList.FieldUtil;

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
    if (this.meta.groupBy && this.meta.groupByOption && this.meta.groupByOption !== 'DP_NOT_SET') {
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
    }).bind(this), null, [this.OBJ_ID]);

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

      currentlyInView = $scope.tickets.filter(function(x) {
          return x.id === ticketId;
        }).length === 1;
      if (currentlyInView) {
        self.queueChangeEvent('refreshTicketResults', [ticketId]);
      } else {
        // Need to put this in a post run because currentlyInView needs to know latest state
        // and a removeTicketResults might be queued
        self.queuePostChangeEvent(function() {
          currentlyInView = $scope.tickets.filter(function(x) {
              return x.id === ticketId;
            }).length === 1;
          if (currentlyInView) {
            self.queueChangeEvent('refreshTicketResults', [ticketId]);
          }
        });
      }
    }, null, [this.OBJ_ID]);

    DeskPRO_Window.getMessageBroker().addMessageListener('agent-notification.tickets.locked-status', function(info) {
      var ticketId = parseInt(info.ticket_id),
        byAgentId = info.locked_by ? (parseInt(info.locked_by) || null) : null,
        data;

      if (info.is_locked) {
        data = {
          locked_by_agent: {
            id:           byAgentId,
            display_name: info.locked_by_name
          },
          date_locked:     moment().format('YYYY-MM-DD HH:mm:ss')
        };
      } else {
        data = {
          locked_by_agent: null,
          date_locked:     null
        };
      }

      self.mergeTicketData(ticketId, data);
    }, null, [this.OBJ_ID]);

    if (this.groupingTerms && this.groupingTerms.length && this.filterId) {
      DeskPRO_Window.getMessageBroker().addMessageListener('agent.filter-update', function(data) {
        var filterId = parseInt(data.filter_id);
        var ticketId = parseInt(data.ticket_id || 0);
        if (filterId === self.filterId) {
          if (ticketId && data.op) {
            if (data.op === 'add') {
              if (!self.isClientSideGroupingLogic) {
                self.refreshCursor(null, true);
                self.updateSubgroupingBubbles('refresh');
              } else {
                self.updateSubgroupingBubbles('refresh');
                if ($scope.tickets.filter(function(x) {
                    return x.id === ticketId;
                  }).length === 1) {
                  self.queueChangeEvent('refreshTicketResults', [ticketId]);
                } else {
                  self.queueChangeEvent('addTicketResults', [ticketId]);
                }
              }
            } else if (data.op === 'del') {
              self.queueChangeEvent('removeTicketResults', [ticketId]);
              self.updateSubgroupingBubbles('refresh');
            }
          }
        }
      }, null, [this.OBJ_ID]);
    } else {
      DeskPRO_Window.getMessageBroker().addMessageListener('agent.filter-update', function(data) {
        var filterId = parseInt(data.filter_id);
        var ticketId = parseInt(data.ticket_id || 0);
        if (filterId === self.filterId) {
          if (ticketId && data.op) {
            if (data.op === 'add') {
              // ticket is added via Tickets.js
              // filterUpdated() method
            } else if (data.op === 'del') {
              self.queueChangeEvent('removeTicketResults', [ticketId]);
            }
          }
        }
      }, null, [this.OBJ_ID]);
    }

    // Tab indicator
    this.addEvent('watchedTabAdded', function(tab) {
      if (tab) {
        var ticketId = parseInt(tab.page.meta.ticket_id);
        if (ticketId) {
          $scope.openTickets[ticketId] = true;
          wrapperEl.find('.ticket-row-' + ticketId).addClass('open');
        }
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
    if (!this.$scope) {
      return;
    }

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
      this.queuedChangeEvents_timeout = $timeout(function() {
        self._runQueuedChangeEvents();
      }, 500);
    }
  },

  _runQueuedChangeEvents: function() {
    if (!this.$scope) {
      return;
    }

    var events = this.queuedChangeEvents;
    this.queuedChangeEvents = {
      'addTicketResults':     [],
      'removeTicketResults':  [],
      'refreshTicketResults': [],
      'postRun':              []
    };
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
      $('.view-alert-subheader').removeClass('hidden');
      return;
    }

    $scope.tickets.forEach(function(t) {
      currentTicketIdsMap[t.id] = true;
    });
    ticketIds = ticketIds.filter(function(tid) {
      return !currentTicketIdsMap[tid];
    });

    if (!ticketIds.length) {
      return;
    }

    promise = this.getTicketRows(ticketIds);
    promise.then(function(tickets) {
      if (!self.$scope) {
        return;
      }
      var firstId = $scope.tickets[0] ? $scope.tickets[0].id : null,
        newFirstTicketIdx = null,
        listTicketIdsMap;

      // Re-gen the map because it could change if another request
      // was made while this one was still processing
      currentTicketIdsMap = {};
      $scope.tickets.forEach(function(t) {
        currentTicketIdsMap[t.id] = true;
      });

      listTicketIdsMap = {};
      self.listTicketIds.forEach(function(tid) {
        listTicketIdsMap[tid] = true;
      });

      // - This adds new tickets to the current collection of loaded tickets.
      // - At this point we dont know if the tickets sholud actually be visible
      // - So we add the tickets to the collection, then sort them (client side)
      // - After they are sorted, we can adjust the cursor to show new
      //   tickets in the proper position (or dont show them at all, if they are out of view)

      // $scope.tickets is our current view (what is in the browser)
      //
      // self.listTicketIds is an array of ALL ticket IDs in this result set
      //     - order matters in listTicketIds because this is how we determine our cursor location. We know where the
      //       first ID in the current browser view is in the listTicketIds, and the length of listTicketIds,
      //       means we can calculate the cursor.
      //     - order is not guaranteed for any id out of the current browser view though. as new tickets come in,
      //       we only know if they are before or after the current list. (But that is all we need to calculate the cursor, so its ok)
      //     - we only know correct order when the ticket is inserted somewhere WITHIN the current view, not elsewhere.
      //           ~ eg: if i am on page 5 and a ticket comes in to position 1 in the current view,
      //             we dont know if that new ticket is really at that position or just "somewhere before the first ticket in this list"
      //             (it might be all the way back on page 2, for example)
      //           ~ this is why we have 'sliding' cursors, the current view is always fixed at a known point
      //                 Eg. Before:    Showing 51-100 of 247  <- Say position 1 is ticket ID 1234.
      //                     After:     Showing 52-101 of 248  <- position 1 stays 1234, we just slid the cursor up so the numbers match the view
      //                                                          Because all we know is that a new ticket was added to the list somewhere before our #1234.
      //           ~ So a dynamic PREPEND never happens if we are not on page1. page1 is special just because it is known, we know
      //             the order is correct and there can be no other previous rows, so we can prepend dynamically.

      // EXAMPLE
      // Assume set: [<BEFORE range>, Abra, Bar, Baz, Gin, Hol, <AFTER range>]
      //   - Add 'DOG' (middle): [???, Abra, Bar, Baz, DOG, Gin, Hol, ???]
      //     We keep the current range, so pop Hol off: [???, Abra, Bar, Baz, DOG, Gin, ???]
      //   - Add 'Aadvark' (first): [???, Aadvark, Abra, Bar, Baz, Gin, Hol, ???]
      //     If page1, we anchor cursor to the top and Hol is popped: [Aadvark, Abra, Bar, Baz, Gin, ???]
      //     Else, cursor remains the same and Aadvark is out of view: [???, Abra, Bar, Baz, Gin, Hol, ???]
      //   - Add 'Zebdra' (last): [???, Abra, Bar, Baz, Gin, Hol, Zebra, ???]
      //     Cursor remains the same, Zebra is out of view: [???, Abra, Bar, Baz, Gin, Hol, ???]


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

        var insertPos = -1;
        if (firstId) {
          insertPos = self.listTicketIds.indexOf(firstId);
        }

        if (insertPos === -1) {
          insertPos = self.listTicketIds.length - 1;// fallback, append
        }

        var spliceArgs = $scope.tickets.map(function(t) {
          return t.id;
        });
        spliceArgs.unshift(0);
        spliceArgs.unshift(insertPos);

        self.listTicketIds.splice.apply(self.listTicketIds, spliceArgs); // splice(insertPos, 0, id1, id2, id3...)

        // De-dupe
        self.listTicketIds = self.listTicketIds.filter(function() {
          var seen = {};
          return function(element, index, array) {
            return !(element in seen) && (seen[element] = 1);
          };
        }());

        // Truncate list to max perPage
        // Or alternatively, if we are on last page, we never prepend results (because we dont know where the
        // ticket *actually* is in the list, it might be in a previous page)
        if ($scope.tickets.length >= self.perPage || ($scope.tickets.length <= self.perPage && self.realCursorStart !== 1)) {

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

          $scope.tickets = $scope.tickets.slice(newFirstTicketIdx, newFirstTicketIdx + self.perPage);
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
      $('.view-alert-subheader').removeClass('hidden');
      return;
    }

    removeTicketIdsMap = {};
    didRemoveList = {};
    ticketIds.forEach(function(x) {
      removeTicketIdsMap[x] = true;
    });

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
        loadExtraStartIdx = this.listTicketIds.indexOf($scope.tickets[$scope.tickets.length - 1].id) + 1;
      }
      if (!loadExtraStartIdx || loadExtraStartIdx === -1) {
        loadExtraStartIdx = 0;
      }

      loadExtraEndIdx = loadExtraStartIdx + (this.perPage - $scope.tickets.length);
      loadExtra = this.listTicketIds.slice(loadExtraStartIdx, loadExtraEndIdx);
      $scope.$safeApply(function() {
        self.getTicketRows(loadExtra).then(function(tickets) {
          var currentTicketIdsMap = {}, didAdd;
          $scope.tickets.forEach(function(t) {
            currentTicketIdsMap[t.id] = true;
          });

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
      $('.view-alert-subheader').removeClass('hidden');
      return;
    }

    if (!$scope.tickets || !$scope.tickets.length) {
      return;
    }

    $scope.tickets.forEach(function(t) {
      validTicketIdsMap[t.id] = true;
    });
    ticketIds = ticketIds.filter(function(tid) {
      return !!validTicketIdsMap[tid];
    });

    if (!ticketIds.length) {
      return;
    }

    promise = this.getTicketRows(ticketIds);
    promise.then(function(tickets) {
      if (!self.$scope) {
        return;
      }
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
          if ($scope.tickets[i].id === newTicket.id) {
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

    if (!$scope) {
      return;
    }

    $scope.tickets.forEach(function(ticket, i) {
      if (ticket.id !== ticketId) {
        return;
      }
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
      formData.push({ name: 'ticket_ids[]', value: tid });
    });

    $.ajax({
      url:             BASE_URL + "agent/ticket-search/ticket-rows.json",
      type:            'GET',
      dataType:        'json',
      data:            formData,
      noErrorOverride: true,
      success:         function(data) {
        def.resolve(data);
      },
      error:           function() {
        def.reject();
      }
    });

    return def.promise;
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
    if (op !== 'refresh') {
      ticketValue = this.fieldUtil.getFieldValue(this.meta.groupBy, ticket);
      if (ticketValue === '__UNKNOWN__') {
        op = 'refresh';
      }
    }
    if (op !== 'refresh') {
      if (ticketValue === null) {
        ticketValue = 0;
      }
      groupingBar.find('li').each(function() {
        var el = $(this), num;
        if (el.data('grouping-option') === ticketValue) {
          foundBubble = el;
          num = parseInt(el.find('span').text().trim() || 0);
          if (op === 'add') {
            num++;
          } else {
            num--;
          }
          el.find('span').text(num);
          if (num === 0) {
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

    if (op === 'refresh') {
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
      url:     this.meta.refreshSubgroupCounts,
      success: function(data) {
        var groupingBar = self.getEl('grouping_bar');
        if (!data.group_display || !data.group_display.counts) {
          return;
        }

        var touched = [];

        for (var k in data.group_display.counts) {
          if (!data.group_display.counts.hasOwnProperty(k)) {
            continue;
          }
          groupingBar.find('li').each(function() {
            var el = $(this), num = data.group_display.counts[k].total || 0;
            if (parseInt(el.data('grouping-option'), 10) === parseInt(k, 10)) {
              touched.push(this);
              el.find('span').text(num);
              if (num === 0) {
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
      if ($scope.checkedTickets[ticketId]) {
        delete $scope.checkedTickets[ticketId];
        $scope.onToggleTicket(ticketId);
      }
    };

    $scope.onToggleTicket = function(id) {
      if ($scope.checkedTickets[id]) {
        $scope.checkedTicketsCount++;
      } else {
        $scope.checkedTicketsCount--;
      }

      $scope.checkedTicketsToggle = ($scope.checkedTicketsCount === $scope.tickets.length);
    };

    $scope.$watch('checkedTicketsToggle', function(isChecked) {
      if (isChecked) {
        if ($scope.checkedTicketsCount === $scope.tickets.length) return;
        $scope.checkedTicketsCount = 0;
        $scope.tickets.forEach(function(x) {
          $scope.checkedTickets[parseInt(x.id)] = true;
          $scope.checkedTicketsCount++;
        });
      } else {
        if ($scope.checkedTicketsCount !== $scope.tickets.length) return;
        $scope.checkedTickets = {};
        $scope.checkedTicketsCount = 0;
      }
    });

    $scope.$watch('checkedTicketsCount', function(count) {
      if (count && DeskPRO_Window.paneVis.tabs) {
        $scope.openMassActions();
      }
      if (0 === count && self.massActions) {
        self.massActions.close();
      }
    });

    //------------------------------
    // Mass actions overlay
    //------------------------------
    $scope.openMassActions = function() {
      setTimeout(function() {
        if (!self.massActions) {
          self.massActions = new DeskPRO.Agent.PageFragment.List.Helper.TicketMassActions({
            frameEl:       self.getEl('mass_actions_frame'),
            "$scope":      self.$scope,
            onPostApply:   function(inst, data, info) {
              console.log('Options', self.massActions.options);
              $scope.$safeApply(function() {
                $scope.checkedTickets = {};
                $scope.checkedTicketsCount = 0;

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
            onClosed:      function() {
              $scope.$safeApply(function() {
                $scope.checkedTickets = {};
                $scope.checkedTicketsCount = 0;
              });
              self.refreshCursor(null, true);
            }
          });
        }
        self.massActions.open();
      }, 0);
    };
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
        case 'brand':
          return !!ticket.brand;
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
        case 'problems':
          return ticket.problems && ticket.problems.length;
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
      self.fixed_fields.each(function(v) {
        fields.push(v);
      });
      $scope.display_fields.each(function(v) {
        if (fields.indexOf(v) > -1 || v === 'id') {
          return;
        }
        fields.push(v);
      });
      return fields;
    };

    $scope.getFieldDisplayName = function(field) {
      return (field.charAt(0).toUpperCase() + field.slice(1)).replace('_', ' ');
    };

    displayOptions = new DeskPRO.Agent.PageHelper.DisplayOptions(this, {
      prefId:          'ticket-' + this.meta.resultTypeName,
      resultId:        this.meta.resultTypeId,
      refreshUrl:      this.meta.refreshUrl,
      isListView:      false,
      fields:          $scope.display_fields,
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
          $scope.$safeApply();
        }
      }
    });
    this.ownObject(displayOptions);

    // Sorting options
    sortMenuBtn = wrapperEl.find('.order-by-menu-trigger');
    sortingMenu = new DeskPRO.UI.Menu({
      triggerElement: sortMenuBtn,
      menuElement:    wrapperEl.find('.order-by-menu'),
      onItemClicked:  function(info) {
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

        if (wrapperEl.find('header.list-grouping-bar').css('display') === 'block') {
          wrapperEl.find('header.list-grouping-bar').hide();
          self.getEl('grouping_loading').show();
        }

        displayOptions.saveAndRefresh({ isSortUpdate: true });
      }
    });

    var disOptWrap = displayOptions.getWrapperElement();
    var sel = disOptWrap.find('select.sel-order-by');
    var text = sel.find('[value="ticket.' + this.orderBy + ':' + this.orderByDir.toLowerCase() +'"]').text();
    sortMenuBtn.find('.label').text(text);

    this.ownObject(sortingMenu);

    groupMenuBtn = wrapperEl.find('.group-by-menu-trigger');
    groupingMenu = new DeskPRO.UI.Menu({
      triggerElement: groupMenuBtn,
      menuElement:    wrapperEl.find('.group-by-menu'),
      onItemClicked:  function(info) {
        var item = $(info.itemEl);

        var prop = item.data('group-by');
        var label = item.text().trim();

        // Change the displayed label for some visual feedback
        groupMenuBtn.find('.label').text(label);

        var url = self.meta.refreshUrl;
        url = Orb.appendQueryData(url, 'group_by', prop);

        self.wrapper.find('header.list-grouping-bar').hide();
        self.getEl('grouping_loading').show();
        self.getEl('grouping_bar').hide();
        DeskPRO_Window.loadListPane(url);
      }
    });
    this.ownObject(groupingMenu);

    $scope.openAgentChat = function(agentId, $event) {
      if (!agentId) {
        return;
      }

      if ($event) {
        $event.stopPropagation();
        $event.preventDefault();
      }

      DeskPRO_Window.sections.agent_chat_section.newChatWindow([agentId]);
    };

    //------------------------------
    // Export
    //------------------------------

    $scope.openDisplayOptions = function() {
      displayOptions.open();
    };
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

    $scope.realtime = true;
    $scope.refreshCursor = function() {
      self.refreshCursor();
    };
    $scope.toggleRealtimeUpdates = function() {
      $scope.realtime = !$scope.realtime;
      if ($scope.realtime && $('.view-alert-subheader').hasClass('hidden')) {
        self.refreshCursor();
      }
    };

    $scope.loadPrevCursorPage = function() {
      if ($scope.hasPrevPage) {
        self.loadPrevCursorPage();
      }
    };
    $scope.loadNextCursorPage = function() {
      if ($scope.hasNextPage) {
        self.loadNextCursorPage();
      }
    };
  },

  refreshCursor: function(cursor, invisibleLoad) {
    var self = this,
      $scope = this.$scope,
      $q = this.$q,
      $timeout = this.$timeout,
      def,
      time1 = new Date(),
      time2;

    if (typeof cursor === 'undefined' || cursor === null) {
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
      url:      this.meta.refreshCursorUrl.replace(/\$cursor/g, cursor),
      dataType: 'json',
      success:  function(data) {
        if (!self.$scope) {
          return;
        }
        this.refreshCursorAjax = null;
        time2 = new Date();
        console.log('[TicketList] refreshCursor :: done load (%dms) :: %o', time2.getTime() - time1.getTime(), data);

        $('.view-alert-subheader').addClass('hidden');
        self._handleRefreshCursor(data);
        def.resolve(data);

        $timeout(function() {
          console.log('[TicketList] refreshCursor :: done render (%dms)', (new Date()).getTime() - time2.getTime());
          $scope.refreshCursorLoading = false;
        }, 10);
      },
      error:    function() {
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
      rDir = dir === 'ASC' ? 'DESC' : 'ASC';

    switch (field) {
      case 'urgency':
        valA = ticketA.urgency || 0;
        valB = ticketB.urgency || 0;
        idDir = rDir;
        break;
      case 'status':
        switch (ticketA.status) {
          case 'awaiting_agent':
            valA = 1;
            break;
          case 'awaiting_user':
            valA = 2;
            break;
          case 'resolved':
            valA = 3;
            break;
          case 'archived':
            valA = 4;
            break;
          default:
            valA = 5;
            break;
        }
        switch (ticketB.status) {
          case 'awaiting_agent':
            valB = 1;
            break;
          case 'awaiting_user':
            valB = 2;
            break;
          case 'resolved':
            valB = 3;
            break;
          case 'archived':
            valB = 4;
            break;
          default:
            valB = 5;
            break;
        }
        break;
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

    if (dir === 'ASC') {
      return valA < valB ? -1 : 1;
    } else {
      return valA < valB ? 1 : -1;
    }
  }
};
