define ->
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
    # $scope.my_model = 1024
    # $scope.my_model_alt = {num: 1, size_unit: "kb"}
    #
    # Example View
    # ------------
    # <dp-filesize-with-unit ng-model="my_model" />
    # (1024 will render as "1 kb")
    ###
	DeskPRO_Directive_DpFilesizeWithUnit = [ ->
		return {
			restrict: 'E',
			template: """
				<div class="dp-filesize-unit">
					<input type="text" ng-model="size_num" class="form-control size_num" />
					<select
						ng-model="size_unit"
						ui-select2
						style="min-width: 100px;"
					>
						<option value="b">B</option>
						<option value="kb">KB</option>
						<option value="mb">MB</option>
						<option value="gb">GB</option>
					</select>
				</div>
			""",
			scope: {},
			require: 'ngModel',
			replace: true,
			link: (scope, iElement, iAttrs, ngModel) ->

				scope.size_num = ''
				scope.size_unit = 'mb'

				modelType = 'b';
				objModelKeys = null

				if iAttrs.modelType
					if iAttrs.modelType == 'object' || iAttrs.modelType.indexOf(':') != -1
						modelType = 'object'

						if iAttrs.modelType.indexOf(':') != -1
							objModelKeys = iAttrs.modelType.split(':')
						else
							objModelKeys = ['size', 'unit']
					else if iAttrs.modelType == 'array'
						modelType = 'array'
					else
						modelType = 'b'

				multiplierMap = {
					b:   1,
					kb:  1024,
					mb:  1048576,
					gb:  1073741824,
					tb:  1099511627776
				}

				multiplierTypes = [
					'b',
					'kb',
					'mb',
					'gb',
					'tb'
				]
				multiplierTypes.reverse()

				ngModel.$parsers.push( (viewValue) ->
					unit = viewValue.unit || 'kb'
					num  = viewValue.num || 1

					switch modelType
						when "object"
							obj = {}
							obj[objModelKeys[0]] = num
							obj[objModelKeys[1]] = unit
							return obj
						when "array"
							arr = [num, unit]
							return arr
						else
							secs = multiplierMap[unit] * num
							return secs
				)

				ngModel.$formatters.push( (modelValue) ->
					unit = false
					num  = 0

					if modelType == null or modelType == "" or not modelType or isNaN(modelType)
						modelType = 0

					switch modelType
						when "object"
							if modelValue?[objModelKeys[0]]?
								unit = modelValue[objModelKeys[0]]
							if modelValue?[objModelKeys[1]]?
								num = modelValue[objModelKeys[1]]
						when "array"
							if modelValue?[1]?
								unit = modelValue[1]
							if modelValue?[0]?
								num = modelValue[0]
						else
							modelValue = parseInt(modelValue || 0)
							if isNaN(modelValue)
								modelValue = 0

							for unitName in multiplierTypes
								if unitName == 'b' then continue
								if modelValue % multiplierMap[unitName] == 0
									unit = unitName
									break

							if not unit
								for unitName in multiplierTypes
									if unitName == 'b' then continue
									if (modelValue / multiplierMap[unitName]) >= 1.0
										unit = unitName
										break

							if not unit
								unit = 'kb'

							if modelValue
								num = modelValue / multiplierMap[unit]

					return {
						unit: unit,
						num:  parseFloat(num).toFixed(2)
					}
				)

				scope.$watch('size_unit + size_num', ->
					if scope.size_unit and scope.size_num
						ngModel.$setViewValue({
							unit: scope.size_unit
							num:  parseInt(scope.size_num)
						})
				)

				ngModel.$render = ->
					viewValue = ngModel.$viewValue
					if viewValue
						scope.size_num  = viewValue.num
						scope.size_unit = viewValue.unit
						iElement.find('select').first().select2('val', viewValue.unit)

				ngModel.$render()
		}
	]

	return DeskPRO_Directive_DpFilesizeWithUnit