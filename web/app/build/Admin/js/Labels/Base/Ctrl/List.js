(function() {
  var __hasProp = {}.hasOwnProperty,
    __extends = function(child, parent) { for (var key in parent) { if (__hasProp.call(parent, key)) child[key] = parent[key]; } function ctor() { this.constructor = child; } ctor.prototype = parent.prototype; child.prototype = new ctor(); child.__super__ = parent.prototype; return child; };

  define(['Admin/Main/Ctrl/Base'], function(Admin_Ctrl_Base) {
    var Admin_Labels_Base_Ctrl_List;
    return Admin_Labels_Base_Ctrl_List = (function(_super) {
      __extends(Admin_Labels_Base_Ctrl_List, _super);

      function Admin_Labels_Base_Ctrl_List() {
        return Admin_Labels_Base_Ctrl_List.__super__.constructor.apply(this, arguments);
      }

      Admin_Labels_Base_Ctrl_List.DEPS = ['em', '$rootScope', 'LabelManager'];

      Admin_Labels_Base_Ctrl_List.CTRL_AS = 'LabelsList';

      Admin_Labels_Base_Ctrl_List.prototype.init = function() {
        this.api_endpoint = '';
        this.ng_route = '';
        this.typename = '';
        this.labels = [];
        this.new_label = '';
        this.add_mode = false;
        this.$scope.order = 'label';
        this.$scope.orderReverse = false;
        this.$rootScope.$on("" + this.api_endpoint + "_new", (function(_this) {
          return function(rec) {
            if (_this.labels.indexOf(rec) === -1) {
              return _this.labels.push(rec);
            }
          };
        })(this));
        return this.$scope.$watch('sortOrder', (function(_this) {
          return function() {
            if (!_this.$scope.sortOrder) {
              return;
            }
            _this.$scope.order = _this.$scope.sortOrder.field;
            return _this.$scope.orderReverse = _this.$scope.sortOrder.dir === 'DESC';
          };
        })(this));
      };

      Admin_Labels_Base_Ctrl_List.prototype.initialLoad = function() {
        var promise;
        promise = this.LabelManager.loadLabels(this.api_endpoint).then((function(_this) {
          return function(labels) {
            return _this.labels = labels;
          };
        })(this));
        return promise;
      };

      Admin_Labels_Base_Ctrl_List.prototype.startDelete = function(label) {
        var inst;
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
        inst.result.then((function(_this) {
          return function() {
            return _this.deleteLabel(label);
          };
        })(this));
        return inst.result["catch"]((function(_this) {
          return function() {};
        })(this));
      };

      Admin_Labels_Base_Ctrl_List.prototype.deleteLabel = function(label) {
        this.LabelManager.removeLabel(this.api_endpoint, label);
        return this.Api.sendDelete(this.api_endpoint, {
          label: label
        }).success((function(_this) {
          return function() {
            if (_this.$state.current.name === ("" + _this.ng_route + ".edit") && _this.$state.params.label === label) {
              return _this.$state.go(_this.ng_route);
            }
          };
        })(this))["finally"]((function(_this) {
          return function() {};
        })(this));
      };

      return Admin_Labels_Base_Ctrl_List;

    })(Admin_Ctrl_Base);
  });

}).call(this);

//# sourceMappingURL=List.js.map
