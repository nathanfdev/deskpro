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
			var $assign = self.getEl('assign_me'),
				$unassign = self.getEl('unassign_agent');

			agent_id == $assign.data('me') ? $assign.hide() : $assign.show();
			agent_id ? $unassign.show() : $unassign.hide();

			if (self.page.ticketReplyBox) {
				self.page.ticketReplyBox.getElById('agent_sel').select2('val', agent_id);
				self.page.ticketReplyBox.getElById('agent_sel').trigger('change');
			}

			callQueue.call(function() {
				self.changeManager.setInstantChange(agentProp, agent_id, function() {
					callQueue.next();
				});
			});
		});

		this.getEl('assign_me').on('click', function() {
			self.getEl('agent_sel').val($(this).data('me')).trigger('change');
		});

		this.getEl('unassign_agent').on('click', function() {
			self.getEl('agent_sel').val(0).trigger('change');
		});

		this.getEl('agent_team_sel').on('change', function() {

			if ($(this).hasClass('eat-change')) {
				$(this).removeClass('eat-change');
				return;
			}

			var agent_team_id = parseInt($(this).find(':selected').val()) || 0;
			var agentTeamProp = self.changeManager.getPropertyManager('agent_team_id');
			var $assign = self.getEl('assign_team'),
				$unassign = self.getEl('unassign_team');
			agent_team_id == $assign.data('team') ? $assign.hide() : $assign.show();
			agent_team_id ? $unassign.show() : $unassign.hide();

			if (self.page.ticketReplyBox) {
				self.page.ticketReplyBox.getElById('agent_team_sel').select2('val', agent_team_id);
				self.page.ticketReplyBox.getElById('agent_sel').trigger('change');
			}

			callQueue.call(function() {
				self.changeManager.setInstantChange(agentTeamProp, agent_team_id, function() {
					callQueue.next();
				});
			});
		});

		this.getEl('assign_team').on('click', function() {
			self.getEl('agent_team_sel').val($(this).data('team')).trigger('change');
		});
		this.getEl('unassign_team').on('click', function() {
			self.getEl('agent_team_sel').val(0).trigger('change');
		});

		//------------------------------
		// Followers
		//------------------------------

		var followerSel = this.page.getEl('followers_sel');
		var followersList = this.page.getEl('followers_list');

		this.page.getEl('add_follower_btn').on('click', function(ev) {
			ev.preventDefault();
			self.page.getEl('followers_sel_wrap').toggleClass('on');
			followerSel.select2('val', '0');
		});

		this.page.getEl('follower_me').on('click', function() {
			followerSel.val($(this).data('me')).trigger('change');
		});

		followerSel.on('change', function() {
			var agentId = parseInt($(this).val());
			self.page.getEl('followers_sel_wrap').removeClass('on');

			if (!agentId || followersList.find('.agent-' + agentId)[0]) {
				return;
			}

			var option = followerSel.find('option[value="' + agentId + '"]');

			var li = $('<li class="agent-'+agentId+'" data-agent-id="'+agentId+'"><a class="dp-btn dp-btn-small agent-link" data-agent-id="'+agentId+'"><span class="text"></span><span class="remove-row-trigger"> <i class="icon-remove"></i></span></a></li>');
			li.find('span.text').css('background-image', 'url(' +option.data('icon-small') + ')').text(option.text());

			followersList.append(li);
			updateFollowersList();
		});

		followersList.on('click', '.remove-row-trigger', function(ev) {
			ev.preventDefault();
			ev.stopPropagation();
			ev.stopImmediatePropagation();

			$(this).closest('li').remove();
			updateFollowersList();
		});

		var updateFollowersList = function() {
			var postData = [{
				name: 'with_set_agent_parts',
				value: 1
			}];
			followersList.find('li').each(function() {
				postData.push({
					name: 'set_agent_part_ids[]',
					value: $(this).data('agent-id')
				});
			});

			callQueue.call(function() {
				self.changeManager.saveChanges(postData, function() {
					callQueue.next();
				});
			});

			var $assign = self.getEl('follower_me');
			followersList.find('.agent-' + $assign.data('me')).length ? $assign.hide() : $assign.show();
		};

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
			if (!this.page.meta.ticket_perms.modify_set_archived) {
				statusEl.find('option[value="archived"]').not(':selected').remove();
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

			this.page.getEl('field_holders').on('change', 'select.prop-input-product, select.prop-input-priority_id, select.prop-input-workflow_id, select.prop-input-category_id', function() {
				self.page.ticketFields.updateDisplay();
			});
		}

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

		var macroMenu = this.getEl('macros_menu');
		var statusMenuMenu = this.statusMenuMenu = new DeskPRO.UI.Menu2(macroMenu, {
			positionBy: this.getEl('macros_menu_trigger'),
			openBelow: true,
			onFilterUpdated: function(info) {
				var isCtrl = info.isCtrl;
				var ev = info.event;

				if (isCtrl && (ev.which == 85)) {
					closeStatusMenu();
					self.page.shortcutReplySetAwaitingUser();
					info.cancel = true;
					return;
				}
				if (isCtrl && (ev.which == 65)) {
					closeStatusMenu();
					self.page.shortcutReplySetAwaitingAgent();
					info.cancel = true;
					return;
				}
				if (isCtrl && (ev.which == 68)) {
					closeStatusMenu();
					self.page.shortcutReplySetResolved();
					info.cancel = true;
					return;
				}
			},
			onItemSelected: function(info) {
				var item = info.item;
				if (item.hasClass('open-settings-trigger')) {
					$('#settingswin').trigger('dp_open', 'macros');
				} else if (item.data('macro-id')) {
					self.confirmMacro(item.data('macro-id'));
				}
			}
		});

		this.getEl('macros_menu_trigger').on('click', function(ev) {
			statusMenuMenu.open();
		});

		DP.select(this.page.getEl('flag'));
		DP.select(this.page.getEl('department_id'));
		DP.select(this.page.getEl('status_code'));
		DP.select(this.page.getEl('urgency'));
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

				var ul = overlayEl.find('ul.actions-list');
				ul.empty();

				data.descriptions.forEach(function(desc) {
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

		console.log('Applying macro %d', this.macroId);

		var url = BASE_URL + 'agent/tickets/'+this.ticketId+'/'+this.macroId+'/apply-macro.json',
      self = this;

		$.ajax({
			url: url,
			type: 'POST',
			dataType: 'json',
			context: this,
			success: function(data) {

				if (data.error) {
          if (data.error_messages) {

            var prop = self.page.changeManager.getPropertyManager('status');
            self.page.changeManager.setInstantChange(prop, 'awaiting_agent');

            var list = self.page.getEl('field_errors').find('ul').empty();
            data.error_messages.forEach(function(msg) {
              var li = $('<li/>');
              li.text(msg);
              li.appendTo(list);
            });

            self.page.getEl('field_errors').show().addClass('on');

            self.page.getEl('field_edit_start').click();
            self.page.getEl('field_edit_cancel').show();
            self.page.getEl('field_edit_save').show();
            self.page.getEl('field_edit_controls').removeClass('loading');

            return DeskPRO_Window.showAlert('Your reply was saved but the status was not set to resolved because of form errors. You should correct these errors and then you may set the status to resolved.');
          }

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
	},

	destroy: function() {
		if (this.statusMenuMenu) {
			this.statusMenuMenu.destroy();
			this.statusMenuMenu = null;
		}
		if (this.macroOverlay) {
			this.macroOverlay.destroy();
			this.macroOverlay = null;
		}
		this.page = null;
		this.changeManager = null;
		this.destroyEvents();
	}
});
