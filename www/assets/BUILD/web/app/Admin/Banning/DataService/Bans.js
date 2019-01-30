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
  'Admin/Banning/IpBanEditFormMapper',
  'Admin/Banning/EmailBanEditFormMapper',
], function(
  BaseListEdit,
  IpBanEditFormMapper,
  EmailBanEditFormMapper,
)  {
  let Bans;
  return Bans = (function() {
    Bans = class Bans extends BaseListEdit {
      static initClass() {
        this.$inject = ['Api', '$q'];
        this.type = 'ip';
      }

      /*
       *
       */

      init() {
        this.setSubLists(['ip_bans', 'email_bans']);
        return this.search_phrase = {
          ip_ban: '',
          email_ban: '',
          email_wildcard: false
        };
      }

      /*
    *
    */

      _doLoadList() {

        const deferred = this.$q.defer();

        this.Api.sendGet('/banning').success( data => {

          const models = data.bans;
          return deferred.resolve(models);
        }
        , (data, status, headers, config) => deferred.reject());

        return deferred.promise;
      }

      /*
       *
       */

      _doRefreshList() {

        const deferred = this.$q.defer();

        this.Api.sendGet('/banning', {

          ip_ban_page: this.pagination.ip_bans.page,
          email_ban_page: this.pagination.email_bans.page,
          ip_ban_search_phrase: this.search_phrase.ip_ban,
          email_ban_search_phrase: this.search_phrase.email_ban,
          email_ban_wildcard: this.search_phrase.email_wildcard ? 1 : 0

        }).success( data => {

          const models = data.bans;
          return deferred.resolve(models);
        }
        , (data, status, headers, config) => deferred.reject());

        return deferred.promise;
      }

      /*
    * Returns object representing the search string defined via user UI
    */

      getSearchPhrase() {

        return this.search_phrase;
      }

      /*
    * Sets type of ban that is used for create / update / delete operations
      *
    * @param {string} type
    */

      setType(type) {

        this.type = type;
        return this.idProp = this.type === 'email' ? `banned_${this.type}` : 'id';
      }

      /*
       * Get the form mapper
       *
       * @return {IpBanEditFormMapper|EmailBanEditFormMapper}
       */

      getFormMapper() {

        if (this.type === 'ip') { this.formMapper = new IpBanEditFormMapper(); }
        if (this.type === 'email') { this.formMapper = new EmailBanEditFormMapper(); }

        return this.formMapper;
      }

      /*
       * Remove complete list
       *
       * @param {String} "email"|"ip"
       * @return {promise}
       */

      deleteBanByType(type) {

        return this.Api.sendDelete(`/banning_${type}`);
      }

      /*
    * Remove a model
    *
    * @param {Integer} id
    * @return {promise}
      */

      deleteBanById(id) {

        const promise = this.Api.sendDelete(`/banning_${this.type}/${window.encodeURIComponent(id)}`).success( () => {
          return this.removeListModelById(id);
        });

        return promise;
      }

      /*
    * Get all data needed for the edit page
    *
    * @param {String} id
    * @return {promise}
      */

      loadEditBanData(id) {

        const deferred = this.$q.defer();

        if (id) {

          this.Api.sendGet(`/banning_${this.type}/${window.encodeURIComponent(id)}`).then( result => {

            const data = {};
            data[this.type + '_ban'] = result.data[this.type + '_ban'];
            data[this.type + '_ban'].old_id = result.data[this.type + '_ban'][this.idProp];

            data.form = this.getFormMapper().getFormFromModel(data);

            return deferred.resolve(data);
          }
          , () => deferred.reject());

        } else {

          const data = {};
          data[this.type + '_ban'] = {};

          data.form = this.getFormMapper().getFormFromModel(data);

          deferred.resolve(data);
        }

        return deferred.promise;
      }

      /*
    * Saves a form model and merges model with list data
    *
    * @param {Object} model
    * @param {Object} formModel  The model representing the form
    * @return {promise}
      */

      saveFormModel(model, formModel) {

        let promise;
        const mapper = this.getFormMapper();

        const postData = mapper.getPostDataFromForm(formModel);

        const sendData = {};
        sendData[this.type + '_ban'] = postData;
      
        if (model[`banned_${this.type}`]) {
          const url = `/banning_${this.type}/${window.encodeURIComponent(this.type === 'email' ? model[`banned_${this.type}`] : model['id'])}`;
          promise = this.Api.sendPostJson(url, sendData).success(data => {
            return model[`banned_${this.type}`] = data[`banned_${this.type}`];
          });
        } else {
          promise = this.Api.sendPutJson(`/banning_${this.type}`, sendData).success( data => {
            return model[`banned_${this.type}`] = data[`banned_${this.type}`];
          });
        }

        promise.success( () => {
          mapper.applyFormToModel(model, formModel);
          return this.mergeDataModel(model, null, this.type + '_bans');
        });

        return promise;
      }
    };
    Bans.initClass();
    return Bans;
  })();
});