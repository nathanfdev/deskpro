(function() {
  var __hasProp = {}.hasOwnProperty,
    __extends = function(child, parent) { for (var key in parent) { if (__hasProp.call(parent, key)) child[key] = parent[key]; } function ctor() { this.constructor = child; } ctor.prototype = parent.prototype; child.prototype = new ctor(); child.__super__ = parent.prototype; return child; };

  define(['Admin/Main/Ctrl/Base', 'angular'], function(Admin_Ctrl_Base, angular) {
    var Admin_CustomFields_Ctrl_Edit;
    Admin_CustomFields_Ctrl_Edit = (function(_super) {
      __extends(Admin_CustomFields_Ctrl_Edit, _super);

      function Admin_CustomFields_Ctrl_Edit() {
        return Admin_CustomFields_Ctrl_Edit.__super__.constructor.apply(this, arguments);
      }

      Admin_CustomFields_Ctrl_Edit.CTRL_ID = 'Admin_CustomFields_Ctrl_Edit';

      Admin_CustomFields_Ctrl_Edit.CTRL_AS = 'EditCtrl';

      Admin_CustomFields_Ctrl_Edit.DEPS = [];

      Admin_CustomFields_Ctrl_Edit.prototype.init = function() {
        var data;
        data = this.$state.current.data;
        this.service = this.DataService.get('CustomFields', data.owner, data.context);
        this.$scope.definition = {
          form_type: 'contextual_choice',
          context_class: data.context
        };
        this.options = {
          expanded: 1,
          multiple: 2
        };
        return this.$scope.options = {
          choices: 0
        };
      };

      Admin_CustomFields_Ctrl_Edit.prototype.initialLoad = function() {
        if (!this.$stateParams.id) {
          return;
        }
        return this.service.get(parseInt(this.$stateParams.id)).then((function(_this) {
          return function(model) {
            var expanded, multiple;
            if (model == null) {
              return;
            }
            if ('[object Array]' === Object.prototype.toString.call(model.options)) {
              model.options = {};
            }
            _this.$scope.definition = angular.copy(model);
            multiple = _this.$scope.definition.options.multiple ? _this.options.multiple : 0;
            expanded = _this.$scope.definition.options.expanded ? _this.options.expanded : 0;
            return _this.$scope.options.choices = _this.$scope.options.choices | multiple | expanded;
          };
        })(this));
      };

      Admin_CustomFields_Ctrl_Edit.prototype.saveForm = function() {
        var is_new, promise;
        is_new = !this.$scope.definition.id;
        this.$scope.definition.options.multiple = this.options.multiple === (this.$scope.options.choices & this.options.multiple);
        this.$scope.definition.options.expanded = this.options.expanded === (this.$scope.options.choices & this.options.expanded);
        promise = this.service.set(this.$scope.definition);
        this.startSpinner('saving');
        return promise.then((function(_this) {
          return function() {
            _this.stopSpinner('saving', true).then(function() {
              return _this.Growl.success('Saved');
            });
            _this.skipDirtyState();
            if (is_new) {
              return _this.$state.go(_this.$state.current.name.replace(/\.(edit|create)$/, '.gocreate'));
            }
          };
        })(this), (function(_this) {
          return function(info, code) {
            _this.stopSpinner('saving', true);
            return _this.applyErrorResponseToView(info);
          };
        })(this));
      };

      Admin_CustomFields_Ctrl_Edit.prototype.startDelete = function() {
        var doDelete;
        doDelete = (function(_this) {
          return function() {
            return _this.service.remove(_this.$scope.definition);
          };
        })(this);
        return this.$modal.open({
          templateUrl: this.getTemplatePath('CustomField/delete-modal.html'),
          controller: [
            '$scope', '$modalInstance', '$state', function($scope, $modalInstance, $state) {
              $scope.confirm = function() {
                return $scope.is_loading = doDelete().then(function() {
                  var baseParts;
                  baseParts = $state.current.name.split('.');
                  $state.go(baseParts[0] + '.' + baseParts[1]);
                  return $modalInstance.dismiss();
                });
              };
              return $scope.dismiss = function() {
                return $modalInstance.dismiss();
              };
            }
          ]
        });
      };

      return Admin_CustomFields_Ctrl_Edit;

    })(Admin_Ctrl_Base);
    return Admin_CustomFields_Ctrl_Edit.EXPORT_CTRL();
  });

}).call(this);

//# sourceMappingURL=Edit.js.map
