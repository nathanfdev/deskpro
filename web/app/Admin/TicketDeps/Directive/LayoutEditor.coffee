define [
	'angular',
	'DeskPRO/Util/Arrays'
], (
	angular,
	Arrays
) ->
	class InterfaceHandler
		constructor: (scope, element, attr, ngModel, $compile, logger) ->
			@scope    = scope
			@element  = element
			@ngModel  = ngModel
			@$compile = $compile
			@logger   = logger

			@scope.form_tab = 'user'

			@els = {}
			@els.user_tab        = @element.find('.user-form')
			@els.user_worksheet  = @els.user_tab.find('.form-worksheet')
			@els.agent_tab       = @element.find('.agent-form')
			@els.agent_worksheet = @els.agent_tab.find('.form-worksheet')

			@required_fields = {
				user: [],
				agent: []
			}

			@_initTab('user', @els.user_tab)
			@_initTab('agent', @els.agent_tab)

			@ngModel.$formatters.push( (modelValue) =>
				# Make sure the basic data structure exists
				if not modelValue
					modelValue = {}
				if not modelValue.user?
					modelValue.user = []
				if not modelValue.agent?
					modelValue.agent = []

				for fieldType in @required_fields.user
					has = false
					for f in modelValue.user
						if not f.id
							if f.field_id
								f.id = "#{f.field_type}_#{f.field_id}"
							else
								f.id = f.field_type

						if f.field_type == fieldType
							has = true

					if not has
						modelValue.user.push(@createFieldValue(fieldType))

				for fieldType in @required_fields.agent
					has = false
					for f in modelValue.agent
						if not f.id
							if f.field_id
								f.id = "#{f.field_type}_#{f.field_id}"
							else
								f.id = f.field_type

						if f.field_type == fieldType
							has = true
					if not has
						modelValue.agent.push(@createFieldValue(fieldType))

				for f, i in modelValue.user
					f.display_order = i
				for f, i in modelValue.agent
					f.display_order = i

				return modelValue
			)

			@ngModel.$parsers.push( (viewModel) =>
				return viewModel
			)

			@ngModel.$render = =>
				@render()

		_initTab: (tabType, tab) ->
			me = @
			ngModel = @ngModel

			requiredFields = @required_fields[tabType]
			tab.find('.dp-layout-editor-layout-field').filter('[data-is-required]').each(->
				requiredFields.push($(this).data('field-type'))
			)

			# moves disabled items to end of the list
			tab.find('.form-elements').find('li.disabled').each(->
				el = $(this)
				parent = el.closest('ul')
				el.detach().appendTo(parent)
			)
			tab.find('.form-elements').find('li').not('.disabled').draggable({
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
						fieldType = ui.item.data('field-type')
						fieldId   = ui.item.data('field-id') || null
						me.logger.debug("[#{tabType}] Dragged #{fieldType}_#{fieldId || '0'}")
						fieldRow = me.createAndAddField(
							tabType,
							fieldType,
							fieldId,
							ui.item
						)
						ui.item.remove()

						if fieldRow
							fid = fieldRow.data('field-id')
							tab.find("[data-fid=\"#{fid}\"]").hide()

				update: ->
					orderMap = {}
					tab.find('.form-worksheet').find('ul').find('li').each( (i) ->
						fid = $(this).data('field-id')
						if fid then orderMap[fid] = i
					)
					if ngModel.$modelValue[tabType] and ngModel.$modelValue[tabType].length
						for f in ngModel.$modelValue[tabType]
							f.display_order = orderMap[f.id] || 0
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
					@logger.info("[#{tabType}] {createAndAddField} Already has #{field.id}")
					return null

			row = @createFieldRow(tabType, field)

			insertAt = null
			if insertAfterEl
				insertAt = $(insertAfterEl).parent().find('.layout-field').index(insertAfterEl)
				row.insertAfter(insertAfterEl)
			else
				if tabType == 'user'
					ul = @els.user_worksheet.find('ul').first()
				else
					ul = @els.agent_worksheet.find('ul').first()

				ul.append(row)

			if insertAt == null
				viewValue[tabType].push(field)
			else
				Arrays.insertAtIndex(viewValue[tabType], field, insertAt)

			@ngModel.$setViewValue(viewValue)

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
					on_viewticket_mode: "value",
					on_editticket: true
					criteria: {
						mode: "all",
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

				tab = @els["#{tabType}_tab"].find('.form-elements')
				tab.find("[data-fid=\"#{field.id}\"]").show()

			if field.id in @required_fields[tabType]
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
			forms = [
				{ typeName: 'user',  worksheetName: 'user_worksheet' },
				{ typeName: 'agent', worksheetName: 'agent_worksheet' }
			]

			for form in forms
				typeName    = form.typeName
				form_model  = @ngModel.$viewValue[form.typeName]
				worksheetEl = @els[form.worksheetName]
				listEl      = worksheetEl.find('ul').first()

				layoutFieldEls = worksheetEl.find('.layout-field');

				draggableEls = @els["#{typeName}_tab"].find('.form-elements')
				draggableEls.show()
				draggableEls.find('li').each( ->
					$el = $(this)
					field_id = $el.data('field-id') || null
					if field_id
						fid = $el.data('field-type') + '_' + field_id
					else
						fid = $el.data('field-type')

					$el.data('fid', fid).attr('data-fid', fid)
				)

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

				if doReorder
					layoutFieldEls.detach()
					for field, order in form_model
						fieldEl = layoutFieldEls.filter('.field-' + field.id)
						fieldEl.appendTo(listEl)

				for f in form_model
					draggableEls.find("[data-fid=\"#{f.id}\"]").hide();

	return ['$compile', 'LoggerManager', ($compile, LoggerManager) ->
		directive = {}
		directive.restrict    = 'E'
		directive.require     = 'ngModel'
		directive.templateUrl = "TicketDeps/layout-editor.html"
		directive.replace     = true
		directive.scope       = {}

		directive.link = (scope, element, attrs, ngModel) ->
			logger = LoggerManager.get('directive.dpLayoutEditor')
			interfaceHandler = new InterfaceHandler(scope, element, attrs, ngModel, $compile, logger)

		return directive
	]