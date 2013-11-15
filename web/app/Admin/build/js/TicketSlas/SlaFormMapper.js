(function() {
  define(function() {
    var SlaFormMapper;
    return SlaFormMapper = (function() {
      function SlaFormMapper() {}

      SlaFormMapper.prototype.getFormFromModel = function(model) {
        var action, form, rowId, setId, term, termSet, _i, _j, _k, _l, _len, _len1, _len2, _len3, _ref, _ref1, _ref2, _ref3, _ref4, _ref5, _ref6, _ref7, _ref8, _ref9;
        form = {};
        form.title = model.title || '';
        form.sla_type = model.sla_type || 'first_response';
        form.active_time = model.active_time || 'all';
        form.sla_warn_time = model.sla_warn_time || 1800;
        form.sla_fail_time = model.sla_fail_time || 3600;
        form.apply_type = model.apply_type || 'all';
        form.sla_warn_actions = {};
        form.sla_fail_actions = {};
        form.criteria_sets = {};
        if ((_ref = model.warning_trigger) != null ? (_ref1 = _ref.actions) != null ? _ref1.length : void 0 : void 0) {
          _ref2 = model.warning_trigger.actions;
          for (_i = 0, _len = _ref2.length; _i < _len; _i++) {
            action = _ref2[_i];
            rowId = _.uniqueId('action');
            form.sla_warn_actions[rowId] = action;
          }
        }
        if ((_ref3 = model.fail_trigger) != null ? (_ref4 = _ref3.actions) != null ? _ref4.length : void 0 : void 0) {
          _ref5 = model.fail_trigger.actions;
          for (_j = 0, _len1 = _ref5.length; _j < _len1; _j++) {
            action = _ref5[_j];
            rowId = _.uniqueId('action');
            form.sla_fail_actions[rowId] = action;
          }
        }
        if ((_ref6 = model.apply_trigger) != null ? (_ref7 = _ref6.terms) != null ? _ref7.length : void 0 : void 0) {
          _ref8 = model.apply_trigger.terms;
          for (_k = 0, _len2 = _ref8.length; _k < _len2; _k++) {
            termSet = _ref8[_k];
            if (!termSet.set_terms || !termSet.set_terms.length) {
              continue;
            }
            setId = _.uniqueId('termset');
            form.criteria_sets[setId] = {};
            _ref9 = termSet.set_terms;
            for (_l = 0, _len3 = _ref9.length; _l < _len3; _l++) {
              term = _ref9[_l];
              rowId = _.uniqueId('term');
              form.criteria_sets[setId][rowId] = term;
            }
          }
        }
        return form;
      };

      return SlaFormMapper;

    })();
  });

}).call(this);

/*
//@ sourceMappingURL=SlaFormMapper.js.map
*/