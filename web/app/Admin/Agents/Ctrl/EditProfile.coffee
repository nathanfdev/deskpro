define [
	'DeskPRO/Util/Strings',
	'Admin/Main/Ctrl/Base'
], (
	Strings,
	Admin_Ctrl_Base
) ->
	class Admin_Agents_Ctrl_EditProfile extends Admin_Ctrl_Base
		@CTRL_ID   = 'Admin_Agents_Ctrl_EditProfile'
		@CTRL_AS   = 'Edit'
		@DEPS      = ['form', 'agent', '$modalInstance']

		init: ->
			@$scope.dismiss = =>
				@$modalInstance.dismiss()

			if @agent.picture_blob
				@form.picture_set = 'current'
			else
				@form.picture_set = 'default'

	Admin_Agents_Ctrl_EditProfile.EXPORT_CTRL()