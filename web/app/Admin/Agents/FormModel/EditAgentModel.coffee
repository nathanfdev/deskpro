define ['DeskPRO/Util/Strings'], (Strings) ->
	class EditAgentModel
		constructor: (agent, groups, teams) ->
			@form = {}

			#--------------------
			# Basic props
			#--------------------

			@form.name = agent.name

			if agent.override_display_name
				@form.enable_display_name = true
				@form.override_name = agent.override_display_name
			else
				@form.enable_display_name = false
				@form.override_name = ''

			@form.email = agent.primary_email?.email

			@form.zones = {
				admin: agent.can_admin,
				reports: agent.can_reports
			}

			#--------------------
			# Teams
			#--------------------

			@form.teams = []
			for t in teams
				enabled = false
				for check in agent.teams
					if check.id = t.id
						enabled = true
						break

				@form.teams.push({
					id:    t.id,
					name:  t.name,
					value: enabled
				})

			#--------------------
			# Groups
			#--------------------

			@form.agent_groups = []
			for g in groups
				enabled = false
				for check in agent.usergroups
					if check.id = g.id
						enabled = true
						break

				@form.agent_groups.push({
					id:    g.id,
					title: g.title,
					value: enabled
				})


		getFormData: ->
			formData = {}
			formData.name = @form.name

			if @form.enable_display_name and Strings.trim(@form.override_name)
				formData.override_name = Strings.trim(@form.override_name)
			else
				formData.override_name = ''

			formData.email = @form.email

			formData.zones = []
			if @form.zones.admin   then formData.zones.push('admin')
			if @form.zones.reports then formData.zones.push('reports')

			formData.teams = []
			for t in @form.teams
				if t.value
					formData.teams.push(t.id)

			formData.agent_groups = []
			for g in @form.agent_groups
				if g.value
					formData.agent_groups.push(g.id)

			return formData