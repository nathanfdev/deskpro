define([
  'Admin/Main/Ctrl/Base'
], function(
  Admin_Ctrl_Base
) {
  class Admin_TicketMacros_Ctrl_List extends Admin_Ctrl_Base {
    static initClass() {
      this.CTRL_ID = 'Admin_TicketMacros_Ctrl_List';
      this.CTRL_AS = 'ListCtrl';
      this.DEPS = ['$state', '$stateParams', 'DataService'];
    }

    init() {
      this.list = [];
      this.macroData = this.DataService.get('TicketMacros');
      this.$scope.display_filter = {
        type:  "all",
        agent: "0"
      };

      return this.$scope.$watch('display_filter', () => {
        return this.updateFilterList();
      }
      , true);
    }

    initialLoad() {
      const promise = this.macroData.loadList();
      promise.then( list => {
        return this.list = list;
      });

      const data_promise = this.Api.sendDataGet({
        agents: '/agents'
      }).then( res => {
        return this.agents = res.data.agents.agents;
      });

      const bothPromise = this.$q.all([promise, data_promise]);
      bothPromise.then(() => {
        return this.updateFilterList();
      });

      return bothPromise;
    }

    updateFilterList() {
      let filterList = [];
      const { display_filter } = this.$scope;

      if (display_filter.type === 'all') {
        filterList = this.list;
      } else {
        if (display_filter.type === 'global') {
          filterList = this.list.filter(x => x.is_global);
        } else if (display_filter.type === 'agent') {
          const agentId = parseInt(display_filter.agent);
          if (agentId) {
            filterList = this.list.filter(x => !x.is_global && x.person && (x.person.id === agentId));
          } else {
            filterList = this.list.filter(x => !x.is_global && x.person);
          }
        } else if (display_filter.type === 'department') {
          filterList = this.list.filter(x => !x.is_global && x.department);
        }
      }

      return this.$scope.filterList = filterList;
    }

    /*
     * Show the delete dlg
     */
    startDelete(macro) {
      const inst = this.$modal.open({
        templateUrl: this.getTemplatePath('TicketMacros/delete-modal.html'),
        controller: ['$scope', '$modalInstance', function($scope, $modalInstance) {
          $scope.confirm = () => $modalInstance.close();

          return $scope.dismiss = () => $modalInstance.dismiss();
        }
        ]
      });

      return inst.result.then( () => {
        return this.macroData.deleteMacroById(macro.id).then(() => {
          if ((this.$state.current.name === 'tickets.ticket_macros.edit') && (parseInt(this.$state.params.id) === macro.id)) {
            return this.$state.go('tickets.ticket_macros');
          }
        });
      });
    }
  }
  Admin_TicketMacros_Ctrl_List.initClass();

  return Admin_TicketMacros_Ctrl_List.EXPORT_CTRL();
});