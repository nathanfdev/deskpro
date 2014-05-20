define ['Admin/Main/Ctrl/Base'], (Admin_Ctrl_Base) ->
	class Admin_Labels_Base_Ctrl_Edit extends Admin_Ctrl_Base
		@CTRL_AS = 'LabelsEdit'
		@DEPS = ['em', '$stateParams', '$rootScope', 'LabelManager']

		init: ->
			@api_endpoint = ''
			@ng_route = ''
			@typename = ''
			@old_label = ''
			@$scope.form = { label: '' }
			return

		initialLoad: ->
			if @$stateParams.label
				get_label = @Api.sendGet("#{@api_endpoint}/get", {
					label: @$stateParams.label
				}).then( (result) =>
					rec = @em.createEntity(@typename, 'label', { label: result.data.label })

					@is_new = false
					@old_label = result.data.label
					@label_object = { label: @old_label }
					@$scope.form.label = result.data.label
				)

				return get_label
			else
				@is_new = true
				@$scope.form.label = ''
				return null

		addNewLabel: ->
			return false if not @$scope.form.label

			@startSpinner('saving_label')
			@Api.sendPost(@api_endpoint, {label: @$scope.form.label}).success(=>
				@stopSpinner('saving_label', true).then(=>
					@Growl.success(@getRegisteredMessage('saved_label'))
					@LabelManager.addLabel(@api_endpoint, @$scope.form.label)
				)
				@skipDirtyState()
				@$state.go("#{@ng_route}.gocreate")
			).error(=>
				@Growl.error(@getRegisteredMessage('not_created_label'))
			).finally(=>
				@stopSpinner('saving_label', true)
			)

		saveLabel: ->
			return false if not @$scope.form.label
			return false if @old_label == @$scope.form.label

			if @is_new
				return @addNewLabel()

			@startSpinner('saving_label')
			@Api.sendPost("#{@api_endpoint}/save", {
				label_old: @old_label, label_new: @$scope.form.label
			}).success(=>
				@stopSpinner('saving_label', true).then(=>
					@Growl.success(@getRegisteredMessage('saved_label'))
					@LabelManager.renameLabel(@api_endpoint, @old_label, @$scope.form.label)
					@old_label = @$scope.form.label
					@label_object = { label: @old_label }
				)
				@skipDirtyState()
			).error(=>
				@Growl.error(@getRegisteredMessage('not_saved_label'))
			).finally(=>
				@stopSpinner('saving_label', true)
			)

		isDirtyState: ->
			if @$scope.form.label != @old_label then return true
			return false