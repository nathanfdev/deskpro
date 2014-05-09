(function() {
  var __hasProp = {}.hasOwnProperty,
    __extends = function(child, parent) { for (var key in parent) { if (__hasProp.call(parent, key)) child[key] = parent[key]; } function ctor() { this.constructor = child; } ctor.prototype = parent.prototype; child.prototype = new ctor(); child.__super__ = parent.prototype; return child; };

  define(['Admin/Main/Ctrl/Base'], function(Admin_Ctrl_Base) {
    var Admin_ApiKeys_Ctrl_List;
    Admin_ApiKeys_Ctrl_List = (function(_super) {
      __extends(Admin_ApiKeys_Ctrl_List, _super);

      function Admin_ApiKeys_Ctrl_List() {
        return Admin_ApiKeys_Ctrl_List.__super__.constructor.apply(this, arguments);
      }

      Admin_ApiKeys_Ctrl_List.CTRL_ID = 'Admin_ApiKeys_Ctrl_List';

      Admin_ApiKeys_Ctrl_List.CTRL_AS = 'ListCtrl';

      Admin_ApiKeys_Ctrl_List.prototype.init = function() {
        return this.keyData = this.DataService.get('ApiKeys');
      };


      /*
      		 * Loads the list
       */

      Admin_ApiKeys_Ctrl_List.prototype.initialLoad = function() {
        var promise;
        promise = this.keyData.loadList().then((function(_this) {
          return function(list) {
            return _this.list = list;
          };
        })(this));
        return promise;
      };


      /*
      		 * Show the delete dlg
       */

      Admin_ApiKeys_Ctrl_List.prototype.startDelete = function(for_key_id) {
        var inst, key;
        key = this.keyData.findListModelById(for_key_id);
        inst = this.$modal.open({
          templateUrl: this.getTemplatePath('ApiKeys/delete-modal.html'),
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
            return _this.deleteApiKey(key);
          };
        })(this));
      };


      /*
      		 * Actually do the delete
       */

      Admin_ApiKeys_Ctrl_List.prototype.deleteApiKey = function(for_key) {
        return this.keyData.deleteApiKeyById(for_key.id).success((function(_this) {
          return function() {
            if (_this.$state.current.name === 'apps.api_keys.edit' && parseInt(_this.$state.params.id) === for_key.id) {
              return _this.$state.go('apps.api_keys');
            }
          };
        })(this)).error((function(_this) {
          return function(info, code) {
            return _this.applyErrorResponseToView(info);
          };
        })(this));
      };

      return Admin_ApiKeys_Ctrl_List;

    })(Admin_Ctrl_Base);
    return Admin_ApiKeys_Ctrl_List.EXPORT_CTRL();
  });

}).call(this);

//# sourceMappingURL=List.js.map
