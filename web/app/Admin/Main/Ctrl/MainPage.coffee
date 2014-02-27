define ['Admin/Main/Ctrl/Base'], (Admin_Ctrl_Base) ->
	class Admin_Main_Ctrl_MainPage extends Admin_Ctrl_Base
		@CTRL_ID   = 'Admin_Main_Ctrl_MainPage'
		@DEPS      = ['$rootScope', '$location']

		init: ->
			if @$location.path() == '/license'
				@$scope.isBillingInterface = true
			else
				@$scope.isBillingInterface = false

			@$rootScope.$on('$locationChangeSuccess', =>
				if @$location.path() == '/license'
					@$scope.isBillingInterface = true
				else
					@$scope.isBillingInterface = false
			)
			return

	Admin_Main_Ctrl_MainPage.EXPORT_CTRL()