Orb.createNamespace('DeskPRO.Agent.PageHelper');

DeskPRO.Agent.PageHelper.TicketMassActions = new Orb.Class({
	Implements: [Orb.Util.Options],

	initialize: function(options) {

		this.options = {
			ticketsWrapper: null,
			selectionBar: null,
			changeManager: null
		};

		this.setOptions(options);

		this.ticketsWrapper = this.options.ticketsWrapper;
		this.selectionBar   = this.options.selectionBar;
		this.actionsWrap    = $('.mass-actions', this.selectionBar.options.selectionBar).first();
		this.changeManager  = this.options.changeManager;

		this._applyButtonCallback = null;

		this.initOverlay();
	},

	initOverlay: function() {

		var self = this;

		this.actionsOverlay = new DeskPRO.UI.Overlay({
			contentElement: this.actionsWrap,
			onBeforeOverlayOpened: function(evData) {
				var count = $('input.ticket-select:checked', self.ticketsWrapper).length;
				$('.check-count-overlay', self.actionsWrap).html(count);
			}
		});

		var self = this;
		$('.radio-option', this.actionsWrap).click(function() {
			var el = $(this);

			if (el.is('.radio-on')) {
				el.removeClass('radio-on');
			} else {
				var group = el.data('radio-group');
				if (group) {
					$('.' + group + '.radio-option', this.actionsWrap).removeClass('radio-on');
				}

				el.addClass('radio-on');
			}
		});

		$('.reply-check', this.actionsWrap).click(function() {
			if ($(this).is(':checked')) {
				$('.reply-area', self.actionsWrap).slideDown();
			} else {
				$('.reply-area', self.actionsWrap).slideUp();
			}
		});
	},

	open: function() {
		this.actionsOverlay.open();
	},

	loadMacroActions: function() {

		var macro_id = parseInt($('select.apply-macro-select', this.actionsWrap).val());
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

					if (info.type == 'reply') {
						reply_text = info.options.reply;
					} else {
						this.actionsEditor.addNewRow($('.actions-terms', this.actionsWrap), basename, {
							type: info.type,
							options: info.options
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
		var ticket_ids = this.selectionBar.getCheckedValues();

		return ticket_ids;
	},

	//#################################################################
	//# Mass actions preview stuff
	//#################################################################

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

		console.debug('loading indicator TicketActionsBar._loadActions');

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
			case 'department':
		 	case 'category':
			case 'product':
			case 'priority':
			case 'workflow':
			case 'status':
			case 'agent':
			case 'agent_team':
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

		var ul = $('.tab-bottom-tabs', this.ticketBar);

		if (!force) {
			if ($('.macros', ul).is(':visible')) {
				force = 'on';
			} else {
				force = 'off';
			}
		}

		if (force == 'off') {
			$('.macros', ul).show();
			$('.macros-apply', ul).hide();
			$('.macros-cancel', ul).hide();
		} else {
			$('.macros', ul).hide();
			$('.macros-apply', ul).show();
			$('.macros-cancel', ul).show();
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
		console.debug('loading indicator TicketActionsBar.saveActions');

		$.ajax({
			cache: false,
			type: 'POST',
			data: data,
			url: BASE_URL + 'agent/ticket-search/ajax-save-actions',
			context: this,
			dataType: 'json',
			success: function () {
				DeskPRO_Window.showStatusMessage('Actions were applied successfully');
			}
		});
	}
});
