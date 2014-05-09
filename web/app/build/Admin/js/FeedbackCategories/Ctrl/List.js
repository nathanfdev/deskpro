(function() {
  var __hasProp = {}.hasOwnProperty,
    __extends = function(child, parent) { for (var key in parent) { if (__hasProp.call(parent, key)) child[key] = parent[key]; } function ctor() { this.constructor = child; } ctor.prototype = parent.prototype; child.prototype = new ctor(); child.__super__ = parent.prototype; return child; };

  define(['Admin/Main/Ctrl/Base'], function(Admin_Ctrl_Base) {
    var Admin_FeedbackCategories_Ctrl_List;
    Admin_FeedbackCategories_Ctrl_List = (function(_super) {
      __extends(Admin_FeedbackCategories_Ctrl_List, _super);

      function Admin_FeedbackCategories_Ctrl_List() {
        return Admin_FeedbackCategories_Ctrl_List.__super__.constructor.apply(this, arguments);
      }

      Admin_FeedbackCategories_Ctrl_List.CTRL_ID = 'Admin_FeedbackCategories_Ctrl_List';

      Admin_FeedbackCategories_Ctrl_List.CTRL_AS = 'FeedbackCategoriesList';

      Admin_FeedbackCategories_Ctrl_List.DEPS = ['$rootScope', '$scope', 'FeedbackCategoriesData', 'em', 'Api', '$state', 'Growl'];

      Admin_FeedbackCategories_Ctrl_List.prototype.init = function() {
        this.feedback_categories = [];
        this.parent_data = [];
        this.child_data = {};
        return this.sortedListOptions = {
          axis: 'y',
          handle: '.drag-handle',
          update: (function(_this) {
            return function(ev, data) {
              var $list, em, postData, x;
              $list = data.item.closest('ul');
              postData = {
                display_orders: []
              };
              x = 0;
              em = _this.em;
              $list.find('li').each(function() {
                var feedback_category, feedback_category_id;
                x += 10;
                feedback_category_id = parseInt($(this).data('id'));
                if (feedback_category_id) {
                  feedback_category = em.getById('feedback_category', feedback_category_id);
                  if (feedback_category) {
                    feedback_category.display_order = x;
                  }
                }
                return postData.display_orders.push(feedback_category_id);
              });
              _this.Api.sendPostJson('/feedback_categories/display_order', postData);
              return _this.pingElement('display_orders');
            };
          })(this)
        };
      };

      Admin_FeedbackCategories_Ctrl_List.prototype.initialLoad = function() {
        var list_promise;
        list_promise = this.FeedbackCategoriesData.loadList().then((function(_this) {
          return function(recs) {
            _this.initHierarchyData(recs.values());
            return _this.addManagedListener(_this.FeedbackCategoriesData.recs, 'changed', function() {
              _this.initHierarchyData(_this.FeedbackCategoriesData.recs.values());
              return _this.ngApply();
            });
          };
        })(this));
        return this.$q.all([list_promise]);
      };

      Admin_FeedbackCategories_Ctrl_List.prototype.initHierarchyData = function(feedback_categories) {
        var category, _i, _len, _results;
        this.feedback_categories = feedback_categories;
        this.parent_data = [];
        this.child_data = {};
        _results = [];
        for (_i = 0, _len = feedback_categories.length; _i < _len; _i++) {
          category = feedback_categories[_i];
          if (parseInt(category.parent_id, 10)) {
            if (!this.child_data[category.parent_id]) {
              this.child_data[category.parent_id] = [];
            }
            _results.push(this.child_data[category.parent_id].push(category));
          } else {
            _results.push(this.parent_data.push(category));
          }
        }
        return _results;
      };


      /*
      		 * Show the delete dlg
       */

      Admin_FeedbackCategories_Ctrl_List.prototype.startDelete = function(feedback_category) {
        var inst, move_feedback_categories_list;
        if (this.FeedbackCategoriesData.hasChildren(feedback_category)) {
          this.showAlert("You cannot delete a category with sub-categories. Move or delete the sub-categories first.");
          return;
        }
        move_feedback_categories_list = this.FeedbackCategoriesData.getListOfMovables(feedback_category);
        inst = this.$modal.open({
          templateUrl: this.getTemplatePath('FeedbackCategories/delete-modal.html'),
          controller: [
            '$scope', '$modalInstance', 'move_feedback_categories_list', function($scope, $modalInstance, move_feedback_categories_list) {
              $scope.move_feedback_categories_list = move_feedback_categories_list;
              $scope.selected = {
                move_to_id: move_feedback_categories_list.length ? move_feedback_categories_list[0].id : ''
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
            move_feedback_categories_list: (function(_this) {
              return function() {
                return move_feedback_categories_list;
              };
            })(this)
          }
        });
        return inst.result.then((function(_this) {
          return function(move_to) {
            return _this.deleteFeedbackCategory(feedback_category, move_to);
          };
        })(this));
      };


      /*
      		 * Actually do the delete
       */

      Admin_FeedbackCategories_Ctrl_List.prototype.deleteFeedbackCategory = function(feedback_category, move_to) {
        return this.Api.sendDelete('/feedback_categories/' + feedback_category.id, {
          move_to: move_to
        }).success((function(_this) {
          return function() {
            _this.FeedbackCategoriesData.remove(feedback_category.id);
            _this.ngApply();
            if (_this.$state.current.name === 'portal.feedback_categories.edit' && parseInt(_this.$state.params.id) === feedback_category.id) {
              return _this.$state.go('portal.feedback_categories');
            }
          };
        })(this));
      };

      return Admin_FeedbackCategories_Ctrl_List;

    })(Admin_Ctrl_Base);
    return Admin_FeedbackCategories_Ctrl_List.EXPORT_CTRL();
  });

}).call(this);

//# sourceMappingURL=List.js.map
