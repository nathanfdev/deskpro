define([
  'Admin/Main/Ctrl/Base'
], (
  Admin_Ctrl_Base
) => {
  class Admin_Banning_Ctrl_EditEmail extends Admin_Ctrl_Base {
    static initClass() {
      this.CTRL_ID   = 'Admin_Banning_Ctrl_EditEmail';
      this.CTRL_AS   = 'EditCtrl';
      this.DEPS      = ['$stateParams'];
    }

    init() {
      this.banData = this.DataService.get('Bans');
      this.banData.setType('email');
      return this.email_ban = null;
    }

    /*
  *
  */

    initialLoad() {
      const promise = this.banData.loadEditBanData(this.$stateParams.ban || null).then((data) => {
        this.email_ban = data.email_ban;
        return this.form = data.form;
      });
      return promise;
    }

    /*
     *
   */

    saveForm() {
      if (!this.$scope.form_props.$valid) {
        return;
      }

      const is_new = !this.$stateParams.ban;

      const promise = this.banData.saveFormModel(this.email_ban, this.form);

      this.startSpinner('saving');
      return promise.then(() => {
        this.stopSpinner('saving', true).then(() => this.Growl.success('Saved'));

        this.skipDirtyState();
        if (is_new) {
          return this.$state.go('crm.banning.gocreate_email');
        }
        return this.$state.go('crm.banning.edit_email', { ban: this.email_ban.banned_email });
      });
    }
  }
  Admin_Banning_Ctrl_EditEmail.initClass();

  return Admin_Banning_Ctrl_EditEmail.EXPORT_CTRL();
});
