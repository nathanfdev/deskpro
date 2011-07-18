Orb.createNamespace('DeskPRO.User.ElementHandler');

DeskPRO.User.ElementHandler.NewTicket = new Orb.Class({

	Extends: DeskPRO.User.ElementHandler.ElementHandlerAbstract,

	init: function() {
		this.depSelect = $('select.department_id', this.el);
		this.departmentId = 0;

		var self = this;
		this.depSelect.change(function() {
			self.handleDepChange();
		});

		$('select.sub_department_id', this.el).change(function(){
			self.setDepartment($(this).val());
		});

		$('.with-sub-options', this.el).each(function() {
			var parentSel = $('.parent-option', this);
			var wrapper = this;

			parentSel.change(function() {
				var val = $(this).val();
				var sub = $('.sub-options-' + val, wrapper);

				$('.sub-options', wrapper).hide();
				sub.show();
			});
		});
	},

	handleDepChange: function() {
		var wrapper = $('.department_id_wrapper', this.el);
		$('.sub-options', wrapper).hide();

		var depId = this.depSelect.val();
		var sub = $('.sub-options-' + depId, wrapper);

		if (!sub.length) {
			this.setDepartment(depId);
			return;
		}

		sub.show();
		var subDepId = $('select.department_id', sub);

		this.setDepartment(subDepId);
	},

	setDepartment: function(department_id) {
		this.clearAll();

		if (department_id == this.departmentId) {
			return;
		}

		this.departmentId = department_id;

		if (!window.DESKPRO_TICKET_DISPLAY) {
			return;
		}

		var activeDepId = this.departmentId;

		if (!window.DESKPRO_TICKET_DISPLAY[activeDepId]) {
			if (!window.DESKPRO_TICKET_CAT_PARENTS) {
				return;
			}

			while (true) {
				var activeDepId = window.DESKPRO_TICKET_CAT_PARENTS[activeDepId];
				if (!activeDepId) {
					return;
				}

				if (window.DESKPRO_TICKET_DISPLAY[activeDepId]) {
					return;
				}
			}
		}

		var depItems = window.DESKPRO_TICKET_DISPLAY[activeDepId];
		console.log('depItems %o', depItems);
		
		Array.each(depItems, function(item) {
			var itemId = this.getItemId(item);
			var itemEl = $('.' + itemId + ':first');
			itemEl.show();
		}, this);
	},

	clearAll: function() {
		$('.ticket-display-field').hide();
	},

	getItemId: function(item) {

		var itemId = item.item_type;
		if (item.item_id) {
			itemId += '_' + item.item_id;
		}

		return itemId;
	}

});