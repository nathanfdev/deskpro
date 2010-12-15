
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
	
	initPage: function(el) {
		
		this.wrapper = el;
		
		this._initPopout();
		this._initReplyEvents();
		this._initTicketActionsMenu();
		this._initMessageActionsMenu();
		this._initTicketOptionsMenus();
		this._initCustomFieldsEditor();
		this._initTicketTabs();
	},
	
	displayNewMessage: function(html) {
		var last_message = $('.messages > ul > li.message-item:first', this.wrapper);
		var new_message = $(html).hide();
		new_message.insertBefore(last_message).slideDown();
	},
	
	_initTicketUploads: function() {
		$(".ticket-attach", this.wrapper).plupload({
			// General settings
			runtimes : 'flash,silverlight,browserplus,html5',
			url : 'upload.php',
			chunk_size : '1mb',
			unique_names : true,

			// Flash settings
			flash_swf_url : '/plupload/js/plupload.flash.swf',

			// Silverlight settings
			silverlight_xap_url : '/plupload/js/plupload.silverlight.xap'
		});
	},
	
	//#################################################################
	//# Ticket "tabs"
	//#################################################################
	
	_initTicketTabs: function() {
		
		var self = this;
		$('.ticket-tabs li', this.wrapper).click(function() {
			self.changeTicketTab($(this).data('tab-for'));
		});
	},
	
	changeTicketTab: function(to_tab) {
		console.log(to_tab);
		$('.tab-content', this.wrapper).removeClass('on');
		$(to_tab, this.wrapper).addClass('on');
		
		$('.tab-trigger', this.wrapper).removeClass('on');
		$($(to_tab).data('tab-trigger'), this.wrapper).addClass('on');
	},

	//#################################################################
	//# Ticket options menus
	//#################################################################
		
	_initTicketOptionsMenus: function() {
		var options = ['department', 'category', 'product', 'priority'];
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
		}
	},
	
	_handleTicketOptionClick: function(info) {
		var opt = $(info.itemEl).parent().data('option-name');
		var itemName = $(info.itemEl).html();
		var itemId = $(info.itemEl).data('option-id');
		
		// Replace the value of in the page
		var dd = $('.ticket-options-'+opt+'-btn dd').html(itemName);
		
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
	//# Ticket actions menu
	//#################################################################
	
	ticketActionsMenu: null,
	_initTicketActionsMenu: function() {
		var trigger =  $('.ticket-info-edit-btn', this.wrapper);
		this.ticketActionsMenu = new DeskPRO.UI.Menu({
			triggerElement: trigger,
			menuElement: $('.ticket-info-edit-menu:first', this.wrapper),
			
			// These two handlers prevent the gear button from disappearing
			// when not being hovered over anymore. (cuz its only displayed with css :hover)
			onMenuOpened: function() {
				trigger.css({'display': 'block'});
			},
			onMenuClosed: function() {
				trigger.css({'display': ''}); //back to default
			},
		});
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
		var width = this.custom_fields_display.width()
		
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
	//# New reply
	//#################################################################
	
	_initReplyEvents: function() {
		$('.agent-reply .placeholder textarea', this.wrapper).focus((function() {
			this.showEditor();
		}).bind(this));
	},
	
	showEditor: function() {
		if (!this._initReplyEditor()) {
			$('.editor-loading', this.wrapper).show();
			return;
		}
		
		var wrapper = this.wrapper;
		$('.agent-reply .placeholder', wrapper).slideUp(function() {
			$('.agent-reply .reply-area', wrapper).slideDown();
			$('.editor-loading', wrapper).hide();
		});
	},
	
	hasInitReplyEditor: false,
	_initReplyEditor: function() {
		if (this.hasInitReplyEditor) return true;
		this.hasInitReplyEditor = true;
		
		$('.editor-loading', wrapper).show();
		
		var self = this;
		var wrapper =  this.wrapper;
		var replyArea = $('.agent-reply .reply-area', this.wrapper);
		
		$('.btn-cancel', replyArea).click(function() {
			$('.agent-reply .reply-area', wrapper).slideUp(function() {
				$('.agent-reply .placeholder', wrapper).slideDown();
			});
		});
		
		$('.btn-submit', replyArea).click((function() {
			this._sendReply();
		}).bind(this));
		
	
		this.newReplyEditor = $('textarea', replyArea).tinymce({
			script_url: DP_TINYMCE_URL,
			theme : "advanced",
			theme_advanced_buttons1: "bold,italic,underline,|,bullist,numlist,|,outdent,indent,|,link,unlink,image,|,code,blockquote,hr,removeformat",
			theme_advanced_buttons2: "",
			theme_advanced_buttons3: "",
			theme_advanced_buttons4: "",
			theme_advanced_buttons5: "",
			theme_advanced_toolbar_location: "top",
			theme_advanced_toolbar_align : "left",
			theme_advanced_resizing: true,
			theme_advanced_resize_horizontal: false,
			theme_advanced_statusbar_location: 'bottom',
			theme_advanced_path: false,
			width : "98%",
			height: '130px',
			setup: function(ed) {
				ed.onInit.add(function(ed) {
					self.showEditor();
				});
			}
		});
		
		// the tinymce setup onInit will show the editor when its done
		return false;
	},
	
	_sendReply: function() {
		var replyArea = $('.agent-reply .reply-area', this.wrapper);
		$('.buttons', replyArea).hide();
		$('.send-reply-load', replyArea).show();
		
		var data = {
			'message': this.newReplyEditor.html()
		};
		$.ajax({
			url: BASE_URL + 'agent/tickets/' + this.getMetaData('ticket_id') + '/ajax-save-reply',
			type: 'POST',
			context: this,
			data: data,
			dataType: 'html',
			success: function(html) {
				this._handleSendReplySuccess(html);
			}
		});
	},
	
	_handleSendReplySuccess: function(html) {
		var wrapper = this.wrapper;
		var replyArea = $('.agent-reply .reply-area', this.wrapper);
		var ed = this.newReplyEditor;
		$('.agent-reply .reply-area', wrapper).slideUp((function() {
			$('.agent-reply .placeholder', wrapper).slideDown();
			$('.buttons', replyArea).show();
			$('.send-reply-load', replyArea).hide();
			ed.html('');
			
			this.displayNewMessage(html);
		}).bind(this));
	},
	
	
	
	//#################################################################
	//# Popout
	//#################################################################
	
	_initPopout: function() {
		var self = this;
		var el = this.wrapper;
		
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
		
		this.popout_overview_content.click(function() {
			DeskPRO_Window.runPageRouteFromElement(this);
		});
	},
	
	openPopOut: function(event) {
		var orig = $('.person-overview', this.wrapper);
		var pos = orig.offset();
		var wrapper_pos = this.wrapper.offset();
		
		// can use the left position of the element to roughly
		// determine how wide the columns are
		// so we want it to stretch as far as we can, minus some wriggle room
		var width = pos.left - 35;
		
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
				'top': (wrapper_pos.top - 8),
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
			this.popoutPage = new DeskPRO.Agent.PageFragment.Page.Person();
			this.popoutPage.setMetaData({
				person_id: 1
			});
			
			this.popoutPage.initPage(this.popout);
			this.hasInitPopout = true;
		}
	},
	
	closePopoutOnmouseout: function() {
		if (this.isMouseOverPopout) {
			return;
		}
		
		this.popout.hide();
		this.popout_overview.hide();
		this.popout_overview_content.hide();
	}
});