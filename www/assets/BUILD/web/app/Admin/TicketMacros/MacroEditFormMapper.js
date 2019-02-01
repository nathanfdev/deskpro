define(function() {
  class Admin_TicketMacros_MacroEditFormMapper {
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
  }

  return Admin_TicketMacros_MacroEditFormMapper;
});
