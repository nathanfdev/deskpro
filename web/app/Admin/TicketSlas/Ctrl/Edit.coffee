define [
	'Admin/Main/Ctrl/Base'
], (
	Admin_Ctrl_Base
) ->
	class Admin_TicketSlas_Ctrl_Edit extends Admin_Ctrl_Base
		@CTRL_ID   = 'Admin_TicketSlas_Ctrl_Edit'
		@CTRL_AS   = 'EditCtrl'
		@DEPS      = ['$stateParams']

		init: ->
			@slaData = @DataService.get('TicketSlas')
			@macro = null

		initialLoad: ->
			if @$stateParams.id
				promise = @slaData.loadEditSlaData(@$stateParams.id).then( (data) =>
					@macro = data.macro
					@form = @getFormFromModel(@macro)
				)
				return promise
			else
				@macro = {}
				@form = @getFormFromModel(@macro)
				return null

		getFormFromModel: (slaModel) ->
			form = {}
			form.title = slaModel.title || ''

			return form

	Admin_TicketSlas_Ctrl_Edit.EXPORT_CTRL()