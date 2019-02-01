define(['Admin/Main/Ctrl/Base'], function(Admin_Ctrl_Base) {
  class Admin_ChatDeps_Ctrl_List extends Admin_Ctrl_Base {
    static initClass() {
      this.CTRL_ID = 'Admin_ChatDeps_Ctrl_List';
      this.CTRL_AS = 'ListCtrl';
    }

    init() {
      this.depData = this.DataService.get('ChatDeps');

      return this.sortedListOptions = {
        axis: 'y',
        handle: '.drag-handle',
        update: (ev, data) => {
          const $list = data.item.closest('ul');

          const order = [];
          $list.find('li').each(function() {
            return order.push(parseInt($(this).data('id')));
          });

          this.depData.saveDisplayOrders(order);
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

    /*
     * Loads the dep list
     */

    initialLoad() {

      const promise = this.depData.loadList().then( list => {
        this.depList = this.sort(list);
        return this.deps = this.depData.listModels;
      });

      return promise;
    }

    /*
     * Show the delete dlg
     */

    startDelete(for_dep_id) {

      const dep = this.depData.findListModelById(for_dep_id);

      if (dep.children && dep.children.length) {
        this.showAlert("You cannot delete a department with sub-departments. Move or delete the sub-departments first.");
        return;
      }

      const move_deps_list = this.depData.getLeafOptionsArray(dep.id);

      if (!move_deps_list.length) {
        this.showAlert('@no_delete_last');
        return;
      }

      const inst = this.$modal.open({
        templateUrl: this.getTemplatePath('ChatDeps/delete-modal.html'),
        controller: ['$scope', '$modalInstance', 'move_deps_list', function($scope, $modalInstance, move_deps_list) {
          $scope.move_deps_list = move_deps_list;
          $scope.selected = {
            move_to_id: move_deps_list[0].id
          };

          $scope.confirm = () => $modalInstance.close($scope.selected.move_to_id);

          return $scope.dismiss = () => $modalInstance.dismiss();
        }
        ],
        resolve: {
          move_deps_list: () => {
            return move_deps_list;
          }
        }
      });

      return inst.result.then( move_to => {
        return this.deleteDepartment(dep, move_to);
      });
    }

    /*
     * Actually do the delete
     */

    deleteDepartment(for_dep, move_to) {

      return this.depData.deleteDepartmentById(for_dep.id, move_to).success( () => {

        if ((this.$state.current.name === 'chat.chat_deps.edit') && (parseInt(this.$state.params.id) === for_dep.id)) {
          return this.$state.go('chat.chat_deps');
        }

      }).error( (info, code) => {
        if (info != null ? info.error_message : undefined) { this.Growl.error(info != null ? info.error_message : undefined); }
        return this.applyErrorResponseToView(info);
      });
    }
  }
  Admin_ChatDeps_Ctrl_List.initClass();

  return Admin_ChatDeps_Ctrl_List.EXPORT_CTRL();
});