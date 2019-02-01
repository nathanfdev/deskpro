// TODO: This file was created by bulk-decaffeinate.
// Sanity-check the conversion and remove this comment.
/*
 * decaffeinate suggestions:
 * DS102: Remove unnecessary code created because of implicit returns
 * Full docs: https://github.com/decaffeinate/decaffeinate/blob/master/docs/suggestions.md
 */
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