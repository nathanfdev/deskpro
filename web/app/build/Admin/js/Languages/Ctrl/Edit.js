(function() {
  var __hasProp = {}.hasOwnProperty,
    __extends = function(child, parent) { for (var key in parent) { if (__hasProp.call(parent, key)) child[key] = parent[key]; } function ctor() { this.constructor = child; } ctor.prototype = parent.prototype; child.prototype = new ctor(); child.__super__ = parent.prototype; return child; };

  define(['Admin/Main/Ctrl/Base'], function(Admin_Ctrl_Base) {
    var Admin_Languages_Ctrl_Edit;
    Admin_Languages_Ctrl_Edit = (function(_super) {
      __extends(Admin_Languages_Ctrl_Edit, _super);

      function Admin_Languages_Ctrl_Edit() {
        return Admin_Languages_Ctrl_Edit.__super__.constructor.apply(this, arguments);
      }

      Admin_Languages_Ctrl_Edit.CTRL_ID = 'Admin_Languages_Ctrl_Edit';

      Admin_Languages_Ctrl_Edit.CTRL_AS = 'EditCtrl';

      Admin_Languages_Ctrl_Edit.prototype.init = function() {
        var format;
        this.id = this.$stateParams.id;
        format = function(flag) {
          if (!flag || !flag.text) {
            return '';
          }
          return "<img src='" + DP_ASSET_URL + "/images/flags/" + flag.id.toLowerCase() + "' style='margin-right: 2px;' />" + flag.text;
        };
        this.$scope.select2Flag = {
          formatResult: format,
          formatSelection: format,
          escapeMarkup: function(m) {
            return m;
          }
        };
        return this.$scope.isDefaultLang = (function(_this) {
          return function() {
            return _this.lang && _this.lang.id && _this.lang.id === parseInt(_this.$scope.$parent.ListCtrl.default_lang_id);
          };
        })(this);
      };

      Admin_Languages_Ctrl_Edit.prototype.initialLoad = function() {
        var promise;
        promise = this.Api.sendGet("/langs/" + this.id).then((function(_this) {
          return function(result) {
            if (!result.data.language) {
              _this.$state.go('setup.languages.install', {
                id: "install-" + _this.id
              });
              return;
            }
            _this.pack = result.data.pack;
            _this.lang = result.data.language;
            return _this.form = {
              title: _this.lang.title,
              flag_image: _this.lang.flag_image,
              locale: _this.lang.locale
            };
          };
        })(this));
        return promise;
      };

      Admin_Languages_Ctrl_Edit.prototype.startUninstall = function() {
        var inst;
        if (this.lang.id === parseInt(this.$scope.$parent.ListCtrl.default_lang_id)) {
          this.showAlert('@no_delete_default');
          return;
        }
        inst = this.$modal.open({
          templateUrl: this.getTemplatePath('Languages/uninstall-modal.html'),
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
            _this.startSpinner('saving');
            return _this.$scope.$parent.ListCtrl.uninstallLang(_this.id).then(function() {
              return _this.$state.go('setup.languages');
            });
          };
        })(this));
      };

      Admin_Languages_Ctrl_Edit.prototype.doSave = function() {
        if (this.$scope.form_props.$invalid) {
          return;
        }
        this.startSpinner('saving');
        return this.$scope.$parent.ListCtrl.saveLanguage(this.id, {
          title: this.form.title,
          flag_image: this.form.flag_image,
          locale: this.form.locale
        }).then((function(_this) {
          return function() {
            return _this.stopSpinner('saving', true);
          };
        })(this));
      };

      return Admin_Languages_Ctrl_Edit;

    })(Admin_Ctrl_Base);
    return Admin_Languages_Ctrl_Edit.EXPORT_CTRL();
  });

}).call(this);

//# sourceMappingURL=Edit.js.map
