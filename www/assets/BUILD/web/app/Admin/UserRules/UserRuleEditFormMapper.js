define([
  'DeskPRO/Util/Util'
], (
  Util
) => {
  class UserRuleEditFormMapper {
    getFormFromModel(model) {
      let title;
      const form = {};

      form.id = model.user_rule.id;
      form.email_patterns = model.user_rule.email_patterns;
      form.usergroups = [];
      for (const id of Object.keys(model.all_usergroups || {})) {
        title = model.all_usergroups[id];
        form.usergroups.push({ id, title });
      }

      if (model.user_rule.usergroup) {
        form.usergroup = {};
        form.usergroup.id = model.user_rule.usergroup.id;
        form.usergroup.title = model.user_rule.usergroup.title;
      }

      return form;
    }

    applyFormToModel(model, formModel) {
      return model.email_patterns = formModel.email_patterns;
    }

    getPostDataFromForm(formModel) {
      const postData = {};
      postData.id = formModel.id;
      postData.email_patterns = formModel.email_patterns;
      postData.add_usergroup = formModel.usergroup.id;
      return postData;
    }
  }

  return UserRuleEditFormMapper;
});
