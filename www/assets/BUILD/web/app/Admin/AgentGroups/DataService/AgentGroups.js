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
], function(
  Admin_Main_DataService_BaseListEdit,
)  {
  let Admin_AgentGroups_DataService_AgentGroups;
  return Admin_AgentGroups_DataService_AgentGroups = (function() {
    Admin_AgentGroups_DataService_AgentGroups = class Admin_AgentGroups_DataService_AgentGroups extends Admin_Main_DataService_BaseListEdit {
      static initClass() {
        this.$inject = ['Api', '$q'];
      }

      url() { return 'agent_groups'; }

      resolveResponse(response) { return response.groups; }

      all() {
        return super.all(false, {with_perms: 1});
      }
    };
    Admin_AgentGroups_DataService_AgentGroups.initClass();
    return Admin_AgentGroups_DataService_AgentGroups;
  })();
});
