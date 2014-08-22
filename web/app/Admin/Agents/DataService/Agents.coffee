define [
	'Admin/Main/DataService/BaseListEdit',
], (
	Admin_Main_DataService_BaseListEdit,
)  ->
	class Admin_Agents_DataService_Agents extends Admin_Main_DataService_BaseListEdit
		@$inject = ['Api', '$q']

		url: -> '/agents'

		resolveResponse: (response) ->
			models = []
			for data in response.agents
				data.agent.permissions = data.perms
				models.push data.agent

			models

		all: (reload) ->
			super (reload), {full: 1}
