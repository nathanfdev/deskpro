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
			@builderData = @DataService.get('ReportBuilder')

			
		###
		# Loads the list
		###
		initialLoad: ->

			promise = @builderData.loadList().then( (list) =>
				@list = list
			)

			return promise

	Reports_Builder_Ctrl_List.EXPORT_CTRL()