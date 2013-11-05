(function() {
  define(function() {
    var Admin_OptionBuilder_TypesDef_TicketCriteria;
    return Admin_OptionBuilder_TypesDef_TicketCriteria = (function() {
      function Admin_OptionBuilder_TypesDef_TicketCriteria($q, Api, dpTemplateManager) {
        this.$q = $q;
        this.Api = Api;
        this.dpTemplateManager = dpTemplateManager;
        this.options_data = null;
        this.init();
      }

      Admin_OptionBuilder_TypesDef_TicketCriteria.prototype.init = function() {};

      Admin_OptionBuilder_TypesDef_TicketCriteria.prototype.getDef = function(type, options) {
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
              return me.dpTemplateManager.get('OptionBuilder/type-criteria-input.html');
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

      Admin_OptionBuilder_TypesDef_TicketCriteria.prototype.getStandardSelect = function(options) {
        var data_name, form_type, me, operators, options_formatter, prop_name, type;
        type = options.type;
        prop_name = options.propName;
        data_name = options.dataName;
        form_type = options.formType || 'select';
        operators = options.operators || ['is', 'not'];
        options_formatter = options.optionsFormatter || null;
        if (!options_formatter) {
          options_formatter = function(options) {
            var opt, opts, title, val, _i, _len;
            opts = [];
            for (_i = 0, _len = options.length; _i < _len; _i++) {
              opt = options[_i];
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
            switch (form_type) {
              case 'input':
                return me.dpTemplateManager.get('OptionBuilder/type-criteria-input.html');
              default:
                return me.dpTemplateManager.get('OptionBuilder/type-criteria-select.html');
            }
          },
          getData: function() {
            var defer,
              _this = this;
            if (data_name) {
              defer = me.$q.defer();
              me.loadDataOptions().then(function() {
                return defer.resolve({
                  operators: operators,
                  options: options_formatter ? options_formatter(me.options_data[data_name]) : me.options_data[data_name],
                  multiselect: true
                });
              });
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

      Admin_OptionBuilder_TypesDef_TicketCriteria.prototype.getStandardIs = function(options) {
        var me, prop_name, type;
        type = options.type;
        prop_name = options.propName;
        me = this;
        return {
          getTemplate: function() {
            return me.dpTemplateManager.get('OptionBuilder/type-criteria-is.html');
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

      Admin_OptionBuilder_TypesDef_TicketCriteria.prototype.getStandardInput = function(options) {
        var me, operators, prop_name, type;
        type = options.type;
        prop_name = options.propName;
        operators = options.operators || ['is', 'not'];
        me = this;
        return {
          getTemplate: function() {
            return me.dpTemplateManager.get('OptionBuilder/type-criteria-input.html');
          },
          getData: function() {
            return {
              operators: operators
            };
          },
          getDataFormatter: function() {
            return {
              getViewValue: function(value, data) {
                var _ref;
                if (value == null) {
                  value = {};
                }
                return {
                  value: ((_ref = value.options) != null ? _ref[prop_name] : void 0) || '',
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

      return Admin_OptionBuilder_TypesDef_TicketCriteria;

    })();
  });

}).call(this);

/*
//@ sourceMappingURL=BaseTypesDef.js.map
*/