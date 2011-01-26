Orb.createNamespace('DeskPRO.Agent.PageHelper');

DeskPRO.Agent.PageHelper.TicketActionsBar = new Class({
	
	page: null,
	wrapper: null,
	contentWrapper: null,
	tableEl: null,
	countEl: null,
	actionTitleEl: null,
	selectedActionData: null,
	
	initialize: function(page, wrapper, contentWrapper) {
		this.page = page;
		this.wrapper = wrapper;
		this.contentWrapper = contentWrapper;
		
		this.actionTitleEl = $('.ticket-bar .action-title', this.wrapper);
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
			var trs = tr.add(tr.next());
		} else {
			var trs = tr.add(tr.prev());
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
		
		this.selectedActionData = {
			op: itemEl.data('op')
		};
		
		if (itemEl.data('op') == 'macro') {
			this.selectedActionData['macro_id'] = itemEl.data('macro-id');
		} else if (itemEl.data('op') == 'status') {
			this.selectedActionData['status'] = itemEl.data('status');
		}
		
		this.actionTitleEl.html(itemEl.html());
	},
	
	
	
	/**
	 * Send the request to the server to perform the actions on the selected
	 * tickets.
	 */
	performMassAction: function() {
		if (this.selectedActionData == null) {
			return;
		}
		
		var data = [];
		
		$('input[type="checkbox"].ticket:checked', this.contentWrapper).each(function() {
			data.push({
				name: 'ticket_ids[]',
				value: $(this).val()
			});
		});
		
		Object.each(this.selectedActionData, function(v,k) {
			data.push({
				name: k,
				value: v
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
	
	_macroMenuItemClicked: function(info) {
		var ticket_ids = this.getSelectedTicketIds();
		
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
			url: this.page.getMetaData('getMacroUrl').replace('$macro_id', $(info.itemEl).data('macro-id')),
			context: this,
			dataType: 'json',
			success: function (data) {
				DeskPRO_Window.stopLoadingIndicator();
				this.applyMacroActions(ticket_ids, data);
			}
		});
	},
	
	applyMacroActions: function(ticket_ids, macro_info) {
		
		var changeManager = this.page.changeManager;
		changeManager.begin(ticket_ids);
		
		Object.each(macro_info.all, function(propName, newValue) {
			var obj = this._getPropClass(propName);
			console.log(propName);
			console.log(obj);
			
			Array.each(ticket_ids, function(id) {
				var property = new obj(this.page, id);
				
				changeManager.addChange(property, newValue);
			}, this);
		}, this);
		
		changeManager.applyChanges();
	},
	
	_getPropClass: function(propName) {
		var obj = null;
		switch (propName) {
			case 'department_id':
		 	case 'category_id':
			case 'product_id':
			case 'priority_id':
			case 'status':
			case 'agent_id':
			case 'agent_team_id':
				obj = DeskPRO.Agent.Ticket.Property.StandardOption;
				break;
			case 'new_reply':
				obj = DeskPRO.Agent.Ticket.Property.NewReply;
				break;
		}
		
		return obj;
	}
});
