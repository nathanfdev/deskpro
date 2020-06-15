define([
  'Admin/CustomFields/Base/Ctrl/Edit',
], (
  Admin_CustomFields_Base_Ctrl_Edit
) => {
  class Admin_CustomFields_Community_Ctrl_Edit extends Admin_CustomFields_Base_Ctrl_Edit {
    static initClass() {
      this.CTRL_ID = 'Admin_CustomFields_Community_Ctrl_Edit';
      this.CTRL_AS = 'EditCtrl';
      this.DEPS    = ['CommunityForumsData'];
    }

    initialLoadExtra() {
      this.community_forums = [];

      const promises = [];
      promises.push(this.CommunityForumsData.loadList().then((recs) => {
        this.community_forums = recs.values();
      }));

      return this.$q.all(promises);
    }

    initialLoad() {
      this.selectedForums = {};

      const promises = super.initialLoad();
      promises.then(() => {
        if (this.field && this.field.forums) {
          this.field.forums.forEach((forum) => {
            this.selectedForums[forum] = true;
          });
        }

        if (!this.$stateParams.id && this.form.is_global) {
          this.community_forums.forEach((forum) => {
            this.selectedForums[forum.id] = true;
          });
        }
      });

      return promises;
    }

    getDataService() {
      return this.DataService.get('CommunityFields');
    }

    getBaseRouteName() {
      return 'portal.community_custom_fields';
    }

    type() {
      return 'community';
    }

    saveForm() {
      this.form.forums = [];
      if (!this.$stateParams.id) {
        this.form.brand = this.$stateParams.brandId;
      }

      Object.keys(this.selectedForums).forEach((forum) => {
        if (this.selectedForums[forum]) {
          this.form.forums.push({ forum });
        }
      });

      super.saveForm();
    }
  }
  Admin_CustomFields_Community_Ctrl_Edit.initClass();

  return Admin_CustomFields_Community_Ctrl_Edit.EXPORT_CTRL();
});
