define ['Admin/Main/Ctrl/Base'], (Admin_Ctrl_Base) ->
	class Admin_RoundRobin_Ctrl_List extends Admin_Ctrl_Base
		@CTRL_ID = 'Admin_RoundRobin_Ctrl_List'
		@DEPS = []
		@CTRL_AS = 'ListCtrl'



		init: ->
			@service = @DataService.get 'RoundRobin'
			@robins = []
			@settings = null



		initialLoad: ->
			@service.all().then (robins) =>
				@robins = robins
			@service.getSettings().then (settings) =>
				@settings = settings



		save: ($event) ->
			$event.stopImmediatePropagation();

			if !@settings.enabled
				@settings.enabled = true
				return @service.saveSettings()

			@service.getSettings(true).then (settings) =>

				if 0 == @settings.active_triggers
					@settings.enabled = false
					return @service.saveSettings()

				service = @service
				settings = @settings
				title = @getRegisteredMessage 'modal_title'
				msg = @getRegisteredMessage 'modal_message'

				@$modal.open({
					templateUrl: @getTemplatePath('Index/modal-confirm.html'),
					controller: ['$scope', '$modalInstance', ($scope, $modalInstance) ->

						$scope.title = title
						$scope.message = msg

						$scope.dismiss = ->
							$modalInstance.dismiss()

						$scope.confirm = ->
							settings.enabled = !settings.enabled
							service.saveSettings()
							$modalInstance.dismiss()
					]
				})

	Admin_RoundRobin_Ctrl_List.EXPORT_CTRL()