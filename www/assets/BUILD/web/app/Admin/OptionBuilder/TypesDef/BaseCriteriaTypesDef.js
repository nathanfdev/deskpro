// TODO: This file was created by bulk-decaffeinate.
// Sanity-check the conversion and remove this comment.
/*
 * decaffeinate suggestions:
 * DS001: Remove Babel/TypeScript constructor workaround
 * DS102: Remove unnecessary code created because of implicit returns
 * DS207: Consider shorter variations of null checks
 * Full docs: https://github.com/decaffeinate/decaffeinate/blob/master/docs/suggestions.md
 */
define([
  'DeskPRO/Util/Util',
  'DeskPRO/Util/Arrays',
  'Admin/OptionBuilder/TypesDef/BaseTypesDef',
  'underscore',
  'moment'
], function(
  Util,
  Arrays,
  BaseTypesDef,
  _,
  moment
) {
  class Admin_OptionBuilder_TypesDef_BaseCriteriaTypesDef extends BaseTypesDef {
    constructor($q, Api, dpTemplateManager) {
      {
        // Hack: trick Babel/TypeScript into allowing this before super.
        if (false) { super(); }
        let thisFn = (() => { return this; }).toString();
        let thisName = thisFn.slice(thisFn.indexOf('return') + 6 + 1, thisFn.indexOf(';')).trim();
        eval(`${thisName} = this;`);
      }
      this.$q = $q;
      this.Api = Api;
      this.dpTemplateManager = dpTemplateManager;
      this.options_data        = null;
      this.inputTemplate       = 'OptionBuilder/type-criteria-input.html';
      this.dateTemplate        = 'OptionBuilder/type-criteria-date.html';
      this.timeElapsedTemplate = 'OptionBuilder/type-criteria-time-elapsed.html';
      this.selectTemplate      = 'OptionBuilder/type-criteria-select.html';
      this.isTemplate          = 'OptionBuilder/type-criteria-is.html';
      this.remoteTemplate      = 'OptionBuilder/type-criteria-remote.html';
      this.init();
    }

    init() {
    }

    /*
      * Gets a type definition by calling a getX method on this class
    */
    getDef(type, options) {
      if (options == null) { options = {}; }
      const typeName = type;
      options.type = type;

      const typeFunc = `get${typeName}`;
      if (this[typeFunc] != null) {
        return this[typeFunc](options);
      } else {
        console.error(`Bad type with no definition getter: ${typeFunc}`);
        const me = this;
        return {
          getTemplate() {
            return me.dpTemplateManager.get(me.inputTemplate);
          },
          getData() {
            return {};
          },
          getDataFormatter() {
            return {
              getViewValue(value, data) {
                if (value == null) { value = {}; }
                return {};
              },
              getValue(model, data) {
                if (model == null) { model = {}; }
                return null;
              }
            };
          }
        };
      }
    }

    /*
      * @param {Object} options
      * @return {Array}
    */
    getOperators(options) {
      const ops = options.operators || ['is', 'not', 'isset', 'not_isset'];
      return ops;
    }

    /*
      * Constructs standard input from a custom field def
    */
    getStandardForFieldDef(field, options) {
      if (options == null) { options = {}; }
      options.type_name = field.type_name;
      if (!options.propName) { options.propName = 'value'; }

      if (field.type_name === 'choice') {
        options.operators = options.operators || ['is', 'not', 'isset', 'not_isset', 'touched', 'nottouched'];
        options.options = field.choices.map( o => ({title: o.title, value: o.id, parent_id: o.parent_id}));
        return this.getStandardSelect(options, field);
      } else if (field.type_name === 'toggle') {
        options.operators = options.operators || ['isset', 'not_isset', 'touched', 'nottouched'];
        options.options = [{title: 'On', value: "1"}, {title: "Off", value: "0"}];
        options.single = true;
        return this.getStandardSelect(options, field);
      } else if ((field.type_name === 'date') || (field.type_name === 'datetime')) {
        options.operators = options.operators || ['lte', 'gte', 'between'];
        return this.getDateInput(options, field);
      } else if (field.type_name === 'currency') {
        options.operators = options.operators || ['is', 'not', 'lte', 'gte', 'between'];
        return this.getStandardInput(options, field);
      } else {
        if (!options.operators) { options.operators = ['is', 'not', 'touched', 'nottouched', 'contains', 'notcontains', 'is_regex', 'not_regex', 'isset', 'not_isset']; }
        return this.getStandardInput(options, field);
      }
    }

    /*
      * Sets the getter for a custom field
      *
      * @param {String} base_name The base name of the field type (e.g., TicketField, UserField etc)
      * @param {Object} f         The field
      * @retrn {String} The name of the field that was set
      */
    initFieldGetter(base_name, f, force, base_options) {
      if (base_options == null) { base_options = {}; }
      const fname = base_name + f.id;

      if (!this[`get${fname}`] || (force != null)) {
        this[`get${fname}`] = options => {
          if (options == null) { options = {}; }
          if (base_options.operators) {
            options.operators = base_options.operators;
          }
          if (base_options.type_name) {
            options.type_name = base_options.type_name;
          }
          options.type = base_name + f.id;
          options.field_id = f.id;
          return this.getStandardForFieldDef(f, options);
        };
      }

      return fname;
    }

    /*
      * Constructs a standard select box type
    */
    getStandardSelect(options, field) {
      const { type }      = options;
      const prop_name = options.propName;
      const data_name = options.dataName;
      const form_type = options.formType || 'select';
      const operators = this.getOperators(options);
      let options_formatter = options.optionsFormatter || null;
      const extraOptions = options.extraOptions || null;

      if (!options_formatter) {
        options_formatter = options => {
          return this.standardOptionsFormatter(options, extraOptions);
        };
      }

      const me = this;

      return {
        getTemplate() {
          if (options.template) {
            return me.dpTemplateManager.get(options.template);
          }
          switch (form_type) {
            case 'input':
              return me.dpTemplateManager.get(me.inputTemplate);
            default:
              return me.dpTemplateManager.get(me.selectTemplate);
          }
        },

        getData() {
          if (options.options) {
            return {
              fieldOptions: options,
              operators,
              options: options_formatter ? options_formatter(options.options) : options.options,
              multiselect: !options.single
            };
          } else if (data_name) {
            const defer = me.$q.defer();
            me.loadDataOptions().then(() => {
              return defer.resolve({
                operators,
                fieldOptions: options,
                options: options_formatter ? options_formatter(me.options_data[data_name]) : me.options_data[data_name],
                multiselect: !options.single
              });
            });

            return defer.promise;
          } else {
            return {
              operators,
              fieldOptions: options
            };
          }
        },

        getDataFormatter() {
          return {
            getViewValue(value, data) {
              if (value == null) { value = {}; }
              let val = (value.options != null ? value.options[prop_name] : undefined) || null;

              if ((val === null) && data.options && prop_name) {
                val = (data.options[0] != null ? data.options[0].value : undefined) || null;
              }

              if (!options.single && !Util.isArray(val)) {
                if (val) {
                  val = [val];
                } else {
                  val = [];
                }
              }

              return {
                value: val,
                op: value.op || _.first(data.operators)
              };
            },
            getValue(model, data) {
              if (model == null) { model = {}; }
              const value = {};
              value.type = type;
              value.op = model.op;
              value.options = {};
              value.options[prop_name] = model.value;
              if (field) {
                value.options.type_name = field.type_name;
              }
              return value;
            }
          };
        }
      };
    }


    /*
     * Constructs a standard "is" template (no options, just a boolean is)
     */
    getStandardIs(options) {
      const { type }      = options;
      const prop_name = options.propName;

      const me = this;
      return {
        getTemplate() {
          return me.dpTemplateManager.get(me.isTemplate);
        },

        getData() {
          return {};
        },

        getDataFormatter() {
          return {
            getViewValue(value, data) {
              if (value == null) { value = {}; }
              return {
                value: true,
                op: 'is'
              };
            },
            getValue(model, data) {
              if (model == null) { model = {}; }
              const value = {};
              value.type = type;
              value.op = 'is';
              value.options = {};
              value.options[prop_name] = true;
              return value;
            }
          };
        }
      };
    }


    /*
      * Constructs a standard input box
    */
    getStandardInput(options, field) {
      const { type }      = options;
      const prop_name = options.propName;
      const operators = this.getOperators(options);

      const me = this;
      return {
        getTemplate() {
          return me.dpTemplateManager.get(me.inputTemplate);
        },

        getData() {
          return {
            operators,
            options
          };
        },

        getDataFormatter() {
          return {
            getViewValue(value, data) {
              if (value == null) { value = {}; }
              let val = (value.options != null ? value.options[prop_name] : undefined) || '';
              if (Util.isArray(val)) { val = val.join(','); }

              if (value.op) {
                if ((value.op === 'is') && (operators.indexOf('is') === -1)) {
                  value.op = 'contains';
                } else if ((value.op === 'not') && (operators.indexOf('not') === -1)) {
                  value.op = 'notcontains';
                }
              }

              return {
                value: val,
                op: value.op || _.first(data.operators)
              };
            },
            getValue(model, data) {

              if (model == null) { model = {}; }
              let val = model.value || '';
              if (options.tags) {
                val = val.split(',');
              }

              const value = {};
              value.type = type;
              value.op = model.op;
              value.options = {};
              value.options[prop_name] = val;
              if (field) {
                value.options.type_name = field.type_name;
              }

              return value;
            }
            };
        }
      };
    }

    getTimeElapsedInput(options) {
      const { type }      = options;
      const operators = options.operators || ['lte', 'gte'];
      const prop_name = options.propName;
      const me = this;
      return {
        getTemplate() {
          return me.dpTemplateManager.get(me.timeElapsedTemplate);
        },

        getData() {
          return {
            operators
          };
        },

        getDataFormatter() {
          return {
          getViewValue(value, data) {
            if (value == null) { value = {}; }
            const val = (value.options != null ? value.options[prop_name] : undefined) || [1, 'days'];
            return {
              op: value.op || _.first(operators),
              value: val
            };
          },
          getValue(model, data) {

            if (model == null) { model = {}; }
            const val = model.value || [1, 'days'];

            const value = {};
            value.type = type;
            value.op = model.op;
            value.options = {};
            value.options[prop_name] = val;
            return value;
          }
          };
        }
      };
    }

    getDateInput(options, field) {
      const { type }      = options;
      const operators = options.operators || ['lte', 'gte', 'between'];
      const me = this;
      return {
        getTemplate() {
          return me.dpTemplateManager.get(me.dateTemplate);
        },

        getData() {
          return {
            operators,
            options
          };
        },

        getDataFormatter() {
          return {
            getViewValue(value, data) {
              if (value == null) { value = {}; }
              value.options = value.options || {};

              let date1 = null;
              let date2 = null;
              let date1_relative = null;
              let date2_relative = null;
              let use_relative = false;

              if (value.options.date1 || (!value.options.date1_relative && !value.options.date2_relative)) {
                use_relative = false;

                if (value.options.date1) {
                  date1 = new Date(value.options.date1 * 1000);
                }
                if (value.options.date2) {
                  date2 = new Date(value.options.date2 * 1000);
                }
              } else {
                use_relative = true;
                if (value.options.date1_relative) {
                  date1_relative = [value.options.date1_relative];
                  date1_relative[1] = value.options.date1_relative_type || 'days';
                }
                if (value.options.date2_relative) {
                  date2_relative = [value.options.date2_relative];
                  date2_relative[1] = value.options.date2_relative_type || 'days';
                }
              }

              return {
                op:             value.op || _.first(operators),
                use_relative,
                date1:          date1 || null,
                date2:          date2 || null,
                date1_relative: date1_relative || [1, 'days'],
                date2_relative: date2_relative || [1, 'days']
              };
            },
            getValue(model, data) {
              if (model == null) { model = {}; }
              const value = {};
              value.type = type;
              value.op = model.op;
              value.options = {};

              if (!model.use_relative) {
                if (!model.date1) { model.date1 = new Date(); }
                value.options.date1 = parseInt(moment(model.date1).toDate().getTime() / 1000);
                if (model.op === 'between') {
                  if (!model.date2) { model.date2 = new Date(); }
                  value.options.date2 = parseInt(moment(model.date2).toDate().getTime() / 1000);
                }
              } else {
                if (((model.op === 'lte') || (model.op === 'gte') || (model.op === 'between')) && model.date1_relative) {
                  const d1 = model.date1_relative || [1, 'days'];
                  value.options.date1_relative = d1[0];
                  value.options.date1_relative_type = d1[1];
                }
                if ((model.op === 'between') && model.date2_relative) {
                  const d2 = model.date2_relative || [1, 'days'];
                  value.options.date2_relative = d2[0];
                  value.options.date2_relative_type = d2[1];
                }
              }

              if (field) {
                value.options.type_name = field.type_name;
              }

              // compatibility with Custom Ticket Field
              value.options.value = 'date';
              return value;
            }
          };
        }
      };
    }
  }
  return Admin_OptionBuilder_TypesDef_BaseCriteriaTypesDef;
});