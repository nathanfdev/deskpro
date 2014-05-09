define ['Admin/Main/Ctrl/Base', 'DeskPRO/Util/Arrays'], (Admin_Ctrl_Base, Arrays) ->
	class Admin_TicketFields_Ctrl_EditWorkflows extends Admin_Ctrl_Base
		@CTRL_ID = 'Admin_TicketFields_Ctrl_EditWorkflows'
		@CTRL_AS = 'TicketWorks'
		@DEPS    = []

		init: ->
			@works            = []
			@default_id       = 0
			@agent_required   = false
			@user_required    = false
			return

		initialLoad: ->
			data_promise = @Api.sendDataGet({
				'info': '/ticket_works'
			}).then( (res) =>
				@works          = res.data.info.workflows
				@default_id     = res.data.info.default_id
				@agent_required = res.data.info.agent_required
				@user_required  = res.data.info.user_required
				@enabled        = res.data.info.enabled
			)

			return data_promise

		save: ->
			postData = {
				priorities:     @works,
				default_id:     @default_id,
				user_required:  @user_required,
				agent_required: @agent_required,
				enabled:        @enabled
			}

			@startSpinner('saving')
			promise = @Api.sendPostJson('/ticket_works', postData).success( =>
				@settings = angular.copy(@$scope.settings)

				@stopSpinner('saving').then(=>
					@Growl.success(@getRegisteredMessage('saved_settings'))
				)
			).error( (info, code) =>
				@stopSpinner('saving', true)
				@applyErrorResponseToView(info)
			)

	Admin_TicketFields_Ctrl_EditWorkflows.EXPORT_CTRL()