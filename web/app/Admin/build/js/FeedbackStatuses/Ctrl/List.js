(function() {
  var __hasProp = {}.hasOwnProperty,
    __extends = function(child, parent) { for (var key in parent) { if (__hasProp.call(parent, key)) child[key] = parent[key]; } function ctor() { this.constructor = child; } ctor.prototype = parent.prototype; child.prototype = new ctor(); child.__super__ = parent.prototype; return child; };

  define(['Admin/Main/Ctrl/Base', 'Admin/App'], function(Admin_Ctrl_Base) {
    var Admin_FeedbackStatuses_Ctrl_List, _ref;
    Admin_FeedbackStatuses_Ctrl_List = (function(_super) {
      __extends(Admin_FeedbackStatuses_Ctrl_List, _super);

      function Admin_FeedbackStatuses_Ctrl_List() {
        _ref = Admin_FeedbackStatuses_Ctrl_List.__super__.constructor.apply(this, arguments);
        return _ref;
      }

      Admin_FeedbackStatuses_Ctrl_List.CTRL_ID = 'Admin_FeedbackStatuses_Ctrl_List';

      Admin_FeedbackStatuses_Ctrl_List.CTRL_AS = 'FeedbackStatusesList';

      Admin_FeedbackStatuses_Ctrl_List.DEPS = ['$rootScope', '$scope', 'FeedbackStatusesData', 'em', 'Api', '$state', 'Growl'];

      Admin_FeedbackStatuses_Ctrl_List.CTRL_TYPE = 'list';

      Admin_FeedbackStatuses_Ctrl_List.prototype.init = function() {
        var _this = this;
        this.$scope.activeType = 'active';
        this.$scope.closedType = 'closed';
        this.feedback_active_statuses = [];
        this.feedback_closed_statuses = [];
        return this.sortedListOptions = {
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
            promise = _this.Api.sendPostJson('/feedback_statuses/display_order', postData);
            return _this.pingElement('display_orders');
          }
        };
      };

      Admin_FeedbackStatuses_Ctrl_List.prototype.initialLoad = function() {
        var list_promise,
          _this = this;
        list_promise = this.FeedbackStatusesData.loadList().then(function(recs) {
          _this.feedback_active_statuses = recs.active_statuses.values();
          _this.feedback_closed_statuses = recs.closed_statuses.values();
          _this.addManagedListener(_this.FeedbackStatusesData.recs.active_statuses, 'changed', function() {
            _this.feedback_active_statuses = _this.FeedbackStatusesData.recs.active_statuses.values();
            return _this.ngApply();
          });
          return _this.addManagedListener(_this.FeedbackStatusesData.recs.closed_statuses, 'changed', function() {
            _this.feedback_closed_statuses = _this.FeedbackStatusesData.recs.closed_statuses.values();
            return _this.ngApply();
          });
        });
        return this.$q.all([list_promise]);
      };

      /*
      # Show the delete dlg
      */


      Admin_FeedbackStatuses_Ctrl_List.prototype.startDelete = function(feedback_status) {
        var inst,
          _this = this;
        inst = this.$modal.open({
          templateUrl: this.getTemplatePath('FeedbackStatuses/delete-modal.html'),
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
        return inst.result.then(function() {
          return _this.deleteFeedbackStatus(feedback_status);
        });
      };

      /*
      		# Actually do the delete
      */


      Admin_FeedbackStatuses_Ctrl_List.prototype.deleteFeedbackStatus = function(feedback_status) {
        var _this = this;
        return this.Api.sendDelete('/feedback_statuses/' + feedback_status.id).success(function() {
          _this.FeedbackStatusesData.remove(feedback_status.id);
          _this.ngApply();
          if (_this.$state.current.name === 'portal.feedback_statuses.edit' && parseInt(_this.$state.params.id) === feedback_status.id) {
            return _this.$state.go('portal.feedback_statuses');
          }
        });
      };

      return Admin_FeedbackStatuses_Ctrl_List;

    })(Admin_Ctrl_Base);
    return Admin_FeedbackStatuses_Ctrl_List.EXPORT_CTRL();
  });

}).call(this);

/*
//@ sourceMappingURL=List.js.map
*/