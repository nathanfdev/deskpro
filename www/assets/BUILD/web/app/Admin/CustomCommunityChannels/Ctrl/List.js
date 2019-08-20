define(['Admin/Main/Ctrl/Base'], function(Admin_Ctrl_Base) {
  class Admin_CustomCommunityChannels_Ctrl_List extends Admin_Ctrl_Base {
    static initClass() {
      this.CTRL_ID = 'Admin_CustomCommunityChannels_Ctrl_List';
      this.CTRL_AS = 'CustomCommunityChannelsList';
      this.DEPS    = ['$rootScope', '$scope', 'CustomCommunityChannelsData', 'em', 'Api', '$state', 'Growl'];
    }

    init() {
      this.$scope.brand_id = this.$stateParams.brandId;
      this.custom_community_channels = [];
      this.parent_data = [];
      this.child_data = {};

      return this.sortedListOptions = {

        axis:   'y',
        handle: '.drag-handle',
        update: (ev, data) => {
          const $list = data.item.closest('ul');

          const postData = { display_orders: [] };

          let x = 0;
          const { em } = this;

          $list.find('li').each(function () {
            x += 10;
            const custom_community_channel_id = parseInt($(this).data('id'));

            if (custom_community_channel_id) {
              const custom_community_channel = em.getById('custom_community_channel', custom_community_channel_id);

              if (custom_community_channel) {
                custom_community_channel.display_order = x;
              }
            }

            return postData.display_orders.push(custom_community_channel_id);
          });

          this.Api.sendPostJson('/custom_community_channels/display_order', postData);
          return this.pingElement('display_orders');
        }
      };
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

    initialLoad() {
      const promises = [];
      promises.push(this.CustomCommunityChannelsData.loadList().then((recs) => {
        this.initHierarchyData(this.sort(recs.values()));

        return this.addManagedListener(this.CustomCommunityChannelsData.recs, 'changed', () => {
          this.initHierarchyData(this.sort(this.CustomCommunityChannelsData.recs.values()));
          return this.ngApply();
        });
      })
      );

      return this.$q.all(promises);
    }

    initHierarchyData(custom_community_channels) {
      this.custom_community_channels = custom_community_channels;
      this.parent_data = [];
      this.child_data = {};

      return (() => {
        const result = [];
        for (const category of Array.from(custom_community_channels)) {
          if (parseInt(category.parent_id, 10)) {
            if (!this.child_data[category.parent_id]) {
              this.child_data[category.parent_id] = [];
            }

            result.push(this.child_data[category.parent_id].push(category));
          } else {
            result.push(this.parent_data.push(category));
          }
        }
        return result;
      })();
    }
  }
  Admin_CustomCommunityChannels_Ctrl_List.initClass();


  return Admin_CustomCommunityChannels_Ctrl_List.EXPORT_CTRL();
});
