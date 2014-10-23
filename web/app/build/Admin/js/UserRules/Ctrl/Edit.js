(function() {
  var __hasProp = {}.hasOwnProperty,
    __extends = function(child, parent) { for (var key in parent) { if (__hasProp.call(parent, key)) child[key] = parent[key]; } function ctor() { this.constructor = child; } ctor.prototype = parent.prototype; child.prototype = new ctor(); child.__super__ = parent.prototype; return child; };

  define(['Admin/Main/Ctrl/Base'], function(Admin_Ctrl_Base) {
    var Admin_UserRules_Ctrl_Edit;
    Admin_UserRules_Ctrl_Edit = (function(_super) {
      __extends(Admin_UserRules_Ctrl_Edit, _super);

      function Admin_UserRules_Ctrl_Edit() {
        return Admin_UserRules_Ctrl_Edit.__super__.constructor.apply(this, arguments);
      }

      Admin_UserRules_Ctrl_Edit.CTRL_ID = 'Admin_UserRules_Ctrl_Edit';

      Admin_UserRules_Ctrl_Edit.CTRL_AS = 'EditCtrl';

      Admin_UserRules_Ctrl_Edit.DEPS = ['$stateParams', 'Api'];

      Admin_UserRules_Ctrl_Edit.prototype.init = function() {
        this.userRulesData = this.DataService.get('UserRules');
        this.user_rule = null;
        this.apply_log = '';
        return this.apply_started = false;
      };

      Admin_UserRules_Ctrl_Edit.prototype.initialLoad = function() {
        var promise;
        promise = this.userRulesData.loadEditUserRuleData(this.$stateParams.id || null).then((function(_this) {
          return function(data) {
            _this.user_rule = data.user_rule;
            return _this.form = data.form;
          };
        })(this));
        return promise;
      };

      Admin_UserRules_Ctrl_Edit.prototype.saveForm = function() {
        var is_new, promise;
        is_new = !this.user_rule.id;
        promise = this.userRulesData.saveFormModel(this.user_rule, this.form);
        this.startSpinner('saving');
        return promise.then((function(_this) {
          return function() {
            _this.stopSpinner('saving', true).then(function() {
              return _this.Growl.success("Saved");
            });
            _this.skipDirtyState();
            if (is_new) {
              return _this.$state.go('crm.rules.gocreate');
            }
          };
        })(this));
      };


      /*
       		 * Applying current user rule to all users
       */

      Admin_UserRules_Ctrl_Edit.prototype.applyRuleToUsers = function() {
        var doRequest, page;
        page = -1;
        this.apply_started = true;
        doRequest = (function(_this) {
          return function() {
            page++;
            return _this.Api.sendGet('/user_rules_apply/' + _this.user_rule.id + '/page_' + page).success(function(result) {
              if (!result.completed && result.success) {
                _this.apply_log += 'Done batch #' + (page + 1) + ' ...<br>';
                return doRequest();
              } else {
                return _this.apply_log += 'Completed<br>';
              }
            }).error(function() {
              return _this.apply_log = 'Error occurred<br>';
            });
          };
        })(this);
        return doRequest();
      };

      return Admin_UserRules_Ctrl_Edit;

    })(Admin_Ctrl_Base);
    return Admin_UserRules_Ctrl_Edit.EXPORT_CTRL();
  });

}).call(this);

//# sourceMappingURL=Edit.js.map
