(function() {
  define(['DeskPRO/Util/Util'], function(Util) {
    var ApiKeyEditFormMapper;
    return ApiKeyEditFormMapper = (function() {
      function ApiKeyEditFormMapper() {}

      /*
      			#
       		#
      */


      ApiKeyEditFormMapper.prototype.getFormFromModel = function(model) {
        var form;
        form = {};
        form.isSuperUser = true;
        form.id = model.api_key.id;
        form.note = model.api_key.note;
        form.code = model.api_key.code;
        form.keyString = model.api_key.keyString;
        if (model.api_key.user) {
          form.isSuperUser = false;
          form.user = {};
          form.user.id = model.api_key.user.id;
          form.user.name = model.api_key.user.name;
        }
        form.agents = model.all_agents;
        return form;
      };

      /*
      			#
      			#
      */


      ApiKeyEditFormMapper.prototype.applyFormToModel = function(model, formModel) {
        return model.note = formModel.note;
      };

      /*
      			#
      			#
      */


      ApiKeyEditFormMapper.prototype.getPostDataFromForm = function(formModel) {
        var postData;
        postData = {};
        postData.id = formModel.id;
        postData.note = formModel.note;
        if (!formModel.isSuperUser) {
          postData.person = formModel.user.id;
        }
        return postData;
      };

      return ApiKeyEditFormMapper;

    })();
  });

}).call(this);

/*
//@ sourceMappingURL=ApiKeyEditFormMapper.js.map
*/