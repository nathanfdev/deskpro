define ['moment', 'DeskPRO/Util/Util'], (moment, Util) ->
  class FieldFormMapper
    ###
    # Get a form model for an existing field
    #
    # @param {Object} fieldModel The field model (eg as returned from the API)
    # @return {Object}
    ###
    getFormFromModel: (fieldModel) ->

      fieldModel = fieldModel || {}
      fieldModel.options = fieldModel.options || {}

      # Default structure
      form = {
        title: '',
        alias: '',
        description: '',
        is_enabled: true,
        text: {
          user_validation:          '0',
          min_length:               '1',
          max_length:               '',
          regex:                    '',
          regex_required:           false,
          agent_validation:         '0',
          agent_min_length:         '1',
          agent_max_length:         '',
          agent_regex:              '',
          agent_regex_required:     false,
          agent_validation_resolve: false,
          clickable_links:          false
        },
        toggle: {
          label_text: '',
          unchecked_text: '',
          user_validation:           '0',
          agent_validation:          '0',
          agent_validation_resolve:  false
        },
        choice: {
          field_type:              'select',
          options:                  [],
          user_validation:          '0',
          agent_validation:         '0',
          agent_validation_resolve: false
        },
        date: {
          default_mode:              if fieldModel?.default_value then 'date' else '0'
          default_value:             if fieldModel?.default_value then moment.utc(fieldModel.default_value, 'YYYY-MM-DD HH:mm:ss').toDate() else new Date()
          valid_weekdays:            [true, true, true, true, true, true, true]
          calendar:                  'gregorian'
          valid_dates_mode:          '0'
          valid_date_date1:          ''
          valid_date_date2:          ''
          valid_date_range1:         ''
          valid_date_range2:         ''
          user_validation:           '0'
          agent_validation:          '0'
          agent_validation_resolve:  false
        },
        datetime: {
          default_mode:              if fieldModel?.default_value then 'date' else '0'
          default_value:             if fieldModel?.default_value then moment.utc(fieldModel.default_value, 'YYYY-MM-DD HH:mm:ss').toDate() else new Date()
          valid_weekdays:            [true, true, true, true, true, true, true]
          valid_dates_mode:          '0'
          valid_date_date1:          ''
          valid_date_date2:          ''
          valid_date_range1:         ''
          valid_date_range2:         ''
          user_validation:           '0'
          agent_validation:          '0'
          agent_validation_resolve:  false
        },
        display: {
          html: ''
        },
        hidden: {
          cookie_name:   '',
          param_name:    '',
          default_value: ''
        },
        data: {
          usersource_id: '0',
          field_name: ''
        }
        datajson: {
          usersource_id: '0',
          field_name: ''
        }
        datalist: {
          usersource_id: '0',
          field_name: ''
        },
        url: {
          allow_file:               false
          user_validation:          '0'
          agent_validation:         '0'
          agent_validation_resolve: false
        },
        currency: {
          currency_id:              null
          user_validation:          '0'
          agent_validation:         '0'
          agent_validation_resolve: false
        },
        file: {
          multiple:                    false
          user_extensions_limit_mode:  'any'
          user_must_extensions:        null
          user_not_extensions:         null
          user_validation:             false
          user_max_file_size:          0
          agent_extensions_limit_mode: 'any'
          agent_must_extensions:       null
          agent_not_extensions:        null
          agent_validation:            false
          agent_validation_resolve:    false
          agent_max_file_size:         0
        }
      }

      if fieldModel
        form.title = fieldModel.title
        form.alias = fieldModel.alias
        form.description = fieldModel.description

        if fieldModel.type_name == 'textarea'
          formTypeOpts = form['text']
        else
          formTypeOpts = form[fieldModel.type_name]

        if fieldModel.is_agent_field
          form.is_agent_field = true
        if fieldModel.is_enabled || not fieldModel.id
          form.is_enabled = true
        else
          form.is_enabled = false

        switch fieldModel.type_name
          when "text", "textarea"
            if fieldModel.options.required || fieldModel.options.min_length || fieldModel.options.max_length || fieldModel.options.regex
              if fieldModel.options.min_length
                formTypeOpts.user_validation = 'required'
                formTypeOpts.min_length = fieldModel.options.min_length
              if fieldModel.options.max_length
                formTypeOpts.user_validation = 'required'
                formTypeOpts.max_length = fieldModel.options.max_length
              if fieldModel.options.regex
                formTypeOpts.user_validation = 'regex'
                formTypeOpts.regex = fieldModel.options.regex
                formTypeOpts.regex_required = !!fieldModel.options.regex_required

            if fieldModel.options.agent_required || fieldModel.options.agent_min_length || fieldModel.options.agent_max_length || fieldModel.options.agent_regex
              if fieldModel.options.agent_min_length
                formTypeOpts.agent_validation = 'required'
                formTypeOpts.agent_min_length = fieldModel.options.agent_min_length
              if fieldModel.options.agent_max_length
                formTypeOpts.agent_validation = 'required'
                formTypeOpts.agent_max_length = fieldModel.options.agent_max_length
              if fieldModel.options.agent_regex
                formTypeOpts.agent_validation = 'regex'
                formTypeOpts.agent_regex = fieldModel.options.agent_regex
                formTypeOpts.agent_regex_required = !!fieldModel.options.agent_regex_required

            if fieldModel.default_value
              formTypeOpts.default_value = fieldModel.default_value
            if fieldModel.options.clickable_links
              formTypeOpts.clickable_links = !!fieldModel.options.clickable_links

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
              formTypeOpts.user_validation = 'required'
            if fieldModel.options.agent_required || fieldModel.options.agent_min_length
              formTypeOpts.agent_validation = 'required'
              if fieldModel.options.agent_validation_resolve
                formTypeOpts.agent_validation_resolve = true

            if fieldModel.choices and fieldModel.choices.length
              formTypeOpts.options = fieldModel.choices
            if formTypeOpts.field_type == 'radio'
              formTypeOpts.none_choice = !!fieldModel.options.none_choice
              if fieldModel.options.none_choice
                formTypeOpts.none_choice_title = fieldModel.options.none_choice_title

            formTypeOpts.default_value = fieldModel.default_value

          when "toggle"
            formTypeOpts.label_text = fieldModel.options.label_text || ''
            formTypeOpts.unchecked_text = fieldModel.options.unchecked_text || ''

            if fieldModel.options.validation_type
              formTypeOpts.user_validation = 'required'
            if fieldModel.options.agent_validation_type
              formTypeOpts.agent_validation = 'required'
              if fieldModel.options.agent_validation_resolve
                formTypeOpts.agent_validation_resolve = true

            if fieldModel.default_value
              formTypeOpts.default_value = true

          when "date", "datetime"
            if fieldModel.options?.date_valid_dow?
              formTypeOpts.valid_weekdays = [false, false, false, false, false, false, false]
              for own day of fieldModel.options.date_valid_dow
                formTypeOpts.valid_weekdays[day] = true

            if fieldModel.options?.calendar
              formTypeOpts.calendar = fieldModel.options.calendar

            if fieldModel.options.date_valid_type?
              if fieldModel.options.date_valid_type == "date"
                formTypeOpts.valid_dates_mode = 'date'
                formTypeOpts.date_valid_date1 = moment(fieldModel.options.date_valid_date1, 'YYYY-MM-DD').toDate() if fieldModel.options.date_valid_date1?
                formTypeOpts.date_valid_date2 = moment(fieldModel.options.date_valid_date2, 'YYYY-MM-DD').toDate() if fieldModel.options.date_valid_date2?
              if fieldModel.options.date_valid_type == "range"
                formTypeOpts.date_valid_range1 = fieldModel.options.date_valid_range1 if fieldModel.options.date_valid_range1?
                formTypeOpts.date_valid_range2 = fieldModel.options.date_valid_range2 if fieldModel.options.date_valid_range2?

            if fieldModel.options.required
              formTypeOpts.user_validation = 'required'
            if fieldModel.options.agent_required
              formTypeOpts.agent_validation = 'required'
              if fieldModel.options.agent_validation_resolve
                formTypeOpts.agent_validation_resolve = true

          when "display"
            formTypeOpts.html = fieldModel.options.html

          when "hidden"
            formTypeOpts.cookie_name   = fieldModel.options.cookie_name   || ''
            formTypeOpts.param_name    = fieldModel.options.param_name    || ''
            formTypeOpts.default_value = fieldModel.options.default_value || ''

            if fieldModel.default_value
              formTypeOpts.default_value = fieldModel.default_value

          when "data", "datajson", "datalist"
            formTypeOpts.usersource_id = (parseInt(fieldModel.options.usersource_id || '0') || 0) + ""
            formTypeOpts.field_name    = fieldModel.options.field_name || ''

          when "url"
            formTypeOpts.allow_file = !!fieldModel.options.allow_file

            if fieldModel.options.required
              formTypeOpts.user_validation = 'required'
            if fieldModel.options.agent_required
              formTypeOpts.agent_validation = 'required'
              if fieldModel.options.agent_validation_resolve
                formTypeOpts.agent_validation_resolve = true

          when "currency"
            formTypeOpts.currency_id = fieldModel.options.currency_id

            if fieldModel.options.required
              formTypeOpts.user_validation = 'required'
            if fieldModel.options.agent_required
              formTypeOpts.agent_validation = 'required'
              if fieldModel.options.agent_validation_resolve
                formTypeOpts.agent_validation_resolve = true

          when "file"
            formTypeOpts.multiple                    = !!fieldModel.options.multiple
            formTypeOpts.user_validation             = !!fieldModel.options.required
            formTypeOpts.user_extensions_limit_mode  = fieldModel.options.user_extensions_limit_mode
            formTypeOpts.user_must_extensions        = fieldModel.options.user_must_extensions
            formTypeOpts.user_not_extensions         = fieldModel.options.user_not_extensions
            formTypeOpts.user_max_file_size          = fieldModel.options.user_max_file_size
            formTypeOpts.agent_extensions_limit_mode = fieldModel.options.agent_extensions_limit_mode
            formTypeOpts.agent_must_extensions       = fieldModel.options.agent_must_extensions
            formTypeOpts.agent_not_extensions        = fieldModel.options.agent_not_extensions
            formTypeOpts.agent_max_file_size         = fieldModel.options.agent_max_file_size

            if fieldModel.options.agent_required
              formTypeOpts.agent_validation = true
              if fieldModel.options.agent_validation_resolve
                formTypeOpts.agent_validation_resolve = true

      if fieldModel.options.agent_validation_resolve
        formTypeOpts.agent_validation_resolve = true

      console.log('belzebut ', form)

      return form


    ###
    # Use a form model to construct a payload we can deliver to the API to save
    # a field.
    #
    # @param {String} fieldType  The field type
    # @param {Object} formModel  The form model
    # @return {Object}
    ###
    getPostDataFromForm: (fieldType, formModel) ->
      postData = {
        title: formModel.title,
        alias: formModel.alias,
        description: formModel.description,
        is_agent_field: formModel.is_agent_field,
        is_enabled: formModel.is_enabled
      }

      if fieldType == 'textarea'
        formTypeOpts = formModel['text']
      else
        formTypeOpts = formModel[fieldType]

      switch fieldType
        when "text", "textarea"
          if fieldType == 'text'
            postData.handler_class = 'Application\\DeskPRO\\CustomFields\\Handler\\Text'
          else
            postData.handler_class = 'Application\\DeskPRO\\CustomFields\\Handler\\Textarea'

          postData.default_value = formTypeOpts.default_value
          postData.clickable_links = formTypeOpts.clickable_links

          if formTypeOpts.user_validation == 'required'
            postData.validation_type = 'required'
            postData.min_length = formTypeOpts.min_length
            postData.max_length = formTypeOpts.max_length
          else if formTypeOpts.user_validation == 'regex'
            postData.validation_type = 'regex'
            postData.regex = formTypeOpts.regex
            postData.regex_required = formTypeOpts.regex_required

          if formTypeOpts.agent_validation == 'required'
            postData.agent_validation_type = 'required'
            postData.agent_min_length = formTypeOpts.agent_min_length
            postData.agent_max_length = formTypeOpts.agent_max_length
          else if formTypeOpts.agent_validation == 'regex'
            postData.agent_validation_type = 'regex'
            postData.agent_regex = formTypeOpts.agent_regex
            postData.agent_regex_required = formTypeOpts.agent_regex_required

        when "choice"
          postData.handler_class = 'Application\\DeskPRO\\CustomFields\\Handler\\Choice'
          postData.field_type = formTypeOpts.field_type
          postData.choices_structure = formTypeOpts.options
          postData.default_value = formTypeOpts.default_value

          if formTypeOpts.user_validation == 'required'
            postData.validation_type = 'required'
            postData.min_length = 1
          if formTypeOpts.agent_validation == 'required'
            postData.agent_validation_type = 'required'
            postData.agent_min_length = 1

          if formTypeOpts.field_type == 'radio'
            postData.none_choice = formTypeOpts.none_choice
            if formTypeOpts.none_choice
              postData.none_choice_title = formTypeOpts.none_choice_title

        when "toggle"
          postData.handler_class = 'Application\\DeskPRO\\CustomFields\\Handler\\Toggle'
          postData.default_value = formTypeOpts.default_value
          postData.label_text = formTypeOpts.label_text
          postData.unchecked_text = formTypeOpts.unchecked_text

          if formTypeOpts.user_validation == 'required'
            postData.validation_type = 'required'
          if formTypeOpts.agent_validation == 'required'
            postData.agent_validation_type = 'required'

        when "date", "datetime"
          format = 'YYYY-MM-DD'
          if 'date' == fieldType
            postData.handler_class = 'Application\\DeskPRO\\CustomFields\\Handler\\Date'
          else
            postData.handler_class = 'Application\\DeskPRO\\CustomFields\\Handler\\DateTime'
            format += ' HH:mm'

          if formTypeOpts.default_mode == 'date'
            postData.default_value = moment(formTypeOpts.default_value).utc().format(format)
          else
            postData.default_value = null

          postData.default_mode = formTypeOpts.default_mode

          postData.date_valid_dow = []
          for x, day in formTypeOpts.valid_weekdays
            if x
              postData.date_valid_dow.push(day)

          if formTypeOpts.user_validation == 'required'
            postData.required = true
          if formTypeOpts.agent_validation == 'required'
            postData.agent_required = true

          postData.date_valid_type = formTypeOpts.valid_dates_mode
          postData.calendar = formTypeOpts.calendar

          if formTypeOpts.valid_dates_mode == 'date'
            postData.date_valid_date1 = ''
            postData.date_valid_date2 = ''

            if not Util.isBlank(formTypeOpts.date_valid_date1)
              postData.date_valid_date1 = moment(formTypeOpts.date_valid_date1).format('YYYY-MM-DD')
            if not Util.isBlank(formTypeOpts.date_valid_date2)
              postData.date_valid_date2 = moment(formTypeOpts.date_valid_date2).format('YYYY-MM-DD')
          else if formTypeOpts.valid_dates_mode == 'range'
            postData.date_valid_range1 = formTypeOpts.date_valid_range1
            postData.date_valid_range2 = formTypeOpts.date_valid_range2

        when "display"
          postData.handler_class = 'Application\\DeskPRO\\CustomFields\\Handler\\Display'
          postData.html = formTypeOpts.html

        when "hidden"
          postData.handler_class = 'Application\\DeskPRO\\CustomFields\\Handler\\Hidden'
          postData.cookie_name = formTypeOpts.cookie_name
          postData.param_name = formTypeOpts.param_name
          postData.default_value = formTypeOpts.default_value

        when "data"
          postData.handler_class = 'Application\\DeskPRO\\CustomFields\\Handler\\Data'
          postData.usersource_id = parseInt(formTypeOpts.usersource_id) || 0
          postData.field_name    = formTypeOpts.field_name

        when "url"
          postData.handler_class = 'Application\\DeskPRO\\CustomFields\\Handler\\Url'
          postData.allow_file    = formTypeOpts.allow_file

          if formTypeOpts.user_validation == 'required'
            postData.required = true
          if formTypeOpts.agent_validation == 'required'
            postData.agent_required = true

        when "currency"
          postData.handler_class = 'Application\\DeskPRO\\CustomFields\\Handler\\Currency'
          postData.currency_id = formTypeOpts.currency_id

          if formTypeOpts.user_validation == 'required'
            postData.required = true
          if formTypeOpts.agent_validation == 'required'
            postData.agent_required = true

        when "file"
          postData.handler_class               = 'Application\\DeskPRO\\CustomFields\\Handler\\File'
          postData.multiple                    = formTypeOpts.multiple
          postData.user_extensions_limit_mode  = formTypeOpts.user_extensions_limit_mode
          postData.user_must_extensions        = formTypeOpts.user_must_extensions
          postData.user_not_extensions         = formTypeOpts.user_not_extensions
          postData.user_max_file_size          = formTypeOpts.user_max_file_size
          postData.agent_extensions_limit_mode = formTypeOpts.agent_extensions_limit_mode
          postData.agent_must_extensions       = formTypeOpts.agent_must_extensions
          postData.agent_not_extensions        = formTypeOpts.agent_not_extensions
          postData.agent_max_file_size         = formTypeOpts.agent_max_file_size

          if formTypeOpts.user_validation
            postData.required = true
          if formTypeOpts.agent_validation
            postData.agent_required = true

      if formTypeOpts.agent_validation_resolve
        postData.agent_validation_resolve = true

      return postData

    ###
    # Applies basic settings from form onto the real field model
      # so the list is showing correct data.
    ###
    applyFormToModel: (fieldModel, formModel) ->
      fieldModel.title = formModel.title
      fieldModel.is_enabled = formModel.is_enabled
      return fieldModel
