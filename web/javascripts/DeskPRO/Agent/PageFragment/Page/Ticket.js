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
		this.wrapper = el;
		var self = this;
		this.getEl('replybox_wrap').data('page', this);

		DeskPRO_Window.recentTabs.add(
			'tickets',
			this.meta.ticket_id,
			this.meta.title,
			BASE_URL + 'agent/tickets/' + this.meta.ticket_id
		);

		this.valueForm = $('form.value-form:first', this.wrapper);
		this.valueForm.on('submit', function(ev) {
			// Never actually submit the form (would load a new page)
			ev.preventDefault();
		});

		this.changeManager = new DeskPRO.Agent.Ticket.ChangeManager(this);
		this.changeManager.addEvent('updateResult', function(data) {
			DeskPRO_Window.getMessageBroker().sendMessage('agent.ui.ticket_updated', { ticket_id: self.meta.ticket_id });

			if (data.data && !data.data.can_view) {
				self.closeSelf();
			}
		});

		this.ticketFields = new DeskPRO.Agent.PageHelper.TicketFields(this);
		this.ownObject(this.ticketFields);

		this._initMessage(this.wrapper.find('.messages-wrap'));

		this._initTicketActionsMenu();
		this._initMessageActionsMenu();
		this._initLabels();
		this._initTicketLocking();
		this._initTasks();
		this._initEditName();
		this._initSlas();

		this.billing = new DeskPRO.Agent.PageHelper.TicketBilling(this.getEl('billing_wrap'), this.meta.baseId, {
			auto_start_bill: this.meta.auto_start_bill
		});

		this.addEvent('deactivate', function() {
			$('form.ticket-reply-form', this.getEl('replybox_wrap')).trigger('page_deactivate');
		});
		this.addEvent('activate', function() {
			$('form.ticket-reply-form', this.getEl('replybox_wrap')).trigger('page_activate');
		});

		this.addEvent('destroy', function() {
			if (self.meta.unlockOnClose && self.getEl('locked_message').data('locked-self')) {
				$.ajax({
					url: BASE_URL + 'agent/tickets/'+self.meta.ticket_id+'/release-lock.json',
					type: 'POST'
				});
			}
		}, false, false, true);

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

			self.focusOnReply();
		});

		this.addEvent('openUserProfile', function(ev) {
			ev.preventDefault();
			self.getEl('profile_link').trigger('click');
		});

		this.addEvent('openOrgProfile', function(ev) {
			ev.preventDefault();
			self.getEl('org_link').trigger('click');
		});

		if (this.meta.ticket_perms.modify_merge) {
			this.merge = new DeskPRO.Agent.Widget.Merge({
				tabType: 'ticket',
				metaId: self.meta.ticket_id,
				metaIdName: 'ticket_id',
				menu: this.getEl('merge_menu'),
				trigger: $('.merge', this.getEl('action_buttons')),
				overlayUrl: BASE_URL + 'agent/tickets/{id}/merge-overlay/{other}',
				mergeUrl: BASE_URL + 'agent/tickets/{id}/merge/{other}',
				loadRoute: 'ticket:' + BASE_URL + 'agent/tickets/{id}',
				overlayLoaded: function(overlay, merge) {
					overlay.getWrapper().find('.ticket-finder').bind('ticketsearchboxclick', function(ev, ticketId, subject, sb) {
						sb.close();

						$.ajax({
							url: merge._getOverlayUrl(merge.options.metaId, ticketId),
							type: 'get',
							dataType: 'html',
							success: function(html) {
								merge.resetOverlay(html);
							}
						});
					});
				}
			});
			this.ownObject(this.merge);

			this.getEl('changeuser_trigger').on('click', function() {
				var changeUserOverlay = new DeskPRO.Agent.Widget.TicketChangeUser({
					ticketId: self.getMetaData('ticket_id'),
					destroyOnClose: true,
					onSuccess: function(data) {
						self.closeSelf();
						DeskPRO_Window.runPageRoute('ticket:' + BASE_URL + 'agent/tickets/' + data.ticket_id);
						changeUserOverlay.close();
					}
				});
				changeUserOverlay.open();
			});
		}

		if (this.meta.ticket_perms.reply) {
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

		this.getEl('newtask').on('click', function(ev) {
			ev.preventDefault();
			DeskPRO_Window.newTaskLoader.open();
		});

		this.ticketFields.updateDisplay();

		this.wrapper.find('.lock-overlay').on('click', function(ev) {
			ev.preventDefault();
			self.showLockAlert();
		});

		this.getEl('idref_switch').on('click', function() {
			if ($(this).hasClass('refmode')) {
				$(this).removeClass('refmode')
				self.getEl('ref_num').hide();
				self.getEl('id_num').show();
			} else {
				$(this).addClass('refmode')
				self.getEl('id_num').hide();
				self.getEl('ref_num').show();
			}
		});

		DeskPRO.ElementHandler_Exec(this.wrapper);
		var messageboxTabs = this.getEl('messagebox_tabs').data('simpletabs');
		if (messageboxTabs) {
			messageboxTabs.addEvent('tabSwitch', function(evData) {
				var type = evData.tabEl.data('list-type');

				if (type == 'messages') {
					self.getEl('messages_wrap').removeClass('show-log show-collapsed-messages');
					self.getEl('messages_wrap').find('article.content-message').show();
				} else if (type == 'feedback') {
					self.getEl('messages_wrap').removeClass('show-log show-collapsed-messages');
					self.getEl('messages_wrap').find('article.content-message').not('article.with-feedback').hide();
				} else {
					self.getEl('messages_wrap').find('article.message-expanded').removeClass('message-expanded');
					self.getEl('messages_wrap').addClass('show-log show-collapsed-messages');
					self.getEl('messages_wrap').find('article.content-message').show();
				}
			});

		}

		self.getEl('messages_wrap').on('click', 'article.message', function(ev) {
			$(this).toggleClass('message-expanded');
		});

		this.getEl('people_box_agent').find('.select2-container-multi').css('width', '90%').find('input.select2-input').css('width', '90%');
	},

	showLockAlert: function() {
		DeskPRO_Window.showAlert('You are not allowed to make any changes to this ticket until it has been unlocked.');
	},

	handleReplySave: function(ev, formData, handler) {

		var self = this;
		var closetabTimeoutHit = false;
		var ajaxHit = false;
		var hitRun = false;
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

		this.getReplyTextArea().data('disable-autosave', true);

		var form = this.getEl('replybox_wrap').find('.ticket-reply-form');
		var keepOpen = false;
		if ($('#' + form.data('base-id') + '_is_note').val() == '1') {
			keepOpen = (form.data('close-note') != '1');
		} else {
			keepOpen = (form.data('close-reply') != '1');
		}

		var keepToggle = this.getEl('replybox_wrap').find('.keep-open-toggle');
		if (keepOpen) {
			keepToggle.addClass('click-close');
			if (!keepToggle.data('close-text')) {
				keepToggle.data('close-text', keepToggle.text());
			}
			if (keepToggle.data('keep-text')) {
				keepToggle.text(keepToggle.data('keep-text'));
			}
			keepToggle.siblings('em').hide();
		} else {
			keepToggle.removeClass('click-close');
			if (keepToggle.data('close-text')) {
				keepToggle.text(keepToggle.data('close-text'));
			}
			keepToggle.siblings('em').show();
		}

		var loadingEl = this.getEl('replybox_wrap').find('.ticket-sending-overlay');
		loadingEl.fadeIn();

		this.getEl('replybox_wrap').find('textarea.touched').removeClass('touched');

		DeskPRO_Window.getMessageChanneler().poller.pause();

		window.setTimeout(function() {
			closetabTimeoutHit = true;
			if (ajaxHit) {
				hitDone();
			}
		}, 3000);

		function hitDone() {
			hitRun = true;
			DeskPRO_Window.getMessageChanneler().poller.unpause();

			var el = self.getEl('replybox_wrap').find('.keep-open-toggle:first');
			if ((el.hasClass('click-close') && el.hasClass('radio-on'))
				|| (!el.hasClass('click-close') && !el.hasClass('radio-on'))
			) {
				self.closeSelf();
				return;
			}

			var result = ajaxHit;

			loadingEl.hide();

			if (result.error && result.error == 'no_message') {
				DeskPRO_Window.showAlert("Please enter a message");
				return;
			}

			self.handleTicketUpdate(result);

			if (!result.dupe_message) {
				// Apply changed props
				var agentProp = self.changeManager.getPropertyManager('agent_id');
				agentProp.setIncomingValue(result.agent_id);

				var agentTeamProp = self.changeManager.getPropertyManager('agent_team_id');
				agentTeamProp.setIncomingValue(result.agent_team_id);

				var statusProp = self.changeManager.getPropertyManager('status');
				statusProp.setIncomingValue(result.status);
			}

			if (result.dupe_message) {
				DeskPRO_Window.showAlert("You have already sent that message.");
				return;
			}

			// Reload the message row in results
			//addTicket
			if (DeskPRO_Window.sections.tickets_section && DeskPRO_Window.sections.tickets_section.listPage) {
				var row = DeskPRO_Window.sections.tickets_section.listPage.wrapper.find('article.ticket-' + self.meta.ticket_id);
				if (row[0]) {
					DeskPRO_Window.sections.tickets_section.listPage.addTicket(self.meta.ticket_id, true);
				}
			}
		};

		$.ajax({
			url: reply_form.attr('action'),
			type: 'POST',
			dataType: 'json',
			data: formData,
			context: this,
			noErrorOverride: true,
			complete: function() {
				DeskPRO_Window.getMessageChanneler().poller.unpause();

				this.getReplyTextArea().data('disable-autosave', false);
			},
			error: function() {
				DeskPRO_Window.getMessageChanneler().poller.unpause();
				var loadingEl = this.getEl('replybox_wrap').find('.ticket-sending-overlay');
				loadingEl.hide();
			},
			success: function(result) {

				// Always perform CM processing right now
				DeskPRO_Window.getMessageChanneler().poller.unpause();

				if (result.client_messages) {
					DeskPRO_Window.getMessageChanneler().handleMessageAjax(result.client_messages);

					// null out so handleTicketUpdate called in hitDone doesnt re-process them
					result.client_messages = null;
				}

				ajaxHit = result;
				if (closetabTimeoutHit) {
					hitDone();
				}
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
		var self = this;
		if (data.client_messages) {
			DeskPRO_Window.getMessageChanneler().handleMessageAjax(data.client_messages);
		}

		// Might be unloaded by the time this callback is called
		if (!this.changeManager) {
			return;
		}

		if (data.status) {
			var statusProp = this.changeManager.getPropertyManager('status');
			statusProp.setIncomingValue(data.status);
		}

		if (data.dupe_message) {
			// If its a dupe then it'd already be added ot the message list,
			// we can just clear out the message box
			var sig = this.getEl('replybox_wrap').find('textarea.signature-value').val();
			if (sig) sig = "\n\n" + sig;

			var textarea = this.getReplyTextArea();
			if (textarea.data('redactor')) {
				textarea.setCode(DP.convertTextToWysiwygHtml(sig, true));
			} else {
				textarea.val(sig);
			}
			return;
		}

		var new_messages = null;
		if (data.ticket_messages_block) {
			new_messages = $(data.ticket_messages_block);

			var any = false;
			if (new_messages.hasClass('message')) {
				if (!this.getEl('messages_wrap').find('.message-' + new_messages.data('message-id'))[0]) {
					any = true;
				}
			} else {
				new_messages.find('.message').each(function() {
					if (self.getEl('messages_wrap').find('.message-' + $(this).data('message-id'))[0]) {
						$(this).hide();
					} else {
						any = true;
					}
				});
			}

			if (any) {
				new_messages.appendTo($(this.getEl('messages_wrap')));
				this._initMessage(new_messages);
			}
		}

		if (data.updated_agent_parts_html) {
			this.getEl('agent_part_list').html(data.updated_agent_parts_html);
			$('.agent-part-count', this.wrapper).text(data.updated_agent_parts_count);
		}

		if (data.replybox_html) {
			// Only refresh the box if we've not begun writing a message
			if (!this.getEl('replybox_wrap').find('textarea.touched')[0]) {
				var textarea = this.getReplyTextArea();
				if (textarea.data('redactor')) {
					textarea.destroyEditor();
				}
				this.getEl('replybox_wrap').empty().append(data.replybox_html);
				DeskPRO_Window.initInterfaceServices(this.getEl('replybox_wrap'));
				$('form.ticket-reply-form', this.getEl('replybox_wrap')).bind('replyboxsubmit', this.handleReplySave.bind(this));
			}
		}

		if (typeof data.cc_list == "string") {
			this.wrapper.find('ul.cc-row-list').empty().html(data.cc_list);
		}

		var billing = this.billing;
		if (billing.hasBilling) {
			if (data.charge_html) {
				billing.addBillingRow(data.charge_html);
				billing.updateBillingForm(true);
				billing.resetBillingForm();
			} else {
				billing.updateBillingForm(false);
			}
		}

		window.setTimeout(this.updateUi.bind(this), 450);
	},

	displayNewMessage: function(html, slideCallback) {
		var self = this;
		var new_message = $(html).hide();

		if (new_message.data('message-id')) {
			if (this.getEl('messages_wrap').find('.message-' + new_message.data('message-id'))[0]) {
				return;
			}
		} else {
			var any = false;
			new_message.find('.message').each(function() {
				if (self.getEl('messages_wrap').find('.message-' + $(this).data('message-id'))[0]) {
					$(this).hide();
				} else {
					any = true;
				}
			});

			if (!any) {
				slideCallback();
				return;
			}
		}

		slideCallback = slideCallback || function(){};

		var old_slideCallback = slideCallback;
		var self = this;
		slideCallback = function() {
			self.updateUi();
			old_slideCallback();
		};

		new_message.appendTo($(this.getEl('messages_wrap'))).slideDown('fast', slideCallback);

		this._initMessage(new_message);
		this.incCount('ticket-messages');
	},

	_initMessage: function(messageEl) {
		var self = this;
		var imageEls = $('ul.attachment-list li.is-image a, a.dp-is-image', messageEl);

		$('.timeago', messageEl).timeago();

		imageEls.colorbox({
			title: function(){
				var url = $(this).attr('href');
				var dl_url = Orb.appendQueryData(url, 'dl', '1');
				return '<a href="'+url+'" target="_blank">Open In New Window</a> | <a href="'+dl_url+'" target="_blank">Download</a>'
			},
			onComplete: function() {
				var image = $('#cboxLoadedContent img');
				if (image.length) {
					$('#cboxLoadedContent').append(
						$('<a />').attr('href', $(this).attr('href')).attr('target', '_blank').append(image)
					);
				}
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
			var articles = messageEl.find('article.content-message');
		} else {
			var articles = messageEl.filter('article.content-message');
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

					if (!fullEl.hasClass('loaded')) {
						var row = $(this).closest('article.content-message');
						var message_id = row.data('message-id');

						$.ajax({
							url: BASE_URL + 'agent/tickets/messages/'+message_id+'/get-full-message.json',
							type: 'GET',
							success: function(data) {
								row.find('.full-message-content').html(data.message_full)
								self.updateUi();
							}
						});
					}
				});
			}
		});
		this.lastMessageCount = lastCount;

		var wr = this.getEl('messages_wrap');
		wr.find('.message-id-txt').each(function() {
			var findclass = '.message-counter-' + $(this).data('message-id');
			var counterText = wr.find(findclass).text().trim();
			if (counterText.length) {
				$(this).attr('title', $(this).text()).text(counterText).removeClass('message-id-txt');
			}
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
		this.insertTextInReply(content);

		// Scroll down
		this.wrapper.find('div.layout-content').trigger('goscrollbottom');

		this.focusOnReply();

		// Resize it by firing change which'll run the resize
		this.getReplyTextArea().trigger('textareaexpander_fire');
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
	//# Labels
	//#################################################################

	_initLabels: function() {
		if (this.getEl('labels_input')[0]) {
			this.labelsInput = new DeskPRO.UI.LabelsInput({
				type: 'tickets',
				input: this.getEl('labels_input'),
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

		this._labelsData = this.labelsInput.getFormData();
		this._saveLabelsTimeout = this._doSaveLabels.delay(2000, this);
	},

	_doSaveLabels: function() {
		var data = this._labelsData;

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
	//# Ticket actions menu
	//#################################################################

	_initTicketLocking: function() {
		var self = this;
		this.getEl('unlock_ticket').on('click', function() {
			self.wrapper.find('.lock-overlay').remove();
			self.getEl('locked_message').hide();
			self.getEl('locked_message').data('locked-self', false);
			self.getEl('lock_ticket').show();
			$.ajax({
				url: BASE_URL + 'agent/tickets/' + self.meta.ticket_id + '/unlock-ticket.json',
				type: 'POST',
				dataType: 'json',
				complete: function() {

				}
			});
		});

		this.getEl('lock_ticket').on('click', function() {
			self.wrapper.find('.lock-overlay').remove();
			self.getEl('locked_message').data('locked-self', true);
			self.getEl('locked_message').show();
			self.getEl('locked_message_self').show();
			self.getEl('locked_message_other').hide();
			self.getEl('lock_ticket').hide();

			$.ajax({
				url: BASE_URL + 'agent/tickets/' + self.meta.ticket_id + '/lock-ticket.json',
				type: 'POST',
				dataType: 'json',
				success: function(data) {
					if (data.error) {
						DeskPRO_Window.showAlert('Someone else has already locked the ticket');
						self.getEl('locked_message').hide();
						self.getEl('lock_ticket').show();

						// Reload the ticket page
						DeskPRO_Window.loadPage(BASE_URL + 'agent/tickets/' + self.getMetaData('ticket_id'), {ignoreExist:true});
						self.closeSelf();
					}
				}
			});
		});
	},

	_initTicketActionsMenu: function() {
		var self = this;

		var removeMenu = new DeskPRO.UI.Menu({
			triggerElement: this.getEl('remove_menu_trigger'),
			menuElement: this.getEl('remove_menu'),
			onItemClicked: function(info) {
				var it = $(info.itemEl);
				var doBan = false;
				if (it.data('action').indexOf('.ban') !== -1) {
					doBan = true;
				}

				switch (it.data('action')) {
					case 'spam':
					case 'spam.ban':
						self.doTicketSpam(doBan);
						break;

					case 'delete':
					case 'delete.ban':
						self.showDeleteOverlay(doBan);
						break;
				}
			}
		});

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

	showDeleteOverlay: function(doBan) {
		this._initDeleteOverlay();
		this.deleteOverlay.doBan = doBan;

		if (doBan) {
			this.getEl('delete_user_list').show();
		} else {
			this.getEl('delete_user_list').hide();
		}

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

		if (this.deleteOverlay.doBan) {
			data.push({
				name: 'ban',
				value: 1
			})
		}

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

	doTicketSpam: function(doBan) {
		var self = this;

		this.getEl('actions_loading').show();

		$.ajax({
			url: BASE_URL + 'agent/tickets/' + this.getMetaData('ticket_id') + '/spam',
			type: 'POST',
			dataType: 'json',
			data: {
				ban: doBan ? 1 : 0
			},
			success: function(data) {
				self.getEl('actions_loading').hide();

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
		var menuElement = $('.ticket-message-edit-menu', this.wrapper);
		this.messageActionsMenu = new DeskPRO.UI.Menu({
			triggerElement: null,
			menuElement: menuElement,
			onBeforeMenuOpened: function(info) {
				if ($(info.menu.getOpenTriggerElement()).closest('article.message').hasClass('note-message')) {
					menuElement.find('li.set-as-message').show();
					menuElement.find('li.set-as-note').hide();
				} else {
					menuElement.find('li.set-as-message').hide();
					menuElement.find('li.set-as-note').show();
				}
			},
			onItemClicked: function(info) {
				self._doMessageAction($(info.itemEl).data('option-id'), $(info.menu.getOpenTriggerElement()).data('message-id'));
			}
		});
		this.ownObject(this.messageActionsMenu);

		// We're using a live event because new messages are always
		// added. So we take care of opening the menu manually.
		var menu = this.messageActionsMenu;
		var wrap = $('.messages-wrap', this.wrapper)[0];
		$('.ticket-message-edit-btn', wrap).live('mousedown', function(event) {
			var textarea = self.getReplyTextArea();
			if (textarea.data('redactor')) {
				// save the selection - but don't focus the editor as that can break this
				$.proxy(function() {
					this.savedSel = this.getOrigin();
					this.savedSelObj = this.getFocus();
				}, textarea.data('redactor'))();
			}
		});
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
					iframeUrl: this.getMetaData('viewMessageUnformattedUrl').replace('{message_id}', messageId) + '?_rt=' + DP_REQUEST_TOKEN,
					destroyOnClose: true
				});
				overlay.openOverlay();
				break;

			case 'quote':
				var quote = $('textarea.message-quote-' + messageId, this.wrapper).val();
				if (!quote) {
					quote = '';
				}

				var textarea = this.getReplyTextArea();
				if (textarea.data('redactor')) {
					textarea.data('redactor').restoreSelection();
				}

				this.insertTextInReply(quote.trim() + "\n");

				// Scroll down
				this.wrapper.find('div.layout-content').trigger('goscrollbottom');

				this.focusOnReply();

				break;

			case 'setnote.note':
			case 'setnote.message':

				var is_note = optionId == 'setnote.note' ? '1' : '0';
				var row = this.wrapper.find('article.message-' + messageId);
				row.addClass('gear-loading');

				$.ajax({
					url: BASE_URL + 'agent/tickets/messages/'+messageId+'/set-message-note.json',
					data: {
						is_note: is_note
					},
					complete: function() {
						row.removeClass('gear-loading');
					},
					success: function(info) {
						if (info.is_note) {
							row.addClass('note-message');
						} else {
							row.removeClass('note-message');
						}
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
							DP.console.log('Ticket split return %o', data);
							if (data.success) {
								DeskPRO_Window.loadPage(BASE_URL + 'agent/tickets/' + data.ticket_id);
							}
						}
					});
				});
				break;

			case 'edit':
				this.showMessageEditor(messageId);
				break;
		}
	},

	showMessageEditor: function(message_id) {
		var self = this;
		this.currentOpenMessageId = message_id;
		if (!this.messageEditOverlay) {
			var overlayEl = this.getEl('message_edit_overlay');
			overlayEl.find('.save-text-trigger').on('click', function(ev) {
				ev.preventDefault();

				$(this).hide();
				overlayEl.find('.save-text-loading').show();

				if (overlayEl.find('textarea.message_text').data('redactor')) {
					overlayEl.find('textarea.message_text').data('redactor').syncCode();
				}

				var postData = {
					message_html: overlayEl.find('textarea.message_text').val()
				};

				$.ajax({
					url: BASE_URL + 'agent/tickets/messages/'+self.currentOpenMessageId+'/save-message-text.json',
					type: 'POST',
					data: postData,
					dataType: 'json',
					complete: function() {
						overlayEl.find('.save-text-loading').hide();
						overlayEl.find('.save-text-trigger').show();
					},
					success: function(info) {
						self.messageEditOverlay.close();
						var messageHtml = info.message_html;
						self.wrapper.find('article.message-' + self.currentOpenMessageId).find('.body-text-message').html(messageHtml);
					}
				});
			});

			this.messageEditOverlay = new DeskPRO.UI.Overlay({
				contentElement: this.getEl('message_edit_overlay'),
				fullScreen: true,
				fullScreenMargin: 55,
				onOverlayOpened: function() {
					overlayEl.find('input.message_id').val(self.currentOpenMessageId);

					if (!self.messageEditOverlay.hasInitRte) {
						self.messageEditOverlay.hasInitRte = true;
						overlayEl.find('textarea.message_text').height(overlayEl.find('.overlay-content').height() - 50);
						//DP.rteTextarea(overlayEl.find('textarea.message_text'), {});
						DeskPRO_Window.initRteAgentReply(overlayEl.find('textarea.message_text'), {
							autoresize: false
						});
					}

					//overlayEl.find('textarea.message_text').html('Loading...');
					overlayEl.find('textarea.message_text').setCode('Loading...');
					$.ajax({
						url: BASE_URL + 'agent/tickets/messages/'+self.currentOpenMessageId+'/get-message-text.json',
						dataType: 'json',
						success: function(data) {
							//overlayEl.find('textarea.message_text').html(data.message_html);
							overlayEl.find('textarea.message_text').setCode(data.message_html);
						}
					});
				}
			});
		}

		this.messageEditOverlay.open();
	},

	getReplyTextArea: function() {
		return this.getEl('replybox_wrap').find('textarea[name="message"]');
	},

	insertTextInReply: function(text) {
		var txt = this.getReplyTextArea();

		if (txt.data('redactor')) {
			txt.data('redactor').insertHtml(DP.convertTextToWysiwygHtml(text, true));
		} else {
			var pos = txt.getCaretPosition();
			if (!pos) {
				txt.setCaretPosition(0);
			}

			txt.insertAtCaret(text);
			txt.trigger('textareaexpander_fire');
		}
	},

	focusOnReply: function() {
		var txt = this.getReplyTextArea();

		if (txt.data('redactor')) {
			txt.setFocus();
		} else {
			txt.focus();
		}
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
			}
		});
	},

	//#################################################################
	//# Tasks
	//#################################################################

	_initTasks: function() {
		var self = this;
		var openForEl = null;

		var assignOb2 = this.getEl('task_assign_ob').clone().appendTo(this.wrapper);
		var menuVis2  = this.getEl('task_menu_vis').clone().appendTo(this.wrapper);

		var assignOptionBox = new DeskPRO.UI.OptionBox({
			element: this.getEl('task_assign_ob'),
			onClose: function(ob) {

				var agentId = parseInt(ob.getSelected('agents') || 0);
				var agentTeamId = parseInt(ob.getSelected('teams') || 0);

				var obel = self.getEl('task_assign_ob');

				if (agentId && agentId != DESKPRO_PERSON_ID) {
					var val = 'agent:' + agentId;
					var text = $('.agent-label-' + agentId).first().text().trim();
				} else if (agentTeamId) {
					var val = 'agent_team:' + agentTeamId;
					var text = $('.agent-team-label-' + agentTeamId).first().text().trim();
				} else {
					var val = '';
					var text = 'Me';
				}

				$('input.input-agent', openForEl).val(val);
				$('.opt-trigger.assigned_agent label', openForEl).text(text);
			}
		});

		var statusMenu = new DeskPRO.UI.Menu({
			menuElement: this.getEl('task_menu_vis'),
			onItemClicked: function(info) {
				$('input.input-vis', openForEl).val($(info.itemEl).data('vis'));
				$('.opt-trigger.visibility label', openForEl).text($(info.itemEl).text());
			}
		});

		var rowContainer = this.getEl('tasks_wrap');

		var openForEl = null;
		rowContainer.on('click', '.remove-row-trigger', function(ev) {
			var row = $(this).closest('.task-row');
			row.slideUp('fast', function() {
				row.remove();
				self.updateUi();
			});
		});
		rowContainer.on('click', '.opt-trigger.assigned_agent', function(ev) {
			openForEl = $(this).closest('.task-row');
			assignOptionBox.open(ev);
		});
		rowContainer.on('click', '.opt-trigger.visibility', function(ev) {
			openForEl = $(this).closest('.task-row');
			statusMenu.open(ev);
		});
		rowContainer.on('click', '.opt-trigger.date_due', function(ev) {
			var label = $('label', this);
			var row = $(this).closest('.task-row');
			var field = $('input.input-date-due', row);
			var date = $('input.input-date-due', row).val();
			if (!date) {
				date = new Date();
			}

			field.datepicker('dialog', date, function(date, inst) {
				$('input.input-date-due', row).val(date);
				label.text(date);
			}, {
				dateFormat: 'yy-mm-dd',
				showButtonPanel: true,
				beforeShow: function(input) {
					setTimeout(function() {
						var buttonPane = $(input).datepicker("widget").find(".ui-datepicker-buttonpane");

						$('button', buttonPane).remove();

						var btn = $('<button class="ui-datepicker-current ui-state-default ui-priority-secondary ui-corner-all" type="button">Clear</button>');
						btn.unbind("click").bind("click", function () { $.datepicker._clearDate( input ); label.text('No due date'); });
						btn.appendTo( buttonPane );

						$(input).datepicker("widget").css('z-index', 30101);
					},1);
				}
			}, ev);
		});

		this.getEl('task_save').on('click', function(ev) {
			ev.preventDefault();

			if ($(this).hasClass('saving')) {
				return;
			}

			$(this).addClass('saving').html('<em>Saving</em>');
			var postData = self.getEl('task_row').find('input').serializeArray();
			postData.push({
				name: 'from_ticket',
				value: 1
			});

			$.ajax({
				url: BASE_URL + 'agent/tasks/save',
				data: postData,
				type: 'POST',
				dataType: 'json',
				complete: function() {
					self.getEl('task_save').removeClass('saving').text('Add');
				},
				success: function(data) {

					updateTaskPane();
					self.getEl('newtask_title').val('');

					if (!data.tasks || !data.tasks[0]) {
						return;
					}

					data = data.tasks[0];
					var row = $(data.row_html);

					self.getEl('task_list').show().prepend(row);

					DeskPRO_Window.util.modCountEl(self.getEl('task_count'), '+', 1);

					if (DeskPRO_Window.sections.tasks_section) {
						DeskPRO_Window.sections.tasks_section.refresh();
					}
				}
			});
		});

		var control = new DeskPRO.Agent.PageHelper.TaskListControl(this.wrapper, {
			menuVis:  menuVis2,
			assignOb: assignOb2,
			completeCountEl: null
		});

		control.addEvent('updateUi', function() {
			self.updateUi();
			updateTaskPane();
		});
		control.addEvent('updateCount', function() {
			updateTaskPane();
		});

		var updateTaskPane = function() {
			if (DeskPRO_Window.sections.tasks_section) {
				DeskPRO_Window.sections.tasks_section.markUnloadPage();
			}
		};
	},

	//#################################################################
	//# Slas
	//#################################################################

	_initSlas: function() {
		var self = this;
		var form = this.getEl('sla_form');
		var idSelect = form.find('select[name=sla_id]');
		var rows = this.getEl('sla_rows');
		var tabHeader = this.getEl('sla_wrap_tab');

		var addSlaRow = function(html) {
			var add = $(html);

			rows.append(add);
			add.find('.timeago').timeago();
			rows.closest('table').show();

			tabHeader.append(
				$('<span />')
					.addClass('sla-pip')
					.addClass(add.data('sla-status'))
					.data('sla-id', add.data('sla-id'))
			);
		};

		var getVisibleOptions = function(options) {
			return options.filter(function() {
				return $(this).css('display') !== 'none';
			});
		};

		var rowRemoved = function(slaId) {
			var table = rows.closest('table');

			if (!table.find('tbody tr').length) {
				table.hide();
			}

			if (idSelect.length) {
				idSelect.find('option[value="' + slaId + '"]').show();
				if (getVisibleOptions(idSelect.find('option')).length > 1) {
					form.show();
				}
			}

			tabHeader.find('.sla-pip').each(function() {
				var $this = $(this);
				if ($this.data('sla-id') == slaId) {
					$this.remove();
					return false;
				}
			});
		};

		rows.on('click', 'a.sla-delete', function(e) {
			var $this = $(this);

			e.preventDefault();

			if (confirm(rows.data('delete-confirm'))) {
				$.ajax({
					url: $this.attr('href'),
					type: 'POST',
					dataType: 'json'
				}).done(function (json) {
					if (json.success) {
						var slaId = $this.closest('tr').data('sla-id');
						var table = $this.closest('table');

						$this.closest('tr').remove();

						rowRemoved(slaId);
					}
				});
			}
		});

		if (form.length) {
			if (getVisibleOptions(idSelect.find('option')).length <= 1) {
				// only the empty option
				form.hide();
			}

			var progress = this.getEl('sla_save_progress');

			DP.select(idSelect, {
				// todo: try to get it to hide hidden select elements
			});

			form.on('click', 'button', function() {
				var val = idSelect.val();
				if (val.length && val != '0') {
					progress.show();

					$.ajax({
						url: form.data('submit-url'),
						data: form.find('input, textarea, select').serialize(),
						type: 'POST',
						dataType: 'json'
					}).done(function(json) {
						if (json.inserted) {
							addSlaRow(json.html);

							idSelect.find('option[value="' + val + '"]').hide();
							if (getVisibleOptions(idSelect.find('option')).length <= 1) {
								// only the empty option
								form.hide();
							} else {
								idSelect.val('0');
							}
						}
					}).always(function() {
						progress.hide();
					});
				}
			});

			// manage sla updates to the ticket
			DeskPRO_Window.getMessageBroker().addMessageListener('agent.ticket-sla-updated', function(info) {
				if (info.ticket_id == self.getMetaData('ticket_id')) {
					rows.find('tr').each(function() {
						var row = $(this);
						if (row.data('sla-id') == info.sla_id) {
							if (info.removed) {
								row.remove();
								rowRemoved(info.sla_id);
							} else {
								row.find('.sla-status-icon').removeClass(info.original_status).addClass(info.sla_status);
								row.data('sla-status', info.sla_status);

								row.find('.warn-date').html(
									info.warn_date
										? $('<time class="timeago" datetime="' + info.warn_date + '"></time>').timeago()
										: 'N/A'
								);
								row.find('.fail-date').html(
									info.fail_date
										? $('<time class="timeago" datetime="' + info.fail_date + '"></time>').timeago()
										: 'N/A'
								);

								tabHeader.find('.sla-pip').each(function() {
									var pip = $(this);
									if (pip.data('sla-id') == info.sla_id) {
										pip.removeClass(info.original_status).addClass(info.sla_status);
										return false;
									}
								});
							}

							return false;
						}
					});
				}
			}, this.pageUid);
		}
	},

	//#################################################################
	//# Edit name
	//#################################################################

	_initEditName: function() {
		var self = this;
		var namef       = this.getEl('showname');
		var editName    = this.getEl('editname');
		var startBtn    = this.getEl('editname_start');
		var stopBtn     = this.getEl('editname_end');

		var startEditable = function() {
			namef.hide();
			editName.show();
			startBtn.hide();
			stopBtn.show();
		};

		var stopEditable = function() {
			var nametxt = editName.find('input').first();

			var setName = nametxt.val().trim();
			if(!setName) {
				return;
			}

			editName.hide();
			startBtn.show();
			namef.show();
			stopBtn.hide();
			namef.text(setName);

			var postData = [];
			postData.push({
				name: 'subject',
				value: setName
			});

			$.ajax({
				url: BASE_URL + 'agent/tickets/'+self.meta.ticket_id+'/ajax-save-subject.json',
				type: 'POST',
				data: postData
			});
		};

		namef.on('dblclick', startEditable).on('keypress', function(ev) {
			if (ev.keyCode == 13 /* enter key */) {
				ev.preventDefault();
				stopEditable();
			}
		});
		this.getEl('editname_start').on('click', startEditable);
		this.getEl('editname_end').on('click', stopEditable);
	}
});
