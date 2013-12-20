define ['Admin/Main/Ctrl/Base'], (Admin_Ctrl_Base) ->
	class Admin_Templates_Ctrl_TemplateEditor extends Admin_Ctrl_Base
		@CTRL_ID   = 'Admin_Templates_Ctrl_TemplateEditor'
		@CTRL_AS   = 'Ctrl'
		@DEPS      = ['$modalInstance', 'templateName']

		init: ->
			@$scope.dismiss = =>
				@$modalInstance.dismiss('cancel')

			@$scope.save = =>
				@$scope.saving_template = true
				postData = {
					template: {
						code: @editor.getValue()
					}
				}
				@Api.sendPostJson("/templates/#{@templateName}", postData).then(=>
					@$scope.saving_template = false
					@$modalInstance.close({
						templateName: @templateName,
						mode: 'custom'
					})
				)

			@$scope.revert = =>
				@showConfirm('Are you sure you want to revert this template? Your changes will be completely lost and the template will be returned to the default.').result.then(=>
					@$scope.saving_template = true
					@Api.sendDelete("/templates/#{@templateName}").then(=>
						@$scope.saving_template = false
						@$modalInstance.close({
							templateName: @templateName,
							mode: 'revert'
						})
					)
				)

			@$scope.aceLoaded = (editor) =>
				@editor = editor
				maxH = $(editor.container).data('max-height') || 500
				updateH = ->
					newHeight = editor.getSession().getScreenLength() * editor.renderer.lineHeight + editor.renderer.scrollBar.getWidth()
					if newHeight > maxH
						newHeight = maxH
					if newHeight < 10
						newHeight = 10

					$(editor.container).height(newHeight)
					editor.resize()

				updateH()
				editor.getSession().on('change', updateH);
				editor.setShowPrintMargin(false)

		initialLoad: ->
			promise = @Api.sendGet("/templates/#{@templateName}").success( (data) =>
				@tpl = data
				@$scope.tpl = @tpl
				@$scope.template_code = data.template_code.code
			)
			return promise

	Admin_Templates_Ctrl_TemplateEditor.EXPORT_CTRL()