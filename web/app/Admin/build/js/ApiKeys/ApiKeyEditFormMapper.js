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
        var form, id, ids, _i, _len;
        form = {};
        form.id = model.api_key.id;
        form.user = {};
        form.agents = model.all_agents;
        form.selected_agents = {};
        ids = _.pluck(form.user.agents, 'id');
        for (_i = 0, _len = ids.length; _i < _len; _i++) {
          id = ids[_i];
          form.selected_agents[id] = true;
        }
        return form;
      };

      /*
      			#
      			#
      */


      ApiKeyEditFormMapper.prototype.applyFormToModel = function(model, formModel) {
        return model.id = formModel.id;
      };

      /*
      			#
      			#
      */


      ApiKeyEditFormMapper.prototype.getPostDataFromForm = function(formModel) {
        var postData;
        postData = formModel;
        return postData;
      };

      return ApiKeyEditFormMapper;

    })();
  });

}).call(this);

/*
//@ sourceMappingURL=ApiKeyEditFormMapper.js.map
*/