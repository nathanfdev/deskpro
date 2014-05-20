define [
	'AdminUpgrade/Main/Ctrl/UpgradeBase'
], (
	UpgradeBase
) ->
	class AdminUpgrade_Main_Ctrl_UpgradeHome extends UpgradeBase
		@CTRL_ID   = 'AdminUpgrade_Main_Ctrl_UpgradeHome'
		@CTRL_AS   = 'Home'
		@DEPS      = ['$sce', '$location']

		init: ->
			@$scope.opt = {
				db_backup: true,
				file_backup: false,
				time_type: 'now',
				delay: 15,
				user_message: ''
			}

			@$scope.card_loaded = false
			@Api.sendDataGet({
				versionInfo: '/dp_license/version-info',
				latestVersion: '/dp_license/latest-version-info',
				updateStatus: '/server/updates/auto'
			}).then((result) =>

				if result.data.updateStatus.is_scheduled
					@$location.path('/progress')
					return

				@$scope.card_loaded = true

				@$scope.version_info = result.data.versionInfo
				@$scope.release_notes_url = @$sce.trustAsResourceUrl("https://www.deskpro.com/members/versions/changelog/#{@$scope.version_info.build_num_base}")

				if not result.data.latestVersion?.version_info?
					@$scope.latest_version = null
				else
					@$scope.latest_version = result.data.latestVersion.version_info
			)
			return

		startUpgrade: ->
			@$scope.is_loading = true
			opt = @$scope.opt

			formData = {
				backup_db:    if opt.db_backup then 1 else 0,
				backup_files: if opt.file_backup then 1 else 0,
				minutes:      if opt.time_type == 'now' then 0 else (parseInt(opt.delay) || 0),
				user_message: opt.user_message
			}

			@Api.sendPutJson('/server/updates/auto', formData).success(=>
				@$location.path('/progress')
			)

	AdminUpgrade_Main_Ctrl_UpgradeHome.EXPORT_CTRL()