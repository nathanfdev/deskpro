define ->
	###
    # Description
    # -----------
    #
    # This is the trigger for an inline help content body to display. When it is clicked,
    # this element will fade away and the body will slide in. When the body is closed again,
    # the body will "minimise into" this switch and the switch will become visible again.
    #
    # The ID of these inline helps should be globally unique because their state is saved.
    #
    # Help State
    # ----------
    #
    # A help state can either be open, closed, or undefined. Undefined states default to being
    # open unless the default-state attribute is used to set it open.
    #
    # Example View
    # ------------
    # <
    ###
	Admin_Main_Directive_DpInhelpSwitch = [ ->
		return {
			restrict: 'A',
			link: (scope, element, attrs) ->
				return
		}
	]

	return Admin_Main_Directive_DpInhelpSwitch