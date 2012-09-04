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

		var wrapper = this.page.wrapper;
		var actionsButtons = this.getEl('action_buttons');

		this.getEl('flag_opt').bind('listradiochange', function(ev, value) {
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
			var menuEl = $('#ticket_status_menu').clone();
			if (!this.page.meta.ticket_perms.modify_set_resolved) {
				menuEl.find('li[data-status="resolved"]').remove();
			}
			if (!this.page.meta.ticket_perms.modify_set_awaiting_agent) {
				menuEl.find('li[data-status="awaiting_agent"]').remove();
			}
			if (!this.page.meta.ticket_perms.modify_set_awaiting_user) {
				menuEl.find('li[data-status="awaiting_user"]').remove();
			}
			if (!this.page.meta.ticket_perms.modify_set_closed) {
				menuEl.find('li[data-status="closed"]').remove();
			}

			if (this.page.meta.isClosed && !this.page.meta.ticket_perms.modify_set_closed) {
				menuEl.find('li').remove();
			}

			if (menuEl.find('li')[0]) {
				this.statusMenu = new DeskPRO.UI.Menu({
					triggerElement: $('.set-status', wrapper),
					menuElement: menuEl,
					onItemClicked: (function(info) {
						var item = $(info.itemEl);
						var prop = this.changeManager.getPropertyManager('status');

						var status = item.data('status');
						this.changeManager.setInstantChange(prop, status);
					}).bind(this)
				});
			}
		}

		//------------------------------
		// Department
		//------------------------------

		if (this.page.meta.ticket_perms.modify_department) {
			var el = $(DeskPRO_Window.util.getPlainTpl($('#department_option_box_tpl')));
			this.departmentOptionBox = new DeskPRO.UI.OptionBox({
				element: el,
				trigger: $('.set-department', wrapper),
				onClose: function(ob) {
					var prop = self.changeManager.getPropertyManager('department_id');
					var depId = parseInt(ob.getSelected('department'));
					var currentDepId = prop.getValue();

					if (!depId) {
						return;
					}

					if (depId == parseInt(currentDepId)) {
						return;
					}

					self.changeManager.setInstantChange(prop, depId);
				}
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
			var menuEl = ['<ul>'];
			for (var i = 1; i <= 10; i++) {
				menuEl.push('<li data-urgency="' + i + '">' + i + '</li>');
			}
			menuEl.push('</ul>');
			menuEl = $(menuEl.join(''));

			this.urgencyMenu = new DeskPRO.UI.Menu({
				triggerElement: this.getEl('urgency'),
				menuElement: menuEl,
				onItemClicked: (function(info) {
					var item = $(info.itemEl);
					var prop = this.changeManager.getPropertyManager('urgency');

					var urgency = item.data('urgency');
					this.changeManager.setInstantChange(prop, urgency);
				}).bind(this)
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
	},

	_initMacroOverlay: function() {
		var self = this;
		if (this.macroOverlay) {
			return;
		}

		var overlayEl = this.getEl('confirm_macro_overlay');
		var add = $(DeskPRO_Window.util.getPlainTpl($('#ticketactions_actionsform_tpl')));
		$('.actions-list', overlayEl).empty().append(add);

		this.getEl('apply_macro_btn').on('click', function() {
			self.saveMacro();
		});

		this.macroOverlay = new DeskPRO.UI.Overlay({
			contentElement: overlayEl
		});
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

				var add = $('.actions-list', this.macroOverlay.getElement()).addClass('static-list');
				$('.search-terms', add).addClass('static-list').empty();

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

				this.macroId = macroId;
				this.macroOverlay.open();
			}
		});
	},

	saveMacro: function() {

		this.macroOverlay.close();

		if (!this.macroActions || !this.macroActions.length) {
			return;
		}

		DP.console.log('Applying macro actions: %o', this.macroActions);
		var formData = [];

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
				Object.each(action_info, function (v, k) {
					if (k != 'action') {
						formData.push({
							name: 'other_actions[' + type + '][' + k + ']',
							value: v
						});
					}
				});
			}
		}, this);

		formData.push({
			name: 'macro_id',
			value: this.macroId
		})

		this.changeManager.applyChanges();

		var self = this;
		this.changeManager.saveChanges(formData, function() {;
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
