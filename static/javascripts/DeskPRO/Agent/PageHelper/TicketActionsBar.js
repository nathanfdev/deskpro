Orb.createNamespace('DeskPRO.Agent.PageHelper');

DeskPRO.Agent.PageHelper.TicketActionsBar = new Class({

	page: null,
	wrapper: null,
	contentWrapper: null,
	tableEl: null,
	selectedActionData: null,
	ticketBar: null,

	initialize: function(page, wrapper, contentWrapper) {
		this.page = page;
		this.wrapper = wrapper;
		this.contentWrapper = contentWrapper;

		this.ticketBar = $('.ticket-bar:first', this.wrapper);

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
			onItemClicked: this._actionMenuItemClicked.bind(this)
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

		var menu = this.selectMenu = new DeskPRO.UI.Menu({
			triggerElement: $('.ticket-bar .counter:first', this.wrapper),
			menuElement: $('.ticket-bar .selected-menu:first', this.wrapper),
			onItemClicked: this._selectMenuItemClicked.bind(this)
		});
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

		$(this.tableEl).delegate('tr', 'hover', function() {
			self._handleTrHover($(this));
		});
	},

	_handleTrHover: function(tr) {
		this._getRowLines(tr).toggleClass('hover');
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



	/**
	 * Handle menu click on the select menu (select all/none/inverse)
	 */
	_selectMenuItemClicked: function(info) {
		this._selectOp($(info.itemEl).data('op'));
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
					changeManager.commitChanges();
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

		Array.each(ticket_ids, function(id) {
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
		console.debug(data);
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

		var changeManager = this.page.changeManager;
		changeManager.begin(ticket_ids);

		Object.each(macro_info, function(actions, ticket_id) {
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
			this.page.changeManager.commitChanges();
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
			}
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
			this.layout.sizePane('south', 150 + this.ticketBar.outerHeight());

			$('div.placeholder', this.ticketBar).hide();
			$('div.reply-buttons', this.ticketBar).show();

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
			this.ticketReply.hide();
			this.barWrapper.removeClass('expanded');
			this.layout.sizePane('south', 27);

			$('div.placeholder', this.ticketBar).show();
			$('div.reply-buttons', this.ticketBar).hide();

			this.ticketReplyTabs.hide();
		}
	}
});
