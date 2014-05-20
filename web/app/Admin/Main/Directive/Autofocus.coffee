define ->
	###
    # Description
    # -----------
    #
    # This sets the initial focus once a form is loaded.
    #
    # Example
    # -------
    # <input autofocus>
	###
	Admin_Main_Directive_Autofocus = [ '$rootScope', '$timeout', ($rootScope, $timeout) ->
		return {
			restrict: 'A',
			link: (scope, element, attrs) ->
				element.focus()

				done = false
				$timeout(->
					element.focus()
				, 150)
				$timeout(->
					element.focus()
				, 250)
		}
	]

	return Admin_Main_Directive_Autofocus