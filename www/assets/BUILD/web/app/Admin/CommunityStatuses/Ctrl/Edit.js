define([
  'Admin/Main/Ctrl/Base'
], (
  Admin_Ctrl_Base
) => {
  class Admin_CommunityStatuses_Ctrl_Edit extends Admin_Ctrl_Base {
    static initClass() {
      this.CTRL_ID = 'Admin_CommunityStatuses_Ctrl_Edit';
      this.CTRL_AS = 'CommunityStatusesEdit';
      this.DEPS    = ['Api', 'Growl', 'CommunityStatusesData', '$stateParams', '$modal'];
    }

    init() {
      this.community_status = {};

      // @$stateParams.type will be defined in case of creation of new community status

      this.statusType = this.$stateParams.type;

      this.$scope.picker = false;
      this.$scope.colors = [
        '#e11d21', '#eb6420', '#fbca04', '#009800', '#006b75', '#207de5', '#0052cc', '#5319e7',
        '#f7c6c7', '#fad8c7', '#fef2c0', '#bfe5bf', '#bfdadc', '#c7def8', '#bfd4f2', '#d4c5f9'
      ];
      if (this.statusType) {
        this.community_status.status_type = this.statusType;
      }
    }

    initialLoad() {
      const promises = [];
      if (this.$stateParams.id) {
        promises.push(this.Api.sendGet(`/community_statuses/${this.$stateParams.id}`).then(result => this.community_status = result.data.community_status)
        );

        return this.$q.all(promises);
      }
    }

    /*
      * Saves the current form
      *
      * @return {promise}
    */
    saveCommunityStatus() {
      let is_new,
        promise;
      this.community_status.brand = this.$stateParams.brandId;

      if (!this.$scope.form_props.$valid) {
        return;
      }

      this.startSpinner('saving_community_status');

      if (this.community_status.id) {
        is_new = false;
        promise = this.Api.sendPostJson(`/community_statuses/${this.community_status.id}`, { community_status: this.community_status });
      } else {
        is_new = true;
        promise = this.Api.sendPutJson('/community_statuses', { community_status: this.community_status });
      }

      promise.success((result) => {
        this.community_status.id = result.id;
        this.community_status.brand = result.brand;

        this.stopSpinner('saving_community_status', true).then(() => this.Growl.success(this.getRegisteredMessage('saved_community_status')));

        this.CommunityStatusesData.updateModel(this.community_status);

        this.skipDirtyState();

        if (is_new) {
          return this.$state.go('portal.community_statuses.gocreate', { type: this.statusType });
        }
        return this.$state.go('portal.community_statuses');
      });
      promise.error((info, code) => {
        this.stopSpinner('saving_community_status', true);
        return this.applyErrorResponseToView(info);
      });

      return promise;
    }
  }
  Admin_CommunityStatuses_Ctrl_Edit.initClass();

  return Admin_CommunityStatuses_Ctrl_Edit.EXPORT_CTRL();
});
