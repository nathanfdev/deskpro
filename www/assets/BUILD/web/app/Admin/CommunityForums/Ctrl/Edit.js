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
      this.DEPS    = ['Api', 'Growl', 'CommunityForumsData', 'CommunityStatusesData', '$stateParams', '$modal', '$upload'];
    }

    init() {
      this.community_forum = {};
      this.usergroups = [];
      this.custom_fields = [];
      this.selected_usergroups = {};
      this.display_orders = {};
      this.status_display_orders = {active: {}, closed: {}};
      this.all_statuses = {};
      this.junctionStatuses = {};

      this.community_statuses = {active: [], closed: []};
      this.community_selected_statuses = {active: [], closed: []};


      this.sortedStatusesOptions = {
        axis:   'y',
        handle: '.drag-handle',
        update: (ev, data) => {
          const $list = data.item.closest('div');
          const listType = $list.data('type');
          let x = 0;

          const self = this;

          $list.find('label').each(function () {
            const status_id = parseInt($(this).data('id'));
            if (status_id) {
              self.status_display_orders[listType][status_id] = x;
            }
            x += 10;
          });

          return this.Api2.sendPostJson(`/community_forums/${this.$stateParams.id}/statuses/display_orders`, { display_orders: {...self.status_display_orders['active'], ...self.status_display_orders['closed'] }});
        }
      };

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
      const self = this;
      promises.push(this.Api.sendDataGet({ usergroups: '/user_groups' }).then(result => this.usergroups = result.data.usergroups.groups));

      const icon = {
        urn: '/'
      };
      const iconPicker = $('#community_forum_icon_picker');
      if (this.$stateParams.id) {
        promises.push(this.Api.sendDataGet({
          community_forum: `/community_forums/${this.$stateParams.id}`,
        }).then((result) => {
          this.community_forum = result.data.community_forum.community_forum;
          const ids = _.pluck(this.community_forum.usergroups, 'id');

          if (this.community_forum.icon_property) {
            icon.urn   = this.community_forum.icon_property.urn;
            icon.style = this.community_forum.icon_property.style;
            icon.color = this.community_forum.icon_property.color;
          }

          window.AdminBundle.renderIconPicker(iconPicker, icon);

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
      } else {
        window.AdminBundle.renderIconPicker(iconPicker, icon);
      }


      promises.push(this.CommunityStatusesData.loadList().then((recs) => {
          [].concat(recs.active_statuses.values(), recs.closed_statuses.values()).forEach(status => {
            this.all_statuses[status.id] = status;
          });
          this.community_statuses['active'] = _.indexBy(this.sort(recs.active_statuses.values()), 'id');
          this.community_statuses['closed'] = _.indexBy(this.sort(recs.closed_statuses.values()), 'id');
        })
      );

      if (this.$stateParams.id) {
        promises.push(this.CommunityStatusesData.loadPerForumList(this.$stateParams.id).then((recs) => {
            this.junctionStatuses = recs;
          })
        );
      }

      const res = this.$q.all(promises);

      const deferred = this.$q.defer();

      res.then(() => {
        Array.from(self.all_statuses).forEach((status) => {
          self.community_statuses[status.status_type].display_order += 1000;
        });
        Array.from(self.junctionStatuses).forEach((status) => {
          let type = '';
          if(self.all_statuses[status.status]) {
            type = self.all_statuses[status.status].status_type;
            self.community_statuses[type][status.status].display_order = status.display_order;
          }
          self.community_selected_statuses[type][status.status] = true;
        });
        deferred.resolve();
      });

      return deferred.promise;
    }

    sort(values) {
      return (values || []).sort((a, b) => {
        const orderA = parseInt(a.display_order);
        const orderB = parseInt(b.display_order);
        if (orderA < orderB) { return -1; }
        if (orderA > orderB) { return 1; }
        return 0;
      });
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
      this.community_forum.topic_statuses = [];

      for (const ukey of Object.keys(this.selected_usergroups || {})) {
        const value = this.selected_usergroups[ukey];
        if (value) {
          const usergroup = _.findWhere(this.usergroups, { id: parseInt(ukey, 10) });
          if (usergroup) { this.community_forum.usergroups.push(usergroup.id); }
        }
      }

      for (const askey of Object.keys(this.community_selected_statuses['active'] || {})) {
        const value = this.community_selected_statuses['active'][askey];
        if (value === true) {
          this.community_forum.topic_statuses.push(
            {
              status: this.community_statuses['active'][askey].id,
              display_order: this.community_statuses['active'][askey].display_order
            }
          );
        }
      }

      for (const cskey of Object.keys(this.community_selected_statuses['closed'] || {})) {
        const value = this.community_selected_statuses['closed'][cskey];
        if (value === true) {
          this.community_forum.topic_statuses.push(
            {
              status: this.community_statuses['closed'][cskey].id,
              display_order: this.community_statuses['closed'][cskey].display_order
            }
          );
        }
      }

      if (!this.$scope.form_props.$valid) {
        return;
      }

      const iconPicker = $('#community_forum_icon_picker');
      const icon = {
        urn: iconPicker.find('input[name="icon[urn]"]').val(),
        options: {
          color: iconPicker.find('input[name="icon[color]"]').val(),
          style: iconPicker.find('input[name="icon[style]"]').val()
        }
      };
      if (icon.urn) {
        this.community_forum.icon_property = icon;
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
