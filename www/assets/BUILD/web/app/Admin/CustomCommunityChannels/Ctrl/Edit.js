define([
  'Admin/Main/Ctrl/Base'
], (
  Admin_Ctrl_Base
) => {
  class Admin_CustomCommunityChannels_Ctrl_Edit extends Admin_Ctrl_Base {
    static initClass() {
      this.CTRL_ID = 'Admin_CustomCommunityChannels_Ctrl_Edit';
      this.CTRL_AS = 'CustomCommunityChannelsEdit';
      this.DEPS    = ['Api', 'Growl', 'CustomCommunityChannelsData', '$stateParams', '$modal'];
    }

    init() {
      this.custom_community_channel = {};
      this.custom_community_channels_parent_list = {};

      this.addManagedListener(this.CustomCommunityChannelsData.recs, 'changed', () => {
        this.custom_community_channels_parent_list = this.CustomCommunityChannelsData.getListOfParents(this.custom_community_channel);
        return this.ngApply();
      });
    }

    initialLoad() {
      const requests = [
        this.CustomCommunityChannelsData.loadList(),
        this.$stateParams.id ?
          this.Api.sendDataGet({
            custom_community_channel: `/custom_community_channels/${this.$stateParams.id}`
          }) : undefined
      ];

      const promise = this.$q.all(requests).then((result) => {
        if (this.$stateParams.id) {
          this.custom_community_channel = result[1].data.custom_community_channel.custom_community_channel;
        }
        return this.custom_community_channels_parent_list = this.CustomCommunityChannelsData.getListOfParents(this.custom_community_channel);
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
      this.custom_community_channel.brand = this.$stateParams.brandId;

      if (!this.$scope.form_props.$valid) {
        return;
      }

      const is_new = !this.custom_community_channel.id;

      this.startSpinner('saving_custom_community_channel');

      if (is_new) {
        promise = this.Api.sendPutJson('/custom_community_channels', { custom_community_channel: this.custom_community_channel });
      } else {
        promise = this.Api.sendPostJson(`/custom_community_channels/${this.custom_community_channel.id}`, { custom_community_channel: this.custom_community_channel });
      }

      promise.success((result) => {
        this.custom_community_channel.id = result.id;
        this.custom_community_channel.brand = result.brand;

        this.stopSpinner('saving_custom_community_channel', true).then(() => this.Growl.success(this.getRegisteredMessage('saved_custom_community_channel')));

        this.CustomCommunityChannelsData.updateModel(this.custom_community_channel);

        this.skipDirtyState();

        if (is_new) {
          return this.$state.go('portal.custom_community_channels.gocreate');
        }
        return this.$state.go('portal.custom_community_channels');
      });

      promise.error((info, code) => {
        this.stopSpinner('saving_custom_community_channel', true);
        return this.applyErrorResponseToView(info);
      });

      return promise;
    }


    showDelete() {
      let inst;
      const id = this.custom_community_channel != null ? this.custom_community_channel.id : undefined;
      if (!id) { return; }

      if (this.CustomCommunityChannelsData.hasChildren(this.custom_community_channel)) {
        return this.showAlert('You cannot delete a category with sub-categories. Move or delete the sub-categories first.');
      }

      const list = this.CustomCommunityChannelsData.getListOfMovables(this.custom_community_channel);

      const deleteStart = move_to => this.Api.sendDelete(`/custom_community_channels/${id}?move_to=${move_to || 0}`).then(() => {
        this.CustomCommunityChannelsData.remove(id);
        return this.$state.go('portal.custom_community_channels');
      });

      return inst = this.$modal.open({
        templateUrl: this.getTemplatePath('CommunityCategories/delete-modal.html'),
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
  Admin_CustomCommunityChannels_Ctrl_Edit.initClass();


  return Admin_CustomCommunityChannels_Ctrl_Edit.EXPORT_CTRL();
});
