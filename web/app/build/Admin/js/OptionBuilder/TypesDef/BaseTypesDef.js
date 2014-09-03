(function() {
  define(['DeskPRO/Util/Util', 'DeskPRO/Util/Arrays'], function(Util, Arrays) {
    var Admin_OptionBuilder_TypesDef_BaseTypesDef;
    return Admin_OptionBuilder_TypesDef_BaseTypesDef = (function() {
      function Admin_OptionBuilder_TypesDef_BaseTypesDef() {}

      Admin_OptionBuilder_TypesDef_BaseTypesDef.prototype.standardOptionsFormatter = function(options, extraOptions) {
        var addTree, getRenderOpt, opt, opts, _i, _len;
        getRenderOpt = function(opt, parentTitleSegs) {
          var pTitle, title, val;
          if (parentTitleSegs == null) {
            parentTitleSegs = [];
          }
          pTitle = parentTitleSegs.join(" > ");
          if (opt.title) {
            title = opt.title;
          } else if (opt.display_name) {
            title = opt.display_name;
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
          if (pTitle.length) {
            title = pTitle + " > " + title;
          }
          if (title !== null && val !== null) {
            return {
              title: title,
              value: val
            };
          } else {
            return null;
          }
        };
        addTree = function(options, parent_id, toOpts, parentTitleSegs) {
          var childOps, o, opt, _i, _len, _results;
          if (parentTitleSegs == null) {
            parentTitleSegs = [];
          }
          parent_id = parseInt(parent_id);
          if (options) {
            _results = [];
            for (_i = 0, _len = options.length; _i < _len; _i++) {
              opt = options[_i];
              if ((parent_id !== 0 && parseInt(opt.parent_id) === parent_id) || (parent_id === 0 && (!opt.parent_id || !parseInt(opt.parent_id)))) {
                o = getRenderOpt(opt, parentTitleSegs);
                if (o) {
                  parentTitleSegs.push(o.title);
                }
                childOps = [];
                addTree(options, opt.id, childOps, parentTitleSegs);
                if (o) {
                  parentTitleSegs.pop();
                }
                if (childOps.length) {
                  _results.push(Arrays.append(toOpts, childOps));
                } else {
                  if (o) {
                    _results.push(toOpts.push(o));
                  } else {
                    _results.push(void 0);
                  }
                }
              } else {
                _results.push(void 0);
              }
            }
            return _results;
          }
        };
        opts = [];
        if (extraOptions) {
          for (_i = 0, _len = extraOptions.length; _i < _len; _i++) {
            opt = extraOptions[_i];
            opts.push(opt);
          }
        }
        addTree(options, 0, opts, []);
        return opts;
      };

      Admin_OptionBuilder_TypesDef_BaseTypesDef.prototype.getVars = function() {
        return this.vars || {};
      };

      Admin_OptionBuilder_TypesDef_BaseTypesDef.prototype.setVar = function(k, v) {
        if (!this.vars) {
          this.vars = {};
        }
        return this.vars[k] = v;
      };


      /*
      		 * Constructs autocomplete with remote data
       */

      Admin_OptionBuilder_TypesDef_BaseTypesDef.prototype.getRemoteInput = function(options) {
        var me, operators, prop_name, type;
        type = options.type;
        prop_name = options.propName;
        operators = this.getOperators(options);
        me = this;
        return {
          getTemplate: function() {
            return me.dpTemplateManager.get(this.remoteTemplate);
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
                var inputOptions, _ref;
                if (value == null) {
                  value = {};
                }
                if (value.op) {
                  if (value.op === 'is' && operators.indexOf('is') === -1) {
                    value.op = 'contains';
                  } else if (value.op === 'not' && operators.indexOf('not') === -1) {
                    value.op = 'notcontains';
                  }
                }
                inputOptions = {
                  dropdownAutoWidth: true,
                  minimumInputLength: 1,
                  initSelection: function(item) {
                    return item.id;
                  },
                  ajax: {
                    data: function(term, page) {
                      return {
                        query: term
                      };
                    },
                    quietMillis: 200,
                    transport: function(query) {
                      return me.Api.sendGet(options.url, query.data).then(query.success);
                    },
                    results: function(data, page) {
                      return {
                        results: data.data
                      };
                    }
                  }
                };
                $.extend(true, inputOptions, options.inputOptions || {});
                return {
                  value: ((_ref = value.options) != null ? _ref[prop_name] : void 0) || '',
                  op: value.op || _.first(data.operators),
                  inputOptions: inputOptions
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
                value.options[prop_name] = model.value || '';
                return value;
              }
            };
          }
        };
      };

      return Admin_OptionBuilder_TypesDef_BaseTypesDef;

    })();
  });

}).call(this);

//# sourceMappingURL=BaseTypesDef.js.map
