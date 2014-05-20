define ->
	###
    # Description
    # -----------
    #
    # This should be used to submit any forms within the DeskPRO interface. It has special
    # logic to add the "attempted" state to models which is used to show correct error state
    # in the UI.
    #
    # Example View
    # ------------
    # <button dp-submit-form>Save</button>
    ###
	DeskPRO_Directive_DpSubmitForm = [ ->
		return {
			restrict: 'A',
			link: (scope, element, attrs) ->
				element.on('click', (ev) ->
					ev.preventDefault()
					ev.stopPropagation()

					form = element.closest('form')
					form.on('submit', (ev) ->
						ev.preventDefault()
					)
					formName = form.attr('name')
					form.submit()
					scope[formName].$attempted = true

					for own k, v of scope[formName]
						if k.substring(0, 1) == '$' then continue
						if not v.$name or not v.$viewChangeListeners then continue

						v.$attempted = true

					scope.$apply()
				)
		}
	]

	return DeskPRO_Directive_DpSubmitForm