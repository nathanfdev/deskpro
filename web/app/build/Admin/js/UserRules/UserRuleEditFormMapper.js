(function() {
  define(['DeskPRO/Util/Util'], function(Util) {
    var UserRuleEditFormMapper;
    return UserRuleEditFormMapper = (function() {
      function UserRuleEditFormMapper() {}


      /*
      			 *
       		 *
       */

      UserRuleEditFormMapper.prototype.getFormFromModel = function(model) {
        var form;
        form = {};
        form.id = model.user_rule.id;
        form.email_patterns = model.user_rule.email_patterns;
        form.usergroups = model.all_usergroups;
        if (model.user_rule.usergroup) {
          form.usergroup = {};
          form.usergroup.id = model.user_rule.usergroup.id;
          form.usergroup.title = model.user_rule.usergroup.title;
        }
        return form;
      };


      /*
      			 *
      			 *
       */

      UserRuleEditFormMapper.prototype.applyFormToModel = function(model, formModel) {
        return model.email_patterns = formModel.email_patterns;
      };


      /*
      			 *
      			 *
       */

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
