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

	initPage: function(el) {

		this.wrapper = el;
		this.contentWrapper = this.wrapper.children('.layout-content').attr('id', Orb.getUniqueId());
		this.barWrapper = $('.bar-wrapper', this.wrapper);

		this.valueForm = $('form.value-form:first', this.contentWrapper);
		this.valueForm.submit(function(ev) {
			// Never actually submit the form (would load a new page)
			ev.preventDefault();
		});
		this.changeManager = new DeskPRO.Agent.Ticket.ChangeManager(this);

		window.TICKET = this;

		if (!this.meta.isDeleted) {
			this._initCustomFieldsEditor();

			//this._initParticipants();
		}

		DeskPRO_Window.getMessageBroker().addMessageListener('window.innerLayout.resize', (function() {
			this._handleResize()
		}).bind(this));

		var self = this;
		$('div.ticket-messages > ul > li').each(function() {
			self._initMessage($(this));
		});

		// Custom field widgets
		$('input.date-field', this.contentWrapper).datepicker({ 'dateFormat': 'M d, yy'});

		this.ticketDisplay = new DeskPRO.Agent.PageHelper.TicketDisplay(this, {
			wrapper: el
		});

		this.initFeaturesOnCollection(this.wrapper, {
			routes: [],
			times: ['.timeago']
		});

		var cw = this.contentWrapper;
		cw.tinyscrollbar();
		$('div.scroll-content:first, div.scroll-viewport:first', this.contentWrapper).resize(function() {
			// When size changes within the pane, need to re-size the scroll
			cw.tinyscrollbar_update();
		});

		this.initRoutesOnCollection($('.with-route', this.wrapper));

		if (!this.meta.isDeleted) {
			this._initTicketActionsMenu();
			this._initMessageActionsMenu();
			this._initFlagMenu();
			this._initLabels();
		} else {
			$('button.undelete-trigger', this.wrapper).click(this.doTicketUndelete.bind(this));
		}

		this._initPopout();
		this._initTicketTabs();
		this._initTicketNotes();

		DeskPRO_Window.getMessageBroker().sendMessage('ui.ticket.opened', { ticketId: this.getMetaData('ticket_id') });
		DeskPRO_Window.getMessageBroker().sendMessage('ui.tab.opened', { type: 'tickets', id: this.getMetaData('ticket_id') });

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
			} else {
				$('div.log-row', msgWrap).hide();
			}
		};

		$('.message-controls input', this.wrapper).click(function() {
			updateMessageTypes();
		});

		// Goto reply
		$('button.goto-reply', this.wrapper).click(function() {
			cw.tinyscrollbar_scrolltop(1000000);
		});


		this.replyBox = new DeskPRO.Agent.PageFragment.Page.Ticket.ReplyBox(this, {
			replyBox: this.getEl('replybox'),
			onBeforeSaveReply: (function() {
				info.formData.push({
					name: 'client_messages_since',
					value: DeskPRO_Window.getLastClientMessageId()
				});
			}).bind(this),
			onSaveReplySuccess: (function(info) {
				this.displayNewMessage(info.result.message_html);
			}).bind(this)
		});

		this.ticketActions = new DeskPRO.Agent.PageFragment.Page.Ticket.TicketActions(this);
		this.ticketParticipants = new DeskPRO.Agent.PageFragment.Page.Ticket.Participants(this);
	},

	destroyPage: function() {

		for (var i = 0; i < this.destroyEls.length; i++) {
			$(this.destroyEls[i]).remove();
		}

		for (var i = 0; i < this.destroyMenus.length; i++) {
			this.destroyMenus[i].destroy();
		}

		for (var i = 0; i < this.destroyOverlays.length; i++) {
			this.destroyOverlays[i].destroy();
		}

		if (this.popoutPage) {
			this.popoutPage.destroyPage();
		}

		DeskPRO_Window.getMessageBroker().sendMessage('ui.ticket.closed', { ticketId: this.getMetaData('ticket_id') });
		DeskPRO_Window.getMessageBroker().removeTaggedListeners(this.pageUid);
	},

	displayNewMessage: function(html, slideCallback) {
		var new_message = $(html).hide();

		slideCallback = slideCallback || function(){};

		new_message.appendTo($(this.getEl('messages_wrap'))).slideDown('fast', slideCallback);

		this._initMessage(new_message);
		this.incCount('ticket-messages');
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

				// Inc note count
				this.incCount('ticket-notes');

				this.displayNewMessage(html);
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
	//# Ticket "tabs"
	//#################################################################

	_initTicketTabs: function() {

		var self = this;

		var bodyTabs = new DeskPRO.UI.SimpleTabs({
			context: $('.full-container-tabbed-contents-wrap', this.contentWrapper),
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

		// Top tabs
		/*
		var topTabs = new DeskPRO.UI.SimpleTabs({
			context: $('.container-tabbed-wrap.ticket-participants', this.contentWrapper),
			triggerElements: $('.container-tabbed-tabs li', this.contentWrapper),
			activeClassname: 'container-tabbed-tabs-active'
		});
		*/

		/*
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
		*/
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
		}
	},

	//#################################################################
	//# Popout
	//#################################################################

	personPopoutHtml: null,
	personPopoutWaiting: false,
	_initPopout: function() {
		return;
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

	ticketActionsMenu: null,
	ticketMacrosMenu: null,

	_handleActionsMenuClick: function(info) {
		var op = $(info.itemEl).data('option-id');

		switch (op) {
			case 'delete':
				$.ajax({
					url: this.getMetaData('deleteTicketUrl'),
					type: 'GET',
					data: {'ticket_ids[]': this.getMetaData('ticket_id') },
					context: this,
					dataType: 'json',
					success: function(data) {
						DeskPRO_Window.getMessageBroker().sendMessage('tickets.deleted', data.deleted_tickets);
					}
				});
				break;

			case 'print':
				var width = 700;
				var height = 550;
				var win = window.open(
					this.getMetaData('printTicketUrl'),
					"print_ticket_win_" + this.getMetaData('ticket_id'),
					"width="+width+",height="+height+",locationbar=false,directories=false,status=false,copyhistory=false"
				);
				break;
		}
	},

	_currentMacroId: null,
	_handleMacroClick: function(info) {

		if ($(info.itemEl).data('no-macro')) {
			var overlay = new DeskPRO.UI.Overlay({
				contentMethod: 'iframe',
				iframeUrl: BASE_URL + 'agent/settings/ticket-macros/new'
			});

			overlay.openOverlay();
			return;
		}

		this._currentMacroId = $(info.itemEl).data('macro-id');
		$.ajax({
			url: this.getMetaData('getMacroUrl').replace('$macro_id', this._currentMacroId),
			type: 'GET',
			context: this,
			dataType: 'json',
			success: function(data) {
				this._performMacro(data);
			}
		});
	},

	_performMacro: function (actions) {
		Object.each(actions, function(action, type) {

			var type_id = null;
			var m = /^(.*?)\[(.*?)\]$/.exec(type);
			if (m !== null) {
				type = m[1];
				type_id = m[2];
			}

			var prop = this.getPropertyManager(type, type_id);

			if (prop) {
				if (typeOf(action) == 'object' && action.value_display) {
					action = action.value_display;//custom fields
				}
				this.changeManager.addChange(prop, action);
			} else {
				console.warn('Unknown property `%s`. Actions: %o', type, actions);
			}
		}, this);

		this.changeManager.applyChanges();
		this.toggleMacroApplyBtn('on');
	},

	toggleMacroApplyBtn: function(force) {

		var ul = $('.bar-actions', this.ticketBar);

		if (!force) {
			if ($('li.macros', ul).is(':visible')) {
				force = 'on';
			} else {
				force = 'off';
			}
		}

		var otherBtns = $('li:not(.macro-on)', ul);
		var applyBtns = $('li.macro-on', ul);

		if (force == 'on') {
			otherBtns.hide();
			applyBtns.show();
		} else {
			otherBtns.show();
			applyBtns.hide();
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