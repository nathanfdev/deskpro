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
			return { 'license_id': @licInfo.licenseId, 'license_code': @licInfo.licenseCode, 'callback': 'JSON_CALLBACK' }

		getPlanUpgradeInfo: ->
			d = @$q.defer()
			@getLicInfo().then(=>
				@$http.jsonp(DP_SECURE_LIC_SERVER + '/api/license/plan-info.json', {
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