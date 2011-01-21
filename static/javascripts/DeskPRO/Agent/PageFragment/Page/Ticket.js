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
		
		this._initPopout();
		this._initMessageActionsMenu();
		this._initTicketTabs();
		this._initTicketAttach();
		this._initFlagMenu();
		this._initLabels();
	},
	
	destroyPage: function() {
		
		this.parent();
		
		if (this.popoutPage) {
			this.popoutPage.destroyPage();
		}
	},
	
	displayNewMessage: function(html) {
		var new_message = $(html).hide();
		new_message.appendTo($('.messages > ul', this.contentWrapper)).slideDown();
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
	
	//#################################################################
	//# Labels
	//#################################################################
	
	labelsList: null,
	_initLabels: function() {
		// Tags
		this.labelsList = $("ul.tagit.ticket", this.contentWrapper).tagit({
			availableTags: this.getMetaData('labelsAutocompleteUrl'),
			enableBackspace: false,
			fieldName: 'labels',
			onchange: this.saveLabels.bind(this)
		});	
	},
	
	_saveLabelsTimeout: null,
	saveLabels: function() {
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
		var item = $(info.itemEl);
		var flag = item.data('flag');
		
		var m = $('.ticket-flag:first', this.wrapper);
		
		var old_flag = m.data('flag');
		
		m.removeClass('icon-flag-'+old_flag);
		m.addClass('icon-flag-'+flag);
		m.data('flag', flag);

		DeskPRO_Window.startLoadingIndicator();
		
		$.ajax({
			url: BASE_URL + 'agent/tickets/' + this.getMetaData('ticket_id') + '/ajax-save-flagged',
			type: 'POST',
			context: this,
			data: { color: flag },
			dataType: 'json',
			success: function(data) {
				this._handleFlagMenuClickSuccess(old_flag, flag);
			}
		});
	},
	
	_handleFlagMenuClickSuccess: function(old_flag, new_flag) {
		DeskPRO_Window.stopLoadingIndicator();
		
		DeskPRO_Window.getMessageBroker().sendMessage('queue-flagged.flag-changed', {
			old_flag: old_flag,
			new_flag: new_flag
		});
	},
	
	//#################################################################
	//# Ticket "tabs"
	//#################################################################
	
	_initTicketTabs: function() {
		
		var self = this;
		var simpleTabs = new DeskPRO.UI.SimpleTabs({
			context: this.contentWrapper,
			triggerElements: $('.ticket-tabs li', this.contentWrapper),
			onTabSwitch: function(info) {
				if (info.tabEl.is('.ticket-log')) {
					self._loadTicketLog();
				}
			}
		});

	},
	
	_loadTicketLog: function() {
		
		if ($('.tab-content.ticket-log', this.wrapper).data('is-loaded')) {
			// Already loaded
			return;
		}
		
		$.ajax({
			url: BASE_URL + 'agent/tickets/' + this.getMetaData('ticket_id') + '/ajax-ticket-log',
			type: 'GET',
			context: this,
			dataType: 'html',
			success: function(html) {
				this._loadTicketLogSuccess(html);
			}
		});
	},
	
	_loadTicketLogSuccess: function(html) {
		$('.tab-content.ticket-log', this.wrapper).html(html).data('is-loaded', true);
	},
	
	//#################################################################
	//# Message actions menu
	//#################################################################
	
	messageActionsMenu: null,
	_initMessageActionsMenu: function() {		
		this.messageActionsMenu = new DeskPRO.UI.Menu({
			triggerElement: null,
			menuElement: $('.ticket-message-edit-menu:first', this.wrapper),

			onMenuOpened: function(data) {
				var trigger = $(data.menu.getOpenTriggerElement());
				trigger.css({'display': 'block'});
			},
			onMenuClosed: function(data) {
				var trigger = $(data.menu.getOpenTriggerElement());
				trigger.css({'display': ''}); //back to default
			}
		});
		
		this.destroyMenus.push(this.messageActionsMenu);
		
		// We're using a live event because new messages are always
		// added. So we take care of opening the menu manually.
		var menu = this.messageActionsMenu;
		var ul = $('.messages > ul', this.wrapper)[0];
		$('.ticket-message-edit-btn', ul).live('click', function(event) {
			menu.openMenu(event);
		});
	},
	
	_handleSendReply: function(els) {
		if (this.isSendingReply) {
			return;
		}

		$('button.submit-trigger', this.ticketReply).addClass('gray');
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

	popoutPinIcon: null,
	_initPopout: function() {
		var self = this;
		var el = this.wrapper;
		
		$('.person-overview', el).css({'cursor': 'pointer'}).click(function(event) {
			self.isMouseOverPopout = true;
			self.openPopOut(event);
		}).mouseout(function(event) {
			self.isMouseOverPopout = false;
			self.closePopoutOnmouseout.delay(10, self);
		});
	},

	_initPopoutEls_done: false,
	_initPopoutEls: function() {
		
		if (this._initPopoutEls_done) return;
		this._initPopoutEls_done = true;
		
		var el = this.contentWrapper;
		var self = this;
		
		this.popoutPinIcon = $('.person-popout .pin-icon', this.wrapper).click((function () {
			this.togglePinPopout();
		}).bind(this));
		
		this.popout = $('.person-popout', el);
		this.popout.mouseover(function() {
			self.isMouseOverPopout = true;
		}).mouseout(function(event) {
			self.isMouseOverPopout = false;
			self.closePopoutOnmouseout.delay(10, self);
		}).click(function(event) {
			event.stopPropagation();
		});
		this.popout.detach().appendTo('body');
		this.destroyEls.push(this.popout);
		
		this.popout_overview = $('.person-overview-popout', el);
		this.popout_overview.mouseover(function() {
			self.isMouseOverPopout = true;
		}).mouseout(function() {
			self.isMouseOverPopout = false;
			self.closePopoutOnmouseout.delay(10, self);
		});
		this.popout_overview.detach().appendTo('body');
		this.destroyEls.push(this.popout_overview);
		
		this.popout_overview_content = $('.person-overview-popout-content', el);
		this.popout_overview_content.detach().appendTo('body');
		this.popout_overview_content.mouseover(function() {
			self.isMouseOverPopout = true;
		}).mouseout(function() {
			self.isMouseOverPopout = false;
			self.closePopoutOnmouseout.delay(10, self);
		});
		this.destroyEls.push(this.popout_overview_content);
		
		$('.info h1', this.popout_overview_content).tipTip({defaultPosition: 'top'});
		
		this.popout_overview_content.dblclick(function() {
			DeskPRO_Window.runPageRouteFromElement(this);
		});
	},
	
	openPopOut: function(event) {
		
		this._initPopoutEls();
		
		// Already open
		if (this.popout.is(':visible')) {
			return;
		}
		
		var orig = $('.person-overview', this.wrapper);
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
			this.popout.css({
				'top': (wrapper_pos.top - 15),
				'left': (pos.left - this.popout.outerWidth() - 20),
				'bottom': 30
			});
		}
		
		this.popout_overview_content.css({
			'position': 'absolute',
			'top': pos.top - this.popout_overview_content.padding().top,
			'left': pos.left - this.popout_overview_content.padding().left,
			'display': 'block',
			'width': orig.width() + 25,
			'height': orig.height(),
			'z-index': 999996
		})
		
		this.popout_overview.css({
			'position': 'absolute',
			'top': pos.top - this.popout_overview.padding().top,
			'left': pos.left - this.popout_overview.padding().left - 20,
			'display': 'block',
			'width': orig.width() + 30,
			'height': orig.height(),
			'z-index': 999996
		});
		
		if (!this.hasInitPopout && show_popout) {
			this.popoutPage = new DeskPRO.Agent.PageFragment.Page.PersonPopout();
			this.popoutPage.setMetaData({
				person_id: 1
			});
			
			this.popoutPage.initPage(this.popout);
			this.hasInitPopout = true;
		}
	},
	
	togglePinPopout: function() {
		
		if (!this._initPopoutEls_done) return;
		
		// Turn off
		if (this.popoutPinIcon.is('.on')) {
			this.popoutPinIcon.removeClass('on');
			this.isMouseOverPopout = false;
			this.closePopoutOnmouseout();
			
		// Turn on
		} else {
			this.popoutPinIcon.addClass('on');
			this.popout_overview.hide();
			this.popout_overview_content.hide()
		}
	},
	
	closePopoutOnmouseout: function() {
		if (!this._initPopoutEls_done || this.isMouseOverPopout || this.popoutPinIcon.is('.on')) {
			return;
		}
		
		this.popout.hide();
		this.popout_overview.hide();
		this.popout_overview_content.hide();
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
			
				self.ticketOptionsMenus['agent'].openMenu(ev);
			}
		});
		$('span.reply-assign-label', this.ticketReply).click(function(ev) {
			ev.customEvents = new Events();
			ev.customEvents.addEvent('itemClicked', self._handleReplybarAssign.bind(self));
			self.ticketOptionsMenus['agent'].openMenu(ev);
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