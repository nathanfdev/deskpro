define ->
	###
  #
  ###
	class DeskPRO_Service_AgentTeam

		_teams = []
		_maps =
			ids: {}



		constructor: (@$http, @$q) ->



		_load: () ->
			d = @$q.defer()

			if _teams.length
				d.resolve _teams
			else
				@$http.get(BASE_URL + 'agent/agent_team', {params: {}})
				.success (data, status, headers, config) =>

					data = data || []
					data.map (team) =>
						i = _teams.length
						_teams.push team
						_maps.ids[team.id] = i
					d.resolve _teams

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
				for team in _teams
					break if res.length >= limit
					res.push team if !term.length || team.name.toLowerCase().indexOf(term) > -1

				d.resolve res

			d.promise



