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
			console.log("oepn")
			if @scope.type == 'user'
				tpl = 'ticketdeps_layouteditor_user_options'
			else
				tpl = 'ticketdeps_layouteditor_agent_options'

			if not @scope.field.options
				@scope.field.options = {}

			inst = @$modal.open({
				templateUrl: tpl,
				controller: ['$scope', '$modalInstance', 'options', 'typeDef', ($scope, $modalInstance, options, typeDef) ->
					$scope.options = options

					$scope.with_criteria = false
					if $scope.options.criteria.terms.length
						$scope.with_criteria = true

					$scope.criteriaOptions = []
					$scope.criteriaOptions.push({
						title: 'Department',
						value: 'department'
					})
					$scope.criteriaOptions.push({
						 title: 'Product',
						 value: 'product'
					})
					$scope.criteriaOptions.push({
						 title: 'Category',
						 value: 'category'
					})
					$scope.criteriaOptions.push({
						 title: 'Priority',
						 value: 'priority'
					})
					$scope.criteriaOptions.push({
						title: 'Workflow',
						value: 'workflow'
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