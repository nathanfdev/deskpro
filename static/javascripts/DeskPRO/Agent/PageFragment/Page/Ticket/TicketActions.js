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
		// Department
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