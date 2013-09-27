define ->
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
							code_safe = code.replace(/\./g, '_')
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