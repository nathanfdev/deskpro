define ['Admin/Main/Ctrl/Base'], (Admin_Main_Ctrl_Base) ->
	class Admin_ChannelFacebook_Ctrl_List extends Admin_Main_Ctrl_Base
		@CTRL_ID = 'Admin_ChannelFacebook_Ctrl_List'
		@CTRL_AS = 'ChannelFacebookList'
		@CTRL_TYPE = 'list'
		@DEPS = ['FacebookPagesData']

		init: ->
			@pages = []

		initialLoad: ->
			list_promise = @FacebookPagesData.loadList().then((recs) =>
				@pages = []
				accounts = recs.values()
				for acc in accounts
					@pages.push acc

				@pages.push {
					id: 5,
					identifier: 'Camp Happy',
					is_enabled: true,
					img: 'computer'
				}

				@pages.push {
					id: 9,
					identifier: 'Big Corp.',
					is_enabled: false,
					img: 'taxi'
				}


				if @$state.current.name == 'tickets.channel_facebook'
					if @pages[0]
						@$state.go('tickets.channel_facebook.edit', {id: @pages[0].id})
					else
						@$state.go('tickets.channel_facebook.create')

				@addManagedListener(@FacebookPagesData.recs, 'changed', =>
					@pages = []
					accounts = @FacebookPagesData.recs.values()
					for acc in accounts
						acc.phone_number_region = acc.phone_number_region?.toLowerCase()
						@pages.push acc
					@ngApply()
				)
			)

			return @$q.all([list_promise]);

		startDelete: (for_acc_id) ->
			for_acc = null
			for v in @pages
				if v.id == for_acc_id
					for_acc = v

			inst = @$modal.open({
				templateUrl: @getTemplatePath('ChannelFacebook/delete-modal.html'),
				controller: [
					'$scope', '$modalInstance', ($scope, $modalInstance) ->
						$scope.confirm = ->
							$modalInstance.close();

						$scope.dismiss = ->
							$modalInstance.dismiss();
				]
			});

			inst.result.then(=>
				@deleteAccount(for_acc)
			)

		deleteAccount: (acc) ->
			@Api.sendDelete('/channel/facebook/page/' + acc.id).success(=>
				@FacebookPagesData.remove(acc.id)
				@ngApply()

				# if currently viewing the deleted account, then should need to switch state
				if @$state.current.name == 'tickets.channel_sms.edit' and parseInt(@$state.params.id) == acc.id
					@$state.go('tickets.channel_sms')
			)

	Admin_ChannelFacebook_Ctrl_List.EXPORT_CTRL()
