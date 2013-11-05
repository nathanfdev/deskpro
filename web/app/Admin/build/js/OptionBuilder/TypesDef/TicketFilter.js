(function() {
  var __hasProp = {}.hasOwnProperty,
    __extends = function(child, parent) { for (var key in parent) { if (__hasProp.call(parent, key)) child[key] = parent[key]; } function ctor() { this.constructor = child; } ctor.prototype = parent.prototype; child.prototype = new ctor(); child.__super__ = parent.prototype; return child; };

  define(['Admin/OptionBuilder/TypesDef/BaseCriteriaTypesDef'], function(BaseCriteriaTypesDef) {
    var Admin_OptionBuilder_TypesDef_TicketFilter, _ref;
    return Admin_OptionBuilder_TypesDef_TicketFilter = (function(_super) {
      __extends(Admin_OptionBuilder_TypesDef_TicketFilter, _super);

      function Admin_OptionBuilder_TypesDef_TicketFilter() {
        _ref = Admin_OptionBuilder_TypesDef_TicketFilter.__super__.constructor.apply(this, arguments);
        return _ref;
      }

      Admin_OptionBuilder_TypesDef_TicketFilter.prototype.init = function() {
        return this.options_data = null;
      };

      Admin_OptionBuilder_TypesDef_TicketFilter.prototype.getOptionsForTypes = function(types, typesData) {
        var options, set_options;
        if (typesData == null) {
          typesData = null;
        }
        set_options = [];
        options = [];
        options.push({
          title: 'Department',
          value: 'CheckDepartment'
        });
        options.push({
          title: 'Product',
          value: 'CheckProduct'
        });
        options.push({
          title: 'Category',
          value: 'CheckCategory'
        });
        options.push({
          title: 'Priority',
          value: 'CheckPriority'
        });
        options.push({
          title: 'Workflow',
          value: 'CheckWorkflow'
        });
        options.push({
          title: 'Subject',
          value: 'CheckSubject'
        });
        set_options.push({
          title: 'Ticket Criteria',
          subOptions: options
        });
        options = [];
        options.push({
          title: 'Name',
          value: 'CheckUserName'
        });
        options.push({
          title: 'Email Address',
          value: 'CheckUserEmailAddress'
        });
        options.push({
          title: 'Label',
          value: 'CheckUserLabels'
        });
        options.push({
          title: 'Usergroup',
          value: 'CheckUserUsergroups'
        });
        options.push({
          title: 'Language',
          value: 'CheckUserLanguage'
        });
        options.push({
          title: 'Is manager of organization',
          value: 'CheckUserIsManager'
        });
        options.push({
          title: 'Is disabled',
          value: 'CheckPersonIsDisabled'
        });
        set_options.push({
          title: 'User Criteria',
          subOptions: options
        });
        options = [];
        options.push({
          title: 'Name',
          value: 'CheckOrgName'
        });
        options.push({
          title: 'Label',
          value: 'CheckOrgLabels'
        });
        options.push({
          title: 'Email Domain',
          value: 'CheckOrgEmailDomain'
        });
        options.push({
          title: 'Linked Usergroup',
          value: 'CheckOrgUsergroups'
        });
        set_options.push({
          title: 'Organization Criteria',
          subOptions: options
        });
        return set_options;
      };

      Admin_OptionBuilder_TypesDef_TicketFilter.prototype.loadDataOptions = function() {
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
            var data, _ref1;
            data = result.data;
            _this.options_data['ticket_deps'] = data.ticket_deps.departments;
            _this.options_data['ticket_cats'] = data.ticket_cats.categories;
            _this.options_data['ticket_pris'] = data.ticket_pris.priorities;
            _this.options_data['ticket_works'] = data.ticket_works.workflows;
            _this.options_data['ticket_prods'] = (_ref1 = data.ticket_prods) != null ? _ref1.products : void 0;
            _this.options_data['ticket_accounts'] = data.ticket_accounts.ticket_accounts;
            return _this.options_data['usergroups'] = data.usergroups.usergroups;
          });
        }
        return p;
      };

      Admin_OptionBuilder_TypesDef_TicketFilter.prototype.getCheckWorkflow = function(options) {
        var def;
        if (options == null) {
          options = {};
        }
        options.propName = 'workflow_ids';
        options.dataName = 'ticket_works';
        def = this.getStandardSelect(options);
        return def;
      };

      Admin_OptionBuilder_TypesDef_TicketFilter.prototype.getCheckPriority = function(options) {
        var def;
        if (options == null) {
          options = {};
        }
        options.propName = 'priority_ids';
        options.dataName = 'ticket_pris';
        def = this.getStandardSelect(options);
        return def;
      };

      Admin_OptionBuilder_TypesDef_TicketFilter.prototype.getCheckCategory = function(options) {
        var def;
        if (options == null) {
          options = {};
        }
        options.propName = 'category_ids';
        options.dataName = 'ticket_cats';
        def = this.getStandardSelect(options);
        return def;
      };

      Admin_OptionBuilder_TypesDef_TicketFilter.prototype.getCheckDepartment = function(options) {
        var def;
        if (options == null) {
          options = {};
        }
        options.propName = 'department_ids';
        options.dataName = 'ticket_deps';
        def = this.getStandardSelect(options);
        return def;
      };

      Admin_OptionBuilder_TypesDef_TicketFilter.prototype.getCheckProduct = function(options) {
        var def;
        if (options == null) {
          options = {};
        }
        options.propName = 'product_ids';
        options.dataName = 'ticket_prods';
        def = this.getStandardSelect(options);
        return def;
      };

      Admin_OptionBuilder_TypesDef_TicketFilter.prototype.getCheckEmailAccount = function(options) {
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

      Admin_OptionBuilder_TypesDef_TicketFilter.prototype.getCheckEmailSubject = function(options) {
        var def;
        if (options == null) {
          options = {};
        }
        options.propName = 'subject';
        options.operators = ['is', 'not', 'contains', 'not_contains', 'is_regex', 'not_regex'];
        def = this.getStandardInput(options);
        return def;
      };

      Admin_OptionBuilder_TypesDef_TicketFilter.prototype.getCheckEmailBody = function(options) {
        var def;
        if (options == null) {
          options = {};
        }
        options.propName = 'body';
        options.operators = ['is', 'not', 'contains', 'not_contains', 'is_regex', 'not_regex'];
        def = this.getStandardInput(options);
        return def;
      };

      Admin_OptionBuilder_TypesDef_TicketFilter.prototype.getCheckEmailToName = function(options) {
        var def;
        if (options == null) {
          options = {};
        }
        options.propName = 'to_name';
        options.operators = ['is', 'not', 'contains', 'not_contains', 'is_regex', 'not_regex'];
        def = this.getStandardInput(options);
        return def;
      };

      Admin_OptionBuilder_TypesDef_TicketFilter.prototype.getCheckEmailToAddress = function(options) {
        var def;
        if (options == null) {
          options = {};
        }
        options.propName = 'to_address';
        options.operators = ['is', 'not', 'contains', 'not_contains', 'is_regex', 'not_regex'];
        def = this.getStandardInput(options);
        return def;
      };

      Admin_OptionBuilder_TypesDef_TicketFilter.prototype.getCheckEmailFromName = function(options) {
        var def;
        if (options == null) {
          options = {};
        }
        options.propName = 'from_name';
        options.operators = ['is', 'not', 'contains', 'not_contains', 'is_regex', 'not_regex'];
        def = this.getStandardInput(options);
        return def;
      };

      Admin_OptionBuilder_TypesDef_TicketFilter.prototype.getCheckEmailFromAddress = function(options) {
        var def;
        if (options == null) {
          options = {};
        }
        options.propName = 'from_address';
        options.operators = ['is', 'not', 'contains', 'not_contains', 'is_regex', 'not_regex'];
        def = this.getStandardInput(options);
        return def;
      };

      Admin_OptionBuilder_TypesDef_TicketFilter.prototype.getCheckCcAddress = function(options) {
        var def;
        if (options == null) {
          options = {};
        }
        options.propName = 'cc_address';
        options.operators = ['is', 'not', 'contains', 'not_contains', 'is_regex', 'not_regex'];
        def = this.getStandardInput(options);
        return def;
      };

      Admin_OptionBuilder_TypesDef_TicketFilter.prototype.getCheckCcName = function(options) {
        var def;
        if (options == null) {
          options = {};
        }
        options.propName = 'cc_name';
        options.operators = ['is', 'not', 'contains', 'not_contains', 'is_regex', 'not_regex'];
        def = this.getStandardInput(options);
        return def;
      };

      Admin_OptionBuilder_TypesDef_TicketFilter.prototype.getCheckEmailHeader = function(options) {
        var def;
        if (options == null) {
          options = {};
        }
        options.propName = 'email_header_match';
        options.operators = ['is', 'not', 'contains', 'not_contains', 'is_regex', 'not_regex'];
        def = this.getStandardInput(options);
        return def;
      };

      Admin_OptionBuilder_TypesDef_TicketFilter.prototype.getCheckSubject = function(options) {
        var def;
        if (options == null) {
          options = {};
        }
        options.propName = 'subject';
        options.operators = ['is', 'not', 'contains', 'not_contains', 'is_regex', 'not_regex'];
        def = this.getStandardInput(options);
        return def;
      };

      Admin_OptionBuilder_TypesDef_TicketFilter.prototype.getCheckMessage = function(options) {
        var def;
        if (options == null) {
          options = {};
        }
        options.propName = 'message';
        options.operators = ['is', 'not', 'contains', 'not_contains', 'is_regex', 'not_regex'];
        def = this.getStandardInput(options);
        return def;
      };

      Admin_OptionBuilder_TypesDef_TicketFilter.prototype.getCheckHasAttach = function(options) {
        var def;
        if (options == null) {
          options = {};
        }
        options.propName = 'with_attach';
        def = this.getStandardIs(options);
        return def;
      };

      Admin_OptionBuilder_TypesDef_TicketFilter.prototype.getCheckHasAttachType = function(options) {
        var def;
        if (options == null) {
          options = {};
        }
        options.propName = 'attach_type';
        options.operators = ['is', 'not'];
        def = this.getStandardInput(options);
        return def;
      };

      Admin_OptionBuilder_TypesDef_TicketFilter.prototype.getCheckHasAttachName = function(options) {
        var def;
        if (options == null) {
          options = {};
        }
        options.propName = 'attach_name';
        options.operators = ['is', 'not', 'contains', 'not_contains', 'is_regex', 'not_regex'];
        def = this.getStandardInput(options);
        return def;
      };

      Admin_OptionBuilder_TypesDef_TicketFilter.prototype.getCheckUserName = function(options) {
        var def;
        if (options == null) {
          options = {};
        }
        options.propName = 'name';
        options.operators = ['is', 'not', 'contains', 'not_contains', 'is_regex', 'not_regex'];
        def = this.getStandardInput(options);
        return def;
      };

      Admin_OptionBuilder_TypesDef_TicketFilter.prototype.getCheckUserEmailAddress = function(options) {
        var def;
        if (options == null) {
          options = {};
        }
        options.propName = 'email';
        options.operators = ['is', 'not', 'contains', 'not_contains', 'is_regex', 'not_regex'];
        def = this.getStandardInput(options);
        return def;
      };

      Admin_OptionBuilder_TypesDef_TicketFilter.prototype.getCheckUserLabels = function(options) {
        var def;
        if (options == null) {
          options = {};
        }
        options.propName = 'labels';
        options.operators = ['contains', 'not_contains'];
        def = this.getStandardInput(options);
        return def;
      };

      Admin_OptionBuilder_TypesDef_TicketFilter.prototype.getCheckUserUsergroups = function(options) {
        var def;
        if (options == null) {
          options = {};
        }
        options.propName = 'usergroup_ids';
        options.dataName = 'usergroups';
        def = this.getStandardSelect(options);
        return def;
      };

      Admin_OptionBuilder_TypesDef_TicketFilter.prototype.getCheckUserLanguage = function(options) {
        var def;
        if (options == null) {
          options = {};
        }
        options.propName = 'language_ids';
        options.dataName = 'languages';
        def = this.getStandardSelect(options);
        return def;
      };

      Admin_OptionBuilder_TypesDef_TicketFilter.prototype.getCheckUserIsManager = function(options) {
        var def;
        if (options == null) {
          options = {};
        }
        options.propName = 'is_manager';
        def = this.getStandardIs(options);
        return def;
      };

      Admin_OptionBuilder_TypesDef_TicketFilter.prototype.getCheckPersonIsDisabled = function(options) {
        var def;
        if (options == null) {
          options = {};
        }
        options.propName = 'is_disabled';
        def = this.getStandardIs(options);
        return def;
      };

      Admin_OptionBuilder_TypesDef_TicketFilter.prototype.getCheckOrgName = function(options) {
        var def;
        if (options == null) {
          options = {};
        }
        options.propName = 'name';
        options.operators = ['is', 'not', 'contains', 'not_contains', 'is_regex', 'not_regex'];
        def = this.getStandardInput(options);
        return def;
      };

      Admin_OptionBuilder_TypesDef_TicketFilter.prototype.getCheckOrgLabels = function(options) {
        var def;
        if (options == null) {
          options = {};
        }
        options.propName = 'labels';
        options.operators = ['contains', 'not_contains'];
        def = this.getStandardInput(options);
        return def;
      };

      Admin_OptionBuilder_TypesDef_TicketFilter.prototype.getCheckOrgEmailDomain = function(options) {
        var def;
        if (options == null) {
          options = {};
        }
        options.propName = 'name';
        options.operators = ['is', 'not', 'contains', 'not_contains', 'is_regex', 'not_regex'];
        def = this.getStandardInput(options);
        return def;
      };

      Admin_OptionBuilder_TypesDef_TicketFilter.prototype.getCheckOrgUsergroups = function(options) {
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

      Admin_OptionBuilder_TypesDef_TicketFilter.prototype.getCheckDayOfWeek = function(options) {
        var me;
        if (options == null) {
          options = {};
        }
        me = this;
        return {
          getTemplate: function() {
            return me.dpTemplateManager.get('OptionBuilder/type-criteria-dayofweek.html');
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
                  op: value.op || 'is'
                };
              },
              getValue: function(model, data) {
                var value;
                if (model == null) {
                  model = {};
                }
                value = {};
                return value;
              }
            };
          }
        };
      };

      Admin_OptionBuilder_TypesDef_TicketFilter.prototype.getCheckTimeOfDay = function(options) {
        var me;
        if (options == null) {
          options = {};
        }
        me = this;
        return {
          getTemplate: function() {
            return me.dpTemplateManager.get('OptionBuilder/type-criteria-timeofday.html');
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
                  op: value.op || 'is'
                };
              },
              getValue: function(model, data) {
                var value;
                if (model == null) {
                  model = {};
                }
                value = {};
                return value;
              }
            };
          }
        };
      };

      Admin_OptionBuilder_TypesDef_TicketFilter.prototype.getCheckWorkingHours = function(options) {
        var me;
        if (options == null) {
          options = {};
        }
        me = this;
        return {
          getTemplate: function() {
            return me.dpTemplateManager.get('OptionBuilder/type-criteria-workinghours.html');
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
                  op: value.op || 'is'
                };
              },
              getValue: function(model, data) {
                var value;
                if (model == null) {
                  model = {};
                }
                value = {};
                return value;
              }
            };
          }
        };
      };

      return Admin_OptionBuilder_TypesDef_TicketFilter;

    })(BaseCriteriaTypesDef);
  });

}).call(this);

/*
//@ sourceMappingURL=TicketFilter.js.map
*/