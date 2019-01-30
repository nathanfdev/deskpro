// TODO: This file was created by bulk-decaffeinate.
// Sanity-check the conversion and remove this comment.
/*
 * decaffeinate suggestions:
 * DS102: Remove unnecessary code created because of implicit returns
 * DS206: Consider reworking classes to avoid initClass
 * Full docs: https://github.com/decaffeinate/decaffeinate/blob/master/docs/suggestions.md
 */
define([
  'Admin/Main/DataService/BaseListEdit',
  'Admin/TwitterAccounts/TwitterAccountEditFormMapper'
], function(
  BaseListEdit,
  TwitterAccountEditFormMapper
)  {
  let Admin_TwitterAccounts_DataService_TwitterAccounts;
  return Admin_TwitterAccounts_DataService_TwitterAccounts = (function() {
    Admin_TwitterAccounts_DataService_TwitterAccounts = class Admin_TwitterAccounts_DataService_TwitterAccounts extends BaseListEdit {
      static initClass() {
        this.$inject = ['Api', '$q'];
      }

      _doLoadList() {
        const deferred = this.$q.defer();

        this.Api.sendGet('/twitter_accounts').success( data => {

          const models = data.twitter_accounts;
          return deferred.resolve(models);
        }
        , (data, status, headers, config) => deferred.reject());

        return deferred.promise;
      }

      /*
        * Remove a model
        *
        * @param {Integer} id twitter_account id
        * @return {promise}
      */
      deleteTwitterAccountById(id) {

        const promise = this.Api.sendDelete(`/twitter_accounts/${id}`).then(() => {
          return this.removeListModelById(id);
        });

        return promise;
      }

      /*
         * Get the form mapper
         *
         * @return {TwitterAccountEditFormMapper}
      */
      getFormMapper() {

        if (this.formMapper) { return this.formMapper; }
        this.formMapper = new TwitterAccountEditFormMapper();
        return this.formMapper;
      }

      /*
        * Get all data needed for the edit page
        *
        * @param {Integer} id twitter_account id
        * @return {promise}
      */
      loadEditTwitterAccountData(id) {

        const deferred = this.$q.defer();

        if (id) {

          this.Api.sendGet(`/twitter_accounts/${id}`).then( result => {

            const data = {};
            data.twitter_account = result.data.twitter_account;
            data.all_agents = result.data.twitter_account.all_agents;

            data.form = this.getFormMapper().getFormFromModel(data);

            return deferred.resolve(data);
          }
          , () => deferred.reject());

        } else {

          const data = {};
          data.twitter_account = {
            id: null,
            verified: false,
            user: {
              profile_image_url: '',
              name: '',
              screen_name: '',
              agents: {}
            }
          };

          data.form = this.getFormMapper().getFormFromModel(data);

          deferred.resolve(data);
        }

        return deferred.promise;
      }


      /*
        * Saves a form model and merges model with list data
        *
        * @param {Object} model twitter_account model
          * @param {Object} formModel  The model representing the form
        * @return {promise}
      */
      saveFormModel(model, formModel) {

        let promise;
        const mapper = this.getFormMapper();

        const postData = mapper.getPostDataFromForm(formModel);

        if (model.id) {
          promise = this.Api.sendPostJson(`/twitter_accounts/${model.id}`, {twitter_account: postData});
        } else {
          promise = this.Api.sendPutJson('/twitter_accounts', {twitter_account: postData}).success( data => model.id = data.id);
        }

        promise.success(() => {
          mapper.applyFormToModel(model, formModel);
          return this.mergeDataModel(model);
        });

        return promise;
      }
    };
    Admin_TwitterAccounts_DataService_TwitterAccounts.initClass();
    return Admin_TwitterAccounts_DataService_TwitterAccounts;
  })();
});