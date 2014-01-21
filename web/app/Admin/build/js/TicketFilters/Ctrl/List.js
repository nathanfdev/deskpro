(function() {
  var __hasProp = {}.hasOwnProperty,
    __extends = function(child, parent) { for (var key in parent) { if (__hasProp.call(parent, key)) child[key] = parent[key]; } function ctor() { this.constructor = child; } ctor.prototype = parent.prototype; child.prototype = new ctor(); child.__super__ = parent.prototype; return child; };

  define(['Admin/Main/Ctrl/Base'], function(Admin_Ctrl_Base) {
    var Admin_TicketFilters_Ctrl_List, _ref;
    Admin_TicketFilters_Ctrl_List = (function(_super) {
      __extends(Admin_TicketFilters_Ctrl_List, _super);

      function Admin_TicketFilters_Ctrl_List() {
        _ref = Admin_TicketFilters_Ctrl_List.__super__.constructor.apply(this, arguments);
        return _ref;
      }

      Admin_TicketFilters_Ctrl_List.CTRL_ID = 'Admin_TicketFilters_Ctrl_List';

      Admin_TicketFilters_Ctrl_List.CTRL_AS = 'ListCtrl';

      Admin_TicketFilters_Ctrl_List.DEPS = ['$state', '$stateParams', 'DataService'];

      Admin_TicketFilters_Ctrl_List.prototype.init = function() {
        var _this = this;
        this.list = [];
        this.filterData = this.DataService.get('TicketFilters');
        this.$scope.display_filter = {
          type: "all",
          agent: "0",
          team: "0"
        };
        this.$scope.$watch('display_filter', function() {
          return _this.updateFilterList();
        }, true);
        return this.sortedListOptions = {
          axis: 'y',
          handle: '.drag-handle',
          update: function(ev, data) {
            var $list, orders;
            $list = data.item.closest('ul');
            orders = [];
            $list.find('li').each(function() {
              var id;
              id = parseInt($(this).data('id'));
              console.log(id);
              if (id) {
                return orders.push(id);
              }
            });
            return _this.filterData.saveDisplayOrder(orders).then(function() {
              return _this.pingElement('display_orders');
            });
          }
        };
      };

      Admin_TicketFilters_Ctrl_List.prototype.initialLoad = function() {
        var bothPromise, data_promise, promise,
          _this = this;
        promise = this.filterData.loadList();
        promise.then(function(list) {
          _this.list = list;
          if (_this.$state.current.name === 'tickets.ticket_filters') {
            if (_this.list[0]) {
              return _this.$state.go('tickets.ticket_filters.edit', {
                id: _this.list[0].id
              });
            } else {
              return _this.$state.go('tickets.ticket_filters.create');
            }
          }
        });
        data_promise = this.Api.sendDataGet({
          agents: '/agents',
          teams: '/agent_teams'
        }).then(function(res) {
          _this.agents = res.data.agents.agents;
          _this.teams = res.data.teams.agent_teams;
          if (!_this.teams[0]) {
            return _this.teams = null;
          }
        });
        bothPromise = this.$q.all([promise, data_promise]);
        bothPromise.then(function() {
          return _this.updateFilterList();
        });
        return bothPromise;
      };

      Admin_TicketFilters_Ctrl_List.prototype.updateFilterList = function() {
        var agentId, display_filter, filterList, teamId;
        filterList = [];
        display_filter = this.$scope.display_filter;
        if (display_filter.type === 'all') {
          filterList = this.list;
        } else {
          if (display_filter.type === 'global') {
            filterList = this.list.filter(function(x) {
              return x.is_global;
            });
          } else if (display_filter.type === 'agent') {
            agentId = parseInt(display_filter.agent);
            if (agentId) {
              filterList = this.list.filter(function(x) {
                return !x.is_global && x.person && x.person.id === agentId;
              });
            } else {
              filterList = this.list.filter(function(x) {
                return !x.is_global && x.person;
              });
            }
          } else if (display_filter.type === 'team') {
            teamId = parseInt(display_filter.team);
            if (teamId) {
              filterList = this.list.filter(function(x) {
                return !x.is_global && x.agent_team && x.agent_team.id === teamId;
              });
            } else {
              filterList = this.list.filter(function(x) {
                return !x.is_global && x.agent_team;
              });
            }
          }
        }
        return this.$scope.filterList = filterList;
      };

      /*
      		# Show the delete dlg
      */


      Admin_TicketFilters_Ctrl_List.prototype.startDelete = function(filter_id) {
        var filter, inst, v, _i, _len, _ref1,
          _this = this;
        filter = null;
        _ref1 = this.list;
        for (_i = 0, _len = _ref1.length; _i < _len; _i++) {
          v = _ref1[_i];
          if (v.id === filter_id) {
            filter = v;
            break;
          }
        }
        inst = this.$modal.open({
          templateUrl: this.getTemplatePath('TicketFilters/delete-modal.html'),
          controller: [
            '$scope', '$modalInstance', function($scope, $modalInstance) {
              $scope.confirm = function() {
                return $modalInstance.close();
              };
              return $scope.dismiss = function() {
                return $modalInstance.dismiss();
              };
            }
          ]
        });
        return inst.result.then(function() {
          return _this.filterData.deleteFilterId(filter.id).then(function() {
            if (_this.$state.current.name === 'tickets.ticket_filters.edit' && parseInt(_this.$state.params.id) === filter.id) {
              return _this.$state.go('tickets.ticket_filters');
            }
          });
        });
      };

      return Admin_TicketFilters_Ctrl_List;

    })(Admin_Ctrl_Base);
    return Admin_TicketFilters_Ctrl_List.EXPORT_CTRL();
  });

}).call(this);

/*
//@ sourceMappingURL=List.js.map
*/