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
		this.contentWrapper = this.wrapper.children('.layout-content').attr('id', Orb.getUniqueId());
		this.barWrapper = this.wrapper.children('.layout-footer').attr('id', Orb.getUniqueId());

		this._initLayout();

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

	_initLayout: function() { },

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
			case 'agent_id':
			case 'agent_team_id':
				manager = new DeskPRO.Agent.Ticket.Property.StandardOption(this, { optionName: type });
				break;
			case 'status':
				manager = new DeskPRO.Agent.Ticket.Property.Status(this, { optionName: 'status'});
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
			var btnEl = $('.menu-trigger.' + opt + ':first', this.wrapper);
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
			product_id: this.getPropertyManager('product_id').getValue(),
			priority_id: this.getPropertyManager('priority_id').getValue(),
			workflow_id: this.getPropertyManager('workflow_id').getValue()
		};

		var display_elements = [];
		if (window.DESKPRO_TICKET_DISPLAY && window.DESKPRO_TICKET_DISPLAY[depId]) {
			display_elements = window.DESKPRO_TICKET_DISPLAY[depId];
		}

		var show = [];
		Array.each(display_elements, function(info) {

			var pass = info.check(ticketInfo);
			var state = info.initial_state;
			if (pass) {
				if (state == 'hidden') state = 'visible';
				else state = 'hidden';
			}

			if (state == 'visible') {
				show.push('.' + info.element_type + '-' + info.element_id);
			}
		});

		var displayElements = $('.display-element', this.contentWrapper).hide();
		displayElements.filter(show.join(', ')).show();
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
		this.ticketBar = $('div.tab-bottom:first', this.barWrapper);
		this.ticketReply = $('div.tab-bottom-open:first', this.barWrapper);


		this.ticketReplyTabs = $('.tab-bottom-tabs', this.barWrapper);

		var self = this;
		$('input.placeholder', this.ticketBar).click(function() {
			self.toggleReplyBar();
		});

		$('a.close-trigger', this.ticketReplyTabs).click(function() {
			self.toggleReplyBar();
		});

		// Send reply
		$('button.submit-reply-trigger', this.barWrapper).click(function(ev) {
			ev.preventDefault(); // its wrapped in a form tag, we dont want to submit the page tho
			self._sendReply();
		});

		// Init ticket reply tabs
		var simpleTabs = this.replySimpleTabs = new DeskPRO.UI.SimpleTabs({
			context: this.ticketReply,
			triggerElements: $('li.tab-trigger', this.ticketReplyTabs)
		});

		// Actions menu
		this.ticketActionsMenu = new DeskPRO.UI.Menu({
			triggerElement: $('.bar-actions li.actions', this.ticketBar),
			menuElement: $('ul.ticket-info-edit-menu:first', this.contentWrapper),
			onItemClicked: this._handleActionsMenuClick.bind(this)
		});
		this.destroyMenus.push(this.ticketActionsMenu);

		// Macros menu
		this.ticketMacrosMenu = new DeskPRO.UI.Menu({
			triggerElement: $('.bar-actions li.macros', this.ticketBar),
			menuElement: $('ul.ticket-macros-menu:first', this.contentWrapper),
			onItemClicked: this._handleMacroClick.bind(this)
		});
		this.destroyMenus.push(this.ticketMacrosMenu);

		// Macro apply/cancel
		$('.bar-actions li.macros-apply', this.ticketBar).click((function() {
			var data = [];
			if (this._currentMacroId) {
				data.push({ name: 'macro_id', value: this._currentMacroId });
			}
			this.changeManager.saveChanges(data);
			this.toggleMacroApplyBtn('off');

			this._currentMacroId = null;
		}).bind(this));

		$('.bar-actions li.macros-cancel', this.ticketBar).click((function() {
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

	toggleReplyBar: function(force) {

		if (!force) {
			if (this.ticketReply.is(':visible')) {
				force = 'off';
			} else {
				force = 'on';
			}
		}

		if (force == 'on') {

			this.ticketReply.show();
			this.barWrapper.addClass('expanded');
			this.layout.expandFooter();

			var msg = $('.tab-content.reply-reply', this.ticketReply);

			$('input.placeholder', this.ticketBar).hide();
			$('li.submit-reply.trigger:first', this.barWrapper).show();

			// When we open we should scroll down by the new height,
			// so the same position is visible in the center pane
			//var h = this.barWrapper.outerHeight() + this.ticketReplyTabs.outerHeight() - 26; /* -26 for original size */
			//this.contentWrapper.scrollTop(this.contentWrapper.scrollTop() + h);

			// Focus textarea
			$('textarea', this.ticketReply).focus();
		} else {
			this.layout.collapseFooter();
			this.ticketReply.hide();
			this.barWrapper.removeClass('expanded');

			$('input.placeholder', this.ticketBar).show();
			$('li.submit-reply.trigger:first', this.barWrapper).hide();
		}
	},

	isSendingReply: false,
	_sendReply: function() {
		this._handleSendReply($(':input, textarea, select', $('.reply-form-fields',this.ticketReply)));
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