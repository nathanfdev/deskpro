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
	Reports_Directive_DpReportBuilderSelectBox = ['$state', ($state) ->
		return {
			restrict: 'AE',
			replace: true,
			template: """
				<a ng-href="{{report_link}}">
					<span ng-repeat="text in texts" style="margin-left: 5px">
						<span style="vertical-align:middle;" ng-bind-html="text"></span>
						<select ng-if="options[$index]" ng-model="selected[$index]" ui-select2="{dropdownAutoWidth:true}" style="min-width:70px;" ng-change="changeLinkParams()">
							<option ng-repeat="option in options[$index]" ng-value="option.value" ng-selected="selected[$parent.$index] == option.value">
								{{ option.label }}
							</option>
						</select>
					</span>
				</a>
			"""
			link: (scope, element, attrs) ->

				###
				# Below variables will look like following
				#
				# scope.texts = ['Number of tickets created','grouped by',' & ']
				# scope.options = [[{value: 'yesterday', label: 'Yesterday'}, {value: 'today', label: 'Today'}, {value: '123', label: '123'}, {value: '456', label: '456'}]
				# 																	[{value: 'department', label: 'Department'}, {value: 'agent', label: 'Agent'}]
				# 																	[{value: 'department', label: 'Department'}, {value: 'agent', label: 'Agent'}]
				# ]
				# scope.selected = ['today', 'agent', 'department']
				###
				scope.texts = []
				scope.options = []
				scope.selected = []
				scope.defaultLinkParams = ''
				scope.type = attrs.type || 'builtIn'

				scope.$watch(attrs.possibleValues, (newVal) =>

					if typeof newVal == 'undefined' then return
					valueToDecorate = scope.$eval(attrs.valueToDecorate)
					if !valueToDecorate then return

					scope.reportId = scope.$eval(attrs.reportId)
					buildDirectiveVariables(valueToDecorate)
					scope.defaultLinkParams = scope.selected.join(',')
					updateLink()
				)


				###
				# This function builds directive by constructing it on 'the fly' using DOM operations
				# The reason for doing so - problems with inner directives that were compiled with $compile() functionality
				###
				buildDirectiveVariables = (value) ->

					lastPiece = value
					regex = /(.*?)(<(\d+:.+?)>)/g

					while match = regex.exec(value)
						scope.texts.push(match[1])
						collected = collectSelectOptions(match[3])
						scope.options.push(collected.options)
						scope.selected.push(collected.selected)

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

					if !options.length
						options.push {value: 0, label: 'invalid value'}

					return {
						options: options
						selected: (if extras.default then extras.default else options[0]?.value)
					}

				updateLink = ->
					linkParams = scope.selected.join(',')
					scope.report_link = $state.href('builder.edit', {id: scope.reportId, params:linkParams, type: scope.type})

				###
				# Going to correponding route after changing selected options inside select box
				###
				scope.changeLinkParams = () ->
					updateLink()
					linkParams = scope.selected.join(',')
					$state.go('builder.edit', {id: scope.reportId, params:linkParams, type: scope.type})
		}
	]

	return Reports_Directive_DpReportBuilderSelectBox