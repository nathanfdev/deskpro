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
        this.dateTemplate = 'OptionBuilder/type-criteria-date.html';
        this.timeElapsedTemplate = 'OptionBuilder/type-criteria-time-elapsed.html';
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
                var val, _ref, _ref1;
                if (value == null) {
                  value = {};
                }
                val = ((_ref = value.options) != null ? _ref[prop_name] : void 0) || null;
                if (val === null && data.options && prop_name) {
                  val = ((_ref1 = data.options[0]) != null ? _ref1.value : void 0) || null;
                }
                return {
                  value: val,
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

      Admin_OptionBuilder_TypesDef_BaseCriteriaTypesDef.prototype.getTimeElapsedInput = function(options) {
        var me, operators, prop_name, type;
        type = options.type;
        operators = options.operators || ['lte', 'gte'];
        prop_name = options.propName;
        me = this;
        return {
          getTemplate: function() {
            return me.dpTemplateManager.get(me.timeElapsedTemplate);
          },
          getData: function() {
            return {
              operators: operators
            };
          },
          getDataFormatter: function() {
            return {
              getViewValue: function(value, data) {
                var val, _ref;
                if (value == null) {
                  value = {};
                }
                val = ((_ref = value.options) != null ? _ref[prop_name] : void 0) || [1, 'days'];
                return {
                  op: value.op || _.first(operators),
                  value: val
                };
              },
              getValue: function(model, data) {
                var val, value;
                if (model == null) {
                  model = {};
                }
                val = model.value || [1, 'days'];
                value = {};
                value.type = type;
                value.op = model.op;
                value.options = {};
                valie.options[prop_name] = val;
                return value;
              }
            };
          }
        };
      };

      Admin_OptionBuilder_TypesDef_BaseCriteriaTypesDef.prototype.getDateInput = function(options) {
        var me, operators, type;
        type = options.type;
        operators = options.operators || ['lte', 'gte', 'between'];
        me = this;
        return {
          getTemplate: function() {
            return me.dpTemplateManager.get(me.dateTemplate);
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
                var date1, date1_relative, date2, date2_relative, use_relative;
                if (value == null) {
                  value = {};
                }
                value.options = value.options || {};
                date1 = null;
                date2 = null;
                date1_relative = null;
                date2_relative = null;
                use_relative = false;
                if (value.options.date1 || value.options.date2 || (!value.options.date1_relative && !value.options.date2_relative)) {
                  use_relative = false;
                  if (value.options.date1) {
                    date1 = new Date(value.options.date1 * 1000);
                  }
                  if (value.options.date2) {
                    date2 = new Date(value.options.date1 * 1000);
                  }
                } else {
                  use_relative = true;
                  if (value.options.date1_relative) {
                    date1_relative = value.options.date1_relative.split(' ');
                  }
                  if (value.options.date2_relative) {
                    date1_relative = value.options.date2_relative.split(' ');
                  }
                }
                return {
                  op: value.op || _.first(operators),
                  use_relative: use_relative,
                  date1: date1 || null,
                  date2: date2 || null,
                  date1_relative: date1_relative || [1, 'days'],
                  date2_relative: date2_relative || [1, 'days']
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
                if (model.use_relative) {
                  if (model.op === 'lte' || model.op === 'between') {
                    if (!model.date1) {
                      model.date1 = new Date();
                    }
                    value.date1 = model.date1.getTime() / 1000;
                  }
                  if ((model.op === 'gte' || model.op === 'between') && model.date2) {
                    if (!model.date2) {
                      model.date2 = new Date();
                    }
                    value.date2 = model.date2.getTime() / 1000;
                  }
                } else {
                  if ((model.op === 'lte' || model.op === 'between') && model.date1_relative) {
                    value.date1_relative = model.date1_relative || [1, 'days'];
                    value.date1_relative = value.date1_relative.join(' ');
                  }
                  if ((model.op === 'lte' || model.op === 'between') && model.date2_relative) {
                    value.date2_relative = model.date2_relative || [1, 'days'];
                    value.date2_relative = value.date2_relative.join(' ');
                  }
                }
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
