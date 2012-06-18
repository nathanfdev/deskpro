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

		this.getElById('replybox_replytab_btn').on('click', function() {
			$(this).addClass('on');
			self.getElById('replybox_notetab_btn').removeClass('on');
			$('.hide-note:not(.is-hidden)', self.el).show();
			$('.hide-reply', self.el).hide();
			self.getElById('is_note').val('0');
		});

		this.getElById('replybox_notetab_btn').on('click', function() {
			$(this).addClass('on');
			self.getElById('replybox_replytab_btn').removeClass('on');
			$('.hide-note', self.el).hide();
			$('.hide-reply', self.el).show();
			self.getElById('is_note').val('1');
		});

		this.getElById('replybox_txt').TextAreaExpander(150, 550).on('textareaexpander_expanded', function() {
			window.setTimeout(function() {
				self.page.wrapper.find('div.layout-content').trigger('goscrollbottom');
			}, 250);
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

        var cc_row = this.getElById('cc_row');
        var cc_input = this.getElById('cc_input');
        cc_row.autoCompleteElement = new DeskPRO.Agent.ElementHandler.SimpleAutoComplete(cc_row);
        this.ccRowTpl = DeskPRO_Window.util.getPlainTpl($('.email-row-tpl', cc_row));
        var ccRemoveFunction = function() {
            var my_row = $(this);
            var email = $('.user-email', my_row).val();
            var emails = cc_input.val().split(',');
            var new_value = '';

            for(var i = 0;i < emails.length; i++) {
                if(email != emails[i]
                    && emails[i] != '') {
                    new_value += emails[i] + ',';
                }
            }

            cc_input.val(new_value);
            my_row.parent().remove();
        };
        $('.user-rows', cc_row).on('click', '.remove-row-trigger', ccRemoveFunction);

        $('.cc-saverow-trigger', cc_row).on('click', function(ev) {
                var user_row = $(self.ccRowTpl);
                var email = $('.user-part', cc_row).val().trim();
                var parts = email.split('@');

                if(email == ''
                || parts.length != 2
                || !parts[0]
                || !parts[1]
                || email.indexOf(',') != -1) {
                    return;
                }

                cc_input.val(email+','+cc_input.val());
                $('.user-rows', cc_row).append(user_row);
                $('.user-email', user_row).text(email);
                $('.user-part', cc_row).val('');
                ev.stopPropagation();
                cc_row.autoCompleteElement.close();
            }
        );
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
			self.getElById('attach_row').slideDown().removeClass('is-hidden');;
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
			onSnippetClick: function(info) {
				if (!self.page) {
					return;
				}

				self.page.insertTextInReply(info.snippet);
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

		DP.select(agentSel);
		DP.select(teamSel);
		DP.select(statusSel);

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

			var formData = self.el.serializeArray();
			self.el.trigger('replyboxsubmit', [formData, self]);
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
		var sig = this.getElById('signature_value').val();
		var val = this.getElById('replybox_txt').val();
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
		this.getElById('replybox_txt').val(val);
	},

	destroy: function() {

	}
});
