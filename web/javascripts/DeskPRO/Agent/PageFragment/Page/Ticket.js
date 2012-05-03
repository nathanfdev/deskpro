Orb.createNamespace('DeskPRO.Agent.PageFragment.Page');

DeskPRO.Agent.PageFragment.Page.Ticket = new Orb.Class({

	Extends: DeskPRO.Agent.PageFragment.Basic,

	initializeProperties: function() {
		this.parent();
		this.TYPENAME = 'ticket';
		this.wrapper = null;
		this.changeManager = null;
		this.valueForm = null;
		this.layout = null;
		this.popout = null;
		this.popout_overview = null;
		this.isMouseOverPopout = false;
		this.hasInitPopout = false;
		this.popoutPage = null;
		this.lastActiveDate = null;
	},

	initPage: function(el) {
		var self = this;
		this.getEl('replybox_wrap').data('page', this);

		this.valueForm = $('form.value-form:first', this.wrapper);
		this.valueForm.on('submit', function(ev) {
			// Never actually submit the form (would load a new page)
			ev.preventDefault();
		});

		this.changeManager = new DeskPRO.Agent.Ticket.ChangeManager(this);
		this.changeManager.addEvent('updateResult', function() {
			DeskPRO_Window.getMessageBroker().sendMessage('agent.ui.ticket_updated', { ticket_id: self.meta.ticket_id });
		});

		this.ticketDisplay = new DeskPRO.Agent.PageHelper.TicketDisplay(this, {
			wrapper: el
		});
		this.ownObject(this.ticketDisplay);

		this._initCustomFieldsEditor();

		this._initMessage($('.messages-wrap'));

		this._initTicketActionsMenu();
		this._initMessageActionsMenu();
		this._initLabels();

		if (this.meta.ticket_perms['delete']) {
			if (this.meta.isDeleted) {
				$('button.undelete-trigger', this.wrapper).on('click', this.doTicketUndelete.bind(this));
			}
			if (this.meta.isSpam) {
				$('button.unspam-trigger', this.wrapper).on('click', this.doTicketUnspam.bind(this));
			}
		}

		DeskPRO_Window.getMessageBroker().sendMessage('ui.ticket.opened', { ticketId: this.getMetaData('ticket_id') });
		DeskPRO_Window.getMessageBroker().sendMessage('ui.tab.opened', { type: 'tickets', id: this.getMetaData('ticket_id') });

		DeskPRO_Window.getMessageBroker().addMessageListener('tickets.deleted', (function(ticket_ids) {
			if (ticket_ids.indexOf(this.getMetaData('ticket_id')) !== -1) {
				DeskPRO_Window.removePage(this);
			}
		}).bind(this), this.pageUid);

		DeskPRO_Window.getMessageBroker().addMessageListener('tickets.new-messages.' + this.getMetaData('ticket_id'), this.getNewTicketMessages.bind(this), this.pageUid);

		this.addEvent('shortcutFocusReply', function(ev) {

			ev.preventDefault();

			// Scroll down
			self.wrapper.find('div.layout-content').trigger('goscrollbottom');

			// Focus reply
			$('textarea[name="message"]', self.ticketReply).focus();
		});

		$('.ticket-urgency', this.wrapper).on('mouseover', function() {
			Tipped.show(this);
		});

		var showMessages = $('input.show-messages', this.wrapper);
		var showAttach = $('input.show-attach', this.wrapper);
		var showNotes = $('input.show-notes', this.wrapper);
		var showLogs = $('input.show-logs', this.wrapper);
		var msgWrap = this.getEl('messages_wrap');

		function updateMessageTypes() {
			var messages = showMessages.is(':checked');
			var notes = showNotes.is(':checked');
			var logs = showLogs.is(':checked');
			var attach = showAttach.is(':checked');

			if (!messages && !notes && !logs && !attach) {
				messages = true;
				showMessages.attr('checked', true);
			}

			if (attach) {
				$('.attachment-list', msgWrap).show();
			} else {
				$('.attachment-list', msgWrap).hide();
			}

			if (messages) {
				$('article.message:not(.note-message)', msgWrap).show();
				$('.attachment-lone', msgWrap).hide();
			} else {
				$('article.message:not(.note-message)', msgWrap).hide();
				if (attach) {
					$('.attachment-lone', msgWrap).show();
				}
			}
			if (notes) {
				$('article.note-message', msgWrap).show();
			} else {
				$('article.note-message', msgWrap).hide();
			}
			if (logs) {
				$('div.log-row', msgWrap).show();
				$('div.log-batch', msgWrap).show();
			} else {
				$('div.log-row', msgWrap).hide();
				$('div.log-batch', msgWrap).hide();
			}

			self.updateUi();
		};

		$('.tickets-msg-controls input', this.wrapper).on('click', function() {
			updateMessageTypes();
		});

		if (this.meta.ticket_perms.modify_merge) {
			this.getEl('merge_trigger').on('click', function() {
				var mergeOverlay = new DeskPRO.Agent.Widget.MergeTicket({
					ticketId: self.getMetaData('ticket_id'),
					destroyOnClose: true,
					onMergeSuccess: function(data) {

						// remove old tabs, theyre outdated
						Array.each(DeskPRO_Window.getTabWatcher().findTabType('ticket'), function(tab) {
							var tid = tab.page.getMetaData('ticket_id');
							if (tid == data.old_ticket_id || tid == data.ticket_id) {
								DeskPRO_Window.TabBar.removeTabById(tab.id);
							}
						});

						DeskPRO_Window.runPageRoute('ticket:' + BASE_URL + 'agent/tickets/' + data.ticket_id);

						mergeOverlay.close();
					}
				});
				mergeOverlay.open();
			});

			$('form.ticket-reply-form', this.getEl('replybox_wrap')).bind('replyboxsubmit', this.handleReplySave.bind(this));
		}

		this.ticketActions = new DeskPRO.Agent.PageFragment.Page.Ticket.TicketActions(this);
		this.ownObject(this.ticketActions);

		if (this.meta.isLocked) {
			this.ticketLocked = new DeskPRO.Agent.PageFragment.Page.Ticket.TicketLocked(this);
			this.ownObject(this.ticketLocked);
		}

        $('.agent-link.other-agent', this.El).on('click', function() {
            DeskPRO_Window.sections.agent_chat_section.newChatWindow([$(this).data('agent-id')]);
        });

		this.rescanMessageTypes = function() {
			if ($('.attachment-list', msgWrap).length) {
				self.getEl('msgcheck_attach').show();
			} else {
				self.getEl('msgcheck_attach').hide();
			}

			if ($('article.note-message', msgWrap).length) {
				self.getEl('msgcheck_notes').show();
			} else {
				self.getEl('msgcheck_notes').hide();
			}
		};
		this.rescanMessageTypes();
	},

	handleReplySave: function(ev, formData, handler) {

		var reply_form = handler.el;

		formData.push({
			name: 'client_messages_since',
			value: DeskPRO_Window.getLastClientMessageId()
		});

		formData.push({
			name: 'last_message_id',
			value: this.getLastMessageId()
		});
		formData.push({
			name: 'last_log_id',
			value: this.getEl('messages_wrap').find('.log-row').last().data('log-id')
		});

		$.ajax({
			url: reply_form.attr('action'),
			type: 'POST',
			dataType: 'json',
			data: formData,
			context: this,
			success: function(result) {
				this.handleTicketUpdate(result);

				if (result.close_tab) {
					window.setTimeout((function() {
						this.closeSelf();
					}).bind(this), 400);
				} else if (!result.dupe_message) {
					// Apply changed props
					var agentProp = this.changeManager.getPropertyManager('agent_id');
					agentProp.setIncomingValue(result.agent_id);

					var agentTeamProp = this.changeManager.getPropertyManager('agent_team_id');
					agentTeamProp.setIncomingValue(result.agent_team_id);

					var statusProp = this.changeManager.getPropertyManager('status');
					statusProp.setIncomingValue(result.status);
				}

				this.rescanMessageTypes();
			},
			complete: function(xhr, textStatus) {
				reply_form.removeClass('loading');
			}
		});
	},

	getLastMessageId: function() {
		var id = this.getEl('messages_wrap').find('article.message').last().data('message-id');
		return id || 0;
	},

	getLastLogId: function() {
		return parseInt($('.log-row', this.getEl('messages_wrap')).last().data('log-id') || 0);
	},

	destroyPage: function() {
		DeskPRO_Window.getMessageBroker().sendMessage('ui.ticket.closed', { ticketId: this.getMetaData('ticket_id') });
	},

	handleTicketUpdate: function(data) {

		if (data.dupe_message) {
			// If its a dupe then it'd already be added ot the message list,
			// we can just clear out the message box
			var sig = this.getEl('replybox_wrap').find('textarea.signature-value').val();
			if (sig) sig = "\n\n" + sig;

			this.getEl('replybox_wrap').find('textarea[name="message"]').val(sig);
			return;
		}

		if (data.client_messages) {
			DeskPRO_Window.getMessageChanneler().handleMessageAjax(data.client_messages);
		}

		var new_messages = null;
		if (data.ticket_messages_block) {
			new_messages = $(data.ticket_messages_block).hide();
			new_messages.appendTo($(this.getEl('messages_wrap'))).slideDown('fast');
		}

		if (data.updated_agent_parts_html) {
			this.getEl('agent_part_list').html(data.updated_agent_parts_html);
			$('.agent-part-count', this.wrapper).text(data.updated_agent_parts_count);
		}

		if (data.replybox_html) {
			this.getEl('replybox_wrap').empty().append(data.replybox_html);
			DeskPRO_Window.initInterfaceServices(this.getEl('replybox_wrap'));
			$('form.ticket-reply-form', this.getEl('replybox_wrap')).bind('replyboxsubmit', this.handleReplySave.bind(this));
		}

		var showMessages = $('input.show-messages', this.wrapper).is(':checked');
		var showAttach   = $('input.show-attach', this.wrapper).is(':checked');
		var showNotes    = $('input.show-notes', this.wrapper).is(':checked');
		var showLogs     = $('input.show-logs', this.wrapper).is(':checked');

		var msgWrap = $('.messages-wrap', this.wrapper);

		if (new_messages) {
			this._initMessage(new_messages);
		}

		if (!showAttach) {
			$('.attachment-list', this.getEl('messages_wrap')).hide();
		}
		if (showMessages) {
			$('.attachment-lone', this.getEl('messages_wrap')).hide();
		}

		if (!showMessages) {
			this.getEl('messages_wrap').find('article.message:not(.note-message)').hide();
			if (showAttach) {
				this.getEl('messages_wrap').find('article.message.has-attach').hide();
			}
		}
		if (!showNotes) {
			this.getEl('messages_wrap').find('div.note-message').hide();
		}

		if (!showLogs) {
			this.getEl('messages_wrap').find('.log-row').hide();
			this.getEl('messages_wrap').find('.log-batch').hide();
		}

		window.setTimeout(this.updateUi.bind(this), 450);
	},

	displayNewMessage: function(html, slideCallback) {
		var new_message = $(html).hide();

		slideCallback = slideCallback || function(){};

		new_message.appendTo($(this.getEl('messages_wrap'))).slideDown('fast', slideCallback);

		this._initMessage(new_message);
		this.incCount('ticket-messages');

		this.rescanMessageTypes();
	},

	_initMessage: function(messageEl) {
		var self = this;
		var imageEls = $('ul.attachment-list li.is-image a', messageEl);

		imageEls.colorbox({
			title: function(){
				var url = $(this).attr('href');
				var dl_url = Orb.appendQueryData(url, 'dl', '1');
				return '<a href="'+url+'" target="_blank">Open In New Window</a> | <a href="'+dl_url+'" target="_blank">Download</a>'
			},
			width: '50%',
			height: '50%',
			initialWidth: '200',
			initialHeight: '150',
			scalePhotos: true,
			photo: true,
			opacity: 0.5,
			transition: 'none'
		});

		$('.log-row:not(.has-init)', messageEl).each(function() {
			$(this).addClass('has-init');
			var expandBtn = $('.expand', this);
			var el = $(this);
			expandBtn.on('click', function() {
				var sel = '.expand-set';
				if ($(this).data('set')) {
					sel = $(this).data('set');
				}

				var expandEl = $(sel, el);
				if (expandEl.is(':visible')) {
					expandEl.slideUp();
					expandBtn.removeClass('open');
				} else {
					expandEl.slideDown();
					expandBtn.addClass('open');
				}
			});
		});

		var lastCount = 0;
		if (this.lastMessageCount) {
			lastCount = this.lastMessageCount;
		}

		if (messageEl.hasClass('messages-wrap')) {
			var articles = messageEl.find('article.message');
		} else {
			var articles = messageEl.filter('article.message');
		}
		articles.each(function() {
			var article = $(this);

			lastCount++;
			article.find('.message-counter').text('#' + lastCount);

			var fullEl = article.find('.body-text-full-message');
			if (fullEl[0]) {
				var simpleEl = article.find('.body-text-message');
				fullEl.find('.message-toggle-btn > em').on('click', function(ev) {
					ev.preventDefault();
					fullEl.hide();
					simpleEl.show();
					self.updateUi();
				});
				simpleEl.find('.message-toggle-btn > em').on('click', function(ev) {
					ev.preventDefault();
					fullEl.show();
					simpleEl.hide();
					self.updateUi();
				});
			}
		});
		this.lastMessageCount = lastCount;
	},

	incCount: function(id) {
		var countEl = $('.'+id+'-count', this.wrapper);
		var count = countEl.data('count') + 1;
		countEl.data('count', count).html('(' + count + ')');
	},

	setCount: function(id, count) {
		var countEl = $('.'+id+'-count', this.wrapper);
		countEl.data('count', count).html('(' + count + ')');
	},

	appendToMessage: function(content) {
		this.insertTextInReply(content);

		// Scroll down
		this.wrapper.find('div.layout-content').trigger('goscrollbottom');

		// Focus reply
		$('textarea[name="message"]', self.ticketReply).focus();
	},

	addAttachToList: function(attachInfo) {
		var row = $('.template-download', this.getEl('replybox')).tmpl(attachInfo);
		$('.file-list', this.getEl('replybox')).append(row);
		this.updateUi();
	},

	//#################################################################
	//# Property managers
	//#################################################################

	getPropertyManager: function(type, type_id) {

		DP.console.error('Depreciated');
		return this.changeManager.getPropertyManager(type, type_id);
	},

	//#################################################################
	//# Custom Fields popout
	//#################################################################

	_initCustomFieldsEditor: function() {
		$('.ticket-custom-fields-edit-btn', this.wrapper).on('click', (function() {
			this.showCustomFieldEditor();
		}).bind(this));

		this.custom_fields_display = $('.ticket-custom-fields:not(.edit)', this.wrapper);
		this.custom_fields_edit = $('.ticket-custom-fields.edit', this.wrapper);
		this.custom_fields_edit.detach().appendTo(this.custom_fields_display.parent().parent().parent().parent());

		$('.close-trigger', this.custom_fields_edit).on('click', (function() {
			this.closeCustomFieldEditor();
		}).bind(this));

		var self = this;
		$('.save-trigger', this.custom_fields_edit).on('click', (function() {
			var fieldEls = $(':input', self.custom_fields_edit);
			this._saveCustomFields(fieldEls);
		}).bind(this));
	},

	showCustomFieldEditor: function() {

		var pos = this.custom_fields_display.position();
		var width = this.custom_fields_display.width();

		if (width > 690) {
			pos.left += width-690; // always want it hugging the right
			width = 690;
		}

		this.custom_fields_edit.css({
			position: 'absolute',
			top: pos.top,
			left: pos.left,
			width: width
		});

		this.custom_fields_edit.slideDown();
		window.setTimeout(this.updateUi.bind(this), 450);
	},

	closeCustomFieldEditor: function() {
		this.custom_fields_edit.slideUp();
		window.setTimeout(this.updateUi.bind(this), 450);
	},

	_saveCustomFields: function(fieldEls) {
		DP.console.error('This method shold be overriden in a subclass!');
	},

	//#################################################################
	//# Labels
	//#################################################################

	_initLabels: function() {
		var txt = $(".ticket-tags input", this.wrapper);
		if (txt[0]) {
			this.labelsInput = new DeskPRO.UI.LabelsInput({
				type: 'tickets',
				textarea: txt,
				onChange: this.saveLabels.bind(this)
			});
			this.ownObject(this.labelsInput);
		}
	},

	saveLabels: function() {
		if (this.changeManager.hasChanges()) {
			// If change manager has changes, we dont save new/removed tags
			return;
		}

		if (this._saveLabelsTimeout) {
			window.clearTimeout(this._saveLabelsTimeout);
		}

		this._saveLabelsTimeout = this._doSaveLabels.delay(2000, this);
	},

	_doSaveLabels: function() {
		var data = this.labelsInput.getFormData();

		$.ajax({
			url: this.getMetaData('labelsSaveUrl'),
			type: 'POST',
			context: this,
			data: data,
			dataType: 'json',
			success: function(data) {
				this._handleSaveLabelsSuccess(data);
			}
		});
	},

	_handleSaveLabelsSuccess: function(data) {

	},

	getNewTicketMessages: function() {
		var last_id = $('li.message-item:last', this.wrapper).data('message-id');

		$.ajax({
			url: this.getMetaData('getMessagesUrl'),
			type: 'POST',
			context: this,
			data: { since: last_id },
			dataType: 'json',
			success: function(data) {

				Array.each(data.messages, function (html) {
					this.displayNewMessage(html);
				}, this);
			}
		});
	},

	//#################################################################
	//# Custom Fields popout
	//#################################################################

	_saveCustomFields: function(fieldEls) {
		$('.buttons .loading-off', this.custom_fields_edit).hide();
		$('.buttons .loading-on', this.custom_fields_edit).show();

		var data = fieldEls.serializeArray();

		$.ajax({
			url: BASE_URL + 'agent/tickets/' + this.getMetaData('ticket_id') + '/ajax-save-custom-fields',
			type: 'POST',
			context: this,
			data: data,
			dataType: 'html',
			success: function(html) {
				this._handleSaveCustomFieldsSuccess(html);
			}
		});
	},

	_handleSaveCustomFieldsSuccess: function(html) {
		$('.buttons .loading-on', this.custom_fields_edit).hide();
		$('.buttons .loading-off', this.custom_fields_edit).show();
		this.closeCustomFieldEditor();

		$('.wrap', this.custom_fields_display).html(html);
	},

	//#################################################################
	//# Ticket actions menu
	//#################################################################

	_initTicketActionsMenu: function() {
		var self = this;

		if (this.meta.ticket_perms['delete']) {
			this.getEl('delete_trigger').click(function() { self.showDeleteOverlay(); });
			this.getEl('spam_trigger').click(function() { self.doTicketSpam(); });
		}
		this.getEl('print_trigger').click(function() { window.print(); });
	},

	_initDeleteOverlay: function() {

		if (this.deleteOverlay) return;

		this.deleteOverlayEl = $('.delete-ticket-overlay:first', this.wrapper);
		this.deleteOverlay = new DeskPRO.UI.Overlay({
			contentElement: this.deleteOverlayEl
		});
		this.ownObject(this.deleteOverlay);

		$('.save-trigger', this.deleteOverlayEl).on('click', (function() {
			this.doTicketDelete();
		}).bind(this));
	},

	showDeleteOverlay: function() {
		this._initDeleteOverlay();
		this.deleteOverlay.openOverlay();
	},

	doTicketDelete: function() {

		$('.loading-off', this.deleteOverlayEl).hide();
		$('.loading-on', this.deleteOverlayEl).show();

		var data = [];
		data.push({
			name: 'reason',
			value: $('.delete-reason', this.deleteOverlayEl).val()
		});

		var self = this;

		$.ajax({
			url: BASE_URL + 'agent/tickets/' + this.getMetaData('ticket_id') + '/delete',
			type: 'POST',
			data: data,
			dataType: 'json',
			success: function(data) {

				self.deleteOverlay.closeOverlay();
				DeskPRO_Window.removePage(self);

				// Reload the ticket page
				DeskPRO_Window.loadPage(BASE_URL + 'agent/tickets/' + self.getMetaData('ticket_id'), {ignoreExist:true});
			}
		});
	},

	doTicketSpam: function() {
		var self = this;

		$.ajax({
			url: BASE_URL + 'agent/tickets/' + this.getMetaData('ticket_id') + '/spam',
			type: 'POST',
			dataType: 'json',
			success: function(data) {
				DeskPRO_Window.removePage(self);

				// Reload the ticket page
				DeskPRO_Window.loadPage(BASE_URL + 'agent/tickets/' + self.getMetaData('ticket_id'), {ignoreExist:true});
			}
		});
	},

	doTicketUndelete: function() {
		var self = this;
		var prop = this.changeManager.getPropertyManager('status');
		this.changeManager.setInstantChange(prop, 'awaiting_agent', function() {
			DeskPRO_Window.removePage(self);

			// Reload the ticket page
			DeskPRO_Window.loadPage(BASE_URL + 'agent/tickets/' + self.getMetaData('ticket_id'), {ignoreExist:true});
		});
	},

	doTicketUnspam: function() {
		var self = this;
		var prop = this.changeManager.getPropertyManager('status');
		this.changeManager.setInstantChange(prop, 'awaiting_agent', function() {
			DeskPRO_Window.removePage(self);

			// Reload the ticket page
			DeskPRO_Window.loadPage(BASE_URL + 'agent/tickets/' + self.getMetaData('ticket_id'), {ignoreExist:true});
		});
	},

	_initMessageActionsMenu: function() {
		var self = this;
		this.messageActionsMenu = new DeskPRO.UI.Menu({
			triggerElement: null,
			menuElement: $('.ticket-message-edit-menu', this.wrapper),
			onItemClicked: function(info) {
				DP.console.log($(info.menu.getOpenTriggerElement()));
				self._doMessageAction($(info.itemEl).data('option-id'), $(info.menu.getOpenTriggerElement()).data('message-id'));
			}
		});
		this.ownObject(this.messageActionsMenu);

		// We're using a live event because new messages are always
		// added. So we take care of opening the menu manually.
		var menu = this.messageActionsMenu;
		var wrap = $('.messages-wrap', this.wrapper)[0];
		$('.ticket-message-edit-btn', wrap).live('click', function(event) {
			menu.openMenu(event);
		});
	},

	_doMessageAction: function(optionId, messageId) {
		switch (optionId) {
			case 'view-details':
				var overlay = new DeskPRO.UI.Overlay({
					contentMethod: 'iframe',
					title: 'Message Details',
					iframeUrl: this.getMetaData('viewMessageUnformattedUrl').replace('{message_id}', messageId),
					destroyOnClose: true
				});
				overlay.openOverlay();
				break;

			case 'quote':
				var quote = $('textarea.message-quote-' + messageId, this.wrapper).val();
				this.insertTextInReply(quote.trim() + "\n");

				// Scroll down
				this.wrapper.find('div.layout-content').trigger('goscrollbottom');

				// Focus reply
				$('textarea[name="message"]', self.ticketReply).focus();

				break;

			case 'split':
				var msg = "Are you sure you want to split this ticket into two?";
				DeskPRO_Window.showConfirm(msg, function() {
					$.ajax({
						url: BASE_URL + 'agent/tickets/split/' + messageId,
						type: 'POST',
						context: this,
						dataType: 'json',
						success: function(data) {
							DP.console.log('Ticket split return %o', data);
							if (data.success) {
								DeskPRO_Window.loadPage(BASE_URL + 'agent/tickets/' + data.ticket_id);
							}
						}
					});
				});
				break;
		}
	},

	insertTextInReply: function(text) {
		var txt = this.getEl('replybox_wrap').find('textarea[name="message"]');

		var pos = txt.getCaretPosition();
		if (!pos) {
			txt.setCaretPosition(0);
		}

		txt.insertAtCaret(text);
	},

	doTicketUpdate: function() {
		var formData = [];
		formData.push({
			name: 'last_message_id',
			value: this.getLastMessageId()
		});
		formData.push({
			name: 'last_log_id',
			value: this.getEl('messages_wrap').find('.log-row').last().data('log-id')
		});

		$.ajax({
			url: BASE_URL + 'agent/tickets/' + this.getMetaData('ticket_id') + '/update-views.json',
			type: 'POST',
			dataType: 'json',
			data: formData,
			context: this,
			success: function(result) {
				this.alertTab();
				this.handleTicketUpdate(result);
				this.rescanMessageTypes();
			}
		});
	}
});
