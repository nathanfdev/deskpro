define ['DeskPRO/Util/Util'], (Util) ->
	class LayoutEditorField
		constructor: (@scope, @element, @attrs, @ngModel, @$modal, @dpObTypesDefTicketCriteria, TicketFields, UserFields, $q, $timeout, TicketFieldsPerPerson, TicketFieldsPerOrg) ->

			@scope.ticketFieldTitleFilter = (f) =>
				@scope.field.field_type == 'ticket_field' and (f.id+'') == (@scope.field.field_id+'')
			@scope.userFieldTitleFilter = (f) =>
				@scope.field.field_type == 'user_field' and (f.id+'') == (@scope.field.field_id+'')
			@scope.CustomFieldTitleFilter = (f) =>
				@scope.field.field_type == 'custom_field' and (f.id+'') == (@scope.field.field_id+'')

			$q.all([TicketFields.loadList(), UserFields.loadList(), TicketFieldsPerPerson.all(), TicketFieldsPerOrg.all()])
			.then (results) =>
				@scope.custom_ticket_fields   = results[0]
				@scope.custom_user_fields     = results[1]
				@scope.ticket_fields_per_person = results[2]
				@scope.ticket_fields_per_org  = results[3]

				$timeout(=>
					@_initEvents()
				, 1)


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

			field = @scope.field

			inst = @$modal.open({
				templateUrl: tpl,
				controller: ['$scope', '$modalInstance', 'options', 'typeDef', ($scope, $modalInstance, options, typeDef) ->
					if not options.criteria? then options.criteria = {}
					if not options.criteria?.terms then options.criteria.terms = {}
					if not options.criteria?.mode then options.criteria.mode = 'all'

					$scope.formOptions = {
						with_criteria: false
					}

					if Util.isArray(options.criteria.terms)
						terms = {}
						for t in options.criteria.terms
							terms[Util.uid('t')] = t
						options.criteria.terms = terms

					for own _ of options.criteria.terms
						$scope.formOptions.with_criteria = true
						break

					$scope.options = options

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

					$scope.dismiss = -> $modalInstance.dismiss();

					$scope.done = ->
						if not $scope.formOptions.with_criteria
							$scope.options.criteria.terms = {}

						$modalInstance.dismiss();
				],
				resolve: {
					options: =>
						return @scope.field.options

					typeDef: =>
						return @dpObTypesDefTicketCriteria
				}
			});

	return [ '$modal', 'dpObTypesDefTicketCriteria', 'DataService', '$q', '$timeout', ($modal, dpObTypesDefTicketCriteria, DataService, $q, $timeout) ->
		directive = {}
		directive.restrict    = 'E'
		directive.replace     = true
		directive.templateUrl = "TicketDeps/layout-editor-field.html"

		TicketFields = DataService.get('TicketFields')
		UserFields   = DataService.get('UserFields')
		TicketFieldsPerPerson = DataService.get 'CustomFields', 'ticket', 'person'
		TicketFieldsPerOrg = DataService.get 'CustomFields', 'ticket', 'organization'

		directive.link = (scope, element, attrs, ngModel) ->
			if not scope.field.options                 then scope.field.options = {}
			if not scope.field.options.criteria?       then scope.field.options.criteria = {}
			if not scope.field.options.criteria?.terms then scope.field.options.criteria.terms = {}
			if not scope.field.options.criteria?.mode  then scope.field.options.criteria.mode = 'all'

			handler = new LayoutEditorField(
				scope,
				element,
				attrs,
				ngModel,
				$modal,
				dpObTypesDefTicketCriteria,
				TicketFields,
				UserFields,
				$q,
				$timeout,
				TicketFieldsPerPerson
				TicketFieldsPerOrg
			)

		return directive
	]