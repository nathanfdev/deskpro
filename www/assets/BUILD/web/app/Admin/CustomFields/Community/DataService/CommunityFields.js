define([
  'Admin/Main/DataService/BaseListEdit',
  'Admin/CustomFields/FieldFormMapper',
], (
  BaseListEdit,
  FieldFormMapper
) => {
  class CommunityFields extends BaseListEdit {
    static initClass() {
      this.$inject = ['Api', '$q', 'Api2', '$state'];
    }

    init() {}

    _doLoadList() {
      const deferred = this.$q.defer();

      this.Api2.sendDataGet([
        `/community_forums/${this.$state.params.forumId}`
      ]).then((res) => {
        const custom_fields = [];
        for (const f of Array.from(res.data.data)) {
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
    deleteFieldById(id, forumId = null) {
      return this.Api2.sendDelete(`/community_forums/${forumId ? forumId : this.$state.params.forumId}/custom_fields/${id}`).then(() => this.removeListModelById(id));
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
        this.Api2.sendGet(`/community_forums/${this.$state.params.forumId}/custom_fields/${id}`).then((result) => {
          const data = {};
          console.log(result.data.data);
          data.field = result.data.data;
          data.field_type = result.data.data.type_name;
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
        promise = this.Api2.sendPutJson(`/community_forums/${this.$state.params.forumId}/custom_fields/${fieldModel.id}`, postData);
      } else {
        promise = this.Api2.sendPostJson(`/community_forums/${this.$state.params.forumId}/custom_fields`, postData).success(data => fieldModel.id = data.field_id);
      }

      promise.success(() => {
        mapper.applyFormToModel(fieldModel, formModel);
        return this.mergeDataModel(fieldModel);
      });

      return promise;
    }
  }
  CommunityFields.initClass();
  return CommunityFields;
});
