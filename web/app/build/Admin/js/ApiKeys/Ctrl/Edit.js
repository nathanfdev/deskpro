(function() {
  var __hasProp = {}.hasOwnProperty,
    __extends = function(child, parent) { for (var key in parent) { if (__hasProp.call(parent, key)) child[key] = parent[key]; } function ctor() { this.constructor = child; } ctor.prototype = parent.prototype; child.prototype = new ctor(); child.__super__ = parent.prototype; return child; };

  define(['Admin/Main/Ctrl/Base'], function(Admin_Ctrl_Base) {
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
        this.keyData = this.DataService.get('ApiKeys');
        return this.api_key = null;
      };


      /*
       	 *
       */

      Admin_ApiKeys_Ctrl_Edit.prototype.initialLoad = function() {
        var promise;
        promise = this.keyData.loadEditApiKeyData(this.$stateParams.id || null).then((function(_this) {
          return function(data) {
            _this.api_key = data.api_key;
            return _this.form = data.form;
          };
        })(this));
        return promise;
      };


      /*
      		 *
       */

      Admin_ApiKeys_Ctrl_Edit.prototype.saveForm = function() {
        var is_new, promise;
        if (!this.$scope.form_props.$valid) {
          return;
        }
        is_new = !this.api_key.id;
        promise = this.keyData.saveFormModel(this.api_key, this.form);
        this.startSpinner('saving');
        return promise.then((function(_this) {
          return function() {
            _this.stopSpinner('saving', true).then(function() {
              return _this.Growl.success('Saved');
            });
            _this.skipDirtyState();
            if (is_new) {
              return _this.$state.go('apps.api_keys.gocreate');
            }
          };
        })(this), (function(_this) {
          return function() {
            return _this.stopSpinner('saving', true).then(function() {
              return _this.Growl.error('Error');
            });
          };
        })(this));
      };


      /*
       	 *
       */

      Admin_ApiKeys_Ctrl_Edit.prototype.regenerateApiKey = function() {
        return this.keyData.regenerateApiKey(this.api_key, this.form).success((function(_this) {
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
