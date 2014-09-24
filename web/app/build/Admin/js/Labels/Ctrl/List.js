(function() {
  var __hasProp = {}.hasOwnProperty,
    __extends = function(child, parent) { for (var key in parent) { if (__hasProp.call(parent, key)) child[key] = parent[key]; } function ctor() { this.constructor = child; } ctor.prototype = parent.prototype; child.prototype = new ctor(); child.__super__ = parent.prototype; return child; };

  define(['Admin/Main/Ctrl/Base'], function(Admin_Ctrl_Base) {
    var Admin_Labels_Ctrl_List;
    Admin_Labels_Ctrl_List = (function(_super) {
      __extends(Admin_Labels_Ctrl_List, _super);

      function Admin_Labels_Ctrl_List() {
        return Admin_Labels_Ctrl_List.__super__.constructor.apply(this, arguments);
      }

      Admin_Labels_Ctrl_List.CTRL_ID = 'Admin_Labels_Ctrl_List';

      Admin_Labels_Ctrl_List.DEPS = ['em', '$rootScope', 'LabelDefinition'];

      Admin_Labels_Ctrl_List.CTRL_AS = 'LabelsList';

      Admin_Labels_Ctrl_List.prototype.init = function() {
        this.type = this.$state.current.data.type;
        this.$scope.order = 'label';
        this.$scope.orderReverse = false;
        this.$scope.labels = {};
        this.$scope.countDefinitions = (function(_this) {
          return function() {
            var count, n;
            count = 0;
            for (n in _this.$scope.labels) {
              count++;
            }
            return count;
          };
        })(this);
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

      Admin_Labels_Ctrl_List.prototype.type = function() {
        throw new Exception('This method must be implemented by a sub-class');
      };

      Admin_Labels_Ctrl_List.prototype.initialLoad = function() {
        return this.LabelDefinition.all(this.type).then((function(_this) {
          return function(definitions) {
            return _this.$scope.labels = definitions;
          };
        })(this));
      };

      return Admin_Labels_Ctrl_List;

    })(Admin_Ctrl_Base);
    return Admin_Labels_Ctrl_List.EXPORT_CTRL();
  });

}).call(this);

//# sourceMappingURL=List.js.map
