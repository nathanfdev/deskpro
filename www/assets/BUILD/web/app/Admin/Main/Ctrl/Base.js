/*
 * decaffeinate suggestions:
 * DS102: Remove unnecessary code created because of implicit returns
 * DS206: Consider reworking classes to avoid initClass
 * Full docs: https://github.com/decaffeinate/decaffeinate/blob/master/docs/suggestions.md
 */
define([
  'DeskPRO/Main/Ctrl/Base'
], function(
  DeskPRO_Main_Ctrl_Base
) {
  let Admin_Ctrl_Base;
  return Admin_Ctrl_Base = (function() {
    Admin_Ctrl_Base = class Admin_Ctrl_Base extends DeskPRO_Main_Ctrl_Base {
      static initClass() {
        this.CTRL_AS   = null;
        this.CTRL_ID   = 'Admin_Main_Ctrl_Base';
        this.DEPS      = [];
      }



      /**
      * Get the URL to the template
      *
      * @return {String}
      */
      getTemplatePath(path) {
        return DP_BASE_ADMIN_URL+'/load-view/' + path;
      }
    };
    Admin_Ctrl_Base.initClass();
    return Admin_Ctrl_Base;
  })();
});