(function() {
  var __hasProp = {}.hasOwnProperty,
    __extends = function(child, parent) { for (var key in parent) { if (__hasProp.call(parent, key)) child[key] = parent[key]; } function ctor() { this.constructor = child; } ctor.prototype = parent.prototype; child.prototype = new ctor(); child.__super__ = parent.prototype; return child; };

  define(['Admin/Main/Ctrl/Base', 'Admin/App'], function(Admin_Ctrl_Base) {
    var Admin_TicketLabels_Ctrl_List, _ref;
    Admin_TicketLabels_Ctrl_List = (function(_super) {
      __extends(Admin_TicketLabels_Ctrl_List, _super);

      function Admin_TicketLabels_Ctrl_List() {
        _ref = Admin_TicketLabels_Ctrl_List.__super__.constructor.apply(this, arguments);
        return _ref;
      }

      Admin_TicketLabels_Ctrl_List.CTRL_ID = 'Admin_TicketLabels_Ctrl_List';

      Admin_TicketLabels_Ctrl_List.CTRL_AS = 'TicketLabelsList';

      Admin_TicketLabels_Ctrl_List.DEPS = ['$scope', 'TicketLabelsData'];

      Admin_TicketLabels_Ctrl_List.CTRL_TYPE = 'list';

      Admin_TicketLabels_Ctrl_List.prototype.init = function() {
        this.labels = [];
        this.new_label = '';
        this.add_mode = false;
        this.$scope.escape_url = function(text) {
          return encodeURIComponent(text);
        };
        console.log(this.$scope);
      };

      Admin_TicketLabels_Ctrl_List.prototype.initialLoad = function() {
        var list_promise,
          _this = this;
        list_promise = this.TicketLabelsData.loadList().then(function(recs) {
          return _this.labels = recs;
        });
        return this.$q.all([list_promise]);
      };

      Admin_TicketLabels_Ctrl_List.prototype.startDelete = function(label) {
        var inst,
          _this = this;
        label.delete_mode = true;
        inst = this.$modal.open({
          templateUrl: this.getTemplatePath('TicketLabels/delete-modal.html'),
          controller: [
            '$scope', '$modalInstance', function($scope, $modalInstance) {
              $scope.confirm = function() {
                return $modalInstance.close();
              };
              return $scope.dismiss = function() {
                $modalInstance.dismiss();
                return label.delete_mode = false;
              };
            }
          ]
        });
        inst.result.then(function() {
          return _this.deleteStatus(label);
        });
        return inst.result["catch"](function() {
          return label.delete_mode = false;
        });
      };

      Admin_TicketLabels_Ctrl_List.prototype.deleteLabel = function(label) {
        var _this = this;
        return this.Api.sendDelete('/ticket_labels/' + label.label).success(function() {
          _this.TicketLabelsData.remove(label);
          if (_this.$state.current.name === 'tickets.labels.edit' && _this.$state.params.label === label.label) {
            return _this.$state.go('tickets.labels');
          }
        })["finally"](function() {
          return label.delete_mode = false;
        });
      };

      Admin_TicketLabels_Ctrl_List.prototype.switchSortOrder = function(to) {
        var from;
        from = this.$scope.order;
        if (from === to) {
          this.$scope.orderReverse = !this.$scope.orderReverse;
        } else {
          this.$scope.orderReverse = !(to === 'label');
        }
        return this.$scope.order = to;
      };

      return Admin_TicketLabels_Ctrl_List;

    })(Admin_Ctrl_Base);
    return Admin_TicketLabels_Ctrl_List.EXPORT_CTRL();
  });

}).call(this);

/*
//@ sourceMappingURL=List.js.map
*/