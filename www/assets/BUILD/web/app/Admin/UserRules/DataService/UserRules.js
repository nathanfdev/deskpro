define([
  'Admin/Main/DataService/BaseListEdit',
  'Admin/UserRules/UserRuleEditFormMapper'
], (
  BaseListEdit,
  UserRuleEditFormMapper
) => {
  class UserRules extends BaseListEdit {
    static initClass() {
      this.$inject = ['Api', '$q'];
    }

    _doLoadList() {
      const deferred = this.$q.defer();

      this.Api.sendGet('/user_rules').success((data) => {
        const models = data.user_rules;
        return deferred.resolve(models);
      }
      , (data, status, headers, config) => deferred.reject());

      return deferred.promise;
    }

    /*
     * Remove a model
     *
     * @param {Integer} id
     * @return {promise}
     */
    deleteUserRuleById(id) {
      const promise = this.Api.sendDelete(`/user_rules/${id}`).success(() => this.removeListModelById(id));

      return promise;
    }

    /*
     * Get the form mapper
     *
     * @return {UserRuleEditFormMapper}
     */
    getFormMapper() {
      if (this.formMapper) { return this.formMapper; }
      this.formMapper = new UserRuleEditFormMapper();
      return this.formMapper;
    }

    /*
     * Get all data needed for the edit page
     *
     * @param {Integer} id
     * @return {promise}
     */
    loadEditUserRuleData(id) {
      const deferred = this.$q.defer();

      if (id) {
        this.Api.sendDataGet({
          user_rule:  `/user_rules/${id}`,
          usergroups: '/non_sys_usergroups'
        }).then((result) => {
          const data = {};
          data.user_rule = result.data.user_rule.user_rule;
          data.all_usergroups = result.data.usergroups.groups;
          data.form = this.getFormMapper().getFormFromModel(data);
          return deferred.resolve(data);
        }
        , () => deferred.reject());
      } else {
        this.Api.sendGet('/non_sys_usergroups').then((result) => {
          const data = {};
          data.user_rule = {};
          data.all_usergroups = result.data.groups;
          data.form = this.getFormMapper().getFormFromModel(data);
          return deferred.resolve(data);
        }
        , () => deferred.reject());
      }

      return deferred.promise;
    }


    /*
     * Saves a form model and merges model with list data
     *
     * @param {Object} model user_rule model
     * @param {Object} formModel  The model representing the form
     * @return {promise}
     */
    saveFormModel(model, formModel) {
      let promise;
      const mapper = this.getFormMapper();
      const postData = mapper.getPostDataFromForm(formModel);

      if (model.id) {
        promise = this.Api.sendPostJson(`/user_rules/${model.id}`, { user_rule: postData });
      } else {
        promise = this.Api.sendPutJson('/user_rules', { user_rule: postData }).success(data => model.id = data.id);
      }

      promise.success(() => {
        mapper.applyFormToModel(model, formModel);
        return this.mergeDataModel(model);
      });

      return promise;
    }
  }
  UserRules.initClass();
  return UserRules;
});
