define([
  'Admin/Main/DataService/BaseListEdit',
  'Admin/CustomFields/FieldFormMapper',
], (
  BaseListEdit,
  FieldFormMapper
) => {
  class DownloadFields extends BaseListEdit {
    static initClass() {
      this.$inject = ['Api', '$q'];
    }

    init() {
      this.eulaFieldExist = false;
    }

    isEulaFieldExist() {
      return this.eulaFieldExist;
    }

    updateEulaFieldExist() {
      this.eulaFieldExist = this.listModels.filter(f => f.sys_name === 'eula' || f.type_name === 'eula').length > 0;
    }

    _doLoadList() {
      const deferred = this.$q.defer();

      this.Api.sendDataGet([
        '/download_fields'
      ]).then((res) => {
        const custom_fields = [];
        this.eulaFieldExist = false;
        for (const f of Array.from(res.data.api_download_fields.custom_fields)) {
          custom_fields.push(f);
          if (f.sys_name === 'eula') {
            this.eulaFieldExist = true;
          }
        }

        return deferred.resolve(custom_fields);
      });

      return deferred.promise;
    }


    /*
     * Update display orders
     *
     * @param {Array} Array of IDs in order
     * @return {promise}
     */
    saveDisplayOrder(display_orders) {
      return this.Api.sendPostJson('/download_fields/display-order', { display_orders });
    }


    /*
      * Remove a field
      *
      * @param {Integer} id Filter id
      * @return {promise}
    */
    deleteFieldById(id) {
      const promise = this.Api.sendDelete(`/download_fields/${id}`).then(() => {
        this.removeListModelById(id);
        this.updateEulaFieldExist();
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
        this.Api.sendGet(`/download_fields/${id}`).then((result) => {
          const data = {};
          if (result.data.field.sys_name === 'eula') {
            result.data.field.type_name = 'eula'; // to properly show this field in UI
          }
          data.field = result.data.field;
          data.field_type = result.data.field.type_name;
          data.form = this.getFormMapper().getFormFromModel(data.field);
          return deferred.resolve(data);
        });
      } else {
        const data = {
          field:      {},
          field_type: '0',
          form:       this.getFormMapper().getFormFromModel(null)
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
        promise = this.Api.sendPostJson(`/download_fields/${fieldModel.id}`, postData);
      } else {
        promise = this.Api.sendPutJson('/download_fields', postData).success(data => fieldModel.id = data.field_id);
      }

      promise.success(() => {
        mapper.applyFormToModel(fieldModel, formModel);
        this.mergeDataModel(fieldModel);
        this.updateEulaFieldExist();
      });

      return promise;
    }
  }
  DownloadFields.initClass();
  return DownloadFields;
});
