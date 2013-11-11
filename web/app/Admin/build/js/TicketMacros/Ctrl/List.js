(function() {
  var __hasProp = {}.hasOwnProperty,
    __extends = function(child, parent) { for (var key in parent) { if (__hasProp.call(parent, key)) child[key] = parent[key]; } function ctor() { this.constructor = child; } ctor.prototype = parent.prototype; child.prototype = new ctor(); child.__super__ = parent.prototype; return child; };

  define(['Admin/Main/Ctrl/Base'], function(Admin_Ctrl_Base) {
    var Admin_TicketMacros_Ctrl_List, _ref;
    Admin_TicketMacros_Ctrl_List = (function(_super) {
      __extends(Admin_TicketMacros_Ctrl_List, _super);

      function Admin_TicketMacros_Ctrl_List() {
        _ref = Admin_TicketMacros_Ctrl_List.__super__.constructor.apply(this, arguments);
        return _ref;
      }

      Admin_TicketMacros_Ctrl_List.CTRL_ID = 'Admin_TicketMacros_Ctrl_List';

      Admin_TicketMacros_Ctrl_List.CTRL_AS = 'ListCtrl';

      Admin_TicketMacros_Ctrl_List.DEPS = ['$state', '$stateParams', 'DataService'];

      Admin_TicketMacros_Ctrl_List.prototype.init = function() {
        this.list = [];
        return this.macroData = this.DataService.get('TicketMacros');
      };

      Admin_TicketMacros_Ctrl_List.prototype.initialLoad = function() {
        var promise,
          _this = this;
        promise = this.macroData.loadList();
        promise.then(function(list) {
          return _this.list = list;
        });
        return promise;
      };

      /*
      		# Show the delete dlg
      */


      Admin_TicketMacros_Ctrl_List.prototype.startDelete = function(macro) {
        var inst,
          _this = this;
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
        return inst.result.then(function() {
          return _this.macroData.deleteMacroById(macro.id).then(function() {
            if (_this.$state.current.name === 'tickets.ticket_macros.edit' && parseInt(_this.$state.params.id) === macro.id) {
              return _this.$state.go('tickets.ticket_macros');
            }
          });
        });
      };

      return Admin_TicketMacros_Ctrl_List;

    })(Admin_Ctrl_Base);
    return Admin_TicketMacros_Ctrl_List.EXPORT_CTRL();
  });

}).call(this);

/*
//@ sourceMappingURL=List.js.map
*/