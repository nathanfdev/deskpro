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
						me.createAndAddField(
							tabType,
							ui.item.data('field-type'),
							ui.item.data('field-id') || null,
							ui.item
						)
						ui.item.remove()
			})


		###
    	# Create a new field, add it to the model and also add it to the UI
    	#
    	# @param {String} tabType
    	# @param {String} fieldType
    	# @param {Integer} fieldId
    	# @param {HTMLElement} insertAfterEl
		###
		createAndAddField: (tabType, fieldType, fieldId = null, insertAfterEl = null) ->
			viewValue = @ngModel.$viewValue
			if not viewValue
				viewValue = {}
			if not viewValue[tabType]
				viewValue[tabType] = []

			field = @createFieldValue(fieldType, fieldId || null)

			for f in viewValue[tabType]
				# Already has field of this type,
				# so we will ignore this drop
				if f.id == field.id
					return null

			viewValue[tabType].push(field)
			@ngModel.$setViewValue(viewValue)

			row = @createFieldRow(tabType, field)

			if insertAfterEl
				row.insertAfter(insertAfterEl)
			else
				if tabType == 'user'
					ul = @els.user_worksheet.find('ul').first()
				else
					ul = @els.agent_worksheet.find('ul').first()

				ul.append(row)

			return row


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
				field_id:      fieldId,
				options: {
					on_newticket: true,
					on_viewticket: true,
					on_viewticket_mode: "VALUE",
					on_editticket: true
					criteria: {
						mode: "ALL",
						terms: []
					}
				}
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

			fieldScope.removeRow = =>
				viewValue = @ngModel.$viewValue[tabType]
				for f, idx in viewValue
					if f == field
						viewValue.splice(idx, 1)
						break

				fieldRow.remove()
				fieldScope.$destroy()

			if field.id in ['subject', 'message', 'user_email']
				fieldScope.removeRow = ->
					return
				fieldScope.isSticky = true

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
				if not @ngModel.$viewValue
					@ngModel.$viewValue = {}
				if not @ngModel.$viewValue[form.modelName]
					@ngModel.$viewValue[form.modelName] = []

				typeName    = form.typeName
				form_model  = @ngModel.$viewValue?[form.modelName]
				worksheetEl = @els[form.worksheetName]
				tabEl       = @els["#{form.typeName}_tab"]
				listEl      = worksheetEl.find('ul').first()

				stickyFields = tabEl.find('.dp-layout-editor-layout-field').filter('[data-is-required]')
				stickyFieldIds = {}
				stickyFields.each(->
					id = $(this).data('field-type')
					stickyFieldIds[id] = $(this)
				)

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

				# Check for required elements
				x = form_model.length
				for own id, fieldEl of stickyFieldIds
					if not elementMap[id]
						field = @createFieldValue(id)
						orderMap[id] = field
						newFields.push(field)
						x++

				# Remove elements
				layoutFieldEls.each( ->
					fieldId = $(this).data('field-id')
					if not elementMap[fieldId]
						$(this).remove()
				)

				# Add new elements
				for field in newFields
					fieldRow = @createFieldRow(typeName, field)
					elementMap[field.id] = fieldRow
					order = orderMap[field.id]

					if order == 0
						listEl.prepend(fieldRow)
					else
						prevField = form_model[order-1]
						if prevField
							prevFieldEl = elementMap[prevField.id]
							fieldRow.insertAfter(prevFieldEl)
						else
							listEl.append(fieldRow)

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

				if doReorder and false
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