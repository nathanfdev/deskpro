// TODO: This file was created by bulk-decaffeinate.
// Sanity-check the conversion and remove this comment.
/*
 * decaffeinate suggestions:
 * DS102: Remove unnecessary code created because of implicit returns
 * DS203: Remove `|| {}` from converted for-own loops
 * DS206: Consider reworking classes to avoid initClass
 * Full docs: https://github.com/decaffeinate/decaffeinate/blob/master/docs/suggestions.md
 */
define(['angular', 'Admin/Main/Ctrl/Base'], function(angular, Admin_Ctrl_Base) {
  class Admin_Templates_Ctrl_TemplateGroupList extends Admin_Ctrl_Base {
    static initClass() {
      this.CTRL_ID   = 'Admin_Templates_Ctrl_TemplateGroupList';
      this.CTRL_AS   = 'ListCtrl';
      this.DEPS      = [];
    }

    init() {}


    initialLoad() {
      let promise;
      return promise = this.Api.sendDataGet({
        info: '/templates-info'
      }).then( res => {
        let title;
        this.groups = [];

        for (let groupName of Object.keys(res.data.info.list.UserBundle || {})) {
          const tplList = res.data.info.list.UserBundle[groupName];
          title = groupName;
          if (title === 'TOP') { title = 'Layout'; }

          this.groups.push({
            id: `UserBundle:${groupName}`,
            title
          });
        }

        return this.groups.push({
          id: "DeskPRO:custom_fields",
          title: 'CustomFields'
        });
      });
    }
  }
  Admin_Templates_Ctrl_TemplateGroupList.initClass();

  return Admin_Templates_Ctrl_TemplateGroupList.EXPORT_CTRL();
});