define [
	'Reports/Main/Ctrl/Base',
], (
	ReportsBaseCtrl,
) ->
	class Reports_Builder_Ctrl_Edit extends ReportsBaseCtrl
		@CTRL_ID   = 'Reports_Builder_Ctrl_Edit'
		@CTRL_AS   = 'EditCtrl'
		@DEPS      = ['$stateParams', '$sce', 'Api']

		init: ->
			if(@$stateParams.type == 'builtIn')
				@reportData = @DataService.get('ReportBuilderBuiltIn')
				@reportType = 'builtIn'
			if(@$stateParams.type == 'custom')
				@reportData = @DataService.get('ReportBuilderCustom')
				@reportType = 'custom'

			@report = null
			@query_parts = null
			@rendered_result = null
			@query_error = null
			@show_query_editor = false

		###
 	#
 	###
		initialLoad: ->
			promise = @reportData.loadEditReportData(@$stateParams.id || null, @$stateParams.params || null).then( (data) =>

				@rendered_result = @$sce.trustAsHtml(data.rendered_result)
				@group_params = @$scope.$parent.ListCtrl.group_params
				@query_parts = data.query_parts
				@report  = data.report
				@form = data.form
			)
			return promise


		###
 	# Shows / hides query editor
 	###
 	toggleQueryEditor: ->
			@show_query_editor = !@show_query_editor


		###
 	# This method is called when user clicks button named 'Test' in query builder form
 	###
		testReport: ->
			promise = @Api.sendPostJson('/reports/builder/test/' + @report.id, {
				parts: @query_parts
			})

			promise.success((data) =>
				if data.error then @query_error = data.error

				if data.rendered_result
					@query_error = null
					@rendered_result = @$sce.trustAsHtml(data.rendered_result)
			)


		###
		#
 	###
		saveForm: ->

			if not @$scope.form_props.$valid
				return

			is_new = !@report.id

			promise = @reportData.saveFormModel(@report, @form)

			@startSpinner('saving')
			promise.then( =>
				@stopSpinner('saving', true).then(=>
					@Growl.success("Saved")
				)

				@skipDirtyState()
				if is_new
					@$state.go('builder.create')
			)

	Reports_Builder_Ctrl_Edit.EXPORT_CTRL()