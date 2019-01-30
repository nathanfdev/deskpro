/*
 * decaffeinate suggestions:
 * DS101: Remove unnecessary use of Array.from
 * DS102: Remove unnecessary code created because of implicit returns
 * DS205: Consider reworking code to avoid use of IIFEs
 * DS207: Consider shorter variations of null checks
 * Full docs: https://github.com/decaffeinate/decaffeinate/blob/master/docs/suggestions.md
 */
define([
  'DeskPRO/Util/Util',
  'DeskPRO/Util/Arrays',
  'underscore'
], function(
  Util,
  Arrays,
  _
) {
  let Admin_OptionBuilder_TypesDef_BaseTypesDef;
  return (Admin_OptionBuilder_TypesDef_BaseTypesDef = class Admin_OptionBuilder_TypesDef_BaseTypesDef {
    standardOptionsFormatter(options, extraOptions) {
      const getRenderOpt = function(opt, parentTitleSegs) {
        let title, val;
        if (parentTitleSegs == null) { parentTitleSegs = []; }
        const pTitle = parentTitleSegs.join(" > ");

        if (opt.title) {
          ({ title } = opt);
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

        if ((title !== null) && (val !== null)) {
          return {
          title,
          value: val
          };
        } else {
          return null;
        }
      };

      var addTree = function(options, parent_id, toOpts, parentTitleSegs) {
        if (parentTitleSegs == null) { parentTitleSegs = []; }
        parent_id = parseInt(parent_id);
        if (options) {
          return (() => {
            const result = [];
            for (let opt of Array.from(options)) {
              if (((parent_id != null) && (parseInt(opt.parent_id) === parent_id)) || ((parent_id === 0) && (!opt.parent_id || !parseInt(opt.parent_id)))) {
                const o = getRenderOpt(opt, parentTitleSegs);

                if (o) { parentTitleSegs.push(o.title); }

                const childOps = [];
                let recursive_parent_id = opt.id;
                // Prevent checkbox from recursive infinite loop
                if (!recursive_parent_id && parseInt(opt.value)) {
                  recursive_parent_id = opt.value;
                }
                addTree(options, recursive_parent_id, childOps, parentTitleSegs);

                if (o) { parentTitleSegs.pop(); }

                if (childOps.length) {
                  result.push(Arrays.append(toOpts, childOps));
                } else {
                  if (o) { result.push(toOpts.push(o)); } else {
                    result.push(undefined);
                  }
                }
              } else {
                result.push(undefined);
              }
            }
            return result;
          })();
        }
      };

      const opts = [];

      if (extraOptions) {
        for (let opt of Array.from(extraOptions)) {
          opts.push(opt);
        }
      }

      addTree(options, 0, opts, []);

      return opts;
    }

    getVars() { return this.vars || {}; }
    setVar(k, v) {
      if (!this.vars) { this.vars = {}; }
      return this.vars[k] = v;
    }

    /*
     * Constructs autocomplete with remote data
     */
    getRemoteInput(options) {
      let result;
      const { type }      = options;
      const prop_name = options.propName;
      const operators = this.getOperators(options);
      const me = this;

      return result  = {
        getTemplate() { return me.dpTemplateManager.get(me.remoteTemplate); },
        getData() { return { operators, options }; },
        getDataFormatter() {
          return {
            getViewValue(value, data) {
              if (value == null) { value = {}; }
              if (value.op) {
                if ((value.op === 'is') && (operators.indexOf('is') === -1)) {
                  value.op = 'contains';
                } else if ((value.op === 'not') && (operators.indexOf('not') === -1)) {
                  value.op = 'notcontains';
                }
              }

              const inputOptions = {
                dropdownAutoWidth: true,
                formatInputTooShort(input, min) {
                  return `Please enter ${min} or more characters`;
                },
                minimumInputLength: 1,
                multiple: options.isMulti,
                initSelection(item) { return item; },
                ajax: {
                  data(term, page) { return { query: term }; },
                  quietMillis: 200,
                  transport(query) { return me.Api.sendGet(options.url, query.data).then(query.success); },
                  results(data, page) { return { results: data.data }; }
                }
              };

              $.extend(true, inputOptions, options.inputOptions || {});
              value.options = value.options || {};

              const ret = {
                valueString: value.options[prop_name] || '',
                op: value.op || _.first(data.operators),
                inputOptions
              };

              if (options.isMulti || ((value.options[prop_name] != null ? value.options[prop_name].map : undefined) != null)) {
                ret.value = [];
                ((value.options[prop_name] != null ? value.options[prop_name].map : undefined) != null) && (value.options[prop_name] != null ? value.options[prop_name].map(function(id) {
                  if (options.hardcodedSkipLoadById) {
                    return ret.value.push(options.map(id));
                  } else {
                    return me.Api.sendGet(options.url + '/' + id).then(function(res) {
                      if (!options.map) { return ret.value.push(res.data); }
                      return ret.value.push(options.map(res.data));
                    });
                  }
                }) : undefined);
              } else {
                if (!value.options[prop_name]) { return ret; }
                if (options.hardcodedSkipLoadById) {
                  ret.value = options.map(value.options[prop_name]);
                } else {
                  me.Api.sendGet(options.url + '/' + value.options[prop_name]).then(function(res) {
                    if (!options.map) { return res.data; }
                    return ret.value = options.map(res.data);
                  });
                }
              }
              return ret;
            },

            getValue(model, data) {
              if (model == null) { model = {}; }
              const value = {};
              value.type = type;
              value.op = model.op;
              value.options = {};

              if ((model.op === 'is') || (model.op === 'not')) {
                if (options.isMulti || ((model.value != null ? model.value.map : undefined) != null)) {
                  value.options[prop_name] = model.value != null ? model.value.map(item => item[prop_name]) : undefined;
                } else {
                  value.options[prop_name] = (model.value != null ? model.value[prop_name] : undefined) || '';
                }
              } else {
                value.options[prop_name] = model.valueString;
                value.options.info = model.valueString;
              }
              return value;
            }
          };
        }
      };
    }
  });
});

