// TODO: This file was created by bulk-decaffeinate.
// Sanity-check the conversion and remove this comment.
/*
 * decaffeinate suggestions:
 * DS101: Remove unnecessary use of Array.from
 * DS102: Remove unnecessary code created because of implicit returns
 * DS103: Rewrite code to no longer use __guard__
 * DS203: Remove `|| {}` from converted for-own loops
 * DS207: Consider shorter variations of null checks
 * Full docs: https://github.com/decaffeinate/decaffeinate/blob/master/docs/suggestions.md
 */
define([
  'DeskPRO/Util/Util'
], function(
  Util
) {
  class SlaFormMapper {
    /*
      * Converts a model we get from the API into a form model that we can use in our page
      *
      * @param {Object} model
      * @return {Object}
    */
    getFormFromModel(model) {
      let action, rowId;
      const form = {};
      form.title         = model.title || '';
      form.sla_type      = model.sla_type || 'first_response';
      form.active_time   = model.active_time || 'all';
      form.apply_type    = model.apply_type || 'all';
      form.warn_time     = [30, 'minutes'];
      form.fail_time     = [60, 'minutes'];
      form.hours_set     = {};
      form.warn_actions  = {};
      form.fail_actions  = {};
      form.apply_terms   = {};

      if (model.active_time === 'custom') {
        const days = [null, false, false, false, false, false, false, false];
        for (let day of Array.from(model.work_days)) {
          days[day] = true;
        }

        form.hours_set = {
          start_hour: Math.floor(model.work_start / 3600),
          start_min:  Math.floor((model.work_start % 3600) / 60),
          end_hour:   Math.floor(model.work_end / 3600),
          end_min:    Math.floor((model.work_end % 3600) / 60),
          work_days:  days,
          holidays:   model.work_holidays,
          timezone:   model.work_timezone
        };
      }

      if (model.warn_time && model.warn_time_unit) {
        form.warn_time = [model.warn_time, model.warn_time_unit];
      }

      if (model.fail_time && model.fail_time_unit) {
        form.fail_time = [model.fail_time, model.fail_time_unit];
      }

      if (__guard__(model.warn_actions != null ? model.warn_actions.actions : undefined, x => x.length)) {
        for (action of Array.from(model.warn_actions.actions)) {
          rowId = Util.uid('action');
          form.warn_actions[rowId] = action;
        }
      }

      if (__guard__(model.fail_actions != null ? model.fail_actions.actions : undefined, x1 => x1.length)) {
        for (action of Array.from(model.fail_actions.actions)) {
          rowId = Util.uid('action');
          form.fail_actions[rowId] = action;
        }
      }

      if (__guard__(model.apply_terms != null ? model.apply_terms.terms : undefined, x2 => x2.length)) {
        for (let termSet of Array.from(model.apply_terms.terms)) {
          if (!termSet.set_terms || !termSet.set_terms.length) { continue; }
          const setId = Util.uid('termset');
          form.apply_terms[setId] = {};

          for (let term of Array.from(termSet.set_terms)) {
            rowId = Util.uid('term');
            form.apply_terms[setId][rowId] = term;
          }
        }
      }

      return form;
    }


    /*
      * Converts the form model into a model we can post back to the API
      * Essentially the reverse of getFormFromModel
      *
      * @param {Object} form
      * @return {Object}
    */
    getPostDataFromFormModel(form) {
      let _x, act;
      const postData = {
        title:          form.title,
        sla_type:       form.sla_type,
        active_time:    form.active_time,
        apply_type:     form.apply_type,
        warn_time:      form.warn_time[0],
        warn_time_unit: form.warn_time[1],
        fail_time:      form.fail_time[0],
        fail_time_unit: form.fail_time[1],
        warn_actions:   [],
        fail_actions:   [],
        apply_terms:    []
      };

      if (form.active_type === 'custom') {
        postData.hours_set = form.hours_set;
      }

      if (form.apply_type === 'terms') {
        for (_x of Object.keys(form.apply_terms || {})) {
          const crit_set = form.apply_terms[_x];
          const set = [];
          for (_x of Object.keys(crit_set || {})) {
            const crit = crit_set[_x];
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
        for (_x of Object.keys(form.warn_actions || {})) {
          act = form.warn_actions[_x];
          if (act.type) {
            postData.warn_actions.push(act);
          }
        }
      }

      if (form.fail_actions) {
        for (_x of Object.keys(form.fail_actions || {})) {
          act = form.fail_actions[_x];
          if (act.type) {
            postData.fail_actions.push(act);
          }
        }
      }


      if (form.active_time === 'custom') {
        const work_days = [];
        for (let day = 0; day < form.hours_set.work_days.length; day++) {
          const enabled = form.hours_set.work_days[day];
          if (enabled) {
            work_days.push(day);
          }
        }

        postData.work_start    = (form.hours_set.start_hour * 3600) + (form.hours_set.start_min * 60);
        postData.work_end      = (form.hours_set.end_hour * 3600) + (form.hours_set.end_min * 60);
        postData.holidays      = (form.hours_set.holidays);
        postData.work_days     = work_days;
        postData.work_timezone = form.hours_set.timezone;
      }

      return postData;
    }
  }

  return SlaFormMapper;
});

function __guard__(value, transform) {
  return (typeof value !== 'undefined' && value !== null) ? transform(value) : undefined;
}