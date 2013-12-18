(function() {
  define(['DeskPRO/Util/Util'], function(Util) {
    var UserGroupEditFormMapper;
    return UserGroupEditFormMapper = (function() {
      function UserGroupEditFormMapper() {}

      /*
      			#
       		#
      */


      UserGroupEditFormMapper.prototype.getFormFromModel = function(model) {
        var form;
        form = {};
        form.id = model.user_group.id;
        form.title = model.user_group.title;
        form.note = model.user_group.note;
        form.is_enabled = model.user_group.is_enabled;
        form.permissions = [];
        return form;
      };

      /*
      			#
      			#
      */


      UserGroupEditFormMapper.prototype.applyFormToModel = function(model, formModel) {
        return model.title = formModel.title;
      };

      /*
      			#
      			#
      */


      UserGroupEditFormMapper.prototype.getPostDataFromForm = function(formModel) {
        var postData;
        postData = {};
        postData.id = formModel.id;
        postData.title = formModel.title;
        postData.note = formModel.note;
        postData.permissions = [];
        return postData;
      };

      return UserGroupEditFormMapper;

    })();
  });

}).call(this);

/*
//@ sourceMappingURL=UserGroupEditFormMapper.js.map
*/