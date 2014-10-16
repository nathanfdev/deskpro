define ->
	###
  #
  ###
	class DeskPRO_Service_Person
		
		_persons = []
		_maps =
			ids: {}
			agents: {}
			disabled: {}
			deleted: {}
			online: {}

		constructor: (@$http) ->

			@$http.get(BASE_URL + 'agent/person', {params: {is_agent: true}})
			.success (data, status, headers, config) =>

				data = data || []
				data.map (person) =>
					i = _persons.length
					_persons.push person
					_maps.ids[person.id] = i
					_maps.agents[person.id] = i # we load only agents for now

				console.info data

			.error (data, status, headers, config) =>
				console.error data, status


