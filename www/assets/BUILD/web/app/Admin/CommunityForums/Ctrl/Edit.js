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
      this.all_statuses = {};
      this.community_statuses = { active: [], closed: [] };
      this.selectedStatuses = [];
      this.selectedCustomFields = [];

      this.$scope.picker = false;
      this.$scope.colors = [
        '#e11d21', '#eb6420', '#fbca04', '#009800', '#006b75', '#207de5', '#0052cc', '#5319e7',
        '#f7c6c7', '#fad8c7', '#fef2c0', '#bfe5bf', '#bfdadc', '#c7def8', '#bfd4f2', '#d4c5f9'
      ];

      this.sortedStatusesOptions = {
        axis:   'y',
        handle: '.drag-handle',
        update: (ev, data) => {
          const $list = data.item.closest('div');
          let x = 0;

          const self = this;

          $list.find('label').each(function () {
            const statusId = parseInt($(this).data('id'), 10);
            if (statusId) {
              const status = self.all_statuses[statusId];
              if (status) {
                status.display_order = x;
              }
            }
            x += 10;
          });
        }
      };

      this.sortedCustomFieldsOptions = {
        axis:   'y',
        handle: '.drag-handle',
        update: (ev, data) => {
          const $list = data.item.closest('div');
          let x = 0;

          const self = this;

          $list.find('label').each(function () {
            const fieldId = parseInt($(this).data('id'), 10);
            if (fieldId) {
              const customField = self.custom_fields.filter(field => parseInt(field.id, 10) === fieldId)[0];
              if (customField) {
                customField.display_order = x;
              }
            }
            x += 10;
          });
        }
      };
    }

    initialLoad() {
      const promises = [];
      promises.push(this.Api.sendDataGet({ usergroups: '/user_groups' }).then((result) => {
        this.usergroups = result.data.usergroups.groups;
      }));

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
            icon.urn      = this.community_forum.icon_property.urn;
            icon.style    = this.community_forum.icon_property.style;
            icon.color    = this.community_forum.icon_property.color;
            icon.imageUrl = this.community_forum.icon_property.url;
          }

          if (iconPicker.get(0)) {
            window.AdminBundle.renderIconPicker(iconPicker, icon);
          }

          Array.from(ids).map(id => (this.selected_usergroups[id] = true));
          this.community_forum.topic_fields.forEach((customField) => {
            this.selectedCustomFields[customField.field] = true;
          });
          this.community_forum.topic_statuses.forEach((status) => {
            this.selectedStatuses[status.status] = true;
          });
        }));
      } else if (iconPicker.get(0)) {
        window.AdminBundle.renderIconPicker(iconPicker, icon);
      }

      promises.push(this.DataService.get('CommunityFields').loadList().then((recs) => {
        this.custom_fields = recs;
        if (!this.$stateParams.id) {
          this.custom_fields.forEach((customField) => {
            if (customField.is_global) {
              this.selectedCustomFields[customField.id] = true;
            }
          });
        }
      }));

      promises.push(this.CommunityStatusesData.loadList().then((recs) => {
        recs.active_statuses.values().forEach((status) => {
          this.all_statuses[status.id] = status;
          this.community_statuses.active.push(status);
        });
        recs.closed_statuses.values().forEach((status) => {
          this.all_statuses[status.id] = status;
          this.community_statuses.closed.push(status);
        });
      }));

      const allPromises = this.$q.all(promises);
      allPromises.then(() => {
        this.community_forum.topic_fields.forEach((customField) => {
          const globalCustomField = this.custom_fields.filter(field => parseInt(field.id, 10) === parseInt(customField.field, 10))[0];
          if (globalCustomField) {
            globalCustomField.display_order = customField.display_order;
          }
        });

        this.community_forum.topic_statuses.forEach((status) => {
          const globalStatus = this.all_statuses[status.status];
          if (globalStatus) {
            globalStatus.display_order = status.display_order;
          }
        });
      });

      return allPromises;
    }

    /*
      * Saves the current form
      *
      * @return {promise}
    */
    saveCommunityForum() {
      let is_new;
      let promise;

      this.community_forum.brand = this.$stateParams.brandId;
      this.community_forum.usergroups = [];
      this.community_forum.topic_statuses = [];
      this.community_forum.topic_fields = [];

      for (const ukey of Object.keys(this.selected_usergroups || {})) {
        const value = this.selected_usergroups[ukey];
        if (value) {
          const usergroup = _.findWhere(this.usergroups, { id: parseInt(ukey, 10) });
          if (usergroup) { this.community_forum.usergroups.push(usergroup.id); }
        }
      }

      for (const askey of Object.keys(this.selectedStatuses || {})) {
        const value = this.selectedStatuses[askey];
        if (value === true) {
          this.community_forum.topic_statuses.push({
            status:        this.all_statuses[askey].id,
            display_order: this.all_statuses[askey].display_order
          });
        }
      }

      for (const fskey of Object.keys(this.selectedCustomFields || {})) {
        const value = this.selectedCustomFields[fskey];
        if (value === true) {
          const customField = this.custom_fields.filter(field => parseInt(field.id, 10) === parseInt(fskey, 10))[0];
          if (customField) {
            this.community_forum.topic_fields.push({
              field:         customField.id,
              display_order: customField.display_order
            });
          }
        }
      }

      if (!this.$scope.form_props.$valid) {
        return;
      }

      const iconPicker = $('#community_forum_icon_picker');
      const icon = {
        urn:     iconPicker.find('input[name="icon[urn]"]').val(),
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
      const event = new CustomEvent('dpLeftDrawer', {
        detail: {
          module:      'SplashImage',
          width:       0,
          selectImage: this.selectSplashImage.bind(this),
          style:       {
            zIndex: 22000
          }
        }
      });

      window.parent.document.dispatchEvent(event);
    }

    selectSplashImage(image) {
      this.Api2.sendPost(`community_forums/${this.community_forum.id}/splash_image`, { image: JSON.stringify(image) })
        .then(
          (response) => {
            this.community_forum.custom_splash_image = response.data.urls.thumb;
          },
          (response) => {
            this.$scope.errors.splash_image = response.data.fields.file.errors[0].message;
          });
    }

    uploadSplashImage(files) {
      return this.$upload
        .upload({ url: `${window.origin}${window.DP_BASE_URL}api/v2/community_forums/${this.community_forum.id}/splash_image_upload`, file: files[0] })
        .then(
          (response) => {
            this.community_forum.custom_splash_image = response.data.image;
          },
          (response) => {
            this.$scope.errors.splash_image = response.data.fields.file.errors[0].message;
          }
        );
    }

    deleteSplashImage() {
      this.Api2.sendDelete(`community_forums/${this.community_forum.id}/splash_image`).success(() => this.community_forum.custom_splash_image = null);
    }
  }
  Admin_CommunityForums_Ctrl_Edit.initClass();

  return Admin_CommunityForums_Ctrl_Edit.EXPORT_CTRL();
});
