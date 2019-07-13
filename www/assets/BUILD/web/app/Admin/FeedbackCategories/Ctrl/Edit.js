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
      this.community_custom_channel = {};
      this.community_custom_channels_parent_list = {};

      this.addManagedListener(this.FeedbackCategoriesData.recs, 'changed', () => {
        this.community_custom_channels_parent_list = this.FeedbackCategoriesData.getListOfParents(this.community_custom_channel);
        return this.ngApply();
      });
    }

    initialLoad() {
      const requests = [
        this.FeedbackCategoriesData.loadList(),
        this.$stateParams.id ?
          this.Api.sendDataGet({
            community_custom_channel: `/community_custom_channels/${this.$stateParams.id}`
          }) : undefined
      ];

      const promise = this.$q.all(requests).then((result) => {
        if (this.$stateParams.id) {
          this.community_custom_channel = result[1].data.community_custom_channel.community_custom_channel;
        }
        return this.community_custom_channels_parent_list = this.FeedbackCategoriesData.getListOfParents(this.community_custom_channel);
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
      this.community_custom_channel.brand = this.$stateParams.brandId;

      if (!this.$scope.form_props.$valid) {
        return;
      }

      const is_new = !this.community_custom_channel.id;

      this.startSpinner('saving_community_custom_channel');

      if (is_new) {
        promise = this.Api.sendPutJson('/community_custom_channels', { community_custom_channel: this.community_custom_channel });
      } else {
        promise = this.Api.sendPostJson(`/community_custom_channels/${this.community_custom_channel.id}`, { community_custom_channel: this.community_custom_channel });
      }

      promise.success((result) => {
        this.community_custom_channel.id = result.id;
        this.community_custom_channel.brand = result.brand;

        this.stopSpinner('saving_community_custom_channel', true).then(() => this.Growl.success(this.getRegisteredMessage('saved_community_custom_channel')));

        this.FeedbackCategoriesData.updateModel(this.community_custom_channel);

        this.skipDirtyState();

        if (is_new) {
          return this.$state.go('portal.community_custom_channels.gocreate');
        }
        return this.$state.go('portal.community_custom_channels');
      });

      promise.error((info, code) => {
        this.stopSpinner('saving_community_custom_channel', true);
        return this.applyErrorResponseToView(info);
      });

      return promise;
    }


    showDelete() {
      let inst;
      const id = this.community_custom_channel != null ? this.community_custom_channel.id : undefined;
      if (!id) { return; }

      if (this.FeedbackCategoriesData.hasChildren(this.community_custom_channel)) {
        return this.showAlert('You cannot delete a category with sub-categories. Move or delete the sub-categories first.');
      }

      const list = this.FeedbackCategoriesData.getListOfMovables(this.community_custom_channel);

      const deleteStart = move_to => this.Api.sendDelete(`/community_custom_channels/${id}?move_to=${move_to || 0}`).then(() => {
        this.FeedbackCategoriesData.remove(id);
        return this.$state.go('portal.community_custom_channels');
      });

      return inst = this.$modal.open({
        templateUrl: this.getTemplatePath('FeedbackCategories/delete-modal.html'),
        controller:  ['$scope', '$modalInstance', function ($scope, $modalInstance) {
          $scope.move_community_channels_list = list;
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
