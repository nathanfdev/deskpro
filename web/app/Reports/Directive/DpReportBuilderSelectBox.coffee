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
			template: """
													<span class="title-text">
														<span ng-repeat="text in texts">
															<span style="vertical-align:middle;">{{ text }}</span>
															<select ng-model="selected[$index]" ui-select2 style="min-width:70px;">
																<option ng-repeat="option in options[$index]" ng-value="option.value" ng-selected="selected[$parent.$index] == option.value">
																	{{ option.label }}
																</option>
															</select>
														</span>
													</span>

													"""
			link: (scope, element, attrs) ->

				###

 			Below vairbales will look liek following

 			scope.texts = ['Number of tickets created','grouped by',' & ']
				scope.options = [[{value: 'yesterday', label: 'Yesterday'}, {value: 'today', label: 'Today'}, {value: '123', label: '123'}, {value: '456', label: '456'}]
																					[{value: 'department', label: 'Department'}, {value: 'agent', label: 'Agent'}]
																					[{value: 'department', label: 'Department'}, {value: 'agent', label: 'Agent'}]
				]
				scope.selected = ['today', 'agent', 'department']

 			###

				scope.texts = []
				scope.options = []
				scope.selected = []

				scope.$watch(attrs.possibleValues, (newVal, oldVal) =>

					if typeof newVal == 'undefined' then return
					valueToDecorate = scope.$eval(attrs.valueToDecorate)
					if !valueToDecorate then return

					buildDirectiveVariables(valueToDecorate)
				)


				###
				# This function builds directive by constructing it on 'the fly' using DOM operations
 			# The reason for doing so - problems with inner directives that were compiled with $compile() functionality
				###

				buildDirectiveVariables = (value) ->

					regex = /([\w\s\&,]*)(<(\d+:.+?)>)/g

					while match = regex.exec(value)
						scope.texts.push(match[1])
						collected = collectSelectOptions(match[3])
						scope.options.push(collected.options)
						scope.selected.push(collected.selected)

				###
				# Returning select box options that was rendered according to 'input' parameter
 			###

				collectSelectOptions = (input) ->

					possibleValues = scope.$eval(attrs.possibleValues)
					choices = {}
					extras = {}
					options = []

					# some regular expressions parsing...

					if input.match(/^\d+:date group(.*)$/)
						choices = possibleValues.dates
						extrasMatch = RegExp.$1
					else if input.match(/^\d+:field group:([a-zA-Z0-9_]+)(.*)$/)
						type = RegExp.$1
						if typeof possibleValues.fields[type] != 'undefined'
							choices = possibleValues.fields[type]
							extrasMatch = RegExp.$2
					else if input.match(/^\d+:status group:([a-zA-Z0-9_]+)(.*)$/)
						type = RegExp.$1
						if typeof possibleValues.statuses[type] != 'undefined'
							choices = possibleValues.statuses[type]
							extrasMatch = RegExp.$2
					else if input.match(/^\d+:order group:([a-zA-Z0-9_]+)(.*)$/)
						type = RegExp.$1
						if typeof possibleValues.orders[type] != 'undefined'
							choices = possibleValues.orders[type]
							extrasMatch = RegExp.$2

					# information about default group...

					if extrasMatch
						regex = /,([a-zA-Z0-9_ ]+):([^,]+)/g
						while match = regex.exec(extrasMatch)
							extras[$.trim(match[1])] = $.trim(match[2])

					# constructing selects...

					for own key, value of choices
						options.push({value: key, label: value[0]})

					return {
						options: options
						selected: (if extras.default then extras.default else options[0].value)
					}

				###
 			#
				###

				scope.selectHandler = () ->
					alert 'ok'
		}
	]

	return Reports_Directive_DpReportBuilderSelectBox