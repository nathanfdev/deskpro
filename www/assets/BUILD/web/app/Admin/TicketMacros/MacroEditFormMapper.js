// TODO: This file was created by bulk-decaffeinate.
// Sanity-check the conversion and remove this comment.
/*
 * decaffeinate suggestions:
 * DS102: Remove unnecessary code created because of implicit returns
 * DS207: Consider shorter variations of null checks
 * Full docs: https://github.com/decaffeinate/decaffeinate/blob/master/docs/suggestions.md
 */
define(function() {
  let Admin_TicketMacros_MacroEditFormMapper;
  return (Admin_TicketMacros_MacroEditFormMapper = class Admin_TicketMacros_MacroEditFormMapper {
    getFormFromModel(macroModel) {
      const form = {};
      form.title = macroModel.title || '';
      form.is_global = macroModel.is_global;
      form.person_id = null;
      form.department_id = null;
      form.actions   = (macroModel.actions != null ? macroModel.actions.actions : undefined) || {};

      if (macroModel.person) {
        form.person_id = macroModel.person.id + "";
      }

      if (macroModel.department) {
        form.department_id = macroModel.department.id + "";
      }

      return form;
    }

    applyFormToModel(macroModel, formModel) {
      return macroModel.title = formModel.title;
    }

    getPostDataFromForm(formModel) {
      const postData = {
        title: formModel.title,
        is_global: formModel.is_global,
        person: formModel.person_id,
        department: formModel.department_id
      };

//      postData.actions = []
//      for own id, row of formModel.actions
//        postData.actions.push(row)

      return postData;
    }
  });
});
