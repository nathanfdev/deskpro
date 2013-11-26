define ['Admin/Main/Ctrl/Base'], (Admin_Ctrl_Base) ->
	class Admin_ChatSetup_Ctrl_ChatSetup extends Admin_Ctrl_Base
		@CTRL_ID = 'Admin_ChatSetup_Ctrl_ChatSetup'
		@CTRL_AS = 'ChatSetup'
		@DEPS    = ['$templateCache']

		###
 	#
		###

		init: ->

			@setup = null

			tpl = @getTemplatePath("ChatSetup/embed-code.html")
			code = @$templateCache.get(tpl)
			@$scope.embed_code = code

		###
 	#
		###

		initialLoad: ->

			data_promise = @Api.sendDataGet({
				'chat_setup': '/chat_setup'
			}).then((res) =>
				@$scope.setup = res.data.chat_setup.chat_setup
			)

			return @$q.all([data_promise])


	Admin_ChatSetup_Ctrl_ChatSetup.EXPORT_CTRL()