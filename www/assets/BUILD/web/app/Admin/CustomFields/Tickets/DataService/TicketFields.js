/*
 * decaffeinate suggestions:
 * DS101: Remove unnecessary use of Array.from
 * DS102: Remove unnecessary code created because of implicit returns
 * DS206: Consider reworking classes to avoid initClass
 * Full docs: https://github.com/decaffeinate/decaffeinate/blob/master/docs/suggestions.md
 */
define([
  'Admin/Main/DataService/BaseListEdit',
  'Admin/CustomFields/FieldFormMapper',
], function(
  BaseListEdit,
  FieldFormMapper
)  {
  let TicketFields;
  return TicketFields = (function() {
    TicketFields = class TicketFields extends BaseListEdit {
      static initClass() {
        this.$inject = ['Api', '$q'];
      }

      init() {
        return this.field_enabled = {
          category: true,
          priority: true,
          workflow: true,
          product:  true
        };
      }

      _doLoadList() {

        const deferred = this.$q.defer();

        this.Api.sendDataGet([
          '/ticket_fields'
        ]).then( res => {
          for (var f of ['category', 'priority', 'workflow', 'product', 'label']) {
            this.field_enabled[f] = false;
            if (res.data.api_ticket_fields[f + '_enabled']) {
              this.field_enabled[f] = true;
            }
          }

          const custom_fields = [];
          for (f of Array.from(res.data.api_ticket_fields.custom_fields)) {
            custom_fields.push(f);
          }

          return deferred.resolve(custom_fields);
        });

        return deferred.promise;
      }


      /*
        * Remove a field
        *
        * @param {Integer} id Filter id
        * @return {promise}
      */
      deleteFieldById(id) {
        const promise = this.Api.sendDelete(`/ticket_fields/${id}`).then(() => {
          return this.removeListModelById(id);
        });
        return promise;
      }


      /*
        * Get all data needed for the edit field page
        *
        * @param {Integer} id Filter id
        * @return {promise}
      */
      loadEditFieldData(id) {
        const deferred = this.$q.defer();

        if (id) {
          this.Api.sendGet(`/ticket_fields/${id}`).then( result => {
            const data = {
              field: result.data.field,
              field_type: result.data.field.type_name,
              form: this.getFormMapper().getFormFromModel(result.data.field),
              referencedBy: result.data.referencedBy || []
            };
            return deferred.resolve(data);
          });
        } else {
          const data = {
            field: {},
            field_type: '0',
            form: this.getFormMapper().getFormFromModel(null)
          };
          deferred.resolve(data);
        }

        return deferred.promise;
      }


      /*
        * Get the form mapper
        *
        * @return {FieldFormMapper}
      */
      getFormMapper() {
        if (this.formMapper) { return this.formMapper; }
        this.formMapper = new FieldFormMapper();
        return this.formMapper;
      }


      /*
        * Saves a form model and applies the form model to the field model
        * once finished.
        *
        * @param {Object} fieldModel The field model
        * @param {Object} formModel  The model representing the form
        * @return {promise}
      */
      saveFormModel(fieldModel, formModel) {
        let promise;
        const mapper = this.getFormMapper();
        const postData = mapper.getPostDataFromForm(fieldModel.type_name, formModel);

        if (fieldModel.id) {
          promise = this.Api.sendPostJson(`/ticket_fields/${fieldModel.id}`, postData);
        } else {
          promise = this.Api.sendPutJson('/ticket_fields', postData).success( data => fieldModel.id = data.field_id);
        }

        promise.success(() => {
          mapper.applyFormToModel(fieldModel, formModel);
          return this.mergeDataModel(fieldModel);
        });

        return promise;
      }
    };
    TicketFields.initClass();
    return TicketFields;
  })();
});
