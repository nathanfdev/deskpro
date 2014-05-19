define ['DeskPRO/Util/Numbers'], (Numbers) ->
	###
    # Description
    # -----------
    #
    # This directive adds a new form element for a filesize described as a number and a unit. For example,
    # "2 mb" or "5 gb". In the model, the number is saved as the size in bytes.
    #
    # Add a "model-type" attribute to the element to change how the time is represented in the model:
    # - bytes (default): Convert size into bytes. E.g., 1 kb is saved as 1024
    # - array: Save as an array: [size, unit]. E.g., 1 kb is [1, 'kb']
    # - object: Save in an object: { size: size, unit: unit}. E.g., 1 kb is {size: 1, unit: 'kb'}
    # - "X:Y": Save in an object using X and Y as keys: {X: size, Y: unit}
    #
    # Example Controller
    # ------------------
    # $scope.my_model = 8
    #
    # Example View
    # ------------
    # <select dp-closest-number ng-model="my_model">
    #     <option value="0">0</option>
    #     <option value="0">5</option>
    #     <option value="0">10</option>
    #     <option value="0">15</option>
    # </select>
    # (Will render with option 10)
    ###
	DeskPRO_Directive_DpClosestNumber = [ ->
		return {
			restrict: 'A',
			require: 'ngModel',
			link: (scope, iElement, iAttrs, ngModel) ->

				valuesExpr = if iAttrs.numberValues? && iAttrs.numberValues then iAttrs.numberValues else null

				getValues = ->
					if valuesExpr
						values = scope.$eval(valuesExpr).map((n) -> parseInt(n))
					else
						values = []
						iElement.find('option').each(-> values.push(parseInt(this.value)))
					return values

				ngModel.$formatters.push( (modelValue) ->
					return Numbers.closest(modelValue, getValues())
				)
		}
	]

	return DeskPRO_Directive_DpClosestNumber