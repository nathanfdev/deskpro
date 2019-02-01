define([
  'DeskPRO/Util/Util'
], function(
  Util
) {
  class EmailBanEditFormMapper {

    /*
      *
    *
    */

    getFormFromModel(model) {

      const form = {};

      form.banned_email = model.email_ban.banned_email;

      return form;
    }

    /*
      *
      *
    */

    applyFormToModel(model, formModel) {}

      // we use data from backend, so no need in applying of form data to list model

    /*
      *
      *
    */

    getPostDataFromForm(formModel) {

      const postData = {};

      postData.banned_email = formModel.banned_email;

      return postData;
    }
  }
  return EmailBanEditFormMapper;
});