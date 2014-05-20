define ->
	###
   # Description
   # -----------
 		#
 		#	Example View
 		#	------------
 		#	<dp-report-builder-title>
 		#	</dp-report-builder-title>
 		#
 		#	Parameters
 		#	------------
   #
	###
	Reports_Directive_DpReportTitle = ['$state', ($state) ->
		return {
			restrict: 'AE',
			replace: true,
			scope: {
				possibleValues: '='
				valueToDecorate: '='
				reportId: '='
			},
			template: """
				<h3 style="font-weight:bold;">
					<span ng-repeat="text in texts" class="inline-select2">
						<span style="vertical-align:middle;" ng-bind-html="text"></span>
						<select ng-if="options[$index]" ng-model="selected[$index]" ui-select2="{dropdownAutoWidth:true}" style="min-width:100px;" ng-change="changeLinkParams()">
							<option ng-repeat="option in options[$index]" ng-value="option.value" ng-selected="selected[$parent.$index] == option.value">
								{{ option.label }}
							</option>
						</select>
					</span>
				</h3>
			"""
			link: (scope, element, attrs) ->

				###

 			Below variables will look like following

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
				scope.defaultLinkParams = ''

				scope.$watch('possibleValues', (newVal) =>

					if typeof newVal == 'undefined' then return
					valueToDecorate = scope.valueToDecorate
					if !valueToDecorate then return

					buildDirectiveVariables(valueToDecorate)
					scope.defaultLinkParams = scope.selected.join(',')
				)


				###
				# This function builds directive by constructing it on 'the fly' using DOM operations
 			# The reason for doing so - problems with inner directives that were compiled with $compile() functionality
				###

				buildDirectiveVariables = (value) ->

					key = 0
					params = $state.params.params.split(',')

					lastPiece = value
					regex = /([\w\s\&,]*)(<(\d+:.+?)>)/g

					while match = regex.exec(value)
						scope.texts.push(match[1])
						collected = collectSelectOptions(match[3])
						scope.options.push(collected.options)
						scope.selected.push(params[key])
						key++

						# case when text that continues after last select box
						lastPiece = lastPiece.replace(match[1], '').replace(match[2], '')

					# finding icon for case we have it
					lastPiece = lastPiece.replace('[', '').replace(']', '')
					lastPiece = lastPiece.replace(/<chart:([a-z0-9_-]+)>/gi, '<span class="report-chart-icon report-chart-icon-$1"></span>')

					scope.texts.push(lastPiece)

				###
				# Returning select box options that was rendered according to 'input' parameter
 			###

				collectSelectOptions = (input) ->

					possibleValues = scope.possibleValues
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

					# constructing selects...

					for own key, value of choices
						options.push({value: key, label: value[0]})

					return {
						options: options
					}

				###
 			# Going to correponding route after changing selected options inside select box
				###

				scope.changeLinkParams = () ->
					linkParams = scope.selected.join(',')
					$state.go('builder.edit', {id: scope.reportId, params:linkParams})
		}
	]

	return Reports_Directive_DpReportTitle