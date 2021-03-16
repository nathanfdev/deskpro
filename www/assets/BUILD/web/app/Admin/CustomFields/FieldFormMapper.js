define(['moment', 'DeskPRO/Util/Util'], function(moment, Util) {
  class FieldFormMapper {
    /*
     * Get a form model for an existing field
     *
     * @param {Object} fieldModel The field model (eg as returned from the API)
     * @return {Object}
     */
    getFormFromModel(fieldModel) {
      let formTypeOpts;
      fieldModel = fieldModel || {};
      fieldModel.options = fieldModel.options || {};

      // Default structure
      const form = {
        title:       '',
        alias:       '',
        description: '',
        is_enabled:  true,
        text:        {
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
          clickable_links:          false,
          code:                     this.getDefaultCode(),
        },
        toggle: {
          label_text:               '',
          unchecked_text:           '',
          user_validation:          '0',
          agent_validation:         '0',
          agent_validation_resolve: false
        },
        choice: {
          field_type:               'select',
          options:                  [],
          user_validation:          '0',
          agent_validation:         '0',
          agent_validation_resolve: false
        },
        eula: {
          field_type:               'select',
          options:                  [],
          user_validation:          '0',
          agent_validation:         '0',
          agent_validation_resolve: false
        },
        date: {
          default_mode:             (fieldModel != null ? fieldModel.default_value : undefined) ? 'date' : '0',
          default_value:            (fieldModel != null ? fieldModel.default_value : undefined) ? moment.utc(fieldModel.default_value, 'YYYY-MM-DD HH:mm:ss').toDate() : new Date(),
          valid_weekdays:           [true, true, true, true, true, true, true],
          calendar:                 'gregorian',
          valid_dates_mode:         '0',
          valid_date_date1:         '',
          valid_date_date2:         '',
          valid_date_range1:        '',
          valid_date_range2:        '',
          user_validation:          '0',
          agent_validation:         '0',
          agent_validation_resolve: false,
          ignore_timezone:          false,
        },
        datetime: {
          default_mode:             (fieldModel != null ? fieldModel.default_value : undefined) ? 'date' : '0',
          default_value:            (fieldModel != null ? fieldModel.default_value : undefined) ? moment.utc(fieldModel.default_value, 'YYYY-MM-DD HH:mm:ss').toDate() : new Date(),
          valid_weekdays:           [true, true, true, true, true, true, true],
          valid_dates_mode:         '0',
          valid_date_date1:         '',
          valid_date_date2:         '',
          valid_date_range1:        '',
          valid_date_range2:        '',
          user_validation:          '0',
          agent_validation:         '0',
          agent_validation_resolve: false,
          ignore_timezone:          false,
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
          field_name:    ''
        },
        datajson: {
          usersource_id: '0',
          field_name:    ''
        },
        datalist: {
          usersource_id: '0',
          field_name:    ''
        },
        url: {
          allow_file:               false,
          user_validation:          '0',
          agent_validation:         '0',
          agent_validation_resolve: false
        },
        currency: {
          currency_id:              null,
          user_validation:          '0',
          agent_validation:         '0',
          agent_validation_resolve: false
        },
        file: {
          multiple:                    false,
          user_extensions_limit_mode:  'any',
          user_must_extensions:        null,
          user_not_extensions:         null,
          user_validation:             false,
          user_max_file_size:          0,
          agent_extensions_limit_mode: 'any',
          agent_must_extensions:       null,
          agent_not_extensions:        null,
          agent_validation:            false,
          agent_validation_resolve:    false,
          agent_max_file_size:         0
        }
      };

      if (fieldModel) {
        form.title = fieldModel.title;
        form.alias = fieldModel.alias;
        form.description = fieldModel.description;

        if (fieldModel.type_name === 'textarea' || fieldModel.type_name === 'javascript') {
          formTypeOpts = form.text;
        } else {
          formTypeOpts = form[fieldModel.type_name];
        }

        if (fieldModel.is_agent_field) {
          form.is_agent_field = true;
        }
        if (fieldModel.is_enabled || !fieldModel.id) {
          form.is_enabled = true;
        } else {
          form.is_enabled = false;
        }

        switch (fieldModel.type_name) {
          case 'text':
          case 'textarea':
          case 'javascript':
            if (fieldModel.options.required || fieldModel.options.min_length || fieldModel.options.max_length || fieldModel.options.regex) {
              if (fieldModel.options.min_length) {
                formTypeOpts.user_validation = 'required';
                formTypeOpts.min_length = fieldModel.options.min_length;
              }
              if (fieldModel.options.max_length) {
                formTypeOpts.user_validation = 'required';
                formTypeOpts.max_length = fieldModel.options.max_length;
              }
              if (fieldModel.options.regex) {
                formTypeOpts.user_validation = 'regex';
                formTypeOpts.regex = fieldModel.options.regex;
                formTypeOpts.regex_required = !!fieldModel.options.regex_required;
              }
            }

            if (fieldModel.options.agent_required || fieldModel.options.agent_min_length || fieldModel.options.agent_max_length || fieldModel.options.agent_regex) {
              if (fieldModel.options.agent_min_length) {
                formTypeOpts.agent_validation = 'required';
                formTypeOpts.agent_min_length = fieldModel.options.agent_min_length;
              }
              if (fieldModel.options.agent_max_length) {
                formTypeOpts.agent_validation = 'required';
                formTypeOpts.agent_max_length = fieldModel.options.agent_max_length;
              }
              if (fieldModel.options.agent_regex) {
                formTypeOpts.agent_validation = 'regex';
                formTypeOpts.agent_regex = fieldModel.options.agent_regex;
                formTypeOpts.agent_regex_required = !!fieldModel.options.agent_regex_required;
              }
            }

            if (fieldModel.default_value) {
              formTypeOpts.default_value = fieldModel.default_value;
            }
            if (fieldModel.options.clickable_links) {
              formTypeOpts.clickable_links = !!fieldModel.options.clickable_links;
            }
            if (fieldModel.options.code) {
              formTypeOpts.code = fieldModel.options.code;
            }
            break;

          case 'choice':
            if (fieldModel.options.expanded) {
              if (fieldModel.options.multiple) {
                formTypeOpts.field_type = 'checkbox';
              } else {
                formTypeOpts.field_type = 'radio';
              }
            } else if (fieldModel.options.multiple) {
              formTypeOpts.field_type = 'multi_select';
            } else {
              formTypeOpts.field_type = 'select';
            }

            if (fieldModel.options.required || fieldModel.options.min_length) {
              formTypeOpts.user_validation = 'required';
            }
            if (fieldModel.options.agent_required || fieldModel.options.agent_min_length) {
              formTypeOpts.agent_validation = 'required';
              if (fieldModel.options.agent_validation_resolve) {
                formTypeOpts.agent_validation_resolve = true;
              }
            }

            if (fieldModel.choices && fieldModel.choices.length) {
              formTypeOpts.options = fieldModel.choices;
            }
            if (formTypeOpts.field_type === 'radio') {
              formTypeOpts.none_choice = !!fieldModel.options.none_choice;
              if (fieldModel.options.none_choice) {
                formTypeOpts.none_choice_title = fieldModel.options.none_choice_title;
              }
            }

            formTypeOpts.default_value = fieldModel.default_value;
            break;

          case 'eula':
            formTypeOpts.field_type = 'select';

            if (fieldModel.choices && fieldModel.choices.length) {
              formTypeOpts.options = fieldModel.choices;
            }
            break;

          case 'toggle':
            formTypeOpts.label_text = fieldModel.options.label_text || '';
            formTypeOpts.unchecked_text = fieldModel.options.unchecked_text || '';

            if (fieldModel.options.validation_type) {
              formTypeOpts.user_validation = 'required';
            }
            if (fieldModel.options.agent_validation_type) {
              formTypeOpts.agent_validation = 'required';
              if (fieldModel.options.agent_validation_resolve) {
                formTypeOpts.agent_validation_resolve = true;
              }
            }

            if (fieldModel.default_value) {
              formTypeOpts.default_value = true;
            }
            break;

          case 'date': case 'datetime':
            if ((fieldModel.options != null ? fieldModel.options.date_valid_dow : undefined) != null) {
              formTypeOpts.valid_weekdays = [false, false, false, false, false, false, false];
              for (const day of Object.keys(fieldModel.options.date_valid_dow || {})) {
                formTypeOpts.valid_weekdays[day] = true;
              }
            }

            if (fieldModel.options != null ? fieldModel.options.calendar : undefined) {
              formTypeOpts.calendar = fieldModel.options.calendar;
            }
            if (fieldModel.options != null ? fieldModel.options.ignore_timezone : undefined) {
              formTypeOpts.ignore_timezone = !!fieldModel.options.ignore_timezone;
            }

            if (fieldModel.options.date_valid_type != null) {
              if (fieldModel.options.date_valid_type === 'date') {
                formTypeOpts.valid_dates_mode = 'date';
                if (fieldModel.options.date_valid_date1 != null) { formTypeOpts.date_valid_date1 = moment(fieldModel.options.date_valid_date1, 'YYYY-MM-DD').toDate(); }
                if (fieldModel.options.date_valid_date2 != null) { formTypeOpts.date_valid_date2 = moment(fieldModel.options.date_valid_date2, 'YYYY-MM-DD').toDate(); }
              }
              if (fieldModel.options.date_valid_type === 'range') {
                formTypeOpts.valid_dates_mode = 'range';
                if (fieldModel.options.date_valid_range1 != null) { formTypeOpts.date_valid_range1 = fieldModel.options.date_valid_range1; }
                if (fieldModel.options.date_valid_range2 != null) { formTypeOpts.date_valid_range2 = fieldModel.options.date_valid_range2; }
              }
            }

            if (fieldModel.options.required) {
              formTypeOpts.user_validation = 'required';
            }
            if (fieldModel.options.agent_required) {
              formTypeOpts.agent_validation = 'required';
              if (fieldModel.options.agent_validation_resolve) {
                formTypeOpts.agent_validation_resolve = true;
              }
            }
            break;

          case 'display':
            formTypeOpts.html = fieldModel.options.html;
            break;

          case 'hidden':
            formTypeOpts.cookie_name   = fieldModel.options.cookie_name   || '';
            formTypeOpts.param_name    = fieldModel.options.param_name    || '';
            formTypeOpts.default_value = fieldModel.options.default_value || '';

            if (fieldModel.default_value) {
              formTypeOpts.default_value = fieldModel.default_value;
            }
            break;

          case 'data': case 'datajson': case 'datalist':
            formTypeOpts.usersource_id = `${parseInt(fieldModel.options.usersource_id || '0') || 0}`;
            formTypeOpts.field_name    = fieldModel.options.field_name || '';
            break;

          case 'url':
            formTypeOpts.allow_file = !!fieldModel.options.allow_file;

            if (fieldModel.options.required) {
              formTypeOpts.user_validation = 'required';
            }
            if (fieldModel.options.agent_required) {
              formTypeOpts.agent_validation = 'required';
              if (fieldModel.options.agent_validation_resolve) {
                formTypeOpts.agent_validation_resolve = true;
              }
            }
            break;

          case 'currency':
            formTypeOpts.currency_id = fieldModel.options.currency_id;

            if (fieldModel.options.required) {
              formTypeOpts.user_validation = 'required';
            }
            if (fieldModel.options.agent_required) {
              formTypeOpts.agent_validation = 'required';
              if (fieldModel.options.agent_validation_resolve) {
                formTypeOpts.agent_validation_resolve = true;
              }
            }
            break;

          case 'file':
            formTypeOpts.multiple                    = !!fieldModel.options.multiple;
            formTypeOpts.user_validation             = !!fieldModel.options.required;
            formTypeOpts.user_extensions_limit_mode  = fieldModel.options.user_extensions_limit_mode;
            formTypeOpts.user_must_extensions        = fieldModel.options.user_must_extensions;
            formTypeOpts.user_not_extensions         = fieldModel.options.user_not_extensions;
            formTypeOpts.user_max_file_size          = fieldModel.options.user_max_file_size;
            formTypeOpts.agent_extensions_limit_mode = fieldModel.options.agent_extensions_limit_mode;
            formTypeOpts.agent_must_extensions       = fieldModel.options.agent_must_extensions;
            formTypeOpts.agent_not_extensions        = fieldModel.options.agent_not_extensions;
            formTypeOpts.agent_max_file_size         = fieldModel.options.agent_max_file_size;

            if (fieldModel.options.agent_required) {
              formTypeOpts.agent_validation = true;
              if (fieldModel.options.agent_validation_resolve) {
                formTypeOpts.agent_validation_resolve = true;
              }
            }
            break;
        }
      }

      if (fieldModel.options.agent_validation_resolve) {
        formTypeOpts.agent_validation_resolve = true;
      }

      console.log('belzebut ', form);

      return form;
    }


    /*
     * Use a form model to construct a payload we can deliver to the API to save
     * a field.
     *
     * @param {String} fieldType  The field type
     * @param {Object} formModel  The form model
     * @return {Object}
     */
    getPostDataFromForm(fieldType, formModel) {
      let formTypeOpts;
      const postData = {
        title:          formModel.title,
        alias:          formModel.alias,
        description:    formModel.description,
        is_agent_field: formModel.is_agent_field,
        is_enabled:     formModel.is_enabled
      };

      if (fieldType === 'textarea' || fieldType === 'javascript') {
        formTypeOpts = formModel.text;
      } else {
        formTypeOpts = formModel[fieldType];
      }

      switch (fieldType) {
        case 'text':
        case 'textarea':
        case 'javascript':
          if (fieldType === 'text') {
            postData.handler_class = 'Application\\DeskPRO\\CustomFields\\Handler\\Text';
          } else if (fieldType === 'textarea') {
            postData.handler_class = 'Application\\DeskPRO\\CustomFields\\Handler\\Textarea';
          } else {
            postData.handler_class = 'Application\\DeskPRO\\CustomFields\\Handler\\Javascript';
          }

          postData.default_value = formTypeOpts.default_value;
          postData.clickable_links = formTypeOpts.clickable_links;

          if (formTypeOpts.user_validation === 'required') {
            postData.validation_type = 'required';
            postData.min_length = formTypeOpts.min_length;
            postData.max_length = formTypeOpts.max_length;
          } else if (formTypeOpts.user_validation === 'regex') {
            postData.validation_type = 'regex';
            postData.regex = formTypeOpts.regex;
            postData.regex_required = formTypeOpts.regex_required;
          }

          if (formTypeOpts.agent_validation === 'required') {
            postData.agent_validation_type = 'required';
            postData.agent_min_length = formTypeOpts.agent_min_length;
            postData.agent_max_length = formTypeOpts.agent_max_length;
          } else if (formTypeOpts.agent_validation === 'regex') {
            postData.agent_validation_type = 'regex';
            postData.agent_regex = formTypeOpts.agent_regex;
            postData.agent_regex_required = formTypeOpts.agent_regex_required;
          }
          if (fieldType === 'javascript') {
            postData.code = formTypeOpts.code;
          }
          break;

        case 'choice':
          postData.handler_class = 'Application\\DeskPRO\\CustomFields\\Handler\\Choice';
          postData.field_type = formTypeOpts.field_type;
          postData.choices_structure = formTypeOpts.options;
          postData.default_value = formTypeOpts.default_value;

          if (formTypeOpts.user_validation === 'required') {
            postData.validation_type = 'required';
            postData.min_length = 1;
          }
          if (formTypeOpts.agent_validation === 'required') {
            postData.agent_validation_type = 'required';
            postData.agent_min_length = 1;
          }

          if (formTypeOpts.field_type === 'radio') {
            postData.none_choice = formTypeOpts.none_choice;
            if (formTypeOpts.none_choice) {
              postData.none_choice_title = formTypeOpts.none_choice_title;
            }
          }
          break;

        case 'eula':
          postData.sys_name = 'eula';
          postData.handler_class = 'Application\\DeskPRO\\CustomFields\\Handler\\Choice';
          postData.field_type = formTypeOpts.field_type;
          postData.choices_structure = formTypeOpts.options;
          break;

        case 'toggle':
          postData.handler_class = 'Application\\DeskPRO\\CustomFields\\Handler\\Toggle';
          postData.default_value = formTypeOpts.default_value;
          postData.label_text = formTypeOpts.label_text;
          postData.unchecked_text = formTypeOpts.unchecked_text;

          if (formTypeOpts.user_validation === 'required') {
            postData.validation_type = 'required';
          }
          if (formTypeOpts.agent_validation === 'required') {
            postData.agent_validation_type = 'required';
          }
          break;

        case 'date': case 'datetime':
          var format = 'YYYY-MM-DD';
          if (fieldType === 'date') {
            postData.handler_class = 'Application\\DeskPRO\\CustomFields\\Handler\\Date';
          } else {
            postData.handler_class = 'Application\\DeskPRO\\CustomFields\\Handler\\DateTime';
            format += ' HH:mm';
          }

          if (formTypeOpts.default_mode === 'date') {
            postData.default_value = moment(formTypeOpts.default_value).utc().format(format);
          } else {
            postData.default_value = null;
          }

          postData.default_mode = formTypeOpts.default_mode;

          postData.date_valid_dow = [];
          for (let day = 0; day < formTypeOpts.valid_weekdays.length; day++) {
            const x = formTypeOpts.valid_weekdays[day];
            if (x) {
              postData.date_valid_dow.push(day);
            }
          }

          if (formTypeOpts.user_validation === 'required') {
            postData.required = true;
          }
          if (formTypeOpts.agent_validation === 'required') {
            postData.agent_required = true;
          }

          postData.date_valid_type = formTypeOpts.valid_dates_mode;
          postData.calendar = formTypeOpts.calendar;
          postData.ignore_timezone = !!formTypeOpts.ignore_timezone;

          if (formTypeOpts.valid_dates_mode === 'date') {
            postData.date_valid_date1 = '';
            postData.date_valid_date2 = '';

            if (!Util.isBlank(formTypeOpts.date_valid_date1)) {
              postData.date_valid_date1 = moment(formTypeOpts.date_valid_date1).format('YYYY-MM-DD');
            }
            if (!Util.isBlank(formTypeOpts.date_valid_date2)) {
              postData.date_valid_date2 = moment(formTypeOpts.date_valid_date2).format('YYYY-MM-DD');
            }
          } else if (formTypeOpts.valid_dates_mode === 'range') {
            postData.date_valid_range1 = formTypeOpts.date_valid_range1;
            postData.date_valid_range2 = formTypeOpts.date_valid_range2;
          }
          break;

        case 'display':
          postData.handler_class = 'Application\\DeskPRO\\CustomFields\\Handler\\Display';
          postData.html = formTypeOpts.html;
          break;

        case 'hidden':
          postData.handler_class = 'Application\\DeskPRO\\CustomFields\\Handler\\Hidden';
          postData.cookie_name = formTypeOpts.cookie_name;
          postData.param_name = formTypeOpts.param_name;
          postData.default_value = formTypeOpts.default_value;
          break;

        case 'data':
          postData.handler_class = 'Application\\DeskPRO\\CustomFields\\Handler\\Data';
          postData.usersource_id = parseInt(formTypeOpts.usersource_id) || 0;
          postData.field_name    = formTypeOpts.field_name;
          break;

        case 'url':
          postData.handler_class = 'Application\\DeskPRO\\CustomFields\\Handler\\Url';
          postData.allow_file    = formTypeOpts.allow_file;

          if (formTypeOpts.user_validation === 'required') {
            postData.required = true;
          }
          if (formTypeOpts.agent_validation === 'required') {
            postData.agent_required = true;
          }
          break;

        case 'currency':
          postData.handler_class = 'Application\\DeskPRO\\CustomFields\\Handler\\Currency';
          postData.currency_id = formTypeOpts.currency_id;

          if (formTypeOpts.user_validation === 'required') {
            postData.required = true;
          }
          if (formTypeOpts.agent_validation === 'required') {
            postData.agent_required = true;
          }
          break;

        case 'file':
          postData.handler_class               = 'Application\\DeskPRO\\CustomFields\\Handler\\File';
          postData.multiple                    = formTypeOpts.multiple;
          postData.user_extensions_limit_mode  = formTypeOpts.user_extensions_limit_mode;
          postData.user_must_extensions        = formTypeOpts.user_must_extensions;
          postData.user_not_extensions         = formTypeOpts.user_not_extensions;
          postData.user_max_file_size          = formTypeOpts.user_max_file_size;
          postData.agent_extensions_limit_mode = formTypeOpts.agent_extensions_limit_mode;
          postData.agent_must_extensions       = formTypeOpts.agent_must_extensions;
          postData.agent_not_extensions        = formTypeOpts.agent_not_extensions;
          postData.agent_max_file_size         = formTypeOpts.agent_max_file_size;

          if (formTypeOpts.user_validation) {
            postData.required = true;
          }
          if (formTypeOpts.agent_validation) {
            postData.agent_required = true;
          }
          break;
      }

      if (formTypeOpts.agent_validation_resolve) {
        postData.agent_validation_resolve = true;
      }

      return postData;
    }

    getDefaultCode() {
      return '/**\n' +
    ' * This function is called whenever your field is added to\n' +
    ' * a form. Note that the function will not be called until the first\n' +
    ' * time the field becomes visible.\n' +
    ' *\n' +
    ' * The function is passed a $ctx context object. This is how your field\n' +
    ' * interacts with the form.\n' +
    ' *\n' +
    ' * Properties in $ctx include:\n' +
    ' * $ctx.field      -- the current field info such as $ctx.field.id\n' +
    ' * $ctx.interface  -- "agent" or "portal"\n' +
    ' * $ctx.user       -- Current user info such as $ctx.user.id and $ctx.user.primary_email.email\n' +
    ' * $ctx.ticket     -- Current ticket info such as $ctx.ticket.id (may be null if new ticket)\n' +
    ' * $ctx.context    -- "newticket" or "viewticket"\n' +
    ' * $ctx.jQuery     -- Reference to jQuery\n' +
    ' * $ctx.Handlebars -- Reference to Handlebars templating engine\n' +
    ' */\n' +
    'function makeFieldDefinition($ctx) {\n' +
    '\t// jQuery is available. You can assign a reference to make it easier to use.\n' +
    '\tvar $ = $ctx.jQuery;\n' +
    '\n' +
    '\t// Handlebars is also available if you want a way to render templates easier\n'  +
    '\t// See https://handlebarsjs.com/\n' +
    '\tvar Handlebars = $ctx.Handlebars;\n' +
    '\n' +
    '\t// Example use of a template\n' +
    ' \tvar linkTpl = Handlebars.compile(\n' +
    ' \t\t"{{title}} <a href=\'http://example.com?product={{id}}\' target=\'_blank\'>Option</a>"\n' +
    ' \t);\n' +
    '\n' +
    '\n' +
    '\t/**\n' +
    '\t * This is called to render the HTML that will be placed\n' +
    '\t * into the form.\n' +
    '\t *\n' +
    '\t * This function is called only once the first time the\n'  +
    '\t * field becomes visible. After that, the fields\n' +
    '\t * are only shown/hidden based on the layout rules.\n' +
    '\t *\n' +
    '\t * If you want to keep references to fields here for later use, then\n' +
    '\t * this is typically where you\'d set those.\n' +
    '\t *\n' +
    '\t * @param {Function} setValue  A function you should call to change the value\n' +
    '\t *                             of the field. This is value that ultimately gets\n' +
    '\t *                             saved to the database.\n' +
    '\t *\n' +
    '\t *                             This accepts one or two parameters:\n' +
    '\t *                                 setValue(string[, object])\n' +
    '\t *                             You must always specify a string value, and you can\n' +
    '\t *                             optionally specify an arbitrary object that will be\n' +
    '\t *                             saved alongside the value. You can use this value\n' +
    '\t *                             later in the renderValue function.\n' +
    '\t * @param {string} currentValue The currently set value. May be null.\n' +
    '\t * @param {object} currentData  Arbitrary data saved with the current value.\n' +
    '\t * @param {HTMLElement} fieldContainer The HTML element that wraps your field.\n' +
    '\t * @return HTMLElement or jQuery collection or an HTML string. Note that this MUST\n' +
    '\t *         be a single element. i.e. if you need multiple elements, then you should\n' +
    '\t *         wrap it in a div or other container.\n' +
    '\t */\n' +
    '\t$ctx.renderField = function(setValue, currentValue, currentData) {\n' +
    '\t\tvar $selectEl = $("<select></select>");\n' +
    '\n' +
    '        var data = [\n' +
    '            {"id": 1, "title": "test1"},    \n' +
    '            {"id": 2, "title": "test2"},\n' +
    '        ];\n' +
    '\n' +
    '\t\tdata.forEach(function (p, index) {\n' +
    '\t    \tvar $opt = $("<option />").val(p.id).text(p.title);\n' +
    '\t    \tif (currentData) {\n' +
    '\t    \t\tif (parseInt(currentData.id, 10) === p.id) {\n' +
    '\t\t\t\t\t$opt.prop(\'selected\', true);\n' +
    '\t\t\t\t} else if (!currentData.id && index === 0) {\n' +
    '\t\t\t\t    $opt.prop(\'selected\', true);\n' +
    '\t\t\t\t    setValue(p.title, { id: p.id });\n' +
    '\t\t\t\t}\n' +
    '\t\t\t}\n' +
    '\t\t\t$opt.appendTo($selectEl);\n' +
    '\t\t});\n' +
    '\n' +
    '\t\t$selectEl.on(\'change\', function() {\n' +
    '\t\t\tvar $selectedOpt = $(this).children("option:selected");\n' +
    '\n' +
    '\t\t\t// Save the option title as the value, and the option value in the extra data\n' +
    '\t\t\tsetValue($selectedOpt.text(), { id: $selectedOpt.val() });\n' +
    '\t\t});\n' +
    '\n' +
    '\t\treturn $selectEl;\n' +
    '\t};\n' +
    '\n' +
    '\t/**\n' +
    '\t * This function gets called when your form field\n' +
    '\t * is rendered to the screen.\n' +
    '\t * \n' +
    '\t * This function is called whenever the field is rendered\n' +
    '\t * to the screen. It may be called multiple times on this instance\n' +
    '\t * if the field were to change.\n' +
    '\t *\n' +
    '\t * @param {string} value  The value saved\n' +
    '\t * @param {object} data   The whole arbitrary data object saved.\n' +
    '\t * @return HTMLElement or jQuery collection or an HTML string\n' +
    '\t */\n' +
    '\t$ctx.renderValue = function(value, data) {\n' +
    '\t    // In the agent interface, render a link\n' +
    '\t\tif ($ctx.interface == "agent") {\n' +
    '\t\t\treturn linkTpl({\n' +
    '\t\t\t\ttitle: value,\n' +
    '\t\t\t\tid: data.id\n' +
    '\t\t\t});\n' +
    '\t\t// In the user interface, render just the value\n' +
    '\t\t} else {\n' +
    '\t\t\treturn value;\n' +
    '\t\t}\n' +
    '\t};\n' +
    '\n' +
    '\t/**\n' +
    '\t * This is called when the form is submitted.\n' +
    '\t * This is only called if the field is actually visible.\n' +
    '\t *\n' +
    '\t * The return value is significant:\n' +
    '\t * - false -- the submit is aborted. i.e. here is where you might add validation.\n' +
    '\t * - any non-false (true, null, undefined, etc) the submit goes through normally\n' +
    '\t *\n' +
    '\t * You may also return a Promise. The submit will wait until the promise resolves.\n' +
    '\t * If the promise resolves to false, or if the promise rejects, then the submit is aborted.\n' +
    '\t * Any other resolve value (true, null, undefined, etc) the submit continues as normal.\n' +
    '\t */\n' +
    '\t$ctx.onSubmit = function(currentValue, currentData, $field) {\n' +
    '\t\tif (!currentValue) {\n' +
    '\t  \t\talert("You need to enter a value");\n' +
    '\t  \t\treturn false;\n' +
    '\t  \t}\n' +
    '\t};\n' +
    '\n' +
    '\t/**\n' +
    '\t * This is called when the form field actually becomes\n' +
    '\t * visible to the user.\n' +
    '\t * @param {string} currentValue The currently set value. May be null.\n' +
    '\t * @param {object} currentData  Arbitrary data saved with the current value.\n' +
    '\t * @param {HTMLElement} your actual hidden input.\n' +
    '\t * @param {HTMLElement} your actual rendered element returned from renderField method.\n' +
    '\t * \n' +
    '\t * This function may be called multiple times.\n' +
    '\t */\n' +
    '\t$ctx.onShow = function(currentValue, currentData, $field, $element) {\n' +
    '\t    $this = this;\n' +
    '\t\t$element.find(\'option\').each(function (index, o) {\n' +
    '\t\t    var $o = $this.jQuery(o);\n' +
    '\t    \tif (parseInt(currentData.id, 10) === parseInt($o.val(), 10)) {\n' +
    '\t\t\t\t$o.prop(\'selected\', true);\n' +
    '\t\t\t} else if (!currentData.id && index === 0) {\n' +
    '\t\t\t    $o.prop(\'selected\', true);\n' +
    '\t\t\t}\n' +
    '    \t});\n' +
    '\t};\n' +
    '\n' +
    '\t/**\n' +
    '\t * This is called when the form field is hidden from view\n' +
    '\t * (i.e. the layout changes to hide it).\n' +
    '\t *\n' +
    '\t * This function may be called multiple times.\n' +
    '\t */\n' +
    '\t$ctx.onHide = function(currentValue, currentData, $field, $element) {\n' +
    '\t\t// you can do something here\n' +
    '\t\t// e.g. use $field to get a reference to your field\n' +
    '\t}\n' +
    '}';
    }

    /*
    * Applies basic settings from form onto the real field model
      * so the list is showing correct data.
    */
    applyFormToModel(fieldModel, formModel) {
      fieldModel.title = formModel.title;
      fieldModel.is_enabled = formModel.is_enabled;
      return fieldModel;
    }
  }

  return FieldFormMapper;
});
