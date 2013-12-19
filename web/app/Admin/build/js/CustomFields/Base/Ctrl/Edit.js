(function() {
  var __hasProp = {}.hasOwnProperty,
    __extends = function(child, parent) { for (var key in parent) { if (__hasProp.call(parent, key)) child[key] = parent[key]; } function ctor() { this.constructor = child; } ctor.prototype = parent.prototype; child.prototype = new ctor(); child.__super__ = parent.prototype; return child; };

  define(['Admin/Main/Ctrl/Base'], function(Admin_Ctrl_Base) {
    var Admin_CustomFields_Base_Ctrl_Edit, _ref;
    return Admin_CustomFields_Base_Ctrl_Edit = (function(_super) {
      __extends(Admin_CustomFields_Base_Ctrl_Edit, _super);

      function Admin_CustomFields_Base_Ctrl_Edit() {
        _ref = Admin_CustomFields_Base_Ctrl_Edit.__super__.constructor.apply(this, arguments);
        return _ref;
      }

      Admin_CustomFields_Base_Ctrl_Edit.CTRL_ID = 'Admin_CustomFields_Base_Ctrl_Edit';

      Admin_CustomFields_Base_Ctrl_Edit.CTRL_AS = 'EditCtrl';

      Admin_CustomFields_Base_Ctrl_Edit.DEPS = [];

      Admin_CustomFields_Base_Ctrl_Edit.prototype.init = function() {
        this.field_type = '0';
        this.field_type_chooser = 'text';
        this.fieldDataService = this.getDataService();
      };

      Admin_CustomFields_Base_Ctrl_Edit.prototype.initialLoad = function() {
        var promise,
          _this = this;
        promise = this.fieldDataService.loadEditFieldData(this.$stateParams.id || null).then(function(data) {
          _this.field = data.field;
          _this.field_type = data.field_type;
          return _this.form = data.form;
        });
        return promise;
      };

      Admin_CustomFields_Base_Ctrl_Edit.prototype.getDataService = function() {
        throw new Error("Not implemented");
      };

      Admin_CustomFields_Base_Ctrl_Edit.prototype.getBaseRouteName = function() {
        throw new Error("Not implemented");
      };

      Admin_CustomFields_Base_Ctrl_Edit.prototype.saveForm = function() {
        var is_new, promise,
          _this = this;
        if (!this.$scope.form_props.$valid) {
          return;
        }
        is_new = !this.field.id;
        this.field.type_name = this.field_type;
        promise = this.fieldDataService.saveFormModel(this.field, this.form);
        this.startSpinner('saving');
        promise.success(function() {
          _this.stopSpinner('saving', true).then(function() {
            return _this.Growl.success('Saved');
          });
          _this.skipDirtyState();
          if (is_new) {
            return _this.$state.go(_this.getBaseRouteName() + ".gocreate");
          } else {
            return _this.$state.go(_this.getBaseRouteName());
          }
        });
        return promise.error(function(info, code) {
          _this.stopSpinner('saving', true);
          return _this.applyErrorResponseToView(info);
        });
      };

      return Admin_CustomFields_Base_Ctrl_Edit;

    })(Admin_Ctrl_Base);
  });

}).call(this);

/*
//@ sourceMappingURL=Edit.js.map
*/