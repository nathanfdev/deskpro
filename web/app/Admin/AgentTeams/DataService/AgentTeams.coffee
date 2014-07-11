define [
	'Admin/Main/DataService/BaseListEdit',
], (
	Admin_Main_DataService_BaseListEdit,
)  ->
	class Admin_AgentTeams_DataService_AgentTeams extends Admin_Main_DataService_BaseListEdit
		@$inject = ['Api', '$q']


		url: -> '/agent_teams'

		resolveResponse: (response) -> response.agent_teams
