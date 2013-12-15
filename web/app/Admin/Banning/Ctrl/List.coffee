define ['Admin/Main/Ctrl/Base'], (Admin_Ctrl_Base) ->
	class Admin_Banning_Ctrl_List extends Admin_Ctrl_Base
		@CTRL_ID = 'Admin_Banning_Ctrl_List'
		@CTRL_AS = 'ListCtrl'

		init: ->
			@banData = @DataService.get('Bans')

		###
		# Loads the list
		###

		initialLoad: ->

			promise = @banData.loadList().then( (list) =>

				@list = list
				@pagination = @banData.getPagination()

				@initializeScopeWatching()
			)

			return promise

		###
		#	Here we watching scope 'page' variable in order to load new page of results
		###

		initializeScopeWatching: ->

			@$scope.$watch('ListCtrl.pagination', (newVal, oldVal) =>

				if parseInt(newVal.ip_bans.page) == parseInt(oldVal.ip_bans.page) and parseInt(newVal.email_bans.page) == parseInt(oldVal.email_bans.page)
					return undefined

				if isNaN(parseInt(newVal.ip_bans.page)) and isNaN(parseInt(newVal.email_bans.page))
					return undefined

				@startSpinner('paginating_ip_bans') if newVal.ip_bans.page != oldVal.ip_bans.page
				@startSpinner('paginating_email_bans') if newVal.email_bans.page != oldVal.email_bans.page

				@banData.refreshList().then( (list) =>

					@stopSpinner('paginating_ip_bans', true) if newVal.ip_bans.page != oldVal.ip_bans.page
					@stopSpinner('paginating_email_bans', true) if newVal.email_bans.page != oldVal.email_bans.page

					@list = list
					@pagination = @banData.getPagination()
				)
			, true)

		###
		# Show the delete dlg
		###

		startDelete: (for_ban_id) ->

			key = @banData.findListModelById(for_ban_id)

			inst = @$modal.open({
				templateUrl: @getTemplatePath('Banning/delete-modal.html'),
				controller: ['$scope', '$modalInstance', ($scope, $modalInstance) ->
					$scope.confirm = ->
						$modalInstance.close()

					$scope.dismiss = ->
						$modalInstance.dismiss()
				]
			});

			inst.result.then(=>
				@deleteBan(key)
			)

		###
		# Actually do the delete
		###

		deleteBan: (for_ban) ->

			if for_ban.banned_ip then key = 'ip'
			if for_ban.banned_email then key = 'email'

			@banData.deleteBanById(for_ban['banned_' + key]).success( =>

				if @$state.current.name == ('crm.banning.edit_' + key) and @$state.params.ban == for_ban['banned_' + key]
					@$state.go('crm.banning')

			).error((info, code) =>
				@applyErrorResponseToView(info)
			)

	Admin_Banning_Ctrl_List.EXPORT_CTRL()