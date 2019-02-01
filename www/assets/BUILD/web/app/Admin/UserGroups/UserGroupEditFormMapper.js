define([
  'DeskPRO/Util/Util'
], (
  Util
) => {
  class UserGroupEditFormMapper {
    getFormFromModel(model) {
      const form = {};

      form.id = model.group.id;
      form.title = model.group.title;
      form.note = model.group.note;
      form.is_enabled = model.group.is_enabled;

      return form;
    }

    applyFormToModel(model, formModel) {
      return model.title = formModel.title;
    }

    getPostDataFromForm(formModel, formPermsModel) {
      const postData = {};
      postData.title = formModel.title;
      postData.note = formModel.note;
      postData.is_enabled = formModel.is_enabled;
      postData.dep_perms = Util.clone(formModel.deps_perms);

      if (formPermsModel) {
        postData.perms = Util.clone(formPermsModel);
        if (postData.perms.options.reopen_resolved_createnew === 'new_ticket') {
          postData.perms.ticket.reopen_resolved_createnew = true;
        } else {
          postData.perms.ticket.reopen_resolved_createnew = false;
        }

        delete postData.perms.options;
      }

      return postData;
    }
  }
  return UserGroupEditFormMapper;
});
