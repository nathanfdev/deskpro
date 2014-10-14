define [
	'Admin/Main/Ctrl/Base',
	'Admin/Brand/FormModel/EditBrandModel'
], (Admin_Ctrl_Base, Admin_Brand_FormModel_EditBrandModel) ->
	class Admin_Brand_Ctrl_Setup extends Admin_Ctrl_Base
		@CTRL_ID = 'Admin_Brand_Ctrl_Setup'
		@CTRL_AS = 'BrandSetup'
		@DEPS = ['Api', 'Growl', 'BrandData', '$stateParams', '$state', '$timeout']

		init: ->
			@brandId = parseInt(@$stateParams.id || 0)
			@new_image = null

			me = @
			@uploadPictureCtrl = [
				'$scope', '$upload', '$http', ($iscope, $upload, $http) ->
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
						}).success((data) ->
							$iscope.is_loading_img = false
							$iscope.new_image = data.blob
						).error((data) ->
							$iscope.is_loading_img = false
							$iscope.error = true
							$iscope.error_message = data?.error_message || null
						)
			]

		getFormModel: ->
			return new Admin_Brand_FormModel_EditBrandModel(@brand || {})

		initialLoad: ->
			if @brandId
				promise = @Api.sendGet("/brands/#{@brandId}").then((result) =>
					if result.data
						@brand = result.data
						@form_model = @getFormModel()
					@setFormOnScope()
				)
			else
				@brand = {}
				@setFormOnScope()
			return promise

		setFormOnScope: ->
			if not @form_model
				@form_model = @getFormModel()
			@$scope.form = @form_model.form

		getPostData: ->
			return {brand: @form_model.getFormData()}

		saveBrand: ->
			postData = @getPostData()

			if @form_model.form.logo_set == 'default' and @brand.logo_blob
				postData.unset_logo = true
			else if @form_model.form.logo_set == 'upload' and @new_image
				postData.set_logo_blob = @new_image.authcode

			if @brandId
				@Api.sendPostJson("/brands/#{@brandId}", postData).then((result) =>
					@brand = result.data
					@BrandData.updateModel(@brand)
					@form_model = @getFormModel()
					@setFormOnScope()
					@Growl.success(@getRegisteredMessage('saved_brand'))
				)
			else
				@Api.sendPostJson("/brands", postData).then((result) =>
					@brand = result.data
					@brandId = @brand.id
					@BrandData.addToList(@brand)
					@form_model = @getFormModel()
					@setFormOnScope()
					@Growl.success(@getRegisteredMessage('saved_brand'))
					@$state.go('brand.setup.edit', { id: @brandId })
				)


	Admin_Brand_Ctrl_Setup.EXPORT_CTRL()
