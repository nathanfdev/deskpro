define(['angular', 'Admin/Main/Ctrl/Base'], (angular, Admin_Ctrl_Base) => {
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
      }).then((res) => {
        let title;
        this.groups = [];

        for (const groupName of Object.keys(res.data.info.list.UserBundle || {})) {
          const tplList = res.data.info.list.UserBundle[groupName];
          title = groupName;
          if (title === 'TOP') { title = 'Layout'; }

          this.groups.push({
            id: `UserBundle:${groupName}`,
            title
          });
        }

        return this.groups.push({
          id:    'DeskPRO:custom_fields',
          title: 'CustomFields'
        });
      });
    }
  }
  Admin_Templates_Ctrl_TemplateGroupList.initClass();

  return Admin_Templates_Ctrl_TemplateGroupList.EXPORT_CTRL();
});
