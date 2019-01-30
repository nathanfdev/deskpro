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
  'DeskPRO/Util/Util',
  'underscore'
], function(
  Util,
  _
) {
  let Admin_TicketEscalations_EscalationEditFormMapper;
  return (Admin_TicketEscalations_EscalationEditFormMapper = class Admin_TicketEscalations_EscalationEditFormMapper {
    getFormFromModel(escModel) {
      let rowId, term;
      const form = {};
      form.title              = escModel.title || '';
      form.event_trigger      = escModel.event_trigger || 'time.open';
      form.event_trigger_time = escModel.event_trigger_time || 3600;
      form.actions            = (escModel.actions != null ? escModel.actions.actions : undefined) || {};

      form.terms = {};
      form.terms_any = {};
      form.actions = {};

      if (__guard__(escModel.terms != null ? escModel.terms.terms : undefined, x => x.length)) {
        for (term of Array.from(escModel.terms.terms)) {
          rowId = _.uniqueId('term');
          form.terms[rowId] = term;
        }
      }

      if (__guard__(escModel.terms_any != null ? escModel.terms_any.terms : undefined, x1 => x1.length)) {
        for (term of Array.from(escModel.terms_any.terms)) {
          rowId = _.uniqueId('term_any');
          form.terms_any[rowId] = term;
        }
      }

      if (__guard__(escModel.actions != null ? escModel.actions.actions : undefined, x2 => x2.length)) {
        for (let action of Array.from(escModel.actions.actions)) {
          rowId = _.uniqueId('action');
          form.actions[rowId] = action;
        }
      }

      return form;
    }

    applyFormToModel(escModel, formModel) {
      return escModel.title = formModel.title;
    }

    getPostDataFromForm(formModel) {
      let row;
      const postData = {};
      postData.title = formModel.title;
      postData.event_trigger = formModel.event_trigger;
      postData.event_trigger_time = formModel.event_trigger_time;

      postData.actions = [];
      for (var id of Object.keys(formModel.actions || {})) {
        row = formModel.actions[id];
        postData.actions.push(row);
      }

      postData.terms = [];
      for (id of Object.keys(formModel.terms || {})) {
        row = formModel.terms[id];
        postData.terms.push(row);
      }

      postData.terms_any = [];
      for (id of Object.keys(formModel.terms_any || {})) {
        row = formModel.terms_any[id];
        postData.terms_any.push(row);
      }

      return postData;
    }
  });
});
function __guard__(value, transform) {
  return (typeof value !== 'undefined' && value !== null) ? transform(value) : undefined;
}