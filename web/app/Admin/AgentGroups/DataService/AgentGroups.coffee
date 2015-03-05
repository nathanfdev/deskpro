define [
  'Admin/Main/DataService/BaseListEdit',
], (
  Admin_Main_DataService_BaseListEdit,
)  ->
  class Admin_AgentGroups_DataService_AgentGroups extends Admin_Main_DataService_BaseListEdit
    @$inject = ['Api', '$q']

    url: -> 'agent_groups'

    resolveResponse: (response) -> response.groups

    all: ->
      super false, {with_perms: 1}
