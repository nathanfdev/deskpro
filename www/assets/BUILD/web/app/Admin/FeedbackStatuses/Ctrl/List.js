define(['Admin/Main/Ctrl/Base'], (Admin_Ctrl_Base) => {
  class Admin_FeedbackStatuses_Ctrl_List extends Admin_Ctrl_Base {
    static initClass() {
      this.CTRL_ID = 'Admin_FeedbackStatuses_Ctrl_List';
      this.CTRL_AS = 'FeedbackStatusesList';
      this.DEPS    = ['$rootScope', '$scope', 'FeedbackStatusesData', 'em', 'Api', '$state', 'Growl'];
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
            const feedback_status_id = parseInt($(this).data('id'));

            if (feedback_status_id) {
              const feedback_status = em.getById('feedback_status', feedback_status_id);

              if (feedback_status) {
                feedback_status.display_order = x;
                ({ status_type } = feedback_status);
              }
            }

            return postData.display_orders.push(feedback_status_id);
          });

          const promise = this.Api.sendPostJson('/feedback_statuses/display_order', postData);
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
      promises.push(this.FeedbackStatusesData.loadList().then((recs) => {
        this.feedback_active_statuses = this.sort(recs.active_statuses.values());
        this.feedback_closed_statuses = this.sort(recs.closed_statuses.values());

        this.addManagedListener(this.FeedbackStatusesData.recs.active_statuses, 'changed', () => {
          this.feedback_active_statuses = this.sort(this.FeedbackStatusesData.recs.active_statuses.values());
          return this.ngApply();
        });

        return this.addManagedListener(this.FeedbackStatusesData.recs.closed_statuses, 'changed', () => {
          this.feedback_closed_statuses = this.sort(this.FeedbackStatusesData.recs.closed_statuses.values());
          return this.ngApply();
        });
      })
      );

      return this.$q.all(promises);
    }

    /*
  * Show the delete dlg
  */

    startDelete(feedback_status) {
      const move_feedback_statuses_list = this.FeedbackStatusesData.getListOfMovables(feedback_status);

      if (!move_feedback_statuses_list.length) {
        this.showAlert('@no_delete_last');
        return;
      }

      const inst = this.$modal.open({
        templateUrl: this.getTemplatePath('FeedbackStatuses/delete-modal.html'),
        controller:  ['$scope', '$modalInstance', 'move_feedback_statuses_list', function ($scope, $modalInstance, move_feedback_statuses_list) {
          $scope.move_feedback_statuses_list = move_feedback_statuses_list;
          $scope.selected = {
            move_to_id: move_feedback_statuses_list[0].id
          };

          $scope.confirm = () => $modalInstance.close($scope.selected.move_to_id);

          return $scope.dismiss = () => $modalInstance.dismiss();
        }
        ],
        resolve: {
          move_feedback_statuses_list: () => move_feedback_statuses_list
        }
      });

      return inst.result.then(move_to => this.deleteFeedbackStatus(feedback_status, move_to));
    }

    /*
    * Actually do the delete
  * @param feedback_status - feedback status we want to delete
  * @param move_to - to what status feedback should be moved
    */

    deleteFeedbackStatus(feedback_status, move_to) {
      return this.Api.sendDelete(`/feedback_statuses/${feedback_status.id}`, {
        move_to
      }).success(() => {
        this.FeedbackStatusesData.remove(feedback_status.id);
        this.ngApply();

        // if currently viewing the deleted feedback status, then should need to switch state
        if ((this.$state.current.name === 'portal.feedback_statuses.edit') && (parseInt(this.$state.params.id) === feedback_status.id)) {
          return this.$state.go('portal.feedback_statuses');
        }
      });
    }
  }
  Admin_FeedbackStatuses_Ctrl_List.initClass();

  return Admin_FeedbackStatuses_Ctrl_List.EXPORT_CTRL();
});
