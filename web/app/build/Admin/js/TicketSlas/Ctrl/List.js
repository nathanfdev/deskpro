(function() {
  var __hasProp = {}.hasOwnProperty,
    __extends = function(child, parent) { for (var key in parent) { if (__hasProp.call(parent, key)) child[key] = parent[key]; } function ctor() { this.constructor = child; } ctor.prototype = parent.prototype; child.prototype = new ctor(); child.__super__ = parent.prototype; return child; };

  define(['Admin/Main/Ctrl/Base'], function(Admin_Ctrl_Base) {
    var Admin_TicketSlas_Ctrl_List;
    Admin_TicketSlas_Ctrl_List = (function(_super) {
      __extends(Admin_TicketSlas_Ctrl_List, _super);

      function Admin_TicketSlas_Ctrl_List() {
        return Admin_TicketSlas_Ctrl_List.__super__.constructor.apply(this, arguments);
      }

      Admin_TicketSlas_Ctrl_List.CTRL_ID = 'Admin_TicketSlas_Ctrl_List';

      Admin_TicketSlas_Ctrl_List.CTRL_AS = 'ListCtrl';

      Admin_TicketSlas_Ctrl_List.DEPS = ['$state', '$stateParams', 'DataService'];

      Admin_TicketSlas_Ctrl_List.prototype.init = function() {
        this.list = [];
        return this.slaData = this.DataService.get('TicketSlas');
      };

      Admin_TicketSlas_Ctrl_List.prototype.initialLoad = function() {
        var promise;
        promise = this.slaData.loadList();
        promise.then((function(_this) {
          return function(list) {
            return _this.list = list;
          };
        })(this));
        return promise;
      };


      /*
      		 * Show the delete dlg
       */

      Admin_TicketSlas_Ctrl_List.prototype.startDelete = function(sla_id) {
        var inst, sla, v, _i, _len, _ref;
        sla = null;
        _ref = this.list;
        for (_i = 0, _len = _ref.length; _i < _len; _i++) {
          v = _ref[_i];
          if (v.id === sla_id) {
            sla = v;
            break;
          }
        }
        inst = this.$modal.open({
          templateUrl: this.getTemplatePath('TicketSlas/delete-modal.html'),
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
            return _this.slaData.deleteSlaById(sla.id).then(function() {
              if (_this.$state.current.name === 'tickets.slas.edit' && parseInt(_this.$state.params.id) === sla.id) {
                return _this.$state.go('tickets.slas');
              }
            });
          };
        })(this));
      };

      return Admin_TicketSlas_Ctrl_List;

    })(Admin_Ctrl_Base);
    return Admin_TicketSlas_Ctrl_List.EXPORT_CTRL();
  });

}).call(this);

//# sourceMappingURL=List.js.map
