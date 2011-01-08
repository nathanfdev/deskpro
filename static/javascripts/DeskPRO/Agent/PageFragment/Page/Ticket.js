
Orb.createNamespace('DeskPRO.Agent.PageFragment.Page');
DeskPRO.Agent.PageFragment.Page.Ticket = new Class({
	
	Extends: DeskPRO.Agent.PageFragment.Basic,
	
	TYPENAME: 'ticket',

	wrapper: null,
	popout: null,
	popout_overview: null,
	
	isMouseOverPopout: false,
	hasInitPopout: false,
	popoutPage: null,
	
	destroyEls: [],
	destroyMenus: [],
	
	initPage: function(el) {

		this.wrapper = el;
		this.contentWrapper = this.wrapper.children('.ticket-content').attr('id', Orb.getUniqueId());
		this.barWrapper = this.wrapper.children('.ticket-bar').attr('id', Orb.getUniqueId());
		
		this.layout = this.wrapper.layout({
			center: {
				paneSelector: '#' + this.contentWrapper.attr('id')
			},
			south: {
				paneSelector: '#' + this.barWrapper.attr('id'),
				size: 27,
				spacing_open: 0,
				spacing_closed: 0
			}
		});
		
		this._initPopout();
		this._initMessageActionsMenu();
		this._initTicketOptionsMenus();
		this._initCustomFieldsEditor();
		this._initTicketTabs();
		this._initTicketAttach();
		this._initFlagMenu();
		
		this._initReplyBar();
	},
	
	destroyPage: function() {
		
		for (var i = 0; i < this.destroyEls.length; i++) {
			$(this.destroyEls[i]).remove();
		}
		
		for (var i = 0; i < this.destroyMenus.length; i++) {
			this.destroyMenus[i].destroy();
		}
	},
	
	displayNewMessage: function(html) {
		var new_message = $(html).hide();
		new_message.appendTo($('.messages > ul', this.contentWrapper)).slideDown();
	},
	
	activate: function() {
		if (this.popoutPinIcon.is('.on')) {
			this.popout.fadeIn(200);
		}
	},
	
	deactivate: function() {
		if (this.popoutPinIcon.is('.on')) {
			this.popout.fadeOut(200);
		}
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
	//# Ticket attachments
	//#################################################################
	
	_initTicketAttach: function() {
		
		$('.ticket-attach-upload-btn', this.wrapper).click((function() {
			this.openAttachOverlay();
		}).bind(this));
	},
	
	openAttachOverlay: function() {
		if (!this._initAttachOverlay()) {
			return;
		}
		
		this.ticketAttachOverlay.openOverlay();
	},
	
	ticketAttachOverlay: null,
	hasInitAttachOverlay: false,
	_initAttachOverlay: function() {
		
		if (this.hasInitAttachOverlay) {
			return true;
		}
		
		this.hasInitAttachOverlay = true;
		
		this.ticketAttachOverlay = new DeskPRO.UI.Overlay({
			contentElement: $('.ticket-attach.overlay:first', this.wrapper),
			customClassname: 'no-pad'
		});
		
		var self = this;
		
		$(".ticket-attach-widget", this.wrapper).pluploadQueue({
			// General settings
			runtimes : 'flash,silverlight,browserplus,html5',
			url : BASE_URL + 'agent/misc/accept-upload',
			chunk_size : '1mb',
			unique_names : true,
			multiple_queues: true,

			// Flash settings
			flash_swf_url : ASSETS_BASE_URL + 'javascripts/plupload/plupload.flash.swf',

			// Silverlight settings
			silverlight_xap_url : ASSETS_BASE_URL + 'javascripts/plupload/plupload.silverlight.xap',
			
			init: {
				Error: function(up, args) {
					console.warn('[Upload Error] %o', args);
				},
				FileUploaded: function(up, file, info) {
					console.info('[Upload Done] %o %o', file, info);
					var name = 'attach['+file.id+']';
					var html = '<li><input type="checkbox" name="'+name+'[save]" value="1" checked="checked" />';
					html += '<input type="hidden" name="'+name+'[name]" value="'+file.name+'" />';
					html += '<input type="hidden" name="'+name+'[tmp_name]" value="'+file.target_name+'" />';
					html += ' ' + file.name + '</li>';
					$('.ticket-newreply-attach-list', self.wrapper).append(html);
				}
				/*
				UploadProgress: function(up, file) {
					console.debug('[Upload Progress] %o', file);
				}
				*/
			}
		});
		
		return true;
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
	//# Ticket options menus
	//#################################################################
		
	_initTicketOptionsMenus: function() {
		var options = ['department', 'category', 'product', 'priority', 'status'];
		var self = this;
		
		for (var i = 0; i < options.length; i++) {
			var opt = options[i];
			var menu = new DeskPRO.UI.Menu({
				triggerElement: $('.ticket-options-'+opt+'-btn', this.wrapper),
				menuElement: $('.ticket-options-'+opt+'-menu', this.wrapper),
				onItemClicked: function(info) {
					self._handleTicketOptionClick(info);
				}
			});
			this.destroyMenus.push(menu);
		}
	},
	
	_handleTicketOptionClick: function(info) {
		var opt = $(info.itemEl).parent().data('option-name');
		var itemName = $(info.itemEl).html();
		var itemId = $(info.itemEl).data('option-id');
		
		// Replace the value of in the page
		var val_el = $('.ticket-options-'+opt+'-btn dd, .ticket-options-'+opt+'-btn .val', this.wrapper);		
		val_el.html(itemName);
		
		// Update the value in teh DB
		DeskPRO_Window.startLoadingIndicator();
		
		var data = {};
		data[opt] = itemId;
		
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
	
	_handleTicketOptionSaveSuccess: function(data) {
		DeskPRO_Window.stopLoadingIndicator();
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
			},
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
		
		$('.close-trigger', this.custom_fields_edit).click((function() {
			this.closeCustomFieldEditor();
		}).bind(this));
		
		$('.save-trigger', this.custom_fields_edit).click((function() {
			this.saveCustomFields();
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
	
	saveCustomFields: function() {
		$('.buttons .loading-off', this.custom_fields_edit).hide();
		$('.buttons .loading-on', this.custom_fields_edit).show();
		
		var data = $(':input', this.custom_fields_edit).serializeArray();
		
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
	//# Reply bar
	//#################################################################
	
	ticketBar: null,
	ticketReply: null,
	ticketReplyTabs: null,
	
	ticketActionsMenu: null,
	ticketMacrosMenu: null,
	
	_initReplyBar: function() {
		this.ticketBar = this.barWrapper.children('div.bar');
		this.ticketReply = this.barWrapper.children('div.reply');
		
		this.ticketReplyTabs = $('.ticket-reply-tabs', this.barWrapper).detach().appendTo('body');
		this.destroyEls.push(this.ticketReplyTabs);
		
		var self = this;
		$('input.placeholder', this.ticketBar).click(function() {
			self.toggleReplyBar();
		});
		
		this.ticketReplyTabs.children('li.close-trigger').click(function() {
			self.toggleReplyBar();
		});
		
		// Send reply
		$('button.submit-trigger', this.ticketReply).click(function(ev) {
			ev.preventDefault(); // its wrapped in a form tag, we dont want to submit the page tho
			self._sendReply();
		});
		
		// Add +1 to zindex because we need to properly layer the ticketReplyTabs
		// - Under barWrapper (south pane), but above contentWrapper (content pane)
		this.barWrapper.css({
			'z-index': parseInt(this.barWrapper.css('z-index'))+1
		});
		
		// Init ticket reply tabs
		var simpleTabs = new DeskPRO.UI.SimpleTabs({
			context: this.ticketReply,
			triggerElements: this.ticketReplyTabs.children('li.tab-trigger')
		});
		
		// Actions menu
		this.ticketActionsMenu = new DeskPRO.UI.Menu({
			triggerElement: $('ul.tools li.actions', this.ticketBar),
			menuElement: $('ul.ticket-info-edit-menu:first', this.contentWrapper)
		});
		this.destroyMenus.push(this.ticketActionsMenu);
		
		// Macros menu
		this.ticketMacrosMenu = new DeskPRO.UI.Menu({
			triggerElement: $('ul.tools li.macros', this.ticketBar),
			menuElement: $('ul.ticket-macros-menu:first', this.contentWrapper)
		});
		this.destroyMenus.push(this.ticketMacrosMenu);
	},

	toggleReplyBar: function(force) {
		
		if (!force) {
			if (this.ticketBar.is(':visible')) {
				force = 'on';
			} else {
				force = 'off';
			}
		}
		
		if (force == 'on') {
			this.ticketBar.hide();
			this.ticketReply.show();
			this.barWrapper.addClass('expanded');
			this.layout.sizePane('south', 150);
			
			this.ticketReplyTabs.css({
				'position': 'absolute',
				'top': this.barWrapper.offset().top - this.ticketReplyTabs.outerHeight() - 2,
				'left': this.barWrapper.offset().left,
				'display': 'block',
				'z-index': parseInt(this.barWrapper.css('z-index'))
			});
			
			// When we open we should scroll down by the new height,
			// so the same position is visible in the center pane
			var h = this.barWrapper.outerHeight() + this.ticketReplyTabs.outerHeight() - 26; /* -26 for original size */
			this.contentWrapper.scrollTop(this.contentWrapper.scrollTop() + h);
			
			// Focus textarea
			$('textarea', this.ticketReply).focus();
		} else {
			this.ticketReplyTabs.hide();
			this.ticketBar.show();
			this.ticketReply.hide();
			this.barWrapper.removeClass('expanded');
			this.layout.sizePane('south', 27);
			
			this.ticketReplyTabs.hide();
		}
	},
	
	isSendingReply: false,
	_sendReply: function() {
		
		if (this.isSendingReply) {
			return;
		}
		
		$('button.submit-trigger', this.ticketReply).addClass('gray');
		this.isSendingReply = true;
		
		var data = $('form.reply-form', this.ticketReply).serializeArray();

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
	
	_handleSendReplySuccess: function(html) {
		
		this.toggleReplyBar('off');
		this.displayNewMessage(html);
		$('textarea[name="message"]', this.ticketReply).val('');
	},
	
	
	//#################################################################
	//# Popout
	//#################################################################

	popoutPinIcon: null,
	_initPopout: function() {
		var self = this;
		var el = this.wrapper;
		
		this.popoutPinIcon = $('.person-popout .pin-icon', this.wrapper).click((function () {
			this.togglePinPopout();
		}).bind(this));
		
		$('.person-overview', el).mouseover(function(event) {
			self.isMouseOverPopout = true;
			self.openPopOut(event);
		}).mouseout(function(event) {
			self.isMouseOverPopout = false;
			self.closePopoutOnmouseout.delay(10, self);
		});
		
		$('.person-popout', el).click(function(event) {
			event.stopPropagation();
		});
		
		
		this.popout = $('.person-popout', el);
		this.popout.mouseover(function() {
			self.isMouseOverPopout = true;
		}).mouseout(function(event) {
			self.isMouseOverPopout = false;
			self.closePopoutOnmouseout.delay(10, self);
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
		if (this.isMouseOverPopout || this.popoutPinIcon.is('.on')) {
			return;
		}
		
		this.popout.hide();
		this.popout_overview.hide();
		this.popout_overview_content.hide();
	}
});