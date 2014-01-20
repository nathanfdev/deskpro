(function() {
  var __hasProp = {}.hasOwnProperty,
    __extends = function(child, parent) { for (var key in parent) { if (__hasProp.call(parent, key)) child[key] = parent[key]; } function ctor() { this.constructor = child; } ctor.prototype = parent.prototype; child.prototype = new ctor(); child.__super__ = parent.prototype; return child; };

  define(['Admin/Main/Ctrl/Base'], function(Admin_Ctrl_Base) {
    var Admin_UserGroups_Ctrl_Edit, _ref;
    Admin_UserGroups_Ctrl_Edit = (function(_super) {
      __extends(Admin_UserGroups_Ctrl_Edit, _super);

      function Admin_UserGroups_Ctrl_Edit() {
        _ref = Admin_UserGroups_Ctrl_Edit.__super__.constructor.apply(this, arguments);
        return _ref;
      }

      Admin_UserGroups_Ctrl_Edit.CTRL_ID = 'Admin_UserGroups_Ctrl_Edit';

      Admin_UserGroups_Ctrl_Edit.CTRL_AS = 'EditCtrl';

      Admin_UserGroups_Ctrl_Edit.DEPS = ['$stateParams'];

      Admin_UserGroups_Ctrl_Edit.prototype.init = function() {
        this.ugData = this.DataService.get('UserGroups');
        return this.group = null;
      };

      Admin_UserGroups_Ctrl_Edit.prototype.initialLoad = function() {
        var promise,
          _this = this;
        promise = this.ugData.loadEditUserGroupData(this.$stateParams.id || null).then(function(data) {
          var _ref1, _ref2;
          _this.group = data.group;
          _this.form = data.form;
          _this.perm_form = _this.group.perms;
          _this.perm_form.options = {};
          if (_this.group.id !== 1) {
            _this.perm_form_reg = data.reg_group.perms;
          } else {
            _this.perm_form_reg = null;
          }
          if (_this.perm_form.ticket.reopen_resolved_createnew || ((_ref1 = _this.perm_form_reg) != null ? (_ref2 = _ref1.ticket) != null ? _ref2.reopen_resolved_createnew : void 0 : void 0)) {
            return _this.perm_form.options.reopen_resolved_createnew = 'new_ticket';
          } else {
            return _this.perm_form.options.reopen_resolved_createnew = 'reject';
          }
        });
        return promise;
      };

      Admin_UserGroups_Ctrl_Edit.prototype.saveForm = function() {
        var is_new, promise,
          _this = this;
        if (!this.$scope.form_props.$valid) {
          return;
        }
        is_new = !this.group.id;
        promise = this.ugData.saveFormModel(this.group, this.form, this.perm_form);
        this.startSpinner('saving');
        return promise.then(function() {
          _this.stopSpinner('saving', true).then(function() {
            return _this.Growl.success("Saved");
          });
          _this.skipDirtyState();
          if (is_new) {
            return _this.$state.go('crm.groups.gocreate');
          }
        });
      };

      return Admin_UserGroups_Ctrl_Edit;

    })(Admin_Ctrl_Base);
    return Admin_UserGroups_Ctrl_Edit.EXPORT_CTRL();
  });

}).call(this);

/*
//@ sourceMappingURL=Edit.js.map
*/