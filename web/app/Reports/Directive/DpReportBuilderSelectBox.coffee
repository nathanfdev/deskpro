define ->
	###
   # Description
   # -----------
 		#
 		#	Example View
 		#	------------
 		#	<dp-report-builder-select-box
 		#				  value-to-decorate="scope.title"
 		#				  possible-values="scope.some_object">
 		#	</span>
 		#
 		#	Parameters
 		#	------------
 		# 1) 'value-to-decorate' (required parameter) - ...
 		# 2) 'possible-values' (required parameter) - ...
   #
	###
	Reports_Directive_DpReportBuilderSelectBox = ['$compile', ($compile) ->
		return {
			restrict: 'AE',
			scope: {},
			template: """
													<span class="title-text">
														<span ng-repeat="text in texts">
															<span style="vertical-align:middle;">{{ text }}</span>
															<select ng-model="selected[$index]" ui-select2>
																<option ng-repeat="option in options[$index]" ng-value="option.value" ng-selected="selected[$parent.$index] == option.value">
																	{{ option.label }}
																</option>
															</select>
														</span>
													</span>

													"""
			link: (scope, element, attrs) ->

				# Instead of this fake data there should be real data (parsed by build directive content function)

				scope.texts = ['Number of tickets created','grouped by',' & ']
				scope.options = [[{value: 'yesterday', label: 'Yesterday'}, {value: 'today', label: 'Today'}, {value: '123', label: '123'}, {value: '456', label: '456'}]
																					[{value: 'department', label: 'Department'}, {value: 'agent', label: 'Agent'}]
																					[{value: 'department', label: 'Department'}, {value: 'agent', label: 'Agent'}]
				]
				scope.selected = ['today', 'agent', 'department']

				scope.$watch(attrs.possibleValues, (newVal, oldVal) =>

					if typeof newVal == 'undefined' then return
					valueToDecorate = scope.$eval(attrs.valueToDecorate)
					if !valueToDecorate then return

					#scope.finalText = $sce.trustAsHtml(_parseTokensAndReplaceThem(valueToDecorate))
					buildDirectiveContent(valueToDecorate)
				)


				###
				# This function builds directive by constructing it on 'the fly' using DOM operations
 			# The reason for doing so - problems with inner directives that were compiled with $compile() functionality
				###

				buildDirectiveContent = (value) ->

					#directiveElement = angular.element('<span')

					newValue = value
					regex = /<(\d+:.+?)>/g

					#while match = regex.exec(value)
						#directiveElement.append(newValue.replace(match[0], ''))
						#directiveElement.append(_returnSelectBoxElement(match[1]))

					#element.append(directiveElement)

						#newValue = newValue.replace(match[0], _addSelectBoxes(match[1]))

				# returning select box element that was rendered according to 'value' parameter
				_returnSelectBoxElement = (value) ->

					possibleValues = scope.$eval(attrs.possibleValues)
					choices = {}
					extras = {}
					options = []

					# some regular expressions parsing...

					if value.match(/^\d+:date group(.*)$/)
						choices = possibleValues.dates
						extrasMatch = RegExp.$1
					else if value.match(/^\d+:field group:([a-zA-Z0-9_]+)(.*)$/)
						type = RegExp.$1
						if typeof possibleValues.fields[type] != 'undefined'
							choices = possibleValues.fields[type]
							extrasMatch = RegExp.$2
					else if value.match(/^\d+:status group:([a-zA-Z0-9_]+)(.*)$/)
						type = RegExp.$1
						if typeof possibleValues[type] != 'undefined'
							choices = possibleValues.statuses[type]
							extrasMatch = RegExp.$2
					else if value.match(/^\d+:order group:([a-zA-Z0-9_]+)(.*)$/)
						type = RegExp.$1
						if typeof possibleValues[type] != 'undefined'
							choices = possibleValues.orders[type]
							extrasMatch = RegExp.$2

					# information about default group...

					if extrasMatch
						regex = /,([a-zA-Z0-9_ ]+):([^,]+)/g
						while match = regex.exec(extrasMatch)
							extras[$.trim(match[1])] = $.trim(match[2])

					# constructing selects...

					for own key, choice_value of choices
						#selected = if extras?.default? == key then true else false
						#if extras.default == key then console.log key, choice_value[0]
						options.push({value: key, label: choice_value[0]})

					selectElement = angular.element("""
																															<select ng-change="selectHandler()"
																																ng-model="selectedOption"
																																ng-options="opt as opt.label for opt in options">
																															</select>
																															""")

					$compile(selectElement)(scope)

					return selectElement

				scope.selectHandler = () ->
					alert 'ok'
		}
	]

	return Reports_Directive_DpReportBuilderSelectBox