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
				scope.$on('dp_loadingstate_change', (evt, id, is_loading) ->
					if not done and id == 'dp_section_page' and not is_loading
						$timeout(->
							element.focus()
						, 150)
				)
		}
	]

	return Admin_Main_Directive_Autofocus