define [
	'Admin/Main/DataService/BaseListEdit',
	'angular'
], (
	Admin_Main_DataService_BaseListEdit,
	angular
)  ->
	class Admin_RoundRobin_DataService_RoundRobin extends Admin_Main_DataService_BaseListEdit
		@$inject = ['Api', '$q']
		settings = {}

		url: -> '/round_robin'

		getSettings: (reload) ->
			def = @$q.defer()

			if settings.enabled? and !reload?
				def.resolve settings
			else
				@Api.sendGet(@url() + '/settings').then (data) =>
					angular.copy data.data, settings
					def.resolve settings

			def.promise


		saveSettings: ->
			@Api.sendPutJson(@url() + '/settings', settings)



		checkTriggers: (id) ->
			url = @url() + '/triggers'
			url += "/#{id}" if id?
			def = @$q.defer()

			@Api.sendGet(url).then (data) =>
				def.resolve data.data

			def.promise
