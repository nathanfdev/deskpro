(function() {
  var __hasProp = {}.hasOwnProperty,
    __extends = function(child, parent) { for (var key in parent) { if (__hasProp.call(parent, key)) child[key] = parent[key]; } function ctor() { this.constructor = child; } ctor.prototype = parent.prototype; child.prototype = new ctor(); child.__super__ = parent.prototype; return child; };

  define(['Admin/Main/Ctrl/Base'], function(Admin_Ctrl_Base) {
    var Admin_FeedbackStatuses_Ctrl_List;
    Admin_FeedbackStatuses_Ctrl_List = (function(_super) {
      __extends(Admin_FeedbackStatuses_Ctrl_List, _super);

      function Admin_FeedbackStatuses_Ctrl_List() {
        return Admin_FeedbackStatuses_Ctrl_List.__super__.constructor.apply(this, arguments);
      }

      Admin_FeedbackStatuses_Ctrl_List.CTRL_ID = 'Admin_FeedbackStatuses_Ctrl_List';

      Admin_FeedbackStatuses_Ctrl_List.CTRL_AS = 'FeedbackStatusesList';

      Admin_FeedbackStatuses_Ctrl_List.DEPS = ['$rootScope', '$scope', 'FeedbackStatusesData', 'em', 'Api', '$state', 'Growl'];

      Admin_FeedbackStatuses_Ctrl_List.prototype.init = function() {
        this.$scope.activeType = 'active';
        this.$scope.closedType = 'closed';
        this.feedback_active_statuses = [];
        this.feedback_closed_statuses = [];
        return this.sortedListOptions = {
          axis: 'y',
          handle: '.drag-handle',
          update: (function(_this) {
            return function(ev, data) {
              var $list, em, postData, promise, status_type, x;
              $list = data.item.closest('ul');
              postData = {
                display_orders: []
              };
              x = 0;
              em = _this.em;
              status_type = 'active';
              $list.find('li').each(function() {
                var feedback_status, feedback_status_id;
                x += 10;
                feedback_status_id = parseInt($(this).data('id'));
                if (feedback_status_id) {
                  feedback_status = em.getById('feedback_status', feedback_status_id);
                  if (feedback_status) {
                    feedback_status.display_order = x;
                    status_type = feedback_status.status_type;
                  }
                }
                return postData.display_orders.push(feedback_status_id);
              });
              promise = _this.Api.sendPostJson('/feedback_statuses/display_order', postData);
              return _this.pingElement('display_orders_' + status_type);
            };
          })(this)
        };
      };

      Admin_FeedbackStatuses_Ctrl_List.prototype.initialLoad = function() {
        var list_promise;
        list_promise = this.FeedbackStatusesData.loadList().then((function(_this) {
          return function(recs) {
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
          };
        })(this));
        return this.$q.all([list_promise]);
      };


      /*
       * Show the delete dlg
       */

      Admin_FeedbackStatuses_Ctrl_List.prototype.startDelete = function(feedback_status) {
        var inst, move_feedback_statuses_list;
        move_feedback_statuses_list = this.FeedbackStatusesData.getListOfMovables(feedback_status);
        if (!move_feedback_statuses_list.length) {
          this.showAlert('@no_delete_last');
          return;
        }
        inst = this.$modal.open({
          templateUrl: this.getTemplatePath('FeedbackStatuses/delete-modal.html'),
          controller: [
            '$scope', '$modalInstance', 'move_feedback_statuses_list', function($scope, $modalInstance, move_feedback_statuses_list) {
              $scope.move_feedback_statuses_list = move_feedback_statuses_list;
              $scope.selected = {
                move_to_id: move_feedback_statuses_list[0].id
              };
              $scope.confirm = function() {
                return $modalInstance.close($scope.selected.move_to_id);
              };
              return $scope.dismiss = function() {
                return $modalInstance.dismiss();
              };
            }
          ],
          resolve: {
            move_feedback_statuses_list: (function(_this) {
              return function() {
                return move_feedback_statuses_list;
              };
            })(this)
          }
        });
        return inst.result.then((function(_this) {
          return function(move_to) {
            return _this.deleteFeedbackStatus(feedback_status, move_to);
          };
        })(this));
      };


      /*
      		 * Actually do the delete
       	 * @param feedback_status - feedback status we want to delete
       	 * @param move_to - to what status feedback should be moved
       */

      Admin_FeedbackStatuses_Ctrl_List.prototype.deleteFeedbackStatus = function(feedback_status, move_to) {
        return this.Api.sendDelete('/feedback_statuses/' + feedback_status.id, {
          move_to: move_to
        }).success((function(_this) {
          return function() {
            _this.FeedbackStatusesData.remove(feedback_status.id);
            _this.ngApply();
            if (_this.$state.current.name === 'portal.feedback_statuses.edit' && parseInt(_this.$state.params.id) === feedback_status.id) {
              return _this.$state.go('portal.feedback_statuses');
            }
          };
        })(this));
      };

      return Admin_FeedbackStatuses_Ctrl_List;

    })(Admin_Ctrl_Base);
    return Admin_FeedbackStatuses_Ctrl_List.EXPORT_CTRL();
  });

}).call(this);

//# sourceMappingURL=List.js.map
