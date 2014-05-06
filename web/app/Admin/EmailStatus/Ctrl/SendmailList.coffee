define ['Admin/Main/Ctrl/Base', 'moment'], (Admin_Ctrl_Base, moment) ->
	class Admin_EmailStatus_Ctrl_SendmailList extends Admin_Ctrl_Base
		@CTRL_ID = 'Admin_EmailStatus_Ctrl_SendmailList'
		@CTRL_AS = 'ListCtrl'
		@DEPS    = []

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

		loadResults: ->
			promise = @Api.sendGet('/email_status/sendmail', {filter: @filter}).success( (data) =>
				@results     = data.sendmail_queue
				@filter.page = data.page
				@page        = data.page
				@num_pages   = data.num_pages
				@num_results = data.count

				@page_nums = []
				for i in [0...@num_pages]
					@page_nums.push(i+1)
			)

			return promise

	Admin_EmailStatus_Ctrl_SendmailList.EXPORT_CTRL()