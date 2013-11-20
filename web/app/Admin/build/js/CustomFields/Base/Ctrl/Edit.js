(function() {
  var __hasProp = {}.hasOwnProperty,
    __extends = function(child, parent) { for (var key in parent) { if (__hasProp.call(parent, key)) child[key] = parent[key]; } function ctor() { this.constructor = child; } ctor.prototype = parent.prototype; child.prototype = new ctor(); child.__super__ = parent.prototype; return child; };

  define(['Admin/Main/Ctrl/Base', 'Admin/CustomFields/FieldFormMapper'], function(Admin_Ctrl_Base, FieldFormMapper) {
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
        this.formMapper = new FieldFormMapper();
      };

      Admin_CustomFields_Base_Ctrl_Edit.prototype.initialLoad = function() {
        var promise,
          _this = this;
        if (this.$stateParams.id) {
          promise = this.getField();
          promise.then(function(result) {
            _this.field = result.data.field;
            _this.field_type = _this.field.type_name;
            return _this.form = _this.formMapper.getFormFromModel(_this.field);
          });
          return promise;
        } else {
          this.field = {};
          this.form = this.formMapper.getFormFromModel(this.field);
          return null;
        }
      };

      return Admin_CustomFields_Base_Ctrl_Edit;

    })(Admin_Ctrl_Base);
  });

}).call(this);

/*
//@ sourceMappingURL=Edit.js.map
*/