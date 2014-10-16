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
        template: '<div class="dp-stickytip"> <div class="previewtext-row"> <cite> <img src="{{ icon }}" /> <span>{{ name }}</span> <span>{{ status }}</span> <span>{{ time }}</span> </cite> <br /> <span></span> </div> <ul class="previewtext-row actions"> <li ng-repeat="action in actions"><a href="#">{{ action.title }}</a></li> </ul> </div>',
        controller: function($scope, $filter, Person) {
          var me;
          $scope.actions = [];
          me = $scope.$root.app_person_id;
          return $scope.setTicket = function(ticket) {
            var isAllowed, service, t, _ref;
            service = Person;
            t = ticket;
            $scope.icon = t.previews[0].person.picture_url_16;
            $scope.name = t.previews[0].person.display_name;
            $scope.status = t.previews[0].message.status;
            $scope.time = $filter('formatTimestampAgo')(t.previews[0].message.date_created_ts);
            $scope.text = t.previews[0].message.preview_text.substr(0, 1000);
            $scope.actions.length = 0;
            if (!((_ref = ticket.actions_allowed) != null ? _ref.length : void 0) || (t.locked_by_agent && t.locked_by_agent.id !== me)) {
              return;
            }
            isAllowed = function(action) {
              return -1 !== ticket.actions_allowed.indexOf(action);
            };
            if (isAllowed('assign_self') && (!t.agent || t.agent.id !== $scope.$root.app_person_id)) {
              $scope.actions.push({
                title: 'Assign Me',
                type: 'assign-agent',
                params: {
                  id: me
                }
              });
            }
            if (isAllowed('assign_agent')) {
              $scope.actions.push({
                title: 'Assign Agent',
                type: 'assign-agent',
                params: {
                  id: 1
                }
              });
            }
            if (isAllowed('assign_team')) {
              $scope.actions.push({
                title: 'Assign Team',
                type: 'assign-team',
                params: {
                  id: 1
                }
              });
            }
            if (isAllowed('set_awaiting_user') && 'awaiting_user' !== t.status) {
              $scope.actions.push({
                title: 'Set Awaiting User',
                type: 'set-status',
                params: {
                  status: 'awaiting_user'
                }
              });
            }
            if (isAllowed('set_awaiting_agent') && 'awaiting_agent' !== t.status) {
              $scope.actions.push({
                title: 'Set Awaiting Agent',
                type: 'set-status',
                params: {
                  status: 'awaiting_agent'
                }
              });
            }
            if (isAllowed('set_resolved') && 'resolved' !== t.status) {
              return $scope.actions.push({
                title: 'Set Resolved',
                type: 'set-status',
                params: {
                  status: 'resolved'
                }
              });
            }
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
            var offset, _ref;
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
            $preview.text($scope.text).trigger('update');
            return DP_DEBUG && console.timeEnd('update quick actions preview text');
          });
          $scope.$root.$on('tickets.quick_actions.hide', function() {
            return $el.trigger('mouseleave');
          });
          $el.on('mousemove', function() {
            return promise && $timeout.cancel(promise);
          });
          $el.on('mouseleave', function() {
            return promise = $timeout(((function(_this) {
              return function() {
                return $el.hide();
              };
            })(this)), options.widget_hide_delay);
          });
          return $el.on('click', '.actions a', function(e) {
            e.preventDefault();
            return e.stopPropagation();
          });
        }
      };
    };
  });

}).call(this);

//# sourceMappingURL=DpTicketQuickActions.js.map
