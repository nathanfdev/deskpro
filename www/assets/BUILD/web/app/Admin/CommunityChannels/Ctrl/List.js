define(['Admin/Main/Ctrl/Base'], function(Admin_Ctrl_Base) {
  class Admin_CommunityChannels_Ctrl_List extends Admin_Ctrl_Base {
    static initClass() {
      this.CTRL_ID = 'Admin_CommunityChannels_Ctrl_List';
      this.CTRL_AS = 'CommunityChannelsList';
      this.DEPS    = ['$rootScope', '$scope', 'CommunityChannelsData', 'em', 'Api', '$state', 'Growl'];
    }

    init() {
      this.$scope.brand_id = this.$stateParams.brandId;
      this.community_channels = [];
      this.brands = [];

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
            const community_channel_id = parseInt($(this).data('id'));

            if (community_channel_id) {
              const community_channel = em.getById('community_channel', community_channel_id);

              if (community_channel) {
                community_channel.display_order = x;
              }
            }

            return postData.display_orders.push(community_channel_id);
          });

          const promise = this.Api.sendPostJson('/community_channels/display_order', postData);
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
      promises.push(this.CommunityChannelsData.loadList().then((recs) => {
        this.community_channels = this.sort(recs.values());

        return this.addManagedListener(this.CommunityChannelsData.recs, 'changed', () => {
          this.community_channels = this.sort(this.CommunityChannelsData.recs.values());
          return this.ngApply();
        });
      })
      );

      return this.$q.all(promises);
    }

    /*
  * Show the delete dlg
  */

    startDelete(community_channel) {
      const move_community_channels_list = this.CommunityChannelsData.getListOfMovables(community_channel);

      if (!move_community_channels_list.length) {
        this.showAlert('@no_delete_last');
        return;
      }

      const inst = this.$modal.open({
        templateUrl: this.getTemplatePath('CommunityChannels/delete-modal.html'),
        controller:  ['$scope', '$modalInstance', 'move_community_channels_list', function ($scope, $modalInstance, move_community_channels_list) {
          $scope.move_community_channels_list = move_community_channels_list;
          $scope.selected = {
            move_to_id: move_community_channels_list[0].id
          };

          $scope.confirm = () => $modalInstance.close($scope.selected.move_to_id);

          return $scope.dismiss = () => $modalInstance.dismiss();
        }
        ],
        resolve: {
          move_community_channels_list: () => move_community_channels_list
        }
      });

      return inst.result.then(move_to => this.deleteCommunityChannel(community_channel, move_to));
    }

    /*
    * Actually do the delete
  * @param community_channel - community channel we want to delete
  * @param move_to - to what type community topic should be moved
    */

    deleteCommunityChannel(community_channel, move_to) {
      return this.Api.sendDelete(`/community_channels/${community_channel.id}`, {
        move_to
      }).success(() => {
        this.CommunityChannelsData.remove(community_channel.id);
        this.ngApply();

        // if currently viewing the deleted community topic, then should need to switch state
        if ((this.$state.current.name === 'portal.community_channels.edit') && (parseInt(this.$state.params.id) === community_channel.id)) {
          return this.$state.go('portal.community_channels');
        }
      });
    }
  }
  Admin_CommunityChannels_Ctrl_List.initClass();

  return Admin_CommunityChannels_Ctrl_List.EXPORT_CTRL();
});
