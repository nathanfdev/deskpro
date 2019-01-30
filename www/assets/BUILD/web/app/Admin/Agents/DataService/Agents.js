/*
 * decaffeinate suggestions:
 * DS101: Remove unnecessary use of Array.from
 * DS102: Remove unnecessary code created because of implicit returns
 * DS206: Consider reworking classes to avoid initClass
 * Full docs: https://github.com/decaffeinate/decaffeinate/blob/master/docs/suggestions.md
 */
define([
  'Admin/Main/DataService/BaseListEdit',
], function(
  Admin_Main_DataService_BaseListEdit,
)  {
  let Admin_Agents_DataService_Agents;
  return Admin_Agents_DataService_Agents = (function() {
    Admin_Agents_DataService_Agents = class Admin_Agents_DataService_Agents extends Admin_Main_DataService_BaseListEdit {
      static initClass() {
        this.$inject = ['Api', '$q'];
      }

      url() { return '/agents'; }

      resolveResponse(response) {
        const models = [];
        for (let data of Array.from(response.agents)) {
          models.push(data);
        }

        return models;
      }

      all(reload) {
        return super.all((reload), {basic: 1});
      }
    };
    Admin_Agents_DataService_Agents.initClass();
    return Admin_Agents_DataService_Agents;
  })();
});