define ['Admin/Main/Ctrl/Base'], (Admin_Ctrl_Base) ->
	class Admin_Agents_Ctrl_DeletedRestore extends Admin_Ctrl_Base
		@CTRL_ID   = 'Admin_Agents_Ctrl_DeletedRestore'
		@CTRL_AS   = 'EditCtrl'
		@DEPS      = ['DpLicense']

		init: ->
			@agentId = parseInt(@$stateParams.id)
			return

		initialLoad: ->
			promise = @Api.sendDataGet({
				agent: "/agents/deleted/#{@agentId}"
			})

			promise.then( (result) =>
				@agent = result.data.agent.agent
			)
			return promise

		restoreAgent: ->
			@startSpinner('saving')

			promise = @Api.sendPost("/agents/deleted/#{@agentId}/undelete")
			promise.then( =>
				@stopSpinner('saving', true)
				@$state.go('agents.agents.edit', {id: @agentId})
			, (res) =>
				@stopSpinner('saving', true)
				if res.data.error_code and res.data.error_code == 'license_exceeded'
					@DpLicense.openUpgradeLicense('upgrade_plan').then(=>
						@restoreAgent()
					)
			)
			return promise

		convertToUser: ->
			@startSpinner('saving_convert')
			promise = @Api.sendDelete("/agents/#{@agentId}/delete/to-user")
			promise.then( =>
				@stopSpinner('saving_convert', true)
				@$state.go('agents.agents')
			)
			return promise

	Admin_Agents_Ctrl_DeletedRestore.EXPORT_CTRL()