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
			)

			return promise

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

			@banData.deleteBanById(for_ban.banned_ip).success( =>

				if @$state.current.name == 'crm.banning.edit_ip' and parseInt(@$state.params.ban) == for_ban.ban
					@$state.go('crm.banning')

			).error((info, code) =>
				@applyErrorResponseToView(info)
			)

	Admin_Banning_Ctrl_List.EXPORT_CTRL()