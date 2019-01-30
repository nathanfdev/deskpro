// TODO: This file was created by bulk-decaffeinate.
// Sanity-check the conversion and remove this comment.
/*
 * decaffeinate suggestions:
 * DS102: Remove unnecessary code created because of implicit returns
 * DS206: Consider reworking classes to avoid initClass
 * Full docs: https://github.com/decaffeinate/decaffeinate/blob/master/docs/suggestions.md
 */
define(['Admin/Main/Ctrl/Base'], function(Admin_Ctrl_Base) {
  class Admin_Banning_Ctrl_List extends Admin_Ctrl_Base {
    static initClass() {
      this.CTRL_ID = 'Admin_Banning_Ctrl_List';
      this.CTRL_AS = 'ListCtrl';
      this.DEPS      = ['Api', '$http', 'Growl'];
    }

    init() {
      this.banData = this.DataService.get('Bans');
      this.$scope.fileUploadOptions = {url: this.$http.formatApiUrl('/banning/import_emails') };
      this.$scope.exportUrl = this.$http.formatApiUrl('/banning/export_emails');

      this.$scope.$on('fileuploaddone', (e, data) => {
        this.Growl.success('Import finished successfully');
        return this.goFirstEmailBanPage();
      });

      return this.$scope.$on('fileuploadfail', (e, data) => {
        this.Growl.error('Import failed');
        return this.goFirstEmailBanPage();
      });
    }

    /*
     * Loads the list
     */

    initialLoad() {

      const promise = this.banData.loadList().then( list => {

        this.list = list;
        this.pagination = this.banData.getPagination();
        this.search_phrase = this.banData.getSearchPhrase();

        return this.initializeScopeWatching();
      });

      return promise;
    }

    /*
     * Here we watching scope 'page' variable in order to load new page of results
     * Reason - 'ng-change' is not working for ui-select2
     */

    initializeScopeWatching() {

      return this.$scope.$watch('ListCtrl.pagination', (newVal, oldVal) => {

        const ip_bans_page_old = parseInt(oldVal.ip_bans.page);
        const ip_bans_page_new = parseInt(newVal.ip_bans.page);
        const email_bans_page_old = parseInt(newVal.email_bans.page);
        const email_bans_page_new = parseInt(oldVal.email_bans.page);

        if ((ip_bans_page_old === ip_bans_page_new) && (email_bans_page_old === email_bans_page_new)) {
          return undefined;
        }

        if (isNaN(ip_bans_page_new) && isNaN(email_bans_page_new)) {
          return undefined;
        }

        return this.reloadList(ip_bans_page_old !== ip_bans_page_new, email_bans_page_old !== email_bans_page_new);
      }

      , true);
    }

    /*
  * Reloads the lists with bans taking into current page & search phrase
  *
    * @param {Boolean} reload_ip - whether we want to reload list with ip bans
  * @param {Boolean} reload_email - whether we want to reload list with email bans
    */

    reloadList(reload_ip, reload_email) {

      if (reload_ip) { this.startSpinner('paginating_ip_bans'); }
      if (reload_email) { this.startSpinner('paginating_email_bans'); }

      return this.banData.refreshList().then(list => {
        if (reload_ip) { this.stopSpinner('paginating_ip_bans', true); }
        if (reload_email) { this.stopSpinner('paginating_email_bans', true); }

        this.list = list;
        return this.pagination = this.banData.getPagination();
      });
    }

    /*
  *
  */

    goNextIpBanPage() {

      return this.pagination.ip_bans.page++;
    }

    /*
     *
     */

    goPrevIpBanPage() {

      return this.pagination.ip_bans.page--;
    }

    /*
     *
     */

    goFirstIpBanPage() {

      return this.pagination.ip_bans.page = 0;
    }

    /*
     *
     */

    goNextEmailBanPage() {

      return this.pagination.email_bans.page++;
    }

    /*
     *
     */

    goPrevEmailBanPage() {

      return this.pagination.email_bans.page--;
    }

    /*
     *
     */

    goFirstEmailBanPage() {

      return this.pagination.email_bans.page = 0;
    }

    /*
     * Show the delete dlg
     */

    startDelete(for_ban_id) {

      const key = this.banData.findListModelById(for_ban_id);

      const inst = this.$modal.open({
        templateUrl: this.getTemplatePath('Banning/delete-modal.html'),
        controller: ['$scope', '$modalInstance', function($scope, $modalInstance) {
          $scope.confirm = () => $modalInstance.close();

          return $scope.dismiss = () => $modalInstance.dismiss();
        }
        ]
      });

      return inst.result.then(() => {
        return this.deleteBan(key);
      });
    }

    /*
     * Actually do the delete
     */

    deleteBan(for_ban) {

      let key, prop;
      if (for_ban.banned_ip) { key = 'ip'; prop = 'id'; }
      if (for_ban.banned_email) { key = 'email'; prop = 'banned_email'; }

      return this.banData.deleteBanById(for_ban[prop]).success( () => {

        if ((this.$state.current.name === (`crm.banning.edit_${key}`)) && (this.$state.params.ban === for_ban[prop])) {
          return this.$state.go('crm.banning');
        }

      }).error((info, code) => {
        return this.applyErrorResponseToView(info);
      });
    }

    /*
     * Show the delete dlg
     */

    deleteList(list) {

      const inst = this.$modal.open({
        templateUrl: this.getTemplatePath('Banning/delete-modal.html'),
        controller: ['$scope', '$modalInstance', function($scope, $modalInstance) {
          $scope.multiple = true;
          $scope.confirm = () => $modalInstance.close();

          return $scope.dismiss = () => $modalInstance.dismiss();
        }
        ]
      });

      return inst.result.then(() => {
        if (list === this.list.email_bans) {
          return this.banData.deleteBanByType('email').then( () => {
            this.reloadList(false, true);
            return this.$state.go('crm.banning');
          });
        } else {
          return this.banData.deleteBanByType('ip').then( () => {
            this.reloadList(true);
            return this.$state.go('crm.banning');
          });
        }
      });
    }
  }
  Admin_Banning_Ctrl_List.initClass();

  return Admin_Banning_Ctrl_List.EXPORT_CTRL();
});