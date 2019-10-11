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
      this.DEPS    = ['Api', 'Growl', 'CommunityForumsData', '$stateParams', '$modal', '$upload'];
    }

    init() {
      this.community_forum = {};
      this.usergroups = [];
      this.custom_fields = [];
      this.selected_usergroups = {};
      this.display_orders = {};

      return this.sortedListOptions = {
        axis:   'y',
        handle: '.drag-handle',
        update: (ev, data) => {
          const $list = data.item.closest('div');
          let x = 0;

          const self = this;

          $list.find('label').each(function () {
            const field_id = parseInt($(this).data('id'));
            if (field_id) {
              self.display_orders[field_id] = x;
            }
            x += 10;
          });

          return this.Api2.sendPostJson(`/community_forums/${this.$stateParams.id}/custom_fields/display_orders`, { display_orders: this.display_orders });
        }
      };
    }

    deleteCustomField(id, forumId) {
      this.DataService.get('CommunityFields').deleteFieldById(id, forumId).then(() => {
        this.Api2.sendGet(`/community_forums/${this.$stateParams.id}/custom_fields/`)
          .then(
            (result) => {
              this.custom_fields = result.data.data;
            });
      });
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
        promises.push(this.Api2.sendGet(`/community_forums/${this.$stateParams.id}/custom_fields/`)
          .then(
            (result) => {
              this.custom_fields = result.data.data;
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

    openUnsplashModal() {
      var event = new CustomEvent('dpLeftDrawer', {detail: {
          module: 'SplashImage',
          width: 0,
          selectImage: this.selectSplashImage.bind(this),
          style: {
            zIndex: 22000
          }
        }});
      window.parent.document.dispatchEvent(event);
    }

    selectSplashImage(image) {
      this.Api2.sendPost(`community_forums/${this.community_forum.id}/splash_image`, { image: JSON.stringify(image) })
        .then(
          (response) => {
            return this.community_forum.custom_splash_image = response.data.urls.thumb;
          },
          response => this.$scope.errors.splash_image = response.data.fields.file.errors[0].message);
    }

    uploadSplashImage(files) {
      return this.$upload
        .upload({ url: `${window.origin}${window.DP_BASE_URL}api/v2/community_forums/${this.community_forum.id}/splash_image_upload`, file: files[0] })
        .then(
          (response) => {
            return this.community_forum.custom_splash_image = response.data.image;
          },
          response => this.$scope.errors.splash_image = response.data.fields.file.errors[0].message);
    }

    deleteSplashImage() {
      this.Api2.sendDelete(`community_forums/${this.community_forum.id}/splash_image`).success(() => this.community_forum.custom_splash_image = null);
    }
  }
  Admin_CommunityForums_Ctrl_Edit.initClass();

  return Admin_CommunityForums_Ctrl_Edit.EXPORT_CTRL();
});
