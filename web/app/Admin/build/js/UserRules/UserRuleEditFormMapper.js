(function() {
  define(['DeskPRO/Util/Util'], function(Util) {
    var UserRuleEditFormMapper;
    return UserRuleEditFormMapper = (function() {
      function UserRuleEditFormMapper() {}

      /*
      			#
       		#
      */


      UserRuleEditFormMapper.prototype.getFormFromModel = function(model) {
        var form;
        form = {};
        form.id = model.user_rule.id;
        form.email_patterns = model.user_rule.email_patterns;
        return form;
      };

      /*
      			#
      			#
      */


      UserRuleEditFormMapper.prototype.applyFormToModel = function(model, formModel) {
        return model.email_patterns = formModel.email_patterns;
      };

      /*
      			#
      			#
      */


      UserRuleEditFormMapper.prototype.getPostDataFromForm = function(formModel) {
        var postData;
        postData = {};
        postData.id = formModel.id;
        postData.email_patterns = formModel.email_patterns;
        return postData;
      };

      return UserRuleEditFormMapper;

    })();
  });

}).call(this);

/*
//@ sourceMappingURL=UserRuleEditFormMapper.js.map
*/