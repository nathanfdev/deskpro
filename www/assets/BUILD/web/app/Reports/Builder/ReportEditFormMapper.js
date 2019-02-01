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