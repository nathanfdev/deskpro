define([
  'Admin/Main/Ctrl/Base',
  'underscore'
], (
  Admin_Ctrl_Base,
  _
) => {
  class Admin_CommunityChannels_Ctrl_Edit extends Admin_Ctrl_Base {
    static initClass() {
      this.CTRL_ID = 'Admin_CommunityChannels_Ctrl_Edit';
      this.CTRL_AS = 'CommunityChannelsEdit';
      this.DEPS    = ['Api', 'Growl', 'CommunityChannelsData', '$stateParams', '$modal'];
    }

    init() {
      this.community_channel = {};
      this.usergroups = [];
      return this.selected_usergroups = {};
    }


    initialLoad() {
      const promises = [];
      promises.push(this.Api.sendDataGet({ usergroups: '/user_groups' }).then(result => this.usergroups = result.data.usergroups.groups)
      );

      if (this.$stateParams.id) {
        promises.push(this.Api.sendDataGet({
          community_channel: `/community_channels/${this.$stateParams.id}`,
        }).then((result) => {
          this.community_channel = result.data.community_channel.community_channel;
          const ids = _.pluck(this.community_channel.usergroups, 'id');
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
    saveCommunityChannel() {
      let is_new,
        promise;
      this.community_channel.brand = this.$stateParams.brandId;
      this.community_channel.usergroups = [];

      for (const key of Object.keys(this.selected_usergroups || {})) {
        const value = this.selected_usergroups[key];
        if (value) {
          const usergroup = _.findWhere(this.usergroups, { id: parseInt(key) });
          if (usergroup) { this.community_channel.usergroups.push(usergroup.id); }
        }
      }

      if (!this.$scope.form_props.$valid) {
        return;
      }

      this.startSpinner('saving_community_channel');

      if (this.community_channel.id) {
        is_new = false;
        promise = this.Api.sendPostJson(`/community_channels/${this.community_channel.id}`, { community_channel: this.community_channel });
      } else {
        is_new = true;
        promise = this.Api.sendPutJson('/community_channels', { community_channel: this.community_channel });
      }

      promise.success((result) => {
        this.community_channel.id = result.id;
        this.community_channel.brand = result.brand;

        this.stopSpinner('saving_community_channel', true).then(() => this.Growl.success(this.getRegisteredMessage('saved_community_channel')));

        this.CommunityChannelsData.updateModel(this.community_channel);

        this.skipDirtyState();

        if (is_new) {
          return this.$state.go('portal.community_channels.gocreate');
        }
        return this.$state.go('portal.community_channels');
      });
      promise.error((info, code) => {
        this.stopSpinner('saving_community_channel', true);
        return this.applyErrorResponseToView(info);
      });

      return promise;
    }
  }
  Admin_CommunityChannels_Ctrl_Edit.initClass();

  return Admin_CommunityChannels_Ctrl_Edit.EXPORT_CTRL();
});
