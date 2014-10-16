(function() {
  var __hasProp = {}.hasOwnProperty,
    __extends = function(child, parent) { for (var key in parent) { if (__hasProp.call(parent, key)) child[key] = parent[key]; } function ctor() { this.constructor = child; } ctor.prototype = parent.prototype; child.prototype = new ctor(); child.__super__ = parent.prototype; return child; };

  define(['Admin/Main/Ctrl/Base', 'angular'], function(Admin_Ctrl_Base, angular) {
    var Admin_Labels_Ctrl_Edit;
    Admin_Labels_Ctrl_Edit = (function(_super) {
      __extends(Admin_Labels_Ctrl_Edit, _super);

      function Admin_Labels_Ctrl_Edit() {
        return Admin_Labels_Ctrl_Edit.__super__.constructor.apply(this, arguments);
      }

      Admin_Labels_Ctrl_Edit.CTRL_ID = 'Admin_Labels_Ctrl_Edit';

      Admin_Labels_Ctrl_Edit.CTRL_AS = 'LabelsEdit';

      Admin_Labels_Ctrl_Edit.DEPS = ['em', '$stateParams', '$rootScope', 'LabelDefinition'];

      Admin_Labels_Ctrl_Edit.prototype.init = function() {
        this.type = this.$state.current.data.type;
        this.endpoint = '/labels/definitions';
        if (!this.$stateParams.label) {
          this.$scope.isNew = true;
        }
        this.definition = null;
        this.$scope.picker = false;
        this.$scope.colors = ['#e11d21', '#eb6420', '#fbca04', '#009800', '#006b75', '#207de5', '#0052cc', '#5319e7', '#f7c6c7', '#fad8c7', '#fef2c0', '#bfe5bf', '#bfdadc', '#c7def8', '#bfd4f2', '#d4c5f9'];
        this.$scope.form = {
          label: '',
          color: "",
          label_type: this.type
        };
        return this.$scope.startDelete = (function(_this) {
          return function() {
            return _this.startDelete();
          };
        })(this);
      };

      Admin_Labels_Ctrl_Edit.prototype.state = function(to) {
        if (to) {
          to = '.' + to;
        }
        return this.$state.current.name.replace(/(.+)\.edit|\.create$/, '$1' + to);
      };

      Admin_Labels_Ctrl_Edit.prototype.initialLoad = function() {
        if (this.$stateParams.label) {
          return this.LabelDefinition.get(this.type, this.$stateParams.label).then((function(_this) {
            return function(def) {
              console.log(def);
              if (!def) {
                return;
              }
              _this.definition = def;
              return _this.$scope.form = angular.copy(def);
            };
          })(this));
        }
      };

      Admin_Labels_Ctrl_Edit.prototype.color2hex = function(color) {
        var hex, rgb;
        color = color.replace(/\s/g, '');
        if (/^#?[0-9A-F]{3}$/i.test(color) || /^#?[0-9A-F]{6}$/i.test(color)) {
          if (!color.match(/^#/)) {
            color = '#' + color;
          }
          return color;
        }
        rgb = color.match(/^rgb\((\d+),\s*(\d+),\s*(\d+)\)$/);
        if (!rgb) {
          return '#ffffff';
        }
        hex = function(x) {
          return ("0" + parseInt(x).toString(16)).slice(-2);
        };
        return "#" + hex(rgb[1]) + hex(rgb[2]) + hex(rgb[3]);
      };

      Admin_Labels_Ctrl_Edit.prototype.saveLabel = function() {
        var color, dummy, method, sendData;
        if (!this.$scope.form.label) {
          return false;
        }
        if (this.definition && this.definition.label === this.$scope.form.label && this.definition.color === this.$scope.form.color) {
          return false;
        }
        dummy = $('<div></div>').css('color', this.color2hex(this.$scope.form.color));
        color = this.color2hex(dummy.css('color'));
        this.$scope.form.color = color;
        this.startSpinner('saving_label');
        if (this.definition) {
          sendData = {
            old: this.definition || {},
            "new": this.$scope.form
          };
          method = 'sendPutJson';
        } else {
          sendData = this.$scope.form;
          method = 'sendPostJson';
        }
        return this.Api[method](this.endpoint, sendData).success((function(_this) {
          return function(data) {
            _this.stopSpinner('saving_label', true).then(function() {
              return _this.Growl.success(_this.getRegisteredMessage('saved_label'));
            });
            _this.LabelDefinition.update(_this.definition, data);
            _this.definition = data;
            if (_this.$scope.isNew) {
              return _this.$state.go(_this.state('gocreate'));
            } else {
              return _this.$state.go(_this.state('edit'), {
                label: data.label
              });
            }
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

      Admin_Labels_Ctrl_Edit.prototype.startDelete = function() {
        var inst;
        if (!this.definition) {
          return;
        }
        inst = this.$modal.open({
          templateUrl: this.getTemplatePath('Labels/delete-modal.html'),
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
            return _this.Api.sendDelete(_this.endpoint, _this.definition).success(function() {
              _this.LabelDefinition.remove(_this.definition);
              return _this.$state.go(_this.state(''));
            }).error(function() {
              return _this.$state.go(_this.state(''));
            })["finally"](function() {
              return _this.$state.go(_this.state(''));
            });
          };
        })(this));
      };

      return Admin_Labels_Ctrl_Edit;

    })(Admin_Ctrl_Base);
    return Admin_Labels_Ctrl_Edit.EXPORT_CTRL();
  });

}).call(this);

//# sourceMappingURL=Edit.js.map
