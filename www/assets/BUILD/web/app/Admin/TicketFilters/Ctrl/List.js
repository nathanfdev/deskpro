// TODO: This file was created by bulk-decaffeinate.
// Sanity-check the conversion and remove this comment.
/*
 * decaffeinate suggestions:
 * DS101: Remove unnecessary use of Array.from
 * DS102: Remove unnecessary code created because of implicit returns
 * DS206: Consider reworking classes to avoid initClass
 * Full docs: https://github.com/decaffeinate/decaffeinate/blob/master/docs/suggestions.md
 */
define([
  'Admin/Main/Ctrl/Base'
], function(
  Admin_Ctrl_Base
) {
  class Admin_TicketFilters_Ctrl_List extends Admin_Ctrl_Base {
    static initClass() {
      this.CTRL_ID = 'Admin_TicketFilters_Ctrl_List';
      this.CTRL_AS = 'ListCtrl';
      this.DEPS = ['$state', '$stateParams', 'DataService'];
    }

    init() {
      this.list = [];
      this.filterData = this.DataService.get('TicketFilters');
      this.$scope.display_filter = {
        type:  "all",
        agent: "0",
        team:  "0"
      };

      this.$scope.$watch('display_filter', () => {
        return this.updateFilterList();
      }
      , true);

      return this.sortedListOptions = {
        axis: 'y',
        handle: '.drag-handle',
        update: (ev, data) => {
          const $list = data.item.closest('ul');

          const orders = [];
          $list.find('li').each(function() {
            const id = parseInt($(this).data('id'));

            if (id) {
              return orders.push(id);
            }
          });

          return this.filterData.saveDisplayOrder(orders).then( () => {
            return this.pingElement('display_orders');
          });
        }
      };
    }

    initialLoad() {
      const promise = this.filterData.loadList();
      promise.then( list => {
        this.list = list;

        if (this.$state.current.name === 'tickets.ticket_filters') {
          if (this.list[0]) {
            return this.$state.go('tickets.ticket_filters.edit', { id: this.list[0].id });
          } else {
            return this.$state.go('tickets.ticket_filters.create');
          }
        }
      });

      const data_promise = this.Api.sendDataGet({
        agents: '/agents',
        teams: '/agent_teams'
      }).then( res => {
        this.agents = res.data.agents.agents;
        this.teams = res.data.teams.agent_teams;

        if (!this.teams[0]) {
          return this.teams = null;
        }
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
        } else if (display_filter.type === 'team') {
          const teamId = parseInt(display_filter.team);
          if (teamId) {
            filterList = this.list.filter(x => !x.is_global && x.agent_team && (x.agent_team.id === teamId));
          } else {
            filterList = this.list.filter(x => !x.is_global && x.agent_team);
          }
        }
      }

      return this.$scope.filterList = filterList;
    }

    /*
     * Show the delete dlg
     */
    startDelete(filter_id) {

      let filter = null;
      for (let v of Array.from(this.list)) {
        if (v.id === filter_id) {
          filter = v;
          break;
        }
      }

      const inst = this.$modal.open({
        templateUrl: this.getTemplatePath('TicketFilters/delete-modal.html'),
        controller: ['$scope', '$modalInstance', function($scope, $modalInstance) {
          $scope.confirm = () => $modalInstance.close();

          return $scope.dismiss = () => $modalInstance.dismiss();
        }
        ]
      });

      return inst.result.then( () => {
        return this.filterData.deleteFilterId(filter.id).then(() => {
          if ((this.$state.current.name === 'tickets.ticket_filters.edit') && (parseInt(this.$state.params.id) === filter.id)) {
            return this.$state.go('tickets.ticket_filters');
          }
        });
      });
    }
  }
  Admin_TicketFilters_Ctrl_List.initClass();

  return Admin_TicketFilters_Ctrl_List.EXPORT_CTRL();
});
