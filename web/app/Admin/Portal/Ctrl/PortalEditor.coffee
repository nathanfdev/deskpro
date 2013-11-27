define ['Admin/Main/Ctrl/Base'], (Admin_Ctrl_Base) ->
	class Admin_Portal_Ctrl_PortalEditor extends Admin_Ctrl_Base
		@CTRL_ID = 'Admin_Portal_Ctrl_PortalEditor'
		@CTRL_AS = 'Portal'

		init: ->
			@portal_enabled = false
			return

		initialLoad: ->
			promise = @Api.sendGet('/settings/values/user.portal_enabled').then( (result) =>
				if result.data.value == true or result.data.value == "1"
					@portal_enabled = true
				else
					@portal_enabled = false
			)
			return promise

		startTogglePortal: ->
			if @portal_enabled
				message = "Are are sure you want to disable the portal? The front-end portal website for end-users will be completely disabled. Anyone who knows the URL of the portal will see a blank page."
			else
				message = "Are are sure you want to enable the portal?"

			@showConfirm(message).result.then( =>
				@togglePortal()
			)
			return

		togglePortal: ->
			newVal = if @portal_enabled then '0' else '1'

			@Api.sendPost('/settings/values/user.portal_enabled', { value: newVal }).then( =>
				@$state.go('portal.portal_editor_go')
			)

	Admin_Portal_Ctrl_PortalEditor.EXPORT_CTRL()