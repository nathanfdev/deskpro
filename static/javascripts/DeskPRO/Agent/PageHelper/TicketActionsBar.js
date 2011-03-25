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

		this.layout = page.layout;

		this.page = page;
		this.wrapper = this.page.wrapper;
		this.contentWrapper = this.page.contentWrapper;
		this.ticketBar = this.page.barWrapper;

		this.initOverlay();

		// Some style stuff on certain change manager states
		this.page.changeManager.addEvent('changesCleared', (function () {
			$('table:first', this.page.contentWrapper).removeClass('preview-mode');
			$('tr', this.page.contentWrapper).removeClass('with-line-3').removeClass('faded');
			$('tr.line-3', this.page.contentWrapper).hide().find('td > ul').html('')

			this.page.actionsBarHelper._selectOp('none');

			this.toggleMacroApplyBtn('off');
		}).bind(this));

		var self = this;
		$('li.macros-apply').click(function() {
			self.saveActions();
		});

		$('li.macros-cancel').click(function() {
			self._removeTicketIdsToCurrentAction(self.getSelectedTicketIds());
			self.toggleMacroApplyBtn('off');
		});
	},

	initOverlay: function() {

		var self = this;

		this.actionsWrap = $('.mass-actions:first', this.ticketBar);

		$('div.overlay-content:first', this.actionsWrap).css({
			'width': $('#pane_list_content').width() + 100,
			'max-height': $('#pane_list_content').height()
		});

		this.actionsOverlay = new DeskPRO.UI.Overlay({
			contentElement: this.actionsWrap,
			triggerElement: $('li.actions', this.ticketBar),
			onBeforeOverlayOpened: function() {
				var countEl = $('.check-count span', self.ticketBar);
				$('.check-count-overlay', self.actionsWrap).html(countEl.html());
			}
		});

		$('select.apply-macro-select', this.actionsWrap).change(function() {
			self.loadMacroActions();
		});

		$('.save-trigger', this.actionsWrap).click((function() {
			this._loadActions(this.getSelectedTicketIds());
		}).bind(this));

		// Set default checked values based on table
		this.actionsEditor = new DeskPRO.Form.RuleBuilder($('.actions-tpl', this.actionsWrap));
		this.actionsEditor.addEvent('newRow', function(new_row) {
			$('.remove', new_row).click(function() {
				new_row.remove();
			});
		});

		// Init ticket reply tabs
		var simpleTabs = this.replySimpleTabs = new DeskPRO.UI.SimpleTabs({
			context: $('.ticket-reply', this.actionsWrap),
			triggerElements: $('li.tab-trigger', $('.ticket-reply', this.actionsWrap))
		});

		var to_el = $('.actions-form .actions-terms', this.actionsWrap);

		$('.actions-form .add-term', this.actionsWrap).data('add-count', 0).click(function() {
			var count = parseInt($(this).data('add-count'));
			var basename = 'actions['+count+']';

			$(this).data('add-count', count+1);

			self.actionsEditor.addNewRow(to_el, basename);
		});
	},

	loadMacroActions: function() {

		var macro_id = parseInt($('select.apply-macro-select').val());
		if (!macro_id) {
			return;
		}

		var spinnerContainer = $('.macro-selector .spinner', this.actionsWrap).show().empty();
		var spinner = new Spinner(spinnerContainer, {
			radii: [4,8],
			padding: 0
		}).play();

		$.ajax({
			cache: false,
			type: 'POST',
			data: {'macro_id': macro_id},
			url: BASE_URL + 'agent/ticket-search/ajax-get-macro-actions',
			context: this,
			dataType: 'json',
			success: function (data) {
				console.log(data);

				var reply_text = false;

				Object.each(data.macro_actions, function(info, type) {

					var countel = $('.actions-form .add-term', this.actionsWrap);
					var count = parseInt(countel.data('add-count'));
					var basename = 'actions['+count+']';

					countel.data('add-count', count+1);

					if (type == 'reply') {
						reply_text = info;
					} else {

						var id = Orb.uuid();
						var op = info[0];
						var choice = info[1];
						this.actionsEditor.addNewRow($('.actions-terms', this.actionsWrap), basename, {
							rule_type: type,
							choice: info
						});
					}
				}, this);

				if (reply_text) {
					$('textarea', this.actionsWrap).val(reply_text);
				}
			},
			complete: function() {
				spinner.remove();
				spinnerContainer.empty();
			}
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
		$('> thead:first > tr > th.check-all > input.check-all-box:first', this.tableEl).click(function(ev) {
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
		var countEl = $('.check-count span', this.ticketBar);
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

		this.updateCount(num);
	},

	_selectOp: function(op) {
		// No table defined
		if (!this.tableEl) return;
		var self = this;

		if (op == 'none') {
			$('input[type="checkbox"].ticket', this.tableEl).attr('checked', false);
			$('tr.on', this.tableEl).removeClass('on');
		} else if (op == 'all') {
			$('tr:not(.locked) input[type="checkbox"].ticket', this.tableEl).attr('checked', true);
			$('tr', this.tableEl).addClass('on');
		} else if (op == 'invert') {
			$('input[type="checkbox"].ticket', this.tableEl).each(function() {
				if ($(this).is(':checked') && $(this).parent().parent().is(':not(.locked)')) {
					$(this).attr('checked', false);
					self._getRowLines($(this).parent().parent()).removeClass('on');
				} else {
					$(this).attr('checked', true);
					self._getRowLines($(this).parent().parent()).addClass('on');
				}
			});
		}

		// Update count
		this.updateCount($('input[type="checkbox"].ticket:checked', this.tableEl).length);
	},

	updateCount: function(num) {
		num = parseInt(num);

		if (num == 0) {
			this.layout.collapseFooter();
			$('.check-count span', this.ticketBar).html(0);
		} else {
			this.layout.expandFooter();
			$('.check-count span', this.ticketBar).html(num);
		}
	},

	_addTicketIdsToCurrentAction: function(ticket_ids) {
		if (!$.isArray(ticket_ids)) {
			ticket_ids = [ticket_ids];
		}

		this._loadActions(ticket_ids);
	},

	_removeTicketIdsToCurrentAction: function(ticket_ids) {
		if (!$.isArray(ticket_ids)) {
			ticket_ids = [ticket_ids];
		}

		Array.each(ticket_ids, function(ticket_id) {
			this.page.changeManager.revertChangesForTicketId(ticket_id);
		}, this);
	},

	//#################################################################
	//# Mass actions preview stuff
	//#################################################################

	_applyButtonCallback: null,

	createPropertyForTicket: function(propName, ticket_id) {

		var propId = null;
		var m = /^(.*?)\[(.*?)\]$/.exec(propName);
		if (m !== null) {
			propName = m[1];
			propId = m[2];
		}

		var objinfo = this._getPropClass(propName, propId);

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

	_loadActions: function(ticket_ids) {
		
		DeskPRO_Window.startLoadingIndicator();

		var loadingOff = $('.loading-off').hide();
		var loadingOn = $('.loading-on').show().empty();
		var spinner = new Spinner(loadingOn, {
			radii: [4,8],
			padding: 0
		}).play();

		var data = $(':input, select, textarea', $('.actions-terms', this.actionsWrap)).serializeArray();
		data.combine($(':input, select, textarea', $('.ticket-reply', this.actionsWrap)).serializeArray());

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
			url: BASE_URL + 'agent/ticket-search/ajax-preview-actions',
			context: this,
			dataType: 'json',
			success: function (data) {
				DeskPRO_Window.stopLoadingIndicator();
				
				this.actionsOverlay.closeOverlay();
				spinner.remove();
				loadingOn.empty().hide();
				loadingOff.show();

				this.applyActions(data, ticket_ids);

			}
		});
	},

	applyActions: function(macro_info, ticket_ids) {

		var changeManager = this.page.changeManager;
		changeManager.begin(ticket_ids);

		Object.each(macro_info.ticket_actions, function(actions, ticket_id) {
			Object.each(actions, function(newValue, propName) {
				var property = this.createPropertyForTicket(propName, ticket_id);

				if (!property) {
					return;
				}

				if (newValue.value_display) {
					newValue = newValue.value_display;//custom fields
				}

				changeManager.addChange(property, newValue);

			}, this);
		}, this);

		changeManager.applyChanges();

		this.toggleMacroApplyBtn('on');

		this._applyButtonCallback = (function() {
			this.saveActions();
			this.toggleMacroApplyBtn('off');
		}).bind(this);
	},

	_getPropClass: function(propName, propId) {
		var obj = null;
		var opt = null;
		switch (propName) {
			case 'department_id':
		 	case 'category_id':
			case 'product_id':
			case 'priority_id':
			case 'workflow_id':
			case 'status':
			case 'agent_id':
			case 'agent_team_id':
				obj = DeskPRO.Agent.TicketList.Property.StandardOption;
				opt = {'optionName': propName };
				break;
			case 'new_reply':
				obj = DeskPRO.Agent.TicketList.Property.NewReply;
				break;
			case 'add_labels':
				obj = DeskPRO.Agent.TicketList.Property.Labels;
				opt = { mode: 'add' };
				break;
			case 'remove_labels':
				obj = DeskPRO.Agent.TicketList.Property.Labels;
				opt = { mode: 'remove' };
				break;
			case 'flag':
				obj = DeskPRO.Agent.TicketList.Property.Flag;
				break;
			case 'ticket_field':
				obj = DeskPRO.Agent.TicketList.Property.TicketField;
				opt = { fieldId: propId };
				break;
		}

		return [obj, opt];
	},

	toggleMacroApplyBtn: function(force, title) {

		var ul = $('.bar-actions', this.ticketBar);

		if (!force) {
			if ($('li.macros', ul).is(':visible')) {
				force = 'on';
			} else {
				force = 'off';
			}
		}

		var otherBtns = $('li:not(.macro-on, .send-reply)', ul);
		var applyBtns = $('li.macro-on', ul);

		if (force == 'on') {
			otherBtns.hide();
			applyBtns.show();
		} else {
			otherBtns.show();
			applyBtns.hide();
		}
	},

	saveActions: function() {
		var ticket_ids = this.getSelectedTicketIds();

		var data = $(':input, select, textarea', $('.actions-terms', this.actionsWrap)).serializeArray();
		data.combine($(':input, select, textarea', $('.ticket-reply', this.actionsWrap)).serializeArray());

		Array.each(ticket_ids, function(id) {
			data.push({
				name: 'ticket_ids[]',
				value: id
			});
		});

		this.page.changeManager.commitChanges();
		DeskPRO_Window.startLoadingIndicator();

		$.ajax({
			cache: false,
			type: 'POST',
			data: data,
			url: BASE_URL + 'agent/ticket-search/ajax-save-actions',
			context: this,
			dataType: 'json',
			success: function () {
				DeskPRO_Window.stopLoadingIndicator();
				DeskPRO_Window.showStatusMessage('Actions were applied successfully');
			}
		});
	}
});
