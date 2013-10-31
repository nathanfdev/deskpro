define ['angular'], (angular) ->
	class InterfaceHandler
		constructor: (scope, element, attr, ngModel, $compile) ->
			@scope    = scope
			@element  = element
			@ngModel  = ngModel
			@$compile = $compile

			@scope.form_tab = 'user'

			@els = {}
			@els.user_tab        = @element.find('.user-form')
			@els.user_worksheet  = @els.user_tab.find('.form-worksheet')
			@els.agent_tab       = @element.find('.agent-form')
			@els.agent_worksheet = @els.agent_tab.find('.form-worksheet')

			@ngModel.$render = =>
				@render()

			@_initTab('user', @els.user_tab)
			@_initTab('agent', @els.agent_tab)

		_initTab: (tabType, tab) ->
			me = @
			ngModel = @ngModel
			scope = @scope

			tab.find('.form-elements').find('li').draggable({
				appendTo: 'body',
				helper: 'clone',
				connectToSortable: tab.find('.form-worksheet').find('ul')
			})
			tab.find('.form-worksheet').find('ul').sortable({
				items: "> li",
				axis: 'y',
				handle: '.drag_handle',
				stop: (event, ui) ->
					if ui.item?.hasClass('dp-layout-editor-layout-field')
						viewValue = ngModel.$viewValue
						if not viewValue
							viewValue = {}
						if not viewValue[tabType]
							viewValue[tabType] = []

						field = me.createFieldValue(
						  ui.item.data('field-type')
						)

						for f in viewValue[scope.form_tab]
							# Already has field of this type,
							# so we will ignore this drop
							if f.id == field.id
								ui.item.remove()
								return

						viewValue[tabType].push(field)
						ngModel.$setViewValue(viewValue)

						row = me.createFieldRow(tabType, field)
						row.insertAfter(ui.item)
						ui.item.remove()
			})

		###
    	# Creates a new field object
    	#
    	# @return {Object}
    	###
		createFieldValue: (fieldType, fieldId = null) ->
			id = fieldType
			if fieldId
				id += '_' + fieldId

			layoutField = {
				id:            id
				field_type:    fieldType,
				field_id:      fieldId
			}

			return layoutField


		###
    	# Renders a new field row
    	#
    	# @param {Object} field
    	# @return {HTMLElement}
    	###
		createFieldRow: (tabType, field) ->
			fieldScope = @scope.$new(true)
			fieldScope.field = field
			fieldScope.type  = tabType

			fieldRow = @$compile("""
						<li class="layout-field"><dp-ticket-layout-editor-field type="#{tabType}" ng-model="field" /></li>
					""")(fieldScope)
			fieldRow.data('field-id', field.id).addClass("field-#{field.id}")

			return fieldRow

		###
    	# Renders options on the left (worksheet) with those saved in the model
    	# Tries to be smart in what it is re-rendering so only changes are rendered.
		###
		render: ->
			console.log("[LayoutEditor] render")
			forms = [
				{ typeName: 'user',  modelName: 'user_form',  worksheetName: 'user_worksheet' },
				{ typeName: 'agent', modelName: 'agent_form', worksheetName: 'agent_worksheet' }
			]

			for form in forms
				if not @ngModel.$viewValue?[form.modelName] then continue
				form_model  = @ngModel.$viewValue?[form.modelName]
				worksheetEl = @els[form.worksheetName]
				listEl      = worksheetEl.find('ul').first()

				layoutFieldEls = worksheetEl.find('.layout-field');

				orderMap = {}
				elementMap = {}

				# Check for new elements
				newFields = []
				for field, order in form_model
					fieldEl = layoutFieldEls.filter('.field-' + field.id)
					if not fieldEl[0]
						newFields.push(field)
					else
						elementMap[field.id] = fieldEl

					orderMap[field.id] = order

				# Remove elements
				layoutFieldEls.each( ->
					fieldId = $(this).data('field-id')
					if not elementMap[fieldId]
						$(this).remove()
				)

				# Add new elements
				for field in newFields
					fieldRow = @createFieldRow(field, typeName)
					elementMap[field.id] = fieldRow
					order = orderMap[field.id]

					if order == 0
						listEl.prepend(fieldRow)
					else
						prevField = form_model[order-1]
						prevFieldEl = elementMap[prevField.id]
						fieldRow.insertAfter(prevFieldEl)

				# Verify order
				doReorder = false
				layoutFieldEls = worksheetEl.find('.layout-field')
				layoutFieldEls.each( (currentOrder) ->
					fieldId = $(this).data('field-id')
					expectedOrder = orderMap[fieldId] || 0

					if currentOrder != expectedOrder
						doReorder = true
						return false
				)

				if doReorder
					layoutFieldEls.detach()
					for field, order in form_model
						fieldEl = layoutFieldEls.filter('.field-' + field.id)
						fieldEl.appendTo(listEl)

	return ['$compile', ($compile) ->
		directive = {}
		directive.restrict    = 'E'
		directive.require     = 'ngModel'
		#directive.controller  = ['$scope', EditorController]
		directive.templateUrl = "TicketDeps/layout-editor.html"
		directive.replace     = true

		directive.link = (scope, element, attrs, ngModel) ->
			interfaceHandler = new InterfaceHandler(scope, element, attrs, ngModel, $compile)

		return directive
	]