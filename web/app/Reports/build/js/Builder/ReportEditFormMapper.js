(function() {
  define(['DeskPRO/Util/Util'], function(Util) {
    var ReportEditFormMapper;
    return ReportEditFormMapper = (function() {
      function ReportEditFormMapper() {}

      /*
      			#
       		#
      */


      ReportEditFormMapper.prototype.getFormFromModel = function(model) {
        var form;
        form = {};
        form.id = model.id;
        return form;
      };

      /*
      			#
      			#
      */


      ReportEditFormMapper.prototype.applyFormToModel = function(model, formModel) {
        return model.note = formModel.note;
      };

      /*
      			#
      			#
      */


      ReportEditFormMapper.prototype.getPostDataFromForm = function(formModel) {
        var postData;
        postData = {};
        postData.id = formModel.id;
        return postData;
      };

      return ReportEditFormMapper;

    })();
  });

}).call(this);

/*
//@ sourceMappingURL=ReportEditFormMapper.js.map
*/