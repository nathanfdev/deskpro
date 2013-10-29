(function() {
  define(function() {
    var Admin_OptionBuilder_TypesDef_TicketCriteria;
    return Admin_OptionBuilder_TypesDef_TicketCriteria = (function() {
      function Admin_OptionBuilder_TypesDef_TicketCriteria($q, Api, dpTemplateManager) {
        this.$q = $q;
        this.Api = Api;
        this.dpTemplateManager = dpTemplateManager;
        this.options_data = null;
      }

      Admin_OptionBuilder_TypesDef_TicketCriteria.prototype.getOptionsForTypes = function(types, typesData) {
        var options, set_options;
        if (typesData == null) {
          typesData = null;
        }
        set_options = [];
        if (types.indexOf('email') !== -1) {
          options = [];
          options.push({
            title: 'Email Account',
            value: 'email_account'
          });
          options.push({
            title: 'Email Subject',
            value: 'email_subject'
          });
          options.push({
            title: 'Email Body',
            value: 'email_body'
          });
          options.push({
            title: 'To Name',
            value: 'email_to_name'
          });
          options.push({
            title: 'To Address',
            value: 'email_to_address'
          });
          options.push({
            title: 'From Name',
            value: 'email_from_name'
          });
          options.push({
            title: 'From Address',
            value: 'email_from_address'
          });
          options.push({
            title: 'CCd Name',
            value: 'email_cc_name'
          });
          options.push({
            title: 'CCd Address',
            value: 'email_cc_address'
          });
          options.push({
            title: 'Email Header',
            value: 'email_header_match'
          });
          set_options.push({
            title: 'Email Criteria',
            subOptions: options
          });
        }
        options = [];
        options.push({
          title: 'Department',
          value: 'department'
        });
        options.push({
          title: 'Product',
          value: 'product'
        });
        options.push({
          title: 'Category',
          value: 'category'
        });
        options.push({
          title: 'Priority',
          value: 'priority'
        });
        if (types.indexOf('web.agent') !== -1) {
          options.push({
            title: 'Workflow',
            value: 'workflow'
          });
        }
        options.push({
          title: 'Title',
          value: 'title'
        });
        options.push({
          title: 'Message',
          value: 'message'
        });
        set_options.push({
          title: 'Ticket Criteria',
          subOptions: options
        });
        options = [];
        options.push({
          title: 'Has attachment',
          value: 'with_attach'
        });
        options.push({
          title: 'Has attachment type',
          value: 'with_attach_type'
        });
        options.push({
          title: 'Has attachment named',
          value: 'with_attach_name'
        });
        set_options.push({
          title: 'Attachment Criteria',
          subOptions: options
        });
        options = [];
        options.push({
          title: 'Name',
          value: 'person_name'
        });
        options.push({
          title: 'Email Address',
          value: 'person_email'
        });
        options.push({
          title: 'Label',
          value: 'person_label'
        });
        options.push({
          title: 'Usergroup',
          value: 'person_usergroup'
        });
        options.push({
          title: 'Language',
          value: 'person_language'
        });
        options.push({
          title: 'Is manager of organization',
          value: 'person_is_manager'
        });
        options.push({
          title: 'Is disabled',
          value: 'person_is_disabled'
        });
        set_options.push({
          title: 'User Criteria',
          subOptions: options
        });
        options = [];
        options.push({
          title: 'Name',
          value: 'org_name'
        });
        options.push({
          title: 'Label',
          value: 'org_label'
        });
        options.push({
          title: 'Email Domain',
          value: 'org_email_domain'
        });
        options.push({
          title: 'Linked Usergroup',
          value: 'org_usergroup'
        });
        set_options.push({
          title: 'Organization Criteria',
          subOptions: options
        });
        options = [];
        options.push({
          title: 'Day of week',
          value: 'day_of_week'
        });
        options.push({
          title: 'Time of day',
          value: 'time_of_day'
        });
        options.push({
          title: 'Within working hours',
          value: 'within_working_hours'
        });
        set_options.push({
          title: 'Dates',
          subOptions: options
        });
        return set_options;
      };

      Admin_OptionBuilder_TypesDef_TicketCriteria.prototype.loadDataOptions = function() {
        var p,
          _this = this;
        if (this.options_data) {
          p = this.$q.fcall(function() {
            return _this.options_data;
          });
        } else {
          this.options_data = {};
          p = this.Api.sendDataGet({
            'ticket_deps': '/ticket_deps',
            'ticket_cats': '/ticket_cats',
            'ticket_prods': '/ticket_prods',
            'ticket_pris': '/ticket_pris',
            'ticket_works': '/ticket_works',
            'ticket_accounts': '/ticket_accounts',
            'usergroups': '/usergroups'
          }).then(function(result) {
            var data, _ref;
            data = result.data;
            _this.options_data['ticket_deps'] = data.ticket_deps.departments;
            _this.options_data['ticket_cats'] = data.ticket_cats.categories;
            _this.options_data['ticket_pris'] = data.ticket_pris.priorities;
            _this.options_data['ticket_works'] = data.ticket_works.workflows;
            _this.options_data['ticket_prods'] = (_ref = data.ticket_prods) != null ? _ref.products : void 0;
            _this.options_data['ticket_accounts'] = data.ticket_accounts.ticket_accounts;
            return _this.options_data['usergroups'] = data.usergroups.usergroups;
          });
        }
        return p;
      };

      Admin_OptionBuilder_TypesDef_TicketCriteria.prototype.getDef = function(type, options) {
        var me, typeFunc, typeName;
        if (options == null) {
          options = {};
        }
        typeName = type.toLowerCase().replace(/_(.)/g, function(match, group1) {
          return group1.toUpperCase();
        });
        typeName = typeName.charAt(0).toUpperCase() + typeName.slice(1);
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

      Admin_OptionBuilder_TypesDef_TicketCriteria.prototype.getWorkflow = function(options) {
        var def;
        if (options == null) {
          options = {};
        }
        options.propName = 'workflow_ids';
        options.dataName = 'ticket_works';
        def = this.getStandardSelect(options);
        return def;
      };

      Admin_OptionBuilder_TypesDef_TicketCriteria.prototype.getPriority = function(options) {
        var def;
        if (options == null) {
          options = {};
        }
        options.propName = 'priority_ids';
        options.dataName = 'ticket_pris';
        def = this.getStandardSelect(options);
        return def;
      };

      Admin_OptionBuilder_TypesDef_TicketCriteria.prototype.getCategory = function(options) {
        var def;
        if (options == null) {
          options = {};
        }
        options.propName = 'category_ids';
        options.dataName = 'ticket_cats';
        def = this.getStandardSelect(options);
        return def;
      };

      Admin_OptionBuilder_TypesDef_TicketCriteria.prototype.getDepartment = function(options) {
        var def;
        if (options == null) {
          options = {};
        }
        options.propName = 'department_ids';
        options.dataName = 'ticket_deps';
        def = this.getStandardSelect(options);
        return def;
      };

      Admin_OptionBuilder_TypesDef_TicketCriteria.prototype.getProduct = function(options) {
        var def;
        if (options == null) {
          options = {};
        }
        options.propName = 'product_ids';
        options.dataName = 'ticket_prods';
        def = this.getStandardSelect(options);
        return def;
      };

      Admin_OptionBuilder_TypesDef_TicketCriteria.prototype.getEmailAccount = function(options) {
        var def;
        if (options == null) {
          options = {};
        }
        options.propName = 'gateway_ids';
        options.dataName = 'ticket_accounts';
        options.optionsFormatter = function(options) {
          var acc, opts, _i, _len;
          opts = [];
          for (_i = 0, _len = options.length; _i < _len; _i++) {
            acc = options[_i];
            opts.push({
              value: acc.id,
              title: acc.email_address
            });
          }
          return opts;
        };
        def = this.getStandardSelect(options);
        return def;
      };

      Admin_OptionBuilder_TypesDef_TicketCriteria.prototype.getEmailSubject = function(options) {
        var def;
        if (options == null) {
          options = {};
        }
        options.propName = 'subject';
        options.operators = ['is', 'not', 'contains', 'not_contains', 'is_regex', 'not_regex'];
        def = this.getStandardInput(options);
        return def;
      };

      Admin_OptionBuilder_TypesDef_TicketCriteria.prototype.getEmailBody = function(options) {
        var def;
        if (options == null) {
          options = {};
        }
        options.propName = 'body';
        options.operators = ['is', 'not', 'contains', 'not_contains', 'is_regex', 'not_regex'];
        def = this.getStandardInput(options);
        return def;
      };

      Admin_OptionBuilder_TypesDef_TicketCriteria.prototype.getEmailToName = function(options) {
        var def;
        if (options == null) {
          options = {};
        }
        options.propName = 'to_name';
        options.operators = ['is', 'not', 'contains', 'not_contains', 'is_regex', 'not_regex'];
        def = this.getStandardInput(options);
        return def;
      };

      Admin_OptionBuilder_TypesDef_TicketCriteria.prototype.getEmailToAddress = function(options) {
        var def;
        if (options == null) {
          options = {};
        }
        options.propName = 'to_address';
        options.operators = ['is', 'not', 'contains', 'not_contains', 'is_regex', 'not_regex'];
        def = this.getStandardInput(options);
        return def;
      };

      Admin_OptionBuilder_TypesDef_TicketCriteria.prototype.getEmailFromName = function(options) {
        var def;
        if (options == null) {
          options = {};
        }
        options.propName = 'from_name';
        options.operators = ['is', 'not', 'contains', 'not_contains', 'is_regex', 'not_regex'];
        def = this.getStandardInput(options);
        return def;
      };

      Admin_OptionBuilder_TypesDef_TicketCriteria.prototype.getEmailFromAddress = function(options) {
        var def;
        if (options == null) {
          options = {};
        }
        options.propName = 'from_address';
        options.operators = ['is', 'not', 'contains', 'not_contains', 'is_regex', 'not_regex'];
        def = this.getStandardInput(options);
        return def;
      };

      Admin_OptionBuilder_TypesDef_TicketCriteria.prototype.getCcAddress = function(options) {
        var def;
        if (options == null) {
          options = {};
        }
        options.propName = 'cc_address';
        options.operators = ['is', 'not', 'contains', 'not_contains', 'is_regex', 'not_regex'];
        def = this.getStandardInput(options);
        return def;
      };

      Admin_OptionBuilder_TypesDef_TicketCriteria.prototype.getCcName = function(options) {
        var def;
        if (options == null) {
          options = {};
        }
        options.propName = 'cc_name';
        options.operators = ['is', 'not', 'contains', 'not_contains', 'is_regex', 'not_regex'];
        def = this.getStandardInput(options);
        return def;
      };

      Admin_OptionBuilder_TypesDef_TicketCriteria.prototype.getEmailHeaderMatch = function(options) {
        var def;
        if (options == null) {
          options = {};
        }
        options.propName = 'email_header_match';
        options.operators = ['is', 'not', 'contains', 'not_contains', 'is_regex', 'not_regex'];
        def = this.getStandardInput(options);
        return def;
      };

      Admin_OptionBuilder_TypesDef_TicketCriteria.prototype.getTitle = function(options) {
        var def;
        if (options == null) {
          options = {};
        }
        options.propName = 'title';
        options.operators = ['is', 'not', 'contains', 'not_contains', 'is_regex', 'not_regex'];
        def = this.getStandardInput(options);
        return def;
      };

      Admin_OptionBuilder_TypesDef_TicketCriteria.prototype.getMessage = function(options) {
        var def;
        if (options == null) {
          options = {};
        }
        options.propName = 'message';
        options.operators = ['is', 'not', 'contains', 'not_contains', 'is_regex', 'not_regex'];
        def = this.getStandardInput(options);
        return def;
      };

      Admin_OptionBuilder_TypesDef_TicketCriteria.prototype.getWithAttach = function(options) {
        var def;
        if (options == null) {
          options = {};
        }
        options.propName = 'with_attach';
        def = this.getStandardIs(options);
        return def;
      };

      Admin_OptionBuilder_TypesDef_TicketCriteria.prototype.getWithAttachType = function(options) {
        var def;
        if (options == null) {
          options = {};
        }
        options.propName = 'attach_type';
        options.operators = ['is', 'not'];
        def = this.getStandardInput(options);
        return def;
      };

      Admin_OptionBuilder_TypesDef_TicketCriteria.prototype.getWithAttachName = function(options) {
        var def;
        if (options == null) {
          options = {};
        }
        options.propName = 'attach_name';
        options.operators = ['is', 'not', 'contains', 'not_contains', 'is_regex', 'not_regex'];
        def = this.getStandardInput(options);
        return def;
      };

      Admin_OptionBuilder_TypesDef_TicketCriteria.prototype.getPersonName = function(options) {
        var def;
        if (options == null) {
          options = {};
        }
        options.propName = 'name';
        options.operators = ['is', 'not', 'contains', 'not_contains', 'is_regex', 'not_regex'];
        def = this.getStandardInput(options);
        return def;
      };

      Admin_OptionBuilder_TypesDef_TicketCriteria.prototype.getPersonEmail = function(options) {
        var def;
        if (options == null) {
          options = {};
        }
        options.propName = 'email';
        options.operators = ['is', 'not', 'contains', 'not_contains', 'is_regex', 'not_regex'];
        def = this.getStandardInput(options);
        return def;
      };

      Admin_OptionBuilder_TypesDef_TicketCriteria.prototype.getPersonLabel = function(options) {
        var def;
        if (options == null) {
          options = {};
        }
        options.propName = 'labels';
        options.operators = ['contains', 'not_contains'];
        def = this.getStandardInput(options);
        return def;
      };

      Admin_OptionBuilder_TypesDef_TicketCriteria.prototype.getPersonUsergroup = function(options) {
        var def;
        if (options == null) {
          options = {};
        }
        options.propName = 'usergroup_ids';
        options.dataName = 'usergroups';
        def = this.getStandardSelect(options);
        return def;
      };

      Admin_OptionBuilder_TypesDef_TicketCriteria.prototype.getPersonLanguage = function(options) {
        var def;
        if (options == null) {
          options = {};
        }
        options.propName = 'language_ids';
        options.dataName = 'languages';
        def = this.getStandardSelect(options);
        return def;
      };

      Admin_OptionBuilder_TypesDef_TicketCriteria.prototype.getPersonIsManager = function(options) {
        var def;
        if (options == null) {
          options = {};
        }
        options.propName = 'is_manager';
        def = this.getStandardIs(options);
        return def;
      };

      Admin_OptionBuilder_TypesDef_TicketCriteria.prototype.getPersonIsDisabled = function(options) {
        var def;
        if (options == null) {
          options = {};
        }
        options.propName = 'is_disabled';
        def = this.getStandardIs(options);
        return def;
      };

      Admin_OptionBuilder_TypesDef_TicketCriteria.prototype.getOrgName = function(options) {
        var def;
        if (options == null) {
          options = {};
        }
        options.propName = 'name';
        options.operators = ['is', 'not', 'contains', 'not_contains', 'is_regex', 'not_regex'];
        def = this.getStandardInput(options);
        return def;
      };

      Admin_OptionBuilder_TypesDef_TicketCriteria.prototype.getOrgLabel = function(options) {
        var def;
        if (options == null) {
          options = {};
        }
        options.propName = 'labels';
        options.operators = ['contains', 'not_contains'];
        def = this.getStandardInput(options);
        return def;
      };

      Admin_OptionBuilder_TypesDef_TicketCriteria.prototype.getOrgEmailDomain = function(options) {
        var def;
        if (options == null) {
          options = {};
        }
        options.propName = 'name';
        options.operators = ['is', 'not', 'contains', 'not_contains', 'is_regex', 'not_regex'];
        def = this.getStandardInput(options);
        return def;
      };

      Admin_OptionBuilder_TypesDef_TicketCriteria.prototype.getOrgUsergroup = function(options) {
        var def;
        if (options == null) {
          options = {};
        }
        options.propName = 'usergroup_ids';
        options.dataName = 'usergroups';
        options.operators = ['is', 'not'];
        def = this.getStandardSelect(options);
        return def;
      };

      Admin_OptionBuilder_TypesDef_TicketCriteria.prototype.getDayOfWeek = function(options) {
        var def;
        if (options == null) {
          options = {};
        }
        options.propName = 'day_of_week';
        options.operators = ['is', 'not'];
        def = this.getStandardInput(options);
        return def;
      };

      Admin_OptionBuilder_TypesDef_TicketCriteria.prototype.getTimeOfDay = function(options) {
        var def;
        if (options == null) {
          options = {};
        }
        options.propName = 'time_of_week';
        options.operators = ['is', 'not'];
        def = this.getStandardInput(options);
        return def;
      };

      Admin_OptionBuilder_TypesDef_TicketCriteria.prototype.getWithinWorkingHours = function(options) {
        var def;
        if (options == null) {
          options = {};
        }
        options.propName = 'working_hours';
        options.operators = ['is', 'not'];
        def = this.getStandardInput(options);
        return def;
      };

      return Admin_OptionBuilder_TypesDef_TicketCriteria;

    })();
  });

}).call(this);

/*
//@ sourceMappingURL=TicketActions.js.map
*/