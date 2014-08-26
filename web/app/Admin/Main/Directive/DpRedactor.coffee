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
			defaults =
				minHeight: 100
				keyupCallback: (html) ->
					ngModel.$setViewValue html.$el.val()
					ngModel.$render()

			$timeout( ->
				element.redactor defaults
			)
		}
	]

	return Admin_Main_Directive_DpRedactor
