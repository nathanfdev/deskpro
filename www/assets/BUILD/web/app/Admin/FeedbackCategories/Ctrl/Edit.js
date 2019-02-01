define([
  'Admin/Main/Ctrl/Base'
], (
  Admin_Ctrl_Base
) => {
  class Admin_FeedbackCategories_Ctrl_Edit extends Admin_Ctrl_Base {
    static initClass() {
      this.CTRL_ID = 'Admin_FeedbackCategories_Ctrl_Edit';
      this.CTRL_AS = 'FeedbackCategoriesEdit';
      this.DEPS    = ['Api', 'Growl', 'FeedbackCategoriesData', '$stateParams', '$modal'];
    }

    init() {
      this.feedback_category = {};
      this.feedback_categories_parent_list = {};

      this.addManagedListener(this.FeedbackCategoriesData.recs, 'changed', () => {
        this.feedback_categories_parent_list = this.FeedbackCategoriesData.getListOfParents(this.feedback_category);
        return this.ngApply();
      });
    }

    initialLoad() {
      const requests = [
        this.FeedbackCategoriesData.loadList(),
        this.$stateParams.id ?
          this.Api.sendDataGet({
            feedback_category: `/feedback_categories/${this.$stateParams.id}`
          }) : undefined
      ];

      const promise = this.$q.all(requests).then((result) => {
        if (this.$stateParams.id) {
          this.feedback_category = result[1].data.feedback_category.feedback_category;
        }
        return this.feedback_categories_parent_list = this.FeedbackCategoriesData.getListOfParents(this.feedback_category);
      });

      return promise;
    }

    /*
      * Saves the current form
      *
      * @return {promise}
    */
    saveForm() {
      let promise;
      this.feedback_category.brand = this.$stateParams.brandId;

      if (!this.$scope.form_props.$valid) {
        return;
      }

      const is_new = !this.feedback_category.id;

      this.startSpinner('saving_feedback_category');

      if (is_new) {
        promise = this.Api.sendPutJson('/feedback_categories', { feedback_category: this.feedback_category });
      } else {
        promise = this.Api.sendPostJson(`/feedback_categories/${this.feedback_category.id}`, { feedback_category: this.feedback_category });
      }

      promise.success((result) => {
        this.feedback_category.id = result.id;
        this.feedback_category.brand = result.brand;

        this.stopSpinner('saving_feedback_category', true).then(() => this.Growl.success(this.getRegisteredMessage('saved_feedback_category')));

        this.FeedbackCategoriesData.updateModel(this.feedback_category);

        this.skipDirtyState();

        if (is_new) {
          return this.$state.go('portal.feedback_categories.gocreate');
        }
        return this.$state.go('portal.feedback_categories');
      });

      promise.error((info, code) => {
        this.stopSpinner('saving_feedback_category', true);
        return this.applyErrorResponseToView(info);
      });

      return promise;
    }


    showDelete() {
      let inst;
      const id = this.feedback_category != null ? this.feedback_category.id : undefined;
      if (!id) { return; }

      if (this.FeedbackCategoriesData.hasChildren(this.feedback_category)) {
        return this.showAlert('You cannot delete a category with sub-categories. Move or delete the sub-categories first.');
      }

      const list = this.FeedbackCategoriesData.getListOfMovables(this.feedback_category);

      const deleteStart = move_to => this.Api.sendDelete(`/feedback_categories/${id}?move_to=${move_to || 0}`).then(() => {
        this.FeedbackCategoriesData.remove(id);
        return this.$state.go('portal.feedback_categories');
      });

      return inst = this.$modal.open({
        templateUrl: this.getTemplatePath('FeedbackCategories/delete-modal.html'),
        controller:  ['$scope', '$modalInstance', function ($scope, $modalInstance) {
          $scope.move_feedback_categories_list = list;
          $scope.model = { move_to: (list[0] != null ? list[0].id : undefined) };
          $scope.dismiss = () => $modalInstance.dismiss();
          return $scope.confirm = () => deleteStart($scope.model.move_to).then(() => $modalInstance.dismiss());
        }
        ]
      });
    }
  }
  Admin_FeedbackCategories_Ctrl_Edit.initClass();


  return Admin_FeedbackCategories_Ctrl_Edit.EXPORT_CTRL();
});
