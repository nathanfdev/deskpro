define ['Admin/Main/Ctrl/Base', 'angular'], (Admin_Ctrl_Base, angular) ->
	class Admin_License_Ctrl_License extends Admin_Ctrl_Base
		@CTRL_ID   = 'Admin_License_Ctrl_License'
		@CTRL_AS   = 'Ctrl'
		@DEPS      = ['$window']

		init: ->
			@license          = null
			@ma_token         = null
			@ma_login_url     = null
			@lic_set_callback = null

		reloadLicData: ->
			data_promise = @Api.sendDataGet({
				'lic_info': '/dp_license'
			}).then( (res) =>
				console.log(res)
				@license          = res.data.lic_info.license
				@ma_token         = res.data.lic_info.ma_token
				@ma_login_url     = res.data.lic_info.ma_login_url
				@lic_set_callback = res.data.lic_info.lic_set_callback

				@lic_code = @license.licenseCode
			)

			return data_promise

		initialLoad: ->
			return @reloadLicData()

		downloadKeyfile: ->
			@$window.location = @Api.formatUrl('dp_license/keyfile.txt') + '?API-TOKEN=' + @Api.api_token

		goToMembersArea: ->
			@$window.location = 'https://www.deskpro.com/members/'
			return

		saveLicenseCode: ->
			postData = {
				license_code: @lic_code
			}

			@$scope.lic_error_code = false
			@$scope.show_lic_error = false
			@startSpinner('saving')
			@Api.sendPost("dp_license", postData).success(=>
				@reloadLicData().then(=>
					@stopSpinner('saving').then(=>
						@Growl.success(@getRegisteredMessage('saved_lic'))
					)
				)
			).error( (data) =>
				@stopSpinner('saving', true)
				@$scope.lic_error_code = false
				if data and data.error_code
					@$scope.lic_error_code = data.error_code

				@$scope.show_lic_error = true
			)

		save: ->
			return

	Admin_License_Ctrl_License.EXPORT_CTRL()