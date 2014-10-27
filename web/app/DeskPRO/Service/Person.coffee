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



		constructor: (@$http, @$q) ->



		_load: () ->
			d = @$q.defer()

			if _persons.length
				d.resolve _persons
			else
				@$http.get(BASE_URL + 'agent/person', {params: {is_agent: true}})
				.success (data, status, headers, config) =>

					data = data || []
					data.map (person) =>
						i = _persons.length
						_persons.push person
						_maps.ids[person.id] = i
						_maps.agents[person.id] = i # we load only agents for now
					d.resolve _persons

				.error (data, status, headers, config) =>
					console.error data, status
					d.reject()

			d.promise




		find: (term, limit) ->
			limit = limit || 10
			term = '' if !term?
			term = term.toLowerCase()
			d = @$q.defer()

			@_load().then () =>
				res = []
				for person in _persons
					break if res.length >= limit
					res.push person if !term.length || person.display_name.toLowerCase().indexOf(term) > -1

				d.resolve res

			d.promise



