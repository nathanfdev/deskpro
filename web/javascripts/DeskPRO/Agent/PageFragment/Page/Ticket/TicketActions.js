Orb.createNamespace('DeskPRO.Agent.PageFragment.Page.Ticket');

/**
 * Handles functionality of most of the header bit, such as
 * Assign to Me, assign to my team, status etc.
 */
DeskPRO.Agent.PageFragment.Page.Ticket.TicketActions = new Orb.Class({
	Implements: [Orb.Util.Events, Orb.Util.Options],

	initialize: function(page, options) {

		var self = this;

		this.macroId = null; // currently open macro id
		this.page = page;
		this.options = {};

		this.setOptions(options);

		this.changeManager = this.page.changeManager;
		this.ticketId = this.page.meta.ticket_id;

		var wrapper = this.page.wrapper;
		var actionsButtons = this.getEl('action_buttons');

		this.page.getEl('flag').on('change', function() {
			var value = $(this).val();
			var prop = self.changeManager.getPropertyManager('flag');
			self.changeManager.setInstantChange(prop, value);
		});

		DP.select(this.getEl('agent_sel'));
		DP.select(this.getEl('agent_team_sel'));
		DP.select(this.getEl('followers_sel'));

		var showSaving = this.getEl('agent_prop_controls').find('.mark-loading');
		var showSaved  = this.getEl('agent_prop_controls').find('.mark-saved');
		var callQueue = new Orb.Util.CallQueue({
			startCallback: function() {
				showSaved.stop().hide();
				showSaving.show();
			},
			endCallback: function() {
				showSaving.hide();
				showSaved.show().fadeOut(1000);
			}
		});

		this.getEl('agent_sel').on('change', function() {

			if ($(this).hasClass('eat-change')) {
				$(this).removeClass('eat-change');
				return;
			}

			var agent_id = parseInt($(this).find(':selected').val()) || 0;
			var agentProp = self.changeManager.getPropertyManager('agent_id');

			callQueue.call(function() {
				self.changeManager.setInstantChange(agentProp, agent_id, function() {
					callQueue.next();
				});
			});
		});

		this.getEl('agent_team_sel').on('change', function() {

			if ($(this).hasClass('eat-change')) {
				$(this).removeClass('eat-change');
				return;
			}

			var agent_team_id = parseInt($(this).find(':selected').val()) || 0;
			var agentTeamProp = self.changeManager.getPropertyManager('agent_team_id');

			callQueue.call(function() {
				self.changeManager.setInstantChange(agentTeamProp, agent_team_id, function() {
					callQueue.next();
				});
			});
		});

		this.getEl('followers_sel').on('change', function() {
			var postData = [{
				name: 'with_set_agent_parts',
				value: 1
			}];
			$(this).find(':selected').each(function() {
				postData.push({
					name: 'set_agent_part_ids[]',
					value: $(this).val()
				});
			});

			callQueue.call(function() {
				self.changeManager.saveChanges(postData, function() {
					callQueue.next();
				});
			});
		});

		var box1 = self.getEl('people_box_person_container');
		var box2 = self.getEl('people_box_agent_container');
		var box1_in = $('> article', box1);
		var box2_in = $('> article', box2);

		var syncSizes = function() {
			var h1 = 0;
			var h2 = 0;

			box1_in.each(function() { var thisH = $(this).outerHeight(); if (thisH > h1) { h1 = thisH; } });
			box2_in.each(function() { var thisH = $(this).outerHeight(); if (thisH > h2) { h2 = thisH; } });

			var h = (h1 > h2) ? h1 : h2;

			box2.css('min-height', h);
			box1.css('min-height', h);
		};

		// TODO handle resize without element resize monitor
		box1.on('resize', syncSizes);
		box2.on('resize', syncSizes);
		syncSizes();

		//------------------------------
		// Status
		//------------------------------

		if (this.page.meta.ticket_perms.modify_set_resolved || this.page.meta.ticket_perms.modify_set_awaiting_agent || this.page.meta.ticket_perms.modify_set_awaiting_user) {
			var statusEl = this.page.getEl('status_code').on('change', function() {
				var prop = self.changeManager.getPropertyManager('status');

				var status = $(this).val();
				self.changeManager.setInstantChange(prop, status);
			});

			if (!this.page.meta.ticket_perms.modify_set_resolved) {
				statusEl.find('option[value="resolved"]').not(':selected').remove();
			}
			if (!this.page.meta.ticket_perms.modify_set_awaiting_agent) {
				statusEl.find('option[value="awaiting_agent"]').not(':selected').remove();
			}
			if (!this.page.meta.ticket_perms.modify_set_awaiting_user) {
				statusEl.find('option[value="awaiting_user"]').not(':selected').remove();
			}
			if (!this.page.meta.ticket_perms.modify_set_closed) {
				statusEl.find('option[value="closed"]').not(':selected').remove();
			}
		}

		//------------------------------
		// Department
		//------------------------------

		if (this.page.meta.ticket_perms.modify_department) {
			this.page.getEl('department_id').on('change', function() {
				var prop = self.changeManager.getPropertyManager('department_id');
				var depId = parseInt($(this).val());
				var currentDepId = prop.getValue();

				if (!depId) {
					return;
				}

				self.changeManager.setInstantChange(prop, depId);
				self.page.ticketFields.updateDisplay();
			});

			this.page.getEl('field_holders').find('select.prop-input-product, select.prop-input-priority_id, select.prop-input-workflow_id, select.prop-input-category_id').on('change', function() {
				self.page.ticketFields.updateDisplay();
			});
		}

		//------------------------------
		// Hold/unhold
		//------------------------------

		wrapper.on('click', '.set-hold', function() {

			var val = 1;
			if ($(this).is('.unhold')) {
				val = 0;
			}

			var prop = self.changeManager.getPropertyManager('is_hold');
			self.changeManager.setInstantChange(prop, val);
		});

		//------------------------------
		// Urgency
		//------------------------------

		if (this.page.meta.ticket_perms.modify_fields) {
			this.page.getEl('urgency').on('change', function() {
				var prop = self.changeManager.getPropertyManager('urgency');

				var urgency = $(this).val();
				self.changeManager.setInstantChange(prop, urgency);
			});
		}

		//------------------------------
		// Macros
		//------------------------------

		this.macroControls  = $('.macro-controls');
		this.macroApplyBtn  = $('.save', this.macroControls);
		this.macroCancelBtn = $('.cancel', this.macroControls);

		var macroMenu = this.getEl('macros_menu');
		this.macrosMenu = new DeskPRO.UI.Menu({
			triggerElement: this.getEl('macros_menu_trigger'),
			menuElement: macroMenu,
			onItemClicked: (function(info) {
				var item = $(info.itemEl);
				if (item.hasClass('open-settings-trigger')) {
					$('#settingswin').trigger('dp_open', 'macros');
				} else {
					this.confirmMacro($(info.itemEl).data('macro-id'));
				}
			}).bind(this)
		});

		$('#settingswin').on('dp_macros_updated', function(ev) {
			macroMenu.find('li').not('.open-settings-trigger').remove();
			Array.each(ev.macroItems, function(x) {
				var li = $('<li />');
				li.data('macro-id', x.id);
				li.text(x.title);

				li.appendTo(macroMenu);
			});
		});

		this.macroCancelBtn.on('click', (function() {
			this.revertMacro();
		}).bind(this));

		this.macroApplyBtn.on('click', (function() {
			this.saveMacro();
		}).bind(this));

		this.page.getEl('headerbox_box_props').find('select').each(function() {
			DP.select($(this));
		});

		DP.select(this.page.getEl('flag'));
	},

	_initMacroOverlay: function() {
		var self = this;
		if (this.macroOverlay) {
			return;
		}

		var overlayEl = this.getEl('confirm_macro_overlay');
		this.getEl('apply_macro_btn').on('click', function() {
			self.saveMacro();
		});

		this.macroOverlay = new DeskPRO.UI.Overlay({
			contentElement: overlayEl
		});
	},

	confirmMacro: function(macroId) {
		this.macroActions = null;

		var overlayEl = this.getEl('confirm_macro_overlay');
		$.ajax({
			url: this.page.getMetaData('getMacroUrl').replace('$macro_id', macroId),
			type: 'GET',
			context: this,
			dataType: 'json',
			success: function(data) {
				this._initMacroOverlay();

				console.log(data);

				var ul = overlayEl.find('ul.actions-list');
				ul.empty();

				Array.each(data.descriptions, function(desc) {
					var li = $('<li />');
					li.html(desc);

					ul.append(li);
				});

				this.macroId = macroId;
				this.macroOverlay.open();
			}
		});
	},

	saveMacro: function() {

		this.macroOverlay.close();

		if (!this.macroId) {
			return;
		}

		DP.console.log('Applying macro %d', this.macroId);

		var url = BASE_URL + 'agent/tickets/'+this.ticketId+'/'+this.macroId+'/apply-macro.json';

		$.ajax({
			url: url,
			type: 'POST',
			dataType: 'json',
			context: this,
			success: function(data) {

				if (data.error) {
					DeskPRO_Window.showAlert("The macro was not applied because you do not have permission to perform one or more of the defined actions.");
					return;
				}

				this.page.closeSelf();

				if (!data.close_tab) {
					DeskPRO_Window.runPageRoute('page:' + BASE_URL + 'agent/tickets/' + this.ticketId);
				}
			}
		});


		this.macroId = null;
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
