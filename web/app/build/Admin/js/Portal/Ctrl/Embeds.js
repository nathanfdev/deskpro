(function() {
  var __hasProp = {}.hasOwnProperty,
    __extends = function(child, parent) { for (var key in parent) { if (__hasProp.call(parent, key)) child[key] = parent[key]; } function ctor() { this.constructor = child; } ctor.prototype = parent.prototype; child.prototype = new ctor(); child.__super__ = parent.prototype; return child; };

  define(['DeskPRO/Util/Strings', 'Admin/Main/Ctrl/Base'], function(Strings, Admin_Ctrl_Base) {
    var Admin_Portal_Ctrl_Embeds;
    Admin_Portal_Ctrl_Embeds = (function(_super) {
      __extends(Admin_Portal_Ctrl_Embeds, _super);

      function Admin_Portal_Ctrl_Embeds() {
        return Admin_Portal_Ctrl_Embeds.__super__.constructor.apply(this, arguments);
      }

      Admin_Portal_Ctrl_Embeds.CTRL_ID = 'Admin_Portal_Ctrl_Embeds';

      Admin_Portal_Ctrl_Embeds.CTRL_AS = 'Embeds';

      Admin_Portal_Ctrl_Embeds.DEPS = ['Api'];

      Admin_Portal_Ctrl_Embeds.prototype.init = function() {
        this.$scope.type = this.$stateParams.type;
        this.$scope.code_snippets = {
          overlay: '',
          chat: '',
          form_frame: '',
          iframe: ''
        };
        this.$scope.overlay_options = {
          title: 'Support & Feedback',
          position: 'left',
          lang_id: '0'
        };
        this.$scope.chat_options = {
          offline_url: '',
          show_offline: false,
          position: 'right',
          start_phrase: 'Click here to chat with us',
          resume_phrase: 'Open your chat',
          offline_phrase: 'Click here to contact us',
          open_window_phrase: 'Open this chat in a new window',
          lang_id: '0'
        };
        this.$scope.form_frame_options = {
          lang_id: '0',
          dep_id: '0'
        };
        return this.$scope.iframe_options = {
          simple_mode: true,
          initial_load: '',
          lang_id: '0'
        };
      };

      Admin_Portal_Ctrl_Embeds.prototype.initCode = function() {
        this.$scope.$watch('overlay_options', (function(_this) {
          return function() {
            return _this.updateWebsiteTabCode();
          };
        })(this), true);
        this.$scope.$watch('chat_options', (function(_this) {
          return function() {
            return _this.updateChatCode();
          };
        })(this), true);
        this.$scope.$watch('form_frame_options', (function(_this) {
          return function() {
            return _this.updateFormFrameCode();
          };
        })(this), true);
        this.$scope.$watch('iframe_options', (function(_this) {
          return function() {
            return _this.updateIframeCode();
          };
        })(this), true);
        this.$scope.embedEditorLoaded = function(editor) {
          return $(editor.container).closest('div.editor').data('ace-editor', editor).addClass('with-ace-editor');
        };
        this.updateWebsiteTabCode();
        this.updateChatCode();
        this.updateFormFrameCode();
        return this.updateIframeCode();
      };

      Admin_Portal_Ctrl_Embeds.prototype.initialLoad = function() {
        var promise;
        promise = this.Api.sendDataGet({
          hdinfo: '/deskpro/info',
          ticket_deps: '/ticket_deps',
          langs: '/langs',
          widget_selections: '/widget/selections'
        }).then((function(_this) {
          return function(res) {
            var d, key, selections, _i, _j, _k, _l, _len, _len1, _len2, _len3, _ref, _ref1, _ref2, _ref3, _ref4, _ref5, _ref6, _ref7, _ref8, _ref9;
            _this.hdinfo = res.data.hdinfo;
            _this.langs = res.data.langs.languages;
            _this.widget_selections = res.data.widget_selections;
            selections = {
              articles: {},
              downloads: {},
              news: {}
            };
            if (((_ref = _this.widget_selections) != null ? (_ref1 = _ref.selections) != null ? _ref1.articles : void 0 : void 0) != null) {
              _ref2 = _this.widget_selections.selections.articles;
              for (_i = 0, _len = _ref2.length; _i < _len; _i++) {
                key = _ref2[_i];
                selections.articles[key] = true;
              }
            }
            if (((_ref3 = _this.widget_selections) != null ? (_ref4 = _ref3.selections) != null ? _ref4.downloads : void 0 : void 0) != null) {
              _ref5 = _this.widget_selections.selections.downloads;
              for (_j = 0, _len1 = _ref5.length; _j < _len1; _j++) {
                key = _ref5[_j];
                selections.downloads[key] = true;
              }
            }
            if (((_ref6 = _this.widget_selections) != null ? (_ref7 = _ref6.selections) != null ? _ref7.news : void 0 : void 0) != null) {
              _ref8 = _this.widget_selections.selections.news;
              for (_k = 0, _len2 = _ref8.length; _k < _len2; _k++) {
                key = _ref8[_k];
                selections.news[key] = true;
              }
            }
            _this.widget_selections.selections = selections;
            _this.deps = [];
            _ref9 = res.data.ticket_deps.departments;
            for (_l = 0, _len3 = _ref9.length; _l < _len3; _l++) {
              d = _ref9[_l];
              if (!d.has_children) {
                d.title = d.title_parts.join(' > ');
                _this.deps.push(d);
              }
            }
            return _this.initCode();
          };
        })(this));
        return promise;
      };


      /*
       	 *
       */

      Admin_Portal_Ctrl_Embeds.prototype.updateSelections = function() {
        var inst;
        inst = this.$modal.open({
          templateUrl: this.getTemplatePath('Portal/selections-modal.html'),
          controller: [
            '$scope', '$modalInstance', 'widget_selections', function($scope, $modalInstance, widget_selections) {
              $scope.widget_selections = widget_selections;
              $scope.confirm = function() {
                return $modalInstance.close($scope.widget_selections.selections);
              };
              return $scope.dismiss = function() {
                return $modalInstance.dismiss();
              };
            }
          ],
          resolve: {
            widget_selections: (function(_this) {
              return function() {
                return _this.widget_selections;
              };
            })(this)
          }
        });
        return inst.result.then((function(_this) {
          return function(selections_data) {
            var key, postData, value, _ref, _ref1, _ref2;
            postData = {
              articles: [],
              downloads: [],
              news: []
            };
            _ref = selections_data.articles;
            for (key in _ref) {
              value = _ref[key];
              if (value) {
                postData.articles.push(key);
              }
            }
            _ref1 = selections_data.downloads;
            for (key in _ref1) {
              value = _ref1[key];
              if (value) {
                postData.downloads.push(key);
              }
            }
            _ref2 = selections_data.news;
            for (key in _ref2) {
              value = _ref2[key];
              if (value) {
                postData.news.push(key);
              }
            }
            return _this.Api.sendPostJson('/widget/selections', {
              selections: postData
            });
          };
        })(this));
      };

      Admin_Portal_Ctrl_Embeds.prototype.updateWebsiteTabCode = function() {
        var btn_pos, btn_title, code, lang_id;
        btn_title = Strings.addslashes(this.$scope.overlay_options.title || 'Support');
        btn_pos = this.$scope.overlay_options.position || 'left';
        lang_id = this.$scope.overlay_options.lang_id || 0;
        code = "<!-- DeskPRO Widget -->\n<script type=\"text/javascript\">\n	var DpOverlayWidget_Options = DpOverlayWidget_Options || {};\n	DpOverlayWidget_Options.phrase = '" + btn_title + "';\n	DpOverlayWidget_Options.tabLocation = '" + btn_pos + "';\n	DpOverlayWidget_Options.languageId = " + lang_id + ";\n	DpOverlayWidget_Options.topPosition = '200px';\n	DpOverlayWidget_Options.btnStyle = {\n		bgColor: '#3F3F3F',\n		border: '2px solid #727272',\n		textColor: '#FFFFFF',\n		textShadow: '0px 0px 2px #000000',\n		font: 'bold 13px Arial, sans-serif'\n	};\n\n	DpOverlayWidget_Options.protocol = ('https:' == document.location.protocol ? 'https' : 'http');\n	DpOverlayWidget_Options.deskproUrl = DpOverlayWidget_Options.protocol + ':" + this.hdinfo.widget_url + "';\n	if (document.getElementsByTagName) {\n		(function() {\n			var scr   = document.createElement('script');\n			scr.type  = 'text/javascript';\n			scr.async = true;\n			scr.src   = DpOverlayWidget_Options.protocol + ':" + this.hdinfo.asset_url + "javascripts/DeskPRO/User/WebsiteWidget/Overlay.js';\n			(document.getElementsByTagName('head')[0] || document.getElementsByTagName('body')[0]).appendChild(scr);\n		})();\n	}\n</script>\n<!-- /DeskPRO Widget -->";
        return this.$scope.code_snippets.overlay = code;
      };

      Admin_Portal_Ctrl_Embeds.prototype.updateChatCode = function() {
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
        code = "<!-- DeskPRO Chat -->\n<script type=\"text/javascript\">\n	var DpChatWidget_Options = DpChatWidget_Options || {};\n	DpChatWidget_Options.startPhrase = '" + start_phrase + "';" + offline_url_code + "\n	DpChatWidget_Options.tabLocation = '" + btn_pos + "';\n\n	DpChatWidget_Options.openInWindowPhrase = '" + open_window_phrase + "';\n	DpChatWidget_Options.resumePhrase = '" + resume_phrase + "';\n	DpChatWidget_Options.languageId = " + lang_id + ";\n\n	/**\n	 * Style for the chat button\n	 */\n	DpChatWidget_Options.btnStyle = {\n		bgColor: '#3F3F3F',\n		border: '2px solid #727272',\n		textColor: '#FFFFFF',\n		textShadow: '0px 1px 2px #000000',\n		font: 'bold 12px Arial, sans-serif'\n	},\n\n	/**\n	 * Style for the chat border/frame that goes\n	 * around the chat window.\n	 */\n	DpChatWidget_Options.frameStyle = {\n		bgColor: '#2A69A9',\n		textColor: '#FFFFFF'\n	};\n\n	DpChatWidget_Options.protocol = ('https:' == document.location.protocol ? 'https' : 'http');\n	DpChatWidget_Options.deskproUrl = DpChatWidget_Options.protocol + ':" + this.hdinfo.widget_url + "';\n	DpChatWidget_Options.currentPageUrl = window.location;\n	DpChatWidget_Options.referrerPageUrl = document.referrer;\n	if (document.getElementsByTagName) {\n		(function() {\n			var scr   = document.createElement('script');\n			scr.type  = 'text/javascript';\n			scr.async = true;\n			scr.src   = DpChatWidget_Options.protocol + ':" + this.hdinfo.asset_url + "javascripts/DeskPRO/User/ChatWidget/ChatWidget.js';\n			(document.getElementsByTagName('head')[0] || document.getElementsByTagName('body')[0]).appendChild(scr);\n		})();\n	}\n</script>\n<!-- /DeskPRO Chat -->";
        return this.$scope.code_snippets.chat = code;
      };

      Admin_Portal_Ctrl_Embeds.prototype.updateFormFrameCode = function() {
        var code, dep_id, lang_id;
        lang_id = this.$scope.form_frame_options.lang_id || 0;
        dep_id = this.$scope.form_frame_options.dep_id || 0;
        code = "<!-- DeskPRO Ticket Form -->\n<div id=\"dp_newticket_form\" style=\"display: none;\"></div>\n<script type=\"text/javascript\">\nvar DpNewTicket_Options = DpNewTicket_Options || {};\nDpNewTicket_Options.departmentId = " + dep_id + ";\nDpNewTicket_Options.languageId = " + lang_id + ";\n\n/**\n* If the user name is already known, you can set it here.\n* When set, the name field is hidden in the form.\n*/\nDpNewTicket_Options.formUserName = '';\n\n/**\n* If the user email is already known, you can set it here.\n* When set, the email field is hidden in the form.\n*/\nDpNewTicket_Options.formUserEmail = '';\n\nDpNewTicket_Options.containerId = 'dp_newticket_form';\nDpNewTicket_Options.initialHeight = 700;\n\nDpNewTicket_Options.protocol = ('https:' == document.location.protocol ? 'https' : 'http');\nDpNewTicket_Options.deskproUrl = DpNewTicket_Options.protocol + ':" + this.hdinfo.widget_url + "';\nif (document.getElementsByTagName) {\n	(function() {\n		var scr = document.createElement('script');\n		scr.type = 'text/javascript';\n		scr.async = true;\n		scr.src = DpNewTicket_Options.protocol + ':" + this.hdinfo.asset_url + "javascripts/DeskPRO/User/TicketFormWidget/TicketFormWidget.js';\n		(document.getElementsByTagName('head')[0] || document.getElementsByTagName('body')[0]).appendChild(scr);\n	})();\n}\n</script>\n<!-- /DeskPRO Ticket Form -->";
        return this.$scope.code_snippets.form_frame = code;
      };

      Admin_Portal_Ctrl_Embeds.prototype.updateIframeCode = function() {
        var code, initial_load_path, lang_id, simple_mode;
        simple_mode = this.$scope.iframe_options.simple_mode ? 'true' : 'false';
        initial_load_path = '/' + this.$scope.iframe_options.initial_load;
        lang_id = this.$scope.iframe_options.lang_id || 0;
        code = "<!-- DeskPRO Helpdesk Embed -->\n<div id=\"dp_helpdesk\" style=\"display: none;\"></div>\n<script type=\"text/javascript\">\n	var DpHelpdesk_Options = DpHelpdesk_Options || {};\n	DpHelpdesk_Options.simpleMode = " + simple_mode + ";\n	DpHelpdesk_Options.loadPath = '" + initial_load_path + "';\n	DpHelpdesk_Options.languageId = " + lang_id + ";\n\n	DpHelpdesk_Options.initialHeight = 700;\n	DpHelpdesk_Options.containerId = 'dp_helpdesk';\n\n	DpHelpdesk_Options.protocol = ('https:' == document.location.protocol ? 'https' : 'http');\n	DpHelpdesk_Options.deskproUrl = DpHelpdesk_Options.protocol + ':" + this.hdinfo.widget_url + "';\n	if (document.getElementsByTagName) {\n		(function() {\n			var scr   = document.createElement('script');\n			scr.type  = 'text/javascript';\n			scr.async = true;\n			scr.src   = DpHelpdesk_Options.protocol + ':" + this.hdinfo.asset_url + "javascripts/DeskPRO/User/HelpdeskWidget/HelpdeskWidget.js';\n			(document.getElementsByTagName('head')[0] || document.getElementsByTagName('body')[0]).appendChild(scr);\n		})();\n	}\n</script>\n<!-- /DeskPRO Helpdesk Embed -->";
        return this.$scope.code_snippets.iframe = code;
      };

      return Admin_Portal_Ctrl_Embeds;

    })(Admin_Ctrl_Base);
    return Admin_Portal_Ctrl_Embeds.EXPORT_CTRL();
  });

}).call(this);

//# sourceMappingURL=Embeds.js.map
