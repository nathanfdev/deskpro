define ['Admin/Main/Ctrl/Base', 'moment'], (Admin_Ctrl_Base, moment) ->
	class Admin_EmailStatus_Ctrl_ViewSend extends Admin_Ctrl_Base
		@CTRL_ID = 'Admin_EmailStatus_Ctrl_ViewSend'
		@CTRL_AS = 'ViewSource'
		@DEPS    = ['$state', '$modal']

		init: ->
			@sendmailId = parseInt(@$stateParams.id)
			return

		initialLoad: ->
			@Api.sendGet("/email_status/sendmail/#{@sendmailId}?with_raw=1").then( (res) =>
				@sendmail     = res.data.sendmail
				@sendmail_raw = res.data.sendmail_raw
				@log          = res.data.sendmail_log
			)

		delete: ->
			@Api.sendDelete("/email_status/sendmail/#{@sendmailId}")

		resend: ->
			@Api.sendPost("/email_status/sendmail/#{@sendmailId}/resend")

		startDelete: ->
			doDelete = =>
				@delete().then( =>
					@$state.go('tickets.ticket_accounts.sendmailqueue')
				)

			@$modal.open({
				templateUrl: @getTemplatePath('EmailStatus/sendmail-delete-modal.html'),
				controller: ['$scope', '$modalInstance', ($scope, $modalInstance) ->
					$scope.dismiss = ->
						$modalInstance.dismiss()

					$scope.doDelete = ->
						$scope.is_loading = true
						doDelete().then(-> $modalInstance.dismiss())
				]
			});

		startResend: ->
			doResend = =>
				@resend().then( =>
					@$state.go('tickets.ticket_accounts.gosendmailview', {id: @sendmailId})
				)

			@$modal.open({
				templateUrl: @getTemplatePath('EmailStatus/sendmail-resend-modal.html'),
				controller: ['$scope', '$modalInstance', ($scope, $modalInstance) ->
					$scope.dismiss = ->
						$modalInstance.dismiss()

					$scope.doResend = ->
						$scope.is_loading = true
						doResend().then(-> $modalInstance.dismiss())
				]
			});

	Admin_EmailStatus_Ctrl_ViewSend.EXPORT_CTRL()