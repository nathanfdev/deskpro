(function() {
  define(['DeskPRO/Util/Util'], function(Util) {
    var IpBanEditFormMapper;
    return IpBanEditFormMapper = (function() {
      function IpBanEditFormMapper() {}


      /*
      			 *
       		 *
       */

      IpBanEditFormMapper.prototype.getFormFromModel = function(model) {
        var form;
        form = {};
        form.banned_ip = model.ip_ban.banned_ip;
        return form;
      };


      /*
      			 *
      			 *
       */

      IpBanEditFormMapper.prototype.applyFormToModel = function(model, formModel) {};


      /*
      			 *
      			 *
       */

      IpBanEditFormMapper.prototype.getPostDataFromForm = function(formModel) {
        var postData;
        postData = {};
        postData.banned_ip = formModel.banned_ip;
        return postData;
      };

      return IpBanEditFormMapper;

    })();
  });

}).call(this);

//# sourceMappingURL=IpBanEditFormMapper.js.map
