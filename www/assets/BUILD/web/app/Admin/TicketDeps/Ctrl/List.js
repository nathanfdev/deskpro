define(['Admin/Main/Ctrl/Base'], function(Admin_Ctrl_Base) {
  class Admin_TicketDeps_Ctrl_List extends Admin_Ctrl_Base {
    constructor(...args) {
      {
        // Hack: trick Babel/TypeScript into allowing this before super.
        if (false) { super(); }
        const thisFn = (() => this).toString();
        const thisName = thisFn.slice(thisFn.indexOf('return') + 6 + 1, thisFn.indexOf(';')).trim();
        eval(`${thisName} = this;`);
      }
      this.changeDefaultDepartment = this.changeDefaultDepartment.bind(this);
      super(...args);
    }

    static initClass() {
      this.CTRL_ID = 'Admin_TicketDeps_Ctrl_List';
      this.CTRL_AS = 'ListCtrl';
      this.DEPS      = ['Api', 'Api2', 'Growl'];
    }

    init() {
      this.depData = this.DataService.get('TicketDeps');
      this.defaultDepartments = {};
      this.brandId = 0;
      this.brandList = [];
      this.initiallyLoaded = false;
      return this.sortedListOptions = {
        axis:   'y',
        handle: '.drag-handle',
        update: (ev, data) => {
          const $list = data.item.closest('ul');

          const order = [];
          $list.find('li').each(function () {
            return order.push(parseInt($(this).data('id')));
          });

          this.depData.saveDisplayOrders(order);
          return this.pingElement('display_orders');
        }
      };
    }

    /*
     * Loads the dep list
     */
    initialLoad() {
      const promise = this.depData.loadList().then((list) => {
        this.depList = (list || []).sort((a, b) => {
          const orderA = parseInt(a.display_order);
          const orderB = parseInt(b.display_order);
          if (orderA < orderB) { return -1; }
          if (orderA > orderB) { return 1; }
          return 0;
        });
        this.deps = this.depData.listModels;
        this.flattenedList = [{ id: '0', title: 'None', has_children: false }];
        if (this.depList) {
          return (() => {
            const result = [];
            for (const dep of Array.from(this.depList)) {
              if (!dep.has_children) { this.flattenedList.push(dep); }
              if (dep.has_children) { result.push(Array.from(dep.children).map(subdep => this.flattenedList.push(subdep))); } else {
                result.push(undefined);
              }
            }
            return result;
          })();
        }
      });

      const brandsPromise = this.Api.sendGet('/ticket_brands').then((response) => {
        this.brandList = response.data.brands;
        return this.brandId = response.data.brands[0].id;
      });

      const brandsSettingsPromise = this.Api2.sendGet('/settings/departments/default').then((response) => {
        for (const setting of Array.from(response.data.data)) {
          if (!this.defaultDepartments[setting.brand]) { this.defaultDepartments[setting.brand] = {}; }
          let defaultDepartment = 0;
          if (setting.department) { defaultDepartment = parseInt(setting.department, 10); }
          this.defaultDepartments[setting.brand][setting.type] = defaultDepartment;
        }
        return this.initiallyLoaded = true;
      });

      return this.$q.all([promise, brandsPromise, brandsSettingsPromise]);
    }

    /*
    * Get the move dep list for use in the delete/move dlg
      * @return {Array}
    */
    getMoveDepList(for_dep) {
      const dep_move_list = [];
      for (const dep of Array.from(this.departments)) {
        if (for_dep.id !== dep.id) {
          if (!dep._child_ids) {
            dep_move_list.push(dep);
          }
        }
      }

      return dep_move_list;
    }

    /**
     * Show the delete dlg
     */

    startDelete(for_dep_id) {
      const dep = this.depData.findListModelById(for_dep_id);

      if (dep.children != null ? dep.children.length : undefined) {
        this.showAlert('You cannot delete a department with sub-departments. Move or delete the sub-departments first.');
        return;
      }

      const move_deps_list = this.depData.getLeafOptionsArray(dep.id);

      if (!move_deps_list.length) {
        this.showAlert('@no_delete_last');
        return;
      }

      const inst = this.$modal.open({
        templateUrl: this.getTemplatePath('TicketDeps/delete-modal.html'),
        controller:  ['$scope', '$modalInstance', 'move_deps_list', function ($scope, $modalInstance, move_deps_list) {
          $scope.move_deps_list = move_deps_list;
          $scope.selected = {
            move_to_id: move_deps_list[0].id
          };

          $scope.confirm = () => $modalInstance.close($scope.selected.move_to_id);

          return $scope.dismiss = () => $modalInstance.dismiss();
        }
        ],
        resolve: {
          move_deps_list: () => move_deps_list
        }
      });

      return inst.result.then(move_to => this.deleteDepartment(dep, move_to));
    }

    /*
     * Actually do the delete
     */

    deleteDepartment(for_dep, move_to) {
      return this.depData.deleteDepartmentById(for_dep.id, move_to).success(() => {
        if ((this.$state.current.name === 'tickets.ticket_deps.edit') && (parseInt(this.$state.params.id) === for_dep.id)) {
          this.skipDirtyState();
          return this.$state.go('tickets.ticket_deps');
        }
      }).error((info, code) => {
        if (info != null ? info.error_message : undefined) { this.Growl.error(info != null ? info.error_message : undefined); }
        return this.applyErrorResponseToView(info);
      });
    }

    changeDefaultDepartment(type) {
      if (this.brandId && !!this.defaultDepartments[this.brandId] && this.defaultDepartments[this.brandId][type] && this.initiallyLoaded) {
        const data = {
          type,
          department: this.defaultDepartments[this.brandId][type] > 0 ? this.defaultDepartments[this.brandId][type] : null,
          brand:      this.brandId
        };

        return this.Api2.sendPutJson('settings/departments/default', data)
          .success(() => this.Growl.success(`Default department for ${type}s was successfully set`));
      }
    }
  }
  Admin_TicketDeps_Ctrl_List.initClass();

  return Admin_TicketDeps_Ctrl_List.EXPORT_CTRL();
});
