define(['Admin/Main/Ctrl/Base'], function(Admin_Ctrl_Base) {
  class Admin_CommunityForums_Ctrl_List extends Admin_Ctrl_Base {
    static initClass() {
      this.CTRL_ID = 'Admin_CommunityForums_Ctrl_List';
      this.CTRL_AS = 'CommunityForumsList';
      this.DEPS    = ['$rootScope', '$scope', 'CommunityForumsData', 'em', 'Api', '$state', 'Growl'];
    }

    init() {
      this.$scope.brand_id = this.$stateParams.brandId;
      this.community_forums = [];
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
            const community_forum_id = parseInt($(this).data('id'));

            if (community_forum_id) {
              const community_forum = em.getById('community_forum', community_forum_id);

              if (community_forum) {
                community_forum.display_order = x;
              }
            }

            return postData.display_orders.push(community_forum_id);
          });

          const promise = this.Api.sendPostJson('/community_forums/display_order', postData);
          return this.pingElement('display_orders');
        }
      };
    }

    sort(values) {
      return (values || [])
        .filter(a => a.brand === parseInt(this.$scope.brand_id, 10))
        .sort((a, b) => {
          const orderA = parseInt(a.display_order);
          const orderB = parseInt(b.display_order);
          if (orderA < orderB) { return -1; }
          if (orderA > orderB) { return 1; }
          return 0;
        }
      );
    }

    initialLoad() {
      const promises = [];
      promises.push(this.CommunityForumsData.loadList().then((recs) => {
        this.community_forums = this.sort(recs.values());

        return this.addManagedListener(this.CommunityForumsData.recs, 'changed', () => {
          this.community_forums = this.sort(this.CommunityForumsData.recs.values());
          return this.ngApply();
        });
      })
      );

      return this.$q.all(promises);
    }

    /*
  * Show the delete dlg
  */

    startDelete(community_forum) {
      const move_community_forums_list = this.CommunityForumsData.getListOfMovables(community_forum);

      if (!move_community_forums_list.length) {
        this.showAlert('@no_delete_last');
        return;
      }

      const inst = this.$modal.open({
        templateUrl: this.getTemplatePath('CommunityForums/delete-modal.html'),
        controller:  ['$scope', '$modalInstance', 'move_community_forums_list', function ($scope, $modalInstance, move_community_forums_list) {
          $scope.move_community_forums_list = move_community_forums_list;
          $scope.selected = {
            move_to_id: move_community_forums_list[0].id
          };

          $scope.confirm = () => $modalInstance.close($scope.selected.move_to_id);

          return $scope.dismiss = () => $modalInstance.dismiss();
        }
        ],
        resolve: {
          move_community_forums_list: () => move_community_forums_list
        }
      });

      return inst.result.then(move_to => this.deleteCommunityForum(community_forum, move_to));
    }

    /*
    * Actually do the delete
  * @param community_forum - community forum we want to delete
  * @param move_to - to what type community topic should be moved
    */

    deleteCommunityForum(community_forum, move_to) {
      return this.Api.sendDelete(`/community_forums/${community_forum.id}`, {
        move_to
      }).success(() => {
        this.CommunityForumsData.remove(community_forum.id);
        this.ngApply();

        // if currently viewing the deleted community topic, then should need to switch state
        if ((this.$state.current.name === 'portal.community_forums.edit') && (parseInt(this.$state.params.id) === community_forum.id)) {
          return this.$state.go('portal.community_forums');
        }
      });
    }
  }
  Admin_CommunityForums_Ctrl_List.initClass();

  return Admin_CommunityForums_Ctrl_List.EXPORT_CTRL();
});
