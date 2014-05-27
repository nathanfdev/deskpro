(function() {
  var __hasProp = {}.hasOwnProperty;

  define(['DeskPRO/Util/Util'], function(Util) {
    var UserRuleEditFormMapper;
    return UserRuleEditFormMapper = (function() {
      function UserRuleEditFormMapper() {}

      UserRuleEditFormMapper.prototype.getFormFromModel = function(model) {
        var form, id, title, _ref;
        form = {};
        form.id = model.user_rule.id;
        form.email_patterns = model.user_rule.email_patterns;
        form.usergroups = [];
        _ref = model.all_usergroups;
        for (id in _ref) {
          if (!__hasProp.call(_ref, id)) continue;
          title = _ref[id];
          form.usergroups.push({
            id: id,
            title: title
          });
        }
        if (model.user_rule.usergroup) {
          form.usergroup = {};
          form.usergroup.id = model.user_rule.usergroup.id;
          form.usergroup.title = model.user_rule.usergroup.title;
        }
        return form;
      };

      UserRuleEditFormMapper.prototype.applyFormToModel = function(model, formModel) {
        return model.email_patterns = formModel.email_patterns;
      };

      UserRuleEditFormMapper.prototype.getPostDataFromForm = function(formModel) {
        var postData;
        postData = {};
        postData.id = formModel.id;
        postData.email_patterns = formModel.email_patterns;
        postData.add_usergroup = formModel.usergroup.id;
        return postData;
      };

      return UserRuleEditFormMapper;

    })();
  });

}).call(this);

//# sourceMappingURL=UserRuleEditFormMapper.js.map
