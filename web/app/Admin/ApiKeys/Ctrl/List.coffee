define ['Admin/Main/Ctrl/Base'], (Admin_Ctrl_Base) ->
	class Admin_ApiKeys_Ctrl_List extends Admin_Ctrl_Base
		@CTRL_ID = 'Admin_ApiKeys_Ctrl_List'
		@CTRL_AS = 'ListCtrl'

		init: ->
			@keyData = @DataService.get('ApiKeys')

		###
		# Loads the list
		###

		initialLoad: ->

			promise = @keyData.loadList().then( (list) =>
				@list = list
			)

			return promise

		###
		# Show the delete dlg
		###

		startDelete: (for_key_id) ->

			key = @keyData.findListModelById(for_key_id)

			inst = @$modal.open({
				templateUrl: @getTemplatePath('ApiKeys/delete-modal.html'),
				controller: ['$scope', '$modalInstance', ($scope, $modalInstance) ->
					$scope.confirm = ->
						$modalInstance.close()

					$scope.dismiss = ->
						$modalInstance.dismiss()
				]
			});

			inst.result.then( =>
				@deleteApiKey(key)
			)

		###
		# Actually do the delete
		###

		deleteApiKey: (for_key) ->

			@keyData.deleteApiKeyById(for_key.id).success( =>

				if @$state.current.name == 'apps.api_keys.edit' and parseInt(@$state.params.id) == for_key.id
					@$state.go('apps.api_keys')

			).error((info, code) =>
				@applyErrorResponseToView(info)
			)

	Admin_ApiKeys_Ctrl_List.EXPORT_CTRL()