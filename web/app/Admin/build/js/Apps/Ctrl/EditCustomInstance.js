(function() {
  var __hasProp = {}.hasOwnProperty,
    __extends = function(child, parent) { for (var key in parent) { if (__hasProp.call(parent, key)) child[key] = parent[key]; } function ctor() { this.constructor = child; } ctor.prototype = parent.prototype; child.prototype = new ctor(); child.__super__ = parent.prototype; return child; };

  define(['Admin/Main/Ctrl/Base', 'DeskPRO/Util/Util'], function(Admin_Ctrl_Base, Util) {
    var Admin_Apps_Ctrl_EditCustomInstance;
    Admin_Apps_Ctrl_EditCustomInstance = (function(_super) {
      __extends(Admin_Apps_Ctrl_EditCustomInstance, _super);

      function Admin_Apps_Ctrl_EditCustomInstance() {
        return Admin_Apps_Ctrl_EditCustomInstance.__super__.constructor.apply(this, arguments);
      }

      Admin_Apps_Ctrl_EditCustomInstance.CTRL_ID = 'Admin_Apps_Ctrl_EditCustomInstance';

      Admin_Apps_Ctrl_EditCustomInstance.CTRL_AS = 'Ctrl';

      Admin_Apps_Ctrl_EditCustomInstance.DEPS = [];

      Admin_Apps_Ctrl_EditCustomInstance.prototype.init = function() {
        this.$scope.setting_values = {};
        this.instanceId = parseInt(this.$stateParams.custom_id.replace(/^custom_/, ''));
        this.$scope.aceLoaded = function(editor) {
          var maxH, updateH;
          maxH = 500;
          updateH = function() {
            var newHeight;
            newHeight = editor.getSession().getScreenLength() * editor.renderer.lineHeight + editor.renderer.scrollBar.getWidth();
            if (newHeight > maxH) {
              newHeight = maxH;
            }
            if (newHeight < 100) {
              newHeight = 100;
            }
            $(editor.container).height(newHeight);
            return editor.resize();
          };
          updateH();
          editor.getSession().on('change', updateH);
          editor.setShowPrintMargin(false);
          return $(editor.container).closest('div.editor').data('ace-editor', editor).addClass('with-ace-editor');
        };
      };

      Admin_Apps_Ctrl_EditCustomInstance.prototype.initialLoad = function() {
        var d;
        d = this.$q.defer();
        this.Api.sendDataGet({
          app: '/apps/instances/' + this.instanceId
        }).then((function(_this) {
          return function(result) {
            _this.app = result.data.app.app;
            _this.$scope.$parent.ListCtrl.ensureCustomAppInList(_this.app);
            return _this.Api.sendDataGet({
              pack: '/apps/packages/' + _this.app.package_name,
              assets: '/apps/custom/' + _this.instanceId + '/assets'
            }).then(function(result) {
              var app_js, asset_groups, assets;
              _this.pack = result.data.pack['package'];
              assets = result.data.assets.assets;
              asset_groups = {
                "main": [],
                "ticket": [],
                "user": [],
                "org": []
              };
              app_js = assets.filter(function(x) {
                return x.tag === 'app_js';
              })[0];
              if (app_js) {
                asset_groups.main.push({
                  title: "App Definition",
                  js: app_js.file_content,
                  js_id: app_js.id,
                  js_name: app_js.name
                });
              }
              asset_groups.ticket = _this._getGroupedAssets(assets.filter(function(x) {
                return x.name.indexOf('Ticket/') !== -1;
              }));
              asset_groups.user = _this._getGroupedAssets(assets.filter(function(x) {
                return x.name.indexOf('User/') !== -1;
              }));
              asset_groups.org = _this._getGroupedAssets(assets.filter(function(x) {
                return x.name.indexOf('Org/') !== -1;
              }));
              _this.asset_groups = asset_groups;
              return d.resolve();
            });
          };
        })(this));
        d.promise.then((function(_this) {
          return function() {
            _this.$scope.pack = _this.pack;
            _this.$scope.setting_values = _this.app.settings;
            if (!_this.$scope.setting_values || Util.isArray(_this.$scope.setting_values)) {
              _this.$scope.setting_values = {};
            }
            return _this.$scope.setting_values.dp_app = {
              title: _this.app.title
            };
          };
        })(this));
        return d.promise;
      };

      Admin_Apps_Ctrl_EditCustomInstance.prototype._getGroupedAssets = function(assets) {
        var app_context, asset, groups, html_asset, _i, _len;
        groups = [];
        app_context = assets.filter(function(x) {
          return x.tag === 'js' && x.name.indexOf('Context.js') !== -1;
        })[0];
        if (app_context) {
          groups.push({
            title: "JS Controller",
            js: app_context.file_content,
            js_id: app_context.id,
            js_name: app_context.name
          });
        }
        for (_i = 0, _len = assets.length; _i < _len; _i++) {
          asset = assets[_i];
          if (!(asset.tag === 'js' && asset.metadata.group_name)) {
            continue;
          }
          html_asset = assets.filter(function(x) {
            var _ref;
            return x.tag === 'html' && ((_ref = x.metadata) != null ? _ref.group_name : void 0) === asset.metadata.group_name;
          })[0];
          groups.push({
            title: asset.metadata.group_name.replace(/_/g, ' ').replace(/([A-Z])/g, ' $1'),
            js: asset.file_content,
            js_id: asset.id,
            js_name: asset.name,
            html: html_asset ? html_asset.file_content : null,
            html_id: html_asset ? html_asset.id : null,
            html_name: html_asset ? html_asset.name : null
          });
        }
        return groups;
      };

      Admin_Apps_Ctrl_EditCustomInstance.prototype.saveSettings = function() {
        var asset, group, postData, _, _i, _len, _ref;
        postData = {
          settings: this.$scope.setting_values,
          save_assets: []
        };
        _ref = this.asset_groups;
        for (_ in _ref) {
          if (!__hasProp.call(_ref, _)) continue;
          group = _ref[_];
          for (_i = 0, _len = group.length; _i < _len; _i++) {
            asset = group[_i];
            if (asset.js_id) {
              postData.save_assets.push({
                id: asset.js_id,
                content: asset.js
              });
            }
            if (asset.html_id) {
              postData.save_assets.push({
                id: asset.html_id,
                content: asset.html
              });
            }
          }
        }
        this.startSpinner('saving_settings');
        return this.Api.sendPostJson("/apps/instances/" + this.instanceId, postData).then((function(_this) {
          return function() {
            return _this.stopSpinner('saving_settings').then(function() {
              _this.$scope.$parent.ListCtrl.updateAppTitle(_this.instanceId, _this.$scope.setting_values.dp_app.title);
              return _this.Growl.success(_this.getRegisteredMessage('saved_settings'));
            });
          };
        })(this), function() {
          return this.stopSpinner('saving_settings');
        });
      };


      /*
      		 * SHow delete modal
       */

      Admin_Apps_Ctrl_EditCustomInstance.prototype.startDelete = function() {
        var doDelete;
        doDelete = (function(_this) {
          return function() {
            return _this.Api.sendDelete('/apps/instances/' + _this.app.id).success(function() {
              var _ref, _ref1;
              if (((_ref = _this.$scope.$parent) != null ? _ref.ListCtrl : void 0) != null) {
                if ((_ref1 = _this.$scope.$parent) != null) {
                  _ref1.ListCtrl.removeAppInstance(_this.app.id);
                }
              }
              return _this.$state.go('apps.apps');
            });
          };
        })(this);
        return this.$modal.open({
          templateUrl: this.getTemplatePath('Apps/instance-delete-modal.html'),
          controller: [
            'app', '$scope', '$modalInstance', function(app, $scope, $modalInstance) {
              $scope.app = app;
              $scope.dismiss = function() {
                return $modalInstance.close();
              };
              return $scope.confirm = function() {
                $scope.is_loading = true;
                return doDelete().then(function() {
                  return $modalInstance.close();
                });
              };
            }
          ],
          resolve: {
            app: (function(_this) {
              return function() {
                return _this.app;
              };
            })(this)
          }
        });
      };

      return Admin_Apps_Ctrl_EditCustomInstance;

    })(Admin_Ctrl_Base);
    return Admin_Apps_Ctrl_EditCustomInstance.EXPORT_CTRL();
  });

}).call(this);

//# sourceMappingURL=EditCustomInstance.js.map
