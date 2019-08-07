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
      this.feedback_status = {};

      // @$stateParams.type will be defined in case of creation of new feedback status

      this.statusType = this.$stateParams.type;

      if (this.statusType) {
        this.feedback_status.status_type = this.statusType;
      }
    }

    initialLoad() {
      const promises = [];
      if (this.$stateParams.id) {
        promises.push(this.Api.sendGet(`/community_statuses/${this.$stateParams.id}`).then(result => this.feedback_status = result.data.feedback_status)
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
      this.feedback_status.brand = this.$stateParams.brandId;

      if (!this.$scope.form_props.$valid) {
        return;
      }

      this.startSpinner('saving_feedback_status');

      if (this.feedback_status.id) {
        is_new = false;
        promise = this.Api.sendPostJson(`/community_statuses/${this.feedback_status.id}`, { feedback_status: this.feedback_status });
      } else {
        is_new = true;
        promise = this.Api.sendPutJson('/community_statuses', { feedback_status: this.feedback_status });
      }

      promise.success((result) => {
        this.feedback_status.id = result.id;
        this.feedback_status.brand = result.brand;

        this.stopSpinner('saving_feedback_status', true).then(() => this.Growl.success(this.getRegisteredMessage('saved_feedback_status')));

        this.CommunityStatusesData.updateModel(this.feedback_status);

        this.skipDirtyState();

        if (is_new) {
          return this.$state.go('portal.community_statuses.gocreate', { type: this.statusType });
        }
        return this.$state.go('portal.community_statuses');
      });
      promise.error((info, code) => {
        this.stopSpinner('saving_feedback_status', true);
        return this.applyErrorResponseToView(info);
      });

      return promise;
    }
  }
  Admin_CommunityStatuses_Ctrl_Edit.initClass();

  return Admin_CommunityStatuses_Ctrl_Edit.EXPORT_CTRL();
});
