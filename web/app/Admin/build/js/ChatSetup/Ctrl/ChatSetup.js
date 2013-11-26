(function() {
  var __hasProp = {}.hasOwnProperty,
    __extends = function(child, parent) { for (var key in parent) { if (__hasProp.call(parent, key)) child[key] = parent[key]; } function ctor() { this.constructor = child; } ctor.prototype = parent.prototype; child.prototype = new ctor(); child.__super__ = parent.prototype; return child; };

  define(['Admin/Main/Ctrl/Base'], function(Admin_Ctrl_Base) {
    var Admin_ChatSetup_Ctrl_ChatSetup, _ref;
    Admin_ChatSetup_Ctrl_ChatSetup = (function(_super) {
      __extends(Admin_ChatSetup_Ctrl_ChatSetup, _super);

      function Admin_ChatSetup_Ctrl_ChatSetup() {
        _ref = Admin_ChatSetup_Ctrl_ChatSetup.__super__.constructor.apply(this, arguments);
        return _ref;
      }

      Admin_ChatSetup_Ctrl_ChatSetup.CTRL_ID = 'Admin_ChatSetup_Ctrl_ChatSetup';

      Admin_ChatSetup_Ctrl_ChatSetup.CTRL_AS = 'ChatSetup';

      Admin_ChatSetup_Ctrl_ChatSetup.DEPS = ['$templateCache'];

      /*
       	#
      */


      Admin_ChatSetup_Ctrl_ChatSetup.prototype.init = function() {
        var code, tpl;
        this.setup = null;
        tpl = this.getTemplatePath("ChatSetup/embed-code.html");
        code = this.$templateCache.get(tpl);
        return this.$scope.embed_code = code;
      };

      /*
       	#
      */


      Admin_ChatSetup_Ctrl_ChatSetup.prototype.initialLoad = function() {
        var data_promise,
          _this = this;
        data_promise = this.Api.sendDataGet({
          'chat_setup': '/chat_setup'
        }).then(function(res) {
          return _this.$scope.setup = res.data.chat_setup.chat_setup;
        });
        return this.$q.all([data_promise]);
      };

      return Admin_ChatSetup_Ctrl_ChatSetup;

    })(Admin_Ctrl_Base);
    return Admin_ChatSetup_Ctrl_ChatSetup.EXPORT_CTRL();
  });

}).call(this);

/*
//@ sourceMappingURL=ChatSetup.js.map
*/