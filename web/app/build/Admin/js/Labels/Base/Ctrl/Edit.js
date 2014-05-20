(function() {
  var __hasProp = {}.hasOwnProperty,
    __extends = function(child, parent) { for (var key in parent) { if (__hasProp.call(parent, key)) child[key] = parent[key]; } function ctor() { this.constructor = child; } ctor.prototype = parent.prototype; child.prototype = new ctor(); child.__super__ = parent.prototype; return child; };

  define(['Admin/Main/Ctrl/Base'], function(Admin_Ctrl_Base) {
    var Admin_Labels_Base_Ctrl_Edit;
    return Admin_Labels_Base_Ctrl_Edit = (function(_super) {
      __extends(Admin_Labels_Base_Ctrl_Edit, _super);

      function Admin_Labels_Base_Ctrl_Edit() {
        return Admin_Labels_Base_Ctrl_Edit.__super__.constructor.apply(this, arguments);
      }

      Admin_Labels_Base_Ctrl_Edit.CTRL_AS = 'LabelsEdit';

      Admin_Labels_Base_Ctrl_Edit.DEPS = ['em', '$stateParams', '$rootScope', 'LabelManager'];

      Admin_Labels_Base_Ctrl_Edit.prototype.init = function() {
        this.api_endpoint = '';
        this.ng_route = '';
        this.typename = '';
        this.old_label = '';
        this.$scope.form = {
          label: ''
        };
      };

      Admin_Labels_Base_Ctrl_Edit.prototype.initialLoad = function() {
        var get_label;
        if (this.$stateParams.label) {
          get_label = this.Api.sendGet("" + this.api_endpoint + "/get", {
            label: this.$stateParams.label
          }).then((function(_this) {
            return function(result) {
              var rec;
              rec = _this.em.createEntity(_this.typename, 'label', {
                label: result.data.label
              });
              _this.is_new = false;
              _this.old_label = result.data.label;
              _this.label_object = {
                label: _this.old_label
              };
              return _this.$scope.form.label = result.data.label;
            };
          })(this));
          return get_label;
        } else {
          this.is_new = true;
          this.$scope.form.label = '';
          return null;
        }
      };

      Admin_Labels_Base_Ctrl_Edit.prototype.addNewLabel = function() {
        if (!this.$scope.form.label) {
          return false;
        }
        this.startSpinner('saving_label');
        return this.Api.sendPost(this.api_endpoint, {
          label: this.$scope.form.label
        }).success((function(_this) {
          return function() {
            _this.stopSpinner('saving_label', true).then(function() {
              _this.Growl.success(_this.getRegisteredMessage('saved_label'));
              return _this.LabelManager.addLabel(_this.api_endpoint, _this.$scope.form.label);
            });
            _this.skipDirtyState();
            return _this.$state.go("" + _this.ng_route + ".gocreate");
          };
        })(this)).error((function(_this) {
          return function() {
            return _this.Growl.error(_this.getRegisteredMessage('not_created_label'));
          };
        })(this))["finally"]((function(_this) {
          return function() {
            return _this.stopSpinner('saving_label', true);
          };
        })(this));
      };

      Admin_Labels_Base_Ctrl_Edit.prototype.saveLabel = function() {
        if (!this.$scope.form.label) {
          return false;
        }
        if (this.old_label === this.$scope.form.label) {
          return false;
        }
        if (this.is_new) {
          return this.addNewLabel();
        }
        this.startSpinner('saving_label');
        return this.Api.sendPost("" + this.api_endpoint + "/save", {
          label_old: this.old_label,
          label_new: this.$scope.form.label
        }).success((function(_this) {
          return function() {
            _this.stopSpinner('saving_label', true).then(function() {
              _this.Growl.success(_this.getRegisteredMessage('saved_label'));
              _this.LabelManager.renameLabel(_this.api_endpoint, _this.old_label, _this.$scope.form.label);
              _this.old_label = _this.$scope.form.label;
              return _this.label_object = {
                label: _this.old_label
              };
            });
            return _this.skipDirtyState();
          };
        })(this)).error((function(_this) {
          return function() {
            return _this.Growl.error(_this.getRegisteredMessage('not_saved_label'));
          };
        })(this))["finally"]((function(_this) {
          return function() {
            return _this.stopSpinner('saving_label', true);
          };
        })(this));
      };

      Admin_Labels_Base_Ctrl_Edit.prototype.isDirtyState = function() {
        if (this.$scope.form.label !== this.old_label) {
          return true;
        }
        return false;
      };

      return Admin_Labels_Base_Ctrl_Edit;

    })(Admin_Ctrl_Base);
  });

}).call(this);

//# sourceMappingURL=Edit.js.map
