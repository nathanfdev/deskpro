(function() {
  define(['DeskPRO/Util/Util'], function(Util) {
    var ReportEditFormMapper;
    return ReportEditFormMapper = (function() {
      function ReportEditFormMapper() {}


      /*
      			 *
       		 *
       */

      ReportEditFormMapper.prototype.getFormFromModel = function(model) {
        var form;
        form = {};
        form.id = model.report.id;
        form.title = model.report.title;
        form.description = model.report.description;
        return form;
      };


      /*
      			 *
      			 *
       */

      ReportEditFormMapper.prototype.applyFormToModel = function(model, formModel) {
        return model.title = formModel.title;
      };


      /*
      			 *
      			 *
       */

      ReportEditFormMapper.prototype.getPostDataFromForm = function(formModel) {
        var postData;
        postData = {};
        postData.id = formModel.id;
        postData.title = formModel.title;
        postData.description = formModel.description;
        return postData;
      };

      return ReportEditFormMapper;

    })();
  });

}).call(this);

//# sourceMappingURL=ReportEditFormMapper.js.map
