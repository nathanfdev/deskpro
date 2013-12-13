define ['Admin/Main/Ctrl/Base', 'angular'], (Admin_Ctrl_Base, angular) ->
	class Admin_License_Ctrl_License extends Admin_Ctrl_Base
		@CTRL_ID   = 'Admin_License_Ctrl_License'
		@CTRL_AS   = 'Ctrl'
		@DEPS      = []

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

		saveLicenseCode: ->
			postData = {
				license_code: @license.license_code
			}

			@startSpinner('saving')
			@Api.sendPost("dp_license", postData).success(=>
				@reloadLicData().then(=>
					@stopSpinner('saving', true)
					@Growl.success(@getRegisteredMessage('saved_lic'))
				)
			).error(=>
				@Growl.error(@getRegisteredMessage('lic_error'))
			)

		save: ->
			return

	Admin_License_Ctrl_License.EXPORT_CTRL()