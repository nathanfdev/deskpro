define [
	'Admin/Main/Ctrl/Base'
	'angular'
], (
	Admin_Ctrl_Base
	angular
) ->
	class Admin_CustomFields_Ctrl_Edit extends Admin_Ctrl_Base
		@CTRL_ID = 'Admin_CustomFields_Ctrl_Edit'
		@CTRL_AS = 'EditCtrl'
		@DEPS    = []



		init: ->
			data = @$state.current.data
			@service = @DataService.get 'CustomFields', data.owner, data.context

			@$scope.definition =
				form_type: 'contextual_choice'
				context_class: data.context
				options: {}

			@options =
				expanded: 1
				multiple: 2

			@$scope.options =
				choices: 0



		initialLoad: ->
			return if !@$stateParams.id

			@service.get(parseInt(@$stateParams.id)).then (model) =>
				return if !model?

				if '[object Array]' == Object.prototype.toString.call( model.options ) then model.options = {}
				@$scope.definition = angular.copy model

				multiple = if @$scope.definition.options.multiple then @options.multiple else 0
				expanded = if @$scope.definition.options.expanded then @options.expanded else 0
				@$scope.options.choices = @$scope.options.choices | multiple | expanded



		saveForm: ->
			is_new = !@$scope.definition.id

			@$scope.definition.options.multiple = @options.multiple == (@$scope.options.choices & @options.multiple)
			@$scope.definition.options.expanded = @options.expanded == (@$scope.options.choices & @options.expanded)

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