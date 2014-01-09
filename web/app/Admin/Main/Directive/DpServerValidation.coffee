define ->
	###
    # Description
    # -----------
    #
    # This attaches known error response codes from server-side validation on to a model.
    # This is needed so we can mark up the proper parts of the form and show the proper errors
    # messages for this particular model when there's an error.
    #
    # For example, say we have strict requirements for a title field. Angular gives us 'required'
    # support by default but we might want to defer our strict format checking to the server.
    #
    # The server can return an error code but there are no facilities in Angular to connect that
    # error code to the title model. We'd essentially have to do it "manually". Which is why we
    # need to use this directive. We can connect the server error codes to the model and then
    # the rest of error handling process is kept the same (e.g., enabling 'has-error' classes to
    # show error state etc).
    #
    # Example View
    # ------------
    # <input type="text" model="myfield" name="myfield" dp-server-validation="myfield.strict_requirements" />
    ###
	Admin_Main_Directive_DpServerValidation = [->
		return {
			require: 'ngModel',
			restrict: 'A',
			link: (scope, elm, attrs, ngModel) ->
				ngModel.dpServerValidationKeys = attrs.dpServerValidation.split(',')

				if not ngModel.dpServerValidationKeys.length
					return

				# Server-side validation errors always reset
				# when we re-validate on the client (e.g., so they can re-submit)
				ngModel.$parsers.unshift( (viewValue) ->
					for own error_code, is_error of ngModel.$error
						if not is_error then continue

						for code in ngModel.dpServerValidationKeys
							if code.indexOf('.') != -1
								code_safe = code.replace(/^.*\.(.*)$/, '$1')
							else
								code_safe = code
							code_safe = code_safe.replace(/\./g, '_')

							if error_code == code_safe
								code_segs = code.split('.')
								last_seg = code_segs.pop();

								switch last_seg
									when 'required'
										ngModel.$setValidity('required', true)
									else
										ngModel.$setValidity(code_safe, true)

					return viewValue;
				)
		}
	]

	return Admin_Main_Directive_DpServerValidation