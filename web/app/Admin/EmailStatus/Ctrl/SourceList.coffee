define ['Admin/Main/Ctrl/Base', 'moment'], (Admin_Ctrl_Base, moment) ->
	class Admin_EmailStatus_Ctrl_SourceList extends Admin_Ctrl_Base
		@CTRL_ID = 'Admin_EmailStatus_Ctrl_SourceList'
		@CTRL_AS = 'ListCtrl'
		@DEPS    = []

		init: ->
			@filter = {
				account: "0",
				page: 1
			}
			@results = []
			@num_results = 0
			@num_pages = 0
			@page_nums = [1]
			@filter_date_mode = "none"
			@page = 1

			@$scope.$watch('ListCtrl.page', (newVal, oldVal) =>
				if parseInt(newVal) == parseInt(oldVal)
					return
				if isNaN(parseInt(newVal))
					return

				@changePage()
			)

		initialLoad: ->
			p1 = @loadResults()
			p2 = @Api.sendGet('/email_accounts').success( (data) =>
				@$scope.email_accounts = data.email_accounts
			)

			return @$q.all([p1, p2])

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

		loadResults: ->
			@startSpinner('loading_page')
			@results = []
			promise = @Api.sendGet('/email_status/sources', {filter: @filter}).success( (data) =>
				@stopSpinner('loading_page', true)
				@results     = data.email_sources
				@filter.page = data.page
				@page        = data.page
				@num_pages   = data.num_pages
				@num_results = data.count

				@page_nums = []
				for i in [0...@num_pages]
					@page_nums.push(i+1)
			)

			return promise

		goPrevPage: ->
			@page--

		goNextPage: ->
			@page++

	Admin_EmailStatus_Ctrl_SourceList.EXPORT_CTRL()