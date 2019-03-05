Orb.createNamespace('DeskPRO.Agent.Ticket.Property');

DeskPRO.Agent.Ticket.Property.Labels = new Orb.Class({
	Extends: DeskPRO.Agent.Ticket.Property.Abstract,

	init: function() {
		this.mode = this.options.mode;
		this.optionName = 'labels';
		this._values = null;
	},

	getName: function() {
		return this.mode + '_' + this.optionName; // add_labels or remove_labels
	},

	getValue: function() {
		return this._values;
	},

	setValue: function(values) {
		this._values = values;
		if (this.mode == 'add') {
			values.forEach(function(val) {
				this.ticketPage.labelsInput.tagit.add(val, '<span class="new">'+val+'</span>');
			}, this);
		} else {
			values.forEach(function(val) {
				var el = $('input[value="'+val+'"]', this.getInterfaceElement());
				if (el.length) {
					el = el.parent(); // input -> li
					li.hide();
				}
			}, this);
		}
	},

	changePersisted: function() {
		if (!this._values) return;

		if (this.mode == 'add') {
			$('li span.new', this.getInterfaceElement()).removeClass('new');
		} else {
			$('li.pending-remove', this.getInterfaceElement()).remove();
		}

		this._values = null;
	},

	changeReverted: function() {
		if (!this._values) return;

		if (this.mode == 'add') {
			$('li:has(span.new)', this.getInterfaceElement()).remove();
		} else {
			values.forEach(function(val) {
				var el = $('input[value="'+val+'"]', this.getInterfaceElement());
				if (el.length) {
					el = el.parent(); // input -> li
					li.show().addClass('pending-remove');
				}
			}, this);
		}

		this._values = null;
	},

	getInterfaceElement: function() {
		return $('ul.tagit', this.ticketPage.contentWrapper);
	}
});
