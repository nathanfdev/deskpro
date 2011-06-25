Orb.createNamespace('DeskPRO.Agent.PageFragment.Page');

DeskPRO.Agent.PageFragment.Page.Ticket = new Class({

	Extends: DeskPRO.Agent.PageFragment.Page.BasicTicket,

	TYPENAME: 'ticket',

	popout: null,
	popout_overview: null,

	isMouseOverPopout: false,
	hasInitPopout: false,
	popoutPage: null,

	initPage: function(el) {

		this.parent(el);

		var cw = this.contentWrapper;
		cw.tinyscrollbar();
		$('div.scroll-content:first, div.scroll-viewport:first', this.contentWrapper).resize(function() {
			// When size changes within the pane, need to re-size the scroll
			cw.tinyscrollbar_update();
		});

		this.initRoutesOnCollection($('.with-route', this.wrapper));

		this._initTicketActionsMenu();
		this._initPopout();
		this._initMessageActionsMenu();
		this._initTicketTabs();
		this._initFlagMenu();
		this._initLabels();
		this._initTicketNotes();

		// Need this to set the initial agent signature
		this.resetReply();

		DeskPRO_Window.getMessageBroker().sendMessage('ui.ticket.opened', { ticketId: this.getMetaData('ticket_id') });

		DeskPRO_Window.getMessageBroker().addMessageListener('tickets.deleted', (function(ticket_ids) {
			if (ticket_ids.indexOf(this.getMetaData('ticket_id')) !== -1) {
				DeskPRO_Window.removePage(this);
			}
		}).bind(this), this.pageUid);

		DeskPRO_Window.getMessageBroker().addMessageListener('tickets.check.' + this.getMetaData('ticket_id'), this.handleTicketCheck.bind(this), this.pageUid);
		//DeskPRO_Window.getMessageBroker().addMessageListener('tickets.updated.' + this.getMetaData('ticket_id'), this.getTicketUpdates.bind(this), this.pageUid);
		DeskPRO_Window.getMessageBroker().addMessageListener('tickets.new-messages.' + this.getMetaData('ticket_id'), this.getNewTicketMessages.bind(this), this.pageUid);

		Array.each(this.getMetaData('fieldHandlers', []), function(h) {
			if (!h) return;

			var handler_class = h.classname;
			var field_wrap_id = h.wrap_id;

			var h = new h($('#' + field_wrap_id), this);
			h.initPage();
		}, this);

		// Sit min length on middletabs so switch doesnt go much smaller
		var el = $('.container-tabbed-wrap.ticket-participants:first', this.wrapper);
		el.css('min-width', el.width());
	},

	destroyPage: function() {

		this.parent();

		if (this.popoutPage) {
			this.popoutPage.destroyPage();
		}

		DeskPRO_Window.getMessageBroker().sendMessage('ui.ticket.closed', { ticketId: this.getMetaData('ticket_id') });
		DeskPRO_Window.getMessageBroker().removeTaggedListeners(this.pageUid);
	},

	displayNewMessage: function(html) {
		var new_message = $(html).hide();

		var self = this;
		new_message.appendTo($('.ticket-messages .messages-wrap', this.contentWrapper)).slideDown(200, function() {
			// Scroll to it
			self.layout.content.scrollTop(10000000);
		});

		this._initMessage(new_message);
	},

	activate: function() {
		if (this.popoutPinIcon && this.popoutPinIcon.is('.on')) {
			this.popout.fadeIn(200);
		}
	},

	deactivate: function() {
		if (this.popoutPinIcon && this.popoutPinIcon.is('.on')) {
			this.popout.fadeOut(200);
		}
	},


	handleTicketCheck: function(info) {
		if (!info.isLocked) {
			$('div.lock-bar:first', this.contentWrapper).hide();
		}
	},

	//#################################################################
	//# Labels
	//#################################################################

	newnoteWrapper: null,
	_initTicketNotes: function() {
		this.newnoteWrapper = $('.new-note:first', this.contentWrapper);
		$('button', this.newnoteWrapper).click(this.saveNewNote.bind(this));
	},

	saveNewNote: function() {

		var loadingOn = $('.loading-on', this.newnoteWrapper).show();
		var loadingOff = $('.loading-off', this.newnoteWrapper).hide();

		var data = [];
		data.push({
			name: 'message',
			value: $('textarea', this.newnoteWrapper).val()
		});

		$.ajax({
			url: BASE_URL + 'agent/tickets/' + this.getMetaData('ticket_id') + '/ajax-save-note',
			type: 'POST',
			context: this,
			data: data,
			dataType: 'html',
			success: function(html) {
				loadingOn.hide();
				loadingOff.show();

				$('textarea', this.newnoteWrapper).val('');
				var el = $(html);
				this.newnoteWrapper.before(el);
				this._initMessage(el);

				this._handleSendReplySuccess(html);
			}
		});
	},

	//#################################################################
	//# Labels
	//#################################################################

	labelsList: null,
	_initLabels: function() {
		// Tags
		this.labelsList = $(".ticket-tags ul", this.contentWrapper);
		this.labelsTagit = this.labelsList.tagit({
			availableTags: this.getMetaData('labelsAutocompleteUrl'),
			enableBackspace: false,
			fieldName: 'labels',
			onchange: this.saveLabels.bind(this)
		});
	},

	_saveLabelsTimeout: null,
	saveLabels: function() {
		if (this.changeManager.hasChanges()) {
			// If change manager has changes, we dont save new/removed
			// tags
			return;
		}

		if (this._saveLabelsTimeout) {
			window.clearTimeout(this._saveLabelsTimeout);
		}

		this._saveLabelsTimeout = this._doSaveLabels.delay(2000, this);
	},

	_doSaveLabels: function() {
		var data = $(':input', this.labelsList).serializeArray();

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

				if (data.has_notes) {
					this.unloadTicketTab('ticket-notes');
				}
			}
		});
	},

	//#################################################################
	//# Ticket options menus
	//#################################################################

	/**
	 * Handle saving of a ticket option like department etc
	 *
	 * @see DeskPRO.Agent.PageFragment.Page.BasicTicket._handleTicketOptionClick()
	 */
	_handleTicketOptionSave: function(option, optionId) {
		// Update the value in teh DB
		DeskPRO_Window.startLoadingIndicator();

		var data = {};
		data[option] = optionId;

		$.ajax({
			url: BASE_URL + 'agent/tickets/' + this.getMetaData('ticket_id') + '/ajax-save-options',
			type: 'POST',
			context: this,
			data: data,
			dataType: 'json',
			success: function(data) {
				this._handleTicketOptionSaveSuccess(data);
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
		DeskPRO_Window.stopLoadingIndicator();

		DeskPRO_Window.getMessageBroker().sendMessage('filter-flagged.flag-changed', {
			old_flag: old_flag,
			new_flag: new_flag
		});
	},

	//#################################################################
	//# Ticket "tabs"
	//#################################################################

	_initTicketTabs: function() {

		var self = this;

		// Top tabs
		var topTabs = new DeskPRO.UI.SimpleTabs({
			context: $('.container-tabbed-wrap.ticket-participants', this.contentWrapper),
			triggerElements: $('.container-tabbed-tabs li', this.contentWrapper),
			activeClassname: 'container-tabbed-tabs-active'
		});

		// Body tabs
		var bodyTabs = new DeskPRO.UI.SimpleTabs({
			context: $('.full-container-tabbed.messages-container', this.contentWrapper),
			triggerElements: $('.full-container-tabbed-tabs li', this.contentWrapper),
			onTabSwitch: function(info) {
				if (info.tabEl.is('.ticket-log')) {
					self._loadTicketTab_Log();
				} else if (info.tabEl.is('.ticket-attach')) {
					self._loadTicketTab_Attach();
				} else if (info.tabEl.is('.ticket-related-content')) {
					self._loadTicketTab_RelatedContent();
				}
			}
		});
	},

	/**
 	 * Resets the 'loaded' status of a tab. If there are changes somewhere,
	 * its easiest to just reloaded the affected part the next time the user
	 * needs to see it.
	 */
	unloadTicketTab: function(tab) {
		var contentEl = $('.tab-content.' + tab, this.wrapper);
		contentEl.html('').addClass('unloaded');
	},

	_loadTicketTab_Log: function() {

		var contentEl = $('.tab-content.ticket-log', this.wrapper);

		if (!contentEl.is('.unloaded')) {
			// Already loaded
			return;
		}

		$.ajax({
			url: this.getMetaData('tabTicketLogUrl'),
			type: 'GET',
			dataType: 'html',
			success: function(html) {
				contentEl.html(html);
				contentEl.removeClass('unloaded');
			}
		});
	},

	_loadTicketTab_RelatedContent: function() {
		var contentEl = $('.tab-content.ticket-realted-content', this.wrapper);

		if (!contentEl.is('.unloaded')) {
			// Already loaded
			return;
		}

		$.ajax({
			url: this.getMetaData('tabRelatedContentUrl'),
			type: 'GET',
			dataType: 'html',
			success: function(html) {
				contentEl.html(html);
				contentEl.removeClass('unloaded');
			}
		});
	},

	_loadTicketTab_Attach: function() {

		var contentEl = $('.tab-content.ticket-attach', this.wrapper);

		if (!contentEl.is('.unloaded')) {
			// Already loaded
			return;
		}

		$.ajax({
			url: this.getMetaData('tabAttachmentsUrl'),
			type: 'GET',
			dataType: 'html',
			success: function(html) {
				contentEl.html(html);
				contentEl.removeClass('unloaded');
			}
		});
	},

	//#################################################################
	//# Message actions menu
	//#################################################################

	ticketActionsMenu: null,
	_initTicketActionsMenu: function() {

		$('button.undelete-trigger', this.wrapper).click(this.doTicketUndelete.bind(this));

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

				// Reload the ticket page
				DeskPRO_Window.loadPage(BASE_URL + 'agent/tickets/' + self.getMetaData('ticket_id'), {ignoreExist:true}, function(page) {
					DeskPRO_Window.removePage(self);

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
				DeskPRO_Window.startLoadingIndicator();
				$.ajax({
					url: this.getMetaData('getMessageQuoteUrl').replace('{message_id}', messageId),
					type: 'GET',
					context: this,
					dataType: 'json',
					success: function(data) {
						DeskPRO_Window.stopLoadingIndicator();
						this.toggleReplyBar('on');
						$('div.reply-form-fields:first textarea[name="message"]:first', this.ticketReply).val(data.message_quote + "\n\n");
					}
				});
				break;
		}
	},

	_handleSendReply: function(els) {
		if (this.isSendingReply) {
			return;
		}

		var spinnerContainer = $('.send-reply .spinner', this.ticketBar).show().empty();
		spinnerContainer.parent().addClass('is-loading');
		var spinner = new Spinner(spinnerContainer, {
			radii: [4,8],
			padding: 0
		}).play();

		this.isSendingReply = true;

		var data = els.serializeArray();

		$.ajax({
			url: BASE_URL + 'agent/tickets/' + this.getMetaData('ticket_id') + '/ajax-save-reply',
			type: 'POST',
			context: this,
			data: data,
			dataType: 'html',
			success: function(html) {
				$('button.submit-trigger', this.ticketReply).removeClass('gray');
				this.isSendingReply = false;
				this._handleSendReplySuccess(html);
			}
		});
	},

	//#################################################################
	//# Popout
	//#################################################################

	personPopoutHtml: null,
	personPopoutWaiting: false,
	_initPopout: function() {
		var self = this;
		var el = this.wrapper;

		// AJAX load the fragment now
		var url = this.getMetaData('viewPersonUrl');
		$.ajax({
			dataType: 'text',
			url: url,
			type: 'GET',
			success: function(html) {
				self.personPopoutHtml = html;
				if (self.personPopoutWaiting) {
					self.personPopoutWaiting = false;
					self._initPopoutPageFragment();
				}
			}
		});

		$('.person-overview', el).css({'cursor': 'pointer'}).click(function(event) {
			self.isMouseOverPopout = true;
			self.openPopOut(event);
		});
	},

	_initPopoutEls_done: false,
	_initPopoutEls: function() {

		if (this._initPopoutEls_done) return;
		this._initPopoutEls_done = true;

		var el = this.contentWrapper;
		var self = this;

		this.popout = $('.person-popout:first', el);
		this.popout.click(function(event) {
			// Any clicks that bubble here should stop now
			event.stopPropagation();
		});
		this.popout.detach().appendTo('body');
		this.destroyEls.push(this.popout);

		this.popoutOuter = $('.person-popout-outer:first', el);
		this.popoutOuter.detach().appendTo('body');
		this.destroyEls.push(this.popoutOuter);

		this.popoutTabs = $('.person-popout-tabs:first', el);
		this.popoutTabs.detach().appendTo('body');
		this.destroyEls.push(this.popoutTabs);

		var self = this;
		$('.close:first', this.popoutTabs).click(function() {
			self.closePopout();
		});

		$('.move-to-tab:first', this.popoutTabs).click(function() {
			DeskPRO_Window.runPageRouteFromElement($('.person-overview', self.wrapper));
			self.closePopout();
		});
	},

	openPopOut: function(event) {

		this._initPopoutEls();

		// Already open
		if (this.popout.is(':visible')) {
			return;
		}

		var orig = $('.person-overview:first', this.wrapper);
		var pos = orig.offset();
		var wrapper_pos = this.wrapper.offset();

		// can use the left position of the element to roughly
		// determine how wide the columns are
		// so we want it to stretch as far as we can, minus some wriggle room
		var width = pos.left - 35;

		// ... but not too big
		if (width > 780) {
			width = 780;
		}

		var show_popout = true;
		if (width < 400) {
			show_popout = false;
		}

		if (show_popout) {
			this.popout.css({
				'position': 'absolute',
				'display': 'block',
				'z-index': 999998,
				'width': width,
				'overflow': 'auto'
			});

			// Separate on purpose, we need the outerWidth which
			// wont be correct until the above rules are applied
			this.popout.css({
				'top': (wrapper_pos.top - 8),
				'left': (pos.left - this.popout.outerWidth() - 20),
				'bottom': 30
			});

			var poppos = this.popout.offset();
			this.popoutOuter.css({
				'position': 'absolute',
				'display': 'block',
				'z-index': 999997,
				'width': width+2+6, //2px for thi sborder, 6px for the popout border
				'overflow': 'auto',
				'top': poppos.top-1,
				'left': poppos.left-1,
				'bottom': 29 //popout bottom (30) -1 for the white border
			});

			this.popoutTabs.css({
				'z-index': 999996,
				'display': 'block',
				'top': (wrapper_pos.top - 30),
				'left': (pos.left - 260)
			});
		}

		if (!this.hasInitPopout && show_popout) {
			if (this.personPopoutHtml) {
				this._initPopoutPageFragment();
			} else {
				this.personPopoutWaiting = true;
			}
		}
	},

	closePopout: function() {
		this.popout.hide();
		this.popoutOuter.hide();
		this.popoutTabs.hide();
	},

	_initPopoutPageFragment: function() {
		this.popoutPage = DeskPRO_Window.createPageFragment(this.personPopoutHtml);
		this.popout.html(this.personPopoutHtml);
		this.personPopoutHtml = null;
		this.popoutPage.initPage(this.popout);
		this.hasInitPopout = true;
	},

	//#################################################################
	//# Reply bar
	//#################################################################

	_initReplyBar: function() {
		this.parent();

		var self = this;

		$('input.reply-assign-trigger', this.ticketReply).click(function(ev) {
			// We can uncheck easy
			if ($(this).val() != '0') {
				$(this).attr('checked', false).val('0');
				$('span.reply-assign-label', self.ticketReply).hide().html('');

			// To check popup the menu
			} else {
				ev.preventDefault();

				ev.customEvents = new Events();
				ev.customEvents.addEvent('itemClicked', self._handleReplybarAssign.bind(self));

				self.ticketOptionsMenus['agent_id'].openMenu(ev);
			}
		});
		$('span.reply-assign-label', this.ticketReply).click(function(ev) {
			ev.customEvents = new Events();
			ev.customEvents.addEvent('itemClicked', self._handleReplybarAssign.bind(self));
			self.ticketOptionsMenus['agent_id'].openMenu(ev);
		});

		var menu = new DeskPRO.UI.Menu({
			menuElement: $('ul.cc-to-menu:first', this.ticketReply)
		});
		this.destroyMenus.push(menu);

		var ccToInput = $('ul.cc-to-menu:first input', this.ticketReply);

		var ccCheck = $('input.cc-to-trigger', this.ticketReply).click(function(ev) {

			// We can uncheck easy
			if ($(this).val() != '') {
				$(this).attr('checked', false).val('0');
				$('span.cc-to-label', self.ticketReply).hide().html('');

			// To check popup the menu
			} else {
				ev.preventDefault();
				menu.openMenu(ev);
				ccCheck.focus();
			}
		})
		$('span.cc-to-label', this.ticketReply).click(function(ev) {
			menu.openMenu(ev);
		});

		var ccSaveBtn = $('button.cc-to-save-trigger', this.ticketReply).click(function(ev) {
			var val = ccToInput.val().trim();

			if (val.length) {
				ccCheck.attr('checked', true).val(val);

				var labelEl = $('span.cc-to-label', self.ticketReply);
				var displayName = labelEl.data('label').replace('%email%', val);

				labelEl.html(displayName).show();

				menu.closeMenu();

			} else {
				ccCheck.attr('checked', false).val('');
				$('span.cc-to-label', self.ticketReply).hide().html('');
			}
		});
	},

	_handleReplybarAssign: function(info) {
		var agentId = $(info.itemEl).data('option-id');
		var agentName = $(info.itemEl).html();

		if (agentId) {
			$('input.reply-assign-trigger', this.ticketReply).attr('checked', true).val(agentId);
			var labelEl = $('span.reply-assign-label', this.ticketReply);
			var displayName = labelEl.data('label').replace('%agent%', agentName);

			labelEl.html(displayName).show();
		} else {
			$('input.reply-assign-trigger', this.ticketReply).attr('checked', false).val('0');
			$('span.reply-assign-label', this.ticketReply).hide().html('');
		}
	}
});