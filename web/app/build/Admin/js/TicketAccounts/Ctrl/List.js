(function() {
  var __hasProp = {}.hasOwnProperty,
    __extends = function(child, parent) { for (var key in parent) { if (__hasProp.call(parent, key)) child[key] = parent[key]; } function ctor() { this.constructor = child; } ctor.prototype = parent.prototype; child.prototype = new ctor(); child.__super__ = parent.prototype; return child; };

  define(['Admin/Main/Ctrl/Base'], function(Admin_Ctrl_Base) {
    var Admin_TicketAccounts_Ctrl_List;
    Admin_TicketAccounts_Ctrl_List = (function(_super) {
      __extends(Admin_TicketAccounts_Ctrl_List, _super);

      function Admin_TicketAccounts_Ctrl_List() {
        return Admin_TicketAccounts_Ctrl_List.__super__.constructor.apply(this, arguments);
      }

      Admin_TicketAccounts_Ctrl_List.CTRL_ID = 'Admin_TicketAccounts_Ctrl_List';

      Admin_TicketAccounts_Ctrl_List.CTRL_AS = 'TicketAccountsList';

      Admin_TicketAccounts_Ctrl_List.DEPS = ['TicketAccountsData'];

      Admin_TicketAccounts_Ctrl_List.prototype.init = function() {
        return this.accounts = [];
      };

      Admin_TicketAccounts_Ctrl_List.prototype.initialLoad = function() {
        var list_promise;
        list_promise = this.TicketAccountsData.loadList().then((function(_this) {
          return function(recs) {
            _this.accounts = recs.values();
            if (_this.$state.current.name === 'tickets.ticket_accounts') {
              if (_this.accounts[0]) {
                _this.$state.go('tickets.ticket_accounts.edit', {
                  id: _this.accounts[0].id
                });
              } else {
                _this.$state.go('tickets.ticket_accounts.create');
              }
            }
            return _this.addManagedListener(_this.TicketAccountsData.recs, 'changed', function() {
              _this.TicketAccountsData.recs.reorder();
              _this.accounts = _this.TicketAccountsData.recs.values();
              return _this.ngApply();
            });
          };
        })(this));
        return this.$q.all([list_promise]);
      };


      /*
      		 * Show the delete dlg
       */

      Admin_TicketAccounts_Ctrl_List.prototype.startDelete = function(for_acc_id) {
        var for_acc, inst, v, _i, _len, _ref;
        for_acc = null;
        _ref = this.accounts;
        for (_i = 0, _len = _ref.length; _i < _len; _i++) {
          v = _ref[_i];
          if (v.id === for_acc_id) {
            for_acc = v;
          }
        }
        inst = this.$modal.open({
          templateUrl: this.getTemplatePath('TicketAccounts/delete-modal.html'),
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
            return _this.deleteAccount(for_acc);
          };
        })(this));
      };


      /*for_acc
      		 * Actually do th edelete
       */

      Admin_TicketAccounts_Ctrl_List.prototype.deleteAccount = function(acc) {
        return this.Api.sendDelete('/email_accounts/' + acc.id).success((function(_this) {
          return function() {
            _this.TicketAccountsData.remove(acc.id);
            _this.ngApply();
            if (_this.$state.current.name === 'tickets.ticket_accounts.edit' && parseInt(_this.$state.params.id) === acc.id) {
              return _this.$state.go('tickets.ticket_accounts');
            }
          };
        })(this));
      };

      return Admin_TicketAccounts_Ctrl_List;

    })(Admin_Ctrl_Base);
    return Admin_TicketAccounts_Ctrl_List.EXPORT_CTRL();
  });

}).call(this);

//# sourceMappingURL=List.js.map
