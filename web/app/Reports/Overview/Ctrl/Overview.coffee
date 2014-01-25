define [
	'Reports/Main/Ctrl/Base'
], (
	ReportsBaseCtrl
) ->
	class Reports_Overview_Ctrl_Overview extends ReportsBaseCtrl
		@CTRL_ID   = 'Reports_Overview_Ctrl_Overview'
		@CTRL_AS   = 'Overview'
		@DEPS      = []


		###
		#
		###
		init: ->

			return


		###
		# Just doing all the necessary AJAX calls here
		###
		initialLoad: ->
			data_promise = @Api.sendDataGet({
				tickets_status:            "/reports/overview/data/tickets_status",
				tickets_awaiting_agent:    "/reports/overview/data/tickets_awaiting_agent",
				tickets_user_waiting_time: "/reports/overview/data/tickets_user_waiting_time",
				tickets_resolved:          "/reports/overview/data/tickets_resolved",
				tickets_response_time:     "/reports/overview/data/tickets_response_time",
				chats_created:             "/reports/overview/data/chats_created",
			}).then( (res) =>
				@$scope.tickets_status            = res.data.tickets_status
				@$scope.tickets_awaiting_agent    = res.data.tickets_awaiting_agent
				@$scope.tickets_user_waiting_time = res.data.tickets_user_waiting_time
				@$scope.tickets_resolved          = res.data.tickets_resolved
				@$scope.tickets_response_time     = res.data.tickets_response_time
				@$scope.chats_created             = res.data.chats_created

				@setVariablesForTicketsStatuses()
				@setVariablesForTicketsAwaitingAgent()
				@setVariablesForTicketsUserWaitingTime()
				@setVariablesForTicketsResolved()
				@setVariablesForTicketsResponseTime()
				@setVariablesForChatsCreated()
			)

			@$q.all([data_promise])


		###
		# We need to display bar graphs - so let's pre-calculate some variables
		###
		setVariablesForTicketsStatuses: ->
			@$scope.tickets_status.stats = []
			denominator = @$scope.tickets_status.max || 1

			for key of @$scope.tickets_status.titles when @$scope.tickets_status.values[key]

				percentage = @$scope.tickets_status.values[key] / denominator * 100
				percentage = 1 if percentage < 1

				@$scope.tickets_status.stats.push({
					title: @$scope.tickets_status.titles[key]
					value: @$scope.tickets_status.values[key] || 0
					left_percentage: percentage
					right_percentage: 100 - percentage
				})


		###
		# We need to display bar graphs - so let's pre-calculate some variables
		###
		setVariablesForTicketsAwaitingAgent: ->
			@$scope.tickets_awaiting_agent.stats = []
			denominator = @$scope.tickets_awaiting_agent.max || 1

			for key of @$scope.tickets_awaiting_agent.titles when @$scope.tickets_awaiting_agent.values[key]

				percentage = @$scope.tickets_awaiting_agent.values[key] / denominator * 100
				percentage = 1 if percentage < 1

				@$scope.tickets_awaiting_agent.stats.push({
					title: @$scope.tickets_awaiting_agent.titles[key]
					value: @$scope.tickets_awaiting_agent.values[key] || 0
					left_percentage: percentage
					right_percentage: 100 - percentage
				})


		###
		# We need to display bar graphs - so let's pre-calculate some variables
		###
		setVariablesForChatsCreated: ->
			@$scope.chats_created.stats = []
			denominator = @$scope.chats_created.max || 1

			for key of @$scope.chats_created.titles when @$scope.chats_created.values[key]

				percentage = @$scope.chats_created.values[key] / denominator * 100
				percentage = 1 if percentage < 1

				@$scope.chats_created.stats.push({
					title: @$scope.chats_created.titles[key]
					value: @$scope.chats_created.values[key] || 0
					left_percentage: percentage
					right_percentage: 100 - percentage
				})


		###
		# We need to display bar graphs - so let's pre-calculate some variables
		###
		setVariablesForTicketsResolved: ->
					@$scope.tickets_resolved.stats = []
					denominator = @$scope.tickets_resolved.max || 1

					for key of @$scope.tickets_resolved.titles when @$scope.tickets_resolved.values[key]

						percentage = @$scope.tickets_resolved.values[key] / denominator * 100
						percentage = 1 if percentage < 1

						@$scope.tickets_resolved.stats.push({
							title: @$scope.tickets_resolved.titles[key]
							value: @$scope.tickets_resolved.values[key] || 0
							left_percentage: percentage
							right_percentage: 100 - percentage
						})


		###
		# We need to display bar graphs - so let's pre-calculate some variables
		# What is special here - we display every piece of data
		# Just for cases with no data we display only labels without graphical bars
		# Ie. if we have 0 tickets created < 5 minutes ago, we still display '< 5 minutes' label, but without bar
		# This leads to the situation that we have to iterate over all the '@$scope.tickets_user_waiting_time.titles' array
		###
		setVariablesForTicketsUserWaitingTime: ->
			@$scope.tickets_user_waiting_time.stats = []
			denominator = @$scope.tickets_user_waiting_time.max || 1

			for key of @$scope.tickets_user_waiting_time.titles

				percentage = @$scope.tickets_user_waiting_time.values[key] / denominator * 100
				percentage = 1 if percentage < 1

				# case of simple data without sub-data

				if not @$scope.tickets_user_waiting_time.sub_titles

					if @$scope.tickets_user_waiting_time.values[key]
						@$scope.tickets_user_waiting_time.stats.push({
							title: @$scope.tickets_user_waiting_time.titles[key]
							value: @$scope.tickets_user_waiting_time.values[key] || 0
							percentage: percentage
						})
					else
						@$scope.tickets_user_waiting_time.stats.push({
							title: @$scope.tickets_user_waiting_time.titles[key]
						})

				else

					# case of more sophisticated case with sub-data

					percentage = @$scope.tickets_user_waiting_time.group_total[key] / denominator * 100
					percentage = 1 if percentage < 1

					if @$scope.tickets_user_waiting_time.group_total[key]
						sub_stats = []

						for subid, subtitle of @$scope.tickets_user_waiting_time.sub_titles when @$scope.tickets_user_waiting_time.values[key][subid]
							sub_percentage = @$scope.tickets_user_waiting_time.values[key][subid] / @$scope.tickets_user_waiting_time.group_total[key] * 100
							sub_percentage = 1 if sub_percentage < 1
							sub_stats.push({
								title: subtitle + ' (' + @$scope.tickets_user_waiting_time.values[key][subid] + ')'
								percentage: sub_percentage
								background: @$scope.tickets_user_waiting_time.group_keys[subid]
							})

						@$scope.tickets_user_waiting_time.stats.push({
							title: @$scope.tickets_user_waiting_time.titles[key]
							value: @$scope.tickets_user_waiting_time.group_total[key] || 0
							percentage: percentage
							sub_stats: sub_stats
						})
					else
						@$scope.tickets_user_waiting_time.stats.push({
							title: @$scope.tickets_user_waiting_time.titles[key]
						})

		###
		# We need to display bar graphs - so let's pre-calculate some variables
		# What is special here - we display every piece of data
		# Just for cases with no data we display only labels without graphical bars
		# Ie. if we have 0 tickets created < 5 minutes ago, we still display '< 5 minutes' label, but without bar
		# This leads to the situation that we have to iterate over all the '@$scope.tickets_user_waiting_time.titles' array
		###
		setVariablesForTicketsResponseTime: ->
			@$scope.tickets_response_time.stats = []
			denominator = @$scope.tickets_response_time.max || 1

			for key of @$scope.tickets_response_time.titles

				percentage = @$scope.tickets_response_time.values[key] / denominator * 100
				percentage = 1 if percentage < 1

				# case of simple data without sub-data

				if not @$scope.tickets_response_time.sub_titles

					if @$scope.tickets_response_time.values[key]
						@$scope.tickets_response_time.stats.push({
							title: @$scope.tickets_response_time.titles[key]
							value: @$scope.tickets_response_time.values[key] || 0
							percentage: percentage
						})
					else
						@$scope.tickets_response_time.stats.push({
							title: @$scope.tickets_response_time.titles[key]
						})

				else

					# case of more sophisticated case with sub-data

					percentage = @$scope.tickets_response_time.group_total[key] / denominator * 100
					percentage = 1 if percentage < 1

					if @$scope.tickets_response_time.group_total[key]
						sub_stats = []

						for subid, subtitle of @$scope.tickets_response_time.sub_titles when @$scope.tickets_response_time.values[key][subid]
							sub_percentage = @$scope.tickets_response_time.values[key][subid] / @$scope.tickets_response_time.group_total[key] * 100
							sub_percentage = 1 if sub_percentage < 1
							sub_stats.push({
								title: subtitle + ' (' + @$scope.tickets_response_time.values[key][subid] + ')'
								percentage: sub_percentage
								background: @$scope.tickets_response_time.group_keys[subid]
							})

						@$scope.tickets_response_time.stats.push({
							title: @$scope.tickets_response_time.titles[key]
							value: @$scope.tickets_response_time.group_total[key] || 0
							percentage: percentage
							sub_stats: sub_stats
						})
					else
						@$scope.tickets_response_time.stats.push({
							title: @$scope.tickets_response_time.titles[key]
						})

	Reports_Overview_Ctrl_Overview.EXPORT_CTRL()