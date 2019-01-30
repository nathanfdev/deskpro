// TODO: This file was created by bulk-decaffeinate.
// Sanity-check the conversion and remove this comment.
/*
 * decaffeinate suggestions:
 * DS102: Remove unnecessary code created because of implicit returns
 * DS206: Consider reworking classes to avoid initClass
 * Full docs: https://github.com/decaffeinate/decaffeinate/blob/master/docs/suggestions.md
 */
define(['Admin/Main/Ctrl/Base'], function(Admin_Ctrl_Base) {
  class Admin_Cloud_License_Ctrl_License extends Admin_Ctrl_Base {
    static initClass() {
      this.CTRL_ID   = 'Admin_Cloud_License_Ctrl_License';
      this.CTRL_AS   = 'Ctrl';
      this.DEPS      = ['$window'];
    }

    init() {
      return this.$scope.iframe_loading = true;
    }

    initialLoad() {
      this.Api.sendGet('/dp_license/cloud/billing-login-token').then( result => {
        this.$scope.iframe_loading = false;
        return this.$scope.iframe_code    = `<iframe src="${result.data.ma_url}" frameborder="0"></iframe>`;
      });
      return null;
    }
  }
  Admin_Cloud_License_Ctrl_License.initClass();

  return Admin_Cloud_License_Ctrl_License.EXPORT_CTRL();
});