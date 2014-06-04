define ['Admin/Main/Ctrl/Base'], (Admin_Ctrl_Base) ->
	class Admin_Main_Ctrl_MainBody extends Admin_Ctrl_Base
		@CTRL_ID   = 'Admin_Main_Ctrl_MainBody'
		@DEPS      = []

		init: ->
			@$scope.do_show_ids = false

			@$scope.$watch('do_show_ids', (isOn) ->
				if isOn
					$('body').addClass('show-title-ids')
				else
					$('body').removeClass('show-title-ids')
			)
			return

	Admin_Main_Ctrl_MainBody.EXPORT_CTRL()