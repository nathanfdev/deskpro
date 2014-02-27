(function() {
  var __hasProp = {}.hasOwnProperty,
    __extends = function(child, parent) { for (var key in parent) { if (__hasProp.call(parent, key)) child[key] = parent[key]; } function ctor() { this.constructor = child; } ctor.prototype = parent.prototype; child.prototype = new ctor(); child.__super__ = parent.prototype; return child; };

  define(['Admin/Main/Ctrl/Base'], function(Admin_Ctrl_Base) {
    var Admin_FeedbackTypes_Ctrl_List;
    Admin_FeedbackTypes_Ctrl_List = (function(_super) {
      __extends(Admin_FeedbackTypes_Ctrl_List, _super);

      function Admin_FeedbackTypes_Ctrl_List() {
        return Admin_FeedbackTypes_Ctrl_List.__super__.constructor.apply(this, arguments);
      }

      Admin_FeedbackTypes_Ctrl_List.CTRL_ID = 'Admin_FeedbackTypes_Ctrl_List';

      Admin_FeedbackTypes_Ctrl_List.CTRL_AS = 'FeedbackTypesList';

      Admin_FeedbackTypes_Ctrl_List.DEPS = ['$rootScope', '$scope', 'FeedbackTypesData', 'em', 'Api', '$state', 'Growl'];

      Admin_FeedbackTypes_Ctrl_List.prototype.init = function() {
        this.feedback_types = [];
        return this.sortedListOptions = {
          axis: 'y',
          handle: '.drag-handle',
          update: (function(_this) {
            return function(ev, data) {
              var $list, em, postData, promise, x;
              $list = data.item.closest('ul');
              postData = {
                display_orders: []
              };
              x = 0;
              em = _this.em;
              $list.find('li').each(function() {
                var feedback_type, feedback_type_id;
                x += 10;
                feedback_type_id = parseInt($(this).data('id'));
                if (feedback_type_id) {
                  feedback_type = em.getById('feedback_type', feedback_type_id);
                  if (feedback_type) {
                    feedback_type.display_order = x;
                  }
                }
                return postData.display_orders.push(feedback_type_id);
              });
              promise = _this.Api.sendPostJson('/feedback_types/display_order', postData);
              return _this.pingElement('display_orders');
            };
          })(this)
        };
      };

      Admin_FeedbackTypes_Ctrl_List.prototype.initialLoad = function() {
        var list_promise;
        list_promise = this.FeedbackTypesData.loadList().then((function(_this) {
          return function(recs) {
            _this.feedback_types = recs.values();
            return _this.addManagedListener(_this.FeedbackTypesData.recs, 'changed', function() {
              _this.feedback_types = _this.FeedbackTypesData.recs.values();
              return _this.ngApply();
            });
          };
        })(this));
        return this.$q.all([list_promise]);
      };


      /*
       * Show the delete dlg
       */

      Admin_FeedbackTypes_Ctrl_List.prototype.startDelete = function(feedback_type) {
        var inst, move_feedback_types_list;
        move_feedback_types_list = this.FeedbackTypesData.getListOfMovables(feedback_type);
        if (!move_feedback_types_list.length) {
          this.showAlert('@no_delete_last');
          return;
        }
        inst = this.$modal.open({
          templateUrl: this.getTemplatePath('FeedbackTypes/delete-modal.html'),
          controller: [
            '$scope', '$modalInstance', 'move_feedback_types_list', function($scope, $modalInstance, move_feedback_types_list) {
              $scope.move_feedback_types_list = move_feedback_types_list;
              $scope.selected = {
                move_to_id: move_feedback_types_list[0].id
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
            move_feedback_types_list: (function(_this) {
              return function() {
                return move_feedback_types_list;
              };
            })(this)
          }
        });
        return inst.result.then((function(_this) {
          return function(move_to) {
            return _this.deleteFeedbackType(feedback_type, move_to);
          };
        })(this));
      };


      /*
      		 * Actually do the delete
       	 * @param feedback_type - feedback type we want to delete
       	 * @param move_to - to what type feedback should be moved
       */

      Admin_FeedbackTypes_Ctrl_List.prototype.deleteFeedbackType = function(feedback_type, move_to) {
        return this.Api.sendDelete('/feedback_types/' + feedback_type.id, {
          move_to: move_to
        }).success((function(_this) {
          return function() {
            _this.FeedbackTypesData.remove(feedback_type.id);
            _this.ngApply();
            if (_this.$state.current.name === 'portal.feedback_types.edit' && parseInt(_this.$state.params.id) === feedback_type.id) {
              return _this.$state.go('portal.feedback_types');
            }
          };
        })(this));
      };

      return Admin_FeedbackTypes_Ctrl_List;

    })(Admin_Ctrl_Base);
    return Admin_FeedbackTypes_Ctrl_List.EXPORT_CTRL();
  });

}).call(this);

//# sourceMappingURL=List.js.map
