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

			@$scope.$watch('ListCtrl.pagination.ip_bans.page', (newVal, oldVal) =>

				if parseInt(newVal) == parseInt(oldVal)
					return undefined

				if isNaN(parseInt(newVal))
					return undefined

				@banData.refreshList().then( (list) =>

					@list = list
					@pagination = @banData.getPagination()
				)
			)

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