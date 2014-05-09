(function() {
  var __hasProp = {}.hasOwnProperty,
    __extends = function(child, parent) { for (var key in parent) { if (__hasProp.call(parent, key)) child[key] = parent[key]; } function ctor() { this.constructor = child; } ctor.prototype = parent.prototype; child.prototype = new ctor(); child.__super__ = parent.prototype; return child; };

  define(['Admin/Main/Ctrl/Base'], function(Admin_Ctrl_Base) {
    var Admin_CustomFields_Base_Ctrl_Edit;
    return Admin_CustomFields_Base_Ctrl_Edit = (function(_super) {
      __extends(Admin_CustomFields_Base_Ctrl_Edit, _super);

      function Admin_CustomFields_Base_Ctrl_Edit() {
        return Admin_CustomFields_Base_Ctrl_Edit.__super__.constructor.apply(this, arguments);
      }

      Admin_CustomFields_Base_Ctrl_Edit.CTRL_ID = 'Admin_CustomFields_Base_Ctrl_Edit';

      Admin_CustomFields_Base_Ctrl_Edit.CTRL_AS = 'EditCtrl';

      Admin_CustomFields_Base_Ctrl_Edit.DEPS = [];

      Admin_CustomFields_Base_Ctrl_Edit.prototype.init = function() {
        this.field_id = parseInt(this.$stateParams.id || 0);
        this.field_type = '0';
        this.field_type_chooser = 'text';
        this.fieldDataService = this.getDataService();
      };

      Admin_CustomFields_Base_Ctrl_Edit.prototype.postLoad = function() {};

      Admin_CustomFields_Base_Ctrl_Edit.prototype.initialLoadExtra = function() {};

      Admin_CustomFields_Base_Ctrl_Edit.prototype.initialLoad = function() {
        var p, promise;
        p = this.initialLoadExtra();
        promise = this.fieldDataService.loadEditFieldData(this.$stateParams.id || null).then((function(_this) {
          return function(data) {
            _this.field = data.field;
            _this.field_type = data.field_type;
            _this.form = data.form;
            return _this.postLoad();
          };
        })(this));
        if (p) {
          return this.$q.all([promise, p]);
        } else {
          return promise;
        }
      };

      Admin_CustomFields_Base_Ctrl_Edit.prototype.getDataService = function() {
        throw new Error("Not implemented");
      };

      Admin_CustomFields_Base_Ctrl_Edit.prototype.getBaseRouteName = function() {
        throw new Error("Not implemented");
      };

      Admin_CustomFields_Base_Ctrl_Edit.prototype.postSave = function() {};

      Admin_CustomFields_Base_Ctrl_Edit.prototype.saveForm = function() {
        var is_new, promise, successFn;
        if (!this.$scope.form_props.$valid) {
          return;
        }
        is_new = !this.field.id;
        this.field.type_name = this.field_type;
        promise = this.fieldDataService.saveFormModel(this.field, this.form);
        this.startSpinner('saving');
        successFn = (function(_this) {
          return function() {
            _this.stopSpinner('saving', true).then(function() {
              return _this.Growl.success('Saved');
            });
            _this.skipDirtyState();
            if (is_new) {
              return _this.$state.go(_this.getBaseRouteName() + ".gocreate");
            }
          };
        })(this);
        promise.success((function(_this) {
          return function(data) {
            var v;
            _this.field = data.field_id;
            _this.field_id = data.field_id;
            v = _this.postSave();
            if (v && v.then) {
              return v.then(function() {
                return successFn();
              });
            } else {
              return successFn();
            }
          };
        })(this));
        return promise.error((function(_this) {
          return function(info, code) {
            _this.stopSpinner('saving', true);
            return _this.applyErrorResponseToView(info);
          };
        })(this));
      };

      return Admin_CustomFields_Base_Ctrl_Edit;

    })(Admin_Ctrl_Base);
  });

}).call(this);

//# sourceMappingURL=Edit.js.map
