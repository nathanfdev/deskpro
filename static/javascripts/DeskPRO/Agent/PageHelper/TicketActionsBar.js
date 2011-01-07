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
	
	actionMenu: null,
	selectMenu: null,
	_initMenus: function() {
		var action_title = $('.actions-bar .action-title', this.wrapper);
		var menu = this.actionMenu = new DeskPRO.UI.Menu({
			triggerElement: $('.actions-bar .action-title', this.wrapper),
			menuElement: $('.actions-bar .action-menu', this.wrapper),
			onItemClicked: this._actionMenuItemClicked.bind(this)
		});
		
		$('.actions-bar .action-perform', this.wrapper).click((function() {
			this.performMassAction();
		}).bind(this));
		
		var menu = this.selectMenu = new DeskPRO.UI.Menu({
			triggerElement: $('.actions-bar .counter', this.wrapper),
			menuElement: $('..actions-bar .selected-menu', this.wrapper),
			onItemClicked: this._selectMenuItemClicked.bind(this)
		});
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
					this._getRowLines($(this).parent().parent()).removeClass('on');
				} else {
					$(this).attr('checked', true);
					this._getRowLines($(this).parent().parent()).addClass('on');
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
	}
});
