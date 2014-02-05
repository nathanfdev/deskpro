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
		@DEPS    = ['Api']

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
			group_params_promise = @Api.sendGet('/reports/builder/group-params').then( (data) =>
				@group_params = data.data
			)

			return @$q.all([custom_promise, built_in_promise, group_params_promise])

	Reports_Builder_Ctrl_List.EXPORT_CTRL()