define [
	'Reports/Main/Ctrl/Base',
], (
	ReportsBaseCtrl,
) ->
	class Reports_Builder_Ctrl_Edit extends ReportsBaseCtrl
		@CTRL_ID   = 'Reports_Builder_Ctrl_Edit'
		@CTRL_AS   = 'EditCtrl'
		@DEPS      = ['$stateParams', '$sce', 'Api', '$window', '$http']

		init: ->
			if @$stateParams.type == 'builtIn'
				@reportData = @DataService.get('ReportBuilderBuiltIn')
				@reportType = 'builtIn'
			if @$stateParams.type == 'custom'
				@reportData = @DataService.get('ReportBuilderCustom')
				@reportType = 'custom'

			@report = null
			@query_parts = null
			@rendered_result = null
			@query_error = null
			@show_query_editor = false

		initialLoad: ->
			promise = @reportData.loadEditReportData(@$stateParams.id || null, @$stateParams.params || null).then( (data) =>

				@rendered_result = @$sce.trustAsHtml(data.rendered_result || '')
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
			@startSpinner('builder_loading')
			@startSpinner('query_loading')

			promise = @Api.sendPostJson('/reports/builder/test/' + @report.id, {
				parts: @query_parts
			})

			promise.success((data) =>
				if data.error then @query_error = data.error

				if data.rendered_result
					@query_error = null
					@rendered_result = @$sce.trustAsHtml(data.rendered_result || '')

				@stopSpinner('builder_loading', true)
				@stopSpinner('query_loading', true)
			)


		###
		# This method is called when user clicks on 'Query' tab inside query builder form
		###
		switchToQuery: ->
			@startSpinner('query_loading')

			promise = @Api.sendPostJson('/reports/builder/parse', {
				currentType: 'builder'
				inputType: 'builder'
				newType: 'query'
				query: @report.query
				parts: @query_parts
			})

			promise.success((data) =>
				if data.error then @query_error = data.error

				if data.query
					@query_error = null
					@report.query = data.query

				@stopSpinner('query_loading', true)
			)


		###
		# This method is called when user clicks on 'Builder' tab inside query builder form
		###
		switchToBuilder: ->
			@startSpinner('builder_loading')

			promise = @Api.sendPostJson('/reports/builder/parse', {
				currentType: 'query'
				inputType: 'query'
				newType: 'builder'
				query: @report.query
				parts: @query_parts
			})

			promise.success((data) =>
				if data.error then @query_error = data.error

				if data.parts
					@query_error = null
					@query_parts = data.parts

				@stopSpinner('builder_loading', true)
			)


		###
		# This method is called when user clicks on 'CSV' button
		###
		downloadCsv: ->
			window.open(@$http.formatApiUrl('/reports/builder/download/' + @report.id +  '/csv'))


		###
		# This method is called when user clicks on 'PDF' button
		###
		downloadPdf: ->
			window.open(@$http.formatApiUrl('/reports/builder/download/' + @report.id + '/pdf'))


		###
		# This method is called when user clicks on 'print' button
		###
		print: ->
			window.print()


		###
		# Saving report
		###
		saveReport: ->

			if !@report.is_custom then throw new Error('Only custom reports could be saved')

			if not @$scope.form_props.$valid
				return

			is_new = !@report.id

			promise = @reportData.saveFormModel(@report, @form, @query_parts)

			@startSpinner('builder_loading')
			@startSpinner('query_loading')
			@startSpinner('saving')

			promise.then( (res) =>

				data = res.data

				if data.error
					@stopSpinner('builder_loading', true)
					@stopSpinner('query_loading', true)
					@stopSpinner('saving', true)
					@query_error = data.error
					if !@show_query_editor then @show_query_editor = true
					return

				if data.rendered_result
					@query_error = null
					@rendered_result = @$sce.trustAsHtml(data.rendered_result || '')

				@skipDirtyState()
				if is_new
					@$state.go('builder')

				@stopSpinner('builder_loading', true)
				@stopSpinner('query_loading', true)
				@stopSpinner('saving', true).then( =>
					@Growl.success("Saved")
				)
			)


		###
		# Cloning the report
		###
		saveToClone: ->
			@startSpinner('builder_loading')
			@startSpinner('query_loading')
			@startSpinner('saving')

			promise = @Api.sendPost('/reports/builder/clone/' + @report.id)

			promise.success((data) =>

				@reportData.loadList(true).then(=>
					@stopSpinner('builder_loading', true)
					@stopSpinner('query_loading', true)
					@stopSpinner('saving', true).then(=>
						@Growl.success("Cloning Done")
						@$state.go('builder.edit', {type: 'custom', id: data.id, params: ''})
					)
				)
			)

	Reports_Builder_Ctrl_Edit.EXPORT_CTRL()