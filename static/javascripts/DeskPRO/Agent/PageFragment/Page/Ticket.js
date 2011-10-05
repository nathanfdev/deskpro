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

		this.valueForm = $('form.value-form:first', this.wrapper);
		this.valueForm.submit(function(ev) {
			// Never actually submit the form (would load a new page)
			ev.preventDefault();
		});

		this.changeManager = new DeskPRO.Agent.Ticket.ChangeManager(this);

		this.ticketDisplay = new DeskPRO.Agent.PageHelper.TicketDisplay(this, {
			wrapper: el
		});
		this.ownObject(this.ticketDisplay);

		this._initCustomFieldsEditor();

		var self = this;
		this._initMessage($('.messages-wrap'));

		this._initTicketActionsMenu();
		this._initMessageActionsMenu();
		this._initLabels();

		if (this.meta.isDeleted) {
			$('button.undelete-trigger', this.wrapper).click(this.doTicketUndelete.bind(this));
		}

		DeskPRO_Window.getMessageBroker().sendMessage('ui.ticket.opened', { ticketId: this.getMetaData('ticket_id') });
		DeskPRO_Window.getMessageBroker().sendMessage('ui.tab.opened', { type: 'tickets', id: this.getMetaData('ticket_id') });

		DeskPRO_Window.getMessageBroker().addMessageListener('tickets.deleted', (function(ticket_ids) {
			if (ticket_ids.indexOf(this.getMetaData('ticket_id')) !== -1) {
				DeskPRO_Window.removePage(this);
			}
		}).bind(this), this.pageUid);

		DeskPRO_Window.getMessageBroker().addMessageListener('tickets.new-messages.' + this.getMetaData('ticket_id'), this.getNewTicketMessages.bind(this), this.pageUid);

		this.addEvent('shortcutFocusReply', (function() {

			// Scroll down
			$('div.scroll-content:first, div.scroll-viewport:first', this.wrapper).scrollTop(100000);

			// Focus reply
			$('textarea[name="message"]', this.ticketReply).focus();
		}).bind(this));

		$('.ticket-urgency', this.wrapper).mouseover(function() {
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
			} else {
				$('article.message:not(.note-message)', msgWrap).hide();
				if (attach) {
					$('article.message.with-attach', msgWrap).show();
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
		};

		$('.tickets-msg-controls input', this.wrapper).click(function() {
			updateMessageTypes();
		});

		this.getEl('merge_trigger').click(function() {
			var mergeOverlay = new DeskPRO.Agent.Widget.MergeTicket({
				ticketId: self.getMetaData('ticket_id'),
				destroyOnClose: true,
				onMergeSuccess: function(data) {

					// remove old tabs, theyre outdated
					Array.each(DeskPRO_Window.getTabWatcher().findTabType('ticket'), function(tab) {
						var tid = tab.page.getMetaData('ticket_id');
						if (tid == data.old_ticket_id || tid == data.ticket_id) {
							DeskPRO_Window.pageTabStrip.removeTabById(tab.id);
						}
					});

					DeskPRO_Window.runPageRoute('ticket:' + BASE_URL + 'agent/tickets/' + data.ticket_id);

					mergeOverlay.close();
				}
			});
			mergeOverlay.open();
		});

		/*
		this.replyBox = new DeskPRO.Agent.PageFragment.Page.Ticket.ReplyBox(this, {
			replyBox: this.getEl('replybox'),
			onBeforeSaveReply: (function(info) {
				info.formData.push({
					name: 'client_messages_since',
					value: DeskPRO_Window.getLastClientMessageId()
				});

				info.formData.push({
					name: 'last_message_id',
					value: this.ticketChecker.getLastMessageId()
				});
				info.formData.push({
					name: 'last_log_id',
					value: this.ticketChecker.getLastLogId()
				});

				this.ticketChecker.pause(true);
			}).bind(this),
			onSaveReplySuccess: (function(info) {

				this.handleTicketUpdate(info.result);
				this.ticketChecker.unpause();

				if (info.result.close_tab) {
					window.setTimeout((function() {
						console.log('ere');
						this.closeSelf();
					}).bind(this), 400);
				} else {

					// Apply changed props
					var agentProp = this.changeManager.getPropertyManager('agent_id');
					agentProp.setIncomingValue(info.result.agent_id);

					var agentTeamProp = this.changeManager.getPropertyManager('agent_team_id');
					agentTeamProp.setIncomingValue(info.result.agent_team_id);

					var statusProp = this.changeManager.getPropertyManager('status');
					statusProp.setIncomingValue(info.result.status);

				}
			}).bind(this)
		});
		this.ownObject(this.replyBox);
		*/

		this.ticketActions = new DeskPRO.Agent.PageFragment.Page.Ticket.TicketActions(this);
		this.ownObject(this.ticketActions);

		this.ticketParticipants = new DeskPRO.Agent.PageFragment.Page.Ticket.Participants(this);
		this.ownObject(this.ticketParticipants);

		this.ticketChecker = new DeskPRO.Agent.PageFragment.Page.Ticket.TicketChecker(this, {
			lastMessageId: this.meta.lastMessageId,
			lastLogId: this.meta.lastLogId,
			checkUrl: BASE_URL + 'agent/tickets/'+this.getMetaData('ticket_id')+'/ajax-update-check'
		});
		this.ownObject(this.ticketChecker);

		if (this.meta.isLocked) {
			this.ticketLocked = new DeskPRO.Agent.PageFragment.Page.Ticket.TicketLocked(this);
			this.ownObject(this.ticketLocked);
		}
	},

	destroyPage: function() {

		if (this.updateCheckTimeout) {
			this.updateCheckTimeout = window.clearTimeout(this.updateCheckTimeout);
		}

		DeskPRO_Window.getMessageBroker().sendMessage('ui.ticket.closed', { ticketId: this.getMetaData('ticket_id') });
	},

	handleTicketUpdate: function(data) {
		if (data.client_messages) {
			DeskPRO_Window.getMessageChanneler().handleMessageAjax(data.client_messages);
		}

		if (data.ticket_messages_block) {
			var new_messages = $(data.ticket_messages_block).hide();
			new_messages.appendTo($(this.getEl('messages_wrap'))).slideDown('fast');

			var showMessages = $('input.show-messages', this.wrapper).is(':checked');
			var showAttach = $('input.show-attach', this.wrapper).is(':checked');
			var showNotes = $('input.show-notes', this.wrapper).is(':checked');
			var showLogs = $('input.show-logs', this.wrapper).is(':checked');

			var msgWrap = $('.messages-wrap', this.wrapper);

			if (!showAttach) {
				$('.attachment-list', new_messages).hide();
			}

			if (!showMessages) {
				new_messages.find('article.message:not(.note-message)').hide();
				if (showAttach) {
					new_messages.find('article.message.has-attach').hide();
				}
			}
			if (!showNotes) {
				new_messages.find('div.note-message').hide();

			if (!showLogs) {
				new_messages.find('div.log-row').hide();
				new_messages.find('div.log-batch').hide();
			}}

			this._initMessage(new_messages);
		}

		if (data.updated_agent_parts_html) {
			this.getEL('agent_part_list').html(data.updated_agent_parts_html);
			$('.agent-part-count', this.wrapper).text(data.updated_agent_parts_count);
		}
	},

	displayNewMessage: function(html, slideCallback) {
		var new_message = $(html).hide();

		slideCallback = slideCallback || function(){};

		new_message.appendTo($(this.getEl('messages_wrap'))).slideDown('fast', slideCallback);

		this._initMessage(new_message);
		this.incCount('ticket-messages');
	},

	activate: function() {
		if (this.ticketChecker) {
			this.ticketChecker.unpause();
		}
	},

	deactivate: function() {
		if (this.ticketChecker) {
			this.ticketChecker.pause();
		}
	},

	_initMessage: function(messageEl) {
		var imageEls = $('ul.attachment-list li.is-image a', messageEl);

		imageEls.colorbox({
			title: function(){ var url = $(this).attr('href'); return '<a href="'+url+'" target="_blank">Open In New Window</a>' },
			width: '50%',
			height: '50%',
			initialWidth: '200',
			initialHeight: '150',
			scalePhotos: true,
			photo: true,
			opacity: 0.5,
			transition: 'none'
		});

		$('.log-row', messageEl).each(function() {
			var expandBtn = $('.expand', this);
			var el = $(this);
			expandBtn.click(function() {
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
		this.getEl('replybox_txt').insertAtCaret(content);
	},

	addAttachToList: function(attachInfo) {
		var row = $('.template-download', this.getEl('replybox')).tmpl(attachInfo);
		$('.file-list', this.getEl('replybox')).append(row);
	},

	//#################################################################
	//# Property managers
	//#################################################################

	getPropertyManager: function(type, type_id) {

		console.warn('Depreciated');
		return this.changeManager.getPropertyManager(type, type_id);
	},

	//#################################################################
	//# Custom Fields popout
	//#################################################################

	_initCustomFieldsEditor: function() {
		$('.ticket-custom-fields-edit-btn', this.wrapper).click((function() {
			this.showCustomFieldEditor();
		}).bind(this));

		this.custom_fields_display = $('.ticket-custom-fields:not(.edit)', this.wrapper);
		this.custom_fields_edit = $('.ticket-custom-fields.edit', this.wrapper);
		this.custom_fields_edit.detach().appendTo(this.custom_fields_display.parent().parent().parent().parent());

		$('.close-trigger', this.custom_fields_edit).click((function() {
			this.closeCustomFieldEditor();
		}).bind(this));

		var self = this;
		$('.save-trigger', this.custom_fields_edit).click((function() {
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
	},

	closeCustomFieldEditor: function() {
		this.custom_fields_edit.slideUp();
	},

	_saveCustomFields: function(fieldEls) {
		console.warn('This method shold be overriden in a subclass!');
	},

	//#################################################################
	//# Labels
	//#################################################################

	_initLabels: function() {
		// Tags
		this.labelsList = $(".ticket-tags ul", this.wrapper);

		this.labelsInput = new DeskPRO.UI.LabelsInput({
			type: 'tickets',
			list: this.labelsList,
			onChange: this.saveLabels.bind(this)
		});
		this.ownObject(this.labelsInput);
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
		this.ticketActionsMenu = new DeskPRO.UI.Menu({
			triggerElement: this.getEl('more_menu_trigger'),
			menuElement: this.getEl('more_menu'),
			onItemClicked: function(info) {
				var op = $(info.itemEl).data('op');

				if (op == 'delete') {
					self.showDeleteOverlay();
				} else if (op == 'print') {
					window.print();
				}
			}
		});
		this.ownObject(this.ticketActionsMenu);
	},

	_initDeleteOverlay: function() {

		if (this.deleteOverlay) return;

		this.deleteOverlayEl = $('.delete-ticket-overlay:first', this.wrapper);
		this.deleteOverlay = new DeskPRO.UI.Overlay({
			contentElement: this.deleteOverlayEl
		});
		this.ownObject(this.deleteOverlay);

		$('.save-trigger', this.deleteOverlayEl).click((function() {
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

	doTicketUndelete: function() {
		var self = this;
		var prop = this.getPropertyManager('status');
		this.changeManager.setInstantChange(prop, 'open', function() {
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
				console.log($(info.menu.getOpenTriggerElement()));
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
					iframeUrl: this.getMetaData('viewMessageUnformattedUrl').replace('{message_id}', messageId),
					destroyOnClose: true
				});
				overlay.openOverlay();
				break;

			case 'quote':
				var quote = $('textarea.message-quote-' + messageId, this.wrapper).val();
				var reply = this.getEl('replybox_txt');
				reply.val(quote + "\n\n" + reply.val());
				reply.focus();
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
							console.log('Ticket split return %o', data);
							if (data.success) {
								DeskPRO_Window.loadPage(BASE_URL + 'agent/tickets/' + data.ticket_id);
							}
						}
					});
				});
				break;
		}
	}
});
