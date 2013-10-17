(function() {
  var __hasProp = {}.hasOwnProperty,
    __extends = function(child, parent) { for (var key in parent) { if (__hasProp.call(parent, key)) child[key] = parent[key]; } function ctor() { this.constructor = child; } ctor.prototype = parent.prototype; child.prototype = new ctor(); child.__super__ = parent.prototype; return child; };

  define(['Admin/Main/Ctrl/Base', 'Admin/App'], function(Admin_Ctrl_Base) {
    var Admin_TicketFeedbackStatuses_Ctrl_List, _ref;
    Admin_TicketFeedbackStatuses_Ctrl_List = (function(_super) {
      __extends(Admin_TicketFeedbackStatuses_Ctrl_List, _super);

      function Admin_TicketFeedbackStatuses_Ctrl_List() {
        _ref = Admin_TicketFeedbackStatuses_Ctrl_List.__super__.constructor.apply(this, arguments);
        return _ref;
      }

      Admin_TicketFeedbackStatuses_Ctrl_List.CTRL_ID = 'Admin_TicketFeedbackStatuses_Ctrl_List';

      Admin_TicketFeedbackStatuses_Ctrl_List.CTRL_AS = 'TicketFeedbackStatusesList';

      Admin_TicketFeedbackStatuses_Ctrl_List.DEPS = ['$scope', 'TicketFeedbackStatusesData'];

      Admin_TicketFeedbackStatuses_Ctrl_List.CTRL_TYPE = 'list';

      Admin_TicketFeedbackStatuses_Ctrl_List.prototype.init = function() {
        var _this = this;
        this.statuses = [];
        this.active_statuses_length = 0;
        this.closed_statuses_length = 0;
        this.add_mode = false;
        this.$scope.escape_url = function(text) {
          return encodeURIComponent(text);
        };
        this.$scope.$watchCollection('statuses', function(newValue) {
          if (!angular.isArray(newValue)) {
            return;
          }
          this.active_statuses_length = _.where(newValue, {
            is_active: true
          }).length;
          this.closed_statuses_length = _.where(newValue, {
            is_active: false
          }).length;
        }, true);
        this.sortedListOptions = {
          axis: 'y',
          handle: '.drag-handle',
          update: function(ev, data) {
            var $list, postData, promise;
            $list = data.item.closest('ul');
            postData = {
              display_orders: []
            };
            $list.find('li').each(function() {
              return postData.display_orders.push($(this).data('id'));
            });
            promise = _this.Api.sendPostJson('/feedback/statuses/order', postData);
            return _this.pingElement('display_orders');
          }
        };
      };

      Admin_TicketFeedbackStatuses_Ctrl_List.prototype.initialLoad = function() {
        var list_promise,
          _this = this;
        list_promise = this.TicketFeedbackStatusesData.loadList().then(function(recs) {
          _this.statuses = recs.values();
          return _this.addManagedListener(_this.TicketFeedbackStatusesData.recs, 'changed', function() {
            _this.statuses = _this.TicketFeedbackStatusesData.recs.values();
            return _this.ngApply();
          });
        });
        return this.$q.all([list_promise]);
      };

      Admin_TicketFeedbackStatuses_Ctrl_List.prototype.startDelete = function(status) {
        var inst,
          _this = this;
        status.delete_mode = true;
        inst = this.$modal.open({
          templateUrl: this.getTemplatePath('TicketFeedbackStatuses/delete-modal.html'),
          controller: [
            '$scope', '$modalInstance', function($scope, $modalInstance) {
              $scope.confirm = function() {
                return $modalInstance.close();
              };
              return $scope.dismiss = function() {
                $modalInstance.dismiss();
                return status.delete_mode = false;
              };
            }
          ]
        });
        inst.result.then(function() {
          return _this.deleteStatus(status);
        });
        return inst.result["catch"](function() {
          return status.delete_mode = false;
        });
      };

      Admin_TicketFeedbackStatuses_Ctrl_List.prototype.deleteStatus = function(status) {
        var _this = this;
        return this.Api.sendDelete('/feedback/statuses/' + status.id).success(function() {
          _this.TicketFeedbackStatusesData.remove(status);
          if (_this.$state.current.name === 'tickets.feedback.statuses.edit' && _this.$state.params.id === status.id) {
            return _this.$state.go('tickets.feedback.statuses');
          }
        })["finally"](function() {
          return status.delete_mode = false;
        });
      };

      Admin_TicketFeedbackStatuses_Ctrl_List.prototype.updateEnabledState = function(status) {
        var _this = this;
        return this.Api.sendPost('/feedback/statuses/switch/' + status.id).success(function() {
          status.is_enabled = !status.is_enabled;
          return _this.pingElement('statuses_enabled');
        });
      };

      return Admin_TicketFeedbackStatuses_Ctrl_List;

    })(Admin_Ctrl_Base);
    return Admin_TicketFeedbackStatuses_Ctrl_List.EXPORT_CTRL();
  });

}).call(this);

/*
//@ sourceMappingURL=List.js.map
*/