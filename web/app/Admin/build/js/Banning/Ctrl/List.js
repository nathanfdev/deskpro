(function() {
  var __hasProp = {}.hasOwnProperty,
    __extends = function(child, parent) { for (var key in parent) { if (__hasProp.call(parent, key)) child[key] = parent[key]; } function ctor() { this.constructor = child; } ctor.prototype = parent.prototype; child.prototype = new ctor(); child.__super__ = parent.prototype; return child; };

  define(['Admin/Main/Ctrl/Base'], function(Admin_Ctrl_Base) {
    var Admin_Banning_Ctrl_List, _ref;
    Admin_Banning_Ctrl_List = (function(_super) {
      __extends(Admin_Banning_Ctrl_List, _super);

      function Admin_Banning_Ctrl_List() {
        _ref = Admin_Banning_Ctrl_List.__super__.constructor.apply(this, arguments);
        return _ref;
      }

      Admin_Banning_Ctrl_List.CTRL_ID = 'Admin_Banning_Ctrl_List';

      Admin_Banning_Ctrl_List.CTRL_AS = 'ListCtrl';

      Admin_Banning_Ctrl_List.prototype.init = function() {
        return this.banData = this.DataService.get('Bans');
      };

      /*
      		# Loads the list
      */


      Admin_Banning_Ctrl_List.prototype.initialLoad = function() {
        var promise,
          _this = this;
        promise = this.banData.loadList().then(function(list) {
          _this.list = list;
          _this.pagination = _this.banData.getPagination();
          return _this.initializeScopeWatching();
        });
        return promise;
      };

      /*
      		#	Here we watching scope 'page' variable in order to load new page of results
      */


      Admin_Banning_Ctrl_List.prototype.initializeScopeWatching = function() {
        var _this = this;
        return this.$scope.$watch('ListCtrl.pagination', function(newVal, oldVal) {
          if (parseInt(newVal.ip_bans.page) === parseInt(oldVal.ip_bans.page)) {
            return void 0;
          }
          if (isNaN(parseInt(newVal.ip_bans.page))) {
            return void 0;
          }
          return _this.banData.refreshList().then(function(list) {
            _this.list = list;
            return _this.pagination = _this.banData.getPagination();
          });
        }, true);
      };

      /*
      		# Show the delete dlg
      */


      Admin_Banning_Ctrl_List.prototype.startDelete = function(for_ban_id) {
        var inst, key,
          _this = this;
        key = this.banData.findListModelById(for_ban_id);
        inst = this.$modal.open({
          templateUrl: this.getTemplatePath('Banning/delete-modal.html'),
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
          return _this.deleteBan(key);
        });
      };

      /*
      		# Actually do the delete
      */


      Admin_Banning_Ctrl_List.prototype.deleteBan = function(for_ban) {
        var key,
          _this = this;
        if (for_ban.banned_ip) {
          key = 'ip';
        }
        if (for_ban.banned_email) {
          key = 'email';
        }
        return this.banData.deleteBanById(for_ban['banned_' + key]).success(function() {
          if (_this.$state.current.name === ('crm.banning.edit_' + key) && _this.$state.params.ban === for_ban['banned_' + key]) {
            return _this.$state.go('crm.banning');
          }
        }).error(function(info, code) {
          return _this.applyErrorResponseToView(info);
        });
      };

      return Admin_Banning_Ctrl_List;

    })(Admin_Ctrl_Base);
    return Admin_Banning_Ctrl_List.EXPORT_CTRL();
  });

}).call(this);

/*
//@ sourceMappingURL=List.js.map
*/