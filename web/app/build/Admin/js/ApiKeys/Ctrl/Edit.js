(function() {
  var __hasProp = {}.hasOwnProperty,
    __extends = function(child, parent) { for (var key in parent) { if (__hasProp.call(parent, key)) child[key] = parent[key]; } function ctor() { this.constructor = child; } ctor.prototype = parent.prototype; child.prototype = new ctor(); child.__super__ = parent.prototype; return child; };

  define(['Admin/Main/Ctrl/Base', 'angular'], function(Admin_Ctrl_Base, angular) {
    var Admin_ApiKeys_Ctrl_Edit;
    Admin_ApiKeys_Ctrl_Edit = (function(_super) {
      __extends(Admin_ApiKeys_Ctrl_Edit, _super);

      function Admin_ApiKeys_Ctrl_Edit() {
        return Admin_ApiKeys_Ctrl_Edit.__super__.constructor.apply(this, arguments);
      }

      Admin_ApiKeys_Ctrl_Edit.CTRL_ID = 'Admin_ApiKeys_Ctrl_Edit';

      Admin_ApiKeys_Ctrl_Edit.CTRL_AS = 'EditCtrl';

      Admin_ApiKeys_Ctrl_Edit.DEPS = ['$stateParams'];

      Admin_ApiKeys_Ctrl_Edit.prototype.init = function() {
        this.agents = [];
        this.form = {
          isSuperUser: false,
          flags: []
        };
        this.service = {
          keys: this.DataService.get('ApiKeys'),
          agents: this.DataService.get('Agents')
        };
        return this.$scope.replayLogEntry = (function(_this) {
          return function(entry) {
            if ((entry != null ? entry.id : void 0) == null) {
              return;
            }
            entry.response = null;
            return _this.service.keys.replayLogEntry(entry).then(function(data) {
              return entry.response = data;
            }, function() {
              return entry.response = {
                status: null,
                content: null
              };
            });
          };
        })(this);
      };

      Admin_ApiKeys_Ctrl_Edit.prototype.initialLoad = function() {
        this.service.keys.get(this.$stateParams.id || null).then((function(_this) {
          return function(model) {
            if (model == null) {
              return;
            }
            _this.form = angular.copy(model);
            _this.form.flags = _this.form.flags || [];
            _this.form.isSuperUser = _this.form.flags.indexOf('super') > -1;
            return _this.form.isAdminManage = _this.form.flags.indexOf('admin_manage') > -1;
          };
        })(this));
        return this.service.agents.all().then((function(_this) {
          return function(agents) {
            return _this.agents = agents;
          };
        })(this));
      };

      Admin_ApiKeys_Ctrl_Edit.prototype.saveForm = function() {
        var is_new;
        is_new = !this.form.id;
        this.form.flags = [];
        if (this.form.isSuperUser) {
          this.form.flags.push('super');
        }
        if (this.form.isAdminManage) {
          this.form.flags.push('admin_manage');
        }
        this.startSpinner('saving');
        return this.service.keys.set(this.form).then((function(_this) {
          return function() {
            _this.stopSpinner('saving', true);
            _this.Growl.success('Saved');
            _this.skipDirtyState();
            if (is_new) {
              return _this.$state.go('apps.api_keys.gocreate');
            }
          };
        })(this), (function(_this) {
          return function() {
            _this.stopSpinner('saving', true);
            return _this.Growl.error('Error');
          };
        })(this));
      };


      /*
      		 * Show the delete dlg
       */

      Admin_ApiKeys_Ctrl_Edit.prototype.startDelete = function(for_key_id) {
        return this.service.keys.get(for_key_id).then((function(_this) {
          return function(key) {
            var inst;
            if (key == null) {
              return;
            }
            inst = _this.$modal.open({
              templateUrl: _this.getTemplatePath('ApiKeys/delete-modal.html'),
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
              return _this.service.keys.remove(key).then(function() {
                return _this.$state.go('apps.api_keys');
              }, function(data) {
                return _this.applyErrorResponseToView(data);
              });
            });
          };
        })(this));
      };

      Admin_ApiKeys_Ctrl_Edit.prototype.regenerateApiKey = function() {
        return this.service.keys.regenerateApiKey(this.form).success((function(_this) {
          return function() {
            return _this.Growl.success("API Key regenerated");
          };
        })(this));
      };

      return Admin_ApiKeys_Ctrl_Edit;

    })(Admin_Ctrl_Base);
    return Admin_ApiKeys_Ctrl_Edit.EXPORT_CTRL();
  });

}).call(this);

//# sourceMappingURL=Edit.js.map
