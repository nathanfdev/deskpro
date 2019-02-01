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
  class ReportEditFormMapper {

    /*
      *
    *
    */
    getFormFromModel(model) {

      const form = {};
      form.id = model.report.id;
      form.title = model.report.title;
      form.description = model.report.description;

      return form;
    }


    /*
      *
      *
    */
    applyFormToModel(model, formModel) {

      return model.title = formModel.title;
    }


    /*
      *
      *
    */
    getPostDataFromForm(formModel) {

      const postData = {};
      postData.id = formModel.id;
      postData.title = formModel.title;
      postData.description = formModel.description;

      return postData;
    }
  }
  return ReportEditFormMapper;
});