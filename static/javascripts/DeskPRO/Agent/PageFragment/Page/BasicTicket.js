Orb.createNamespace('DeskPRO.Agent.PageFragment.Page');

/**
 * Common code shared between ticket and newticket interfaces
 */
DeskPRO.Agent.PageFragment.Page.BasicTicket = new Class({

	Extends: DeskPRO.Agent.PageFragment.Basic,

	TYPENAME: 'basicticket',

	wrapper: null,

	destroyEls: [],
	destroyMenus: [],

	changeManager: null,
	valueForm: null,

	layout: null,

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

		this.valueForm = $('form.value-form:first', this.contentWrapper);
		this.changeManager = new DeskPRO.Agent.Ticket.ChangeManager(this);

		window.TICKET = this;

		this._initTicketOptionsMenus();
		this._initCustomFieldsEditor();

		this._initReplyBar();
		this._initAttachments();

		DeskPRO_Window.getMessageBroker().sendMessage('ticket.opened', { ticketId: this.getMetaData('ticket_id') });

		DeskPRO_Window.getMessageBroker().addMessageListener('tickets.deleted', (function(ticket_ids) {
			if (ticket_ids.indexOf(this.getMetaData('ticket_id')) !== -1) {
				DeskPRO_Window.removePage(this);
			}
		}).bind(this));

		DeskPRO_Window.getMessageBroker().addMessageListener('window.innerLayout.resize', (function() {
			this._handleResize()
		}).bind(this));
	},

	_handleResize: function() {
		if (!this.layout) return;
		this.layout.resizeAll();
	},

	destroyPage: function() {

		for (var i = 0; i < this.destroyEls.length; i++) {
			$(this.destroyEls[i]).remove();
		}

		for (var i = 0; i < this.destroyMenus.length; i++) {
			this.destroyMenus[i].destroy();
		}

		DeskPRO_Window.getMessageBroker().sendMessage('ticket.closed', { ticketId: this.getMetaData('ticket_id') });
	},

	//#################################################################
	//# Property managers
	//#################################################################

	propertyManagers: {},

	getPropertyManager: function(type) {

		if (this.propertyManagers[type]) {
			return this.propertyManagers[type];
		}

		var manager = null;
		switch (type) {
			case 'department_id':
		 	case 'category_id':
			case 'product_id':
			case 'priority_id':
			case 'status':
			case 'agent_id':
			case 'agent_team_id':
				manager = new DeskPRO.Agent.Ticket.Property.StandardOption(this, { optionName: type });
				break;
			case 'new_reply':
				manager = new DeskPRO.Agent.Ticket.Property.NewReply(this);
				break;
		}

		this.propertyManagers[type] = manager;

		return manager;
	},

	//#################################################################
	//# Ticket attachments
	//#################################################################

	_initAttachments: function() {
		var self = this;

		$('form.reply-form', this.barWrapper).fileUploadUI({
			url: this.getMetaData('uploadAttachUrl'),
			dropZone: $('div.reply', this.barWrapper),
			dropZoneEnlarge: function() {
				self.replySimpleTabs.activateTab($('.attachments.tab-trigger', self.ticketReplyTabs));
				self.barWrapper.addClass('upload-drop-over')
			},
			dropZoneReduce: function() {
				self.barWrapper.removeClass('upload-drop-over')
			},
			formData: function() { return []; },
			cancelSelector: '.cancel-trigger',
			uploadTable: $('.file-list', this.barWrapper),
			downloadTable: $('.file-list', this.barWrapper),
			initProgressBar: function () { return null; },
			buildUploadRow: function (files, index) {
				var file = files[index];
				return $('<li class="uploading">' + file.name + ' <span class="cancel-trigger">Cancel</span></li>');
			},
			buildDownloadRow: function (file) {
				return $('<li><input type="checkbox" checked="checked" name="attach[]" value="'+ file.blob_id + '" /> <a href="'+ file.download_url + '" target="_blank">' + file.filename + '</a></li>');
			}
		});
	},

	/*
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
			}
		});

		return true;
	},
	*/

	//#################################################################
	//# Ticket options menus
	//#################################################################

	ticketOptionsMenus: {},
	_initTicketOptionsMenus: function() {
		var options = ['department_id', 'category_id', 'product_id', 'priority_id', 'status', 'agent_id', 'agent_team_id'];
		var self = this;

		for (var i = 0; i < options.length; i++) {
			var opt = options[i];
			var btnClass = '.menu-trigger.'+opt+':first';
			var btnEl = $(btnClass, this.wrapper);
			var menu = new DeskPRO.UI.Menu({
				triggerElement: btnEl,
				menuElement: $('.ticket-menu.'+opt+':first', this.wrapper),
				onItemClicked: function(info) {
					self._handleTicketOptionClick(info);
				}
			});
			this.ticketOptionsMenus[opt] = menu;
			this.destroyMenus.push(menu);

			// And if its a no-value, update the proper title
			if ($('.no-value', btnEl).length) {
				//this._initNoValOption(opt, null);
			}
		}
	},

	_handleTicketOptionClick: function(info) {
		var opt = $(info.itemEl).parent().data('option-name');
		var itemId = $(info.itemEl).data('option-id');

		var prop = this.getPropertyManager(opt);
		this.changeManager.setInstantChange(prop, itemId);
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

		// Since the tabs were added to the document for absolute positioning,
		// we need to properly hide/show them on activation and deactivation
		// for when the reply bar is open when switching between tabs
		this.addEvent('activate', function() { if (self.ticketReply.is(':visible')) self.ticketReplyTabs.show(); });
		this.addEvent('deactivate', function() { self.ticketReplyTabs.hide(); });

		// Send reply
		$('li.submit-reply.trigger:first', this.barWrapper).click(function(ev) {
			ev.preventDefault(); // its wrapped in a form tag, we dont want to submit the page tho
			self._sendReply();
		});

		// Add +1 to zindex because we need to properly layer the ticketReplyTabs
		// - Under barWrapper (south pane), but above contentWrapper (content pane)
		this.barWrapper.css({
			'z-index': parseInt(this.barWrapper.css('z-index'))+1
		});

		// Init ticket reply tabs
		var simpleTabs = this.replySimpleTabs = new DeskPRO.UI.SimpleTabs({
			context: this.ticketReply,
			triggerElements: this.ticketReplyTabs.children('li.tab-trigger')
		});

		// Actions menu
		this.ticketActionsMenu = new DeskPRO.UI.Menu({
			triggerElement: $('ul.tools li.actions', this.ticketBar),
			menuElement: $('ul.ticket-info-edit-menu:first', this.contentWrapper),
			onItemClicked: this._handleActionsMenuClick.bind(this)
		});
		this.destroyMenus.push(this.ticketActionsMenu);

		// Macros menu
		this.ticketMacrosMenu = new DeskPRO.UI.Menu({
			triggerElement: $('ul.tools li.macros', this.ticketBar),
			menuElement: $('ul.ticket-macros-menu:first', this.contentWrapper),
			onItemClicked: this._handleMacroClick.bind(this)
		});
		this.destroyMenus.push(this.ticketMacrosMenu);

		// Macro apply/cancel
		$('ul.tools li.macros-apply', this.ticketBar).click((function() {
			this.changeManager.saveChanges();
			this.toggleMacroApplyBtn('off');
		}).bind(this));

		$('ul.tools li.macros-cancel', this.ticketBar).click((function() {
			this.changeManager.revertChanges();
			this.toggleMacroApplyBtn('off');
		}).bind(this));

		// Menus to change reply info
		var menu = this.actionMenu = new DeskPRO.UI.Menu({
			triggerElement: $('span.trigger.agent_id', this.ticketReply),
			menuElement: $('.reply-agent_id-menu', this.ticketReply),
			onItemClicked: (function(info) {
				var id = $(info.itemEl).data('option-value');
				var display = DeskPRO_Window.getDisplayName('agent', id);

				$('span.prop-val.agent_id', this.ticketReply).html(display);
				$('input[name="options[agent_id]"]', this.ticketReply).val(id);
			}).bind(this)
		});
		var menu = this.actionMenu = new DeskPRO.UI.Menu({
			triggerElement: $('span.trigger.agent_team_id', this.ticketReply),
			menuElement: $('.reply-agent_team_id-menu', this.ticketReply),
			onItemClicked: (function(info) {
				var id = $(info.itemEl).data('option-value');
				var display = DeskPRO_Window.getDisplayName('agent_team', id);

				$('span.prop-val.agent_team_id', this.ticketReply).html(display);
				$('input[name="options[agent_team_id]"]', this.ticketReply).val(id);
			}).bind(this)
		});
		var menu = this.actionMenu = new DeskPRO.UI.Menu({
			triggerElement: $('span.trigger.status', this.ticketReply),
			menuElement: $('.reply-status-menu', this.ticketReply),
			onItemClicked: (function(info) {
				var id = $(info.itemEl).data('option-value');
				var display = DeskPRO_Window.getDisplayName('status', id);

				$('span.prop-val.status', this.ticketReply).html(id);
				$('input[name="options[status]"]', this.ticketReply).val(id);
			}).bind(this)
		});
	},

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

	_handleMacroClick: function(info) {
		$.ajax({
			url: this.getMetaData('getMacroUrl').replace('$macro_id', $(info.itemEl).data('macro-id')),
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
			var prop = this.getPropertyManager(type);

			if (prop) {
				this.changeManager.addChange(prop, action);
			} else {
				console.warn('Unknown property `%s`. Actions: %o', type, actions);
			}
		}, this);

		this.changeManager.applyChanges();
		this.toggleMacroApplyBtn('on');
	},

	toggleMacroApplyBtn: function(force) {

		var ul = $('ul.tools', this.ticketBar);

		if (!force) {
			if ($('li.macros', ul).is(':visible')) {
				force = 'on';
			} else {
				force = 'off';
			}
		}

		var otherBtns = $('li.macros, li.actions', ul);;
		var applyBtns = $('li.macros-apply, li.macros-cancel', ul);

		if (force == 'on') {
			otherBtns.hide();
			applyBtns.show();
		} else {
			otherBtns.show();
			applyBtns.hide();
		}
	},

	toggleReplyBar: function(force) {

		if (!force) {
			if (this.ticketReply.is(':visible')) {
				force = 'off';
			} else {
				force = 'on';
			}
		}

		if (force == 'on') {

			// TODO: figure out correct css height maths here, where are 140 and 150 coming from?

			this.ticketReply.show().css({ 'height': 140 });
			this.barWrapper.addClass('expanded');
			this.layout.sizePane('south', 110 + this.ticketBar.outerHeight());

			$('div.placeholder', this.ticketBar).hide();
			$('li.submit-reply.trigger:first', this.barWrapper).show();

			this.ticketReplyTabs.css({
				'position': 'absolute',
				'top': this.barWrapper.offset().top - this.ticketReplyTabs.outerHeight() - 2,
				'left': this.barWrapper.offset().left,
				'display': 'block',
				'z-index': parseInt(this.barWrapper.css('z-index')),
				'width': this.barWrapper.width()-50
			});

			// When we open we should scroll down by the new height,
			// so the same position is visible in the center pane
			var h = this.barWrapper.outerHeight() + this.ticketReplyTabs.outerHeight() - 26; /* -26 for original size */
			this.contentWrapper.scrollTop(this.contentWrapper.scrollTop() + h);

			// Focus textarea
			$('textarea', this.ticketReply).focus();
		} else {
			this.ticketReplyTabs.hide();
			this.ticketReply.hide();
			this.barWrapper.removeClass('expanded');
			this.layout.sizePane('south', 27);

			$('div.placeholder', this.ticketBar).show();
			$('li.submit-reply.trigger:first', this.barWrapper).hide();

			this.ticketReplyTabs.hide();
		}
	},

	isSendingReply: false,
	_sendReply: function() {
		this._handleSendReply($('form.reply-form', this.ticketReply));
	},

	_handleSendReply: function(els) {
		console.warn('This method should be overriden in a subclass!');
	},

	_handleSendReplySuccess: function(html) {

		this.toggleReplyBar('off');
		this.displayNewMessage(html);
		this.afterNewReply();
	},

	afterNewReply: function() {
		// If there are new attachments, that tab is now stale
		if ($('.attachments-area ul.file-list li', this.ticketReply).length) {
			this.unloadTicketTab('attachments');
		}

		// New reply means theres a ticketlog entry of course
		this.unloadTicketTab('ticket-log');

		this.resetReply();
	},

	resetReply: function() {
		$('textarea[name="message"]', this.ticketReply).val('');
		$('.attachments-area ul.file-list', this.ticketReply).html('');

		// Make sure reply tab is selected again
		this.replySimpleTabs.activateTab($('.reply-area.tab-trigger', this.ticketReplyTabs));
	}
});