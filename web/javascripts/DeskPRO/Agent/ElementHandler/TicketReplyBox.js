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

		var agentDetailEl  = this.getElById('assign_detail_agent');
		var teamDetailEl   = this.getElById('assign_detail_agent_team');
		var followDetailEl = this.getElById('assign_detail_followers');

		//assign_btn
		this.assignOptionBox = new DeskPRO.UI.OptionBoxRevertable({
			element: this.getElById('agent_selector'),
			trigger: this.getElById('assign_btn'),
			onSave: function(ob) {
				var selections = ob.getAllSelected();

				var exist_agent_id      = parseInt(self.getElById('exist_agent_id').val());
				var exist_agent_team_id = parseInt(self.getElById('exist_agent_team_id').val());

				// Agent
				var agent_id = parseInt(selections.agents || 0);
				if (agent_id == exist_agent_id) {
					agentDetailEl.removeClass('changed');
					self.getElById('do_agent_id').val('0');
				} else {
					agentDetailEl.addClass('changed');
					self.getElById('do_agent_id').val('0');
					self.getElById('agent_id').val(agent_id);

					var label = $('.agent-label-' + agent_id, self.getElById('agent_selector')).text().trim();
					$('.new-val-label', agentDetailEl).text(label);
				}

				// Agent Team
				var agent_team_id = parseInt(selections.teams || 0);
				if (agent_team_id == exist_agent_team_id) {
					teamDetailEl.removeClass('changed');
					self.getElById('do_agent_team_id').val('0');
				} else {
					teamDetailEl.addClass('changed');
					self.getElById('do_agent_team_id').val('1');
					self.getElById('agent_team_id').val(agent_team_id);

					var label = $('.agent-team-label-' + agent_team_id, self.getElById('agent_selector')).text().trim();
					$('.new-val-label', teamDetailEl).text(label);
				}

				// Followers
				var follower_names = [];
				var inputs = $('.inputs', followDetailEl).empty();

				Array.each(selections.followers, function(part_id) {
					var label = $('.agent-part-label-' + part_id, self.getElById('agent_selector')).text().trim();
					follower_names.push(label);

					var i = $('<input type="hidden" name="agent_parts[]" value="'+part_id+'" />');
					inputs.append(i);
				});
				if (follower_names.length) {
					$('.no-followers', followDetailEl).hide();
					var f = $('.is-followers', followDetailEl).show();
					f.find('.names').text(follower_names.join(', '));
					f.find('.count').text(follower_names.length);
				} else {
					$('.no-followers', followDetailEl).show();
					$('.is-followers', followDetailEl).text('').hide();
				}
			}
		});

		//assign_btn
		var noteFollowDetailEl = this.getElById('assign_followers_detail');

		this.assignOptionBox = new DeskPRO.UI.OptionBoxRevertable({
			element: this.getElById('agent_followers_selector'),
			trigger: this.getElById('assign_followers_btn'),
			onSave: function(ob) {
				var selections = ob.getAllSelected();

				// Followers
				var follower_names = [];
				var inputs = $('.inputs', noteFollowDetailEl).empty();

				Array.each(selections.followers, function(part_id) {
					var label = $('.agent-part-label-' + part_id, self.getElById('agent_selector')).text().trim();
					follower_names.push(label);

					var i = $('<input type="hidden" name="agent_parts[]" value="'+part_id+'" />');
					inputs.append(i);
				});
				if (follower_names.length) {
					$('.no-followers', noteFollowDetailEl).hide();
					var f = $('.is-followers', noteFollowDetailEl).show();
					f.find('.names').text(follower_names.join(', '));
					f.find('.count').text(follower_names.length);
				} else {
					$('.no-followers', noteFollowDetailEl).show();
					$('.is-followers', noteFollowDetailEl).text('').hide();
				}
			}
		});

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
