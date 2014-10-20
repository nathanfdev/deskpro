define ['Admin/Main/Ctrl/Base', 'moment'], (Admin_Ctrl_Base, moment) ->
	class Admin_EmailStatus_Ctrl_ViewSource extends Admin_Ctrl_Base
		@CTRL_ID = 'Admin_EmailStatus_Ctrl_ViewSource'
		@CTRL_AS = 'ViewSource'
		@DEPS    = ['$state', '$modal', 'DpDateService']

		init: ->
			@sourceId = parseInt(@$stateParams.id)
			@$scope.ds = @DpDateService
			return

		initialLoad: ->
			@Api.sendGet("/email_status/sources/#{@sourceId}?with_raw=1").then( (res) =>
        for own k,v of res.data
	        if 'date_status' == k || 'date_created' == k
		        v = @DpDateService.local v
          @[k] = v
			)

		delete: ->
			@Api.sendDelete("/email_status/sources/#{@sourceId}")

		reprocess: ->
			@Api.sendPost("/email_status/sources/#{@sourceId}/reprocess")

		startDelete: ->
			doDelete = =>
				@delete().then( =>
					@$state.go('tickets.ticket_accounts.emailsources')
				)

			@$modal.open({
				templateUrl: @getTemplatePath('EmailStatus/emailsource-delete-modal.html'),
				controller: ['$scope', '$modalInstance', ($scope, $modalInstance) ->
					$scope.dismiss = ->
						$modalInstance.dismiss()

					$scope.doDelete = ->
						$scope.is_loading = true
						doDelete().then(-> $modalInstance.dismiss())
				]
			});

		startReprocess: ->
			doReprocess = =>
				@reprocess().then( =>
					@$state.go('tickets.ticket_accounts.goemailsourcesview', {id: @sourceId})
				)

			@$modal.open({
				templateUrl: @getTemplatePath('EmailStatus/emailsource-reprocess-modal.html'),
				controller: ['$scope', '$modalInstance', ($scope, $modalInstance) ->
					$scope.dismiss = ->
						$modalInstance.dismiss()

					$scope.doReprocess = ->
						$scope.is_loading = true
						doReprocess().then(-> $modalInstance.dismiss())
				]
			});

	Admin_EmailStatus_Ctrl_ViewSource.EXPORT_CTRL()