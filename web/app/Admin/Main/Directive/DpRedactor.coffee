define ['redactor', 'jquery'], (redactor, $) ->
	###
	# Description
	# -----------
	# textarea editor, moved from agent iface
	#
	###
	Admin_Main_Directive_DpRedactor = [ ('$timeout'), ($timeout) ->
		return {
			restrict: 'A'
			require: 'ngModel'
			link: (scope, element, attrs, ngModel) ->
				api = null

				defaults = {
					minHeight: 100
				}

				updateModel = (val) ->
					$timeout(->
						scope.$apply(->
							ngModel.$setViewValue(val)
						)
					)

				ngModel.$render = ->
					if api then $timeout(-> api.setCode(ngModel.$viewValue || ''))

				$timeout( ->
					element.redactor(defaults)
					api = element.data('redactor')

					origSyncCode = api.syncCode
					api.syncCode = ->
						origSyncCode.call(api)
						updateModel(api.getCode())

					ngModel.$render()
				)
		}
	]

	return Admin_Main_Directive_DpRedactor
