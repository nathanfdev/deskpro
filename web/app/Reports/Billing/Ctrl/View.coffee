define [
	'Reports/Main/Ctrl/Base',
], (
	ReportsBaseCtrl,
) ->
	class Reports_Billing_Ctrl_View extends ReportsBaseCtrl
		@CTRL_ID   = 'Reports_Billing_Ctrl_View'
		@CTRL_AS   = 'Ctrl'
		@DEPS      = ['$stateParams', '$sce', 'Api']

		init: ->

			@rendered_result = null

		###
 	#
 	###
		initialLoad: ->

			promise = @Api.sendGet('/reports/billing/' + @$stateParams.id, {params: @$stateParams.params}).then( (res) =>
				data = res.data
				@rendered_result = @$sce.trustAsHtml(data.rendered_result)
			)

			return promise

	Reports_Billing_Ctrl_View.EXPORT_CTRL()