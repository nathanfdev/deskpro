define ['Admin/Main/Ctrl/Base'], (Admin_Ctrl_Base) ->
	class Admin_Languages_Ctrl_Edit extends Admin_Ctrl_Base
		@CTRL_ID = 'Admin_Languages_Ctrl_Edit'
		@CTRL_AS = 'EditCtrl'

		init: ->
			@id = @$stateParams.id
			@form = {
				flag_image: 'us',
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
			return

	Admin_Languages_Ctrl_Edit.EXPORT_CTRL()