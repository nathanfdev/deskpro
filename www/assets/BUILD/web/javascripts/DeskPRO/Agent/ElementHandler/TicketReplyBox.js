Orb.createNamespace('DeskPRO.Agent.ElementHandler');

DeskPRO.Agent.ElementHandler.TicketReplyBox = new Orb.Class({
	Extends: DeskPRO.ElementHandler,

	init: function() {
		this.initRetries = 5;
		this.initRetryTimeout = 300;
		this.baseId = this.el.data('base-id');
		this.agentNotifyListShown = false;
		this.uploading = false;
		this.fwdMessages = [];
		this.fwdAttachments = [];
		this.fwdInfo = null;
		this.dontDispatch = false;
	},

	initPage: function() {
    this.lang = eval(this.el.data('dp-lang') || '{}');
    var self = this;
		this.page = this.el.closest('.with-page-fragment').data('page-fragment');

		if (!this.page && this.initRetries-- > 0) {
			return setTimeout(this.initPage.bind(this), this.initRetryTimeout);
		}

		var textarea = this.getElById('replybox_txt'), isWysiwyg = false;
		this.textarea = textarea;

		this.isNote = false;
		var snippetBtn = null;

		var closeTabCheck = this.getElById('close_tab_opt');
		var closeReply    = this.el.data('close-reply') ? true : false;
		var closeNote     = this.el.data('close-note') ? true : false;

		var agentSel      = this.getElById('agent_sel');
		var agentSelText  = this.getElById('agent_sel_text');
		var agentSelCheck = this.getElById('agent_sel_check');
		var teamSel       = this.getElById('agent_team_sel');
		var teamSelText   = this.getElById('agent_team_sel_text');
		var teamSelCheck  = this.getElById('agent_team_sel_check');

		var jiraActionSel = this.getElById('jira_app_action'),
				jiraActionText = this.getElById('jira_app_action_text'),
				jiraActionCheck = this.getElById('jira_app_action_check');

		var storedReplyText = '';
		var storedNoteText = '';
		var storedFWDText = '';

		var sig = this.el.find('textarea.signature-value-html').val() || "";
		sig = sig.replace(/<div class="dp-signature-start">([\w\W]*)<\/div>/, '<p class="dp-signature-start">$1</p>');

		var draft = this.getElById('draft_html');
		if (draft.length) {
			if (self.el.data('draft-is-note') == '1') {
				storedNoteText = draft.val();
				if (sig) {
					textarea.val(($.browser.msie ? '<p></p><p></p>' : '<p><br></p><p><br></p>') + '\n\n' + sig);
				}
			} else {
				textarea.val(draft.val());
			}
		} else {
			if (sig) {
				textarea.val(($.browser.msie ? '<p></p><p></p>' : '<p><br></p><p><br></p>') + '\n\n' + sig);
			}
		}

		storedFWDText = textarea.val();

		DeskPRO_Window.initRteAgentReply(textarea, {
			defaultIsHtml: true,
			inlineHiddenPosition: this.getElById('is_html_reply'),
			autosaveContent: 'ticket',
			minHeight: 120,
			autosaveContentId: (this.page ? this.page.meta.ticket_id : false),
			preAutosaveCallback: function(textarea, data) {

				if (self.getElById('reply_is_trans').val() != "") {
					var newContent = textarea.data('redactor').getCode(),
					name = textarea.attr('name');

					data = [];
					data.push({
						name: name,
						value: newContent
					});
				}

				data.push({
					name: 'extras[is_note]',
					value: self.isNote ? 1 : 0
				});

				self.el.find('input[name="attach[]"]').each(function() {
					data.push({
						name: 'extras[attach][]',
						value: $(this).val()
					});
				});

				self.el.find('input[name="blob_inline_ids[]"]').each(function() {
					data.push({
						name: 'extras[blob_inline_ids][]',
						value: $(this).val()
					});
				});

				return data;
			},
			callback: function(obj) {
				obj.addBtnFirst('dp_attach', 'Click here to attach a file. You may also drag a file from your computer desktop into this reply area to upload attachments faster.', function(){});
				obj.addBtnAfter('dp_attach', 'dp_snippets', 'Open snippets', function(){});
				obj.addBtnSeparatorAfter('dp_attach');

				snippetBtn = obj.$toolbar.find('.redactor_btn_dp_snippets').closest('li');

				var snippets_html = self.lang.snippets_btn;
				snippets_html = snippets_html.replace(/Ss/, '<span class="show-key-shortcut">S</span>');
				snippetBtn.addClass('snippets').find('a').html(snippets_html);

				var attachBtn = obj.$toolbar.find('.redactor_btn_dp_attach').closest('li');
				attachBtn.addClass('attach');
				attachBtn.find('a').text(self.lang.attach_btn).append('<input type="file" class="file" name="file-upload" />');

				obj.addBtnSeparatorAfter('dp_snippets');
			}
		});
		this.getElById('is_html_reply').val(1);

		if (textarea.data('redactor')) {
			var ed = textarea.getEditor();
			var lastH = ed.height();
			if (DESKPRO_ENABLE_KB_SHORTCUTS) {
				ed.on('keydown', function(e) {
					if (e.which === 13 && (e.ctrlKey || e.metaKey)) {
						self.page.shortcutSendReply();
						return;
					}
				});
				ed.on('keyup', function(ev) {
					var isCtrl = false;
					if (ev.ctrlKey && DeskPRO_Window.keyboardShortcuts.isMac) {
						isCtrl = true;
					} else if (ev.altKey && !DeskPRO_Window.keyboardShortcuts.isMac) {
						isCtrl = true;
					}

					if (isCtrl) {
						if (isCtrl && (ev.which == 85)) {
							ev.preventDefault();
							self.page.shortcutReplySetAwaitingUser();
							return;
						}
						if (isCtrl && (ev.which == 65)) {
							ev.preventDefault();
							self.page.shortcutReplySetAwaitingAgent();
							return;
						}
						if (isCtrl && (ev.which == 68)) {
							ev.preventDefault();
							self.page.shortcutReplySetResolved();
							return;
						}
						if (isCtrl && (ev.which == 82)) {
							ev.preventDefault();
							self.page.shortcutSendReply();
							return;
						}
						if (isCtrl && (ev.which == 83)) {
							ev.preventDefault();
							window.setTimeout(function() {
								self.page.shortcutOpenSnippets();
							}, 10);
							ev.stopPropagation();
							return;
						}
						if (isCtrl && (ev.which == 79)) {
							ev.preventDefault();
							window.setTimeout(function() {
								self.page.shortcutReplyOpenProperties();
							}, 10);
							return;
						}
					}
				});
			}
			var heightUp = function() {
				textarea.addClass('touched');

				if (self.page && lastH != ed.height()) {
					var newH = ed.height();
					var hDiff = newH - lastH;
					lastH = newH;

					if (!self.page.meta.ticket_reverse_order) {
						self.page.doScrollBottom = true;
					}
					window.setTimeout(function() {
						if (self.page) {
							var sEl = self.page.wrapper.find('.layout-content').first().find('.scroll-viewport').first();
							if (sEl && sEl[0]) {
								sEl.get(0).scrollTop = sEl.get(0).scrollTop + hDiff;
							}

							var focus = textarea.getObject().getFocus();
							if (focus && focus[0]) {
								if (focus[0].nodeType == 3) {
									var focusEl = $(focus[0].parentNode);
								} else {
									var focusEl = $(focus[0]);
								}
								var focusPos = focusEl.offset();
								if (focusPos.top+focusEl.height() > $('#dp_window').height()) {
									self.page.updateUi(newH);
								}
							} else {
								self.page.updateUi();
							}
						}
					}, 60);
				}
			};
			ed.on('paste', function(ev) {
				heightUp();
			});
			ed.on('keypress change', function() {
				heightUp();
			});

			this._initAgentNotifier(textarea);
		}

		var translateControls = this.el.find('.translate-controls');
		if (translateControls[0]) {
			var transTrigger = translateControls.find('.trans-trigger');
			translateControls.find('select').on('change', function(ev) {
				var langId = $(this).val();
				var langTitle = $.trim($(this).find(':selected').text());
				transTrigger.find('.translate-lang').data('locale', langId).text(langTitle);
			});

			transTrigger.on('click', function(ev) {
				Orb.cancelEvent(ev);
				self.refreshMessageTranslation(transTrigger.find('.translate-lang').data('locale'));
			});

			var textarea2 = self.getElById('replybox_txt2');
			DeskPRO_Window.initRteAgentReply(textarea2, {
				defaultIsHtml: true,
				minHeight: 120,
				callback: function(obj) {
					obj.addBtn('dp_cancel_trans', 'Cancel message translation', function(){
						self.closeMessageTranslation();
					});
					obj.setBtnRight('dp_cancel_trans');

					var cancelTransBtn = obj.$toolbar.find('.redactor_btn_dp_cancel_trans').closest('li');
					cancelTransBtn.addClass('cancel_trans');
					cancelTransBtn.find('a').text('Cancel Translation');
				}
			});

			self.page.getEl('value_form').find('.language_id').on('change', function() {
				var langId     = $(this).val();
				if (!langId) {
					langId = DESKPRO_DEFAULT_LANG_ID;
				}

				var langLocale = DESKPRO_NAME_REGISTRY.lang_data[langId].locale;
				var langTitle  = $.trim(DESKPRO_NAME_REGISTRY.lang_data[langId].title);

				transTrigger.find('.translate-lang').data('locale', langLocale).text(langTitle);
			});
		}

		var wasAgentChecked = agentSelCheck.prop('checked');
		var wasTeamChecked  = teamSelCheck.prop('checked');

		var replyMode = 'reply';

		this.getElById('replybox_replytab_btn').on('click', function() {
			self.el.addClass('dp-reply-on');

			switch(replyMode) {
				case 'note':
					// process elements
          self.getElById('replybox_notetab_btn').removeClass('on');
          self.el.removeClass('dp-note-on');
          self.getElById('is_note').val('0');
          self.isNote = false;
          // process stored text
          if (textarea.data('redactor')) {
            storedNoteText = textarea.getCode();
            textarea.setCode(storedReplyText || '');
          }
					break;
				case 'fwd':
				  self.clearFwd();
          self.getElById('replybox_fwdtab_btn').removeClass('on');
          self.getElById('fwd_body').html('');
					self.el.removeClass('dp-fwd-on');
          self.fwdMessages = [];
          if (textarea.data('redactor')) {
            storedFWDText = textarea.getCode();
            textarea.setCode(storedReplyText || '');
          }
					break;
        case 'reply':
				default:
					//no-op
					return;
			}

      $('.show-fwd', self.el).hide();
      $('.show-reply', self.el).show();
      $('.show-note', self.el).hide();

      // process special stuff
      if (closeReply) {
        closeTabCheck.prop('checked', true);
      } else {
        closeTabCheck.prop('checked', false);
      }
      if (wasAgentChecked) {
        agentSelCheck.prop('checked', true);
      }
      if (wasTeamChecked) {
        teamSelCheck.prop('checked', true);
      }

      var actionsRow = self.getElById('actions_row');
      if (actionsRow.find('ul').find('li')[0]) {
        actionsRow.show();
      }

      $(this).addClass('on');
			replyMode = 'reply';
      self.hideAgentNotifyList();
		});

    this.getElById('replybox_notetab_btn').on('click', function() {

      switch(replyMode) {
        case 'reply':
          self.getElById('replybox_replytab_btn').removeClass('on');
					self.el.removeClass('dp-reply-on');
          if (textarea.data('redactor')) {
            storedReplyText = textarea.getCode();
            textarea.setCode(storedNoteText || '');
          }
          break;
        case 'fwd':
          self.clearFwd();
          self.getElById('replybox_fwdtab_btn').removeClass('on');
          self.getElById('fwd_body').html('');
					self.el.removeClass('dp-fwd-on');
          self.fwdMessages = [];
          if (textarea.data('redactor')) {
            storedFWDText = textarea.getCode();
            textarea.setCode(storedNoteText || '');
          }
          break;
        case 'note':
        default:
          // no-op
          return;
      }

			replyMode = 'note';
      $('.show-fwd', self.el).hide();
      $('.show-reply', self.el).hide();
      $('.show-note', self.el).show();
			self.getElById('actions_row').hide();
			self.el.addClass('dp-note-on');
			$(this).addClass('on');
			self.getElById('is_note').val('1');
			self.isNote = true;
			self.hideAgentNotifyList();

			if (closeNote) {
				closeTabCheck.prop('checked', true);
			} else {
				closeTabCheck.prop('checked', false);
			}
			wasAgentChecked = agentSelCheck.prop('checked');
			wasTeamChecked  = teamSelCheck.prop('checked');
			agentSelCheck.prop('checked', false);
			teamSelCheck.prop('checked', false);
		});

    this.getElById('replybox_fwdtab_btn').on('click', function() {
      switch(replyMode) {
        case 'reply':
          self.getElById('replybox_replytab_btn').removeClass('on');
					self.el.removeClass('dp-reply-on');

          if (textarea.data('redactor')) {
            storedReplyText = textarea.getCode();
            textarea.setCode(storedFWDText || '');
          }
          break;
        case 'note':
          self.getElById('replybox_notetab_btn').removeClass('on');

          self.el.removeClass('dp-note-on');
          self.getElById('is_note').val('0');
          self.isNote = false;
          // process stored text
          if (textarea.data('redactor')) {
            storedNoteText = textarea.getCode();
            textarea.setCode(storedFWDText || '');
          }
          break;
        case 'fwd':
        default:
          // no-op
          return;
      }
      $('.show-fwd', self.el).show();
      $('.show-reply', self.el).hide();
      $('.show-note', self.el).hide();
      replyMode = 'fwd';
      self.getElById('actions_row').hide();
      self.el.addClass('dp-fwd-on');
      $(this).addClass('on');
      self.hideAgentNotifyList();
      if(!self.dontDispatch && self.page) {
				self.page.handleFwd({mode: 'all'});
			}
			self.dontDispatch = false;
    });

		//------------------------------
		// Expanding cc row
		//------------------------------

		this.el.find('.expander').on('click', function() {
			var target = self.el.find($(this).data('target'));
			if (target.is(':visible')) {
				$(this).removeClass('expanded').addClass('is-hidden');
				target.slideUp('fast');
			} else {
				$(this).addClass('expanded').removeClass('is-hidden');
				target.slideDown('fast');
			}
		});

		var cc_add_wrap = this.getElById('newcc');
		var cc_del_wrap = this.getElById('delcc');

        var cc_row = this.getElById('cc_row');
        var cc_input = this.getElById('cc_input');
		var cc_user_rows = this.getElById('cc_user_rows');

        cc_row.autoCompleteElement = new DeskPRO.Agent.ElementHandler.SimpleAutoComplete(cc_row);
        this.cc_row_autoCompleteElement = cc_row.autoCompleteElement;
        this.ccRowTpl = '';
        var ccRemoveFunction = function() {
			var row = $(this).closest('.cc-user-row');
			var input = $('<input type="hidden" />');
			input.attr('name', 'delcc[]');
			input.val(row.data('email-address'));

			cc_del_wrap.append(input);
			row.remove();
        };
        $('.user-rows', cc_row).on('click', '.remove-row-trigger', ccRemoveFunction);

        $('.cc-saverow-trigger', cc_row).on('click', function(ev) {
			var user_row = $(self.ccRowTpl);
			var email = $.trim($('input.user-part', cc_row).val());
			var parts = email.split('@');

			if(email == ''
			|| parts.length != 2
			|| !parts[0]
			|| !parts[1]
			|| email.indexOf(',') != -1) {
				return;
			}

			var input = $('<input type="hidden" />');
			input.attr('name', 'addcc[]');
			input.val(email);
			$('input.user-part', cc_row).val('');

			cc_add_wrap.append(input);

			var newrow = $('<li />').addClass('cc-user-row').data('email', email);
			newrow.append('<span class="btn-small-remove remove-row-trigger" />');
			var span = $('<span class="user-email" />');
			span.text(email);
			newrow.append(' ');
			newrow.append(span);

			cc_user_rows.append(newrow);

			ev.stopPropagation();
			cc_row.autoCompleteElement.close();
		});

		//------------------------------
		// Upload handling
		//------------------------------

		DeskPRO_Window.util.fileupload(this.el, {
			dropZone: this.getElById('file_drop_zone'),
			uploadTemplate: $('.template-upload', this.el),
			downloadTemplate: $('.template-download', this.el),
      uploadUrlParameters: {
        tag: 'ticket_attachment'
      }
		});

		this.el.bind('fileuploaddone', function(attachInfo, data) {
			self.uploading = false;
			if (data) {
				self.fwdAttachments.push(data.result[0].blob_id);
			}
			self.getElById('reply_as_type').parent().removeAttr('disabled');
			self.getElById('reply_as_type').parent().siblings('.status-menu-trigger').removeAttr('disabled');
			self.getElById('attach_row').show().removeClass('is-hidden');
			if (self.page) {
				self.page.updateUi();
				if (!self.page.meta.ticket_reverse_order) {
					if (self.page.scrollHandlers && self.page.scrollHandlers[0]) {
						$(self.page.scrollHandlers[0]).data('scroll_handler').getElement().trigger('goscrollbottom_stick');
					}
				}
			}
		});
		this.el.bind('fileuploadstart', function() {
			self.uploading = true;
			self.getElById('reply_as_type').parent().attr('disabled', 'disabled');
			self.getElById('reply_as_type').parent().siblings('.status-menu-trigger').attr('disabled', 'disabled');
			self.getElById('attach_row').show().removeClass('is-hidden');
			if (self.page) {
				self.page.updateUi();
				if (self.page.scrollHandlers && self.page.scrollHandlers[0]) {
					if (!self.page.meta.ticket_reverse_order) {
						$(self.page.scrollHandlers[0]).data('scroll_handler').getElement().trigger('goscrollbottom_stick');
					}
				}
			}
		});

		this.el.on('click', '.remove-attach-trigger', function() {
      var blobId = parseInt($(this).prev('input').val(), 10);
      var row = $(this).closest('li');
      self.removeBlob(blobId, row);
		});

		//------------------------------
		// Toggle buttons
		//------------------------------

		$('.option-buttons', this.el).on('click', 'li.toggle', function() {
			if ($(this).hasClass('keep_open_toggle')) {
				return;
			}
			var check = $(':checkbox', this);
			if (!check.length) {
				return;
			}

			if (check.is(':checked')) {
				check.attr('checked', false);
				$(this).removeClass('on');
			} else {
				check.attr('checked', true);
				$(this).addClass('on');
			}
		});


		//------------------------------
		// Snippets Viewer
		//------------------------------

		if (window.DP_HAS_NEW_SNIPPETS) {
      snippetBtn.on('click', function () {
        self.openNewSnippets();
			});
    } else {
			this.snippetsViewer = new DeskPRO.Agent.Widget.SnippetViewer({
				driver: DeskPRO_Window.ticketSnippetDriver,
				triggerElement: snippetBtn,
				onBeforeOpen: function() {
					if (textarea.data('redactor')) {
						try {
							// this saves a begining of the reply as current position when the redactor is not in focus
							// textarea.data('redactor').saveSelection();
						} catch (e) {}
					}
				},
				onSnippetClick: function(info) {
					if (!self.page) {
						return;
					}

					var ticketLangId = self.page.getEl('value_form').find('.language_id').val();
					var snippetId    = info.snippetId;
					var snippetCode  = info.snippetCode;
					var vars         = self.page.meta.api_data;
					var result;
					var useText;

					var selectText = function(options, value_prop, lang_id_prop, fallback_text) {
						var agentText, defaultText, wantText, useText;

						options.forEach(function(info) {
							if (info[value_prop]) {
								if (info[lang_id_prop] == ticketLangId) {
									wantText = info[value_prop];
								}
								if (info[lang_id_prop] == DESKPRO_PERSON_LANG_ID) {
									agentText = info[value_prop];
								}
								if (info[lang_id_prop] == DESKPRO_DEFAULT_LANG_ID) {
									defaultText = info[value_prop];
								}
								useText = info[value_prop];
							}
						});

						if (wantText) {
							useText = wantText;
						} else if (agentText) {
							useText = agentText;
						} else if (defaultText) {
							useText = defaultText;
						} else if (fallback_text) {
							useText = fallback_text;
						}

						return useText;
					};

					useText = selectText(snippetCode, 'value', 'language_id');

					['department', 'product', 'category', 'workflow', 'priority'].forEach(function(prop) {
						if (vars[prop] && vars[prop]['title_translated']) {
							vars[prop]['title'] = selectText(vars[prop]['title_translated'], 'title', 'language_id', vars[prop]['title']);
						}
					});

					try {
						var tpl = twig({
							data: useText,
							strict_variables: false
						});
						if (tpl) {
							result = tpl.render({
								ticket: vars
							}, {
								strict_variables: false
							});
							if (!result) {
								result = useText;
							}
						} else {
							result = useText;
						}
					} catch(e) {
						console.log("Snippet render failed: %o", e);
						result = useText;
					}

					if (!result) result = '';

					if (textarea.data('redactor')) {
						try {
							textarea.data('redactor').restoreSelection();
							textarea.data('redactor').setBuffer();
						} catch (e) {}

						var html = result;
						html = html.replace(/<\/p>\s*<p>/g, '<br/>');
						html = html.replace(/^<p>/, '');
						html = html.replace(/<\/p>$/, '');
						textarea.data('redactor').insertHtml(html);
					}
					textarea.addClass('touched');
					self.recordSnippetUse(snippetId);

					self.snippetsViewer.close();
				}
			});
		}

		self.wasSnippetOpen = false;
		this.el.bind('page_deactivate', function() {
      if (window.DP_HAS_NEW_SNIPPETS) {
      	if (self.isSnippetOpen) {
      		var event = new CustomEvent('dpLeftDrawerClose');
					window.document.dispatchEvent(event);
					self.wasSnippetOpen = true;
          self.isSnippetOpen  = false;
				}
      } else {
				if (self.snippetsViewer && self.snippetsViewer.pop && self.snippetsViewer.pop.isOpen()) {
				 	self.snippetsViewer.close();
          self.wasSnippetOpen = true;
				}
			}
		});
		this.el.bind('page_activate', function() {
			if (self.wasSnippetOpen) {
        if (window.DP_HAS_NEW_SNIPPETS) {
					self.openNewSnippets();
        } else {
          self.snippetsViewer.open();
        }
			}
		});

		if (textarea.data('redactor')) {
			var ed = textarea.getEditor();
			var api = textarea.data('redactor');

			this.textExpander = new DeskPRO.TextExpander({
				textarea: ed,
				onCombo: function(combo, ev) {
					combo = combo.replace(/%/g, '');
					if (!window.DESKPRO_TICKET_SNIPPET_SHORTCODES || !window.DESKPRO_TICKET_SNIPPET_SHORTCODES[combo]) {
            return;
          }

          ev.preventDefault();

          for (var i = 0; i < window.DESKPRO_TICKET_SNIPPET_SHORTCODES[combo].length; i++) {
            var snippetId = window.DESKPRO_TICKET_SNIPPET_SHORTCODES[combo][i];

            var focus = api.getFocus(),
              focusNode = $(focus[0]),
              testText;

            if (focus[0].nodeType == 3) {
              testText = focusNode.text().substring(0, focus[1]);
            } else {
              focus[0] = focusNode.contents().get(focus[1] - 1);
              focusNode = $(focus[0]);
              testText = focusNode.text();
              focus[1] = testText.length;
            }

            var lastAt = testText.lastIndexOf('%'), matches = [];

            if (lastAt != -1) {
              api.setSelection(focus[0], lastAt, focus[0], focus[1]);
            }

            // web kit handles content editable without an issue. this prevents the span
            // from being extended unnecessarily
            var editable = $.browser.webkit ? ' contenteditable="false"' : '';
            api.insertHtml('<span class="editor-inserting-var snippet-' + snippetId + '" ' + editable + ' data-snippet-id="' + snippetId + '">Inserting snippet</span>');

            if (!self.page) {
              self.page = self.el.closest('.with-page-fragment').data('page-fragment');
            }

            self.page.pauseSend = true;

            if (window.DP_HAS_NEW_SNIPPETS) {
              var snippet = window.LegacyStoreProvider.getSnippets().get(snippetId);
              var blobs = window.LegacyStoreProvider.getSnippetBlobs();
              self.insertSnippet(snippet.toJS(), blobs.toJS());
              if (self.page) self.page.pauseSend = false;
            } else {
							$.ajax({
								url: BASE_URL + 'agent/text-snippets/tickets/' + snippetId + '.json',
								dataType: 'json',
								complete: function () {
									if (self.page) self.page.pauseSend = false;
								},
								success: function (data) {
									var snippet = data.snippet;
									var ticketLangId = self.page ? self.page.getEl('value_form').find('.language_id').val() : 0;
									var snippetId = snippet.id;
									var snippetCode = snippet.snippet;

									self.recordSnippetUse(snippetId);

									var agentText;
									var defaultText;
									var wantText;
									var useText;
									var result;

									snippetCode.forEach(function (info) {
										if (info.language_id == ticketLangId) {
											wantText = info.value;
										}
										if (info.language_id == DESKPRO_PERSON_LANG_ID) {
											agentText = info.value;
										}
										if (info.language_id == DESKPRO_DEFAULT_LANG_ID) {
											defaultText = info.value;
										}
										useText = info.value;
									});

									if (wantText) {
										useText = wantText;
									} else if (agentText) {
										useText = agentText;
									} else if (defaultText) {
										useText = defaultText;
									}

									try {
										var tpl = twig({
											data: useText,
											strict_variables: false
										});
										if (tpl) {
											result = tpl.render({
												ticket: self.page.meta.api_data
											}, {
												strict_variables: false
											});
											if (!result) {
												result = useText;
											}
										} else {
											result = useText;
										}
									} catch (e) {
										console.log("Snippet render failed: %o", e);
										result = useText;
									}


									var data = result;
									data = data.replace(/<\/p>\s*<p>/g, '<br/>');
									data = data.replace(/^<p>/, '');
									data = data.replace(/<\/p>$/, '');
									data = $('<div>' + data + '</div>');

									var el = api.$editor.find('.editor-inserting-var.snippet-' + snippetId);
									var cursor = $('<span class="_cursor"></span>');
									var cursorPos = data.find('> p');
									if (!cursorPos[0]) {
										cursorPos = data;
									}

									el.after(data);
									cursorPos.append(cursor);
									el.remove();

									api.setSelection(cursor[0], 0, cursor[0], 0);
									api.syncCode();
								}
							});
						}
          }
				}
			});
		}

		//------------------------------
		// Status
		//------------------------------

		var statusDetailEl = this.getElById('status_detail');
		this.statusMenu = new DeskPRO.UI.Menu({
			triggerElement: $('.status-trigger', statusDetailEl),
			menuElement: this.getElById('status_menu'),
			onItemClicked: function(info) {
				var item = $(info.itemEl);
				var val = item.data('status');

				if (val == 'no-change') {
					self.getElById('ticket_do_status').val(0);
					statusDetailEl.removeClass('changed');
				} else {
					$('.new-val-label', statusDetailEl).text($.trim(item.text()));
					self.getElById('ticket_do_status').val(1);
					self.getElById('ticket_status').val(val);
					statusDetailEl.addClass('changed');
				}
			}
		});

		//------------------------------
		// Assignments
		//------------------------------

		var setupSelects = function() {
      DP.select(agentSel);
      DP.select(teamSel);
      DP.select(jiraActionSel);
		};

    window.requestIdleCallback ?
      window.requestIdleCallback(setupSelects, {timeout: 1000}) :
      window.setTimeout(setupSelects, 500);;

		agentSel.on('change', function() {
			var option = agentSel.find(':selected');
			agentSelText.text(option.data('name-short'));
			agentSelText.css('background-image', 'url(' + option.data('icon')+ ')');
			agentSelCheck.prop('checked', true);

			if (agentSel.data('auto-switch-status')) {
				if (agentSelCheck.get(0).checked) {
					if (self.getElById('action').val().indexOf('macro') === -1) {
						self.setReplyAsOptionName('awaiting_agent');
					}
				}
			}
		});
		teamSel.on('change', function() {
			teamSelText.text($(this).find(':selected').text());
			teamSelCheck.prop('checked', true);
		});
        jiraActionSel.on('change', function() {
          jiraActionText.text($(this).find(':selected').text());
          jiraActionCheck.prop('checked', true);
        });

		var option = agentSel.find(':selected');
		agentSelText.text(option.data('name-short'));
		agentSelText.css('background-image', 'url(' + option.data('icon')+ ')');
		teamSelText.text(teamSel.find(':selected').text());
        jiraActionText.text(jiraActionSel.find(':selected').text());

		if (agentSel.data('auto-switch-status')) {
			agentSelCheck.on('change', function() {
				if (agentSelCheck.get(0).checked) {
					if (self.getElById('action').val().indexOf('macro') === -1) {
						self.setReplyAsOptionName('awaiting_agent');
					}
				}
			});
		}

		//------------------------------
		// Submit
		//------------------------------

		this.el.on('submit', function(ev) {
			ev.preventDefault();
			ev.stopPropagation();
		});

		this.el.find('.submit-trigger').on('click', function(ev) {
			if (self.uploading) {
				return;
			}
			if ($(this).attr('disabled')) {
				return;
			}
			ev.preventDefault();
			ev.stopPropagation();

			var api = textarea.data('redactor');
      if (api) {
				api.$editor.linkify();
				api.syncCode();
			}

			var copy = $.trim(self.el.find('.editor-row').find('.redactor_editor').text()).replace(/\s/g, ' ');
			var tmp = $('<div/>').html(self.el.find('textarea.signature-value-html').val());
			var sig = $.trim(tmp.text()).replace(/\s/g, ' ');

			if (!copy || copy == sig) {
				DeskPRO_Window.showAlert('Please enter a message.');
				return;
			}

			self.getElById('action').val(self.getElById('reply_as_type').data('type'));

			var formData = self.el.serializeArray();
			self.el.trigger('replyboxsubmit', [formData, self, {
				hasBillingControl: self.getElById('billing_reply')[0] ? true : false
			}]);
		});

		this.el.find('.fwd-trigger').on('click', function(ev) {
      ev.preventDefault();
      ev.stopPropagation();

      var api = textarea.data('redactor');
      if (api) {
        api.$editor.linkify();
        api.syncCode();
      }

      var formData = {
      	custom_message: api.getCode(),
				messages_ids:   self.fwdMessages,
				info: self.fwdInfo,
				to: {},
				to_type: {},
			  from: self.getElById('fwd_from').val(),
				subject: self.getElById('fwd_subject').val(),
        attachments: self.fwdAttachments
      };

      $.each(self.getElById('fwd_to_container').find('.email-address-input'), (function(index, item){
      	var $item = $(item);
				formData.to[$item.attr('id')] = $item.val();
				formData.to_type[$item.attr('id')] = $item.data('type');
			}));

      var loadingEl = self.el.find('.ticket-sending-overlay');
      loadingEl.fadeIn();

      $.ajax({
        url: '/agent/tickets/' + self.page.meta.ticket_id + '/forward/send',
        data: formData,
        type: 'POST',
        dataType: 'json',
        success: function(data) {
        	if (data && data.error) {
						switch (data.error) {
							case 'to_helpdesk_address':
								DeskPRO_Window.showAlert('The following addresses are helpdesk email accounts and cannot be used: ' + data.addresses.join(', '), 'error');
								break;
              case 'invalid_address':
                DeskPRO_Window.showAlert('The following email addresses are invalid: ' + data.addresses.join(', '), 'error');
                break;
							case 'missing_to':
                DeskPRO_Window.showAlert('At least one recipient is required', 'error');
								break;
							default:
                DeskPRO_Window.showAlert('There was a problem trying to send your message.', 'error');
						}
						return;
					}
          self.page.doTicketUpdate(true);
          // Reload message page to show `message forwarded` mark
          // need to call this manually because doTicketUpdate will not update page without new messages
          self.page.loadMessagePage(0, true);
          DeskPRO_Window.showAlert('Your forwarded message was successfully sent.');
          self.getElById('replybox_replytab_btn').click();
        },
				complete: function() {
          loadingEl.hide();
				}
      });

		});

		this.el.find('.fwd-control-add').on('click', function (ev) {
			self.addTo($(ev.target).data('add'), $(ev.target).data('set-email') || null);
    });
		this.addTo('to');

		this.getElById('keep_open_toggle').on('click', function(ev) {
			ev.preventDefault();
			if ($(this).hasClass('radio-on')) {
				$(this).removeClass('radio-on on');
			} else {
				$(this).addClass('radio-on on');
			}
		});

		//------------------------------
		// Status menu
		//------------------------------

		var statusMenuTrigger = this.el.find('.status-menu-trigger');
		var statusMenu = this.getElById('status_menu');
		var statusMacroList = statusMenu.find('.macro-list');
		var statusMacroListMap = null;
		var replyAsType = this.getElById('reply_as_type');

		var statusMenuMenu = this.statusMenuMenu = new DeskPRO.UI.Menu2(statusMenu, {
			positionBy: self.getElById('reply_btn_group'),
			onBeforeMenuOpen: function(info) {
				var statusMenu = info.statusMenu;
				var type = replyAsType.data('type');
				statusMenu.find('li').removeClass('cursor')
					.filter('[data-type]').removeClass('on')
					.filter('[data-type="' + type + '"]').addClass('on');

				var w = self.getElById('reply_btn_group').width() - 3;
				if (w < 200) {
					w = 200;
				}
				statusMenu.width(w);
			},
			onFilterUpdated: function(info) {
				var isCtrl = info.isCtrl;
				var ev = info.event;

				if (isCtrl && (ev.which == 85)) {
          statusMenuMenu.close();
					self.page.shortcutReplySetAwaitingUser();
					info.cancel = true;
					return;
				}
				if (isCtrl && (ev.which == 65)) {
          statusMenuMenu.close();
					self.page.shortcutReplySetAwaitingAgent();
					info.cancel = true;
					return;
				}
				if (isCtrl && (ev.which == 68)) {
          statusMenuMenu.close();
					self.page.shortcutReplySetResolved();
					info.cancel = true;
					return;
				}
			},
			onItemSelected: function(info) {
				var item = info.item;
				if (item.data('type')) {
					self.setReplyAsOption(item);
				}
			}
		});

		statusMenuTrigger.on('click', function(ev) {
			ev.preventDefault();
			if ($(this).attr('disabled')) {
				return;
			}

      statusMenuMenu.open();
		});

		this.onMacrosUpdated = function(ev) {
      ev.macroItems.forEach(function (info) {
        var has = statusMacroList.find('.res-ticketmacro-' + info.id);
        if (has[0]) {
          return;
        }

        var li = $('<li><div class="on-icon"><i class="fas fa-check"></i></div><span class="macro-title"></span></li>');
        if (self.page) {
          li.data('get-macro-url', BASE_URL + 'agent/tickets/' + self.page.meta.ticket_id + '/ajax-get-macro?macro_id=' + info.id + '&macro_reply_context=1');
        }
        li.data('label', 'Send Reply and ' + info.title);
        li.data('type', 'macro:' + info.id);
        li.attr('data-type', 'macro:' + info.id);
        li.find('.macro-title').text(info.title);

        statusMacroList.append(li);
      });
    };
		$('#settingswin').on('dp_macros_updated', this.onMacrosUpdated);


		if (this.page) {
			this.page.setTicketReplyBox(this);

			// If agent/team already set to the default values, dont precheck (makes it a bit clearer that nothing would change)
			if (agentSel.val() == this.page.getEl('value_form').find('.agent_id').val()) {
				agentSelCheck.prop('checked', false);
			}
			if (teamSel.val() == this.page.getEl('value_form').find('.agent_team_id').val()) {
				teamSelCheck.prop('checked', false);
			}
		}

		// Init macro title list
		if (!window.DESKPRO_MACRO_LABELS) {
			window.DESKPRO_MACRO_LABELS = [];
			statusMacroListMap = {};
			statusMacroList.find('li').each(function() {
				// the weird casting here is to make sure numeric
				// titles are treated as a string. jquery will convert data-macro-title="123" into an int which has no toLowerCase
				var label = (($(this).data('macro-title') || '')+'').toLowerCase();
				var macro_id = parseInt($(this).data('macro-id'));
				window.DESKPRO_MACRO_LABELS.push([macro_id, label.toLowerCase()]);
				statusMacroListMap[macro_id] = this;
			});
		}

		// Depending on perms, the note tab might be already on
		// And we need ot run certain other hide/show actions
		if (this.el.hasClass('dp-note-on')) {
			this.getElById('replybox_notetab_btn').click();
		}
	},

  addTo: function(type, setEmail) {
    var copy = this.getElById('template > .to-line').clone();
    var container = this.getElById('fwd_to_container');
    var header = copy.find('.label > span');
    var input = copy.find('.email-address-input');
    var wrap = copy.find('.email-address-wrap').removeClass('with-handler').removeClass('with-display-handler');
    wrap.on('personsearchboxclick', function (event, personId, name, email, box) {
      input.val(email);
      box.close();
    });
    var id = Orb.getUniqueId();

    wrap.data('position-bound', '#'+id);
    input.attr('id', id);
    switch(type) {
      case 'to':
        input.data('type', 'to');
        header.text('To:');
        break;
      case 'cc':
        input.data('type', 'cc');
        header.text('CC:');
        break;
      case 'bcc':
        input.data('type', 'bcc');
        header.text('BCC:');
        break;
      default:
        return;
    }

    copy.find('.fwd_removerow').on('click', (function() {
    	this.removeTo(copy);
		}).bind(this));

    if (setEmail) {
      input.val(setEmail);
    }

    container.append(copy);
    DeskPRO.ElementHandler_Exec(this.el);

    var xbtns = container.find('.fwd_removerow');
    if (xbtns.length === 1) xbtns.hide();
    else xbtns.show();
  },

	removeTo: function (row) {
		row.remove();
    var container = this.getElById('fwd_to_container');
    var xbtns = container.find('.fwd_removerow');
    if (xbtns.length === 1) xbtns.hide();
    else xbtns.show();
  },

	setReplyAsOptionName: function(name) {
		var item = this.getElById('status_menu').find('li[data-type="' + name + '"]').first();
		if (item[0]) {
			this.setReplyAsOption(item);
		}
	},

	setReplyAsOption: function(item) {
		var replyAsType = this.getElById('reply_as_type');

		var html = Orb.escapeHtml(item.data('label'));
		html = html.replace(/^Send Reply/, 'Send <span class="show-key-shortcut">R</span>eply');
		replyAsType.data('type', item.data('type')).html(html);

		var macroUrl = item.data('get-macro-url');

		var textarea = this.textarea;
		var api = this.textarea.data('redactor');

		if (this.el.data('resolve-auto-close')) {
			if (item.data('type') == 'resolved') {
				this.getElById('close_tab_opt').prop('checked', true);
			}
		}

		if (!macroUrl) {
			this.getElById('actions_row').hide();
			if (this.page) {
				this.page.updateUi();
				if (!this.page.meta.ticket_reverse_order) {
					this.page.wrapper.find('div.layout-content').trigger('goscrollbottom');
				}
			}
		} else {
			var actionsRow = this.getElById('actions_row');
			var actionsRowList = actionsRow.find('ul');
			actionsRowList.empty();
			actionsRowList.append('<li class="load"><i class="flat-spinner"></i></li>');

			actionsRow.show();

			if (this.page) {
				this.page.updateUi();
				if (!this.page.meta.ticket_reverse_order) {
					this.page.wrapper.find('div.layout-content').trigger('goscrollbottom');
				}
			}

			$.ajax({
				url: macroUrl,
				type: 'GET',
				context: this,
				dataType: 'json',
				success: function(data) {
					actionsRowList.empty();
					data.descriptions.forEach(function(desc) {
						var li = $('<li />');
						li.html(desc);

						actionsRowList.append(li);
					});

					// There's a snippet reply point
					var sig = null;
					if (api) {
						sig = api.$editor.find('.dp-signature-start');
						if (!sig[0]) {
							sig = null;
						}
					}

					actionsRowList.find('.with-reply, .with-snippet').each(function() {
						var pos = $(this).data('reply-pos');
						var html = $(this).find('.reply-text').get(0).innerHTML;

						if (pos) {
							if (api) {
								if (pos == 'overwrite') {
									api.$editor.html(html);
									if (sig) {
										api.$editor.append(sig);
									}
								} else if (pos == 'prepend') {
									api.$editor.prepend(html);
								} else {
									if (sig) {
										var usesig = sig;
										var prev = sig.prev();
										if (prev[0] && prev.is('p') && $.trim(prev.text()) === '') {
											usesig = prev;
											var prev2 = prev.prev();
											if (prev2[0] && prev2.is('p') && $.trim(prev2.text()) === '') {
												prev2.remove()
											}
										}
										usesig.before(html);
									} else {
										api.$editor.append(html);
									}
								}

								api.syncCode();
							} else {
								var text = $('<div>' + html + '</div>');
								text = $.trim(text.text());
								textarea.val($.trim(textarea.val() + "\n\n" + text));
							}
						}
					});

					var agentId = parseInt(actionsRowList.find('.with-agent').data('agent-id'));
					if (agentId) {
						if (agentId == -1) {
							agentId = DESKPRO_PERSON_ID;
						}

						this.getElById('agent_sel').select2('val', agentId);
						this.getElById('agent_sel').change();
					}
					var agentTeamId = parseInt(actionsRowList.find('.with-agent-team').data('agent-team-id'));
					if (agentTeamId) {
						if (agentTeamId == -1) {
							if (!window.DESKPRO_TEAM_IDS || !window.DESKPRO_TEAM_IDS.length) {
								agentTeamId = null;
							} else {
								agentTeamId = window.DESKPRO_TEAM_IDS[0];
							}
						}

						if (agentTeamId) {
							this.getElById('agent_team_sel').select2('val', agentTeamId);
							this.getElById('agent_team_sel').change();
						}
					}

					if (actionsRowList.find('.with-close-tab')) {
						this.getElById('close_tab_opt').prop('checked', true);
					}

					if (this.page) {
						this.page.updateUi();
						if (!this.page.meta.ticket_reverse_order) {
							this.page.wrapper.find('div.layout-content').trigger('goscrollbottom');
						}
					}
				}
			});
		}

		if (this.page) {
			this.page.focusOnReply()
		}
	},

	hideAgentNotifyList: function() {
		DeskPRO_Window.hideAgentNotifyList(this);
	},

	_initAgentNotifier: function(textarea) {
		DeskPRO_Window.initAgentNotifierForRte(this, textarea, false);
	},

	getElById: function(id) {
		var el = $('#' + this.baseId + '_' + id);
		return el;
	},

	addCc: function(email) {
		var input = $('.token-input input', this.getElById('cc_input'));
		input.val(email).focus().blur();
	},

	removeCc: function(email) {
		$('.token-x', this.getElById('cc_input')).each(function() {
			if ($(this).data('for-value') == email) {
				$(this).click();
			}
		});
	},

	appendToMessage: function(content) {
		var textarea = this.getElById('replybox_txt');
		var isWysiwyg = textarea.data('redactor') ? true : false;

		var sig = this.getElById('signature_value').val();

		if (isWysiwyg) {
			sig = DP.convertTextToWysiwygHtml(sig, true);
			content = DP.convertTextToWysiwygHtml(content, true);

			var val = textarea.getCode();
			if (val == '<p></p>' || val == '<p><br></p>') {
				val = '';
			}
		} else {
			var val = textarea.val();
		}

		if ($.trim(val).length) {

			// Always put it before the signature
			// (if have sig and val ends with sig)
			if (sig.length && val.indexOf(sig, val.length - sig.length) !== -1) {
				val = content + val;
			} else {
				val += " ";
				val += content;
			}
		} else {
			val = content;
		}

		if (isWysiwyg) {
			textarea.setCode(val);
		} else {
			textarea.val(val);
		}
	},

  recordSnippetUse: function(snippetId) {
    var el = $("#" + this.page.meta.baseId + "_snippet_ids");
    var current = el.val() || '';
    var newval = current.length ? current + ',' + snippetId : snippetId+'';
    el.val(newval);
  },

  registerCloseSnippetViewer: function() {
    this.isSnippetOpen = false;
  },

	openNewSnippets: function() {
		var self = this;
    var departmentId = 0;
    if (self.page.meta.api_data.department) {
      departmentId = self.page.meta.api_data.department.id;
    }
    var helpDeskLang = window.DP_DEFAULT_LANG_ID;
    var ticketLang = self.page.meta.ticket.language ? self.page.meta.ticket.language.id : null;
    var personLang = self.page.meta.ticket.person.language ? self.page.meta.ticket.person.language.id : null;
    ticketLang = ticketLang || personLang || helpDeskLang;

    var event = new CustomEvent('dpLeftDrawer', {detail: {
      module: 'SnippetsMenu',
      department: departmentId,
      width: 745,
      langId: ticketLang,
      insertSnippet: self.insertSnippet.bind(self),
      onClose: self.registerCloseSnippetViewer.bind(self)
    }});
    window.document.dispatchEvent(event);
    self.isSnippetOpen = true;
	},

	insertSnippet: function(snippet, blobs, langId) {
    var ticketLangId = this.page ? this.page.getEl('value_form').find('.language_id').val() : 0;
    if (langId) {
      ticketLangId = langId;
    }
    var vars         = this.page.meta.api_data;

    var selectText = function(options, value_prop, lang_id_prop, fallback_text) {
      var agentText, defaultText, wantText, useText;

      options.forEach(function(info) {
        if (info[value_prop]) {
          if (info[lang_id_prop] == parseInt(ticketLangId, 10)) {
            wantText = info[value_prop];
          }
          if (info[lang_id_prop] === DESKPRO_PERSON_LANG_ID) {
            agentText = info[value_prop];
          }
          if (info[lang_id_prop] === DESKPRO_DEFAULT_LANG_ID) {
            defaultText = info[value_prop];
          }
          useText = info[value_prop];
        }
      });

      if (wantText) {
        useText = wantText;
      } else if (agentText) {
        useText = agentText;
      } else if (defaultText) {
        useText = defaultText;
      } else if (fallback_text) {
        useText = fallback_text;
      }

      return useText;
    };

    ['department', 'product', 'category', 'workflow', 'priority'].forEach(function(prop) {
      if (vars[prop] && vars[prop]['title_translated']) {
        vars[prop]['title'] = selectText(vars[prop]['title_translated'], 'title', 'language_id', vars[prop]['title']);
      }
    });
    window.LegacySnippetInserter.insertSnippet(
      snippet,
      blobs,
      ticketLangId,
      vars,
      'ticket',
      this.textarea,
      this.attachBlobs.bind(this),
      this.recordSnippetUse.bind(this)
    );
    this.isSnippetOpen = false;
	},

  attachBlobs: function(blobs, source) {
    var self = this;
    var $attachRow = this.getElById('attach_row');
    blobs.forEach(function (info) {
      var blob = source[info];
      if (blob) {
        var html = window.tmpl($('.template-download', self.page.wrapper).attr('id'))({files: [blob]});
        $attachRow.find('ul.files:first').append(html);
      }
    });
    $attachRow.slideDown().removeClass('is-hidden');
  },

	refreshMessageTranslation: function(to) {
		var self       = this;
		var previewRow = this.el.find('.translate-row');

		if (!to) {
			this.closeMessageTranslation();
			return;
		}

		var textarea = this.textarea;
		var api = this.textarea.data('redactor');

		var formData = {
			from: 'me',
			to: to,
			message_text: textarea.val()
		};

		var translateControls = this.el.find('.translate-controls');
		translateControls.addClass('dp-loading-on');
		$.ajax({
			url: window.DESKPRO_TRANSLATE_SERVICE.translate_text_url,
			data: formData,
			type: 'POST',
			dataType: 'json',
			complete: function() {
				translateControls.removeClass('dp-loading-on');
			},
			success: function(data) {
				previewRow.show();

				self.getElById('replybox_txt2').data('redactor').setCode(formData.message_text);
				self.getElById('replybox_txt').data('redactor').setCode(data.message);
				self.getElById('reply_is_trans').val(to);
			}
		});
	},

	closeMessageTranslation: function() {
		var previewRow = this.el.find('.translate-row');
		previewRow.hide();

		this.getElById('replybox_txt').data('redactor').setCode(this.getElById('replybox_txt2').data('redactor').getCode());
		this.getElById('replybox_txt2').data('redactor').setCode('');
		this.getElById('reply_is_trans').val('');
	},

	destroy: function() {
		this.page = null;
		var textarea = this.getElById('replybox_txt');
		this.textarea = null;
		if (textarea.data('redactor')) {
      textarea.getEditor().off();
			try {
				textarea.destroyEditor();
			} catch (e) {}
		}
		if (this.agentNotifyList) {
			this.agentNotifyList.remove();
      this.agentNotifyList = null;
		}
		if (window.DP_HAS_NEW_SNIPPETS) {
      if (self.isSnippetOpen) {
        var event = new CustomEvent('dpLeftDrawerClose');
        window.document.dispatchEvent(event);
      }
    }
		if (this.snippetsViewer) {
			this.snippetsViewer.destroy();
			this.snippetsViewer = null;
		}
		if (this.cc_row_autoCompleteElement) {
			this.cc_row_autoCompleteElement.destroy();
			this.cc_row_autoCompleteElement = null;
		}
		if (this.textExpander) {
			this.textExpander.destroy();
			this.textExpander = null;
		}
		if (this.statusMenu) {
			this.statusMenu.destroy();
			this.statusMenu = null;
		}
    if (this.statusMenuMenu) {
      this.statusMenuMenu.destroy();
      this.statusMenuMenu = null;
    }
    if (this.onMacrosUpdated) {
      $('#settingswin').off('dp_macros_updated', this.onMacrosUpdated);
      this.onMacrosUpdated = null;
		}

    this.destroyEl();
	},

	clearFwd: function() {
	  var self = this;
		this.getElById('fwd_body').html('');
		this.fwdMessages = [];
		this.fwdAttachments = [];
    this.fwdMode = null;
    this.getElById('attach_row').find('li.in').map(function(index, row){
      var $row = $(row);
      self.removeBlob($row.find('input').eq(0).val(), $row);
    });
	},

	appendFwdCollection: function(messages) {
		var self = this;
		var tpl = [];
    tpl.push('<div><strong>From:</strong> <span class="name-part"></span> &lt;<span class="email-part"></span>&gt;</div>');
    tpl.push('<div><strong>Date:</strong> <span class="datetime-part"></span></div>');
    tpl.push('<div><strong>Subject:</strong> <span class="subject-part"></span></div>');
    tpl.push('<br/>');
    tpl.push('<table border="0" cellspacing="0" cellpadding="3">');
    tpl.push('	<tr><td>');
    tpl.push('		<table border="0" cellspacing="0" cellpadding="0" width="100%"><tr><td>');
    tpl.push('			<div data-dp-type="blockquote" class="dp-quoted-message"></div>');
    tpl.push('		</td></tr></table>');
    tpl.push('	</td></tr>');
    tpl.push('</table>');
    tpl.push('<br/><br/>');
    tpl = $(tpl.join("\n"));

    var rows = [];

		messages.forEach(function(m) {
			var row = tpl.clone();
      row.find('.datetime-part').text(moment(m.date).format('dddd, MMMM Do YYYY, h:mm:ss a'));
      row.find('.name-part').text(m.author.name);
      row.find('.email-part').text(m.author.email);
      row.find('.subject-part').text(self.page.meta.title);
			row.find('.dp-quoted-message').html(m.bodyHtml);

      self.fwdMessages.push(m.messageId);
      rows.push(row);
		});

    this.getElById('fwd_body').append('<div>---------- Forwarded Message ----------</div>');
    this.getElById('fwd_body').append(rows);
	},

	appendFwdAttach: function(element, ticket) {
    var blobId = element.data('blob-id');
    if(-1 === this.fwdAttachments.indexOf(blobId)) {
      var attachInfo = {
        "blob_id":           blobId,
        "blob_auth":         element.data('blob-auth'),
        "blob_auth_id":      element.data('blob-auth-id'),
        "download_url":      element.data('deskpro-url'),
        "filename":          element.data('filename'),
        "filesize_readable": element.data('filesize-readable'),
        "is_image":          element.data('is-image')
      };
      this.fwdAttachments.push(blobId);
      ticket.addAttachToList(attachInfo, true);
		}
	},

  setFwdMode: function(info) {
		this.fwdInfo = info;
	},

	removeBlob: function(blobId, row) {
    $(this).trigger('blobremove', [blobId]);
    var self = this;
    row.fadeOut('fast', function() {
			row.remove();
			var blobIndex = self.fwdAttachments.indexOf(blobId);
			if (blobIndex !== -1) {
			  delete(self.fwdAttachments[blobIndex]);
      }
			var rows = $('ul.files li', self.getElById('attach_row'));
      if (!rows.length) {
        self.getElById('attach_row').hide().addClass('is-hidden');
        if (self.page) {
          self.page.updateUi();
          if (!self.page.meta.ticket_reverse_order) {
            if (self.page.scrollHandlers && self.page.scrollHandlers[0]) {
              $(self.page.scrollHandlers[0]).data('scroll_handler').getElement().trigger('goscrollbottom_stick');
            }
          }
        }
      }
    });
	},

	focusReplyBox: function() {
    var api = this.textarea.data('redactor');
    if (!api || !api.$editor) return;

    api.$editor.focus();
	}
});
