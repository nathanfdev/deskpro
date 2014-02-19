define ->
	###
    # Description
    # -----------
    #
    # This adds a has-error class to the element when the specified model becomes
    # invalid.
    #
    # This only applies the has-error class if:
    # * The model is invalid AND
    # * The user has changed the value, or the form the model is a part of has been submitted
    #
    # E.g., this differs from just using ng-class in that it's an easy way to only show an error
    # when the error state actually matters. Like if you just load up a form and the default
    # value of a required field is blank, we shouldn't be showing a red 'invalid' error next to it.
    #
    # Example
    # -------
    # <div dp-error-class="myform.myfield">
    #    <input type="text" ng-model="myfield" name="myfield" required />
    # </div>
	###
	DeskPRO_Directive_DpErrorClass = [ ->
		return {
			restrict: 'A',
			link: (scope, element, attrs) ->
				updateClass = ->
					formProp = scope.$eval(attrs.dpErrorClass)
					if not formProp then return

					set_errorclass = false
					if formProp.$invalid and (formProp.$dirty or formProp.$attempted)
						set_errorclass = true

					if set_errorclass
						element.addClass('has-error')
					else
						element.removeClass('has-error')

				watch_vars = [
					attrs.dpErrorClass+'.$invalid',
					attrs.dpErrorClass+'.$dirty',
					attrs.dpErrorClass+'.$attempted'
				]
				scope.$watch('dpErrorClass', ->
					for varname in watch_vars
						scope.$watch(varname, ->
							updateClass()
						, true)
				)
		}
	]

	return DeskPRO_Directive_DpErrorClass