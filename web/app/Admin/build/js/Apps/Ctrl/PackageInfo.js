(function() {
  var __hasProp = {}.hasOwnProperty,
    __extends = function(child, parent) { for (var key in parent) { if (__hasProp.call(parent, key)) child[key] = parent[key]; } function ctor() { this.constructor = child; } ctor.prototype = parent.prototype; child.prototype = new ctor(); child.__super__ = parent.prototype; return child; };

  define(['Admin/Main/Ctrl/Base', 'angular'], function(Admin_Ctrl_Base, angular) {
    var Admin_Apps_Ctrl_PackageInfo;
    Admin_Apps_Ctrl_PackageInfo = (function(_super) {
      __extends(Admin_Apps_Ctrl_PackageInfo, _super);

      function Admin_Apps_Ctrl_PackageInfo() {
        return Admin_Apps_Ctrl_PackageInfo.__super__.constructor.apply(this, arguments);
      }

      Admin_Apps_Ctrl_PackageInfo.CTRL_ID = 'Admin_Apps_Ctrl_PackageInfo';

      Admin_Apps_Ctrl_PackageInfo.CTRL_AS = 'Ctrl';

      Admin_Apps_Ctrl_PackageInfo.DEPS = ['$http'];

      Admin_Apps_Ctrl_PackageInfo.prototype.init = function() {
        var createAceLoaded;
        this.packageName = this.$stateParams.name;
        this.aceEditors = {};
        this.$scope.package_assets = {
          app_js: "",
          readme: ""
        };
        createAceLoaded = (function(_this) {
          return function(name, maxH) {
            if (maxH == null) {
              maxH = 500;
            }
            return function(editor) {
              var updateH;
              _this.aceEditors[name] = editor;
              updateH = function() {
                var newHeight;
                newHeight = editor.getSession().getScreenLength() * editor.renderer.lineHeight + editor.renderer.scrollBar.getWidth();
                if (newHeight > maxH) {
                  newHeight = maxH;
                }
                if (newHeight < 300) {
                  newHeight = 300;
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
        })(this);
        this.$scope.aceLoaded = {
          app_js: createAceLoaded('app_js')
        };
      };

      Admin_Apps_Ctrl_PackageInfo.prototype.initialLoad = function() {
        var promise;
        promise = this.Api.sendDataGet({
          pack: '/apps/packages/' + this.packageName
        }).then((function(_this) {
          return function(result) {
            var getResource;
            _this.pack = result.data.pack['package'];
            getResource = function(id, tag) {
              var asset;
              asset = _this.pack.assets.filter(function(x) {
                return x.tag === tag;
              })[0];
              if (asset) {
                return _this.$http.get(asset.blob.relative_url, {
                  responseType: "text"
                }).success(function(data) {
                  return _this.$scope.package_assets[id] = data;
                });
              }
            };
            return getResource('app_js', 'app_js');
          };
        })(this));
        return promise;
      };


      /*
        	 * Shows the install overlay
       */

      Admin_Apps_Ctrl_PackageInfo.prototype.startInstall = function() {
        var doInstall, inst, listCtrl, _ref;
        if (((_ref = this.$scope.$parent.ListCtrl) != null ? _ref.addAppInstance : void 0) != null) {
          listCtrl = this.$scope.$parent.ListCtrl;
        }
        doInstall = (function(_this) {
          return function(setting_values) {
            return _this.Api.sendPut("/apps/packages/" + _this.packageName, {
              settings: setting_values
            }).success(function(info) {
              var instanceInfo;
              if (listCtrl) {
                instanceInfo = {
                  id: info.id,
                  title: setting_values.dp_app.title,
                  package_name: _this.pack.name,
                  "package": _this.pack
                };
                listCtrl.addAppInstance(instanceInfo);
              }
              return _this.$state.go('apps.apps.instance', {
                id: info.id
              });
            });
          };
        })(this);
        return inst = this.$modal.open({
          templateUrl: this.getTemplatePath('Apps/package-install-modal.html'),
          controller: [
            '$scope', '$modalInstance', 'pack', function($scope, $modalInstance, pack) {
              var setting, _i, _len, _ref1, _results;
              $scope.dismiss = function() {
                return $modalInstance.dismiss();
              };
              $scope.doInstall = function() {
                $scope.is_loading = true;
                return doInstall($scope.setting_values).then(function() {
                  return $modalInstance.dismiss();
                }, function() {
                  return $scope.is_loading = false;
                });
              };
              $scope.pack = pack;
              $scope.setting_values = {
                dp_app: {
                  title: pack.title
                }
              };
              _ref1 = pack.settings_def;
              _results = [];
              for (_i = 0, _len = _ref1.length; _i < _len; _i++) {
                setting = _ref1[_i];
                if (setting.default_value) {
                  _results.push($scope.setting_values[setting.name] = setting.default_value);
                } else {
                  _results.push(void 0);
                }
              }
              return _results;
            }
          ],
          resolve: {
            pack: (function(_this) {
              return function() {
                return _this.pack;
              };
            })(this)
          }
        });
      };

      return Admin_Apps_Ctrl_PackageInfo;

    })(Admin_Ctrl_Base);
    return Admin_Apps_Ctrl_PackageInfo.EXPORT_CTRL();
  });

}).call(this);

//# sourceMappingURL=PackageInfo.js.map
