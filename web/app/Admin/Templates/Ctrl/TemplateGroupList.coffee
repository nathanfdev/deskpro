define ['angular', 'Admin/Main/Ctrl/Base'], (angular, Admin_Ctrl_Base) ->
	class Admin_Templates_Ctrl_TemplateGroupList extends Admin_Ctrl_Base
		@CTRL_ID   = 'Admin_Templates_Ctrl_TemplateGroupList'
		@CTRL_AS   = 'ListCtrl'
		@DEPS      = []

		init: ->


		initialLoad: ->
			promise = @Api.sendDataGet({
				info: '/templates-info'
			}).then( (res) =>
				@groups = []

				for own groupName, tplList of res.data.info.list.UserBundle
					title = groupName
					if title == 'TOP' then title = 'Layout'

					@groups.push({
						id: "UserBundle:#{groupName}"
						title: title
					})

				@groups.push({
					id: "DeskPRO:custom_fields"
					title: 'CustomFields'
				})
			)

	Admin_Templates_Ctrl_TemplateGroupList.EXPORT_CTRL()