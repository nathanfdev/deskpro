define([
  'Admin/Main/Ctrl/Base',
  'underscore'
], (
  Admin_Ctrl_Base,
  _
) => {
  class Admin_CommunityForums_Ctrl_Edit extends Admin_Ctrl_Base {
    static initClass() {
      this.CTRL_ID = 'Admin_CommunityForums_Ctrl_Edit';
      this.CTRL_AS = 'CommunityForumsEdit';
      this.DEPS    = ['Api', 'Growl', 'CommunityForumsData', '$stateParams', '$modal'];
    }

    init() {
      this.community_forum = {};
      this.usergroups = [];
      return this.selected_usergroups = {};
    }


    initialLoad() {
      const promises = [];
      promises.push(this.Api.sendDataGet({ usergroups: '/user_groups' }).then(result => this.usergroups = result.data.usergroups.groups)
      );

      if (this.$stateParams.id) {
        promises.push(this.Api.sendDataGet({
          community_forum: `/community_forums/${this.$stateParams.id}`,
        }).then((result) => {
          this.community_forum = result.data.community_forum.community_forum;
          const ids = _.pluck(this.community_forum.usergroups, 'id');
          return Array.from(ids).map(id =>
            (this.selected_usergroups[id] = true));
        })
        );
      }

      return this.$q.all(promises);
    }


    /*
      * Saves the current form
      *
      * @return {promise}
    */
    saveCommunityForum() {
      let is_new,
        promise;
      this.community_forum.brand = this.$stateParams.brandId;
      this.community_forum.usergroups = [];

      for (const key of Object.keys(this.selected_usergroups || {})) {
        const value = this.selected_usergroups[key];
        if (value) {
          const usergroup = _.findWhere(this.usergroups, { id: parseInt(key) });
          if (usergroup) { this.community_forum.usergroups.push(usergroup.id); }
        }
      }

      if (!this.$scope.form_props.$valid) {
        return;
      }

      this.startSpinner('saving_community_forum');

      if (this.community_forum.id) {
        is_new = false;
        promise = this.Api.sendPostJson(`/community_forums/${this.community_forum.id}`, { community_forum: this.community_forum });
      } else {
        is_new = true;
        promise = this.Api.sendPutJson('/community_forums', { community_forum: this.community_forum });
      }

      promise.success((result) => {
        this.community_forum.id = result.id;
        this.community_forum.brand = result.brand;

        this.stopSpinner('saving_community_forum', true).then(() => this.Growl.success(this.getRegisteredMessage('saved_community_forum')));

        this.CommunityForumsData.updateModel(this.community_forum);

        this.skipDirtyState();

        if (is_new) {
          return this.$state.go('portal.community_forums.gocreate');
        }
        return this.$state.go('portal.community_forums');
      });
      promise.error((info, code) => {
        this.stopSpinner('saving_community_forum', true);
        return this.applyErrorResponseToView(info);
      });

      return promise;
    }
  }
  Admin_CommunityForums_Ctrl_Edit.initClass();

  return Admin_CommunityForums_Ctrl_Edit.EXPORT_CTRL();
});
