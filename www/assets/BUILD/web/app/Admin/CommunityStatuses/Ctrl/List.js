define(['Admin/Main/Ctrl/Base'], function(Admin_Ctrl_Base) {
  class Admin_CommunityStatuses_Ctrl_List extends Admin_Ctrl_Base {
    static initClass() {
      this.CTRL_ID = 'Admin_CommunityStatuses_Ctrl_List';
      this.CTRL_AS = 'CommunityStatusesList';
      this.DEPS    = ['$rootScope', '$scope', 'CommunityStatusesData', 'em', 'Api', '$state', 'Growl'];
    }

    init() {
      this.$scope.brand_id = this.$stateParams.brandId;
      this.$scope.activeType = 'active';
      this.$scope.closedType = 'closed';

      this.feedback_active_statuses = [];
      this.feedback_closed_statuses = [];

      return this.sortedListOptions = {
        axis:   'y',
        handle: '.drag-handle',
        update: (ev, data) => {
          const $list = data.item.closest('ul');

          const postData = { display_orders: [] };

          let x = 0;
          const { em } = this;

          let status_type = 'active';

          $list.find('li').each(function () {
            x += 10;
            const community_status_id = parseInt($(this).data('id'));

            if (community_status_id) {
              const community_status = em.getById('community_status', community_status_id);

              if (community_status) {
                community_status.display_order = x;
                ({ status_type } = community_status);
              }
            }

            return postData.display_orders.push(community_status_id);
          });

          const promise = this.Api.sendPostJson('/community_statuses/display_order', postData);
          return this.pingElement(`display_orders_${status_type}`);
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
      promises.push(this.CommunityStatusesData.loadList().then((recs) => {
        this.feedback_active_statuses = this.sort(recs.active_statuses.values());
        this.feedback_closed_statuses = this.sort(recs.closed_statuses.values());

        this.addManagedListener(this.CommunityStatusesData.recs.active_statuses, 'changed', () => {
          this.feedback_active_statuses = this.sort(this.CommunityStatusesData.recs.active_statuses.values());
          return this.ngApply();
        });

        return this.addManagedListener(this.CommunityStatusesData.recs.closed_statuses, 'changed', () => {
          this.feedback_closed_statuses = this.sort(this.CommunityStatusesData.recs.closed_statuses.values());
          return this.ngApply();
        });
      })
      );

      return this.$q.all(promises);
    }

    /*
  * Show the delete dlg
  */

    startDelete(community_status) {
      const move_community_statuses_list = this.CommunityStatusesData.getListOfMovables(community_status);

      if (!move_community_statuses_list.length) {
        this.showAlert('@no_delete_last');
        return;
      }

      const inst = this.$modal.open({
        templateUrl: this.getTemplatePath('CommunityStatuses/delete-modal.html'),
        controller:  ['$scope', '$modalInstance', 'move_community_statuses_list', function ($scope, $modalInstance, move_community_statuses_list) {
          $scope.move_community_statuses_list = move_community_statuses_list;
          $scope.selected = {
            move_to_id: move_community_statuses_list[0].id
          };

          $scope.confirm = () => $modalInstance.close($scope.selected.move_to_id);

          return $scope.dismiss = () => $modalInstance.dismiss();
        }
        ],
        resolve: {
          move_community_statuses_list: () => move_community_statuses_list
        }
      });

      return inst.result.then(move_to => this.deleteFeedbackStatus(community_status, move_to));
    }

    /*
    * Actually do the delete
  * @param community_status - feedback status we want to delete
  * @param move_to - to what status feedback should be moved
    */

    deleteFeedbackStatus(community_status, move_to) {
      return this.Api.sendDelete(`/community_statuses/${community_status.id}`, {
        move_to
      }).success(() => {
        this.CommunityStatusesData.remove(community_status.id);
        this.ngApply();

        // if currently viewing the deleted feedback status, then should need to switch state
        if ((this.$state.current.name === 'portal.community_statuses.edit') && (parseInt(this.$state.params.id) === community_status.id)) {
          return this.$state.go('portal.community_statuses');
        }
      });
    }
  }
  Admin_CommunityStatuses_Ctrl_List.initClass();

  return Admin_CommunityStatuses_Ctrl_List.EXPORT_CTRL();
});
