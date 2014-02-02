define [
	'Reports/Main/Ctrl/Base',
	'DeskPRO/Util/Util',
], (
	ReportsBaseCtrl,
	Util,
) ->
	class Reports_Builder_Ctrl_List extends ReportsBaseCtrl
		@CTRL_ID = 'Reports_Builder_Ctrl_List'
		@CTRL_AS = 'ListCtrl'

		init: ->
			@customData = @DataService.get('ReportBuilderCustom')
			@builtInData = @DataService.get('ReportBuilderBuiltIn')

			
		###
		# Loads 2 lists - first with custom reports, second with built-in reports
		###
		initialLoad: ->

			custom_promise = @customData.loadList().then( (list) =>
				@custom_data_list = list
			)
			built_in_promise = @builtInData.loadList().then( (list) =>
				@built_in_data_list = list
			)

			return @$q.all([custom_promise, built_in_promise])

	Reports_Builder_Ctrl_List.EXPORT_CTRL()