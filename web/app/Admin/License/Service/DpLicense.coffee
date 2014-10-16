define ->
	class Admin_License_Service_DpLicense
		constructor: (@Api, @$modal, @$http, @$q) ->

		getLicInfo: ->
			return @licGetting if @licGetting
			@licGetting = @Api.sendGet('/dp_license?basic=1').success( (data) =>
				@licInfo = data.license
				@licInfo.licenseCode = @licInfo.licenseCode.replace(/\s/g, '')
			)

		getLicServerParams: ->
			return { 'license_id': @licInfo.licenseId, 'license_code': @licInfo.licenseCode, 'callback': 'JSON_CALLBACK', 'email': window.DP_PERSON_EMAIL }

		getPlanUpgradeInfo: (num_agents) ->
			d = @$q.defer()

			params = @getLicServerParams();
			params.num_agents = num_agents || 0

			@getLicInfo().then(=>
				@$http.jsonp(DP_SECURE_LIC_SERVER + '/api/license/plan-info', {
					params: params,
					timeout: 25000,
					cache: false
				}).then((x) ->
					d.resolve(x.data, x)
				, (x) ->
					d.reject(x.data, x)
				)
			, (x) -> d.reject(x))

			return d.promise

		getRenewInfo: (num_years) ->
			d = @$q.defer()

			params = @getLicServerParams();
			params.num_years = num_years || 0

			@getLicInfo().then(=>
				@$http.jsonp(DP_SECURE_LIC_SERVER + '/api/license/renew-info', {
					params: params,
					timeout: 25000,
					cache: false
				}).then((x) ->
					d.resolve(x.data, x)
				, (x) ->
					d.reject(x.data, x)
				)
			, (x) -> d.reject(x))

			return d.promise

		getNewLicenseKey: ->
			d = @$q.defer()
			@getLicInfo().then(=>
				@$http.jsonp(DP_SECURE_LIC_SERVER + '/api/license/renew-key', {
					params: @getLicServerParams(),
					timeout: 25000,
					cache: false
				}).then((x) ->
					d.resolve(x.data, x)
				, (x) ->
					d.reject(x.data, x)
				)
			, (x) -> d.reject(x))

			return d.promise

		setNewLicenseCode: (lic_code) ->
			postData = {
				license_code: lic_code
			}

			d = @$q.defer()

			@Api.sendPost("dp_license", postData).success(=>
				d.resolve({success: true, lic_code: lic_code})
			).error( (data) =>
				d.reject({success: false, lic_code: lic_code, error_code: data?.error_code})
			)

			return d.promise

		sendPayInvoiceRequest: (mode, card_info, invoice_id) ->

			params = @getLicServerParams()
			params.mode = mode
			params.invoice_id = invoice_id

			if mode == 'new'
				params.cc_type      = card_info.type
				params.cc_name      = card_info.name
				params.cc_number    = card_info.number
				params.cc_cv2       = card_info.cv2
				params.cc_expire_yy = card_info.expire_yy
				params.cc_expire_mm = card_info.expire_mm

			d = @$q.defer()
			@getLicInfo().then(=>
				@$http.jsonp(DP_SECURE_LIC_SERVER + '/api/license/pay-invoice', {
					params: params,
					timeout: 25000,
					cache: false
				}).then((x) ->
					d.resolve(x.data, x)
				, (x) ->
					d.reject(x.data, x)
				)
			, (x) -> d.reject(x))

			return d.promise

		openUpgradeLicense: (upgradeType, options = {}) ->
			modalInstance = @$modal.open({
				templateUrl: '/admin/load-view/License/upgrade-license-modal.html',
				controller: 'Admin_License_Ctrl_UpgradeLicenseModal',
				resolve: {
					upgradeType: ->
						return upgradeType
					upgradeOptions: ->
						return options
				}
			})
			return modalInstance.result

		openRenewLicense: (options = {}) ->
			modalInstance = @$modal.open({
				templateUrl: '/admin/load-view/License/upgrade-license-modal.html',
				controller: 'Admin_License_Ctrl_UpgradeLicenseModal',
				resolve: {
					upgradeType: ->
						return 'extend'
					upgradeOptions: ->
						return options
				}
			})
			return modalInstance.result