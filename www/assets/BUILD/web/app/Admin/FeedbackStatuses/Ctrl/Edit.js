// TODO: This file was created by bulk-decaffeinate.
// Sanity-check the conversion and remove this comment.
/*
 * decaffeinate suggestions:
 * DS102: Remove unnecessary code created because of implicit returns
 * DS206: Consider reworking classes to avoid initClass
 * Full docs: https://github.com/decaffeinate/decaffeinate/blob/master/docs/suggestions.md
 */
define([
  'Admin/Main/Ctrl/Base'
], function(
  Admin_Ctrl_Base
) {
  class Admin_FeedbackStatuses_Ctrl_Edit extends Admin_Ctrl_Base {
    static initClass() {
      this.CTRL_ID = 'Admin_FeedbackStatuses_Ctrl_Edit';
      this.CTRL_AS = 'FeedbackStatusesEdit';
      this.DEPS    = ['Api', 'Growl', 'FeedbackStatusesData', '$stateParams', '$modal'];
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
        promises.push(this.Api.sendGet(`/feedback_statuses/${this.$stateParams.id}`).then(result => {
          return this.feedback_status = result.data.feedback_status;
        })
        );

        return this.$q.all(promises);
      }
    }

    /*
      * Saves the current form
      *
      * @return {promise}
    */
    saveFeedbackStatus() {

      let is_new, promise;
      this.feedback_status.brand = this.$stateParams.brandId;

      if (!this.$scope.form_props.$valid) {
        return;
      }

      this.startSpinner('saving_feedback_status');

      if (this.feedback_status.id) {
        is_new = false;
        promise = this.Api.sendPostJson(`/feedback_statuses/${this.feedback_status.id}`, {feedback_status: this.feedback_status});
      } else {
        is_new = true;
        promise = this.Api.sendPutJson('/feedback_statuses', {feedback_status: this.feedback_status});
      }

      promise.success(result => {

        this.feedback_status.id = result.id;
        this.feedback_status.brand = result.brand;

        this.stopSpinner('saving_feedback_status', true).then(() => {
          return this.Growl.success(this.getRegisteredMessage('saved_feedback_status'));
        });

        this.FeedbackStatusesData.updateModel(this.feedback_status);

        this.skipDirtyState();

        if (is_new) {
          return this.$state.go('portal.feedback_statuses.gocreate', {type: this.statusType});
        } else {
          return this.$state.go('portal.feedback_statuses');
        }
      });
      promise.error((info, code) => {
        this.stopSpinner('saving_feedback_status', true);
        return this.applyErrorResponseToView(info);
      });

      return promise;
    }
  }
  Admin_FeedbackStatuses_Ctrl_Edit.initClass();

  return Admin_FeedbackStatuses_Ctrl_Edit.EXPORT_CTRL();
});