(function() {
  var __hasProp = {}.hasOwnProperty,
    __extends = function(child, parent) { for (var key in parent) { if (__hasProp.call(parent, key)) child[key] = parent[key]; } function ctor() { this.constructor = child; } ctor.prototype = parent.prototype; child.prototype = new ctor(); child.__super__ = parent.prototype; return child; };

  define(['Admin/Main/Ctrl/Base'], function(Admin_Ctrl_Base) {
    var Admin_UserRules_Ctrl_List, _ref;
    Admin_UserRules_Ctrl_List = (function(_super) {
      __extends(Admin_UserRules_Ctrl_List, _super);

      function Admin_UserRules_Ctrl_List() {
        _ref = Admin_UserRules_Ctrl_List.__super__.constructor.apply(this, arguments);
        return _ref;
      }

      Admin_UserRules_Ctrl_List.CTRL_ID = 'Admin_UserRules_Ctrl_List';

      Admin_UserRules_Ctrl_List.CTRL_AS = 'ListCtrl';

      Admin_UserRules_Ctrl_List.prototype.init = function() {
        return this.userRulesData = this.DataService.get('UserRules');
      };

      /*
      		# Loads the list
      */


      Admin_UserRules_Ctrl_List.prototype.initialLoad = function() {
        var promise,
          _this = this;
        promise = this.userRulesData.loadList().then(function(list) {
          return _this.list = list;
        });
        return promise;
      };

      /*
      		# Show the delete dlg
      */


      Admin_UserRules_Ctrl_List.prototype.startDelete = function(for_rule_id) {
        var inst, rule,
          _this = this;
        rule = this.userRulesData.findListModelById(for_rule_id);
        inst = this.$modal.open({
          templateUrl: this.getTemplatePath('UserRules/delete-modal.html'),
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
          return _this.deleteUserRule(rule);
        });
      };

      /*
      		# Actually do the delete
      */


      Admin_UserRules_Ctrl_List.prototype.deleteUserRule = function(for_rule) {
        var _this = this;
        return this.userRulesData.deleteUserRuleById(for_rule.id).success(function() {
          if (_this.$state.current.name === 'crm.rules.edit' && parseInt(_this.$state.params.id) === for_rule.id) {
            return _this.$state.go('crm.rules');
          }
        }).error(function(info, code) {
          return _this.applyErrorResponseToView(info);
        });
      };

      return Admin_UserRules_Ctrl_List;

    })(Admin_Ctrl_Base);
    return Admin_UserRules_Ctrl_List.EXPORT_CTRL();
  });

}).call(this);

/*
//@ sourceMappingURL=List.js.map
*/