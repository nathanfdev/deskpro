define [
	'Reports/Main/Ctrl/Base',
	'DeskPRO/Util/Util',
], (
	ReportsBaseCtrl,
	Util,
) ->
	class Reports_Overview_Ctrl_Overview extends ReportsBaseCtrl
		@CTRL_ID   = 'Reports_Overview_Ctrl_Overview'
		@CTRL_AS   = 'Overview'

		###
		# Just doing all the necessary AJAX calls here
		###
		initialLoad: ->
			data_promise = @Api.sendDataGet({
				tickets_status:            "/reports/overview/data/tickets_status"
				tickets_awaiting_agent:    "/reports/overview/data/tickets_awaiting_agent"
				tickets_user_waiting_time: "/reports/overview/data/tickets_user_waiting_time"
				tickets_resolved:          "/reports/overview/data/tickets_resolved"
				tickets_response_time:     "/reports/overview/data/tickets_response_time"
				tickets_sla_status:        "/reports/overview/data/tickets_sla_status"
				tickets_opened_hour:       "/reports/overview/data/tickets_opened_hour"
				chats_created:             "/reports/overview/data/chats_created"
			}).then( (res) =>
				@$scope.tickets_status            = res.data.tickets_status
				@$scope.tickets_awaiting_agent    = res.data.tickets_awaiting_agent
				@$scope.tickets_user_waiting_time = res.data.tickets_user_waiting_time
				@$scope.tickets_resolved          = res.data.tickets_resolved
				@$scope.tickets_response_time     = res.data.tickets_response_time
				@$scope.tickets_sla_status        = res.data.tickets_sla_status
				@$scope.tickets_opened_hour       = res.data.tickets_opened_hour
				@$scope.chats_created             = res.data.chats_created

				@setDataForBarGraphs('tickets_status')
				@setDataForBarGraphs('tickets_awaiting_agent')
				@setDataForBarGraphs('tickets_resolved')
				@setDataForBarGraphs('tickets_sla_status')
				@setDataForBarGraphs('chats_created')

				@setDataForTicketsOpenedHours()

				@setDataForTableWithBarGraphs('tickets_user_waiting_time')
				@setDataForTableWithBarGraphs('tickets_response_time')
			)

			@$q.all([data_promise])


		###
		# This method is used in select boxes for defining grouping field and / or other search parameters
		# @param {String} data_key - using this key data is looked in @$scope
		###
		getStats: (data_key) ->
			@toggleLoadingState(data_key)

			promise = @Api.sendGet('/reports/overview/get-stats/' + data_key, {
				grouping_field: @$scope[data_key].grouping_field
				date_choice: @$scope[data_key].date_choice
				sla_id: @$scope[data_key].sla_id
			})

			promise.success((data) =>
				@toggleLoadingState(data_key)
				@$scope[data_key] = data

				if data_key == 'tickets_user_waiting_time' or data_key == 'tickets_response_time'
					@setDataForTableWithBarGraphs(data_key)
				else if data_key == 'tickets_opened_hour'
					@setDataForTicketsOpenedHours()
				else
					@setDataForBarGraphs(data_key)
			)


		###
		# Used for hiding / showing AJAX loader
		###
		toggleLoadingState: (data_key) ->
			@$scope[data_key].loading = !@$scope[data_key].loading


		###
		# We need to display bar graphs - so let's pre-calculate some variables
		# @param {String} data_key - using this key data is looked in @$scope
		###
		setDataForBarGraphs: (data_key) ->
			@$scope[data_key].empty = true if Util.isEmpty(@$scope[data_key].values)
			@$scope[data_key].stats = []
			denominator = @$scope[data_key].max || 1

			for key of @$scope[data_key].titles when @$scope[data_key].values[key]

				percentage = @$scope[data_key].values[key] / denominator * 100
				percentage = 1 if percentage < 1

				@$scope[data_key].stats.push({
					title: @$scope[data_key].titles[key]
					value: @$scope[data_key].values[key] || 0
					left_percentage: percentage
					right_percentage: 100 - percentage
				})


		###
		# We need to display bar graphs - so let's pre-calculate some variables
		# This method is special case of @setDataForBarGraphs()
		###
		setDataForTicketsOpenedHours: ->
			@$scope.tickets_opened_hour.empty = true if Util.isEmpty(@$scope.tickets_opened_hour.values)
			@$scope.tickets_opened_hour.stats = []
			@$scope.tickets_opened_hour.column_width = 100 / Object.keys(@$scope.tickets_opened_hour.titles).length
			denominator = @$scope.tickets_opened_hour.max || 1

			for key of @$scope.tickets_opened_hour.titles

				if @$scope.tickets_opened_hour.values[key]

					percentage = @$scope.tickets_opened_hour.values[key] / denominator * 100
					percentage = 1 if percentage < 1

					@$scope.tickets_opened_hour.stats.push({
						title: @$scope.tickets_opened_hour.titles[key]
						value: @$scope.tickets_opened_hour.values[key] || 0
						percentage: percentage
					})
				else
					@$scope.tickets_opened_hour.stats.push({
						title: @$scope.tickets_opened_hour.titles[key]
					})


		###
		# We need to display bar graphs - so let's pre-calculate some variables
		# What is special here - we display every piece of data
		# Just for cases with no data we display only labels without graphical bars
		# Ie. if we have 0 tickets created < 5 minutes ago, we still display '< 5 minutes' label, but without bar
		# This leads to the situation that we have to iterate over all the '@$scope.tickets_user_waiting_time.titles' array
		# @param {String} data_key - using this key data is looked in @$scope
		###
		setDataForTableWithBarGraphs: (data_key) ->
			@$scope[data_key].empty = true if Util.isEmpty(@$scope[data_key].values)
			@$scope[data_key].stats = []
			denominator = @$scope[data_key].max || 1

			for key of @$scope[data_key].titles

				percentage = @$scope[data_key].values[key] / denominator * 100
				percentage = 1 if percentage < 1

				# case of simple data without sub-data

				if not @$scope[data_key].sub_titles

					if @$scope[data_key].values[key]
						@$scope[data_key].stats.push({
							title: @$scope[data_key].titles[key]
							value: @$scope[data_key].values[key] || 0
							percentage: percentage
						})
					else
						@$scope[data_key].stats.push({
							title: @$scope[data_key].titles[key]
						})

				else

					# case of more sophisticated case with sub-data

					percentage = @$scope[data_key].group_total[key] / denominator * 100
					percentage = 1 if percentage < 1

					if @$scope[data_key].group_total[key]
						sub_stats = []

						for subid, subtitle of @$scope[data_key].sub_titles when @$scope[data_key].values[key][subid]
							sub_percentage = @$scope[data_key].values[key][subid] / @$scope[data_key].group_total[key] * 100
							sub_percentage = 1 if sub_percentage < 1
							sub_stats.push({
								title: subtitle + ' (' + @$scope[data_key].values[key][subid] + ')'
								percentage: sub_percentage
								background: @$scope[data_key].group_keys[subid]
							})

						@$scope[data_key].stats.push({
							title: @$scope[data_key].titles[key]
							value: @$scope[data_key].group_total[key] || 0
							percentage: percentage
							sub_stats: sub_stats
						})
					else
						@$scope[data_key].stats.push({
							title: @$scope[data_key].titles[key]
						})

	Reports_Overview_Ctrl_Overview.EXPORT_CTRL()