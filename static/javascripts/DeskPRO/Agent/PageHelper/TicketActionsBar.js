Orb.createNamespace('DeskPRO.Agent.PageHelper');

DeskPRO.Agent.PageHelper.TicketActionsBar = new Class({
	
	wrapper: null,
	contentWrapper: null,
	tableEl: null,
	countEl: null,
	actionTitleEl: null,
	selectedActionData: null,
	
	initialize: function(wrapper, contentWrapper) {
		this.wrapper = wrapper;
		this.contentWrapper = contentWrapper;
		
		this.actionTitleEl = $('.actions-bar .action-title', this.wrapper);
		this.countEl = $('.actions-bar .counter .count', this.wrapper);
		
		this._initMenus();
	},
	
	_initMenus: function() {
		var action_title = $('.actions-bar .action-title', this.wrapper);
		var menu = new DeskPRO.UI.Menu({
			triggerElement: $('.actions-bar .action-title', this.wrapper),
			menuElement: $('.actions-bar .action-menu', this.wrapper),
			onItemClicked: this._actionMenuItemClicked.bind(this)
		});
		
		$('.actions-bar .action-perform', this.wrapper).click((function() {
			this.performMassAction();
		}).bind(this));
		
		var menu = new DeskPRO.UI.Menu({
			triggerElement: $('.actions-bar .counter', this.wrapper),
			menuElement: $('..actions-bar .selected-menu', this.wrapper),
			onItemClicked: this._selectMenuItemClicked.bind(this)
		});
	},
	
	
	
	/**
	 * Sets up a new active table for the live click events
	 */
	setActiveTable: function(tableEl) {
		this.tableEl = tableEl;
		
		var self = this;
		$('input[type="checkbox"].ticket', this.tableEl).live('click', function() {
			self.handleTicketCheckClick($(this));
		});
	},
	
	
	
	/**
	 * Handle when a ticket checkbox is checked or unchecked.
	 */
	handleTicketCheckClick: function(checkEl) {
		var num =  parseInt(this.countEl.html());
		
		if (checkEl.is(':checked')) {
			num++;
		} else {
			num--;
		}
		
		if (num < 0) num = 0;
		
		 this.countEl.html(num);
	},
	
	
	
	/**
	 * Handle menu click on the select menu (select all/none/inverse)
	 */
	_selectMenuItemClicked: function(info) {
		
		// No table defined
		if (!this.tableEl) return;
		
		var itemEl = $(info.itemEl);
		
		if (itemEl.data('op') == 'none') {
			$('input[type="checkbox"].ticket', this.tableEl).attr('checked', false);
		} else if (itemEl.data('op') == 'all') {
			$('input[type="checkbox"].ticket', this.tableEl).attr('checked', true);
		} else if (itemEl.data('op') == 'invert') {
			$('input[type="checkbox"].ticket', this.tableEl).each(function() {
				if ($(this).is(':checked')) {
					$(this).attr('checked', false);
				} else {
					$(this).attr('checked', true);
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
});
