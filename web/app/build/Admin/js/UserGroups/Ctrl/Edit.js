(function() {
  var __hasProp = {}.hasOwnProperty,
    __extends = function(child, parent) { for (var key in parent) { if (__hasProp.call(parent, key)) child[key] = parent[key]; } function ctor() { this.constructor = child; } ctor.prototype = parent.prototype; child.prototype = new ctor(); child.__super__ = parent.prototype; return child; };

  define(['Admin/Main/Ctrl/Base'], function(Admin_Ctrl_Base) {
    var Admin_UserGroups_Ctrl_Edit;
    Admin_UserGroups_Ctrl_Edit = (function(_super) {
      __extends(Admin_UserGroups_Ctrl_Edit, _super);

      function Admin_UserGroups_Ctrl_Edit() {
        return Admin_UserGroups_Ctrl_Edit.__super__.constructor.apply(this, arguments);
      }

      Admin_UserGroups_Ctrl_Edit.CTRL_ID = 'Admin_UserGroups_Ctrl_Edit';

      Admin_UserGroups_Ctrl_Edit.CTRL_AS = 'EditCtrl';

      Admin_UserGroups_Ctrl_Edit.DEPS = ['$stateParams'];

      Admin_UserGroups_Ctrl_Edit.prototype.init = function() {
        this.groupId = parseInt(this.$stateParams.id) || 0;
        this.ugData = this.DataService.get('UserGroups');
        return this.group = null;
      };

      Admin_UserGroups_Ctrl_Edit.prototype.initialLoad = function() {
        var promise;
        promise = this.ugData.loadEditUserGroupData(this.$stateParams.id || null).then((function(_this) {
          return function(data) {
            var _ref, _ref1, _ref2, _ref3;
            _this.group = data.group;
            _this.form = data.form;
            _this.perm_form = _this.group.perms;
            _this.perm_form.options = {};
            if (_this.group.id !== 1 && data.reg_group.is_enabled) {
              _this.perm_form_reg = data.reg_group.perms;
            } else {
              _this.perm_form_reg = null;
            }
            if (((_ref = _this.perm_form) != null ? (_ref1 = _ref.ticket) != null ? _ref1.reopen_resolved_createnew : void 0 : void 0) || ((_ref2 = _this.perm_form_reg) != null ? (_ref3 = _ref2.ticket) != null ? _ref3.reopen_resolved_createnew : void 0 : void 0)) {
              return _this.perm_form.options.reopen_resolved_createnew = 'new_ticket';
            } else {
              return _this.perm_form.options.reopen_resolved_createnew = 'reject';
            }
          };
        })(this));
        return promise;
      };

      Admin_UserGroups_Ctrl_Edit.prototype.saveForm = function() {
        var is_new, promise;
        if (!this.$scope.form_props.$valid) {
          return;
        }
        is_new = !this.group.id;
        promise = this.ugData.saveFormModel(this.group, this.form, this.perm_form);
        this.startSpinner('saving');
        return promise.then((function(_this) {
          return function() {
            _this.stopSpinner('saving', true).then(function() {
              return _this.Growl.success("Saved");
            });
            _this.skipDirtyState();
            if (is_new) {
              return _this.$state.go('crm.groups.gocreate');
            }
          };
        })(this));
      };


      /*
        	 * Shows the copy settings modal
       */

      Admin_UserGroups_Ctrl_Edit.prototype.showDelete = function() {
        var deleteGroup, group, inst;
        deleteGroup = (function(_this) {
          return function() {
            var p;
            p = _this.ugData.removeGroupById(_this.groupId);
            p.then(function() {
              return _this.$state.go('crm.groups');
            });
            return p;
          };
        })(this);
        group = this.group;
        return inst = this.$modal.open({
          templateUrl: this.getTemplatePath('UserGroups/delete-modal.html'),
          controller: [
            '$scope', '$modalInstance', function($scope, $modalInstance) {
              $scope.group = group;
              $scope.dismiss = function() {
                return $modalInstance.dismiss();
              };
              return $scope.doDelete = function(options) {
                $scope.is_loading = true;
                return deleteGroup().then(function() {
                  return $modalInstance.dismiss();
                });
              };
            }
          ]
        });
      };

      return Admin_UserGroups_Ctrl_Edit;

    })(Admin_Ctrl_Base);
    return Admin_UserGroups_Ctrl_Edit.EXPORT_CTRL();
  });

}).call(this);

//# sourceMappingURL=Edit.js.map
