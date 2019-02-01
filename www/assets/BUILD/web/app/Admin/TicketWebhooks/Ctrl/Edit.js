define([
  'Admin/Main/Ctrl/Base',
  'DeskPRO/Util/Util'
], function(
  Admin_Ctrl_Base,
  Util
) {
  class Admin_TicketWebhooks_Ctrl_Edit extends Admin_Ctrl_Base {
    static initClass() {
      this.CTRL_ID   = 'Admin_TicketWebhooks_Ctrl_Edit';
      this.CTRL_AS   = 'EditCtrl';
      this.DEPS      = ['dpObTypesDefTicketFilter', '$stateParams', 'Api2'];
    }

    init() {
      this.webhook     = null;
      this.webhookId   = parseInt(this.$stateParams.id || 0);

      this.form = {};
      this.filter_criteria = {};
      this.criteriaTypeDef = this.dpObTypesDefTicketFilter;
      this.criteriaOptionTypes = this.criteriaTypeDef.getOptionsForTypes();
      this.webhookUrl = null;

    }

    /*
     * Load the trigger
     */
    initialLoad() {
      let promise = Promise.resolve();
      if (this.webhookId) {
        promise = this.Api2.sendGet(`/webhooks/tickets/${this.webhookId}`).then(
          result => {
            this.webhook = result.data.data;
            this.webhookUrl = this.Api2.buildEndpointAPIUrl(`webhooks/${this.webhook.auth_id}/invocation`);

            this.form = this.getFormFromModel(result.data.data);
            this.filter_criteria = {};
            if (this.webhook.search_terms != null ? this.webhook.search_terms.length : undefined) {
              return (() => {
                const result1 = [];
                for (let term of Array.from(this.webhook.search_terms)) {
                  const rowId = Util.uid('term');
                  result1.push(this.filter_criteria[rowId] = term);
                }
                return result1;
              })();
            }
        });
      }

      const promise2 = this.criteriaTypeDef.loadDataOptions().then(() => {
        return this.criteriaOptionTypes = this.criteriaTypeDef.getOptionsForTypes();
      });
      const promises = [promise, promise2];
      return this.$q.all(promises);
    }

    copyWebhookUrl() {
      return ev.preventDefault();
    }

    getFormFromModel(model) {
      const form = {};
      form.title = model.title || '';
      form.payload_decoder = model.payload_decoder || '';

      form.terms_set = {};
      if (model.search_terms != null ? model.search_terms.length : undefined) {
        const setId = _.uniqueId('termset');
        form.terms_set[setId] = {};

        for (let term of Array.from(model.search_terms)) {
          const rowId = _.uniqueId('term');
          form.terms_set[setId][rowId] = term;
        }
      }

      return form;
    }

    saveForm() {
      if (!this.$scope.form_props.$valid) { return; }

      const data = {
        title: this.form.title,
        search_terms: Object.keys(this.filter_criteria).map(key => this.filter_criteria[key])
      };

      if (this.form.payload_decoder != null ? this.form.payload_decoder.length : undefined) {
        data.payload_decoder = this.form.payload_decoder;
      }

      let p = null;
      if (this.webhookId) {
        return p = this.Api2.sendPutJson(`/webhooks/tickets/${this.webhookId}`, data);
      } else {
        p = this.Api2.sendPostJson("/webhooks/tickets", data);

        return p.then(res => {
          if (!this.webhookId) {
            this.webhook = res.data.data;
            this.webhookId = this.webhook.id;
          }
          this.$scope.$parent.List.onWebhookAdded(this.webhook);
          return this.Growl.success(this.getRegisteredMessage('saved_filter'));
        }).catch(res => {
          if (res.data != null ? res.data.error_message : undefined) { return this.Growl.error(res.data != null ? res.data.error_message : undefined); }
        });
      }
    }
  }
  Admin_TicketWebhooks_Ctrl_Edit.initClass();


  return Admin_TicketWebhooks_Ctrl_Edit.EXPORT_CTRL();
});
