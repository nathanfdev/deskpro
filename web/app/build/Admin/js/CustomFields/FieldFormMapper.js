(function() {
  define(['moment', 'DeskPRO/Util/Util'], function(moment, Util) {
    var FieldFormMapper;
    return FieldFormMapper = (function() {
      function FieldFormMapper() {}


      /*
      		 * Get a form model for an existing field
      		 *
      		 * @param {Object} fieldModel The field model (eg as returned from the API)
      		 * @return {Object}
       */

      FieldFormMapper.prototype.getFormFromModel = function(fieldModel) {
        var day, form, formTypeOpts, _i, _len, _ref;
        fieldModel = fieldModel || {};
        fieldModel.options = fieldModel.options || {};
        form = {
          title: '',
          description: '',
          text: {
            user_validation: '0',
            user_min_length: '1',
            user_max_length: '',
            user_regex: '',
            agent_validation: '0',
            agent_min_length: '1',
            agent_max_length: '',
            agent_regex: '',
            agent_validation_resolve: false
          },
          toggle: {
            label_text: '',
            user_validation: '0',
            agent_validation: '0',
            agent_validation_resolve: false
          },
          choice: {
            field_type: 'select',
            options: [],
            user_validation: '0',
            agent_validation: '0',
            agent_validation_resolve: false
          },
          date: {
            default_mode: '0',
            default_value: '',
            valid_weekdays: [true, true, true, true, true, true, true],
            valid_dates_mode: '0',
            valid_date_range_start: '',
            valid_date_range_end: '',
            valid_date_relrange_start: '',
            valid_date_relrange_end: '',
            user_validation: '0',
            agent_validation: '0',
            agent_validation_resolve: false
          },
          display: {
            html: ''
          },
          hidden: {
            cookie_name: '',
            param_name: '',
            default_value: ''
          }
        };
        if (fieldModel) {
          form.title = fieldModel.title;
          form.description = fieldModel.description;
          if (fieldModel.type_name === 'textarea') {
            formTypeOpts = form['text'];
          } else {
            formTypeOpts = form[fieldModel.type_name];
          }
          if (fieldModel.is_agent_field) {
            form.is_agent_field = true;
          }
          if (fieldModel.is_enabled) {
            form.is_enabled = true;
          }
          switch (fieldModel.type_name) {
            case "text":
            case "textarea":
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
                }
              }
              if (fieldModel.default_value) {
                formTypeOpts.default_value = fieldModel.default_value;
              }
              break;
            case "choice":
              if (fieldModel.options.expanded) {
                if (fieldModel.options.multiple) {
                  formTypeOpts.field_type = 'checkbox';
                } else {
                  formTypeOpts.field_type = 'radio';
                }
              } else {
                if (fieldModel.options.multiple) {
                  formTypeOpts.field_type = 'multi_select';
                } else {
                  formTypeOpts.field_type = 'select';
                }
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
              if (fieldModel.default_value) {
                formTypeOpts.default_value = parseInt(fieldModel.default_value);
              }
              break;
            case "toggle":
              formTypeOpts.label_text = fieldModel.options.label_text || '';
              if (fieldModel.options.required) {
                formTypeOpts.user_validation = 'required';
              }
              if (fieldModel.options.agent_required) {
                formTypeOpts.agent_validation = 'required';
                if (fieldModel.options.agent_validation_resolve) {
                  formTypeOpts.agent_validation_resolve = true;
                }
              }
              if (fieldModel.default_value) {
                formTypeOpts.default_value = true;
              }
              break;
            case "date":
              if (!Util.isBlank(fieldModel.default_value)) {
                formTypeOpts.default_mode = 'date';
                formTypeOpts.default_value = moment(fieldModel.default_value, 'YYYY-MM-DD').toDate();
              }
              if (!Util.isBlank(fieldModel.options.date_valid_dow)) {
                formTypeOpts.valid_weekdays = [false, false, false, false, false, false, false];
                _ref = fieldModel.options.date_valid_dow;
                for (_i = 0, _len = _ref.length; _i < _len; _i++) {
                  day = _ref[_i];
                  formTypeOpts.valid_weekdays[day] = true;
                }
              }
              if (fieldModel.options.date_valid_type != null) {
                if (fieldModel.options.date_valid_type === "date") {
                  formTypeOpts.valid_dates_mode = 'date';
                  if (!Util.isBlank(fieldModel.options.date_valid_date1)) {
                    formTypeOpts.date_valid_date1 = moment(fieldModel.options.date_valid_date1, 'YYYY-MM-DD').toDate();
                  }
                  if (!Util.isBlank(fieldModel.options.date_valid_date2)) {
                    formTypeOpts.date_valid_date2 = moment(fieldModel.options.date_valid_date2, 'YYYY-MM-DD').toDate();
                  }
                }
                if (fieldModel.options.date_valid_type === "range") {
                  if (!Util.isBlank(fieldModel.options.date_valid_date1)) {
                    formTypeOpts.date_valid_reldate1 = fieldModel.options.date_valid_date1;
                  }
                  if (!Util.isBlank(fieldModel.options.date_valid_date2)) {
                    formTypeOpts.date_valid_reldate2 = fieldModel.options.date_valid_date2;
                  }
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
            case "display":
              formTypeOpts.html = fieldModel.options.html;
              break;
            case "hidden":
              formTypeOpts.cookie_name = fieldModel.options.cookie_name || '';
              formTypeOpts.param_name = fieldModel.options.param_name || '';
              formTypeOpts.default_value = fieldModel.options.default_value || '';
              if (fieldModel.default_value) {
                formTypeOpts.default_value = fieldModel.default_value;
              }
          }
        }
        if (fieldModel.options.agent_validation_resolve) {
          formTypeOpts.agent_validation_resolve = true;
        }
        return form;
      };


      /*
      		 * Use a form model to construct a payload we can deliver to the API to save
      		 * a field.
      		 *
      		 * @param {String} fieldType  The field type
      		 * @param {Object} formModel  The form model
      		 * @return {Object}
       */

      FieldFormMapper.prototype.getPostDataFromForm = function(fieldType, formModel) {
        var day, formTypeOpts, postData, x, _i, _len, _ref;
        postData = {
          title: formModel.title,
          description: formModel.description,
          is_agent_field: formModel.is_agent_field,
          is_enabled: formModel.is_enabled
        };
        if (fieldType === 'textarea') {
          formTypeOpts = formModel['text'];
        } else {
          formTypeOpts = formModel[fieldType];
        }
        switch (fieldType) {
          case "text":
          case "textarea":
            if (fieldType === 'text') {
              postData.handler_class = 'Application\\DeskPRO\\CustomFields\\Handler\\Text';
            } else {
              postData.handler_class = 'Application\\DeskPRO\\CustomFields\\Handler\\Textarea';
            }
            postData.default_value = formTypeOpts.default_value;
            if (formTypeOpts.user_validation === 'required') {
              postData.validation_type = 'required';
              postData.min_length = formTypeOpts.agent_min_length;
              postData.max_length = formTypeOpts.agent_max_length;
            } else if (formTypeOpts.user_validation === 'regex') {
              postData.validation_type = 'regex';
              postData.regex = formTypeOpts.validation_regex;
            }
            if (formTypeOpts.agent_validation === 'required') {
              postData.agent_validation_type = 'required';
              postData.agent_min_length = formTypeOpts.agent_min_length;
              postData.agent_max_length = formTypeOpts.agent_max_length;
            } else if (formTypeOpts.agent_validation === 'regex') {
              postData.agent_validation_type = 'regex';
              postData.agent_regex = formTypeOpts.agent_regex;
            }
            break;
          case "choice":
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
            break;
          case "toggle":
            postData.handler_class = 'Application\\DeskPRO\\CustomFields\\Handler\\Toggle';
            postData.default_value = formTypeOpts.default_value;
            postData.label_text = formTypeOpts.label_text;
            if (formTypeOpts.user_validation === 'required') {
              postData.validation_type = 'required';
            }
            if (formTypeOpts.agent_validation === 'required') {
              postData.agent_validation_type = 'required';
            }
            break;
          case "date":
            postData.handler_class = 'Application\\DeskPRO\\CustomFields\\Handler\\Date';
            if (formTypeOpts.default_mode === 'date') {
              postData.default_value = moment(formTypeOpts.default_value).format('YYYY-MM-DD');
            }
            postData.date_valid_dow = [];
            _ref = formTypeOpts.valid_weekdays;
            for (day = _i = 0, _len = _ref.length; _i < _len; day = ++_i) {
              x = _ref[day];
              if (x) {
                postData.date_valid_dow.push(day);
              }
            }
            if (formTypeOpts.user_validation === 'required') {
              postData.validation_type = 'required';
            }
            if (formTypeOpts.agent_validation === 'required') {
              postData.agent_validation_type = 'required';
            }
            if (formTypeOpts.valid_dates_mode === 'date') {
              postData.date_valid_type = 'date';
              postData.date_valid_date1 = '';
              postData.date_valid_date2 = '';
              if (!Util.isBlank(formTypeOpts.date_valid_date1)) {
                postData.date_valid_date1 = moment(formTypeOpts.date_valid_date1).format('YYYY-MM-DD');
              }
              if (!Util.isBlank(formTypeOpts.date_valid_date2)) {
                postData.date_valid_date2 = moment(formTypeOpts.date_valid_date2).format('YYYY-MM-DD');
              }
            } else if (formTypeOpts.valid_dates_mode === 'range') {
              postData.date_valid_type = 'range';
              postData.date_valid_range1 = '';
              postData.date_valid_range2 = '';
              if (!Util.isBlank(formTypeOpts.date_valid_reldate1)) {
                postData.date_valid_range1 = formTypeOpts.date_valid_reldate1;
              }
              if (!Util.isBlank(formTypeOpts.date_valid_reldate2)) {
                postData.date_valid_range2 = formTypeOpts.date_valid_reldate2;
              }
            }
            break;
          case "display":
            postData.handler_class = 'Application\\DeskPRO\\CustomFields\\Handler\\Display';
            postData.html = formTypeOpts.html;
            break;
          case "hidden":
            postData.handler_class = 'Application\\DeskPRO\\CustomFields\\Handler\\Hidden';
            postData.cookie_name = formTypeOpts.cookie_name;
            postData.param_name = formTypeOpts.param_name;
            postData.default_value = formTypeOpts.default_value;
        }
        if (formTypeOpts.agent_validation_resolve) {
          postData.agent_validation_resolve = true;
        }
        return postData;
      };


      /*
      		 * Applies basic settings from form onto the real field model
        	 * so the list is showing correct data.
       */

      FieldFormMapper.prototype.applyFormToModel = function(fieldModel, formModel) {
        fieldModel.title = formModel.title;
        fieldModel.is_enabled = formModel.is_enabled;
        return fieldModel;
      };

      return FieldFormMapper;

    })();
  });

}).call(this);

//# sourceMappingURL=FieldFormMapper.js.map
