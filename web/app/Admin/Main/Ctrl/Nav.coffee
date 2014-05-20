define ['Admin/Main/Ctrl/Base'], (Admin_Ctrl_Base) ->
	class Admin_Main_Ctrl_Nav extends Admin_Ctrl_Base
		@CTRL_ID   = 'Admin_Main_Ctrl_Nav'
		@DEPS = ['$timeout']

		init: ->
			depth = @$state.current.name.split('.').length
			if depth == 1
				@$timeout(->
					$('.dp-layout-appnav').find('li').first().find('a').click();
				, 10)
			return

	Admin_Main_Ctrl_Nav.EXPORT_CTRL()