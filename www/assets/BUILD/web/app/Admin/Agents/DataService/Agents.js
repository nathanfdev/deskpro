define([
  'Admin/Main/DataService/BaseListEdit'
], function(
  Admin_Main_DataService_BaseListEdit
)  {
  class Admin_Agents_DataService_Agents extends Admin_Main_DataService_BaseListEdit {
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
  }
  Admin_Agents_DataService_Agents.initClass();
  return Admin_Agents_DataService_Agents;
});
