Orb.createNamespace('DeskPRO.Agent.ElementHandler');

DeskPRO.Agent.ElementHandler.TicketReplyBox = new Orb.Class({
	Extends: DeskPRO.ElementHandler,

	init: function() {
		this.baseId = this.el.data('base-id');
		this.agentNotifyListShown = false;
	},

	initPage: function() {
		var self = this;
		this.page = this.el.closest('.with-page-fragment').data('page-fragment');
		this.page.ticketReplyBox = this;
		var sigTrimmed = false;

		var textarea = this.getElById('replybox_txt'), isWysiwyg = false;
		this.textarea = textarea;

		this.isNote = false;
		var snippetBtn = null;

		if (DeskPRO_Window.canUseAgentReplyRte()) {
			var sig = this.el.find('textarea.signature-value-html').val();
			sig = sig.replace(/<div class="dp-signature-start">([\w\W]*)<\/div>/, '<p class="dp-signature-start">$1</p>');

			var draft = this.getElById('draft_html');
			if (draft.length) {
				textarea.val(draft.val());
			} else if (sig) {
				textarea.val(($.browser.msie ? '<p></p><p></p>' : '<p><br></p><p><br></p>') + '\n\n' + sig);
			}

			isWysiwyg = true;

			DeskPRO_Window.initRteAgentReply(textarea, {
				defaultIsHtml: true,
				inlineHiddenPosition: this.getElById('is_html_reply'),
				autosaveContent: 'ticket',
				autosaveContentId: (this.page ? this.page.meta.ticket_id : false),
				preAutosaveCallback: function(textarea, data) {
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
					obj.addBtnFirst('dp_attach', 'Attach a file', function(){});
					obj.addBtnAfter('dp_attach', 'dp_snippets', 'Open snippets', function(){});
					obj.addBtnSeparatorAfter('dp_attach');
					obj.addBtnSeparatorAfter('dp_snippets');

					snippetBtn = obj.$toolbar.find('.redactor_btn_dp_snippets').closest('li');
					snippetBtn.addClass('snippets').find('a').html('<span class="show-key-shortcut">S</span>nippets');

					var attachBtn = obj.$toolbar.find('.redactor_btn_dp_attach').closest('li');
					attachBtn.addClass('attach');
					attachBtn.find('a').text('Attach').append('<input type="file" class="file" name="file-upload" />');
				}
			});
			this.getElById('is_html_reply').val(1);

			if (textarea.data('redactor')) {
				var ed = textarea.getEditor();
				var lastH = ed.height();
				ed.on('keyup', function(ev) {
					var isCtrl = false;
					if (ev.ctrlKey && DeskPRO_Window.keyboardShortcuts.isMac) {
						isCtrl = true;
					} else if (ev.altKey) {
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
				ed.on('keypress change', function() {
					textarea.addClass('touched');

					if (self.page && lastH != ed.height()) {
						lastH = ed.height();
						self.page.doScrollBottom = true;
						window.setTimeout(function() {
							if (self.page) {
								self.page.updateUi();
							}
						}, 50);
					}
				});

				this._initAgentNotifier(textarea);
			}
		} else {
			var sig = this.el.find('textarea.signature-value').val();
			if (sig) {
				textarea.val('\n\n' + sig);
			}

			textarea.data('expander-max-height', $(window).height() - 500).TextAreaExpander(150, $(window).height() - 500).on('textareaexpander_expanded', function() {
				var h = $(this).height();
				window.setTimeout(function() {
					if (self.page && $(window).height() - 500 > h) {
						self.page.wrapper.find('div.layout-content').trigger('goscrollbottom');
					}
				}, 250);
			});

			textarea.on('keypress change', function() {
				$(this).addClass('touched');
			});
		}

		var keepOpenBtn   = this.getElById('keep_open_toggle');
		var keepOpenReply = this.el.data('close-reply') == '1' ? true : false;
		var keepOpenNote  = this.el.data('close-note') == '1' ? true : false;

		this.getElById('replybox_replytab_btn').on('click', function() {
			self.el.removeClass('dp-note-on');
			$(this).addClass('on');
			self.getElById('replybox_notetab_btn').removeClass('on');
			$('.hide-note:not(.is-hidden)', self.el).show();
			$('.hide-reply', self.el).hide();
			self.getElById('is_note').val('0');
			self.isNote = false;
			self.hideAgentNotifyList();

			if (keepOpenReply) {
				keepOpenBtn.addClass('radio-on on');
			} else {
				keepOpenBtn.removeClass('radio-on on');
			}

			if (sigTrimmed) {
				if (isWysiwyg && textarea.data('redactor')) {
					var reply = textarea.getCode();
					if (sig.length) {
						reply = reply.replace(/\s*(<p>(<br\s*\/?>)?<\/p>\s*)*$/, '');
						if (!reply.length) {
							reply += ($.browser.msie ? '<p></p><p></p>' : '<p><br></p><p><br></p>');
						} else {
							reply += ($.browser.msie ? '<p></p>' : '<p><br></p>');
						}
						textarea.setCode(reply + "\n\n" + sig);
					}
				} else {
					var reply = textarea.val();
					textarea.val(reply + "\n\n" + sig);
				}
				sigTrimmed = false;
			}

			if (self.page) {
				var scroller = self.page.wrapper.find('div.layout-content');
				scroller.data('scroll_handler').updateSize();
				scroller.trigger('goscrollbottom');
			}
		});

		this.getElById('replybox_notetab_btn').on('click', function() {
			self.el.addClass('dp-note-on');
			$(this).addClass('on');
			self.getElById('replybox_replytab_btn').removeClass('on');
			$('.hide-note', self.el).hide();
			$('.hide-reply', self.el).show();
			self.getElById('is_note').val('1');
			self.isNote = true;
			self.hideAgentNotifyList();

			if (keepOpenNote) {
				keepOpenBtn.addClass('radio-on on');
			} else {
				keepOpenBtn.removeClass('radio-on on');
			}

			if (isWysiwyg && textarea.data('redactor')) {
				var reply = textarea.getCode();
				var newReply = reply.replace(/<(p|div) class="dp-signature-start">[\w\W]*$/, '');
				if (newReply != reply) {
					textarea.setCode(newReply);
					sigTrimmed = true;
				}
			} else {
				var reply = textarea.val();
				if (Orb.strEndsWith(reply, sig)) {
					var pos = reply.indexOf(sig);
					reply = $.trim(reply.substring(0, pos));
					textarea.val(reply);
					sigTrimmed = true;
				}
			}

			if (self.page) {
				var scroller = self.page.wrapper.find('div.layout-content');
				scroller.data('scroll_handler').updateSize();
				scroller.trigger('goscrollbottom');
			}
		});

		if (this.el.data('default-is-note') == '1') {
			this.el.addClass('dp-note-on');
			this.getElById('replybox_notetab_btn').addClass('on');
			this.getElById('replybox_replytab_btn').removeClass('on');
			$('.hide-note', this.el).hide();
			$('.hide-reply', this.el).show();
			this.getElById('is_note').val('1');
			this.isNote = true;
			this.hideAgentNotifyList();
		}

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
        this.ccRowTpl = DeskPRO_Window.util.getPlainTpl($('.email-row-tpl', cc_row));
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
			var email = $('input.user-part', cc_row).val().trim();
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
			dropZone: $('.option-rows', this.el),
			uploadTemplate: $('.template-upload', this.el),
			downloadTemplate: $('.template-download', this.el)
		});
		this.el.bind('fileuploaddone', function() {
			self.getElById('attach_row').slideDown().removeClass('is-hidden');
		});
		this.el.bind('fileuploadstart', function() {
			self.getElById('attach_row').slideDown().removeClass('is-hidden');
		});

		this.el.on('click', '.remove-attach-trigger', function() {

			var row = $(this).closest('li');
			row.fadeOut('fast', function() {
				row.remove();

				var rows = $('ul.files li', self.getElById('attach_row'));
				if (!rows.length) {
					self.getElById('attach_row').slideUp().addClass('is-hidden');
				}
			});
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

		this.snippetsViewer = new DeskPRO.Agent.Widget.SnippetViewer({
			viewUrl: this.el.data('snippet-viewer-url'),
			triggerElement: snippetBtn,
			onBeforeOpen: function() {
				if (isWysiwyg && textarea.data('redactor')) {
					textarea.data('redactor').saveSelection();
				}
			},
			onSnippetClick: function(info) {

				if (!self.page) {
					return;
				}

				if (isWysiwyg && textarea.data('redactor')) {
					try {
						textarea.data('redactor').restoreSelection();
					} catch (e) {}
					textarea.data('redactor').setBuffer();
					textarea.data('redactor').insertHtml(info.snippetHtml);
				} else {
					self.page.insertTextInReply(info.snippet);
				}
			}
		});

		self.wasSnippetOpen = false;
		this.el.bind('page_deactivate', function() {
			if (self.snippetsViewer && self.snippetsViewer.pop && self.snippetsViewer.pop.isOpen()) {
				self.snippetsViewer.close();
				self.wasSnippetOpen = true;
			}
		});
		this.el.bind('page_activate', function() {
			if (self.wasSnippetOpen) {
				self.snippetsViewer.open();
			}
		});

		if (textarea.data('redactor')) {
			var ed = textarea.getEditor();
			var api = textarea.data('redactor');

			var te = new DeskPRO.TextExpander({
				textarea: ed,
				onCombo: function(combo, ev) {
					combo = combo.replace(/%/g, '');
					if (window.DESKPRO_TICKET_SNIPPET_SHORTCODES && window.DESKPRO_TICKET_SNIPPET_SHORTCODES[combo]) {
						ev.preventDefault();

						var snippetId = window.DESKPRO_TICKET_SNIPPET_SHORTCODES[combo];

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

						var	lastAt = testText.lastIndexOf('%'), matches = [];

						if (lastAt != -1) {
							api.setSelection(focus[0], lastAt, focus[0], focus[1]);
						}

						// web kit handles content editable without an issue. this prevents the span
						// from being extended unnecessarily
						var editable = $.browser.webkit ? ' contenteditable="false"' : '';
						api.insertHtml('<p><span class="editor-inserting-var snippet-'+snippetId+'" ' + editable + ' data-snippet-id="' + snippetId + '">Inserting snippet...</span></p>');

						$.ajax({
							url: BASE_URL + 'agent/tickets/' + self.page.meta.ticket_id + '/get-snippet/' + snippetId,
							dataType: 'text',
							success: function(data) {
								var el = api.$editor.find('.editor-inserting-var.snippet-' + snippetId);
								el.after(data);
								el.remove();
							}
						});
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
					$('.new-val-label', statusDetailEl).text(item.text().trim());
					self.getElById('ticket_do_status').val(1);
					self.getElById('ticket_status').val(val);
					statusDetailEl.addClass('changed');
				}
			}
		});

		//------------------------------
		// Assignments
		//------------------------------

		var agentSel  = this.getElById('agent_sel');
		var teamSel   = this.getElById('agent_team_sel');
		var statusSel = this.getElById('status_sel');

		window.setTimeout(function() {
			DP.select(agentSel);
			DP.select(teamSel);
			DP.select(statusSel);
		}, 150);

		var hasSwitched = false;

		if (agentSel.data('auto-switch-status')) {
			agentSel.one('change', function() {
				if (hasSwitched) return;
				hasSwitched = true;
				statusSel.select2('val', 'awaiting_agent');
			});
		}
		if (teamSel.data('auto-switch-status')) {
			teamSel.one('change', function() {
				if (hasSwitched) return;
				hasSwitched = true;
				statusSel.select2('val', 'awaiting_agent');
			});
		}
		statusSel.one('change', function() {
			hasSwitched = true;
		});

		if (statusSel.data('resolve-auto-close')) {
			statusSel.on('change', function() {
				var val = $(this).val();

				if (val == 'resolved' && !self.getElById('keep_open_toggle').hasClass('on')) {
					self.getElById('keep_open_toggle').click();
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
			ev.preventDefault();
			ev.stopPropagation();

			if (isWysiwyg && textarea.data('redactor')) {
				textarea.data('redactor').syncCode();
			}

			self.getElById('action').val(self.getElById('reply_as_type').data('type'));

			var formData = self.el.serializeArray();
			self.el.trigger('replyboxsubmit', [formData, self]);
		});

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
		var footerEl = this.el.find('footer').first();
		var statusMenu = this.getElById('status_menu');
		var statusMenuH = null;
		var statusBackdrop = null;
		var statusMacroFilter = null;
		var statusMacroList = null;
		var statusListItems = null;
		var replyAsType = this.getElById('reply_as_type');

		var closeStatusMenu = function() {
			statusBackdrop.hide();
			statusMenu.hide();
		};

		var updateStatusPos = function() {
			statusMenuH = statusMenu.height();
			if (statusMenu > 500) {
				statusMenu.find('macro-list').css('max-height', 500).css('overflow', 'auto');
				statusMenuH = 500;
			}

			var pos = footerEl.offset();
			statusMenu.css({
				left: pos.left + 6,
				top: pos.top - statusMenuH + 3
			});
		};

		var openStatusMenu = function() {
			statusListItems = statusMenu.find('li[data-type]').not('.off');

			// Means we're opening fo rhte first time
			if (!statusBackdrop) {
				statusBackdrop = $('<div class="backdrop"></div>');
				statusBackdrop.appendTo('body');
				statusBackdrop.on('click', function(ev) {
					ev.stopPropagation();
					closeStatusMenu();
				});
				statusMenu.detach().appendTo('body');

				// Handle macro filtering
				statusMacroFilter = statusMenu.find('.macro-filter');
				statusMacroList = statusMenu.find('.macro-list');

				statusMenu.on('click', 'li[data-type]', function(ev) {
					ev.stopPropagation();
					self.setReplyAsOption($(this));
					closeStatusMenu();
				});

				statusMacroFilter.on('keyup', function(ev) {

					var isCtrl = false;
					if (ev.ctrlKey && DeskPRO_Window.keyboardShortcuts.isMac) {
						isCtrl = true;
					} else if (ev.altKey) {
						isCtrl = true;
					}
					if (isCtrl) {
						if (isCtrl && (ev.which == 85)) {
							closeStatusMenu();
							self.page.shortcutReplySetAwaitingUser();
							return;
						}
						if (isCtrl && (ev.which == 65)) {
							closeStatusMenu();
							self.page.shortcutReplySetAwaitingAgent();
							return;
						}
						if (isCtrl && (ev.which == 68)) {
							closeStatusMenu();
							self.page.shortcutReplySetResolved();
							return;
						}
					}

					if (ev.keyCode == 13 /* enter key */) {
						ev.preventDefault();
						var current = statusListItems.filter('.cursor');
						if (current[0]) {
							self.setReplyAsOption(current);
							closeStatusMenu();
						}
					} else if (ev.keyCode == 27 /* escape key */) {
						ev.preventDefault();
						closeStatusMenu();
					} else if (ev.keyCode == 40 /* down key */ || ev.keyCode == 38 /* up key */) {
						ev.preventDefault();
						var dir = ev.keyCode == 40 ? 'down' : 'up';

						var current = statusListItems.filter('.cursor');
						if (!current.length) {
							if (dir == 'down') {
								statusListItems.first().addClass('cursor');
							} else {
								statusListItems.last().addClass('cursor');
							}
						} else {
							var nextIndex = statusListItems.index(current);
							if (dir == 'down') {
								nextIndex++;
							} else {
								nextIndex--;
							}

							if (nextIndex < 0) {
								nextIndex = statusListItems.length-1;
							} else if (nextIndex > (statusListItems.length-1)) {
								nextIndex = 0;
							}

							current.removeClass('cursor');
							statusListItems.eq(nextIndex).addClass('cursor');
						}
					}
				});

				statusMacroFilter.on('keyup', function() {
					var val = $.trim($(this).val());

					if (!val) {
						statusMacroList.find('li').show().removeClass('off');
						updateStatusPos();
					} else {
						val = val.toLowerCase();
						statusMacroList.find('li').each(function() {
							if ($(this).text().toLowerCase().indexOf(val) !== -1) {
								$(this).show().removeClass('off');
							} else {
								$(this).hide().addClass('off');
							}
						});
						updateStatusPos();
					}

					statusListItems = statusMenu.find('li[data-type]').not('.off');
					if (!statusListItems.filter('.cursor')[0]) {
						statusMenu.find('li.cursor').removeClass('cursor');
						statusListItems.first().addClass('cursor');
					}
				});
			}

			// Pre-select proper value
			var type = replyAsType.data('type');
			statusMenu.find('li').removeClass('cursor')
				.filter('[data-type]').removeClass('on')
				.filter('[data-type="' + type + '"]').addClass('on');

			var w = self.getElById('reply_btn_group').width() - 3;
			if (w < 200) {
				w = 200;
			}
			statusMenu.width(w);

			statusBackdrop.show();
			updateStatusPos();
			statusMenu.show();

			statusMacroFilter.focus();
		};

		this.openStatusMenu = openStatusMenu;

		statusMenuTrigger.on('click', function(ev) {
			ev.preventDefault();
			openStatusMenu();
		});
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

		var api = this.textarea.data('redactor');
		api.$editor.find('.editor-text-insertion-point').remove();

		if (!macroUrl) {
			this.getElById('actions_row').hide();
			this.page.updateUi();
			this.page.wrapper.find('div.layout-content').trigger('goscrollbottom');
		} else {
			var actionsRow = this.getElById('actions_row');
			var actionsRowList = actionsRow.find('ul');
			actionsRowList.empty();
			actionsRowList.append('<li class="load"><i class="flat-spinner"></i></li>');

			actionsRow.show();

			this.page.updateUi();
			this.page.wrapper.find('div.layout-content').trigger('goscrollbottom');

			$.ajax({
				url: macroUrl,
				type: 'GET',
				context: this,
				dataType: 'json',
				success: function(data) {
					actionsRowList.empty();
					Array.each(data.descriptions, function(desc) {
						var li = $('<li />');
						li.html(desc);

						actionsRowList.append(li);
					});

					// There's a snippet reply point
					var sig = api.$editor.find('.dp-signature-start');
					if (!sig[0]) {
						sig = null;
					}

					actionsRowList.find('.with-reply, .with-snippet').each(function() {
						var pos = $(this).data('reply-pos');
						var html = $(this).find('.reply-text').get(0).innerHTML;

						if (pos) {
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
						}
					});

					var agentId = parseInt(actionsRowList.find('.with-agent').data('agent-id'));
					if (agentId) {
						if (agentId == -1) {
							agentId = DESKPRO_PERSON_ID;
						}

						this.getElById('agent_sel').select2('val', agentId);
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
						}
					}

					if (actionsRowList.find('.with-close-tab')) {
						this.getElById('close_tab_opt').prop('checked', true);
					}

					this.page.updateUi();
					this.page.wrapper.find('div.layout-content').trigger('goscrollbottom');
				}
			});
		}

		this.page.focusOnReply()
	},

	hideAgentNotifyList: function() {
		DeskPRO_Window.hideAgentNotifyList(this);
		/*if (this.agentNotifyList && this.agentNotifyListShown) {
			this.agentNotifyList.empty().hide();
			this.agentNotifyListShown = false;
		}*/
	},

	_initAgentNotifier: function(textarea) {
		DeskPRO_Window.initAgentNotifierForRte(
			this, textarea,
			this.page && this.page.meta.agentMap ? this.page.meta.agentMap : false
		);
	},

	/*_initAgentNotifier: function(textarea) {
		var api = textarea.data('redactor');
		if (!api) {
			return;
		}

		var ed = textarea.getEditor();
		var self = this;

		var agentMap = (this.page && this.page.meta.agentMap ? this.page.meta.agentMap : false);
		if (!agentMap) {
			return;
		}

		var agentMapLower = {}, hasAgents = false;
		Object.each(agentMap, function(data, agentId) {
			hasAgents = true;
			agentMapLower[agentId] = data.name.toLowerCase();
		});

		if (!hasAgents) {
			return;
		}

		this.agentNotifyList = $('<ul />').addClass('message-agent-notify-list').hide().appendTo(document.body);

		var insertAgentNotify = function(agentId) {
			if (typeof agentMap[agentId] === 'undefined') {
				return;
			}

			self.hideAgentNotifyList();

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

			var	lastAt = testText.lastIndexOf('@'),
				matches = [];

			if (lastAt != -1) {
				api.setSelection(focus[0], lastAt, focus[0], focus[1]);
			}

			// web kit handles content editable without an issue. this prevents the span
			// from being extended unnecessarily
			var editable = $.browser.webkit ? ' contenteditable="false"' : '';
			api.insertHtml('<span' + editable + ' data-notify-agent-id="' + agentId + '">@' + Orb.escapeHtml(agentMap[agentId].name) + '</span>&nbsp;');
		};

		this.agentNotifyList.on('mousedown', 'li', function(e) {
			e.preventDefault();
			insertAgentNotify($(this).data('agent-id'));
		});

		ed.on('click blur', function() {
			if (self.isNote) {
				self.hideAgentNotifyList();
			}
		});

		ed.on('keydown', function(e) {
			if (!self.isNote) {
				self.hideAgentNotifyList();
				return;
			}

			switch (e.keyCode) {
				case 38: // up
				case 40: // down
				case 13: // enter
					if (!self.agentNotifyList.is(':visible')) {
						return;
					}
					break;

				default:
					return;
			}

			e.preventDefault();

			if (e.keyCode == 13) { // enter - inserting the selected
				var li = self.agentNotifyList.find('li.selected');
				if (!li.length) {
					li = self.agentNotifyList.find('li:first');
				}

				insertAgentNotify(li.data('agent-id'));
			} else if (e.keyCode == 40) { // down - moves down the list
				var li = self.agentNotifyList.find('li.selected');
				if (!li.length) {
					self.agentNotifyList.find('li:first').addClass('selected');
				} else {
					li.removeClass('selected');
					var next = li.next('li');
					if (next.length) {
						next.addClass('selected');
					} else {
						self.agentNotifyList.find('li:first').addClass('selected');
					}
				}
			} else if (e.keyCode == 38) { // up - moves up the list
				var li = self.agentNotifyList.find('li.selected');
				if (!li.length) {
					self.agentNotifyList.find('li:last').addClass('selected');
				} else {
					li.removeClass('selected');
					var prev = li.prev('li');
					if (prev.length) {
						prev.addClass('selected');
					} else {
						self.agentNotifyList.find('li:last').addClass('selected');
					}
				}
			}
		});

		ed.on('keyup', function(e) {
			if (!self.isNote) {
				return;
			}

			if (e.ctrlKey || e.metaKey) {
				return;
			}

			switch (e.keyCode) {
				case 16: // shift
				case 17: // ctrl
				case 18: // alt
				case 19: // pause/break
				case 20: // caps lock
				case 91: // left windows
				case 92: // right windows
				case 93: // select
				case 224: // apple key
					return;

				case 13: // enter
				case 38: // up
				case 40: // down
					// these don't hide as that messes up the keydown handler
					e.stopImmediatePropagation();
					e.preventDefault();
					return;

				case 9: // tab
				case 27: // esc
				case 33: // page up
				case 34: // page down
				case 35: // end
				case 36: // home
				case 37: // left
				case 39: // right
					self.hideAgentNotifyList();
					return;

				default:
					// function keys and other special ones
					if (e.keyCode >= 112 && e.keyCode <= 145) {
						self.hideAgentNotifyList();
						return;
					}
			}

			var focus = api.getFocus(),
				origin = api.getOrigin();

			if (focus[0] != origin[0] || focus[1] != origin[1]) {
				// selected multiple points, don't show
				self.hideAgentNotifyList();
				return;
			}

			var	focusNode = $(focus[0]),
				testText = focus[0].nodeType == 3 ? focusNode.text().substring(0, focus[1]) : $(focusNode.contents().get(focus[1] - 1)).text(),
				lastAt = testText.lastIndexOf('@'),
				matches = [];

			if (lastAt != -1 && (lastAt == 0 || testText[lastAt - 1].match(/^(\s|[\.!?:;,()<>|/-])$/))) {
				var afterAt = testText.substring(lastAt + 1, testText.length).toLowerCase();

				if (afterAt.length >= 2 && afterAt.length < 75) {
					Object.each(agentMap, function(data, agentId) {
						if (agentMapLower[agentId].indexOf(afterAt) == 0) {
							matches.push(agentId);
						}
					});
				}
			}

			if (matches.length) {
				var selectedId = self.agentNotifyList.find('li:selected').data('agent-id');

				self.agentNotifyList.empty();
				for (var i = 0; i < matches.length; i++) {
					var li = $('<li>')
						.text(agentMap[matches[i]].name)
						.css('background-image', 'url('+agentMap[matches[i]].picture_url+')')
						.data('agent-id', matches[i]);
					if (matches[i] === selectedId) {
						li.addClass('selected');
					}
					self.agentNotifyList.append(li);
				}

				if (!self.agentNotifyList.find('li:selected').length) {
					self.agentNotifyList.find('li:first').addClass('selected');
				}

				var containingNode = focus[0].nodeType == 3 ? focusNode.parent() : focusNode;
				if (!containingNode.is('div, p, li, ul, ol, blockquote, table, body')) {
					containingNode = containingNode.closest('div, p, li, ul, ol, blockquote, table, body');
				}
				var offset = containingNode.offset();
				self.agentNotifyList.css({
					top: offset.top - self.agentNotifyList.outerHeight() - 1,
					left: offset.left
				});

				self.agentNotifyList.show();
				self.agentNotifyListShown = true;
			} else {
				self.hideAgentNotifyList();
			}
		});

		// this is important as I need this keyup handler to run before redactor's own because of new line handling
		ed.data('events').keyup.reverse();
	},*/

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

		if (val.trim().length) {

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

	destroy: function() {
		var textarea = this.getElById('replybox_txt');
		if (textarea.data('redactor')) {
			textarea.destroyEditor();
		}
		if (this.agentNotifyList) {
			this.agentNotifyList.remove();
		}
	}
});
