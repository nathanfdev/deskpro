define ['Admin/Main/Ctrl/Base', 'DeskPRO/Util/Strings'], (Admin_Ctrl_Base, Strings) ->
	class Admin_License_Ctrl_UpgradeLicenseModal extends Admin_Ctrl_Base
		@CTRL_ID   = 'Admin_License_Ctrl_UpgradeLicenseModal'
		@CTRL_AS   = 'Ctrl'
		@DEPS      = ['$modalInstance', 'upgradeType', 'upgradeOptions', 'Api', 'DpLicense']

		init: ->
			@$scope.upgradeType     = @upgradeType
			@$scope.upgradeOptions  = @upgradeOptions
			@$scope.initial_loading = true;

			@DpLicense.getPlanUpgradeInfo().then((info) =>
				console.log(info)
				@planInfo = info
				@$scope.planInfo = info
				@$scope.initial_loading = false;
				@$scope.paymentForm = {}
				@$scope.paymentForm.exist_card = info.card_details || null

				if @$scope.paymentForm.exist_card
					@$scope.paymentForm.card_type = 'existing'
				else
					@$scope.paymentForm.card_type = 'new'
			)
			return

	Admin_License_Ctrl_UpgradeLicenseModal.EXPORT_CTRL()