define ['Admin/Main/Ctrl/Base'], (Admin_Ctrl_Base) ->
	class Admin_Languages_Ctrl_Edit extends Admin_Ctrl_Base
		@CTRL_ID = 'Admin_Languages_Ctrl_Edit'
		@CTRL_AS = 'EditCtrl'

		init: ->
			@id = @$stateParams.id

			format = (flag) ->
				console.log(flag)
				if not flag or not flag.text then return ''
				return "<img src='"+DP_ASSET_URL+"/images/flags/" + flag.id.toLowerCase() + "' style='margin-right: 2px;' />" + flag.text;

			@$scope.select2Flag = {
				formatResult: format,
				formatSelection: format,
				escapeMarkup: (m) -> return m
			}

		initialLoad: ->
			promise = @Api.sendGet("/langs/#{@id}").then( (result) =>
				if not result.data.language
					@$state.go('setup.languages.install', {id: "install-#{@id}"})
					return

				@pack = result.data.pack
				@lang = result.data.language
				@form = {
					title: @lang.title,
					flag_image: @lang.flag_image,
					locale: @lang.locale
				}
			)
			return promise

	Admin_Languages_Ctrl_Edit.EXPORT_CTRL()