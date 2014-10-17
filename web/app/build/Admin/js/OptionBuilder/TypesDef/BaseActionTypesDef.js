(function() {
  var __hasProp = {}.hasOwnProperty,
    __extends = function(child, parent) { for (var key in parent) { if (__hasProp.call(parent, key)) child[key] = parent[key]; } function ctor() { this.constructor = child; } ctor.prototype = parent.prototype; child.prototype = new ctor(); child.__super__ = parent.prototype; return child; };

  define(['DeskPRO/Util/Util', 'DeskPRO/Util/Arrays', 'Admin/OptionBuilder/TypesDef/BaseTypesDef'], function(Util, Arrays, BaseTypesDef) {
    var Admin_OptionBuilder_TypesDef_BaseActionTypesDef;
    return Admin_OptionBuilder_TypesDef_BaseActionTypesDef = (function(_super) {
      __extends(Admin_OptionBuilder_TypesDef_BaseActionTypesDef, _super);

      function Admin_OptionBuilder_TypesDef_BaseActionTypesDef($q, Api, dpTemplateManager) {
        this.$q = $q;
        this.Api = Api;
        this.dpTemplateManager = dpTemplateManager;
        this.options_data = null;
        this.inputTemplate = 'OptionBuilder/type-actions-input.html';
        this.selectTemplate = 'OptionBuilder/type-actions-select.html';
        this.isTemplate = 'OptionBuilder/type-actions-is.html';
        this.init();
      }

      Admin_OptionBuilder_TypesDef_BaseActionTypesDef.prototype.init = function() {};


      /*
        	 * Gets a type definition by calling a getX method on this class
       */

      Admin_OptionBuilder_TypesDef_BaseActionTypesDef.prototype.getDef = function(type, options) {
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
        	 * Constructs a standard select box type
       */

      Admin_OptionBuilder_TypesDef_BaseActionTypesDef.prototype.getStandardSelect = function(options) {
        var data_name, extraOptions, is_multi, me, options_formatter, prop_name, type;
        type = options.type;
        prop_name = options.propName;
        data_name = options.dataName;
        options_formatter = options.optionsFormatter || null;
        is_multi = options.isMulti;
        extraOptions = options.extraOptions || null;
        if (!options_formatter) {
          options_formatter = (function(_this) {
            return function(options) {
              return _this.standardOptionsFormatter(options, extraOptions);
            };
          })(this);
        }
        me = this;
        return {
          getTemplate: function() {
            return me.dpTemplateManager.get(options.template || me.selectTemplate);
          },
          getData: function() {
            var defer;
            if (options.options) {
              return {
                options: options_formatter ? options_formatter(options.options) : options.options,
                multiselect: is_multi
              };
            }
            if (data_name) {
              defer = me.$q.defer();
              me.loadDataOptions().then((function(_this) {
                return function() {
                  return defer.resolve({
                    options: options_formatter ? options_formatter(me.options_data[data_name]) : me.options_data[data_name],
                    multiselect: is_multi
                  });
                };
              })(this));
              return defer.promise;
            } else {
              return {};
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
                  value: val
                };
              },
              getValue: function(model, data) {
                var value;
                if (model == null) {
                  model = {};
                }
                value = {};
                value.type = type;
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

      Admin_OptionBuilder_TypesDef_BaseActionTypesDef.prototype.getStandardIs = function(options) {
        var icon, me, prop_name, type;
        type = options.type;
        prop_name = options.propName;
        icon = options.icon;
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
                  op: 'is',
                  icon: icon || false
                };
              },
              getValue: function(model, data) {
                var value;
                if (model == null) {
                  model = {};
                }
                value = {};
                value.type = type;
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

      Admin_OptionBuilder_TypesDef_BaseActionTypesDef.prototype.getStandardInput = function(options) {
        var me, prop_name, type;
        type = options.type;
        prop_name = options.propName;
        me = this;
        return {
          getTemplate: function() {
            return me.dpTemplateManager.get(me.inputTemplate);
          },
          getData: function() {
            return {
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
                value.options = {};
                value.options[prop_name] = val;
                return value;
              }
            };
          }
        };
      };


      /*
        	 * Constructs standard input from a custom field def
       */

      Admin_OptionBuilder_TypesDef_BaseActionTypesDef.prototype.getStandardForFieldDef = function(field, options) {
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
              value: o.id + ""
            };
          });
          return this.getStandardSelect(options);
        } else if (field.type_name === 'toggle') {
          options.options = [
            {
              title: 'On',
              value: "1"
            }, {
              title: "Off",
              value: "0"
            }
          ];
          return this.getStandardSelect(options);
        } else {
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

      Admin_OptionBuilder_TypesDef_BaseActionTypesDef.prototype.initFieldGetter = function(base_name, f) {
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

      return Admin_OptionBuilder_TypesDef_BaseActionTypesDef;

    })(BaseTypesDef);
  });

}).call(this);

//# sourceMappingURL=BaseActionTypesDef.js.map
