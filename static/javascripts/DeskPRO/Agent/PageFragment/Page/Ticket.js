Orb.createNamespace('DeskPRO.Agent.PageFragment.Page');

DeskPRO.Agent.PageFragment.Page.Ticket = new Class({

	Extends: DeskPRO.Agent.PageFragment.Basic,

	TYPENAME: 'ticket',

	wrapper: null,

	destroyEls: [],
	destroyMenus: [],
	destroyOverlays: [],

	changeManager: null,
	valueForm: null,

	layout: null,

	popout: null,
	popout_overview: null,

	isMouseOverPopout: false,
	hasInitPopout: false,
	popoutPage: null,

	lastActiveDate: null,

	initPage: function(el) {

		this.wrapper = el;
		this.contentWrapper = $('.layout-content', this.wrapper).attr('id', Orb.getUniqueId());
		this.barWrapper = $('.bar-wrapper', this.wrapper);

		var cw = this.contentWrapper;
		cw.tinyscrollbar();
		$('div.scroll-content:first, div.scroll-viewport:first', this.contentWrapper).resize(function() {
			// When size changes within the pane, need to re-size the scroll
			cw.tinyscrollbar_update();
		});

		this.valueForm = $('form.value-form:first', this.contentWrapper);
		this.valueForm.submit(function(ev) {
			// Never actually submit the form (would load a new page)
			ev.preventDefault();
		});
		this.changeManager = new DeskPRO.Agent.Ticket.ChangeManager(this);

		window.TICKET = this;

		if (!this.meta.isDeleted) {
			this._initCustomFieldsEditor();
		}

		var self = this;
		this._initMessage($('div.messages-wrap'));

		// Custom field widgets
		$('input.date-field', this.contentWrapper).datepicker({ 'dateFormat': 'M d, yy'});

		this.ticketDisplay = new DeskPRO.Agent.PageHelper.TicketDisplay(this, {
			wrapper: el
		});

		this.initFeaturesOnCollection(this.wrapper, {
			routes: [],
			times: ['.timeago']
		});

		if (!this.meta.isDeleted) {
			this._initTicketActionsMenu();
			this._initMessageActionsMenu();
			this._initFlagMenu();
			this._initLabels();
		} else {
			$('button.undelete-trigger', this.wrapper).click(this.doTicketUndelete.bind(this));
		}

		this._initPopout();

		DeskPRO_Window.getMessageBroker().sendMessage('ui.ticket.opened', { ticketId: this.getMetaData('ticket_id') });
		DeskPRO_Window.getMessageBroker().sendMessage('ui.tab.opened', { type: 'tickets', id: this.getMetaData('ticket_id') });

		DeskPRO_Window.getMessageBroker().addMessageListener('tickets.deleted', (function(ticket_ids) {
			if (ticket_ids.indexOf(this.getMetaData('ticket_id')) !== -1) {
				DeskPRO_Window.removePage(this);
			}
		}).bind(this), this.pageUid);

		DeskPRO_Window.getMessageBroker().addMessageListener('tickets.new-messages.' + this.getMetaData('ticket_id'), this.getNewTicketMessages.bind(this), this.pageUid);

		Array.each(this.getMetaData('fieldHandlers', []), function(h) {
			if (!h) return;

			var handler_class = h.classname;
			var field_wrap_id = h.wrap_id;

			var h = new h($('#' + field_wrap_id), this);
			h.initPage();
		}, this);

		this.addEvent('shortcutFocusReply', (function() {

			// Scroll down
			$('div.scroll-content:first, div.scroll-viewport:first', this.contentWrapper).scrollTop(100000);

			// Focus reply
			$('textarea[name="message"]', this.ticketReply).focus();
		}).bind(this));

		$('.ticket-urgency', this.contentWrapper).mouseover(function() {
			Tipped.show(this);
		});

		var showMessages = $('input.show-messages', this.wrapper);
		var showNotes = $('input.show-notes', this.wrapper);
		var showLogs = $('input.show-logs', this.wrapper);
		var msgWrap = $('.messages-wrap', this.wrapper);

		function updateMessageTypes() {
			var messages = showMessages.is(':checked');
			var notes = showNotes.is(':checked');
			var logs = showLogs.is(':checked');

			if (!messages && !notes && !logs) {
				messages = true;
				showMessages.attr('checked', true);
			}

			if (messages) {
				$('div.message:not(.note-message)', msgWrap).show();
			} else {
				$('div.message:not(.note-message)', msgWrap).hide();
			}
			if (notes) {
				$('div.note-message', msgWrap).show();
			} else {
				$('div.note-message', msgWrap).hide();
			}
			if (logs) {
				$('div.log-row', msgWrap).show();
				$('div.log-batch', msgWrap).show();
			} else {
				$('div.log-row', msgWrap).hide();
				$('div.log-batch', msgWrap).hide();
			}
		};

		$('.message-controls input', this.wrapper).click(function() {
			updateMessageTypes();
		});

		// Goto reply
		$('button.goto-reply', this.wrapper).click(function() {
			cw.tinyscrollbar_scrolltop(1000000);
		});

		this.moreActionsMenu = new DeskPRO.UI.Menu({
			triggerElement: $('.more', this.getEl('action_buttons')),
			menuElement: this.getEl('more_actions_menu'),
			onItemClicked: (function(info) {
				var el = $(info.itemEl);
				if (el.is('.merge-trigger')) {
					var mergeOverlay = new DeskPRO.Agent.Widget.MergeTicket({
						ticketId: this.getMetaData('ticket_id'),
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
				}
			}).bind(this)
		});


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

		this.ticketActions = new DeskPRO.Agent.PageFragment.Page.Ticket.TicketActions(this);
		this.ticketParticipants = new DeskPRO.Agent.PageFragment.Page.Ticket.Participants(this);

		this.ticketChecker = new DeskPRO.Agent.PageFragment.Page.Ticket.TicketChecker(this, {
			lastMessageId: this.meta.lastMessageId,
			lastLogId: this.meta.lastLogId,
			checkUrl: BASE_URL + 'agent/tickets/'+this.getMetaData('ticket_id')+'/ajax-update-check'
		});

		if (this.meta.isLocked) {
			this.ticketLocked = new DeskPRO.Agent.PageFragment.Page.Ticket.TicketLocked(this);
		}
	},

	destroyPage: function() {

		this.ticketChecker.destroy();

		if (this.updateCheckTimeout) {
			this.updateCheckTimeout = window.clearTimeout(this.updateCheckTimeout);
		}

		for (var i = 0; i < this.destroyEls.length; i++) {
			$(this.destroyEls[i]).remove();
		}

		for (var i = 0; i < this.destroyMenus.length; i++) {
			this.destroyMenus[i].destroy();
		}

		for (var i = 0; i < this.destroyOverlays.length; i++) {
			this.destroyOverlays[i].destroy();
		}

		if (this.personPopover) {
			this.personPopover.destroy();
		}
		if (this.orgPopover) {
			this.orgPopover.destroy();
		}

		DeskPRO_Window.getMessageBroker().sendMessage('ui.ticket.closed', { ticketId: this.getMetaData('ticket_id') });
		DeskPRO_Window.getMessageBroker().removeTaggedListeners(this.pageUid);
	},

	handleTicketUpdate: function(data) {
		if (data.client_messages) {
			DeskPRO_Window.getMessageChanneler().handleMessageAjax(data.client_messages);
		}

		if (data.ticket_messages_block) {
			var new_messages = $(data.ticket_messages_block).hide();
			new_messages.appendTo($(this.getEl('messages_wrap'))).slideDown('fast');

			var showMessages = $('input.show-messages', this.wrapper).is(':checked');
			var showNotes = $('input.show-notes', this.wrapper).is(':checked');
			var showLogs = $('input.show-logs', this.wrapper).is(':checked');

			console.log($('input.show-logs', this.wrapper));

			var msgWrap = $('.messages-wrap', this.wrapper);

			if (!showMessages) {
				new_messages.find('div.message:not(.note-message)').hide();
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
		this.ticketChecker.unpause();
	},

	deactivate: function() {
		this.ticketChecker.pause();
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

				var expandEl = $(sel, messageEl);
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

	custom_fields_display: null,
	custom_fields_edit: null,
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

	labelsList: null,
	_initLabels: function() {
		// Tags
		this.labelsList = $(".ticket-tags ul", this.contentWrapper);

		this.labelsInput = new DeskPRO.UI.LabelsInput({
			type: 'tickets',
			list: this.labelsList,
			onChange: this.saveLabels.bind(this)
		});
	},

	_saveLabelsTimeout: null,
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
		var last_id = $('li.message-item:last', this.contentWrapper).data('message-id');

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
	//# Ticket flag
	//#################################################################


	flagMenu: null,
	_initFlagMenu: function() {
		var self = this;
		this.flagMenu = new DeskPRO.UI.Menu({
			triggerElement: $('.ticket-flag:first', this.wrapper),
			menuElement: $('.ticket-flag-menu:first', this.wrapper),
			onItemClicked: function(info) {
				self._handleFlagMenuClick(info);
			}
		});

		this.destroyMenus.push(this.flagMenu);
	},

	_handleFlagMenuClick: function(info) {
		var opt = 'flag';
		var itemId = $(info.itemEl).data('flag');

		var prop = this.getPropertyManager(opt);
		this.changeManager.setInstantChange(prop, itemId);
	},

	_handleFlagMenuClickSuccess: function(old_flag, new_flag) {

		DeskPRO_Window.getMessageBroker().sendMessage('filter-flagged.flag-changed', {
			old_flag: old_flag,
			new_flag: new_flag
		});
	},

	//#################################################################
	//# Message actions menu
	//#################################################################

	ticketActionsMenu: null,
	_initTicketActionsMenu: function() {

		var self = this;
		this.ticketActionsMenu = new DeskPRO.UI.Menu({
			triggerElement: $('.ticket-actions-trigger:first', this.wrapper),
			menuElement: $('.ticket-actions-menu:first', this.wrapper),
			onItemClicked: function(info) {
				var op = $(info.itemEl).data('op');

				if (op == 'delete') {
					self.showDeleteOverlay();
				}
			}
		});

		this.destroyMenus.push(this.ticketActionsMenu);
	},

	deleteOverlay: null,
	deleteOverlayEl: null,
	_initDeleteOverlay: function() {

		if (this.deleteOverlay) return;

		this.deleteOverlayEl = $('.delete-ticket-overlay:first', this.wrapper);
		this.deleteOverlay = new DeskPRO.UI.Overlay({
			contentElement: this.deleteOverlayEl
		});

		$('.save-trigger', this.deleteOverlayEl).click((function() {
			this.doTicketDelete();
		}).bind(this));

		this.destroyOverlays.push(this.deleteOverlay);
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
				DeskPRO_Window.loadPage(BASE_URL + 'agent/tickets/' + self.getMetaData('ticket_id'), {ignoreExist:true}, function(page) {
					var ticket_title = self.getMetaData('title');
					if (ticket_title.length > 20) {
						ticket_title = ticket_title.substr(0, 20) + ' ...';
					}
					ticket_title = Orb.escapeHtml(ticket_title);
					DeskPRO_Window.showUndoMessage("Deleted ticket \""+ticket_title+"\"", function() {
						page.doTicketUndelete();
					});
				});
			}
		});
	},

	doTicketUndelete: function() {
		var prop = this.getPropertyManager('status');
		this.changeManager.setInstantChange(prop, 'open');
	},

	messageActionsMenu: null,
	_initMessageActionsMenu: function() {
		var self = this;
		this.messageActionsMenu = new DeskPRO.UI.Menu({
			triggerElement: null,
			menuElement: $('.ticket-message-edit-menu:first', this.wrapper),
			onItemClicked: function(info) {
				self._doMessageAction($(info.itemEl).data('option-id'), $(info.menu.getOpenTriggerElement()).data('message-id'));
			}
		});

		this.destroyMenus.push(this.messageActionsMenu);

		// We're using a live event because new messages are always
		// added. So we take care of opening the menu manually.
		var menu = this.messageActionsMenu;
		var wrap = $('.messages-wrap:first', this.wrapper)[0];
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
				console.debug('todo loading indicator when loading _doMessageAction quote');
				$.ajax({
					url: this.getMetaData('getMessageQuoteUrl').replace('{message_id}', messageId),
					type: 'GET',
					context: this,
					dataType: 'json',
					success: function(data) {
						var reply = $('.reply-form-fields:first textarea:first', this.ticketBar);
						console.log(reply);
						reply.val(data.message_quote + "\n\n" + reply.val());
						reply.focus();
					}
				});
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
	},

	//#################################################################
	//# Popout
	//#################################################################

	_initPopout: function() {

		this.personPopover = new DeskPRO.Agent.PageHelper.Popover({
			pageUrl: this.getMetaData('viewPersonUrl'),
			tabRoute: $('.person-overview', this.wrapper).data('route'),
			loadTimeout: 500
		});

		$('.person-overview', this.wrapper).css({'cursor': 'pointer'}).click((function(event) {

			this.personPopover.toggle();
		}).bind(this));

		this.orgPopover = null;

		var orgEl = $('.org-overview', this.wrapper);
		if (orgEl.length) {
			this.orgPopover = new DeskPRO.Agent.PageHelper.Popover({
				pageUrl: this.getMetaData('viewOrgUrl'),
				tabRoute: orgEl.data('route'),
				loadTimeout: 1200
			});

			orgEl.css({'cursor': 'pointer'}).click((function(event) {
				this.orgPopover.toggle();
			}).bind(this));
		}
	},

	updateCounts: function() {
		var wrap = $('.full-container-tabbed-tabs', this.wrapper);

		$.ajax({
			url: this.getMetaData('getUpdatedCountsUrl'),
			type: 'GET',
			context: this,
			dataType: 'json',
			success: function(counts) {
				Object.each(counts, function(v,k) {
					var sel = '.ticket-' + k + '-count';
					$(sel, wrap).html('(' + v + ')');
				});
			}
		});
	}
});
