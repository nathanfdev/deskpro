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


		###
		# Show the delete dlg
		###
		startDelete: (for_report_id) ->
			report = @customData.findListModelById(for_report_id)

			if not report.is_custom then throw new Error('Report you are going to delete should be custom report')

			inst = @$modal.open({
				templateUrl: @getTemplatePath('Builder/delete-modal.html'),
				controller: ['$scope', '$modalInstance', ($scope, $modalInstance) ->
					$scope.confirm = ->
						$modalInstance.close()

					$scope.dismiss = ->
						$modalInstance.dismiss()
				]
			});

			inst.result.then(=>
				@deleteReport(report)
			)


		###
		# Actually do the delete
		###
		deleteReport: (for_report) ->
			@customData.deleteReportById(for_report.id).success(=>
				if @$state.current.name == 'builder.edit' and parseInt(@$state.params.id) == for_report.id
					@$state.go('builder')

			).error((info, code) =>
				@applyErrorResponseToView(info)
			)

	Reports_Builder_Ctrl_List.EXPORT_CTRL()