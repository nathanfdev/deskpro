define [
	'Admin/Main/Ctrl/Base'
], (
	Admin_Ctrl_Base
) ->
	class Admin_CustomFields_Base_Ctrl_Edit extends Admin_Ctrl_Base
		@CTRL_ID = 'Admin_CustomFields_Base_Ctrl_Edit'
		@CTRL_AS = 'EditCtrl'
		@DEPS    = []

		init: ->
			@field_id = parseInt(@$stateParams.id || 0)
			@field_type = '0'
			@field_type_chooser = 'text'
			@fieldDataService = @getDataService()
			return

		postLoad: ->
			return

		initialLoadExtra: ->
			return

		initialLoad: ->
			p = @initialLoadExtra()
			promise = @fieldDataService.loadEditFieldData(@$stateParams.id || null).then( (data) =>
				@field      = data.field
				@field_type = data.field_type
				@form       = data.form
				@postLoad()
			)

			if p
				return @$q.all([promise, p])
			else
				promise

		getDataService: ->
			throw new Error("Not implemented")

		getBaseRouteName: ->
			throw new Error("Not implemented")

		postSave: ->
			return

		saveForm: ->
			is_new = !@field.id

			@field.type_name = @field_type
			promise = @fieldDataService.saveFormModel(@field, @form)

			@startSpinner('saving')

			successFn = =>
				@stopSpinner('saving', true).then(=>
					@Growl.success('Saved')
				)

				@skipDirtyState()

				if is_new
					@$state.go(@getBaseRouteName() + ".gocreate")

			promise.success( (data) =>

				if not @field_id
					@field.id = data.field_id
					@field_id = data.field_id

				v = @postSave()
				if v and v.then
					v.then(-> successFn())
				else
					successFn()
			)

			promise.error((info, code) =>
				@stopSpinner('saving', true)
				@applyErrorResponseToView(info)
			)

		startDelete: ->
			doDelete = =>
				@fieldDataService.deleteFieldById(@field_id)

			baseRouteName = @getBaseRouteName()
			@$modal.open({
				templateUrl: @getTemplatePath('CustomField/delete-modal.html'),
				controller: ['$scope', '$modalInstance', '$state', ($scope, $modalInstance, $state) ->
					$scope.confirm = ->
						$scope.is_loading =
						doDelete().then(->
							baseParts = baseRouteName.split '.'
							$state.go baseParts[0] + '.' + baseParts[1]
							$modalInstance.dismiss()
						)

					$scope.dismiss = ->
						$modalInstance.dismiss();
				]
			});
