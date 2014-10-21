define ['Admin/Main/Ctrl/Base', 'moment'], (Admin_Ctrl_Base, moment) ->
	class Admin_EmailStatus_Ctrl_SendmailList extends Admin_Ctrl_Base
		@CTRL_ID = 'Admin_EmailStatus_Ctrl_SendmailList'
		@CTRL_AS = 'ListCtrl'
		@DEPS    = ['DpDateService']

		init: ->
			@filter = {
				page: 1
			}
			@results = []
			@num_results = 0
			@num_pages = 0
			@page_nums = [1]
			@filter_date_mode = "none"
			@page = 1
			@massActionsOp = "resend"

			@$scope.$watch('ListCtrl.page', (newVal, oldVal) =>
				if parseInt(newVal) == parseInt(oldVal)
					return
				if isNaN(parseInt(newVal))
					return

				@changePage()
			)

		initialLoad: ->
			return @loadResults()

		changePage: ->
			if @filter.page == @page
				return

			@filter.page = @page
			@loadResults()

		updateFilter: ->
			@page = 1
			@filter.page = @page

			@filter.date_start = null
			@filter.date_end = null
			if @filter_date_mode and @filter_date_mode != 'none'
				if @filter_date1 and (@filter_date_mode == 'between' || @filter_date_mode == 'after')
					@filter.date_start = moment(@filter_date1).format("YYYY-MM-DD")
				if @filter_date2 and (@filter_date_mode == 'between' || @filter_date_mode == 'before')
					@filter.date_end = moment(@filter_date2).format("YYYY-MM-DD")

			@loadResults()

		loadResults: (fallbackPrevPage) ->
			@startSpinner('loading_page')
			@results = []
			promise = @Api.sendGet('/email_status/sendmail', {filter: @filter}).success( (data) =>
				@stopSpinner('loading_page', true)
				@results     = data.sendmail_queue
				@page        = data.page
				@num_pages   = data.num_pages
				@num_results = data.count
				@massActions = {}
				@massActionsAll = false
				@massActionsLoading = false

				@page_nums = []
				for i in [0...@num_pages]
					@page_nums.push(i+1)

				@results.map (res) =>
					res.date_created = @DpDateService.local res.date_created
					if res.date_sent
						res.date_sent = @DpDateService.local res.date_sent
					if res.date_next_attempt
						res.date_next_attempt = @DpDateService.local res.date_next_attempt

				if fallbackPrevPage and !@results.length and data.page > 1
					@filter.page = data.page - 1;
					@loadResults()
			)

			return promise

		toggleMassActions: ->
			@massActions = {}
			if @massActionsAll
				for r in @results
					@massActions[r.id] = true

		hasAnyMassActions: ->
			for r in @results
				return true if @massActions[r.id]
			return false

		performMassActions: ->
			url = "/email_status/sendmail/mass-actions/#{@massActionsOp}"
			@massActionsLoading = true

			ids = []
			for r in @results
				if @massActions[r.id] then ids.push(r.id)

			@Api.sendPostJson(url, { ids: ids }).then(=>
				@Growl.success(@getRegisteredMessage("#{@massActionsOp}_done"))
				@loadResults(true)
			)

		goPrevPage: ->
			@page--

		goNextPage: ->
			@page++

	Admin_EmailStatus_Ctrl_SendmailList.EXPORT_CTRL()