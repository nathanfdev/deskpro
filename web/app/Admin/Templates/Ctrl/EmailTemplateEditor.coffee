define ['Admin/Main/Ctrl/Base'], (Admin_Ctrl_Base) ->
	class Admin_Templates_Ctrl_EmailTemplateEditor extends Admin_Ctrl_Base
		@CTRL_ID   = 'Admin_Templates_Ctrl_EmailTemplateEditor'
		@CTRL_AS   = 'EmailTemplateEditor'
		@DEPS      = ['$modalInstance', 'templateName']

		init: ->
			@$scope.dismiss = =>
				@$modalInstance.dismiss('cancel')

			@$scope.save = =>
				@$scope.saving_template = true
				postData = {
					template: {
						subject: @editorSubject.getValue(),
						body: @editorMessage.getValue()
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

			@$scope.aceLoadedSubject = (editor) =>
				@editorSubject = editor
				maxH = $(editor.container).data('max-height') || 150
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

			@$scope.aceLoadedMessage = (editor) =>
				@editorMessage = editor
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
			p = @Api.sendGet("/templates/#{@templateName}").success( (data) =>
				@initTemplateData(data)
			)

			return p

		initTemplateData: (info) ->
			@templateName = info.name
			@email = info
			@$scope.email = @email

	Admin_Templates_Ctrl_EmailTemplateEditor.EXPORT_CTRL()