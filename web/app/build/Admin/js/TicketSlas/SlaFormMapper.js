(function() {
  var __hasProp = {}.hasOwnProperty;

  define(['DeskPRO/Util/Util'], function(Util) {
    var SlaFormMapper;
    return SlaFormMapper = (function() {
      function SlaFormMapper() {}


      /*
        	 * Converts a model we get from the API into a form model that we can use in our page
        	 *
        	 * @param {Object} model
        	 * @return {Object}
       */

      SlaFormMapper.prototype.getFormFromModel = function(model) {
        var action, day, days, form, rowId, setId, term, termSet, _i, _j, _k, _l, _len, _len1, _len2, _len3, _len4, _m, _ref, _ref1, _ref10, _ref2, _ref3, _ref4, _ref5, _ref6, _ref7, _ref8, _ref9;
        form = {};
        form.title = model.title || '';
        form.sla_type = model.sla_type || 'first_response';
        form.active_time = model.active_time || 'all';
        form.apply_type = model.apply_type || 'all';
        form.warn_time = [30, 'minutes'];
        form.fail_time = [60, 'minutes'];
        form.hours_set = {};
        form.warn_actions = {};
        form.fail_actions = {};
        form.apply_terms = {};
        if (model.active_time === 'custom') {
          days = [false, false, false, false, false, false];
          _ref = model.work_days;
          for (_i = 0, _len = _ref.length; _i < _len; _i++) {
            day = _ref[_i];
            days[day] = true;
          }
          form.hours_set = {
            start_hour: Math.floor(model.work_start / 3600),
            start_min: Math.floor((model.work_start % 3600) / 60),
            end_hour: Math.floor(model.work_end / 3600),
            end_min: Math.floor((model.work_end % 3600) / 60),
            work_days: days,
            holidays: model.work_holidays,
            timezone: model.work_timezone
          };
        }
        if (model.warn_time && model.warn_time_unit) {
          form.warn_time = [model.warn_time, model.warn_time_unit];
        }
        if (model.fail_time && model.fail_time_unit) {
          form.fail_time = [model.fail_time, model.fail_time_unit];
        }
        if ((_ref1 = model.warn_actions) != null ? (_ref2 = _ref1.actions) != null ? _ref2.length : void 0 : void 0) {
          _ref3 = model.warn_actions.actions;
          for (_j = 0, _len1 = _ref3.length; _j < _len1; _j++) {
            action = _ref3[_j];
            rowId = Util.uid('action');
            form.warn_actions[rowId] = action;
          }
        }
        if ((_ref4 = model.fail_actions) != null ? (_ref5 = _ref4.actions) != null ? _ref5.length : void 0 : void 0) {
          _ref6 = model.fail_actions.actions;
          for (_k = 0, _len2 = _ref6.length; _k < _len2; _k++) {
            action = _ref6[_k];
            rowId = Util.uid('action');
            form.fail_actions[rowId] = action;
          }
        }
        if ((_ref7 = model.apply_terms) != null ? (_ref8 = _ref7.terms) != null ? _ref8.length : void 0 : void 0) {
          _ref9 = model.apply_terms.terms;
          for (_l = 0, _len3 = _ref9.length; _l < _len3; _l++) {
            termSet = _ref9[_l];
            if (!termSet.set_terms || !termSet.set_terms.length) {
              continue;
            }
            setId = Util.uid('termset');
            form.apply_terms[setId] = {};
            _ref10 = termSet.set_terms;
            for (_m = 0, _len4 = _ref10.length; _m < _len4; _m++) {
              term = _ref10[_m];
              rowId = Util.uid('term');
              form.apply_terms[setId][rowId] = term;
            }
          }
        }
        return form;
      };


      /*
        	 * Converts the form model into a model we can post back to the API
        	 * Essentially the reverse of getFormFromModel
        	 *
        	 * @param {Object} form
        	 * @return {Object}
       */

      SlaFormMapper.prototype.getPostDataFromFormModel = function(form) {
        var act, crit, crit_set, day, enabled, postData, set, work_days, _, _i, _len, _ref, _ref1, _ref2, _ref3;
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
        if (form.active_type === 'custom') {
          postData.hours_set = form.hours_set;
        }
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
        if (form.active_time === 'custom') {
          work_days = [];
          _ref3 = form.hours_set.work_days;
          for (day = _i = 0, _len = _ref3.length; _i < _len; day = ++_i) {
            enabled = _ref3[day];
            if (enabled) {
              work_days.push(day);
            }
          }
          postData.work_start = (form.hours_set.start_hour * 3600) + (form.hours_set.start_min * 60);
          postData.work_end = (form.hours_set.end_hour * 3600) + (form.hours_set.end_min * 60);
          postData.holidays = form.hours_set.holidays;
          postData.work_days = work_days;
          postData.work_timezone = form.hours_set.timezone;
        }
        return postData;
      };

      return SlaFormMapper;

    })();
  });

}).call(this);

//# sourceMappingURL=SlaFormMapper.js.map
