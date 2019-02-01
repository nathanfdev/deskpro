define([
  'Admin/Main/Ctrl/Base',
  'DeskPRO/Util/Util',
  'Admin/TicketSlas/SlaFormMapper'
], function(
  Admin_Ctrl_Base,
  Util,
  SlaFormMapper
) {
  class Admin_TicketSlas_Ctrl_Edit extends Admin_Ctrl_Base {
    static initClass() {
      this.CTRL_ID   = 'Admin_TicketSlas_Ctrl_Edit';
      this.CTRL_AS   = 'EditCtrl';
      this.DEPS      = ['dpObTypesDefTicketActions', 'dpObTypesDefTicketCriteria'];
    }

    init() {
      this.formMapper = new SlaFormMapper();
      this.slaData    = this.DataService.get('TicketSlas');
      this.sla        = null;
      this.form       = this.getFormFromModel({});

      this.actionsTypeDef = this.dpObTypesDefTicketActions;
      this.criteraTypeDef = this.dpObTypesDefTicketCriteria;

      this.$scope.criteriaOptionTypes = [];
      this.$scope.actionOptionTypes   = [];

      return this.updateCriteriaOptionTypes();
    }

    updateCriteriaOptionTypes() {
      let types = [];
      const setActionOptions = this.actionsTypeDef.getOptionsForTypes(types, {dynamicOptions: this.customActions});
      this.$scope.actionOptionTypes.length = 0;
      for (var opt of Array.from(setActionOptions)) {
        this.$scope.actionOptionTypes.push(opt);
      }

      types = [
        'web', 'web.user', 'email', 'email.user', 'api', 'api.user',
        'web.agent', 'email.agent', 'api.agent'
      ];
      const setCritOptions = this.criteraTypeDef.getOptionsForTypes(types);
      this.$scope.criteriaOptionTypes.length = 0;
      return (() => {
        const result = [];
        for (opt of Array.from(setCritOptions)) {
          result.push(this.$scope.criteriaOptionTypes.push(opt));
        }
        return result;
      })();
    }

    initialLoad() {
      const proms = [];

      if (this.$stateParams.id) {
        proms.push(this.slaData.loadEditSlaData(this.$stateParams.id).then( data => {
          this.sla = data.sla;
          this.form = this.getFormFromModel(this.sla);
          return this.origForm = Util.clone(this.form, true);
        })
        );
      } else {
        this.macro = {};
        this.sla = {};
        this.form = this.getFormFromModel({});
        this.origForm = Util.clone(this.form, true);
      }

      proms.push(this.actionsTypeDef.loadDataOptions());
      proms.push(this.criteraTypeDef.loadDataOptions());
      proms.push(this.Api.sendDataGet({customActions: '/ticket_triggers/get-custom-actions'}).then(result => {
        return this.customActions = result.data.customActions.action_defs;
      })
      );

      return this.$q.all(proms).then(() => {
        return this.updateCriteriaOptionTypes();
      });
    }



    getFormFromModel(slaModel) {
      return this.formMapper.getFormFromModel(slaModel);
    }

    saveForm() {
      let is_new, promise;
      const postData = this.formMapper.getPostDataFromFormModel(this.form);

      this.startSpinner('saving');
      if (this.sla.id) {
        is_new = false;
        promise = this.Api.sendPostJson(`/ticket_slas/${this.sla.id}`, postData);
      } else {
        is_new = true;
        promise = this.Api.sendPutJson('/ticket_slas', postData);
      }

      promise.success( result => {
        this.sla.id = result.sla_id;

        if (is_new) {
          this.sla.is_enabled = true;
        }

        this.sla.title = postData.title;

        this.stopSpinner('saving', true).then(() => {
          return this.Growl.success("Saved");
        });

        this.slaData.mergeDataModel({
          id: this.sla.id,
          title: this.sla.title
        });

        this.skipDirtyState();
        if (is_new) {
          return this.$state.go('tickets.slas.gocreate');
        }
      });
      promise.error( (info, code) => {
        this.stopSpinner('saving', true);
        this.applyErrorResponseToView(info);
        if (info != null ? info.error_message : undefined) {
          return this.Growl.error(info != null ? info.error_message : undefined);
        }
      });

      return promise;
    }
  }
  Admin_TicketSlas_Ctrl_Edit.initClass();

  return Admin_TicketSlas_Ctrl_Edit.EXPORT_CTRL();
});