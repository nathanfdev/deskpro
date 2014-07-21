define ['Admin/Main/Ctrl/Base', 'angular'], (Admin_Ctrl_Base, angular) ->
	class Admin_Agents_Ctrl_Import extends Admin_Ctrl_Base
		@CTRL_ID   = 'Admin_Agents_Ctrl_Import'
		@CTRL_AS   = 'Ctrl'
		@DEPS      = []

		init: ->
			@busy = false
			@emails = []
			@fails = 0
			@invited = []
			@page = 0 # import page layout number



		log: ->
			console.log @emails
		initialLoad: ->



		sendEmails: ->
			# todo redo with agents dataservice (provided in round robin branch)
			@Api.sendPostJson('/agents_bulk', {emails: @emails}).then (data) =>
				console.log data


	Admin_Agents_Ctrl_Import.EXPORT_CTRL()