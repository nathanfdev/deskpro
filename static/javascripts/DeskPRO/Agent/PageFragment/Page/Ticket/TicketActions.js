Orb.createNamespace('DeskPRO.Agent.PageFragment.Page.Ticket');

/**
 * Handles functionality of most of the header bit, such as
 * Assign to Me, assign to my team, status etc.
 */
DeskPRO.Agent.PageFragment.Page.Ticket.TicketActions = new Orb.Class({
	Implements: [Orb.Util.Events, Orb.Util.Options],

	initialize: function(page, options) {

		var self = this;

		this.page = page;
		this.options = {};

		this.setOptions(options);

		this.changeManager = this.page.changeManager;

		var ticketHeader = this.getEl('ticket_header');
		var actionsButtons = this.getEl('action_buttons');

		//------------------------------
		// Assign ...
		//------------------------------

		var agentProp = this.changeManager.getPropertyManager('agent_id');

		$('.assign-me button', actionsButtons).click((function() {
			this.changeManager.setInstantChange(agentProp, DESKPRO_PERSON_ID);
		}).bind(this));

		$('.assign-none button', actionsButtons).click((function() {
			this.changeManager.setInstantChange(agentProp, 0);
		}).bind(this));

		this.assignAgentSelector = new DeskPRO.Agent.Widget.AgentSelector({
			agentList: $('#agent_selector_list'),
			showNone: true,
			multipleChoice: false,
			triggerElement: $('nav.actions .assign-to button, .prop-agent-id', ticketHeader),
			startWith: [agentProp.getValue()],
			onSelectionChanged: (function(info) {
				this.changeManager.setInstantChange(agentProp, info.selection);
			}).bind(this)
		});

		this.assignTeamMenu = new DeskPRO.UI.Menu({
			triggerElement: $('nav.actions .assign-to-team button, .prop-agent-team-id', ticketHeader),
			menuElement: $('#agent_teams_menu'),
			onItemClicked: (function(info) {
				var item = $(info.itemEl);
				var prop = this.changeManager.getPropertyManager('agent_team_id');

				var teamId = parseInt(item.data('team-id'));
				this.changeManager.setInstantChange(prop, teamId);
			}).bind(this)
		});

		//------------------------------
		// Status
		//------------------------------

		this.statusMenu = new DeskPRO.UI.Menu({
			triggerElement: $('.prop-status-icon', ticketHeader),
			menuElement: $('#ticket_status_menu'),
			onItemClicked: (function(info) {
				var item = $(info.itemEl);
				var prop = this.changeManager.getPropertyManager('status');

				var status = item.data('status');
				this.changeManager.setInstantChange(prop, status);
			}).bind(this)
		});

		$('.set-resolved button', actionsButtons).click((function() {
			var prop = this.changeManager.getPropertyManager('status');
			this.changeManager.setInstantChange(prop, 'resolved');
		}).bind(this));

		$('.set-closed button', actionsButtons).click((function() {
			var prop = this.changeManager.getPropertyManager('status');
			this.changeManager.setInstantChange(prop, 'closed');
		}).bind(this));

		$('.set-opem button', actionsButtons).click((function() {
			var prop = this.changeManager.getPropertyManager('status');
			this.changeManager.setInstantChange(prop, 'open');
		}).bind(this));

		$('.set-open button', actionsButtons).click((function() {
			var prop = this.changeManager.getPropertyManager('status');
			this.changeManager.setInstantChange(prop, 'open');
		}).bind(this));

		$('.marks-spam button', actionsButtons).click((function() {
			var prop = this.changeManager.getPropertyManager('status');
			this.changeManager.setInstantChange(prop, 'hidden.spam');
		}).bind(this));

		//------------------------------
		// Status
		//------------------------------

		this.statusMenu = new DeskPRO.UI.Menu({
			triggerElement: $('.prop-department-id', ticketHeader),
			menuElement: $('#department_menu'),
			onItemClicked: (function(info) {
				var item = $(info.itemEl);
				var prop = this.changeManager.getPropertyManager('department_id');

				var status = parseInt(item.data('department-id'));
				this.changeManager.setInstantChange(prop, status);
			}).bind(this)
		});

		//------------------------------
		// Macros
		//------------------------------

		this.macroControls  = $('.macro-controls');
		this.macroApplyBtn  = $('.save', this.macroControls);
		this.macroCancelBtn = $('.cancel', this.macroControls);

		this.macrosMenu = new DeskPRO.UI.Menu({
			triggerElement: $('.macros button', actionsButtons),
			menuElement: this.getEl('macros_menu'),
			onItemClicked: (function(info) {

				if ($(info.itemEl).data('no-macro')) {
					var overlay = new DeskPRO.UI.Overlay({
						contentMethod: 'iframe',
						iframeUrl: BASE_URL + 'agent/settings/ticket-macros/new'
					});

					overlay.openOverlay();
					return;
				}

				this.activateMacro($(info.itemEl).data('macro-id'));

			}).bind(this)
		});

		this.macroCancelBtn.click((function() {
			this.revertMacro();
		}).bind(this));

		this.macroApplyBtn.click((function() {
			this.saveMacro();
		}).bind(this));
	},

	activateMacro: function(macroId) {
		this.currentMacroId = macroId;
		
		$.ajax({
			url: this.page.getMetaData('getMacroUrl').replace('$macro_id', this.currentMacroId),
			type: 'GET',
			context: this,
			dataType: 'json',
			success: function(data) {
				this.previewMacroActions(data);
			}
		});
	},

	saveMacro: function() {
		this.changeManager.saveChanges();
		this.toggleMacroApplyBtn('off');
	},

	revertMacro: function() {
		this.changeManager.revertChanges();
		this.toggleMacroApplyBtn('off');
	},

	previewMacroActions: function(actions) {

		Array.each(actions, function(action_info) {
			var type = action_info.action;
			var action;

			delete action_info.action;
			var action_info_vals = Object.values(action_info);
			if (action_info_vals.length == 1) {
				action = action_info_vals[0];
			} else {
				action = action_info;
			}

			var type_id = null;
			var m = /^(.*?)\[(.*?)\]$/.exec(type);
			if (m !== null) {
				type = m[1];
				type_id = m[2];
			}

			var prop = this.changeManager.getPropertyManager(type, type_id);

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

		if (!force) {
			if (this.macroControls.is(':visible')) {
				force = 'off';
			} else {
				force = 'on';
			}
		}

		if (force == 'on') {
			this.macroControls.show();
		} else {
			this.macroControls.hide();
		}
	},

	/**
	 * Alias for <code>this.page</code>
	 *
	 * @param {HTMLElement}
	 */
	getEl: function(id) {
		return this.page.getEl(id);
	}
});