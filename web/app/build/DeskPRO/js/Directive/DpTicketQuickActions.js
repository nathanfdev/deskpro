(function() {
  define(function() {

    /*
      *
     */
    var DeskPRO_Directive_DpTicketQuickActions;
    return DeskPRO_Directive_DpTicketQuickActions = function($timeout, PersonService, AgentTeamService) {
      var options;
      options = {
        preview_text_height: 62,
        widget_hide_delay: 200
      };
      return {
        restrict: 'E',
        scope: {},
        replace: true,
        template: '<div class="dp-stickytip"> <div class="previewtext-row"> <cite> <img src="{{ icon }}" /> <span>{{ name }}</span> <span>{{ status }}</span> <span>{{ time }}</span> </cite> <br /> <span></span> </div> <ul class="previewtext-row actions" style="overflow: visible;"> <li ng-repeat="action in actions" style="position: relative;"> <a href="#" ng-click="$event.preventDefault(); action.select2 && showDropdown(action) || handleAction(action)">{{ action.title }}</a> <div ng-if="action.select2" ng-show="action.visible" style="position: absolute; width: 150px;"> <input type="hidden" ui-select2="action.select2" ng-model="action.model" style="width: 100%;" ng-change="handleAction(action)" /> </div> </li> </ul> </div>',
        controller: function($scope, $filter, $http) {
          var agentsSelectOptions, format, me, teamsSelectOptions;
          $scope.actions = [];
          me = $scope.$root.app_person_id;
          format = function(state) {
            if (!state.id) {
              return state.text;
            }
            return "<img src='" + state.picture_url + "' style='vertical-align: middle' /> <span style='display:inline-block;vertical-align: middle'>" + state.text + "</span></div>";
          };
          agentsSelectOptions = {
            query: function(query) {
              return PersonService.find(query.term).then(function(res) {
                var data;
                res.map(function(entry) {
                  return entry.text = entry.display_name;
                });
                data = {
                  results: res
                };
                return query.callback(data);
              });
            },
            formatResult: format,
            formatSelection: format,
            escapeMarkup: function(m) {
              return m;
            }
          };
          teamsSelectOptions = {
            query: function(query) {
              return AgentTeamService.find(query.term).then(function(res) {
                var data;
                res.map(function(entry) {
                  return entry.text = entry.name;
                });
                data = {
                  results: res
                };
                return query.callback(data);
              });
            },
            formatResult: format,
            formatSelection: format,
            escapeMarkup: function(m) {
              return m;
            }
          };
          $scope.setTicket = function(ticket) {
            var isAllowed, service, t, _ref;
            service = PersonService;
            t = ticket;
            $scope.icon = t.previews[0].person.picture_url_16;
            $scope.name = t.previews[0].person.display_name;
            $scope.status = t.previews[0].message.status;
            $scope.time = $filter('formatTimestampAgo')(t.previews[0].message.date_created_ts);
            $scope.actions.length = 0;
            $scope.ticket_id = t.id;
            if (!((_ref = ticket.actions_allowed) != null ? _ref.length : void 0) || (t.locked_by_agent && t.locked_by_agent.id !== me)) {
              return;
            }
            isAllowed = function(action) {
              return -1 !== ticket.actions_allowed.indexOf(action);
            };
            if (isAllowed('assign_self') && (!t.agent || t.agent.id !== $scope.$root.app_person_id)) {
              $scope.actions.push({
                title: 'Assign Me',
                params: {
                  agent_id: me
                },
                visible: true
              });
            }
            if (isAllowed('assign_agent')) {
              $scope.actions.push({
                title: 'Assign Agent',
                prop: 'agent_id',
                params: {
                  agent_id: null
                },
                select2: agentsSelectOptions
              });
            }
            if (isAllowed('assign_team')) {
              $scope.actions.push({
                title: 'Assign Team',
                prop: 'agent_team_id',
                params: {
                  agent_team_id: null
                },
                select2: teamsSelectOptions
              });
            }
            if (isAllowed('set_awaiting_user') && 'awaiting_user' !== t.status) {
              $scope.actions.push({
                title: 'Set Awaiting User',
                params: {
                  status: 'awaiting_user',
                  hidden_status: false
                }
              });
            }
            if (isAllowed('set_awaiting_agent') && 'awaiting_agent' !== t.status) {
              $scope.actions.push({
                title: 'Set Awaiting Agent',
                params: {
                  status: 'awaiting_agent',
                  hidden_status: false
                }
              });
            }
            if (isAllowed('set_resolved') && 'resolved' !== t.status) {
              return $scope.actions.push({
                title: 'Set Resolved',
                params: {
                  status: 'resolved',
                  hidden_status: false
                }
              });
            }
          };
          $scope.showDropdown = function(action) {
            $scope._visibleAction && ($scope._visibleAction.visible = false);
            $scope._visibleAction = action;
            return action.visible = true;
          };
          return $scope.handleAction = function(action) {
            if (action.select2 && !action.model) {
              return;
            }
            if (action.model) {
              action.params[action.prop] = action.model.id;
            }
            $http.post("/agent/tickets/" + $scope.ticket_id + "/ajax-save-actions", {
              actions: action.params
            }).success(function() {
              return window.DeskPRO_Window.getMessageChanneler().poller.send();
            });
            return $scope.$root.$emit('tickets.quick_actions.hide');
          };
        },
        link: function($scope, $el) {
          var $preview, promise;
          $el.hide();
          promise = null;
          $preview = $el.find('.previewtext-row > span:eq(0)');
          $preview.dotdotdot({
            elipsis: '...',
            wrap: 'word',
            height: options.preview_text_height
          });
          $scope.$root.$on('tickets.quick_actions.show', function(angularEvent, e, ticket) {
            var offset, _ref, _ref1;
            promise && $timeout.cancel(promise);
            if (!(ticket != null ? (_ref = ticket.previews) != null ? _ref.length : void 0 : void 0)) {
              return;
            }
            $el.show();
            offset = $(e.target).offset();
            offset.top += $(e.target).height();
            $el.css(offset);
            DP_DEBUG && console.time('bind quick actions data');
            $scope.setTicket(ticket);
            DP_DEBUG && console.timeEnd('bind quick actions data');
            DP_DEBUG && console.time('update quick actions preview text');
            $preview.text((_ref1 = ticket.previews[0].message) != null ? _ref1.preview_text : void 0).trigger('update');
            return DP_DEBUG && console.timeEnd('update quick actions preview text');
          });
          $scope.$root.$on('tickets.quick_actions.hide', function() {
            return $el.trigger('mouseleave');
          });
          $el.on('mousemove', function(e) {
            return promise && $timeout.cancel(promise);
          });
          $el.on('mouseleave', function(e) {
            return promise = $timeout(((function(_this) {
              return function() {
                return $el.hide();
              };
            })(this)), options.widget_hide_delay);
          });
          return $(document).on('mousemove', '#select2-drop-mask', function(e) {
            return $el.trigger(e);
          });
        }
      };
    };
  });

}).call(this);

//# sourceMappingURL=DpTicketQuickActions.js.map
