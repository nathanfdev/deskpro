(function() {
  var __hasProp = {}.hasOwnProperty,
    __extends = function(child, parent) { for (var key in parent) { if (__hasProp.call(parent, key)) child[key] = parent[key]; } function ctor() { this.constructor = child; } ctor.prototype = parent.prototype; child.prototype = new ctor(); child.__super__ = parent.prototype; return child; };

  define(['Admin/Main/Ctrl/Base'], function(Admin_Ctrl_Base) {
    var Admin_Banning_Ctrl_List;
    Admin_Banning_Ctrl_List = (function(_super) {
      __extends(Admin_Banning_Ctrl_List, _super);

      function Admin_Banning_Ctrl_List() {
        return Admin_Banning_Ctrl_List.__super__.constructor.apply(this, arguments);
      }

      Admin_Banning_Ctrl_List.CTRL_ID = 'Admin_Banning_Ctrl_List';

      Admin_Banning_Ctrl_List.CTRL_AS = 'ListCtrl';

      Admin_Banning_Ctrl_List.prototype.init = function() {
        return this.banData = this.DataService.get('Bans');
      };


      /*
      		 * Loads the list
       */

      Admin_Banning_Ctrl_List.prototype.initialLoad = function() {
        var promise;
        promise = this.banData.loadList().then((function(_this) {
          return function(list) {
            _this.list = list;
            _this.pagination = _this.banData.getPagination();
            _this.search_phrase = _this.banData.getSearchPhrase();
            return _this.initializeScopeWatching();
          };
        })(this));
        return promise;
      };


      /*
      		 *	Here we watching scope 'page' variable in order to load new page of results
       	 * Reason - 'ng-change' is not working for ui-select2
       */

      Admin_Banning_Ctrl_List.prototype.initializeScopeWatching = function() {
        return this.$scope.$watch('ListCtrl.pagination', (function(_this) {
          return function(newVal, oldVal) {
            var email_bans_page_new, email_bans_page_old, ip_bans_page_new, ip_bans_page_old;
            ip_bans_page_old = parseInt(oldVal.ip_bans.page);
            ip_bans_page_new = parseInt(newVal.ip_bans.page);
            email_bans_page_old = parseInt(newVal.email_bans.page);
            email_bans_page_new = parseInt(oldVal.email_bans.page);
            if (ip_bans_page_old === ip_bans_page_new && email_bans_page_old === email_bans_page_new) {
              return void 0;
            }
            if (isNaN(ip_bans_page_new) && isNaN(email_bans_page_new)) {
              return void 0;
            }
            return _this.reloadList(ip_bans_page_old !== ip_bans_page_new, email_bans_page_old !== email_bans_page_new);
          };
        })(this), true);
      };


      /*
       	 * Reloads the lists with bans taking into current page & search phrase
       	 *
      		 * @param {Boolean} reload_ip - whether we want to reload list with ip bans
       	 * @param {Boolean} reload_email - whether we want to reload list with email bans
       */

      Admin_Banning_Ctrl_List.prototype.reloadList = function(reload_ip, reload_email) {
        if (reload_ip) {
          this.startSpinner('paginating_ip_bans');
        }
        if (reload_email) {
          this.startSpinner('paginating_email_bans');
        }
        return this.banData.refreshList().then((function(_this) {
          return function(list) {
            if (reload_ip) {
              _this.stopSpinner('paginating_ip_bans', true);
            }
            if (reload_email) {
              _this.stopSpinner('paginating_email_bans', true);
            }
            _this.list = list;
            return _this.pagination = _this.banData.getPagination();
          };
        })(this));
      };


      /*
       	 *
       */

      Admin_Banning_Ctrl_List.prototype.goNextIpBanPage = function() {
        return this.pagination.ip_bans.page++;
      };


      /*
      		 *
       */

      Admin_Banning_Ctrl_List.prototype.goPrevIpBanPage = function() {
        return this.pagination.ip_bans.page--;
      };


      /*
      		 *
       */

      Admin_Banning_Ctrl_List.prototype.goFirstIpBanPage = function() {
        return this.pagination.ip_bans.page = 0;
      };


      /*
       	   *
       */

      Admin_Banning_Ctrl_List.prototype.goNextEmailBanPage = function() {
        return this.pagination.email_bans.page++;
      };


      /*
      		 *
       */

      Admin_Banning_Ctrl_List.prototype.goPrevEmailBanPage = function() {
        return this.pagination.email_bans.page--;
      };


      /*
      		 *
       */

      Admin_Banning_Ctrl_List.prototype.goFirstEmailBanPage = function() {
        return this.pagination.email_bans.page = 0;
      };


      /*
      		 * Show the delete dlg
       */

      Admin_Banning_Ctrl_List.prototype.startDelete = function(for_ban_id) {
        var inst, key;
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
        return inst.result.then((function(_this) {
          return function() {
            return _this.deleteBan(key);
          };
        })(this));
      };


      /*
      		 * Actually do the delete
       */

      Admin_Banning_Ctrl_List.prototype.deleteBan = function(for_ban) {
        var key;
        if (for_ban.banned_ip) {
          key = 'ip';
        }
        if (for_ban.banned_email) {
          key = 'email';
        }
        return this.banData.deleteBanById(for_ban['banned_' + key]).success((function(_this) {
          return function() {
            if (_this.$state.current.name === ('crm.banning.edit_' + key) && _this.$state.params.ban === for_ban['banned_' + key]) {
              return _this.$state.go('crm.banning');
            }
          };
        })(this)).error((function(_this) {
          return function(info, code) {
            return _this.applyErrorResponseToView(info);
          };
        })(this));
      };


      /*
      		 * Show the delete dlg
       */

      Admin_Banning_Ctrl_List.prototype.deleteList = function(list) {
        var inst;
        inst = this.$modal.open({
          templateUrl: this.getTemplatePath('Banning/delete-modal.html'),
          controller: [
            '$scope', '$modalInstance', function($scope, $modalInstance) {
              $scope.multiple = true;
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
            if (list === _this.list.email_bans) {
              return _this.banData.deleteBanByType('email').then(function() {
                _this.reloadList(false, true);
                return _this.$state.go('crm.banning');
              });
            } else {
              return _this.banData.deleteBanByType('ip').then(function() {
                _this.reloadList(true);
                return _this.$state.go('crm.banning');
              });
            }
          };
        })(this));
      };

      return Admin_Banning_Ctrl_List;

    })(Admin_Ctrl_Base);
    return Admin_Banning_Ctrl_List.EXPORT_CTRL();
  });

}).call(this);

//# sourceMappingURL=List.js.map
