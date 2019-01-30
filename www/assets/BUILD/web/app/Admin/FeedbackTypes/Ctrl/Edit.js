/*
 * decaffeinate suggestions:
 * DS101: Remove unnecessary use of Array.from
 * DS102: Remove unnecessary code created because of implicit returns
 * DS203: Remove `|| {}` from converted for-own loops
 * DS206: Consider reworking classes to avoid initClass
 * Full docs: https://github.com/decaffeinate/decaffeinate/blob/master/docs/suggestions.md
 */
define([
  'Admin/Main/Ctrl/Base',
  'underscore'
], function(
  Admin_Ctrl_Base,
  _
) {
  class Admin_FeedbackTypes_Ctrl_Edit extends Admin_Ctrl_Base {
    static initClass() {
      this.CTRL_ID = 'Admin_FeedbackTypes_Ctrl_Edit';
      this.CTRL_AS = 'FeedbackTypesEdit';
      this.DEPS    = ['Api', 'Growl', 'FeedbackTypesData', '$stateParams', '$modal'];
    }

    init() {

      this.feedback_type = {};
      this.usergroups = [];
      return this.selected_usergroups = {};
    }




    initialLoad() {
      const promises = [];
      promises.push(this.Api.sendDataGet({usergroups: '/user_groups'}).then(result => {
        return this.usergroups = result.data.usergroups.groups;
      })
      );

      if (this.$stateParams.id) {
        promises.push(this.Api.sendDataGet({
          feedback_type: `/feedback_types/${this.$stateParams.id}`,
        }).then(result => {
          this.feedback_type = result.data.feedback_type.feedback_type;
          const ids = _.pluck(this.feedback_type.usergroups, 'id');
          return Array.from(ids).map((id) =>
            (this.selected_usergroups[id] = true));
        })
        );
      }

      return this.$q.all(promises);
    }



    /*
      * Saves the current form
      *
      * @return {promise}
    */
    saveFeedbackType() {

      let is_new, promise;
      this.feedback_type.brand = this.$stateParams.brandId;
      this.feedback_type.usergroups = [];

      for (let key of Object.keys(this.selected_usergroups || {})) {
        const value = this.selected_usergroups[key];
        if (value) {
          const usergroup = _.findWhere(this.usergroups, {id: parseInt(key)});
          if (usergroup) { this.feedback_type.usergroups.push(usergroup.id); }
        }
      }

      if (!this.$scope.form_props.$valid) {
        return;
      }

      this.startSpinner('saving_feedback_type');

      if (this.feedback_type.id) {
        is_new = false;
        promise = this.Api.sendPostJson(`/feedback_types/${this.feedback_type.id}`, {feedback_type: this.feedback_type});
      } else {
        is_new = true;
        promise = this.Api.sendPutJson('/feedback_types', {feedback_type: this.feedback_type});
      }

      promise.success(result => {

        this.feedback_type.id = result.id;
        this.feedback_type.brand = result.brand;

        this.stopSpinner('saving_feedback_type', true).then(() => {
          return this.Growl.success(this.getRegisteredMessage('saved_feedback_type'));
        });

        this.FeedbackTypesData.updateModel(this.feedback_type);

        this.skipDirtyState();

        if (is_new) {
          return this.$state.go('portal.feedback_types.gocreate');
        } else {
          return this.$state.go('portal.feedback_types');
        }
      });
      promise.error((info, code) => {
        this.stopSpinner('saving_feedback_type', true);
        return this.applyErrorResponseToView(info);
      });

      return promise;
    }
  }
  Admin_FeedbackTypes_Ctrl_Edit.initClass();

  return Admin_FeedbackTypes_Ctrl_Edit.EXPORT_CTRL();
});