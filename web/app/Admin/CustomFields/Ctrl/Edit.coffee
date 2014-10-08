define [
	'Admin/Main/Ctrl/Base'
], (
	Admin_Ctrl_Base
) ->
	class Admin_CustomFields_Ctrl_Edit extends Admin_Ctrl_Base
		@CTRL_ID = 'Admin_CustomFields_Ctrl_Edit'
		@CTRL_AS = 'EditCtrl'
		@DEPS    = []



		init: ->
			@options =
				expanded: 1
				multiple: 2

			@service = @DataService.get 'CustomFields'
			@$scope.definition = {}
			@$scope.options =
				choices: 0

			@$scope.$watch 'options.choices', (newVal, oldVal) =>
				return if !newVal?
				@$scope.definition.options = {} if !@$scope.definition.options?
				@$scope.definition.options.multiple = @options.multiple == (newVal & @options.multiple)
				@$scope.definition.options.expanded = @options.expanded == (newVal & @options.expanded)



		initialLoad: ->
			if @$stateParams.id
				@service.get(parseInt(@$stateParams.id)).then (model) =>
					return if !model?
					if '[object Array]' == Object.prototype.toString.call( model.options ) then model.options = {}
					@$scope.definition = model
					multiple = if @$scope.definition.options.multiple then @options.multiple else 0
					expanded = if @$scope.definition.options.expanded then @options.expanded else 0
					@$scope.options.choices = @$scope.options.choices | multiple | expanded



		saveForm: ->
			is_new = !@$scope.definition.id

			# todo
			if !@$scope.definition.form_type then @$scope.definition.form_type = 'ContextualChoice'
			if !@$scope.definition.context_class then @$scope.definition.context_class = 'Person'

			promise = @service.set @$scope.definition
			@startSpinner('saving')

			promise.then(
				=>
					@stopSpinner('saving', true).then => @Growl.success('Saved')
					@skipDirtyState()
					if is_new
						@$state.go @$state.current.name.replace /\.(edit|create)$/, '.gocreate'
				(info, code) =>
					@stopSpinner('saving', true)
					@applyErrorResponseToView(info)
			)



		startDelete: ->
			doDelete = => @service.remove @$scope.definition

			@$modal.open({
				templateUrl: @getTemplatePath('CustomField/delete-modal.html'),
				controller: ['$scope', '$modalInstance', '$state', ($scope, $modalInstance, $state) ->
					$scope.confirm = ->
						$scope.is_loading =
							doDelete().then ->
								baseParts = $state.current.name.split '.'
								$state.go baseParts[0] + '.' + baseParts[1]
								$modalInstance.dismiss()

					$scope.dismiss = -> $modalInstance.dismiss();
				]
			});



	Admin_CustomFields_Ctrl_Edit.EXPORT_CTRL()