define ->
	###
    # Description
    # -----------
    #
    # This directive adds a new form element for a time period described as a number and a unit. For example,
    # "2 days" or "5 hours". In the model, the number is saved as the time in seconds.
    #
    # Example Controller
    # ------------------
    # $scope.my_model = 7200
    #
    # Example View
    # ------------
    # <dp-time-with-unit ng-model="my_model" />
    # (7200 will render as "2 hours")
    ###
	DeskPRO_Directive_DpTimeWithUnit = [ ->
		return {
			restrict: 'E',
			template: """
				<div class="dp-time-unit">
					<input type="text" ng-model="time_num" class="form-control time_num" />
					<select
						ng-model="time_unit"
						ui-select2
						style="min-width: 100px;"
					>
						<option value="secs">seconds</option>
						<option value="mins">minutes</option>
						<option value="hours">hours</option>
						<option value="days">days</option>
						<option value="weeks">weeks</option>
						<option value="months">months</option>
						<option value="years">years</option>
					</select>
				</div>
			""",
			require: 'ngModel',
			replace: true,
			link: (scope, iElement, iAttrs, ngModel) ->
				multiplierMap = {
					secs:   1,
					mins:   60,
					hours:  3600,
					days:   86400,
					weeks:  604800,
					months: 2419200,
					years:  31536000
				}

				multiplierTypes = [
					'secs',
					'mins',
					'hours',
					'days',
					'weeks',
					'months',
					'years'
				]

				ngModel.$parsers.push( (viewValue) ->
					unit = viewValue.unit || 'secs'
					num  = viewValue.num || 1

					return multiplierMap[unit] * num
				)

				ngModel.$formatters.push( (modelValue) ->
					unit = null
					modelValue = parseInt(modelValue)

					for unitName in multiplierTypes.reverse()
						if modelValue % multiplierMap[unitName] == 0
							unit = unitName
							break

					if not unit
						unit = 'secs'

					return {
						unit: unit,
						num:  modelValue / multiplierMap[unit]
					}
				)

				scope.$watch('time_unit + time_num', ->
					if scope.time_unit and scope.time_num
						ngModel.$setViewValue({
							unit: scope.time_unit
							num:  parseInt(scope.time_num)
						})
				)

				ngModel.$render = ->
					viewValue = ngModel.$viewValue
					if viewValue
						scope.time_num  = viewValue.num
						scope.time_unit = viewValue.unit

				ngModel.$render()
		}
	]

	return DeskPRO_Directive_DpTimeWithUnit