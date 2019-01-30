/*
 * decaffeinate suggestions:
 * DS102: Remove unnecessary code created because of implicit returns
 * DS206: Consider reworking classes to avoid initClass
 * Full docs: https://github.com/decaffeinate/decaffeinate/blob/master/docs/suggestions.md
 */
define(['Admin/Main/Ctrl/Base', 'DeskPRO/Util/Arrays'], function(Admin_Ctrl_Base, Arrays) {
  class Admin_ApiKeys_Ctrl_Logs extends Admin_Ctrl_Base {
    static initClass() {
      this.CTRL_ID = 'Admin_ApiKeys_Ctrl_Logs';
      this.CTRL_AS = 'LogsCtrl';
    }

    init() {
      this.isCloud = window.DP_IS_CLOUD;
      this.service = this.DataService.get('ApiLogs');
      this.list = [];
      this.filtration = this.service.getFiltration();
      this.methods = ['GET', 'POST', 'PUT', 'DELETE']; // I know, I know, but we use only listed
      return this.options = {
        enabled: false
      };
    }

    /*
     * Loads the list
     */
    initialLoad() {
      this.service.getOptions().then(data => {
        return this.options = data;
      });

      return this.service.loadList(null, {page: 1}).then( data => {
        this.list = data;
        this.pagination = this.service.getPagination();
        return this.initializeScopeWatching();
      });
    }

    toggleLogs() {
      return this.options.enabled = !this.options.enabled;
    }

    updateOptions() {
      return this.service.updateOptions(this.options).then(
        () => {
          this.Growl.success('Successfully saved your new settings');
          return this.service.getOptions().then(data => {
            return this.options = data;
          });
        },
        () => {
          return this.Growl.error('Error while saving');
      });
    }

    initializeScopeWatching() {

      return this.$scope.$watch('LogsCtrl.pagination', (newVal, oldVal) => {

        const old_page = parseInt(oldVal.page);
        const new_page = parseInt(newVal.page);

        if ((old_page === new_page) || isNaN(new_page)) {
          return undefined;
        }

        return this.service.refreshList().then( data => {
          this.list = data;
          return this.pagination = this.service.getPagination();
        });
      }
      , true);
    }

    refreshList() {
      this.startSpinner('loading');
      return this.service.refreshList().then( () => this.stopSpinner('loading'));
    }
  }
  Admin_ApiKeys_Ctrl_Logs.initClass();

  return Admin_ApiKeys_Ctrl_Logs.EXPORT_CTRL();
});