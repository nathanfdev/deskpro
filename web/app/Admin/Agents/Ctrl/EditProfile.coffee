define [
	'DeskPRO/Util/Strings',
	'Admin/Main/Ctrl/Base'
], (
	Strings,
	Admin_Ctrl_Base
) ->
	class Admin_Agents_Ctrl_EditProfile extends Admin_Ctrl_Base
		@CTRL_ID   = 'Admin_Agents_Ctrl_EditProfile'
		@CTRL_AS   = 'Edit'
		@DEPS      = ['agent', 'saveMethod', '$modalInstance']

		init: ->
			@form = {}
			@$scope.dismiss = =>
				@$modalInstance.dismiss()

			@form.timezone = @agent.timezone || 'UTC'
			@form.signature_html = @agent.signature_html || ''

			if @agent.picture_blob
				@form.picture_set = 'current'
			else
				@form.picture_set = 'default'

			me = @
			@uploadPictureCtrl = ['$scope', '$upload', '$http', ($iscope, $upload, $http) ->
				$iscope.$watch('new_image', (new_image) -> me.new_image = new_image)
				$iscope.onFileSelect = (files) ->
					$iscope.is_loading_img = true
					$iscope.error = false
					$iscope.error_message = false
					file = files[0]
					$upload.upload({
						url: $http.formatApiUrl('/misc/upload'),
						data: { is_image: true },
						file: file
					}).success( (data) ->
						$iscope.is_loading_img = false
						$iscope.new_image = data.blob
					).error( (data) ->
						$iscope.is_loading_img = false
						$iscope.error = true
						$iscope.error_message = data?.error_message || null
					)
			]

			@$scope.doSave = =>
				postForm = {
					timezone:       @form.timezone || 'UTC',
					signature_html: @form.signature_html || ''
				}

				if @form.picture_set == 'default' and @agent.picture_blob
					postForm.unset_picture = true
				else if @form.picture_set == 'upload' and @new_image
					postForm.set_picture_blob = @new_image.authcode

				@$scope.is_loading = true
				p = @saveMethod(postForm, this)

				if p == true
					window.setTimeout(=>
						@$scope.is_loading = false
						@$modalInstance.dismiss()
					, 500)
				else
					p.then(=>
						@$scope.is_loading = false
						@$modalInstance.dismiss()
					, =>
						@$scope.is_loading = false
					)

	Admin_Agents_Ctrl_EditProfile.EXPORT_CTRL()