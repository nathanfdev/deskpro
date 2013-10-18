(function() {
  define(function() {
    var Admin_OptionBuilder_TypesDef_TicketCriteria;
    return Admin_OptionBuilder_TypesDef_TicketCriteria = (function() {
      function Admin_OptionBuilder_TypesDef_TicketCriteria($q, Api, dpTemplateManager) {
        this.$q = $q;
        this.Api = Api;
        this.dpTemplateManager = dpTemplateManager;
        this.options_data = null;
        this.type_to_data = {
          'department_ids': 'departments'
        };
      }

      Admin_OptionBuilder_TypesDef_TicketCriteria.prototype.loadOptions = function() {
        var p,
          _this = this;
        if (this.options_data) {
          p = this.$q.fcall(function() {
            return _this.options_data;
          });
        } else {
          this.options_data = {};
          p = this.Api.sendDataGet(['/ticket_deps']).then(function(result) {
            var data;
            data = result.data;
            return _this.options_data['departments'] = data.api_ticket_deps.departments;
          });
        }
        return p;
      };

      Admin_OptionBuilder_TypesDef_TicketCriteria.prototype.getDef = function(type, options) {
        var typeFunc, typeName;
        typeName = type.toLowerCase().replace(/_(.)/g, function(match, group1) {
          return group1.toUpperCase();
        });
        typeName = typeName.charAt(0).toUpperCase() + typeName.slice(1);
        typeFunc = "get" + typeName;
        return this[typeFunc](options);
      };

      Admin_OptionBuilder_TypesDef_TicketCriteria.prototype.getStandardSelect = function(options) {
        var form_type, me, operators, prop_name;
        prop_name = options.propName;
        form_type = options.formType || 'select';
        operators = options.operators || ['is', 'not'];
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
            if (me.type_to_data[prop_name]) {
              defer = me.$q.defer();
              me.loadOptions().then(function() {
                var opt_name;
                opt_name = me.type_to_data[prop_name];
                return defer.resolve({
                  operators: operators,
                  options: me.options_data[opt_name],
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
                if (value == null) {
                  value = {};
                }
                return {
                  value: value[prop_name],
                  op: value.op || _.first(data.operators)
                };
              },
              getValue: function(model, data) {
                var value;
                if (model == null) {
                  model = {};
                }
                value = {};
                value[prop_name] = model.value;
                value.op = model.op;
                return value;
              }
            };
          }
        };
      };

      Admin_OptionBuilder_TypesDef_TicketCriteria.prototype.getWorkflow = function(options) {
        var def;
        if (options == null) {
          options = {};
        }
        options.propName = 'workflow_ids';
        def = this.getStandardSelect(options);
        return def;
      };

      Admin_OptionBuilder_TypesDef_TicketCriteria.prototype.getPriority = function(options) {
        var def;
        if (options == null) {
          options = {};
        }
        options.propName = 'priority_ids';
        def = this.getStandardSelect(options);
        return def;
      };

      Admin_OptionBuilder_TypesDef_TicketCriteria.prototype.getCategory = function(options) {
        var def;
        if (options == null) {
          options = {};
        }
        options.propName = 'category_ids';
        def = this.getStandardSelect(options);
        return def;
      };

      Admin_OptionBuilder_TypesDef_TicketCriteria.prototype.getDepartment = function(options) {
        var def;
        if (options == null) {
          options = {};
        }
        options.propName = 'department_ids';
        def = this.getStandardSelect(options);
        return def;
      };

      Admin_OptionBuilder_TypesDef_TicketCriteria.prototype.getProduct = function(options) {
        var def;
        if (options == null) {
          options = {};
        }
        options.propName = 'product_ids';
        def = this.getStandardSelect(options);
        return def;
      };

      return Admin_OptionBuilder_TypesDef_TicketCriteria;

    })();
  });

}).call(this);

/*
//@ sourceMappingURL=TicketCriteria.js.map
*/