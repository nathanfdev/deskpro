Orb.createNamespace('DeskPRO.Agent.PageHelper');

DeskPRO.Agent.PageHelper.TicketActionsBar = new Class({
	
	page: null,
	wrapper: null,
	contentWrapper: null,
	tableEl: null,
	countEl: null,
	selectedActionData: null,
	ticketBar: null,
	
	initialize: function(page, wrapper, contentWrapper) {
		this.page = page;
		this.wrapper = wrapper;
		this.contentWrapper = contentWrapper;
		
		this.ticketBar = $('.ticket-bar:first', this.wrapper);
		this.countEl = $('.ticket-bar .counter .count', this.wrapper);
		
		this._initMenus();
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
			onItemClicked: this._macroMenuItemClicked.bind(this)
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
		var num =  parseInt(this.countEl.html());
		
		if (checkEl.is(':checked')) {
			this._getRowLines(checkEl.parent().parent()).addClass('on');
			num++;
		} else {
			this._getRowLines(checkEl.parent().parent()).removeClass('on');
			num--;
		}
		
		if (num < 0) num = 0;
		
		 this.countEl.html(num);
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
		this.countEl.html($('input[type="checkbox"].ticket:checked', this.tableEl).length);
	},
	
	
	
	/**
	 * When the action is selected from the menu, update the title
	 * in the UI and set the action data.
	 */
	_actionMenuItemClicked: function(info) {
		var itemEl = $(info.itemEl);
		
		var op = itemEl.data('option-id');
		
		var ticket_ids = this.getSelectedTicketIds();
		this._selectedMacroTickets = ticket_ids;

		var data = [];
		Array.each(ticket_ids, function(id) {
			data.push({
				name: 'ticket_ids[]',
				value: id
			});
		});
		
		switch (op) {
			case 'delete':
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
				
				var changeManager = this.page.changeManager;
				changeManager.begin(ticket_ids);
				
				var displayName = '';
				var displayNameType = '';
				Array.each(ticket_ids, function(ticket_id) {
					var property = this.createPropertyForTicket(optionName, ticket_id);
					displayName = property.displayCaption;
					displayNameType = property.displayNameType;
					changeManager.addChange(property, value);
				}, this);
				
				this.toggleMacroApplyBtn('on', 'Set ' + displayName + ': ' + DeskPRO_Window.getDisplayName(displayNameType, value));
				
				changeManager.applyChanges();
				
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
	
	
	
	/**
	 * Send the request to the server to perform the actions on the selected
	 * tickets.
	 */
	performMassAction: function(data) {	
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
	_selectedMacroTickets: null,
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
	
	_macroMenuItemClicked: function(info) {
		this._selectedMacroId = $(info.itemEl).data('macro-id');

		var ticket_ids = this.getSelectedTicketIds();
		this._selectedMacroTickets = ticket_ids;
		
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
			url: this.page.getMetaData('getMacroUrl').replace('$macro_id', this._selectedMacroId),
			context: this,
			dataType: 'json',
			success: function (data) {
				DeskPRO_Window.stopLoadingIndicator();
				this.applyMacroActions(data);
			}
		});
	},
	
	applyMacroActions: function(macro_info) {
		
		var ticket_ids = this._selectedMacroTickets;
		
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
			return null;
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
			$('li.macros-apply span', ul).text(title + ' ('+this._selectedMacroTickets.length+')');
			applyBtns.show();
		} else {
			otherBtns.show();
			applyBtns.hide();
		}
	},
	
	saveMacro: function() {
		var ticket_ids = this._selectedMacroTickets;
		
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
			url: this.page.getMetaData('saveMacroUrl').replace('$macro_id', this._selectedMacroId),
			context: this,
			dataType: 'json',
			success: function () {
				DeskPRO_Window.stopLoadingIndicator();
			}
		});
	}
});
