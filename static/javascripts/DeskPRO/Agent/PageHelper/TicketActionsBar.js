Orb.createNamespace('DeskPRO.Agent.PageHelper');

// TODO this needs to be heavily refactored, so search listing and ticket view
// can both use the same basic principals
DeskPRO.Agent.PageHelper.TicketActionsBar = new Class({

	page: null,
	wrapper: null,
	contentWrapper: null,
	tableEl: null,
	selectedActionData: null,
	ticketBar: null,
	barWrapper: null,

	initialize: function(page) {
		this.page = page;
		this.wrapper = this.page.wrapper;
		this.contentWrapper = this.page.contentWrapper;

		this.ticketBar = $('.ticket-bar:first', this.wrapper);
		this.barWrapper = this.ticketBar;

		this._initReplyBar();
		this._initMenus();

		// Some style stuff on certain change manager states
		this.page.changeManager.addEvent('changesCleared', (function () {
			$('table:first', this.page.contentWrapper).removeClass('preview-mode');
			$('tr', this.page.contentWrapper).removeClass('with-line-3').removeClass('faded');
			$('tr.line-3', this.page.contentWrapper).hide().find('td > ul').html('')

			this.page.actionsBarHelper._selectOp('none');

			this.toggleMacroApplyBtn('off');
		}).bind(this));
	},

	actionMenu: null,
	selectMenu: null,
	_initMenus: function() {
		var menu = this.actionMenu = new DeskPRO.UI.Menu({
			triggerElement: $('.ticket-bar ul.tools li.actions:first', this.wrapper),
			menuElement: $('.ticket-bar .ticket-action-menu:first', this.wrapper),
			onItemClicked: this._actionMenuItemClicked.bind(this),
			initMenuNow: true
		});

		var menu = this.macrosMenu = new DeskPRO.UI.Menu({
			triggerElement: $('.ticket-bar ul.tools li.macros:first', this.wrapper),
			menuElement: $('.ticket-bar .ticket-macros-menu:first', this.wrapper),
			onItemClicked: (function (info) {
				var macroId = $(info.itemEl).data('macro-id');
				this._loadMacro(macroId, this.getSelectedTicketIds());
			}).bind(this)
		});

		// Macro apply/cancel
		$('ul.tools li.macros-apply', this.ticketBar).click(this._applyButtonClicked.bind(this));

		$('ul.tools li.macros-cancel', this.ticketBar).click((function() {
			this.page.changeManager.revertChanges();
			this.toggleMacroApplyBtn('off');
		}).bind(this));
	},



	/**
	 * Get all the ticket ID's currently selected
	 *
	 * @return {Array}
	 */
	getSelectedTicketIds: function() {
		var ticket_ids = [];

		$('input.ticket:checked', this.tableEl).each(function() {
			ticket_ids.push(parseInt($(this).val()));
		});

		return ticket_ids;
	},



	/**
	 * Sets up a new active table for the live click events
	 */
	setActiveTable: function(tableEl) {
		this.tableEl = $(tableEl);

		var self = this;

		// The header check-all-box should check whole table
		$('thead > tr > th.check-all > input.check-all-box', this.tableEl).click(function(ev) {
			ev.stopPropagation();
			if ($(this).is(':checked')) {
				self._selectOp('all');
			} else {
				self._selectOp('none');
			}
		});

		this.tableEl.delegate('input[type="checkbox"].ticket', 'click', function() {
			self.handleTicketCheckClick($(this));
		});
	},

	_getRowLines: function(tr) {
		if (tr.is('.line-1')) {
			var trs = tr.add(tr.next()).add(tr.next());
		} else if (tr.is('.line-2')) {
			var trs = tr.add(tr.prev()).add(tr.next());
		} else {
			var trs = tr.add(tr.prev()).add(tr.prev().prev());
		}

		return trs;
	},



	/**
	 * Handle when a ticket checkbox is checked or unchecked.
	 */
	handleTicketCheckClick: function(checkEl) {
		var countEl = $('.count', this.ticketBar);
		var num =  parseInt(countEl.html());
		if (!num) {
			num = 0;
		}

		if (checkEl.is(':checked')) {
			this._getRowLines(checkEl.parent().parent()).addClass('on');
			num++;

			if (this.page.changeManager.hasChanges) {
				this._addTicketIdsToCurrentAction(checkEl.val());
			}
		} else {
			this._getRowLines(checkEl.parent().parent()).removeClass('on');
			num--;

			if (this.page.changeManager.hasChanges) {
				this._removeTicketIdsToCurrentAction(checkEl.val());
			}
		}

		if (num < 0) num = 0;

		countEl.html(num);
	},



	_selectOp: function(op) {
		// No table defined
		if (!this.tableEl) return;
		var self = this;

		if (op == 'none') {
			$('input[type="checkbox"].ticket', this.tableEl).attr('checked', false);
			$('tr.on', this.tableEl).removeClass('on');
		} else if (op == 'all') {
			$('input[type="checkbox"].ticket', this.tableEl).attr('checked', true);
			$('tr', this.tableEl).addClass('on');
		} else if (op == 'invert') {
			$('input[type="checkbox"].ticket', this.tableEl).each(function() {
				if ($(this).is(':checked')) {
					$(this).attr('checked', false);
					self._getRowLines($(this).parent().parent()).removeClass('on');
				} else {
					$(this).attr('checked', true);
					self._getRowLines($(this).parent().parent()).addClass('on');
				}
			});
		}

		// Update count
		$('.count', this.ticketBar).html($('input[type="checkbox"].ticket:checked', this.tableEl).length);
	},


	_currentActionInfo: null,
	/**
	 * When the action is selected from the menu, update the title
	 * in the UI and set the action data.
	 */
	_actionMenuItemClicked: function(info) {
		var itemEl = $(info.itemEl);

		var op = itemEl.data('option-id');

		var ticket_ids = this.getSelectedTicketIds();

		if (!ticket_ids.length) {
			return;
		}

		switch (op) {
			case 'delete':
				this._currentActionInfo = {'op': 'delete' };
				this.toggleMacroApplyBtn('on', 'Delete Tickets');
				this._applyButtonCallback = (function() {

					this.toggleMacroApplyBtn('off');

					$.ajax({
						url: this.page.getMetaData('deleteTicketUrl'),
						type: 'GET',
						data: data,
						dataType: 'json',
						success: function(data) {
							DeskPRO_Window.getMessageBroker().sendMessage('tickets.deleted', data.deleted_tickets);
						}
					});
				}).bind(this);
				break;

			case 'open':
				this._currentActionInfo = {'op': 'open' };
				this.toggleMacroApplyBtn('on', 'Open');
				this._applyButtonCallback = (function() {

					this.toggleMacroApplyBtn('off');

					Array.each(this.getSelectedTicketIds(), function(ticket_id) {
						DeskPRO_Window.runPageRoute('page:' + this.page.getMetaData('viewTicketUrl').replace('$ticket_id', ticket_id));
					}, this);

					this._selectOp('none');
				}).bind(this);
				break;

			case 'standard':

				var optionName = itemEl.data('option-name');
				var value = itemEl.data('option-value');

				this._currentActionInfo = {'op': 'standard', 'name': optionName, 'value': value };
				var props = this._addTicketIdsToCurrentAction(ticket_ids);

				// just so we can get a caption for the button on the next line
				var property = props[0];

				this.toggleMacroApplyBtn('on', 'Set ' + property.displayCaption + ': ' + DeskPRO_Window.getDisplayName(property.displayNameType, value));

				var data = [];
				data.push({
					name: 'actions['+optionName+']',
					value: value
				});

				this._applyButtonCallback = (function() {
					this.toggleMacroApplyBtn('off');
					this.performMassAction(data);
					this.page.changeManager.commitChanges();
				}).bind(this);

				break;
		}
	},

	_addTicketIdsToCurrentAction: function(ticket_ids) {
		if (typeOf(ticket_ids) != 'array') {
			ticket_ids = [ticket_ids];
		}

		if (!this._currentActionInfo) {
			return;
		}

		// Macro mode, we need to send new IDs through ajax to get actions
		if (this._currentActionInfo.op == 'macro') {
			this._loadMacro(this._currentActionInfo.macroId, ticket_ids);

		// Action mode, we can apply imediatley
		} else if (this._currentActionInfo.op == 'standard') {

			var changeManager = this.page.changeManager;
			changeManager.begin(ticket_ids);

			var properties = [];
			Array.each(ticket_ids, function(ticket_id) {
				var property = this.createPropertyForTicket(this._currentActionInfo.name, ticket_id);
				changeManager.addChange(property, this._currentActionInfo.value);

				properties.push(property);
			}, this);

			changeManager.applyChanges();

			return properties;
		}
	},

	_removeTicketIdsToCurrentAction: function(ticket_ids) {
		if (typeOf(ticket_ids) != 'array') {
			ticket_ids = [ticket_ids];
		}

		Array.each(ticket_ids, function(ticket_id) {
			this.page.changeManager.revertChangesForTicketId(ticket_id);
		}, this);
	},

	/**
	 * Send the request to the server to perform the actions on the selected
	 * tickets.
	 */
	performMassAction: function(data) {

		Array.each(this.getSelectedTicketIds(), function(id) {
			data.push({
				name: 'ticket_ids[]',
				value: id
			});
		});

		DeskPRO_Window.startLoadingIndicator();
		$.ajax({
			cache: false,
			type: 'POST',
			data: data,
			url: BASE_URL + 'agent/ticket-search/ajax-mass-actions',
			context: this,
			dataType: 'json',
			success: function (data) {
				this._handleMassActionsReply(data);
			}
		});
	},

	_handleMassActionsReply: function(data) {
		DeskPRO_Window.stopLoadingIndicator();
		DeskPRO_Window.showStatusMessage('Ticket changes were applied successfully');
	},


	//#################################################################
	//# Mass actions preview stuff
	//#################################################################

	_selectedMacroId: null,
	_applyButtonCallback: null,

	createPropertyForTicket: function(propName, ticket_id) {
		var objinfo = this._getPropClass(propName);

		if (!objinfo) {
			return false;
		}

		var property = new objinfo[0](this.page, ticket_id, objinfo[1]);

		return property;
	},

	_applyButtonClicked: function() {
		if (this._applyButtonCallback) {
			this._applyButtonCallback();
		}
	},

	_loadMacro: function(macroId, ticket_ids) {
		this._currentActionInfo = {'op': 'macro', 'macroId': macroId };

		if (!ticket_ids.length) {
			DeskPRO_Window.showAlert('You need to select one or more tickets to perform actions on.');
			return;
		}

		DeskPRO_Window.startLoadingIndicator();

		var data = [];
		Array.each(ticket_ids, function(id) {
			data.push({
				name: 'ticket_ids[]',
				value: id
			});
		});

		$.ajax({
			cache: false,
			type: 'GET',
			data: data,
			url: this.page.getMetaData('getMacroUrl').replace('$macro_id', this._currentActionInfo.macroId),
			context: this,
			dataType: 'json',
			success: function (data) {
				DeskPRO_Window.stopLoadingIndicator();
				this.applyMacroActions(data, ticket_ids);
			}
		});
	},

	applyMacroActions: function(macro_info, ticket_ids) {

		// Reset reply area if this is the first
		if (!this.page.changeManager.hasChanges) {
			if (macro_info.raw_actions.new_reply) {
				$('form.reply-form textarea', this.ticketReply).val(macro_info.raw_actions.new_reply);
			}
		}

		var changeManager = this.page.changeManager;
		changeManager.begin(ticket_ids);

		Object.each(macro_info.ticket_actions, function(actions, ticket_id) {
			Object.each(actions, function(newValue, propName) {
				var property = this.createPropertyForTicket(propName, ticket_id);

				if (!property) {
					return;
				}

				changeManager.addChange(property, newValue);

			}, this);
		}, this);

		changeManager.applyChanges();

		this.toggleMacroApplyBtn('on');

		this._applyButtonCallback = (function() {
			this.saveMacro();
			this.toggleMacroApplyBtn('off');
		}).bind(this);
	},

	_getPropClass: function(propName) {
		var obj = null;
		var opt = null;
		switch (propName) {
			case 'department_id':
		 	case 'category_id':
			case 'product_id':
			case 'priority_id':
			case 'status':
			case 'agent_id':
			case 'agent_team_id':
				obj = DeskPRO.Agent.TicketList.Property.StandardOption;
				opt = {'optionName': propName };
				break;
			case 'new_reply':
				obj = DeskPRO.Agent.TicketList.Property.NewReply;
				break;
		}

		return [obj, opt];
	},

	toggleMacroApplyBtn: function(force, title) {

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
			if (!title) title = 'Apply';
			$('li.macros-apply span', ul).html(title + ' (<span class="count">'+this.getSelectedTicketIds().length+'</span>)');
			applyBtns.show();
		} else {
			otherBtns.show();
			applyBtns.hide();
		}
	},

	saveMacro: function() {
		var ticket_ids = this.getSelectedTicketIds();

		var data = [];
		Array.each(ticket_ids, function(id) {
			data.push({
				name: 'ticket_ids[]',
				value: id
			});
		});

		var reply = $('form.reply-form textarea', this.ticketReply).val().trim();
		if (reply.length) {
			data.push({
				'name': 'new_reply',
				'value': reply
			});
		}

		this.page.changeManager.commitChanges();
		DeskPRO_Window.startLoadingIndicator();

		$.ajax({
			cache: false,
			type: 'POST',
			data: data,
			url: this.page.getMetaData('saveMacroUrl').replace('$macro_id', this._currentActionInfo.macroId),
			context: this,
			dataType: 'json',
			success: function () {
				DeskPRO_Window.stopLoadingIndicator();
				DeskPRO_Window.showStatusMessage('Macro was applied successfully');
			}
		});
	},

	//#################################################################
	//# To do with reply area
	//#################################################################

	_initReplyBar: function() {
		this.ticketBar = this.barWrapper.children('div.bar');
		this.ticketReply = this.barWrapper.children('div.reply');

		this.ticketReplyTabs = $('.ticket-reply-tabs', this.barWrapper).detach().appendTo('body');
		//this.page.destroyEls.push(this.ticketReplyTabs);

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
		this.page.addEvent('activate', function() { if (self.ticketReply.is(':visible')) self.ticketReplyTabs.show(); });
		this.page.addEvent('deactivate', function() { self.ticketReplyTabs.hide(); });

		// Send reply
		$('button.submit-trigger', this.barWrapper).click(function(ev) {
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

		$('button.submit-trigger', this.ticketBar).click(this._sendReply.bind(this));

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
			this.page.layout.sizePane('south', 110 + this.ticketBar.outerHeight());

			$('div.placeholder', this.ticketBar).hide();
			$('div.reply-buttons', this.ticketBar).show();
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
			this.page.layout.sizePane('south', 27);

			$('div.placeholder', this.ticketBar).show();
			$('div.reply-buttons', this.ticketBar).hide();
			$('li.submit-reply.trigger:first', this.barWrapper).hide();

			this.ticketReplyTabs.hide();
		}
	},

	isSendingReply: false,
	_sendReply: function() {
		this._handleSendReply($('form.reply-form', this.ticketReply));
	},

	_handleSendReply: function(els) {
		$('button.submit-trigger', this.ticketReply).addClass('gray');

		var data = els.serializeArray();

		var ticket_ids = this.getSelectedTicketIds();

		Array.each(ticket_ids, function(id) {
			data.push({
				name: 'ticket_ids[]',
				value: id
			});
		});

		$.ajax({
			url: this.page.getMetaData('massReplyUrl'),
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

		DeskPRO_Window.showStatusMessage('Replies were sent successfully');
		this.toggleReplyBar('off');
		$('textarea[name="message"]', this.ticketReply).val('');
	}
});
