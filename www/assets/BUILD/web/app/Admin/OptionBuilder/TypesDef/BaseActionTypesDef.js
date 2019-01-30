/*
 * decaffeinate suggestions:
 * DS001: Remove Babel/TypeScript constructor workaround
 * DS102: Remove unnecessary code created because of implicit returns
 * DS207: Consider shorter variations of null checks
 * Full docs: https://github.com/decaffeinate/decaffeinate/blob/master/docs/suggestions.md
 */
define([
  'underscore',
  'DeskPRO/Util/Util',
  'DeskPRO/Util/Arrays',
  'Admin/OptionBuilder/TypesDef/BaseTypesDef'
], function(_,
  Util,
  Arrays,
  BaseTypesDef) {
  let Admin_OptionBuilder_TypesDef_BaseActionTypesDef;
  return (Admin_OptionBuilder_TypesDef_BaseActionTypesDef = class Admin_OptionBuilder_TypesDef_BaseActionTypesDef extends BaseTypesDef {
    constructor($q, Api, Api2, dpTemplateManager) {
      {
        // Hack: trick Babel/TypeScript into allowing this before super.
        if (false) { super(); }
        let thisFn = (() => { return this; }).toString();
        let thisName = thisFn.slice(thisFn.indexOf('return') + 6 + 1, thisFn.indexOf(';')).trim();
        eval(`${thisName} = this;`);
      }
      this.$q = $q;
      this.Api = Api;
      this.Api2 = Api2;
      this.dpTemplateManager = dpTemplateManager;
      this.options_data   = null;
      this.inputTemplate  = 'OptionBuilder/type-actions-input.html';
      this.selectTemplate = 'OptionBuilder/type-actions-select.html';
      this.isTemplate     = 'OptionBuilder/type-actions-is.html';
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
      * Constructs a standard select box type
    */
    getStandardSelect(options) {
      const { type } = options;
      const prop_name = options.propName;
      const data_name = options.dataName;
      let options_formatter = options.optionsFormatter || null;
      const is_multi = options.isMulti;
      const extraOptions = options.extraOptions || null;

      if (!options_formatter) {
        options_formatter = options => {
          return this.standardOptionsFormatter(options, extraOptions);
        };
      }

      const me = this;

      return {
        getTemplate() {
          return me.dpTemplateManager.get(options.template || me.selectTemplate);
        },

        getData() {
          const operators = options.operators || [];
          if (options.options) {
            return {
              options: options_formatter ? options_formatter(options.options) : options.options,
              multiselect: is_multi,
              operators
            };
          }
          if (data_name) {
            const defer = me.$q.defer();
            me.loadDataOptions().then(() => {
              return defer.resolve({
                options: options_formatter ? options_formatter(me.options_data[data_name]) : me.options_data[data_name],
                multiselect: is_multi,
                operators
              });
            });

            return defer.promise;
          } else {
            return {
              operators
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

              return {
                value: val,
                op: (value.options != null ? value.options.op : undefined) || value.op || _.first(data.operators)
              };
            },
            getValue(model, data) {
              if (model == null) { model = {}; }
              const value = {};
              value.type               = type;
              value.options            = {};
              value.options[prop_name] = model.value;
              value.options.op         = model.op;
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
      const { type } = options;
      const prop_name = options.propName;
      const { icon } = options;

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
                op: 'is',
                icon: icon || false
              };
            },
            getValue(model, data) {
              if (model == null) { model = {}; }
              const value = {};
              value.type               = type;
              value.options            = {};
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
    getStandardInput(options) {
      const { type } = options;
      const prop_name = options.propName;

      const me = this;
      return {
        getTemplate() {
          return me.dpTemplateManager.get(options.template || me.inputTemplate);
        },

        getData() {
          const operators = options.operators || [];
          return {
            options,
            operators
          };
        },

        getDataFormatter() {
          return {
            getViewValue(value, data) {
              if (value == null) { value = {}; }
              let val = (value.options != null ? value.options[prop_name] : undefined) || '';
              if (Util.isArray(val)) { val = val.join(','); }
              return {
                value: val,
                op: (value.options != null ? value.options.op : undefined) || value.op || _.first(data.operators),
                with_formatter: (value.options != null ? value.options.with_formatter : undefined) || false
              };
            },
            getValue(model, data) {
              if (model == null) { model = {}; }
              let val = model.value || '';
              if (options.tags) {
                val = val.split(',');
              }

              const value = {};
              value.type               = type;
              value.options            = {};
              value.options[prop_name] = val;
              value.options.op         = model.op;
              if (options.with_formatter) {
                value.options.with_formatter = !!model.with_formatter;
              }
              return value;
            }
          };
        }
      };
    }


    /*
      * Constructs standard input from a custom field def
    */
    getStandardForFieldDef(field, options) {
      if (options == null) { options = {}; }
      if (!options.propName) { options.propName = 'value'; }

      options.operators = ['set', 'unset'];
      if (field.type_name === 'choice') {
        options.options  = field.choices.map(o => ({ title: o.title, value: o.id + "" }));
        options.template = 'OptionBuilder/type-actions-custom-select.html';
        options.isMulti  = !!field.options.multiple;
        return this.getStandardSelect(options);
      } else if (field.type_name === 'toggle') {
        options.options  = [{ title: 'On', value: "1" }, { title: "Off", value: "0" }];
        options.template = 'OptionBuilder/type-actions-custom-select.html';
        return this.getStandardSelect(options);
      } else {
        options.template = 'OptionBuilder/type-actions-custom-input.html';
        options.with_formatter = true;
        return this.getStandardInput(options);
      }
    }


    /*
      * Sets the getter for a custom field
      *
      * @param {String} base_name The base name of the field type (e.g., TicketField, UserField etc)
      * @param {Object} f         The field
      * @retrn {String} The name of the field that was set
      */
    initFieldGetter(base_name, f, force) {
      const fname = base_name + f.id;

      if (!this[`get${fname}`] || (force != null)) {
        this[`get${fname}`] = options => {
          if (options == null) { options = {}; }
          options.type = base_name + f.id;
          return this.getStandardForFieldDef(f, options);
        };
      }

      return fname;
    }
  });
});
