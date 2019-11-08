define([
  'Admin/Main/Ctrl/Base'
], (
  Admin_Ctrl_Base
) => {
  class Admin_CommunityCategories_Ctrl_Edit extends Admin_Ctrl_Base {
    static initClass() {
      this.CTRL_ID = 'Admin_CommunityCategories_Ctrl_Edit';
      this.CTRL_AS = 'CommunityCategoriesEdit';
      this.DEPS    = ['Api', 'Growl', 'CommunityCategoriesData', '$stateParams', '$modal'];
    }

    init() {
      this.community_category = {};
      this.community_categories_parent_list = {};

      this.addManagedListener(this.CommunityCategoriesData.recs, 'changed', () => {
        this.community_categories_parent_list = this.CommunityCategoriesData.getListOfParents(this.community_category);
        return this.ngApply();
      });
    }

    initialLoad() {
      const requests = [
        this.CommunityCategoriesData.loadList(),
        this.$stateParams.id ?
          this.Api.sendDataGet({
            community_category: `/community_categories/${this.$stateParams.id}`
          }) : undefined
      ];

      const promise = this.$q.all(requests).then((result) => {
        if (this.$stateParams.id) {
          this.community_category = result[1].data.community_category.community_category;
        }
        return this.community_categories_parent_list = this.CommunityCategoriesData.getListOfParents(this.community_category);
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
      this.community_category.brand = this.$stateParams.brandId;

      if (!this.$scope.form_props.$valid) {
        return;
      }

      const is_new = !this.community_category.id;

      this.startSpinner('saving_community_category');

      if (is_new) {
        promise = this.Api.sendPutJson('/community_categories', { community_category: this.community_category });
      } else {
        promise = this.Api.sendPostJson(`/community_categories/${this.community_category.id}`, { community_category: this.community_category });
      }

      promise.success((result) => {
        this.community_category.id = result.id;
        this.community_category.brand = result.brand;

        this.stopSpinner('saving_community_category', true).then(() => this.Growl.success(this.getRegisteredMessage('saved_community_category')));

        this.CommunityCategoriesData.updateModel(this.community_category);

        this.skipDirtyState();

        if (is_new) {
          return this.$state.go('portal.community_categories.gocreate');
        }
        return this.$state.go('portal.community_categories');
      });

      promise.error((info, code) => {
        this.stopSpinner('saving_community_category', true);
        return this.applyErrorResponseToView(info);
      });

      return promise;
    }


    showDelete() {
      let inst;
      const id = this.community_category != null ? this.community_category.id : undefined;
      if (!id) { return; }

      if (this.CommunityCategoriesData.hasChildren(this.community_category)) {
        return this.showAlert('You cannot delete a category with sub-categories. Move or delete the sub-categories first.');
      }

      const list = this.CommunityCategoriesData.getListOfMovables(this.community_category);

      const deleteStart = move_to => this.Api.sendDelete(`/community_categories/${id}?move_to=${move_to || 0}`).then(() => {
        this.CommunityCategoriesData.remove(id);
        return this.$state.go('portal.community_categories');
      });

      return inst = this.$modal.open({
        templateUrl: this.getTemplatePath('CommunityCategories/delete-modal.html'),
        controller:  ['$scope', '$modalInstance', function ($scope, $modalInstance) {
          $scope.move_community_forums_list = list;
          $scope.model = { move_to: (list[0] != null ? list[0].id : undefined) };
          $scope.dismiss = () => $modalInstance.dismiss();
          return $scope.confirm = () => deleteStart($scope.model.move_to).then(() => $modalInstance.dismiss());
        }
        ]
      });
    }
  }
  Admin_CommunityCategories_Ctrl_Edit.initClass();


  return Admin_CommunityCategories_Ctrl_Edit.EXPORT_CTRL();
});
