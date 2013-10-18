define ->
	###
	# The dpOptionBuilder directive is a component that handles a form that adds/removes rows
	# (e.g., a search builder, an option builder etc)
	#
	# The dpOptionBuilder is made up of a few parts:
    #
	# - The options select box which defines the available options to choose from
    #
	# - Type definitions (the typesDef attribute) must be an object that
	# knows how to create the available options. It knows the correct template,
	# knows how to fetch the required data (e.g., options for a select box) and
	# optionally can specify methods to convert model data to and from the stored value.
    #
    # typesDef must have a getDef method that returns an object with these methods:
    # - getTemplate: Returns either a string template or a promise if the template is loaded somewhere else
    # - getData: Returns an object or a promise
    # - getDataFormatter: Optional. An object with getViewValue() to convert the view data into
    #                     a saveable model, and getValue() to convert a saved model to the view.
    #
    # Example
    # -------
    #
    # view.html:
    #
	#    <dp-option-builder types-def="myTypeDef" ng-model="trigger_criteria">
	#    	<select>
	#    		<option value="0">Add criteria</option>
	#    		<option value="my_row">Example</option>
	#    	</select>
	#    </dp-option-builder>
    #
    # myTypeDef.coffee:
	#     {
	#         getDef: (type) ->
	#         	return {
	#     			getTemplate: ->
	#     				return "<dp-optionbuilder-row>{{ title }} <input type="text" ng-model="model.value" /></dp-optionbuilder-row>"
	#
	#     			getData: ->
	#         			return { title: "My Input" }
	#
	#         		getDataFormatter: {
	#         			getViewValue: (value) ->
    #    					return { value: value.my_value }
    #     				getValue: (view_model) ->
    #     					return { my_value: view_model.value }
	#         		}
	#         	}
	#     }
	#
	###
	class DeskPRO_OptionBuilder_Controller
		constructor: ($scope, $element, $attrs, $transclude, dpTemplateManager, $compile, $q) ->
			@dpTemplateManager = dpTemplateManager
			@$scope            = $scope
			@$compile          = $compile
			@element           = $element
			@attrs             = $attrs
			@rows              = {}
			@rowsCount         = 0
			@$q                = $q
			@typesDef          = @$scope.getTypesDef()
			@options           = @$scope.getOptions()

			@els = {}

			$transclude( (clone) =>
				clone.css('width', '100%')
				@element.find('.select2-wrap').append(clone)
			)

			# Select box
			@els.select = @element.find('.select2-wrap').find('select').first()
			@els.select.select2()
			@els.select.on('change', =>
				@els.addBtn.click()
			)

			# The list to append options to
			@els.optionList = @element.find('.dp-ob-options')

			# The no options message
			@els.noOptionsMessage = @els.optionList.find('.dp-ob-no-options')

			# The loading message
			@els.loadingOptionMessage = @els.optionList.find('.dp-ob-loading-options')

			# Add button
			@els.addBtn = @element.find('.add_btn');
			@els.addBtn.on('click', (ev) =>
				ev.preventDefault()
				selected_opt = @els.select.find(':selected').first()

				if not selected_opt[0] or selected_opt.val() == "0"
					@els.select.select2('open')
					return

				@addRow(selected_opt.val(), {})
			)


		###
    	# Add a new row to the form
    	#
    	# @param {String} type
    	# @param {Object} value
		###
		addRow: (type, value) ->
			def = @typesDef.getDef(type)

			tplPromise    = def.getTemplate()
			dataPromise   = def.getData()
			dataFormatter = def.getDataFormatter()

			# They might optionally return values rather than promises
			# so wrap in a promise to simplify the api
			if not @$q.isPromise(tplPromise)
				retTpl = tplPromise
				tplPromise = @$q.fcall(->
					return retTpl
				)
			if not @$q.isPromise(dataPromise)
				retData = dataPromise
				dataPromise = @$q.fcall(->
					return retData
				)

			@els.loadingOptionMessage.show()
			@$q.all([tplPromise, dataPromise]).then( (returns) =>

				tpl  = returns[0]
				data = returns[1]

				option_row = @els.select.find('option[value="'+type+'"]').first()
				if option_row[0]
					option_title = option_row.text()

				@rowsCount++
				rowScope = @$scope.$new()
				rowScope.type = type
				rowScope.type_title = option_title

				if dataFormatter
					rowScope.value = value || {}

					rowScope.model = dataFormatter.getViewValue(rowScope.value, data)

					rowScope.$watch(rowScope.model, (newModel) ->
						rowScope.value = dataFormatter.getValue(newModel, data)
					)

				else
					rowScope.model = {}
					rowScope.value = rowScope.model

				if data
					for own k, v of data
						rowScope[k] = v

				element = @$compile(tpl)(rowScope)
				element.find('.remove-row-trigger').on('click', (ev) =>
					ev.preventDefault()
					@removeRow(element)
				)

				element.data('scopeId', rowScope.$id)
				@els.loadingOptionMessage.hide()
				@els.noOptionsMessage.hide()
				@els.optionList.append(element)
				@rows[rowScope.$id] = {
					element: element,
					scope: rowScope
				}
			)

		###
    	# Removes a row by element
    	#
    	# @param {HTMLElememnt} row
		###
		removeRow: (row) ->
			scopeId = $(row).data('scopeId')
			return @removeRowById(scopeId)


		###
    	# Removes a row by a scope ID
    	#
    	# @param {Integer} scopeId
		###
		removeRowById: (scopeId) ->
			row = @rows[scopeId]

			delete @rows[scopeId]
			row.element.remove()
			row.scope.$destroy()

			@rowsCount--
			if @rowsCount == 0
				@els.noOptionsMessage.show()

			return true


		###
    	# Collect all data from all rows in the builder
    	#
    	# @return {Array}
		###
		collectData: ->
			data = []

			for own scopeId, row of @rows
				r = row.scope.value
				if r
					data.push(r)

			return data

		@FACTORY = [ '$scope', '$element', '$attrs', '$transclude', 'dpTemplateManager', '$compile', '$q', ($scope, $element, $attrs, $transclude, dpTemplateManager, $compile, $q) ->
			return new DeskPRO_OptionBuilder_Controller($scope, $element, $attrs, $transclude, dpTemplateManager, $compile, $q)
		]