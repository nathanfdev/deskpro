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
  'Admin/UserGroups/UserGroupEditFormMapper'
], function(
  BaseListEdit,
  UserGroupEditFormMapper
)  {
  let UserGroups;
  return UserGroups = (function() {
    UserGroups = class UserGroups extends BaseListEdit {
      static initClass() {
        this.$inject = ['Api', '$q'];
      }

      _doLoadList() {
        const deferred = this.$q.defer();

        this.Api.sendGet('/user_groups').success( data => {
          const models = data.groups;
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
      deleteUserGroupById(id) {
        const promise = this.Api.sendDelete(`/user_groups/${id}`).success( () => {
          return this.removeListModelById(id);
        });
        return promise;
      }


      /*
       * Get the form mapper
       *
       * @return {UserGroupEditFormMapper}
       */
      getFormMapper() {
        if (this.formMapper) { return this.formMapper; }
        this.formMapper = new UserGroupEditFormMapper();
        return this.formMapper;
      }


      /*
       * Get all data needed for the edit page. Also returns the "reg_group" usergroup info as well.
       *
       * @param {Integer} id
       * @return {promise}
       */
      loadEditUserGroupData(id) {
        const deferred = this.$q.defer();

        const sendTypes = {
          everyone_group: '/user_groups/everyone',
          reg_group:      '/user_groups/registered'
        };
        if (id && (id !== 1)) {
          sendTypes.group = `/user_groups/${id}`;
        }

        this.Api.sendDataGet(sendTypes).then( result => {
          const data = {};

          data.everyone_group = result.data.everyone_group.group;
          data.reg_group      = result.data.reg_group.group;

          if (result.data.group) {
            data.group = result.data.group.group;
            data.form = this.getFormMapper().getFormFromModel(data);
          } else {
            data.group = { id: null, title: '', is_enabled: true};
            data.group.perms = {};
            data.form = this.getFormMapper().getFormFromModel(data);
          }

          return deferred.resolve(data);
        }
        , () => deferred.reject());

        return deferred.promise;
      }


      /*
       * Saves a form model and merges model with list data
       *
       * @param {Object} model api_key model
       * @param {Object} formModel  The model representing the form
       * @return {promise}
       */
      saveFormModel(model, formModel, formPermsModel) {
        let promise;
        const mapper = this.getFormMapper();
        formModel.deps_perms = model.deps_perms;
        const postData = mapper.getPostDataFromForm(formModel, formPermsModel);

        if (model.id) {
          promise = this.Api.sendPostJson(`/user_groups/${model.id}`, {group: postData});
        } else {
          promise = this.Api.sendPutJson('/user_groups', {group: postData}).success( data => model.id = data.id);
        }

        promise.success( () => {
          mapper.applyFormToModel(model, formModel);
          return this.mergeDataModel(model);
        });

        return promise;
      }


      /*
        * Removes group by id
        *
        * @param {Integer} groupId
        * @return promise
      */
      removeGroupById(groupId) {
        const p = this.Api.sendDelete(`/user_groups/${groupId}`);
        p.then(() => {
          return this.removeListModelById(groupId);
        });

        return p;
      }

      url() { return 'user_groups'; }

      resolveResponse(response) { return response.groups; }
    };
    UserGroups.initClass();
    return UserGroups;
  })();
});
