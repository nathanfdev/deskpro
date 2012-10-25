Orb.createNamespace('DeskPRO.Agent.ElementHandler');

DeskPRO.Agent.ElementHandler.TicketReplyBox = new Orb.Class({
	Extends: DeskPRO.ElementHandler,

	init: function() {
		this.baseId = this.el.data('base-id');
		this.headerRows = $('')
	},

	initPage: function() {
		var self = this;
		this.page = this.el.closest('div.replybox-wrap').data('page');
		var sig = this.el.find('textarea.signature-value').val();
		var sigTrimmed = false;

		var textarea = this.getElById('replybox_txt'), isWysiwyg = false;

		if (false) {
			isWysiwyg = true;

			var val = textarea.val();
			if (val.length) {
				textarea.val(DP.convertTextToWysiwygHtml(val));
			}

			textarea.redactor({
				direction: textarea.attr('dir') || 'ltr',
				buttons: ['html', '|', 'bold', 'italic', '|',  'unorderedlist', 'orderedlist', 'outdent', 'indent', '|', 'image', 'link', '|', 'alignment'],
				minHeight: 150,
				observeImages: false,
				imageUpload: BASE_URL + 'agent/misc/accept-redactor-image-upload',
				imageUploadCallback: function(obj, json) {
					var templateEl = $('.template-download', self.el);
					if (!templateEl.attr('id')) {
						templateEl.attr('id', Orb.getUniqueId('up'));
					}

					var template = window.tmpl(templateEl.attr('id'));
					var results = template({
						files: [json]
					});
					$(self.el).find('.files').append(results);

					self.el.trigger('fileuploaddone');
				},
				imageUploadErrorCallback: function(obj, json) {
					alert(json.error);
				}
			});

			this.el.bind('fileremoved', function(ev, li) {
				var downloadUrlRegex = li.find('a').attr('href').replace('.', '\\.');
				console.log("url: " + downloadUrlRegex);
				if (downloadUrlRegex) {
					var html = textarea.getCode(),
						regex1 = new RegExp('<p><img[^>]+src="' + downloadUrlRegex + '"[^>]*></p>', 'g'),
						regex2 = new RegExp('<img[^>]+src="' + downloadUrlRegex + '"[^>]*>', 'g');

					html = html.replace(regex1, '').replace(regex2, '');
					textarea.setCode(html);
				}
			});

			sig = DP.convertTextToWysiwygHtml(sig);

			this.getElById('is_html_reply').val(1);
		} else {
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

		this.getElById('replybox_replytab_btn').on('click', function() {
			self.el.removeClass('dp-note-on');
			$(this).addClass('on');
			self.getElById('replybox_notetab_btn').removeClass('on');
			$('.hide-note:not(.is-hidden)', self.el).show();
			$('.hide-reply', self.el).hide();
			self.getElById('is_note').val('0');

			if (sigTrimmed) {
				if (isWysiwyg) {
					var reply = textarea.getCode();
					if (sig.length) {
						if (!reply.length) {
							reply = '<p><br></p>';
						}
						textarea.setCode(reply + "\n\n" + sig);
					}
				} else {
					var reply = textarea.val();
					textarea.val(reply + "\n\n" + sig);
				}
				sigTrimmed = false;
			}
		});

		this.getElById('replybox_notetab_btn').on('click', function() {
			self.el.addClass('dp-note-on');
			$(this).addClass('on');
			self.getElById('replybox_replytab_btn').removeClass('on');
			$('.hide-note', self.el).hide();
			$('.hide-reply', self.el).show();
			self.getElById('is_note').val('1');

			if (isWysiwyg) {
				var reply = textarea.getCode();
				if (Orb.strEndsWith(reply, sig)) {
					var pos = reply.indexOf(sig);
					reply = $.trim(reply.substring(0, pos));
					textarea.setCode(reply);
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
			newrow.append(span);

			cc_user_rows.append(newrow);

			ev.stopPropagation();
			cc_row.autoCompleteElement.close();
		});

		//------------------------------
		// Upload handling
		//------------------------------

		DeskPRO_Window.util.fileupload(this.el, {
			uploadTemplate: $('.template-upload', this.replyBox),
			downloadTemplate: $('.template-download', this.replyBox)
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
			triggerElement: this.getElById('text_snippets_btn'),
			onBeforeOpen: function() {
				if (isWysiwyg) {
					textarea.data('redactor').saveSelection();
				}
			},
			onSnippetClick: function(info) {
				if (!self.page) {
					return;
				}

				if (isWysiwyg) {
					textarea.data('redactor').restoreSelection();
				}

				self.page.insertTextInReply(info.snippet);
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

		//------------------------------
		// Submit
		//------------------------------

		this.el.on('submit', function(ev) {
			ev.preventDefault();
			ev.stopPropagation();
		});

		this.getElById('send_btn').on('click', function(ev) {
			ev.preventDefault();
			ev.stopPropagation();

			if (isWysiwyg) {
				textarea.data('redactor').syncCode();
			}

			var formData = self.el.serializeArray();
			self.el.trigger('replyboxsubmit', [formData, self]);
		});

		this.getElById('keep_open_toggle').on('click', function(ev) {
			ev.preventDefault();
			$(this).toggleClass('radio-on');
		});
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
			sig = DP.convertTextToWysiwygHtml(sig);
			content = DP.convertTextToWysiwygHtml(content);

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

	}
});
