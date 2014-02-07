define [
	'Reports/Main/Ctrl/Base',
], (
	ReportsBaseCtrl,
) ->
	class Reports_Builder_Ctrl_Edit extends ReportsBaseCtrl
		@CTRL_ID   = 'Reports_Builder_Ctrl_Edit'
		@CTRL_AS   = 'EditCtrl'
		@DEPS      = ['$stateParams', '$sce']

		init: ->
			if(@$stateParams.type == 'builtIn')
				@reportData = @DataService.get('ReportBuilderBuiltIn')
				@reportType = 'builtIn'
			if(@$stateParams.type == 'custom')
				@reportData = @DataService.get('ReportBuilderCustom')
				@reportType = 'custom'

			@report = null
			@rendered_result = null


		###
 	#
 	###
		initialLoad: ->
			promise = @reportData.loadEditReportData(@$stateParams.id || null, @$stateParams.params || null).then( (data) =>

				@report  = data.report
				@rendered_result = @$sce.trustAsHtml(data.rendered_result)
				@form = data.form
			)
			return promise


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