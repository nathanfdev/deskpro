// TODO: This file was created by bulk-decaffeinate.
// Sanity-check the conversion and remove this comment.
/*
 * decaffeinate suggestions:
 * DS101: Remove unnecessary use of Array.from
 * DS102: Remove unnecessary code created because of implicit returns
 * DS206: Consider reworking classes to avoid initClass
 * DS207: Consider shorter variations of null checks
 * Full docs: https://github.com/decaffeinate/decaffeinate/blob/master/docs/suggestions.md
 */
define([
  'Admin/Main/DataService/BaseListEdit'
], function(
  BaseListEdit
)  {
  let Admin_TicketWebhooks_DataService_Webhooks;
  return Admin_TicketWebhooks_DataService_Webhooks = (function() {
    Admin_TicketWebhooks_DataService_Webhooks = class Admin_TicketWebhooks_DataService_Webhooks extends BaseListEdit {
      static initClass() {
        this.$inject = ['Api', '$q', 'Api2'];
      }

      init() {
        return this.type = 'webhook';
      }

      url() {
        return '/webhooks/tickets';
      }

      _doLoadList() {
        const deferred = this.$q.defer();

        this.Api2.sendGet('/webhooks/tickets?include=ticket_trigger&inline_sideloads=1').then(
          response => {
            const webhooks = response.data.data;
            return deferred.resolve(webhooks);
        }).catch(res => {
          return deferred.reject();
        });

        return deferred.promise;
      }

      deleteTriggerById(triggerId) {
        let webhook = null;
        let trigger = null;
        for (let model of Array.from(this.listModels)) {
          for (trigger of Array.from(model.triggers)) {
            if (trigger[this.idProp] === triggerId) {
              webhook = model;
            }
            if (webhook) { break; }
          }
          if (webhook) { break; }
        }

        let deferred = this.$q.defer();
        if (!webhook) {
          return Promise.reject(new Error(`could not find parent webhook for trigger id: ${triggerId}`));
        }

        deferred = this.$q.defer();
        this.Api2.sendDelete(`/webhooks/${webhook[this.idProp]}/triggers/${triggerId}`).success(
          () => {
            webhook.triggers = webhook.triggers.filter(
              t => { return t[this.idProp] !== triggerId;
             });
            return deferred.resolve(trigger);
          }
        ,
          (data, status, headers, config) => deferred.reject());

        return deferred.promise;
      }

      _doRemove(model) {
        const deferred = this.$q.defer();

        const id = model[this.idProp] || 0;
        this.Api2.sendDelete(this.url() + `/${id}`).success(
          () => deferred.resolve()
          ,
          (data, status, headers, config) => deferred.reject(
            {info: data.error_message, status}
          )

         );
        return deferred.promise;
      }

      _doSave(model) {
        const deferred = this.$q.defer();

        let method = 'sendPostJson'; // is new
        if ((model[this.idProp] != null) && model[this.idProp]) { method = 'sendPutJson'; }

        const id = model[this.idProp] || 0;
        this.Api2[method](this.url() + `/${id}`, model).then(
          response => {
            console.log('after save ', response);
            return deferred.resolve(response.data.data);
        }).catch(res => {
          // data, status, headers, config
          return deferred.reject();
        });



        return deferred.promise;
      }
    };
    Admin_TicketWebhooks_DataService_Webhooks.initClass();
    return Admin_TicketWebhooks_DataService_Webhooks;
  })();
});


