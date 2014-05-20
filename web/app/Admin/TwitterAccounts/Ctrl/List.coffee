define [
	'Admin/Main/Ctrl/Base'
], (
	Admin_Ctrl_Base
) ->
	class Admin_TwitterAccounts_Ctrl_List extends Admin_Ctrl_Base
		@CTRL_ID = 'Admin_TwitterAccounts_Ctrl_List'
		@CTRL_AS = 'ListCtrl'
		@DEPS = ['$state', '$stateParams', 'DataService']

		init: ->
			@list = []
			@twitterAccountData = @DataService.get('TwitterAccounts')

		initialLoad: ->
			promise = @twitterAccountData.loadList()
			promise.then( (list) =>

				@list = list
			)

			return promise


		###
		# Show the delete dlg
		###
		startDelete: (twitter_account_id) ->

			twitter_account = null
			for v in @list
				if v.id == twitter_account_id
					twitter_account = v
					break

			inst = @$modal.open({
				templateUrl: @getTemplatePath('TwitterAccounts/delete-modal.html'),
				controller: ['$scope', '$modalInstance', ($scope, $modalInstance) ->
					$scope.confirm = ->
						$modalInstance.close();

					$scope.dismiss = ->
						$modalInstance.dismiss();
				]
			});

			inst.result.then( =>
				@twitterAccountData.deleteTwitterAccountById(twitter_account.id).then(=>
					if @$state.current.name == 'twitter.accounts.edit' and parseInt(@$state.params.id) == twitter_account.id
						@$state.go('twitter.accounts')
				)
			)

	Admin_TwitterAccounts_Ctrl_List.EXPORT_CTRL()