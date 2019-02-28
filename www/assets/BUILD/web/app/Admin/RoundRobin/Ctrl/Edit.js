define(['Admin/Main/Ctrl/Base', 'DeskPRO/Util/Arrays'], function(Admin_Ctrl_Base, Arrays) {
  class Admin_RoundRobin_Ctrl_Edit extends Admin_Ctrl_Base {
    static initClass() {
      this.CTRL_ID = 'Admin_RoundRobin_Ctrl_Edit';
      this.CTRL_AS = 'EditCtrl';
      this.DEPS = ['$stateParams', 'Growl', '$timeout'];
    }


    init() {
      this.robin = {};
      this.agents = [];
      this.nextAgentInQueue = null;
      this.bulk = null;
      this.service = this.DataService.get('RoundRobin');
      this.serviceAgents = this.DataService.get('Agents');
      this.serviceDeps = this.DataService.get('TicketDeps');
      this.serviceGroups = this.DataService.get('AgentGroups');
      this.serviceTeams = this.DataService.get('AgentTeams');

      this.groups = [];
      this.teams = [];
      this.deps = [];

      return this.sortedListOptions = {
        axis:   'y',
        items:  'li.sortable',
        handle: '.drag-handle'
      };
    }


    initialLoad() {
      this.service.loadList(true);
      const promises = [this.serviceAgents.all(), this.service.get(parseInt(this.$stateParams.id || 0)),
        this.serviceDeps.all(), this.serviceGroups.all(), this.serviceTeams.all(),];

      return this.$q.all(promises).then((res) => {
        this.agents = res[0];
        this.mapFormModel(res[1]);
        this.deps = res[2];
        this.groups = res[3];
        return this.teams = res[4];
      });
    }


    mapFormModel(model) {
      this.robin.agents = [];
      if ((model == null)) { return; }

      this.robin.id = model.id;
      this.robin.title = model.title;
      this.robin.online_only = model.online_only;

      if (model.next != null) { this.serviceAgents.get(model.next.id).then(agent => this.robin.next = agent); }
      // remap agents to list models
      const promises = [];
      model.agents.map((data) => {
        const promise = this.serviceAgents.get(data.id).then(agent => this.robin.agents.push(agent));
        return promises.push(promise);
      });

      return this.$q.all(promises).then(() => this.sortAgents());
    }


    sortAgents() {
      return this.agents.sort((a, b) => {
        const indexA = this.robin.agents.indexOf(a);
        const indexB = this.robin.agents.indexOf(b);
        if (indexA === indexB) { return 0; }
        if (indexA === -1) { return 1; }
        if (indexB === -1) { return -1; }
        if (indexA < indexB) { return -1; }  return 1;
      });
    }


    handleAgent(agent) {
      if (this.agents.indexOf(agent) === -1) { return; }
      const index = this.robin.agents.indexOf(agent);
      if (index === -1) { return this.robin.agents.unshift(agent); }  return this.robin.agents.splice(index, 1);
    }


    handleBulk() {
      if ((this.bulk == null)) { return; }
      const params = this.bulk.split('.');

      const findAgent = id => Arrays.find(this.agents, a => a.id === id);

      switch (params[0]) {
        case 'd':
          return this.Api.sendGet(`/ticket_deps/${params[1]}?with_agents_list=1`).success((data) => {
            if (!data || !data.agents_list) { return; }

            return (() => {
              const result = [];
              for (const a of Array.from(data.agents_list)) {
                const agent = findAgent(a.id);
                if (agent) { this.handleAgent(agent); }
                result.push(this.sortAgents());
              }
              return result;
            })();
          });

        case 'g':
          return this.Api.sendGet(`/agent_groups/${params[1]}`).success((data) => {
            if (!data || !data.group.members) { return; }

            return (() => {
              const result = [];
              for (const a of Array.from(data.group.members)) {
                const agent = findAgent(a.id);
                if (agent) { this.handleAgent(agent); }
                result.push(this.sortAgents());
              }
              return result;
            })();
          });

        case 't':
          return this.Api.sendGet(`/agent_teams/${params[1]}`).success((data) => {
            if (!data || !data.team.members) { return; }

            return (() => {
              const result = [];
              for (const a of Array.from(data.team.members)) {
                const agent = findAgent(a.id);
                if (agent) { this.handleAgent(agent); }
                result.push(this.sortAgents());
              }
              return result;
            })();
          });
      }
    }


    save() {
      this.startSpinner('saving');
      return this.service.set(this.robin).then(
        (model) => {
          this.stopSpinner('saving');
          this.mapFormModel(model);
          this.$state.go('tickets.roundrobin');
          return this.Growl.success('Saved');
        },
        (res) => {
          this.stopSpinner('saving');
          return this.Growl.error(res.info);
        });
    }


    showLogs() {
      return this.Api.sendGet(`/round_robin/${this.robin.id}/logs`).then(res => this.$modal.open({
        template:   res.data,
        controller: ['$scope', '$modalInstance', ($scope, $modalInstance) =>
            $scope.dismiss = () => $modalInstance.dismiss()

        ]
      }));
    }


    delete() {
      return this.service.checkTriggers(this.robin.id).then((data) => {
        this.active_triggers = data.active_triggers;

        return this.$timeout(
          () => {
            const title = this.getRegisteredMessage('modal_title');
            const msg = this.getRegisteredMessage('modal_message');
            const state = this.$state;

            const _del = modal => this.service.remove(this.robin).then(() => {
              modal.dismiss();
              return state.go('tickets.roundrobin');
            });

            return this.$modal.open({
              templateUrl: this.getTemplatePath('Index/modal-confirm.html'),
              controller:  ['$scope', '$modalInstance', function ($scope, $modalInstance) {
                $scope.title = title;
                $scope.message = msg;

                $scope.dismiss = () => $modalInstance.dismiss();

                return $scope.confirm = () => _del($modalInstance);
              }
              ]
            });
          },
          1
        );
      });
    }
  }
  Admin_RoundRobin_Ctrl_Edit.initClass();


  return Admin_RoundRobin_Ctrl_Edit.EXPORT_CTRL();
});
