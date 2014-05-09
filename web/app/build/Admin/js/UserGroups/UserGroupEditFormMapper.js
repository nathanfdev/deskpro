(function() {
  define(['DeskPRO/Util/Util'], function(Util) {
    var UserGroupEditFormMapper;
    return UserGroupEditFormMapper = (function() {
      function UserGroupEditFormMapper() {}

      UserGroupEditFormMapper.prototype.getFormFromModel = function(model) {
        var form;
        form = {};
        form.id = model.group.id;
        form.title = model.group.title;
        form.note = model.group.note;
        form.is_enabled = model.group.is_enabled;
        return form;
      };

      UserGroupEditFormMapper.prototype.applyFormToModel = function(model, formModel) {
        return model.title = formModel.title;
      };

      UserGroupEditFormMapper.prototype.getPostDataFromForm = function(formModel, formPermsModel) {
        var postData;
        postData = {};
        postData.title = formModel.title;
        postData.note = formModel.note;
        postData.is_enabled = formModel.is_enabled;
        if (formPermsModel) {
          postData.perms = Util.clone(formPermsModel);
          if (postData.perms.options.reopen_resolved_createnew === 'new_ticket') {
            postData.perms.ticket.reopen_resolved_createnew = true;
          } else {
            postData.perms.ticket.reopen_resolved_createnew = false;
          }
          delete postData.perms.options;
        }
        return postData;
      };

      return UserGroupEditFormMapper;

    })();
  });

}).call(this);

//# sourceMappingURL=UserGroupEditFormMapper.js.map
