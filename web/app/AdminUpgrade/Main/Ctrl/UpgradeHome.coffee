define [
	'AdminUpgrade/Main/Ctrl/UpgradeBase'
], (
	UpgradeBase
) ->
	class AdminUpgrade_Main_Ctrl_UpgradeHome extends UpgradeBase
		@CTRL_ID   = 'AdminUpgrade_Main_Ctrl_UpgradeHome'
		@CTRL_AS   = 'Home'
		@DEPS      = []

		init: ->
			@$scope.card_loaded = false
			@Api.sendDataGet({
				versionInfo: '/dp_license/version-info',
				latestVersion: '/dp_license/latest-version-info',
				updateStatus: '/server/updates/auto'
			}).then((result) =>
				@$scope.card_loaded = true

				@$scope.version_info   = result.data.versionInfo

				if not result.data.latestVersion?.version_info?
					@$scope.latest_version = null
				else
					@$scope.latest_version = result.data.latestVersion.version_info
			)
			return

		startUpgrade: ->
			@$scope.is_loading = true

	AdminUpgrade_Main_Ctrl_UpgradeHome.EXPORT_CTRL()