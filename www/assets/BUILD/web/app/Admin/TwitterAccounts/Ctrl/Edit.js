define([
  'Admin/Main/Ctrl/Base',
  'underscore'
], function(
  Admin_Ctrl_Base,
  _
) {
  class Admin_TwitterAccounts_Ctrl_Edit extends Admin_Ctrl_Base {
    static initClass() {
      this.CTRL_ID   = 'Admin_TwitterAccounts_Ctrl_Edit';
      this.CTRL_AS   = 'EditCtrl';
      this.DEPS      = ['$stateParams'];
    }

    init() {
      this.twitterAccountData = this.DataService.get('TwitterAccounts');
      return this.twitter_account = null;
    }

    initialLoad() {
      const promise = this.twitterAccountData.loadEditTwitterAccountData(this.$stateParams.id || null).then( data => {

        this.twitter_account  = data.twitter_account;
        return this.form = data.form;
      });
      return promise;
    }

    saveForm() {

      this.twitter_account.persons = [];

      for (let key of Object.keys(this.selected_agents || {})) {
        const value = this.selected_agents[key];
        if (value) {
          const agent = _.findWhere(this.agents, {id: parseInt(key)});
          if (agent) { this.twitter_account.persons.push(agent.id); }
        }
      }

      if (!this.$scope.form_props.$valid) {
        return;
      }

      const is_new = !this.twitter_account.id;

      const promise = this.twitterAccountData.saveFormModel(this.twitter_account, this.form);

      this.startSpinner('saving');
      return promise.then( () => {
        this.stopSpinner('saving', true).then(() => {
          return this.Growl.success("Saved");
        });

        this.skipDirtyState();
        if (is_new) {
          return this.$state.go('twitter.accounts.gocreate');
        }
      });
    }
  }
  Admin_TwitterAccounts_Ctrl_Edit.initClass();

  return Admin_TwitterAccounts_Ctrl_Edit.EXPORT_CTRL();
});