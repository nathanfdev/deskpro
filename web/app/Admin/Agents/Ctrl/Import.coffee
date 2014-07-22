define ['Admin/Main/Ctrl/Base', 'angular'], (Admin_Ctrl_Base, angular) ->
	class Admin_Agents_Ctrl_Import extends Admin_Ctrl_Base
		@CTRL_ID   = 'Admin_Agents_Ctrl_Import'
		@CTRL_AS   = 'Ctrl'
		@DEPS      = []

		init: ->
			@busy = false
			@restart()



		initialLoad: ->



		restart: ->
			@page = 0 # import page layout number
			@emails = []
			@results = []
			@invited = 0



		sendEmails: ->
			return if !@$scope.Form.$valid

			@busy = true

			# todo redo with agents dataservice (provided in round robin branch)
			@Api.sendPostJson('/agents_bulk', {emails: @emails}).then(
				(data) =>
					@busy = false
					@page = 1

					for email, entry of data.data
						@invited++ if entry.id?
						@results.push entry

				() =>
					@busy => false
			)


	Admin_Agents_Ctrl_Import.EXPORT_CTRL()