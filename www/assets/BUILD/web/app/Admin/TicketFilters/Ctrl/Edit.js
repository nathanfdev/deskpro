define([
  'Admin/Main/Ctrl/Base',
  'DeskPRO/Util/Util'
], (
  Admin_Ctrl_Base,
  Util
) => {
  class Admin_TicketFilters_Ctrl_Edit extends Admin_Ctrl_Base {
    static initClass() {
      this.CTRL_ID   = 'Admin_TicketFilters_Ctrl_Edit';
      this.CTRL_AS   = 'EditCtrl';
      this.DEPS      = ['dpObTypesDefTicketFilter', '$stateParams', '$timeout'];
    }

    init() {
      this.filterId = parseInt(this.$stateParams.id || 0);
      this.filterData = this.DataService.get('TicketFilters');
      this.filter = null;

      this.filter_criteria = {};
      this.criteriaTypeDef = this.dpObTypesDefTicketFilter;
      return this.criteriaOptionTypes = this.criteriaTypeDef.getOptionsForTypes();
    }

    initialLoad() {
      const p = this.filterData.loadEditFilterData(this.filterId).then((data) => {
        this.agents = data.agents;
        this.teams = data.teams;
        if (!this.teams[0]) {
          this.teams = null;
        }

        if (data.filter) {
          return this.filter = data.filter;
        }
        return this.filter = {
          is_global: true
        };
      });

      const p2 = this.criteriaTypeDef.loadDataOptions().then(() => this.criteriaOptionTypes = this.criteriaTypeDef.getOptionsForTypes());

      return this.$q.all([p, p2]).then(() => {
        this.form = this.getFormFromModel(this.filter);

        this.filter_criteria = {};
        if (this.filter.terms) {
          return (() => {
            const result = [];
            for (const term of Array.from(this.filter.terms.terms)) {
              const rowId = Util.uid('term');
              result.push(this.filter_criteria[rowId] = term);
            }
            return result;
          })();
        }
      });
    }

    getFormFromModel(filterModel) {
      const form = {};
      form.title = filterModel.title || '';

      if (filterModel.is_global) {
        form.perm_type = 'global';
      } else if (filterModel.agent_team && this.teams[0]) {
        form.perm_type = 'team';
      } else {
        form.perm_type = 'agent';
      }

      if (this.filter.person) {
        form.agent_id = `${this.filter.person.id}`;
      } else {
        form.agent_id = `${this.agents[0].id}`;
      }

      form.team_id = null;
      if (this.teams) {
        if (this.filter.agent_team) {
          form.team_id = `${this.filter.agent_team.id}`;
        } else {
          form.team_id = `${this.teams[0].id}`;
        }
      }

      return form;
    }

    saveForm() {
      let method,
        url;
      if (!this.$scope.form_props.$valid) { return; }

      if (this.filterId) {
        method = 'POST';
        url = `/ticket_filters/${this.filterId}`;
      } else {
        method = 'PUT';
        url = '/ticket_filters';
      }

      const postData = {
        filter: {
          title:         this.form.title,
          is_global:     this.form.perm_type === 'global',
          person_id:     this.form.perm_type === 'agent' ? parseInt(this.form.agent_id) || null : null,
          agent_team_id: this.form.perm_type === 'team' ? parseInt(this.form.team_id) || null : null
        }
      };
      postData.filter.terms = this.filter_criteria;

      return this.sendFormSaveApiCall(method, url, postData).then(
        (res) => {
          this.Growl.success(this.getRegisteredMessage('saved_filter'));

          this.filter.title = this.form.title;
          if (res.data.filter_id) {
            this.filter.id = res.data.filter_id;
          }

          this.filter.is_global = this.form.perm_type === 'global';
          this.filter.person = null;
          this.filter.agent_team = null;

          if (this.form.perm_type === 'agent') {
            this.filter.person = this.agents.filter(x => x.id === parseInt(this.form.agent_id))[0];
          }
          if (this.form.perm_type === 'team') {
            this.filter.agent_team = this.teams.filter(x => x.id === parseInt(this.form.team_id))[0];
          }

          return this.filterData.loadList(true).then(() => {
            if (!this.filterId) { return this.$state.go('tickets.ticket_filters.gocreate'); }
          });
        },

        (res) => {
          if (res.data != null ? res.data.error_message : undefined) { return this.Growl.error(res.data != null ? res.data.error_message : undefined); }
        });
    }
  }
  Admin_TicketFilters_Ctrl_Edit.initClass();

  return Admin_TicketFilters_Ctrl_Edit.EXPORT_CTRL();
});
