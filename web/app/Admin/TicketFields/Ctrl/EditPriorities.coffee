define ['Admin/Main/Ctrl/Base'], (Admin_Ctrl_Base) ->
	class Admin_TicketFields_Ctrl_EditPriorities extends Admin_Ctrl_Base
		@CTRL_ID = 'Admin_TicketFields_Ctrl_EditPriorities'
		@CTRL_AS = 'TicketPris'
		@DEPS    = []

		init: ->
			@pris             = []
			@default_id       = 0
			@agent_required   = false
			@user_required    = false
			return

		initialLoad: ->
			data_promise = @Api.sendDataGet({
				'info': '/ticket_pris',
				'layouts': '/ticket_layouts/fields/priority'
			}).then( (res) =>
				@pris           = res.data.info.priorities
				@default_id     = res.data.info.default_id
				@agent_required = res.data.info.agent_required
				@user_required  = res.data.info.user_required
				@enabled        = res.data.info.enabled

				@user_layouts  = res.data.layouts.user_layouts
				@agent_layouts = res.data.layouts.agent_layouts
			)

			return data_promise

		save: ->
			if not @pris or not @pris.length
				@enabled = false

			postData = {
				priorities:     @pris,
				default_id:     @default_id,
				user_required:  @user_required,
				agent_required: @agent_required,
				enabled:        @enabled
			}

			@startSpinner('saving')
			promise = @Api.sendPostJson('/ticket_pris', postData).success( =>
				@$scope.$parent?.TicketFieldsList?.saveLayoutData('priority', @user_layouts, @agent_layouts)
				@$scope.$parent?.TicketFieldsList?.setFieldEnabled('priority', @enabled)
				@settings = angular.copy(@$scope.settings)

				@stopSpinner('saving').then(=>
					@Growl.success(@getRegisteredMessage('saved_settings'))
				)
			).error( (info, code) =>
				@stopSpinner('saving', true)
				@applyErrorResponseToView(info)
			)

	Admin_TicketFields_Ctrl_EditPriorities.EXPORT_CTRL()