(function() {
  var __hasProp = {}.hasOwnProperty,
    __extends = function(child, parent) { for (var key in parent) { if (__hasProp.call(parent, key)) child[key] = parent[key]; } function ctor() { this.constructor = child; } ctor.prototype = parent.prototype; child.prototype = new ctor(); child.__super__ = parent.prototype; return child; };

  define(['DeskPRO/Util/Strings', 'Admin/Main/Ctrl/Base'], function(Strings, Admin_Ctrl_Base) {
    var Admin_ChatSetup_Ctrl_ChatSetup;
    Admin_ChatSetup_Ctrl_ChatSetup = (function(_super) {
      __extends(Admin_ChatSetup_Ctrl_ChatSetup, _super);

      function Admin_ChatSetup_Ctrl_ChatSetup() {
        return Admin_ChatSetup_Ctrl_ChatSetup.__super__.constructor.apply(this, arguments);
      }

      Admin_ChatSetup_Ctrl_ChatSetup.CTRL_ID = 'Admin_ChatSetup_Ctrl_ChatSetup';

      Admin_ChatSetup_Ctrl_ChatSetup.CTRL_AS = 'ChatSetup';

      Admin_ChatSetup_Ctrl_ChatSetup.DEPS = [];


      /*
       	 *
       */

      Admin_ChatSetup_Ctrl_ChatSetup.prototype.init = function() {
        this.setup = null;
        this.$scope.code_snippets = {
          chat: ''
        };
        return this.$scope.chat_options = {
          offline_url: '',
          show_offline: false,
          position: 'right',
          start_phrase: 'Click here to chat with us',
          resume_phrase: 'Open your chat',
          offline_phrase: 'Click here to contact us',
          open_window_phrase: 'Open this chat in a new window',
          lang_id: '0'
        };
      };


      /*
       	 *
       */

      Admin_ChatSetup_Ctrl_ChatSetup.prototype.initialLoad = function() {
        var data_promise;
        data_promise = this.Api.sendDataGet({
          hdinfo: '/deskpro/info',
          chat_setup: '/chat_setup'
        }).then((function(_this) {
          return function(res) {
            _this.hdinfo = res.data.hdinfo;
            _this.$scope.setup = res.data.chat_setup.chat_setup;
            return _this.$scope.$watch('chat_options', function() {
              return _this.updateChatCode();
            }, true);
          };
        })(this));
        return this.$q.all([data_promise]);
      };


      /*
      		 *
       */

      Admin_ChatSetup_Ctrl_ChatSetup.prototype.toggleChat = function() {
        var val;
        if (this.$scope.setup.chat_enabled) {
          val = '1';
        } else {
          val = '0';
        }
        return this.Api.sendPost('/chat_setup/toggle_chat/' + val);
      };

      Admin_ChatSetup_Ctrl_ChatSetup.prototype.updateChatCode = function() {
        var btn_pos, code, lang_id, offline_phrase, offline_url_code, open_window_phrase, resume_phrase, start_phrase;
        start_phrase = Strings.addslashes(this.$scope.chat_options.start_phrase || 'Click here to chat with us');
        resume_phrase = Strings.addslashes(this.$scope.chat_options.resume_phrase || 'Open your chat');
        offline_phrase = Strings.addslashes(this.$scope.chat_options.offline_phrase || 'Click here to contact us');
        open_window_phrase = Strings.addslashes(this.$scope.chat_options.open_window_phrase || 'Open this chat in a new window');
        btn_pos = this.$scope.chat_options.position || 'right';
        lang_id = this.$scope.chat_options.lang_id || 0;
        offline_url_code = '';
        if (this.$scope.chat_options.show_offline) {
          offline_url_code = "\n\tDpChatWidget_Options.offlineUrl = '" + (Strings.addslashes(this.$scope.chat_options.offline_url)) + "';";
          offline_url_code += "\n\tDpChatWidget_Options.offlinePhrase = '" + offline_phrase + "';";
        }
        code = "<!-- DeskPRO Chat -->\n<script type=\"text/javascript\">\n	var DpChatWidget_Options = DpChatWidget_Options || {};\n	DpChatWidget_Options.startPhrase = '" + start_phrase + "';" + offline_url_code + "\n	DpChatWidget_Options.tabLocation = '" + btn_pos + "';\n\n	DpChatWidget_Options.openInWindowPhrase = '" + open_window_phrase + "';\n	DpChatWidget_Options.resumePhrase = '" + resume_phrase + "';\n	DpChatWidget_Options.languageId = " + lang_id + ";\n\n	/**\n		* Style for the chat button\n		*/\n	DpChatWidget_Options.btnStyle = {\n		bgColor: '#3F3F3F',\n		border: '2px solid #727272',\n		textColor: '#FFFFFF',\n		textShadow: '0px 1px 2px #000000',\n		font: 'bold 12px Arial, sans-serif'\n	},\n\n	/**\n			* Style for the chat border/frame that goes\n			* around the chat window.\n			*/\n	DpChatWidget_Options.frameStyle = {\n		bgColor: '#2A69A9',\n		textColor: '#FFFFFF'\n	};\n\n	DpChatWidget_Options.protocol = ('https:' == document.location.protocol ? 'https' : 'http');\n	DpChatWidget_Options.deskproUrl = DpChatWidget_Options.protocol + ':" + this.hdinfo.widget_url + "';\n	DpChatWidget_Options.currentPageUrl = window.location;\n	DpChatWidget_Options.referrerPageUrl = document.referrer;\n	if (document.getElementsByTagName) {\n		(function() {\n		var scr   = document.createElement('script');\n		scr.type  = 'text/javascript';\n		scr.async = true;\n		scr.src   = DpChatWidget_Options.protocol + ':" + this.hdinfo.asset_url + "javascripts/DeskPRO/User/ChatWidget/ChatWidget.js';\n		(document.getElementsByTagName('head')[0] || document.getElementsByTagName('body')[0]).appendChild(scr);\n	})();\n}\n</script>\n<!-- /DeskPRO Chat -->";
        return this.$scope.code_snippets.chat = code;
      };

      return Admin_ChatSetup_Ctrl_ChatSetup;

    })(Admin_Ctrl_Base);
    return Admin_ChatSetup_Ctrl_ChatSetup.EXPORT_CTRL();
  });

}).call(this);

//# sourceMappingURL=ChatSetup.js.map
