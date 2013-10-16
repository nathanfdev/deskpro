define ['angular', 'Admin/Main/Ctrl/Base'], (angular, Admin_Ctrl_Base) ->
	class Admin_Templates_Ctrl_EmailTemplateEditor extends Admin_Ctrl_Base
		@CTRL_ID   = 'Admin_Templates_Ctrl_EmailTemplateEditor'
		@CTRL_TYPE = 'modal'
		@CTRL_AS   = 'EmailTemplateEditor'
		@DEPS      = ['$modalInstance', 'templateName', 'variantOf']

		init: ->
			@$scope.dismiss = =>
				@$modalInstance.dismiss('cancel')

			@$scope.save = =>
				@$modalInstance.close()

			@$scope.aceLoaded = (editor) ->
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

		initialLoad: ->
			if @templateName
				p = @Api.sendGet("/templates/#{@templateName}").success( (data) =>
					@initTemplateData(data)
				)
			else
				p = @Api.sendPost("/templates/#{@variantOf}/create-random-variant").success( (data) =>
					@initTemplateData(data)
				)

			return p

		initTemplateData: (info) ->
			@templateName = info.name
			@email = info
			@$scope.email = @email

	Admin_Templates_Ctrl_EmailTemplateEditor.EXPORT_CTRL()