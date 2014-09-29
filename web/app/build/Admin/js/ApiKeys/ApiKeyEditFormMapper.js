(function() {
  define(['angular'], function(angular) {
    var ApiKeyEditFormMapper;
    return ApiKeyEditFormMapper = (function() {
      function ApiKeyEditFormMapper() {}


      /*
      		 *
       		 *
       */

      ApiKeyEditFormMapper.prototype.getFormFromModel = function(model) {
        var form;
        form = angular.copy(model.api_key);
        if (form.flags == null) {
          form.flags = ['super'];
        }
        if (form.all_agents == null) {
          form.all_agents = model.all_agents;
        }
        form.isSuperUser = form.flags.length;
        return form;
      };


      /*
      		 *
      		 *
       */

      ApiKeyEditFormMapper.prototype.applyFormToModel = function(model, formModel) {
        delete formModel.person;
        return angular.extend(model, formModel);
      };


      /*
      		 *
      		 *
       */

      ApiKeyEditFormMapper.prototype.getPostDataFromForm = function(formModel) {
        var postData;
        postData = {};
        postData.id = formModel.id;
        postData.note = formModel.note;
        postData.flags = [];
        if (formModel.person) {
          postData.person = formModel.person.id;
        }
        if (formModel.isSuperUser) {
          postData.flags.push('super');
        }
        return postData;
      };

      return ApiKeyEditFormMapper;

    })();
  });

}).call(this);

//# sourceMappingURL=ApiKeyEditFormMapper.js.map
