Orb.createNamespace('DeskPRO.Agent.PageHelper');

/**
 * Handles updating display based on department and rules
 */
DeskPRO.Agent.PageHelper.TicketFields = new Orb.Class({
	initialize: function(page) {
		var self = this;
		this.page = page;
		this.display = this.page.getEl('field_holders');

		this.currentDisplay = [];

		this.ticketReader = {
			getDepartmentId: function() {
				var catId = self.page.getEl('department_id').val();
				return parseInt(catId) || 0;
			},
			getCategoryId: function() {
				var catId = self.page.getEl('ticket_category_id').val();
				return parseInt(catId) || 0;
			},
			getPriorityId: function() {
				var catId = self.page.getEl('ticket_priority_id').val();
				return parseInt(catId) || 0;
			},
			getProductId: function() {
				var catId = self.page.getEl('ticket_product_id').val();
				return parseInt(catId) || 0;
			},
			getOrganizationId: function() {
				return 0;
			},
			getWorkflow: function() {
				var catId = self.page.getEl('ticket_workflow_id').val();
				return parseInt(catId) || 0;
			}
		};

		this.fieldDisplay = new DeskPRO.Agent.PageHelper.TicketFieldDisplay(this.ticketReader);

		this.page.getEl('department').on('change', function() {
			self.updateDisplay();
		});
	},

	updateDisplay: function() {
		var fields = this.fieldDisplay.getFields(this.ticketReader.getDepartmentId());

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
		}

		// No Changes, dont need to do any expensive dom work
		if (!change) {
			return;
		}

		this.currentDisplay = fields;

		this.display.find('div.item.item-on').hide().removeClass('item-on');

		Object.each(this.currentDisplay, function(fields, section) {
			Array.each(fields, function(f) {
				if (f.field_type == 'ticket_field') {
					var classname = 'ticket_field_' + f.field_id;
				} else {
					var classname = f.field_type;
				}

				this.display.find('.item.' + classname).detach().appendTo(this.display).show().addClass('item-on');
			});
		}, this);

		if (this.display.find('div.item.item-on')[0]) {
			this.page.getEl('fields_display_main_wrap_tab').show();
		} else {
			this.page.getEl('fields_display_main_wrap_tab').hide();
			if (this.page.getEl('fields_display_main_wrap_tab').hasClass('on')) {
				this.page.getEl('fields_display_main_wrap_tab').next().trigger('click');
			}
		}
	}
});