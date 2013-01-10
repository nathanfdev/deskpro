Orb.createNamespace('DeskPRO.Agent.PageHelper');

/**
 * Handles updating display based on department and rules
 */
DeskPRO.Agent.PageHelper.TicketFields = new Orb.Class({
	initialize: function(page) {
		var self = this;
		this.page = page;
		this.display = this.page.getEl('field_holders').find('.field-holders-table');

		this.mode = 'view';
		this.currentDisplay = [];
		this.currentDisplayModify = [];

		this.ticketReader = {
			getDepartmentId: function() {
				if (self.mode == 'edit') {
					var catId = self.page.getEl('department_id').val();
				} else {
					var catId = self.page.getEl('value_form').find('.department_id').val();
				}
				return parseInt(catId) || 0;
			},
			getCategoryId: function() {
				if (self.mode == 'edit') {
					var catId = self.page.getEl('ticket_category_id').val();
				} else {
					var catId = self.page.getEl('value_form').find('.category_id').val();
				}
				return parseInt(catId) || 0;
			},
			getPriorityId: function() {
				if (self.mode == 'edit') {
					var catId = self.page.getEl('ticket_priority_id').val();
				} else {
					var catId = self.page.getEl('value_form').find('.priority_id').val();
				}
				return parseInt(catId) || 0;
			},
			getProductId: function() {
				if (self.mode == 'edit') {
					var catId = self.page.getEl('ticket_product_id').val();
				} else {
					var catId = self.page.getEl('value_form').find('.product_id').val();
				}
				return parseInt(catId) || 0;
			},
			getOrganizationId: function() {
				return 0;
			},
			getWorkflow: function() {
				if (self.mode == 'edit') {
					self.page.getEl('value_form').find('.workflow_id').val();
				} else {
					var catId = self.page.getEl('ticket_workflow_id').val();
				}
				return parseInt(catId) || 0;
			}
		};

		this.fieldDisplay = new DeskPRO.Agent.PageHelper.TicketFieldDisplay(this.ticketReader, 'view');
		this.fieldDisplayModify = new DeskPRO.Agent.PageHelper.TicketFieldDisplay(this.ticketReader, 'modify');

		this.page.getEl('department').on('change', function() {
			self.updateDisplay();
		});

		this.page.getEl('field_edit_start').on('click', function(ev) {
			ev.preventDefault();
			self.openEditMode();
		});

		self.page.getEl('field_edit_cancel').on('click', function(ev) {
			ev.preventDefault();
			self.closeEditMode();
		});

		self.page.getEl('field_edit_save').on('click', function(ev) {
			self.page.getEl('field_edit_cancel').hide();
			self.page.getEl('field_edit_save').hide();
			self.page.getEl('field_edit_start').hide();
			self.page.getEl('field_edit_controls').addClass('loading');
			self.saveChanges();
		});

		this.page.changeManager.addEvent('updateResult', function(data) {
			if (data.holders) {
				self.replaceHolders(data.holders);
			}
		});

		this.page.getEl('fields_display_main_wrap').data('tab-on-hide', function() {
			self.page.getEl('field_edit_controls').hide();
		});
		this.page.getEl('fields_display_main_wrap').data('tab-on-show', function() {
			self.page.getEl('field_edit_controls').show();
		});
	},

	openEditMode: function() {
		this.mode = 'edit';

		this.display.addClass('mode-edit-on');
		this.page.getEl('field_edit_start').hide();
		this.page.getEl('field_edit_cancel').show();
		this.page.getEl('field_edit_save').show();
		this.page.getEl('field_edit_controls').removeClass('loading');

		this.display.find('select[multiple]').each(function() {
			var min = $(this).width() + 30;
			var parent = $(this).closest('td').find('> div').first().width();
			if (parent) {
				min = Math.max(min, Math.ceil(parent / 1.75));
			}
			$(this).width(min);
		});
		DP.select(this.display.find('select'));

		this.updateDisplay();

		$('.Date.customfield input', this.display).datepicker({
			dateFormat: 'yy-mm-dd',
			showButtonPanel: true,
			beforeShow: function(input) {
				setTimeout(function() {
					var buttonPane = $(input).datepicker("widget").find(".ui-datepicker-buttonpane");

					buttonPane.find('button:first').remove();

					var btn = $('<button class="ui-datepicker-current ui-state-default ui-priority-secondary ui-corner-all" type="button">Clear</button>');
					btn.unbind("click").bind("click", function () { $.datepicker._clearDate( input ); });
					btn.appendTo( buttonPane );

					$(input).datepicker("widget").css('z-index', 30001);
				},1);
			}
		});
	},

	closeEditMode: function() {
		this.mode = 'view';

		this.display.removeClass('mode-edit-on');
		this.page.getEl('field_edit_save').hide();
		this.page.getEl('field_edit_cancel').hide();
		this.page.getEl('field_edit_start').show();
		this.page.getEl('field_edit_controls').removeClass('loading');
		this.updateDisplay();
	},

	updateDisplay: function() {
		if (this.mode == 'view') {
			this.updateDisplay_view();
		} else {
			this.updateDisplay_modify();
		}
	},

	updateDisplay_modify: function() {
		var fields = this.fieldDisplayModify.getFields(this.ticketReader.getDepartmentId());
		if (!fields || !fields['default']) {
			fields['default'] = [];
		}

		fields = fields['default'];

		// Check to see if the fields are the same and in the same order
		if (fields.length == this.currentDisplayModify.length) {
			var change = false;
			for (var i = 0; i < fields.length; i++) {
				if (fields[i].field_type == this.currentDisplay[i].field_type) {
					if (fields[i].field_type == 'ticket_field' && fields[i].field_id != this.currentDisplay[i].field_id) {
						change = true;
						break;
					}
				} else {
					change = true;
					break;
				}
			}
		} else {
			var change = true;
		}

		// No Changes, dont need to do any expensive dom work
		if (!change) {
			console.log("[TicketFields] No change");
			return;
		}

		this.currentDisplayModify = fields;

		this.display.find('tbody.item.item-on').hide().removeClass('item-on');

		Array.each(this.currentDisplayModify, function(f) {
			if (f.field_type == 'ticket_field') {
				var classname = 'ticket_field_' + f.field_id;
			} else {
				var classname = f.field_type;
			}

			this.display.find('.item.' + classname).detach().appendTo(this.display).show().addClass('item-on');
		}, this);

		var ons = this.display.find('tbody.item-on');
		if (ons[0]) {
			ons.removeClass('last');
			ons.last().addClass('last');
			this.page.getEl('fields_display_main_wrap_tab').show();
		} else {
			this.page.getEl('fields_display_main_wrap_tab').hide();
			if (this.page.getEl('fields_display_main_wrap_tab').hasClass('on')) {
				this.page.getEl('fields_display_main_wrap_tab').next().trigger('click');
			}
		}
	},

	updateDisplay_view: function() {
		var fields = this.fieldDisplay.getFields(this.ticketReader.getDepartmentId());
		if (!fields || !fields['default']) {
			fields['default'] = [];
		}

		fields = fields['default'];

		// Check to see if the fields are the same and in the same order
		if (fields.length == this.currentDisplay.length) {
			var change = false;
			for (var i = 0; i < fields.length; i++) {
				if (fields[i].field_type == this.currentDisplay[i].field_type) {
					if (fields[i].field_type == 'ticket_field' && fields[i].field_id != this.currentDisplay[i].field_id) {
						change = true;
						break;
					}
				} else {
					change = true;
					break;
				}
			}
		} else {
			var change = true;
		}

		// No Changes, dont need to do any expensive dom work
		if (!change) {
			console.log("[TicketFields] No change");
			return;
		}

		this.currentDisplay = fields;

		this.display.find('tbody.item.item-on').hide().removeClass('item-on');

		Array.each(this.currentDisplay, function(f) {
			if (f.field_type == 'ticket_field') {
				var classname = 'ticket_field_' + f.field_id;
			} else {
				var classname = f.field_type;
			}

			this.display.find('.item.' + classname).detach().appendTo(this.display).show().addClass('item-on');
		}, this);

		var ons = this.display.find('tbody.item-on');
		if (ons[0]) {
			ons.removeClass('last');
			ons.last().addClass('last');
			this.page.getEl('fields_display_main_wrap_tab').show();
		} else {
			this.page.getEl('fields_display_main_wrap_tab').hide();
			if (this.page.getEl('fields_display_main_wrap_tab').hasClass('on')) {
				this.page.getEl('fields_display_main_wrap_tab').next().trigger('click');
			}
		}
	},

	saveChanges: function() {
		var changeManager = this.page.changeManager;

		this.display.find('[data-prop-id]').each(function() {
			var prop = changeManager.getPropertyManager($(this).data('prop-id'));
			prop.setValue($(this).val());

			changeManager.addChange(prop);
		});

		var customFieldData = this.display.find('.custom-field input, .custom-field textarea, .custom-field select').serializeArray();

		this.display.find('input[type="checkbox"][value="1"]').not(':checked').each(function() {
			customFieldData.push({
				name: $(this).attr('name'),
				value: '0'
			});
		});

		changeManager.saveChanges(customFieldData, (function(data) {
			this.closeEditMode();
			if (data.data && data.data.reload) {
				this.page.closeSelf();
				DeskPRO_Window.runPageRoute('ticket:' + BASE_URL + 'agent/tickets/' + this.page.meta.ticket_id);
			}
		}).bind(this));
	},

	replaceHolders: function(html) {
		var isEdit = this.display.hasClass('mode-edit-on');
		this.display.parent().html(html);
		this.display = this.page.getEl('field_holders').find('.field-holders-table');
		this.currentDisplay = [];
		this.updateDisplay();
		if (isEdit) {
			this.openEditMode();
		}
	}
});