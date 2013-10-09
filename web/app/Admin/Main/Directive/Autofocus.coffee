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
	Admin_Main_Directive_Autofocus = [ '$timeout', ($timeout) ->
		return {
			restrict: 'A',
			link: (scope, element, attrs) ->
				# There is no 'post render' callback with angular
				# to know when the element is actually in the page
				# so lets just focus after some time
				$timeout(->
					element.focus()
				, 50)
				$timeout(->
					element.focus()
				, 150)
				$timeout(->
					element.focus()
				, 200)
		}
	]

	return Admin_Main_Directive_Autofocus