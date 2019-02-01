// TODO: This file was created by bulk-decaffeinate.
// Sanity-check the conversion and remove this comment.
/*
 * decaffeinate suggestions:
 * DS101: Remove unnecessary use of Array.from
 * DS102: Remove unnecessary code created because of implicit returns
 * DS103: Rewrite code to no longer use __guard__
 * DS207: Consider shorter variations of null checks
 * Full docs: https://github.com/decaffeinate/decaffeinate/blob/master/docs/suggestions.md
 */
define([
  'underscore'
], function(
  _
) {
  class Admin_TicketTriggers_TriggerEditFormMapper {
    getFormFromModel(model, forceModeMapping) {
      let rowId;
      if (forceModeMapping == null) { forceModeMapping = false; }
      const form = {};
      form.title = model.title || '';

      if (model.id || (forceModeMapping === true)) {
        let x;
        form.typeForm = {
          by_user: false,
          by_agent: false,
          by_app: false,
          by_agent_mode: {
            web: false,
            email: false,
            api: false
          },
          by_user_mode: {
            portal: false,
            widget: false,
            form: false,
            email: false,
            api: false
          },
          by_app_mode: {}
        };

        if (model.by_agent_mode.length) {
          form.typeForm.by_agent = true;
          for (x of Array.from(model.by_agent_mode)) {
            form.typeForm.by_agent_mode[x] = true;
          }
        }
        if (model.by_user_mode.length) {
          form.typeForm.by_user = true;
          for (x of Array.from(model.by_user_mode)) {
            form.typeForm.by_user_mode[x] = true;
          }
        }
        if (model.by_app_mode.length) {
          form.typeForm.by_app = true;
          for (x of Array.from(model.by_app_mode)) {
            form.typeForm.by_app_mode[x] = true;
          }
        }
      } else {
        form.typeForm = {
          by_user: true,
          by_agent: true,
          by_app: false,
          by_agent_mode: {
            web: true,
            email: true,
            api: true
          },
          by_user_mode: {
            portal: true,
            widget: true,
            form: true,
            email: true,
            api: true
          },
          by_app_mode: {}
        };
      }

      form.flags = {};
      if (__guard__(model != null ? model.event_flags : undefined, x1 => x1.indexOf('run_newreply')) !== -1) {
        form.flags.run_newreply = true;
      } else {
        form.flags.run_newreply = false;
      }

      form.terms_set = {};
      form.actions = {};

      if (__guard__(model.terms != null ? model.terms.terms : undefined, x2 => x2.length)) {
        for (let termSet of Array.from(model.terms.terms)) {
          if (!termSet.set_terms || !termSet.set_terms.length) { continue; }
          const setId = _.uniqueId('termset');
          form.terms_set[setId] = {};

          for (let term of Array.from(termSet.set_terms)) {
            rowId = _.uniqueId('term');
            form.terms_set[setId][rowId] = term;
          }
        }
      }

      if (__guard__(model.actions != null ? model.actions.actions : undefined, x3 => x3.length)) {
        for (let action of Array.from(model.actions.actions)) {
          rowId = _.uniqueId('action');
          form.actions[rowId] = action;
        }
      }

      return form;
    }
  }
  return Admin_TicketTriggers_TriggerEditFormMapper;
});
function __guard__(value, transform) {
  return (typeof value !== 'undefined' && value !== null) ? transform(value) : undefined;
}