define(['Admin/Main/Ctrl/Base'], (Admin_Ctrl_Base) => {
  class Admin_FeedbackTypes_Ctrl_List extends Admin_Ctrl_Base {
    static initClass() {
      this.CTRL_ID = 'Admin_FeedbackTypes_Ctrl_List';
      this.CTRL_AS = 'FeedbackTypesList';
      this.DEPS    = ['$rootScope', '$scope', 'FeedbackTypesData', 'em', 'Api', '$state', 'Growl'];
    }

    init() {
      this.$scope.brand_id = this.$stateParams.brandId;
      this.feedback_types = [];
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
            const feedback_type_id = parseInt($(this).data('id'));

            if (feedback_type_id) {
              const feedback_type = em.getById('feedback_type', feedback_type_id);

              if (feedback_type) {
                feedback_type.display_order = x;
              }
            }

            return postData.display_orders.push(feedback_type_id);
          });

          const promise = this.Api.sendPostJson('/feedback_types/display_order', postData);
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
      promises.push(this.FeedbackTypesData.loadList().then((recs) => {
        this.feedback_types = this.sort(recs.values());

        return this.addManagedListener(this.FeedbackTypesData.recs, 'changed', () => {
          this.feedback_types = this.sort(this.FeedbackTypesData.recs.values());
          return this.ngApply();
        });
      })
      );

      return this.$q.all(promises);
    }

    /*
  * Show the delete dlg
  */

    startDelete(feedback_type) {
      const move_feedback_types_list = this.FeedbackTypesData.getListOfMovables(feedback_type);

      if (!move_feedback_types_list.length) {
        this.showAlert('@no_delete_last');
        return;
      }

      const inst = this.$modal.open({
        templateUrl: this.getTemplatePath('FeedbackTypes/delete-modal.html'),
        controller:  ['$scope', '$modalInstance', 'move_feedback_types_list', function ($scope, $modalInstance, move_feedback_types_list) {
          $scope.move_feedback_types_list = move_feedback_types_list;
          $scope.selected = {
            move_to_id: move_feedback_types_list[0].id
          };

          $scope.confirm = () => $modalInstance.close($scope.selected.move_to_id);

          return $scope.dismiss = () => $modalInstance.dismiss();
        }
        ],
        resolve: {
          move_feedback_types_list: () => move_feedback_types_list
        }
      });

      return inst.result.then(move_to => this.deleteFeedbackType(feedback_type, move_to));
    }

    /*
    * Actually do the delete
  * @param feedback_type - feedback type we want to delete
  * @param move_to - to what type feedback should be moved
    */

    deleteFeedbackType(feedback_type, move_to) {
      return this.Api.sendDelete(`/feedback_types/${feedback_type.id}`, {
        move_to
      }).success(() => {
        this.FeedbackTypesData.remove(feedback_type.id);
        this.ngApply();

        // if currently viewing the deleted feedback type, then should need to switch state
        if ((this.$state.current.name === 'portal.feedback_types.edit') && (parseInt(this.$state.params.id) === feedback_type.id)) {
          return this.$state.go('portal.feedback_types');
        }
      });
    }
  }
  Admin_FeedbackTypes_Ctrl_List.initClass();

  return Admin_FeedbackTypes_Ctrl_List.EXPORT_CTRL();
});
