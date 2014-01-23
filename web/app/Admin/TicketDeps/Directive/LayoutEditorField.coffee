define ->
	class LayoutEditorField
		constructor: (@scope, @element, @attrs, @ngModel, @$modal, @dpObTypesDefTicketCriteria) ->
			@_initEvents()

		_initEvents: ->
			if not @scope.isSticky
				@element.find('.opt_btn').on('click', (ev) =>
					ev.preventDefault()
					@openOptions()
				)

				@element.find('.remove_btn').on('click', (ev) =>
					ev.preventDefault()
					if @scope.removeRow?
						@scope.removeRow()
					else
						@scope.$destroy()
				)
			else
				@element.find('nav').remove()

		openOptions: ->
			if @scope.type == 'user'
				tpl = 'ticketdeps_layouteditor_user_options'
			else
				tpl = 'ticketdeps_layouteditor_agent_options'

			if not @scope.field.options
				@scope.field.options = {}

			inst = @$modal.open({
				templateUrl: tpl,
				controller: ['$scope', '$modalInstance', 'options', 'typeDef', ($scope, $modalInstance, options, typeDef) ->
					if not options.criteria? then options.criteria = {}
					if not options.criteria?.terms then options.criteria.terms = {}
					if not options.criteria?.mode then options.criteria.mode = 'all'

					$scope.options = options

					$scope.with_criteria = false
					if $scope.options.criteria.terms.length
						$scope.with_criteria = true

					$scope.criteriaOptions = []
					$scope.criteriaOptions.push({
						title: 'Department',
						value: 'CheckDepartment'
					})
					$scope.criteriaOptions.push({
						 title: 'Product',
						 value: 'CheckProduct'
					})
					$scope.criteriaOptions.push({
						 title: 'Category',
						 value: 'CheckCategory'
					})
					$scope.criteriaOptions.push({
						 title: 'Priority',
						 value: 'CheckPriority'
					})
					$scope.criteriaOptions.push({
						title: 'Workflow',
						value: 'CheckWorkflow'
					})

					$scope.criteriaTypesDef = typeDef

					$scope.done = ->
						if not $scope.with_criteria
							$scope.options.criteria.terms.length = 0

						$modalInstance.dismiss();
				],
				resolve: {
					options: =>
						return @scope.field.options

					typeDef: =>
						return @dpObTypesDefTicketCriteria
				}
			});

	return [ '$modal', 'dpObTypesDefTicketCriteria', ($modal, dpObTypesDefTicketCriteria) ->
		directive = {}
		directive.restrict    = 'E'
		directive.replace     = true
		directive.templateUrl = "TicketDeps/layout-editor-field.html"
		directive.link = (scope, element, attrs, ngModel) ->
			handler = new LayoutEditorField(scope, element, attrs, ngModel, $modal, dpObTypesDefTicketCriteria)

		return directive
	]