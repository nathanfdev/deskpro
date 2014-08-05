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

      return Admin_OptionBuilder_TypesDef_BaseTypesDef;

    })();
  });

}).call(this);

//# sourceMappingURL=BaseTypesDef.js.map
