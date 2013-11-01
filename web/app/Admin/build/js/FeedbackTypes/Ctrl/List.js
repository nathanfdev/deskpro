(function() {
  var __hasProp = {}.hasOwnProperty,
    __extends = function(child, parent) { for (var key in parent) { if (__hasProp.call(parent, key)) child[key] = parent[key]; } function ctor() { this.constructor = child; } ctor.prototype = parent.prototype; child.prototype = new ctor(); child.__super__ = parent.prototype; return child; };

  define(['Admin/Main/Ctrl/Base', 'Admin/App'], function(Admin_Ctrl_Base) {
    var Admin_FeedbackTypes_Ctrl_List, _ref;
    Admin_FeedbackTypes_Ctrl_List = (function(_super) {
      __extends(Admin_FeedbackTypes_Ctrl_List, _super);

      function Admin_FeedbackTypes_Ctrl_List() {
        _ref = Admin_FeedbackTypes_Ctrl_List.__super__.constructor.apply(this, arguments);
        return _ref;
      }

      Admin_FeedbackTypes_Ctrl_List.CTRL_ID = 'Admin_FeedbackTypes_Ctrl_List';

      Admin_FeedbackTypes_Ctrl_List.CTRL_AS = 'FeedbackTypesList';

      Admin_FeedbackTypes_Ctrl_List.DEPS = ['$rootScope', '$scope', 'FeedbackTypesData', 'em', 'Api', '$state', 'Growl'];

      Admin_FeedbackTypes_Ctrl_List.CTRL_TYPE = 'list';

      Admin_FeedbackTypes_Ctrl_List.prototype.init = function() {
        return this.feedback_types = [];
      };

      Admin_FeedbackTypes_Ctrl_List.prototype.initialLoad = function() {
        var list_promise,
          _this = this;
        list_promise = this.FeedbackTypesData.loadList().then(function(recs) {
          _this.feedback_types = recs.values();
          return _this.addManagedListener(_this.FeedbackTypesData.recs, 'changed', function() {
            _this.feedback_types = _this.FeedbackTypesData.recs.values();
            return _this.ngApply();
          });
        });
        return this.$q.all([list_promise]);
      };

      /*
      # Show the delete dlg
      */


      Admin_FeedbackTypes_Ctrl_List.prototype.startDelete = function(feedback_type) {
        var inst, move_feedback_types_list,
          _this = this;
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
            move_feedback_types_list: function() {
              return move_feedback_types_list;
            }
          }
        });
        return inst.result.then(function(move_to) {
          return _this.deleteFeedbackType(feedback_type, move_to);
        });
      };

      /*
      		# Actually do the delete
       	# @param feedback_type - feedback type we want to delete
       	# @param move_to - to what type feedback should be moved
      */


      Admin_FeedbackTypes_Ctrl_List.prototype.deleteFeedbackType = function(feedback_type, move_to) {
        var _this = this;
        return this.Api.sendDelete('/feedback_types/' + feedback_type.id, {
          move_to: move_to
        }).success(function() {
          _this.FeedbackTypesData.remove(feedback_type.id);
          _this.ngApply();
          if (_this.$state.current.name === 'portal.feedback_types.edit' && parseInt(_this.$state.params.id) === feedback_type.id) {
            return _this.$state.go('portal.feedback_types');
          }
        });
      };

      return Admin_FeedbackTypes_Ctrl_List;

    })(Admin_Ctrl_Base);
    return Admin_FeedbackTypes_Ctrl_List.EXPORT_CTRL();
  });

}).call(this);

/*
//@ sourceMappingURL=List.js.map
*/