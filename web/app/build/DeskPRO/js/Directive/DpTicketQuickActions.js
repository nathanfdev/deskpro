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
        template: "<div class=\"dp-stickytip\">\n	<header>\n		<cite>\n			<img src=\"{{ icon }}\" />\n			<span>{{ name }}</span>\n			<span>{{ status }}</span>\n			<span>{{ time }}</span>\n		</cite>\n	</header>\n	<article><div class=\"preview-text\"></div></article>\n	<footer>\n		<ul class=\"actions\">\n			<li ng-repeat=\"action in actions\" style=\"position: relative;\">\n				<a href=\"#\" ng-click=\"$event.preventDefault(); action.select2 && showDropdown(action, $event) || handleAction(action)\">{{ action.title }}<i ng-if=\"action.select2\" class=\"fa fa-caret-down\"></i></a>\n				<div ng-if=\"action.select2\" ng-show=\"action === visible\" style=\"position: absolute; width: 250px; bottom: -28px; left: -1px;\">\n					<input type=\"hidden\" ui-select2=\"action.select2\" ng-model=\"action.model\" style=\"width: 100%;\" ng-change=\"handleAction(action)\" />\n				</div>\n			</li>\n		</ul>\n	<footer>\n</div>",
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
            var isAllowed, t, _ref;
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
            if (isAllowed('assign_agent') && t.agent) {
              $scope.actions.push({
                title: 'Unassign',
                params: {
                  agent_id: 0
                }
              });
            }
            if (isAllowed('assign_agent')) {
              $scope.actions.push({
                title: 'Assign Agent',
                prop: 'agent_id',
                params: {},
                select2: agentsSelectOptions
              });
            }
            if (isAllowed('assign_team')) {
              $scope.actions.push({
                title: 'Assign Team',
                prop: 'agent_team_id',
                params: {},
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
              $scope.actions.push({
                title: 'Set Resolved',
                params: {
                  status: 'resolved',
                  hidden_status: false
                }
              });
            }
            return $scope.updateWidth();
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
          var $actions, $mask, $preview, isClicked, isOpenSelect, promise, showTimeout;
          $el.hide();
          promise = null;
          $preview = $el.find('.preview-text').first();
          $preview.dotdotdot({
            elipsis: '...',
            wrap: 'word',
            height: options.preview_text_height
          });
          $actions = $el.find('footer > ul.actions:first');
          $mask = $('<div></div>').addClass('select2-drop-mask').css({
            bottom: 0,
            right: 0
          }).hide().appendTo('body');
          isOpenSelect = false;
          isClicked = false;
          $mask.on('click', function(e) {
            return $('#select2-drop-mask').trigger('mousedown', e);
          });
          $el.on('click', function() {
            return isClicked = true;
          });
          $scope.updateWidth = function() {
            $el.css('max-width', '650px');
            return $timeout((function() {
              return $el.css('max-width', $actions.outerWidth(true) + 'px');
            }), 1);
          };
          showTimeout = null;
          $scope.$root.$on('tickets.quick_actions.show', function(angularEvent, e, ticket, delay) {
            var _ref;
            isClicked = false;
            promise && $timeout.cancel(promise);
            if (!(ticket != null ? (_ref = ticket.previews) != null ? _ref.length : void 0 : void 0)) {
              return;
            }
            if (showTimeout) {
              $timeout.cancel(showTimeout);
            }
            showTimeout = null;
            return showTimeout = $timeout(function() {
              var offset, _ref1;
              $el.show();
              offset = $(e.target).offset();
              offset.top += $(e.target).height();
              $el.css(offset);
              $scope.setTicket(ticket);
              return $preview.text((_ref1 = ticket.previews[0].message) != null ? _ref1.preview_text : void 0).trigger('update');
            }, delay);
          });
          $scope.showDropdown = function(action, $event) {
            var $input;
            $scope.visible = action;
            $input = $($event.target).next().children('input');
            if ($input.length) {
              return $timeout(function() {
                $input.off('select2-open');
                $input.off('select2-close');
                $input.on('select2-open', function() {
                  promise && $timeout.cancel(promise);
                  isOpenSelect = true;
                  isClicked = false;
                  $(document).on('mousemove.quick-actions-select2', '#select2-drop-mask, #select2-drop', function(e) {
                    return $el.trigger(e);
                  });
                  $('#select2-drop-mask').hide();
                  return $mask.show();
                });
                $input.on('select2-close', function(e) {
                  promise && $timeout.cancel(promise);
                  isOpenSelect = false;
                  $mask.hide();
                  $(document).off('mousemove.quick-actions-select2');
                  $(document).off('click.quick-actions-select2');
                  $scope.visible = null;
                  return $timeout(function() {
                    return !isClicked && $el.trigger('mouseleave', 10);
                  });
                });
                return $input.select2('open');
              }, 1);
            }
          };
          $scope.$root.$on('tickets.quick_actions.hide', function() {
            $el.trigger('mouseleave');
            if (showTimeout) {
              $timeout.cancel(showTimeout);
            }
            return showTimeout = null;
          });
          $el.on('mousemove', function(e) {
            return promise && $timeout.cancel(promise);
          });
          return $el.on('mouseleave', function(e) {
            return promise = $timeout(((function(_this) {
              return function() {
                !isOpenSelect && $el.hide();
                return isClicked = false;
              };
            })(this)), options.widget_hide_delay);
          });
        }
      };
    };
  });

}).call(this);

//# sourceMappingURL=DpTicketQuickActions.js.map
