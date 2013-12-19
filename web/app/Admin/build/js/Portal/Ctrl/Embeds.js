(function() {
  var __hasProp = {}.hasOwnProperty,
    __extends = function(child, parent) { for (var key in parent) { if (__hasProp.call(parent, key)) child[key] = parent[key]; } function ctor() { this.constructor = child; } ctor.prototype = parent.prototype; child.prototype = new ctor(); child.__super__ = parent.prototype; return child; };

  define(['DeskPRO/Util/Strings', 'Admin/Main/Ctrl/Base'], function(Strings, Admin_Ctrl_Base) {
    var Admin_Portal_Ctrl_Embeds, _ref;
    Admin_Portal_Ctrl_Embeds = (function(_super) {
      __extends(Admin_Portal_Ctrl_Embeds, _super);

      function Admin_Portal_Ctrl_Embeds() {
        _ref = Admin_Portal_Ctrl_Embeds.__super__.constructor.apply(this, arguments);
        return _ref;
      }

      Admin_Portal_Ctrl_Embeds.CTRL_ID = 'Admin_Portal_Ctrl_Embeds';

      Admin_Portal_Ctrl_Embeds.CTRL_AS = 'Embeds';

      Admin_Portal_Ctrl_Embeds.prototype.init = function() {
        var _this = this;
        this.$scope.code_snippets = {
          overlay: '',
          chat: '',
          form_frame: ''
        };
        this.$scope.overlay_options = {
          title: 'Support & Feedback',
          position: 'left'
        };
        this.$scope.chat_options = {
          offline_url: '',
          show_offline: false,
          position: 'right',
          start_phrase: 'Click here to chat with us',
          resume_phrase: 'Open your chat',
          offline_phrase: 'Click here to contact us',
          open_window_phrase: 'Open this chat in a new window',
          language_id: '0'
        };
        this.$scope.iframe_options = {
          simple_mode: true,
          initial_load: ''
        };
        this.$scope.$watch('overlay_options', function() {
          return _this.updateWebsiteTabCode();
        }, true);
        this.$scope.$watch('chat_options', function() {
          return _this.updateChatCode();
        }, true);
        this.$scope.$watch('iframe_options', function() {
          return _this.updateIframeCode();
        }, true);
        this.$scope.embedEditorLoaded = function(editor) {
          return $(editor.container).closest('div.editor').data('ace-editor', editor).addClass('with-ace-editor');
        };
        this.updateWebsiteTabCode();
        this.updateChatCode();
        this.updateFormFrameCode();
        return this.updateIframeCode();
      };

      Admin_Portal_Ctrl_Embeds.prototype.initialLoad = function() {
        var promise,
          _this = this;
        return null;
        promise = this.Api.sendDataGet({}).then(function(res) {});
        return promise;
      };

      Admin_Portal_Ctrl_Embeds.prototype.updateWebsiteTabCode = function() {
        var btn_pos, btn_title, code;
        btn_title = Strings.addslashes(this.$scope.overlay_options.title || 'Support');
        btn_pos = this.$scope.overlay_options.position || 'left';
        code = "<!-- DeskPRO Widget -->\n<script type=\"text/javascript\">\n	var DpOverlayWidget_Options = DpOverlayWidget_Options || {};\n	DpOverlayWidget_Options.protocol = ('https:' == document.location.protocol ? 'https' : 'http');\n	DpOverlayWidget_Options.deskproUrl = DpOverlayWidget_Options.protocol + '://';\n	DpOverlayWidget_Options.phrase = '" + btn_title + "';\n	DpOverlayWidget_Options.tabLocation = '" + btn_pos + "';\n	DpOverlayWidget_Options.topPosition = '200px';\n	DpOverlayWidget_Options.btnStyle = {\n		bgColor: '#3F3F3F',\n		border: '2px solid #727272',\n		textColor: '#FFFFFF',\n		textShadow: '0px 0px 2px #000000',\n		font: 'bold 13px Arial, sans-serif'\n	};\n\n	if (document.getElementsByTagName) {\n		(function() {\n			var scr   = document.createElement('script');\n			scr.type  = 'text/javascript';\n			scr.async = true;\n			scr.src   = DpOverlayWidget_Options.protocol + '://javascripts/DeskPRO/User/WebsiteWidget/Overlay.js';\n			(document.getElementsByTagName('head')[0] || document.getElementsByTagName('body')[0]).appendChild(scr);\n		})();\n	}\n</script>\n<!-- /DeskPRO Widget -->";
        return this.$scope.code_snippets.overlay = code;
      };

      Admin_Portal_Ctrl_Embeds.prototype.updateChatCode = function() {
        var btn_pos, code, offline_phrase, offline_url_code, open_window_phrase, resume_phrase, start_phrase;
        start_phrase = Strings.addslashes(this.$scope.chat_options.start_phrase || 'Click here to chat with us');
        resume_phrase = Strings.addslashes(this.$scope.chat_options.resume_phrase || 'Open your chat');
        offline_phrase = Strings.addslashes(this.$scope.chat_options.offline_phrase || 'Click here to contact us');
        open_window_phrase = Strings.addslashes(this.$scope.chat_options.open_window_phrase || 'Open this chat in a new window');
        btn_pos = this.$scope.chat_options.position || 'right';
        offline_url_code = '';
        if (this.$scope.chat_options.show_offline) {
          offline_url_code = "\n\tDpChatWidget_Options.offlineUrl = '" + (Strings.addslashes(this.$scope.chat_options.offline_url)) + "';";
          offline_url_code += "\n\tDpChatWidget_Options.offlinePhrase = '" + offline_phrase + "';";
        }
        code = "<!-- DeskPRO Chat -->\n<script type=\"text/javascript\">\n	var DpChatWidget_Options = DpChatWidget_Options || {};\n	DpChatWidget_Options.startPhrase = '" + start_phrase + "';" + offline_url_code + "\n	DpChatWidget_Options.tabLocation = '" + btn_pos + "';\n\n	DpChatWidget_Options.openInWindowPhrase = '" + open_window_phrase + "';\n	DpChatWidget_Options.resumePhrase = '" + resume_phrase + "';\n	DpChatWidget_Options.languageId = 0;\n\n	/**\n	 * Style for the chat button\n	 */\n	DpChatWidget_Options.btnStyle = {\n		bgColor: '#3F3F3F',\n		border: '2px solid #727272',\n		textColor: '#FFFFFF',\n		textShadow: '0px 1px 2px #000000',\n		font: 'bold 12px Arial, sans-serif'\n	},\n\n	/**\n	 * Style for the chat border/frame that goes\n	 * around the chat window.\n	 */\n	DpChatWidget_Options.frameStyle = {\n		bgColor: '#2A69A9',\n		textColor: '#FFFFFF'\n	};\n\n	DpChatWidget_Options.protocol = ('https:' == document.location.protocol ? 'https' : 'http');\n	DpChatWidget_Options.deskproUrl = DpChatWidget_Options.protocol + '://';\n	DpChatWidget_Options.currentPageUrl = window.location;\n	DpChatWidget_Options.referrerPageUrl = document.referrer;\n	if (document.getElementsByTagName) {\n		(function() {\n			var scr   = document.createElement('script');\n			scr.type  = 'text/javascript';\n			scr.async = true;\n			scr.src   = DpChatWidget_Options.protocol + '://javascripts/DeskPRO/User/ChatWidget/ChatWidget.js';\n			(document.getElementsByTagName('head')[0] || document.getElementsByTagName('body')[0]).appendChild(scr);\n		})();\n	}\n</script>\n<!-- /DeskPRO Chat -->";
        return this.$scope.code_snippets.chat = code;
      };

      Admin_Portal_Ctrl_Embeds.prototype.updateFormFrameCode = function() {
        var code;
        code = "<!-- DeskPRO Ticket Form -->\n<div id=\"dp_newticket_form\" style=\"display: none;\"></div>\n<script type=\"text/javascript\">\nvar DpNewTicket_Options = DpNewTicket_Options || {};\nDpNewTicket_Options.protocol = ('https:' == document.location.protocol ? 'https' : 'http');\nDpNewTicket_Options.deskproUrl = DpNewTicket_Options.protocol + '://support.deskpro.com/';\nDpNewTicket_Options.initialHeight = 700;\nDpNewTicket_Options.containerId = 'dp_newticket_form';\nDpNewTicket_Options.departmentId = 0;\n\n/**\n* The Language ID to load for users with no language preference\n*/\nDpNewTicket_Options.languageId = 0;\n\n/**\n* If the user name is already known, you can set it here.\n* When set, the name field is hidden in the form.\n*/\nDpNewTicket_Options.formUserName = '';\n\n/**\n* If the user email is already known, you can set it here.\n* When set, the email field is hidden in the form.\n*/\nDpNewTicket_Options.formUserEmail = '';\n\nif (document.getElementsByTagName) {\n	(function() {\n		var scr = document.createElement('script');\n		scr.type = 'text/javascript';\n		scr.async = true;\n		scr.src = DpNewTicket_Options.protocol + '://javascripts/DeskPRO/User/TicketFormWidget/TicketFormWidget.js';\n		(document.getElementsByTagName('head')[0] || document.getElementsByTagName('body')[0]).appendChild(scr);\n	})();\n}\n</script>\n<!-- /DeskPRO Ticket Form -->";
        return this.$scope.code_snippets.form_frame = code;
      };

      Admin_Portal_Ctrl_Embeds.prototype.updateIframeCode = function() {
        var code, initial_load_path, simple_mode;
        simple_mode = this.$scope.iframe_options.simple_mode ? 'true' : 'false';
        initial_load_path = '/' + this.$scope.iframe_options.initial_load;
        code = "<!-- DeskPRO Helpdesk Embed -->\n<div id=\"dp_helpdesk\" style=\"display: none;\"></div>\n<script type=\"text/javascript\">\n	var DpHelpdesk_Options = DpHelpdesk_Options || {};\n	DpHelpdesk_Options.simpleMode = " + simple_mode + ";\n	DpHelpdesk_Options.loadPath = '" + initial_load_path + "';\n\n	/**\n	 * The Language ID to load for users with no language preference\n	 */\n	DpHelpdesk_Options.languageId = 0;\n\n	DpHelpdesk_Options.protocol = ('https:' == document.location.protocol ? 'https' : 'http');\n	DpHelpdesk_Options.deskproUrl = DpHelpdesk_Options.protocol + '://';\n	DpHelpdesk_Options.initialHeight = 700;\n	DpHelpdesk_Options.containerId = 'dp_helpdesk';\n\n	if (document.getElementsByTagName) {\n		(function() {\n			var scr   = document.createElement('script');\n			scr.type  = 'text/javascript';\n			scr.async = true;\n			scr.src   = DpHelpdesk_Options.protocol + '://javascripts/DeskPRO/User/HelpdeskWidget/HelpdeskWidget.js';\n			(document.getElementsByTagName('head')[0] || document.getElementsByTagName('body')[0]).appendChild(scr);\n		})();\n	}\n</script>\n<!-- /DeskPRO Helpdesk Embed -->";
        return this.$scope.code_snippets.iframe = code;
      };

      return Admin_Portal_Ctrl_Embeds;

    })(Admin_Ctrl_Base);
    return Admin_Portal_Ctrl_Embeds.EXPORT_CTRL();
  });

}).call(this);

/*
//@ sourceMappingURL=Embeds.js.map
*/