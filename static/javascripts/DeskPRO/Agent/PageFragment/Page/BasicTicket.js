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
		this.changeManager.addEvent('changesApplied', this.handleTicketChanges.bind(this));

		window.TICKET = this;

		this._initTicketOptionsMenus();
		this._initCustomFieldsEditor();

		this._initReplyBar();
		this._initAttachments();

		DeskPRO_Window.getMessageBroker().addMessageListener('window.innerLayout.resize', (function() {
			this._handleResize()
		}).bind(this));

		this.handleTicketChanges();

		var self = this;
		$('div.ticket-messages > ul > li').each(function() {
			self._initMessage($(this));
		});

		// Custom field widgets
		$('input.date-field', this.contentWrapper).datepicker({ 'dateFormat': 'M d, yy'});
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

	//#################################################################
	//# Property managers
	//#################################################################

	propertyManagers: {},

	getPropertyManager: function(type, type_id) {

		if (this.propertyManagers[type]) {
			return this.propertyManagers[type];
		}

		var manager = null;
		switch (type) {
			case 'department_id':
		 	case 'category_id':
			case 'product_id':
			case 'workflow_id':
			case 'priority_id':
			case 'status':
			case 'agent_id':
			case 'agent_team_id':
				manager = new DeskPRO.Agent.Ticket.Property.StandardOption(this, { optionName: type });
				break;
			case 'add_labels':
				manager = new DeskPRO.Agent.Ticket.Property.Labels(this, { mode: 'add' });
				break;
			case 'remove_labels':
				manager = new DeskPRO.Agent.Ticket.Property.Labels(this, { mode: 'remove' });
				break;
			case 'flag':
				manager = new DeskPRO.Agent.Ticket.Property.Flag(this);
				break;
			case 'new_reply':
				manager = new DeskPRO.Agent.Ticket.Property.NewReply(this);
				break;
			case 'ticket_field':
				manager = new DeskPRO.Agent.Ticket.Property.TicketField(this, { fieldId: type_id });
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

		var list = $('.file-list', this.barWrapper);
		$('input', list[0]).live('click', function() {
			var el = $(this);
			var li = el.parent();
			if (el.is(':checked')) {
				li.removeClass('unchecked');
			} else {
				li.addClass('unchecked');
			}
		});

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
				return $('<li class="uploaded"><input type="checkbox" checked="checked" name="attach[]" value="'+ file.blob_id + '" /> <a href="'+ file.download_url + '" target="_blank">' + file.filename + '</a> <span class="size">('+file.filesize_readable+')</span></li>');
			}
		});
	},

	//#################################################################
	//# Ticket options menus
	//#################################################################

	ticketOptionsMenus: {},
	ticketOptionsMenuEls: {},
	_initTicketOptionsMenus: function() {
		var options = ['department_id', 'category_id', 'product_id', 'priority_id', 'workflow_id', 'status', 'agent_id', 'agent_team_id'];
		var self = this;

		for (var i = 0; i < options.length; i++) {
			var opt = options[i];
			var menuEl = $('.menu.'+opt+':first', this.wrapper);

			var btnClass = '.menu-trigger.'+opt+':first';
			var btnEl = $(btnClass, this.wrapper);
			var menu = new DeskPRO.UI.Menu({
				triggerElement: btnEl,
				menuElement: menuEl,
				onItemClicked: function(info) {
					self._handleTicketOptionClick(info);
				}
			});
			this.ticketOptionsMenus[opt] = menu;
			this.ticketOptionsMenuEls[opt] = menuEl;
			this.destroyMenus.push(menu);

			// And if its a no-value, update the proper title
			if ($('.no-value', btnEl).length) {
				//this._initNoValOption(opt, null);
			}
		}
	},

	_handleTicketOptionClick: function(info) {
		var typeEl = $(info.itemEl);
		if (!typeEl.data('option-name')) typeEl =  typeEl.parent();
		if (!typeEl.data('option-name')) typeEl =  typeEl.parent();
		if (!typeEl.data('option-name')) typeEl =  typeEl.parent();
		if (!typeEl.data('option-name')) typeEl =  typeEl.parent();

		var opt = typeEl.data('option-name');
		var itemId = $(info.itemEl).data('option-id');
		if (!itemId) itemId = $(info.itemEl).data('option-value');

		var prop = this.getPropertyManager(opt);
		this.changeManager.setInstantChange(prop, itemId);
	},

	handleTicketChanges: function() {
		// When department is updated, we have to update display
		// options for category

		var map = DeskPRO_Window.getData('ticketDepToCatMap');

		var depProperty = this.getPropertyManager('department_id');
		var depId = depProperty.getValue();

		var catProperty = this.getPropertyManager('category_id');
		var catId = catProperty.getValue();

		var validCatIds = [];
		if (map[depId]) {
			validCatIds = map[depId];
		}

		if (!validCatIds.contains(catId)) {
			catProperty.setValue(0);
		}

		// Update the UI menu with correct
		var catMenuList = this.ticketOptionsMenuEls['category_id'];

		$('li', catMenuList).hide();
		Array.each(validCatIds, function(id) {
			$('.cat-'+id, catMenuList).show();
		});

		// We have to run rules to check custom fields now
		var ticketInfo = {
			department_id: depId,
			category_id: catId,
			product_id: this.getPropertyManager('product_id').getValue()
		};

		var rules = window.DESKPRO_CUSTOM_TICKET_DEF_RULES;
		if (!rules) {
			rules = [];
		}

		var hide = [];
		Array.each(rules, function (ruleFn) {
			var actions = ruleFn(ticketInfo);
			if (!actions) return;

			var do_stop = false;

			Array.each(actions, function (action) {
				if (action[0] == 'show') {
					hide.erase(action[1]);
				} else if (action[0] == 'hide') {
					hide.include(action[1]);
				} else if(action[0] == 'stop_rules') {
					do_stop = true;
				}
			});

			// Dont exec any more rules
			if (do_stop) {
				return true;
			}
		});

		var allFields = $('.custom-field', this.contentWrapper);

		if (hide.length) {
			var hideSel = '.custom-field-' + hide.join(', .custom-field-');
			allFields.not(hideSel).show();
			allFields.filter(hideSel).hide();
		} else {
			allFields.show();
		}
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
			var data = [];
			if (this._currentMacroId) {
				data.push({ name: 'macro_id', value: this._currentMacroId });
			}
			this.changeManager.saveChanges(data);
			this.toggleMacroApplyBtn('off');

			this._currentMacroId = null;
		}).bind(this));

		$('ul.tools li.macros-cancel', this.ticketBar).click((function() {
			this.changeManager.revertChanges();
			this.toggleMacroApplyBtn('off');

			this._currentMacroId = null;
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

	_currentMacroId: null,
	_handleMacroClick: function(info) {
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
		this.unloadTicketTab('notes');

		this.resetReply();
	},

	resetReply: function() {
		$('textarea[name="message"]', this.ticketReply).val('');
		$('.attachments-area ul.file-list', this.ticketReply).html('');

		// Make sure reply tab is selected again
		this.replySimpleTabs.activateTab($('.reply-area.tab-trigger', this.ticketReplyTabs));
	}
});