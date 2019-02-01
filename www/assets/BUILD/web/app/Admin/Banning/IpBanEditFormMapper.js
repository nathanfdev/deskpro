define([
  'DeskPRO/Util/Util'
], function(
  Util
) {
  class IpBanEditFormMapper {

    /*
      *
    *
    */

    getFormFromModel(model) {

      const form = {};

      form.banned_ip = model.ip_ban.banned_ip;

      return form;
    }

    /*
      *
      *
    */

    applyFormToModel(model, formModel) {
      return formModel = model;
    }

    /*
      *
      *
    */

    getPostDataFromForm(formModel) {

      const postData = {};

      postData.banned_ip = formModel.banned_ip;

      return postData;
    }
  }
  return IpBanEditFormMapper;
});