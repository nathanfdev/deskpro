(function() {
  var __hasProp = {}.hasOwnProperty,
    __extends = function(child, parent) { for (var key in parent) { if (__hasProp.call(parent, key)) child[key] = parent[key]; } function ctor() { this.constructor = child; } ctor.prototype = parent.prototype; child.prototype = new ctor(); child.__super__ = parent.prototype; return child; };

  define(['Admin/Main/Ctrl/Base'], function(Admin_Ctrl_Base) {
    var Admin_UserRules_Ctrl_Edit, _ref;
    Admin_UserRules_Ctrl_Edit = (function(_super) {
      __extends(Admin_UserRules_Ctrl_Edit, _super);

      function Admin_UserRules_Ctrl_Edit() {
        _ref = Admin_UserRules_Ctrl_Edit.__super__.constructor.apply(this, arguments);
        return _ref;
      }

      Admin_UserRules_Ctrl_Edit.CTRL_ID = 'Admin_UserRules_Ctrl_Edit';

      Admin_UserRules_Ctrl_Edit.CTRL_AS = 'EditCtrl';

      Admin_UserRules_Ctrl_Edit.DEPS = ['$stateParams'];

      Admin_UserRules_Ctrl_Edit.prototype.init = function() {
        this.userRulesData = this.DataService.get('UserRules');
        return this.user_rule = null;
      };

      /*
       	#
      */


      Admin_UserRules_Ctrl_Edit.prototype.initialLoad = function() {
        var promise,
          _this = this;
        promise = this.userRulesData.loadEditUserRuleData(this.$stateParams.id || null).then(function(data) {
          _this.user_rule = data.user_rule;
          return _this.form = data.form;
        });
        return promise;
      };

      /*
      		#
      */


      Admin_UserRules_Ctrl_Edit.prototype.saveForm = function() {
        var is_new, promise,
          _this = this;
        if (!this.$scope.form_props.$valid) {
          return;
        }
        is_new = !this.user_rule.id;
        promise = this.userRulesData.saveFormModel(this.user_rule, this.form);
        this.startSpinner('saving');
        return promise.then(function() {
          _this.stopSpinner('saving', true).then(function() {
            return _this.Growl.success("Saved");
          });
          _this.skipDirtyState();
          if (is_new) {
            return _this.$state.go('crm..gocreate');
          }
        });
      };

      return Admin_UserRules_Ctrl_Edit;

    })(Admin_Ctrl_Base);
    return Admin_UserRules_Ctrl_Edit.EXPORT_CTRL();
  });

}).call(this);

/*
//@ sourceMappingURL=Edit.js.map
*/