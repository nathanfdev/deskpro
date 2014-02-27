(function() {
  define(['DeskPRO/Util/Util'], function(Util) {
    var EmailBanEditFormMapper;
    return EmailBanEditFormMapper = (function() {
      function EmailBanEditFormMapper() {}


      /*
      			 *
       		 *
       */

      EmailBanEditFormMapper.prototype.getFormFromModel = function(model) {
        var form;
        form = {};
        form.banned_email = model.email_ban.banned_email;
        return form;
      };


      /*
      			 *
      			 *
       */

      EmailBanEditFormMapper.prototype.applyFormToModel = function(model, formModel) {};


      /*
      			 *
      			 *
       */

      EmailBanEditFormMapper.prototype.getPostDataFromForm = function(formModel) {
        var postData;
        postData = {};
        postData.banned_email = formModel.banned_email;
        return postData;
      };

      return EmailBanEditFormMapper;

    })();
  });

}).call(this);

//# sourceMappingURL=EmailBanEditFormMapper.js.map
