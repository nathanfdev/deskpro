define ['Admin/Main/Ctrl/Base'], (Admin_Main_Ctrl_Base) ->
	class Admin_ChannelSms_Ctrl_List extends Admin_Main_Ctrl_Base
		@CTRL_ID = 'Admin_ChannelSms_Ctrl_List'
		@CTRL_AS = 'ChannelSmsList'
		@CTRL_TYPE = 'list'
		@DEPS = ['SmsAccountsData']

		init: ->
			@accounts = []

		initialLoad: ->
			list_promise = @SmsAccountsData.loadList().then((recs) =>
				@accounts = []
				accounts = recs.values()
				for acc in accounts
					acc.phone_number_region = acc.phone_number_region?.toLowerCase()
					@accounts.push acc


				if @$state.current.name == 'tickets.channel_sms'
					if @accounts[0]
						@$state.go('tickets.channel_sms.edit', {id: @accounts[0].id})
					else
						@$state.go('tickets.channel_sms.create')

				@addManagedListener(@SmsAccountsData.recs, 'changed', =>
					@accounts = []
					accounts = @SmsAccountsData.recs.values()
					for acc in accounts
						acc.phone_number_region = acc.phone_number_region?.toLowerCase()
						@accounts.push acc
					@ngApply()
				)
			)

			return @$q.all([list_promise]);

		startDelete: (for_acc_id) ->
			for_acc = null
			for v in @accounts
				if v.id == for_acc_id
					for_acc = v

			inst = @$modal.open({
				templateUrl: @getTemplatePath('ChannelSms/delete-modal.html'),
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
			@Api.sendDelete('/channel/sms/account/' + acc.id).success(=>
				@SmsAccountsData.remove(acc.id)
				@ngApply()

				# if currently viewing the deleted account, then should need to switch state
				if @$state.current.name == 'tickets.channel_sms.edit' and parseInt(@$state.params.id) == acc.id
					@$state.go('tickets.channel_sms')
			)

	Admin_ChannelSms_Ctrl_List.EXPORT_CTRL()
