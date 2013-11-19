define ->
	class FieldFormMapper
		getFormFromModel: (fieldModel) ->
			console.log(fieldModel)

			# Default structure
			form = {
				title: '',
				description: '',
				text: {
					user_validation:          '0',
					user_validation_minlen:   '1',
					user_validation_maxlen:   '',
					user_validation_regex:    '',
					agent_validation:         '0',
					agent_validation_minlen:   '1',
					agent_validation_maxlen:   '',
					agent_validation_regex:   '',
					agent_validation_resolve: false,
				},
				textarea: {
					user_validation:          '0',
					user_validation_minlen:   '1',
					user_validation_maxlen:   '',
					user_validation_regex:    '',
					agent_validation:         '0',
					agent_validation_minlen:   '1',
					agent_validation_maxlen:   '',
					agent_validation_regex:   '',
					agent_validation_resolve: false,
				},
				choice: {
					field_type:              'select',
					options:                  [],
					user_validation:          '0',
					agent_validation:         '0',
					agent_validation_resolve: false
				},
				date: {
					default_mode:              '0',
					default_value:             '',
					valid_weekdays:            [true, true, true, true, true, true, true],
					valid_dates_mode:          '0',
					valid_date_range_start:    '',
					valid_date_range_end:      '',
					valid_date_relrange_start: '',
					valid_date_relrange_end:   '',
					user_validation:           '0',
					agent_validation:          '0',
					agent_validation_resolve:  false
				},
				display: {
					html: ''
				},
				hidden: {
					cookie_name:   '',
					param_name:    '',
					default_value: ''
				}
			}

			if fieldModel
				form.title = fieldModel.title
				form.description = fieldModel.description

				formTypeOpts = form[fieldModel.type_name]

				if fieldModel.is_agent_field
					formTypeOpts.is_agent_field = true

				switch fieldModel.type_name
					when "choice"
						if fieldModel.options.expanded
							if fieldModel.options.multiple
								formTypeOpts.field_type = 'checkbox'
							else
								formTypeOpts.field_type = 'radio'
						else
							if fieldModel.options.multiple
								formTypeOpts.field_type = 'multi_select'
							else
								formTypeOpts.field_type = 'select'

						if fieldModel.options.required || fieldModel.options.min_length
							formTypeOpts.user_validation = true
						if fieldModel.options.agent_required || fieldModel.options.agent_min_length
							formTypeOpts.agent_validation = true
						if fieldModel.options.agent_validation_resolve
							formTypeOpts.agent_validation_resolve = true

						if fieldModel.choices and fieldModel.choices.length
							formTypeOpts.options = fieldModel.choices

					when "text", "textarea"
						if fieldModel.options.required || fieldModel.options.min_length || fieldModel.options.max_length || fieldModel.regex
							formTypeOpts.user_validation = true

							if fieldModel.options.min_length
								formTypeOpts.user_validation_minlength = fieldModel.options.min_length
							if fieldModel.options.max_length
								formTypeOpts.user_validation_maxlength = fieldModel.options.max_length
							if fieldModel.options.regex
								formTypeOpts.user_validation_regex = fieldModel.options.regex

						if fieldModel.options.agent_required || fieldModel.options.agent_min_length || fieldModel.options.agent_max_length || fieldModel.agent_regex
							formTypeOpts.user_validation = true

							if fieldModel.options.agent_min_length
								formTypeOpts.agent_validation_minlength = fieldModel.options.agent_min_length
							if fieldModel.options.agent_max_length
								formTypeOpts.agent_validation_maxlength = fieldModel.options.agent_max_length
							if fieldModel.options.agent_regex
								formTypeOpts.agent_validation_regex = fieldModel.options.agent_regex

					when "display"
						formTypeOpts.html = fieldModel.options.html

					when "hidden"
						formTypeOpts.cookie_name   = fieldModel.options.cookie_name   || ''
						formTypeOpts.param_name    = fieldModel.options.param_name    || ''
						formTypeOpts.default_value = fieldModel.options.default_value || ''

			return form