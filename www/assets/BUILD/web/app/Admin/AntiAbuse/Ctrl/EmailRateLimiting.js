// TODO: This file was created by bulk-decaffeinate.
// Sanity-check the conversion and remove this comment.
/*
 * decaffeinate suggestions:
 * DS102: Remove unnecessary code created because of implicit returns
 * DS206: Consider reworking classes to avoid initClass
 * Full docs: https://github.com/decaffeinate/decaffeinate/blob/master/docs/suggestions.md
 */
define(['Admin/Main/Ctrl/Base'], function(Admin_Ctrl_Base) {
 var Admin_AntiAbuse_Ctrl_EmailRateLimiting = (function() {
   let _url = undefined;
   Admin_AntiAbuse_Ctrl_EmailRateLimiting = class Admin_AntiAbuse_Ctrl_EmailRateLimiting extends Admin_Ctrl_Base {
    static initClass() {
      this.CTRL_ID = 'Admin_AntiAbuse_Ctrl_EmailRateLimiting';
      this.CTRL_AS = 'EmailRateLimiting';
  
      _url = '/email_accounts/settings';
    }

    init() {
     return this.$scope.settings = null;
   }

    initialLoad() {
     return this.Api.sendGet(_url).then(res => {
      return this.$scope.settings = res.data.email_settings;
     });
   }

    save() {
     const postData = {
      settings: this.$scope.settings
     };

     this.startSpinner('saving');
     return this.Api.sendPutJson(_url, postData).success( () => {
      this.stopSpinner('saving');
      return this.Growl.success(this.getRegisteredMessage('saved_settings'));
     }).error( info => {
      this.stopSpinner('saving', true);
      return this.applyErrorResponseToView(info);
     });
   }
  };
   Admin_AntiAbuse_Ctrl_EmailRateLimiting.initClass();
   return Admin_AntiAbuse_Ctrl_EmailRateLimiting;
 })();

 return Admin_AntiAbuse_Ctrl_EmailRateLimiting.EXPORT_CTRL();
});
