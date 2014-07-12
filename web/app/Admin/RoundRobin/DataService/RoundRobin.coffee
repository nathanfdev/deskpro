define [
	'Admin/Main/DataService/BaseListEdit',
], (
	Admin_Main_DataService_BaseListEdit,
)  ->
	class Admin_RoundRobin_DataService_RoundRobin extends Admin_Main_DataService_BaseListEdit
		@$inject = ['Api', '$q']
		settings = null

		url: -> '/round_robin'

		getSettings: ->
			def = @$q.defer()

			if null != settings
				def.resolve settings
			else
				@Api.sendGet(@url() + '/settings').then (data) =>
					settings = data.data
					def.resolve settings

			def.promise


		saveSettings: ->
			@Api.sendPutJson(@url() + '/settings', settings)
