(function() {
  var __hasProp = {}.hasOwnProperty,
    __extends = function(child, parent) { for (var key in parent) { if (__hasProp.call(parent, key)) child[key] = parent[key]; } function ctor() { this.constructor = child; } ctor.prototype = parent.prototype; child.prototype = new ctor(); child.__super__ = parent.prototype; return child; };

  define(['Admin/Main/Ctrl/Base'], function(Admin_Ctrl_Base) {
    var Admin_TicketMacros_Ctrl_List;
    Admin_TicketMacros_Ctrl_List = (function(_super) {
      __extends(Admin_TicketMacros_Ctrl_List, _super);

      function Admin_TicketMacros_Ctrl_List() {
        return Admin_TicketMacros_Ctrl_List.__super__.constructor.apply(this, arguments);
      }

      Admin_TicketMacros_Ctrl_List.CTRL_ID = 'Admin_TicketMacros_Ctrl_List';

      Admin_TicketMacros_Ctrl_List.CTRL_AS = 'ListCtrl';

      Admin_TicketMacros_Ctrl_List.DEPS = ['$state', '$stateParams', 'DataService'];

      Admin_TicketMacros_Ctrl_List.prototype.init = function() {
        this.list = [];
        this.macroData = this.DataService.get('TicketMacros');
        this.$scope.display_filter = {
          type: "all",
          agent: "0"
        };
        return this.$scope.$watch('display_filter', (function(_this) {
          return function() {
            return _this.updateFilterList();
          };
        })(this), true);
      };

      Admin_TicketMacros_Ctrl_List.prototype.initialLoad = function() {
        var bothPromise, data_promise, promise;
        promise = this.macroData.loadList();
        promise.then((function(_this) {
          return function(list) {
            return _this.list = list;
          };
        })(this));
        data_promise = this.Api.sendDataGet({
          agents: '/agents'
        }).then((function(_this) {
          return function(res) {
            return _this.agents = res.data.agents.agents;
          };
        })(this));
        bothPromise = this.$q.all([promise, data_promise]);
        bothPromise.then((function(_this) {
          return function() {
            return _this.updateFilterList();
          };
        })(this));
        return bothPromise;
      };

      Admin_TicketMacros_Ctrl_List.prototype.updateFilterList = function() {
        var agentId, display_filter, filterList;
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
          }
        }
        return this.$scope.filterList = filterList;
      };


      /*
      		 * Show the delete dlg
       */

      Admin_TicketMacros_Ctrl_List.prototype.startDelete = function(macro) {
        var inst;
        inst = this.$modal.open({
          templateUrl: this.getTemplatePath('TicketMacros/delete-modal.html'),
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
        return inst.result.then((function(_this) {
          return function() {
            return _this.macroData.deleteMacroById(macro.id).then(function() {
              if (_this.$state.current.name === 'tickets.ticket_macros.edit' && parseInt(_this.$state.params.id) === macro.id) {
                return _this.$state.go('tickets.ticket_macros');
              }
            });
          };
        })(this));
      };

      return Admin_TicketMacros_Ctrl_List;

    })(Admin_Ctrl_Base);
    return Admin_TicketMacros_Ctrl_List.EXPORT_CTRL();
  });

}).call(this);

//# sourceMappingURL=List.js.map
