define ['Admin/Main/Ctrl/Base'], (Admin_Ctrl_Base) ->
	class Admin_ServerFileUploads_Ctrl_ServerFileUploads extends Admin_Ctrl_Base

		@CTRL_ID   = 'Admin_ServerFileUploads_Ctrl_ServerFileUploads'
		@CTRL_AS   = 'Ctrl'
		@DEPS      = []

		init: ->
			@$scope.data = null

		initialLoad: ->
			data_promise = @Api.sendGet('/server_file_uploads').then( (res) =>

				@$scope.data = res.data.server_file_uploads
			)

			return @$q.all([data_promise])

	Admin_ServerFileUploads_Ctrl_ServerFileUploads.EXPORT_CTRL()