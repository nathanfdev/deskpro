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

		var wrapper = this.page.wrapper;
		var actionsButtons = this.getEl('action_buttons');

		this.getEl('flag_opt').bind('listradiochange', function(ev, value) {
			var prop = self.changeManager.getPropertyManager('flag');
			self.changeManager.setInstantChange(prop, value);
		});

		//------------------------------
		// Assign ...
		//------------------------------

		var el = $('#set_agent_and_team_optionbox_radio').clone();
		this.assignAgentOptionBox = new DeskPRO.UI.OptionBox({
			element: el,
			trigger: this.getEl('assign_to_btn'),
			onClose: function() {
				// Agent
				var agent_id = parseInt(selections.agents || 0);
				if (agent_id == exist_agent_id) {
					self.getElById('do_agent_id').val('0');
				} else {
					self.getElById('do_agent_id').val('0');
					self.getElById('agent_id').val(agent_id);

					var label = $('.agent-label-' + agent_id, self.getElById('agent_selector')).text().trim();
					$('.new-val-label', agentDetailEl).text(label);
				}

				// Agent Team
				var agent_team_id = parseInt(selections.teams || 0);
				if (agent_team_id == exist_agent_team_id) {
					self.getElById('do_agent_team_id').val('0');
				} else {
					self.getElById('do_agent_team_id').val('1');
					self.getElById('agent_team_id').val(agent_team_id);

					var label = $('.agent-team-label-' + agent_team_id, self.getElById('agent_selector')).text().trim();
					$('.new-val-label', teamDetailEl).text(label);
				}
			}
		});
		el.delegate('button', 'click', function() {
			var btn = $(this);

			if (btn.data('type') == 'team') {
				var teamId = btn.data('team-id');
				var agentTeamProp = self.changeManager.getPropertyManager('agent_team_id');
				self.changeManager.setInstantChange(agentTeamProp, teamId);
			} else {
				var agentId = btn.data('agent-id');

				var agentProp = self.changeManager.getPropertyManager('agent_id');
				self.changeManager.setInstantChange(agentProp, agentId);
			}

			self.assignAgentOptionBox.close();
		});

		//------------------------------
		// Status
		//------------------------------

		this.statusMenu = new DeskPRO.UI.Menu({
			triggerElement: $('.set-status', wrapper),
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

		this.depMenu = new DeskPRO.UI.Menu({
			triggerElement: $('.set-department', wrapper),
			menuElement: $('#department_menu'),
			onItemClicked: (function(info) {
				var item = $(info.itemEl);
				var prop = this.changeManager.getPropertyManager('department_id');

				var status = parseInt(item.data('department-id'));
				this.changeManager.setInstantChange(prop, status);
			}).bind(this)
		});

		//------------------------------
		// Hold/unhold
		//------------------------------

		$('.set-hold.hold', wrapper).click((function() {
			var prop = this.changeManager.getPropertyManager('is_hold');
			this.changeManager.setInstantChange(prop, 1);
		}).bind(this));
		$('.set-hold.unhold', wrapper).click((function() {
			var prop = this.changeManager.getPropertyManager('is_hold');
			this.changeManager.setInstantChange(prop, 0);
		}).bind(this));


		//------------------------------
		// Macros
		//------------------------------

		this.macroControls  = $('.macro-controls');
		this.macroApplyBtn  = $('.save', this.macroControls);
		this.macroCancelBtn = $('.cancel', this.macroControls);

		this.macrosMenu = new DeskPRO.UI.Menu({
			triggerElement: this.getEl('macros_menu_trigger'),
			menuElement: this.getEl('macros_menu'),
			onItemClicked: (function(info) {
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

				this.macroOpacityHighlight = $('section.ticket-header, div.messages-wrap').css('opacity', '0.4');
			}
		});
	},

	saveMacro: function() {

		if (this.changeManager.hasChangedProperty('reply')) {
			this.page.replyBox.saveReply();
		}

		this.changeManager.saveChanges();
		if (this.macroOpacityHighlight) {
			this.macroOpacityHighlight.css('opacity', 1);
			this.macroOpacityHighlight = null;
		}
		this.toggleMacroApplyBtn('off');
	},

	revertMacro: function() {
		this.changeManager.revertChanges();
		if (this.macroOpacityHighlight) {
			this.macroOpacityHighlight.css('opacity', 1);
			this.macroOpacityHighlight = null;
		}
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
			this.page.wrapper.addClass('macro-open');
		} else {
			this.page.wrapper.removeClass('macro-open');
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
