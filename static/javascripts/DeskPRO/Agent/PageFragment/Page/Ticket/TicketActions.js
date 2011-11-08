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

		var followersList = this.getEl('followers_list');
		var el = this.getEl('agent_assign_ob');
		this.assignOptionBox = new DeskPRO.UI.OptionBoxRevertable({
			element: el,
			trigger: this.getEl('assign_ob_trigger'),
			onSave: function(ob) {
				var selections = ob.getAllSelected();

				var agent_id = parseInt(selections.agents || 0);
				var agentProp = self.changeManager.getPropertyManager('agent_id');
				self.changeManager.setInstantChange(agentProp, agent_id);

				var agent_team_id = parseInt(selections.teams || 0);
				var agentTeamProp = self.changeManager.getPropertyManager('agent_team_id');
				self.changeManager.setInstantChange(agentTeamProp, agent_team_id);

				followersList.empty();

				var selections = ob.getAllSelected();

				var postData = [];
				Array.each(selections.followers, function(part_id) {
					var label = $('.agent-part-label-' + part_id, ob.getElement()).first().text().trim();

					var li = $('<li />');
					li.text(label);

					followersList.append(li);

					postData.push({
						name: 'agent_part_ids[]',
						value: part_id
					});
				});

				if (!selections.followers.length) {
					followersList.append('<li>No followers</li>');
				}

				$.ajax({
					url: BASE_URL + 'agent/tickets/'+self.page.meta.ticket_id+'/set-agent-parts.json',
					type: 'POST',
					dataType: 'json',
					data: postData
				});
			}
		});

		var box1 = self.getEl('people_box_person');
		var box2 = self.getEl('people_box_agent');
		var syncSizes = function() {
			var h1 = box1.height();
			var h2 = box2.height();

			if (h1 > h2) {
				box2.css('min-height', h1);
			} else {
				box1.css('min-height', h2);
			}
		};

		box1.resize(syncSizes);
		box2.resize(syncSizes);
		syncSizes();

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

		var el = $(DeskPRO_Window.util.getPlainTpl($('#department_option_box_tpl')));
		this.departmentOptionBox = new DeskPRO.UI.OptionBox({
			element: el,
			trigger: $('.set-department', wrapper),
			onClose: function(ob) {
				var prop = self.changeManager.getPropertyManager('department_id');
				var depId = parseInt(ob.getSelected('department'));
				self.changeManager.setInstantChange(prop, depId);
			}
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
				this.confirmMacro($(info.itemEl).data('macro-id'));
			}).bind(this)
		});

		this.macroCancelBtn.click((function() {
			this.revertMacro();
		}).bind(this));

		this.macroApplyBtn.click((function() {
			this.saveMacro();
		}).bind(this));
	},

	_initMacroOverlay: function() {
		var self = this;
		if (this.macroOverlay) {
			return;
		}

		var overlayEl = this.getEl('confirm_macro_overlay');

		this.getEl('apply_macro_btn').click(function() {
			self.saveMacro();
		});

		this.macroOverlay = new DeskPRO.UI.Overlay({
			contentElement: overlayEl
		});

		var add = $(DeskPRO_Window.util.getPlainTpl($('#ticketactions_actionsform_tpl')));
		$('.actions-list', this.macroOverlay.getElement()).empty().append(add);
	},

	confirmMacro: function(macroId) {
		this.macroActions = null;

		$.ajax({
			url: this.page.getMetaData('getMacroUrl').replace('$macro_id', macroId),
			type: 'GET',
			context: this,
			dataType: 'json',
			success: function(data) {
				this._initMacroOverlay();

				var add = $('.actions-list', this.macroOverlay.getElement());
				$('.search-terms', add).empty();

				var editor = new DeskPRO.Form.RuleBuilder($('.actions-builder-tpl', add));
				Array.each(data.actions_display, function(info, x) {
					var basename = 'actions[initial_' + x + ']';
					editor.addNewRow($('.search-terms', add), basename, {
						type: info.type,
						op: info.op,
						options: info.options
					});
				});

				this.macroActions = data.actions_apply;

				$('.menu-trigger', add).removeClass('menu-trigger').unbind('click');
				$('.remove', add).remove();

				this.macroOverlay.close();
			}
		});
	},

	saveMacro: function() {
		if (!this.macroActions || !this.macroActions.length) {
			return;
		}

		console.log('Applying macro actions: %o', this.macroActions);

		Array.each(this.macroActions, function(action_info) {
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
				console.warn('Unknown property `%s`. Actions: %o', type, action);
			}
		}, this);

		this.changeManager.applyChanges();
		if (this.changeManager.hasChangedProperty('reply')) {
			this.page.replyBox.saveReply();
		}

		var self = this;
		this.changeManager.saveChanges(null, function() {;
				this.macroOverlay
		});

		this.macroActions = null;
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
