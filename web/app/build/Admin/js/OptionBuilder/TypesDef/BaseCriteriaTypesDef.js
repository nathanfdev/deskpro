(function() {
  define(['DeskPRO/Util/Util'], function(Util) {
    var Admin_OptionBuilder_TypesDef_BaseCriteriaTypesDef;
    return Admin_OptionBuilder_TypesDef_BaseCriteriaTypesDef = (function() {
      function Admin_OptionBuilder_TypesDef_BaseCriteriaTypesDef($q, Api, dpTemplateManager) {
        this.$q = $q;
        this.Api = Api;
        this.dpTemplateManager = dpTemplateManager;
        this.options_data = null;
        this.inputTemplate = 'OptionBuilder/type-criteria-input.html';
        this.selectTemplate = 'OptionBuilder/type-criteria-select.html';
        this.isTemplate = 'OptionBuilder/type-criteria-is.html';
        this.init();
      }

      Admin_OptionBuilder_TypesDef_BaseCriteriaTypesDef.prototype.init = function() {};


      /*
        	 * Gets a type definition by calling a getX method on this class
       */

      Admin_OptionBuilder_TypesDef_BaseCriteriaTypesDef.prototype.getDef = function(type, options) {
        var me, typeFunc, typeName;
        if (options == null) {
          options = {};
        }
        typeName = type;
        options.type = type;
        typeFunc = "get" + typeName;
        if (this[typeFunc] != null) {
          return this[typeFunc](options);
        } else {
          console.error("Bad type with no definition getter: " + typeFunc);
          me = this;
          return {
            getTemplate: function() {
              return me.dpTemplateManager.get(me.inputTemplate);
            },
            getData: function() {
              return {};
            },
            getDataFormatter: function() {
              return {
                getViewValue: function(value, data) {
                  if (value == null) {
                    value = {};
                  }
                  return {};
                },
                getValue: function(model, data) {
                  if (model == null) {
                    model = {};
                  }
                  return null;
                }
              };
            }
          };
        }
      };


      /*
        	 * @param {Object} options
        	 * @return {Array}
       */

      Admin_OptionBuilder_TypesDef_BaseCriteriaTypesDef.prototype.getOperators = function(options) {
        return options.operators || ['is', 'not'];
      };


      /*
        	 * Constructs standard input from a custom field def
       */

      Admin_OptionBuilder_TypesDef_BaseCriteriaTypesDef.prototype.getStandardForFieldDef = function(field, options) {
        if (options == null) {
          options = {};
        }
        if (!options.propName) {
          options.propName = 'value';
        }
        if (field.type_name === 'choice') {
          options.options = field.choices.map(function(o) {
            return {
              title: o.title,
              value: o.id
            };
          });
          return this.getStandardSelect(options);
        } else {
          if (!options.operators) {
            options.operators = ['is', 'not', 'contains', 'not_contains', 'is_regex', 'not_regex'];
          }
          return this.getStandardInput(options);
        }
      };


      /*
        	 * Sets the getter for a custom field
        	 *
        	 * @param {String} base_name The base name of the field type (e.g., TicketField, UserField etc)
        	 * @param {Object} f         The field
        	 * @retrn {String} The name of the field that was set
       */

      Admin_OptionBuilder_TypesDef_BaseCriteriaTypesDef.prototype.initFieldGetter = function(base_name, f) {
        var fname;
        fname = base_name + f.id;
        if (!this['get' + fname]) {
          this['get' + fname] = (function(_this) {
            return function(options) {
              if (options == null) {
                options = {};
              }
              options.type = base_name + f.id;
              return _this.getStandardForFieldDef(f, options);
            };
          })(this);
        }
        return fname;
      };


      /*
        	 * Constructs a standard select box type
       */

      Admin_OptionBuilder_TypesDef_BaseCriteriaTypesDef.prototype.getStandardSelect = function(options) {
        var data_name, extraOptions, form_type, me, operators, options_formatter, prop_name, type;
        type = options.type;
        prop_name = options.propName;
        data_name = options.dataName;
        form_type = options.formType || 'select';
        operators = this.getOperators(options);
        options_formatter = options.optionsFormatter || null;
        extraOptions = options.extraOptions || null;
        if (!options_formatter) {
          options_formatter = function(options) {
            var opt, opts, title, val, _i, _j, _len, _len1;
            opts = [];
            if (extraOptions) {
              for (_i = 0, _len = extraOptions.length; _i < _len; _i++) {
                opt = extraOptions[_i];
                opts.push(opt);
              }
            }
            for (_j = 0, _len1 = options.length; _j < _len1; _j++) {
              opt = options[_j];
              if (opt.title) {
                title = opt.title;
              } else if (opt.name) {
                title = opt.name;
              } else {
                title = null;
              }
              if (opt.id) {
                val = opt.id;
              } else if (opt.value) {
                val = opt.value;
              } else {
                val = null;
              }
              if (title !== null && val !== null) {
                opts.push({
                  title: title,
                  value: val
                });
              }
            }
            return opts;
          };
        }
        me = this;
        return {
          getTemplate: function() {
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
          getData: function() {
            var defer;
            if (options.options) {
              return {
                operators: operators,
                options: options_formatter ? options_formatter(options.options) : options.options,
                multiselect: true
              };
            } else if (data_name) {
              defer = me.$q.defer();
              me.loadDataOptions().then((function(_this) {
                return function() {
                  return defer.resolve({
                    operators: operators,
                    options: options_formatter ? options_formatter(me.options_data[data_name]) : me.options_data[data_name],
                    multiselect: true
                  });
                };
              })(this));
              return defer.promise;
            } else {
              return {
                operators: operators
              };
            }
          },
          getDataFormatter: function() {
            return {
              getViewValue: function(value, data) {
                var _ref;
                if (value == null) {
                  value = {};
                }
                return {
                  value: ((_ref = value.options) != null ? _ref[prop_name] : void 0) || null,
                  op: value.op || _.first(data.operators)
                };
              },
              getValue: function(model, data) {
                var value;
                if (model == null) {
                  model = {};
                }
                value = {};
                value.type = type;
                value.op = model.op;
                value.options = {};
                value.options[prop_name] = model.value;
                return value;
              }
            };
          }
        };
      };


      /*
        	 * Constructs a standard "is" template (no options, just a boolean is)
       */

      Admin_OptionBuilder_TypesDef_BaseCriteriaTypesDef.prototype.getStandardIs = function(options) {
        var me, prop_name, type;
        type = options.type;
        prop_name = options.propName;
        me = this;
        return {
          getTemplate: function() {
            return me.dpTemplateManager.get(me.isTemplate);
          },
          getData: function() {
            return {};
          },
          getDataFormatter: function() {
            return {
              getViewValue: function(value, data) {
                if (value == null) {
                  value = {};
                }
                return {
                  value: true,
                  op: 'is'
                };
              },
              getValue: function(model, data) {
                var value;
                if (model == null) {
                  model = {};
                }
                value = {};
                value.type = type;
                value.op = 'is';
                value.options = {};
                value.options[prop_name] = true;
                return value;
              }
            };
          }
        };
      };


      /*
        	 * Constructs a standard input box
       */

      Admin_OptionBuilder_TypesDef_BaseCriteriaTypesDef.prototype.getStandardInput = function(options) {
        var me, operators, prop_name, type;
        type = options.type;
        prop_name = options.propName;
        operators = this.getOperators(options);
        me = this;
        return {
          getTemplate: function() {
            return me.dpTemplateManager.get(me.inputTemplate);
          },
          getData: function() {
            return {
              operators: operators,
              options: options
            };
          },
          getDataFormatter: function() {
            return {
              getViewValue: function(value, data) {
                var val, _ref;
                if (value == null) {
                  value = {};
                }
                val = ((_ref = value.options) != null ? _ref[prop_name] : void 0) || '';
                if (Util.isArray(val)) {
                  val = val.join(',');
                }
                return {
                  value: val,
                  op: value.op || _.first(data.operators)
                };
              },
              getValue: function(model, data) {
                var val, value;
                if (model == null) {
                  model = {};
                }
                val = model.value || '';
                if (options.tags) {
                  val = val.split(',');
                }
                value = {};
                value.type = type;
                value.op = model.op;
                value.options = {};
                value.options[prop_name] = val;
                return value;
              }
            };
          }
        };
      };

      return Admin_OptionBuilder_TypesDef_BaseCriteriaTypesDef;

    })();
  });

}).call(this);

//# sourceMappingURL=BaseCriteriaTypesDef.js.map
