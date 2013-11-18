(function() {
  var __hasProp = {}.hasOwnProperty;

  define(function() {
    var SlaFormMapper;
    return SlaFormMapper = (function() {
      function SlaFormMapper() {}

      /*
        	# Converts a model we get from the API into a form model that we can use in our page
        	#
        	# @param {Object} model
        	# @return {Object}
      */


      SlaFormMapper.prototype.getFormFromModel = function(model) {
        var action, form, rowId, setId, term, termSet, _i, _j, _k, _l, _len, _len1, _len2, _len3, _ref, _ref1, _ref2, _ref3, _ref4, _ref5, _ref6, _ref7, _ref8, _ref9;
        form = {};
        form.title = model.title || '';
        form.sla_type = model.sla_type || 'first_response';
        form.active_time = model.active_time || 'all';
        form.apply_type = model.apply_type || 'all';
        form.warn_time = [30, 'minutes'];
        form.fail_time = [60, 'minutes'];
        form.warn_actions = {};
        form.fail_actions = {};
        form.apply_terms = {};
        if (model.warn_time && model.warn_time_unit) {
          form.warn_time = [model.warn_time, model.warn_time_unit];
        }
        if (model.fail_time && model.fail_time_unit) {
          form.fail_time = [model.fail_time, model.fail_time_unit];
        }
        if ((_ref = model.warn_actions) != null ? (_ref1 = _ref.actions) != null ? _ref1.length : void 0 : void 0) {
          _ref2 = model.warn_actions.actions;
          for (_i = 0, _len = _ref2.length; _i < _len; _i++) {
            action = _ref2[_i];
            rowId = _.uniqueId('action');
            form.warn_actions[rowId] = action;
          }
        }
        if ((_ref3 = model.fail_actions) != null ? (_ref4 = _ref3.actions) != null ? _ref4.length : void 0 : void 0) {
          _ref5 = model.fail_actions.actions;
          for (_j = 0, _len1 = _ref5.length; _j < _len1; _j++) {
            action = _ref5[_j];
            rowId = _.uniqueId('action');
            form.fail_actions[rowId] = action;
          }
        }
        if ((_ref6 = model.apply_terms) != null ? (_ref7 = _ref6.terms) != null ? _ref7.length : void 0 : void 0) {
          _ref8 = model.apply_terms.terms;
          for (_k = 0, _len2 = _ref8.length; _k < _len2; _k++) {
            termSet = _ref8[_k];
            if (!termSet.set_terms || !termSet.set_terms.length) {
              continue;
            }
            setId = _.uniqueId('termset');
            form.apply_terms[setId] = {};
            _ref9 = termSet.set_terms;
            for (_l = 0, _len3 = _ref9.length; _l < _len3; _l++) {
              term = _ref9[_l];
              rowId = _.uniqueId('term');
              form.apply_terms[setId][rowId] = term;
            }
          }
        }
        return form;
      };

      /*
        	# Converts the form model into a model we can post back to the API
        	# Essentially the reverse of getFormFromModel
        	#
        	# @param {Object} form
        	# @return {Object}
      */


      SlaFormMapper.prototype.getPostDataFromFormModel = function(form) {
        var act, crit, crit_set, postData, set, _, _ref, _ref1, _ref2;
        postData = {
          title: form.title,
          sla_type: form.sla_type,
          active_time: form.active_time,
          apply_type: form.apply_type,
          warn_time: form.warn_time[0],
          warn_time_unit: form.warn_time[1],
          fail_time: form.fail_time[0],
          fail_time_unit: form.fail_time[1],
          warn_actions: [],
          fail_actions: [],
          apply_terms: []
        };
        if (form.apply_type === 'terms') {
          _ref = form.apply_terms;
          for (_ in _ref) {
            if (!__hasProp.call(_ref, _)) continue;
            crit_set = _ref[_];
            set = [];
            for (_ in crit_set) {
              if (!__hasProp.call(crit_set, _)) continue;
              crit = crit_set[_];
              if (crit.type) {
                set.push(crit);
              }
            }
            if (set.length) {
              postData.apply_terms.push(set);
            }
          }
        }
        if (form.warn_actions) {
          _ref1 = form.warn_actions;
          for (_ in _ref1) {
            if (!__hasProp.call(_ref1, _)) continue;
            act = _ref1[_];
            if (act.type) {
              postData.warn_actions.push(act);
            }
          }
        }
        if (form.fail_actions) {
          _ref2 = form.fail_actions;
          for (_ in _ref2) {
            if (!__hasProp.call(_ref2, _)) continue;
            act = _ref2[_];
            if (act.type) {
              postData.fail_actions.push(act);
            }
          }
        }
        return postData;
      };

      return SlaFormMapper;

    })();
  });

}).call(this);

/*
//@ sourceMappingURL=SlaFormMapper.js.map
*/