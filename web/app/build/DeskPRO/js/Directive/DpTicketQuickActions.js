(function() {
  define(function() {

    /*
      *
     */
    var DeskPRO_Directive_DpTicketQuickActions;
    return DeskPRO_Directive_DpTicketQuickActions = function($timeout) {
      var options;
      options = {
        preview_text_height: 62,
        widget_hide_delay: 200
      };
      return {
        restrict: 'E',
        scope: {},
        replace: true,
        template: '<div class="dp-stickytip"> <div class="previewtext-row"> <cite> <img src="{{ icon }}" /> <span>{{ name }}</span> <span>{{ status }}</span> <span>{{ time }}</span> </cite> <br /> <span></span> </div> <ul class="previewtext-row actions"> <li ng-repeat="action in actions"> <a href="#" ng-click="$event.preventDefault(); handleAction(action)">{{ action.title }}</a> </li> </ul> <input type="hidden" style="width: 200px;position: absolute;bottom: -30px;" /> </div>',
        controller: function($scope, $filter, PersonService, $http) {
          var me;
          $scope.actions = [];
          me = $scope.$root.app_person_id;
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
                }
              });
            }
            if (isAllowed('assign_agent')) {
              $scope.actions.push({
                title: 'Assign Agent',
                params: {
                  agent_id: null
                }
              });
            }
            if (isAllowed('assign_team')) {
              $scope.actions.push({
                title: 'Assign Team',
                params: {
                  agent_team_id: null
                }
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
          return $scope.handleAction = function(action) {
            var callback;
            callback = function() {
              $http.post("/agent/tickets/" + $scope.ticket_id + "/ajax-save-actions", {
                actions: action.params
              }).success(function() {
                return window.DeskPRO_Window.getMessageChanneler().poller.send();
              });
              return $scope.$root.$emit('tickets.quick_actions.hide');
            };
            switch (true) {
              case (action.params.status != null) || action.params.agent_id === me:
                console.info('set status or assign self');
                return callback();
              case null === action.params.agent_id:
                console.info('assign agent');
                return 1;
              case null === action.params.agent_team_id:
                console.info('assign team');
                return 1;
            }
          };
        },
        link: function($scope, $el) {
          var $preview, $select2, promise;
          $el.hide();
          promise = null;
          $preview = $el.find('.previewtext-row > span:eq(0)');
          $preview.dotdotdot({
            elipsis: '...',
            wrap: 'word',
            height: options.preview_text_height
          });
          $select2 = $el.find('input').select2({
            data: [
              {
                id: 0,
                text: 'test'
              }
            ]
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
            console.info('leave');
            return promise = $timeout(((function(_this) {
              return function() {
                return $el.hide();
              };
            })(this)), options.widget_hide_delay);
          });
          console.info($select2.data('select2'));
          return $(document).on('mousemove', '#select2-drop-mask', function(e) {
            return $el.trigger(e);
          });
        }
      };
    };
  });

}).call(this);

//# sourceMappingURL=DpTicketQuickActions.js.map
