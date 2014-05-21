define ['Admin/Main/Ctrl/Base'], (Admin_Ctrl_Base) ->
	class Admin_Templates_Ctrl_EmailTemplateEditor extends Admin_Ctrl_Base
		@CTRL_ID   = 'Admin_Templates_Ctrl_EmailTemplateEditor'
		@CTRL_AS   = 'EmailTemplateEditor'
		@DEPS      = ['$modalInstance', 'templateName']

		init: ->

			@$scope.is_new_email = @templateName == null
			if @$scope.is_new_email
				@$scope.$watch('email.email_name', =>
					@$scope.email.email_name = @$scope.email.email_name || ''
					@$scope.email.email_name = @$scope.email.email_name.toLowerCase()
					@$scope.email.email_name = @$scope.email.email_name.replace(/\s/g, '-')
					@$scope.email.email_name = @$scope.email.email_name.replace(/[^a-z0-9\-_\.]/g, '')
					@validateName();
				)

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

				if @$scope.is_new_email
					url = "/templates/" + 'DeskPRO:emails_custom:' + @$scope.email.email_name + '.html.twig'
					postData.create_new = true
				else
					url = "/templates/#{@templateName}"

				@Api.sendPostJson(url, postData).then( (res) =>
					@$scope.saving_template = false
					@$modalInstance.close({
						templateName: res.data.name,
						isNewEmail:   @$scope.is_new_email,
						mode:         'custom'
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

		validateName: ->
			@$scope.email_name_error = null
			if not @customNames then return # custom names might not be loaded yet
			if @customNames.indexOf(@$scope.email.email_name + '.html') != -1
				@$scope.email_name_error = 'exists'

		initialLoad: ->
			if not @$scope.is_new_email
				p = @Api.sendGet("/templates/#{@templateName}").success( (data) =>
					@initTemplateData(data)
				)

				return p
			else
				@initTemplateData({
					name: null,
					email: { email_name: '', template_code: { subject: '', body: '' } }
				})
				p = @Api.sendGet('/email-templates-info').success( (data) =>
					@initCustomNames(data.list['custom'].groups['custom'].templates)
				)
				return p

		initCustomNames: (templates) ->
			@customNames = templates.map((x) -> x.showName.replace(/^.*?\//, ''))
			return

		initTemplateData: (info) ->
			@templateName = info.name
			@email = info
			@$scope.email = @email

	Admin_Templates_Ctrl_EmailTemplateEditor.EXPORT_CTRL()